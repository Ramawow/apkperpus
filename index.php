<?php
require 'db.php'; // Menghubungkan dengan file database
$notif_message = ''; // Variabel untuk menyimpan pesan notifikasi

// Menangani aksi insert, update, dan delete untuk peminjam
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add_peminjam' || $action === 'update_peminjam') {
        // Mendapatkan data dari form peminjam
        $kd_peminjam = $_POST['kd_peminjam'] ?? null;
        $nama_peminjam = $_POST['nama_peminjam'];
        $alamat = $_POST['alamat'];
        $tgl_daftar = $_POST['tgl_daftar'];
        $no_telpon = $_POST['no_telpon'];

        if ($action === 'add_peminjam') {
            // Menambah data peminjam
            $stmt = $pdo->prepare("INSERT INTO peminjam (nama_peminjam, alamat, tgl_daftar, no_telpon) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama_peminjam, $alamat, $tgl_daftar, $no_telpon]);
            $notif_message = "Data peminjam berhasil ditambahkan";
        } else {
            // Mengupdate data peminjam
            $stmt = $pdo->prepare("UPDATE peminjam SET nama_peminjam = ?, alamat = ?, tgl_daftar = ?, no_telpon = ? WHERE kd_peminjam = ?");
            $stmt->execute([$nama_peminjam, $alamat, $tgl_daftar, $no_telpon, $kd_peminjam]);
            $notif_message = "Data peminjam berhasil di edit";
        }
    } elseif ($action === 'delete_peminjam') {
        // Menghapus data peminjam
        $kd_peminjam = $_POST['kd_peminjam'];
        $stmt = $pdo->prepare("DELETE FROM peminjam WHERE kd_peminjam = ?");
        $stmt->execute([$kd_peminjam]);
        $notif_message = "Data peminjam berhasil dihapus";
    }
}

// Menangani aksi insert, update, dan delete untuk buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add_buku' || $action === 'update_buku') {
        // Mendapatkan data dari form buku
        $kd_buku = $_POST['kd_buku'] ?? null;
        $judul_buku = $_POST['judul_buku'];
        $nama_pengarang = $_POST['nama_pengarang'];
        $nama_penerbit = $_POST['nama_penerbit'];
        $tahun_terbit = $_POST['tahun_terbit'];
        $jumlah_buku = $_POST['jumlah_buku'];

        if ($action === 'add_buku') {
            // Menambah data buku
            $stmt = $pdo->prepare("INSERT INTO buku (judul_buku, nama_pengarang, nama_penerbit, tahun_terbit, jumlah_buku) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$judul_buku, $nama_pengarang, $nama_penerbit, $tahun_terbit, $jumlah_buku]);
            $notif_message = "Data buku berhasil ditambahkan";
        } else {
            // Mengupdate data buku
            $stmt = $pdo->prepare("UPDATE buku SET judul_buku = ?, nama_pengarang = ?, nama_penerbit = ?, tahun_terbit = ?, jumlah_buku = ? WHERE kd_buku = ?");
            $stmt->execute([$judul_buku, $nama_pengarang, $nama_penerbit, $tahun_terbit, $jumlah_buku, $kd_buku]);
            $notif_message = "Data buku berhasil di edit";
        }
    } elseif ($action === 'delete_buku') {
        // Menghapus data buku
        $kd_buku = $_POST['kd_buku'];
        $stmt = $pdo->prepare("DELETE FROM buku WHERE kd_buku = ?");
        $stmt->execute([$kd_buku]);
        $notif_message = "Data buku berhasil dihapus";
    }
}

// Menangani transaksi peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'transaksi_peminjaman') {
    // Mendapatkan data dari form transaksi
    $kd_peminjam = $_POST['kd_peminjam'];
    $tgl_kembali = $_POST['tgl_kembali'];
    $buku = $_POST['buku']; // array of kd_buku and jumlah

    // Memulai transaksi database
    $pdo->beginTransaction();

    try {
        // Menambah data transaksi peminjaman
        $stmt = $pdo->prepare("INSERT INTO transaksi_peminjaman (kd_peminjam, tgl_kembali) VALUES (?, ?)");
        $stmt->execute([$kd_peminjam, $tgl_kembali]);
        $no_transaksi = $pdo->lastInsertId();

        // Menambah detail transaksi peminjaman dan mengurangi jumlah buku
        $stmt = $pdo->prepare("INSERT INTO detail_transaksi_peminjaman (no_transaksi, kd_buku, jumlah) VALUES (?, ?, ?)");
        foreach ($buku as $item) {
            $stmt->execute([$no_transaksi, $item['kd_buku'], $item['jumlah']]);
            $stmtUpdate = $pdo->prepare("UPDATE buku SET jumlah_buku = jumlah_buku - ? WHERE kd_buku = ?");
            $stmtUpdate->execute([$item['jumlah'], $item['kd_buku']]);
        }

        // Commit transaksi database
        $pdo->commit();
        $notif_message = "Transaksi peminjaman berhasil!";
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $pdo->rollBack();
        $notif_message = "Failed: " . $e->getMessage();
    }
}

// Mengambil data peminjam dari database
$peminjam = $pdo->query("SELECT * FROM peminjam")->fetchAll(PDO::FETCH_ASSOC);
// Mengambil data buku dari database
$buku = $pdo->query("SELECT * FROM buku")->fetchAll(PDO::FETCH_ASSOC);
// Mengambil riwayat transaksi peminjaman dari database
$history = $pdo->query("
    SELECT 
        tp.no_transaksi, tp.tgl_peminjaman, tp.tgl_kembali, 
        p.nama_peminjam, 
        b.judul_buku, dt.jumlah 
    FROM transaksi_peminjaman tp
    JOIN peminjam p ON tp.kd_peminjam = p.kd_peminjam
    JOIN detail_transaksi_peminjaman dt ON tp.no_transaksi = dt.no_transaksi
    JOIN buku b ON dt.kd_buku = b.kd_buku
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">
    <h1>Aplikasi Perpustakaan</h1>
    <div class="form-container">
        <h2>Tambah/Update Peminjam</h2>
        <form id="form-peminjam" method="POST">
            <input type="hidden" name="action" value="add_peminjam">
            <input type="hidden" name="kd_peminjam" id="kd_peminjam">
            <input type="text" name="nama_peminjam" id="nama_peminjam" placeholder="Nama Peminjam" required>
            <input type="text" name="alamat" id="alamat" placeholder="Alamat" required>
            <input type="date" name="tgl_daftar" id="tgl_daftar" placeholder="Tanggal Daftar" required>
            <input type="text" name="no_telpon" id="no_telpon" placeholder="No. Telpon" required>
            <button type="submit">Simpan</button>
        </form>
    </div>
    <div>
        <h2>Daftar Peminjam</h2>
        <ul id="peminjam-list">
            <?php foreach ($peminjam as $p): ?>
            <li>
                <?= $p['nama_peminjam'] ?>
                <button onclick="editPeminjam(<?= htmlspecialchars(json_encode($p)) ?>)">Edit</button>
                <form method="POST" style="display:inline-block;" onsubmit="return confirmDeletePeminjam();">
                    <input type="hidden" name="action" value="delete_peminjam">
                    <input type="hidden" name="kd_peminjam" value="<?= $p['kd_peminjam'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="form-container">
        <h2>Tambah/Update Buku</h2>
        <form id="form-buku" method="POST">
            <input type="hidden" name="action" value="add_buku">
            <input type="hidden" name="kd_buku" id="kd_buku">
            <input type="text" name="judul_buku" id="judul_buku" placeholder="Judul Buku" required>
            <input type="text" name="nama_pengarang" id="nama_pengarang" placeholder="Nama Pengarang" required>
            <input type="text" name="nama_penerbit" id="nama_penerbit" placeholder="Nama Penerbit" required>
            <input type="number" name="tahun_terbit" id="tahun_terbit" placeholder="Tahun Terbit" required>
            <input type="number" name="jumlah_buku" id="jumlah_buku" placeholder="Jumlah Buku" required>
            <button type="submit">Simpan</button>
        </form>
    </div>
    <div>
        <h2>Daftar Buku</h2>
        <ul id="buku-list">
            <?php foreach ($buku as $b): ?>
            <li>
                <?= $b['judul_buku'] ?>
                <button onclick="editBuku(<?= htmlspecialchars(json_encode($b)) ?>)">Edit</button>
                <form method="POST" style="display:inline-block;" onsubmit="return confirmDeleteBuku();">
                    <input type="hidden" name="action" value="delete_buku">
                    <input type="hidden" name="kd_buku" value="<?= $b['kd_buku'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div>
        <h2>Transaksi Peminjaman</h2>
        <form id="form-transaksi" method="POST">
            <input type="hidden" name="action" value="transaksi_peminjaman">
            <select name="kd_peminjam" required>
                <option value="">Pilih Peminjam</option>
                <?php foreach ($peminjam as $p): ?>
                <option value="<?= $p['kd_peminjam'] ?>"><?= $p['nama_peminjam'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="tgl_kembali" required>
            <div id="buku-list">
                <select name="buku[0][kd_buku]" required>
                    <option value="">Pilih Buku</option>
                    <?php foreach ($buku as $b): ?>
                    <option value="<?= $b['kd_buku'] ?>"><?= $b['judul_buku'] ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="buku[0][jumlah]" placeholder="Jumlah" required>
            </div>
            <button type="button" id="add-buku">Tambah Buku</button>
            <button type="submit">Proses Transaksi</button>
        </form>
    </div>
    <div>
        <h2>Riwayat Transaksi</h2>
        <ul>
            <?php foreach ($history as $h): ?>
            <li>
                <?= $h['nama_peminjam'] ?> meminjam <?= $h['judul_buku'] ?> (<?= $h['jumlah'] ?>) pada tanggal <?= $h['tgl_peminjaman'] ?> dan akan kembali pada <?= $h['tgl_kembali'] ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
    // Mengisi form peminjam dengan data untuk edit
    function editPeminjam(peminjam) {
        document.getElementById('kd_peminjam').value = peminjam.kd_peminjam;
        document.getElementById('nama_peminjam').value = peminjam.nama_peminjam;
        document.getElementById('alamat').value = peminjam.alamat;
        document.getElementById('tgl_daftar').value = peminjam.tgl_daftar;
        document.getElementById('no_telpon').value = peminjam.no_telpon;
        document.getElementById('form-peminjam').action.value = 'update_peminjam';
    }

    // Mengisi form buku dengan data untuk edit
    function editBuku(buku) {
        document.getElementById('kd_buku').value = buku.kd_buku;
        document.getElementById('judul_buku').value = buku.judul_buku;
        document.getElementById('nama_pengarang').value = buku.nama_pengarang;
        document.getElementById('nama_penerbit').value = buku.nama_penerbit;
        document.getElementById('tahun_terbit').value = buku.tahun_terbit;
        document.getElementById('jumlah_buku').value = buku.jumlah_buku;
        document.getElementById('form-buku').action.value = 'update_buku';
    }

    // Konfirmasi sebelum menghapus data peminjam
    function confirmDeletePeminjam() {
        if (confirm('Apakah kamu ingin menghapus data peminjam?')) {
            alert('Data peminjam berhasil dihapus');
            return true;
        }
        return false;
    }

    // Konfirmasi sebelum menghapus data buku
    function confirmDeleteBuku() {
        if (confirm('Apakah kamu ingin menghapus data buku?')) {
            alert('Data buku berhasil dihapus');
            return true;
        }
        return false;
    }

    // Menambahkan elemen buku baru pada form transaksi peminjaman
    document.getElementById('add-buku').addEventListener('click', function () {
        const bukuList = document.getElementById('buku-list');
        const index = bukuList.children.length / 2;
        const select = document.createElement('select');
        select.name = `buku[${index}][kd_buku]`;
        select.required = true;
        <?php foreach ($buku as $b): ?>
        const option = document.createElement('option');
        option.value = '<?= $b['kd_buku'] ?>';
        option.textContent = '<?= $b['judul_buku'] ?>';
        select.appendChild(option);
        <?php endforeach; ?>
        const input = document.createElement('input');
        input.type = 'number';
        input.name = `buku[${index}][jumlah]`;
        input.placeholder = 'Jumlah';
        input.required = true;
        bukuList.appendChild(select);
        bukuList.appendChild(input);
    });

    // Menampilkan pesan notifikasi jika ada
    <?php if ($notif_message): ?>
    alert('<?= $notif_message ?>');
    <?php endif; ?>
</script>
</body>

</html>
