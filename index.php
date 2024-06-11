<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_peminjam':
                $nama_peminjam = $_POST['nama_peminjam'];
                $alamat = $_POST['alamat'];
                $no_telpon = $_POST['no_telpon'];

                $stmt = $pdo->prepare("INSERT INTO peminjam (nama_peminjam, alamat, no_telpon) VALUES (?, ?, ?)");
                $stmt->execute([$nama_peminjam, $alamat, $no_telpon]);

                echo "Peminjam added successfully!";
                break;

            case 'add_buku':
                $judul_buku = $_POST['judul_buku'];
                $nama_pengarang = $_POST['nama_pengarang'];
                $nama_penerbit = $_POST['nama_penerbit'];
                $tahun_terbit = $_POST['tahun_terbit'];
                $jumlah_buku = $_POST['jumlah_buku'];

                $stmt = $pdo->prepare("INSERT INTO buku (judul_buku, nama_pengarang, nama_penerbit, tahun_terbit, jumlah_buku) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$judul_buku, $nama_pengarang, $nama_penerbit, $tahun_terbit, $jumlah_buku]);

                echo "Buku added successfully!";
                break;

            case 'transaksi_peminjaman':
                $kd_peminjam = $_POST['kd_peminjam'];
                $tgl_kembali = $_POST['tgl_kembali'];
                $buku = $_POST['buku']; // array of kd_buku and jumlah

                $pdo->beginTransaction();

                try {
                    $stmt = $pdo->prepare("INSERT INTO transaksi_peminjaman (kd_peminjam, tgl_kembali) VALUES (?, ?)");
                    $stmt->execute([$kd_peminjam, $tgl_kembali]);
                    $no_transaksi = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO detail_transaksi_peminjaman (no_transaksi, kd_buku, jumlah) VALUES (?, ?, ?)");

                    foreach ($buku as $item) {
                        $stmt->execute([$no_transaksi, $item['kd_buku'], $item['jumlah']]);
                        $stmtUpdate = $pdo->prepare("UPDATE buku SET jumlah_buku = jumlah_buku - ? WHERE kd_buku = ?");
                        $stmtUpdate->execute([$item['jumlah'], $item['kd_buku']]);
                    }

                    $pdo->commit();
                    echo "Transaksi peminjaman berhasil!";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo "Failed: " . $e->getMessage();
                }
                break;
        }
    }
}

$peminjam = $pdo->query("SELECT * FROM peminjam")->fetchAll(PDO::FETCH_ASSOC);
$buku = $pdo->query("SELECT * FROM buku")->fetchAll(PDO::FETCH_ASSOC);
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
        <h2>Tambah Peminjam</h2>
        <form id="form-peminjam" method="POST">
            <input type="hidden" name="action" value="add_peminjam">
            <input type="text" name="nama_peminjam" placeholder="Nama Peminjam" required>
            <input type="text" name="alamat" placeholder="Alamat" required>
            <input type="text" name="no_telpon" placeholder="No. Telpon" required>
            <button type="submit">Tambah</button>
        </form>
    </div>
    <div>
        <h2>Daftar Peminjam</h2>
        <ul>
            <?php foreach ($peminjam as $p): ?>
            <li>
                <?= $p['nama_peminjam'] ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div>
        <h2>Tambah Buku</h2>
        <form id="form-buku" method="POST">
            <input type="hidden" name="action" value="add_buku">
            <input type="text" name="judul_buku" placeholder="Judul Buku" required>
            <input type="text" name="nama_pengarang" placeholder="Nama Pengarang" required>
            <input type="text" name="nama_penerbit" placeholder="Nama Penerbit" required>
            <input type="number" name="tahun_terbit" placeholder="Tahun Terbit" required>
            <input type="number" name="jumlah_buku" placeholder="Jumlah Buku" required>
            <button type="submit">Tambah</button>
        </form>
    </div>
    <div>
        <h2>Daftar Buku</h2>
        <ul>
            <?php foreach ($buku as $b): ?>
            <li>
                <?= $b['judul_buku'] ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div>
        <h2>Transaksi Peminjaman</h2>
        <form id="form-transaksi" method="POST">
            <input type="hidden" name="action" value="transaksi_peminjaman">
            <select name="kd_peminjam" required>
                <?php foreach ($peminjam as $p): ?>
                <option value="<?= $p['kd_peminjam'] ?>">
                    <?= $p['nama_peminjam'] ?>
                </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="tgl_kembali" placeholder="Tanggal Kembali" required>
            <div id="buku-list">
                <select name="buku[0][kd_buku]" required>
                    <?php foreach ($buku as $b): ?>
                    <option value="<?= $b['kd_buku'] ?>">
                        <?= $b['judul_buku'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="buku[0][jumlah]" placeholder="Jumlah" required>
            </div>
            <button type="button" id="add-buku">Tambah Buku</button>
            <button type="submit">Pinjam</button>
        </form>
    </div>
    <div>
        <h2>History Peminjaman</h2>
        <ul>
            <?php foreach ($history as $h): ?>
            <li>
                <?= $h['nama_peminjam'] ?> meminjam
                <?= $h['judul_buku'] ?> (
                <?= $h['jumlah'] ?> buku) pada
                <?= $h['tgl_peminjaman'] ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="app.js"></script>
    <script>
        $(document).ready(function () {
            $('#add-buku').click(function () {
                var count = $('#buku-list select').length;
                var newBuku = `
                    <div>
                        <select name="buku[${count}][kd_buku]" required>
                            <?php foreach ($buku as $b): ?>
                                <option value="<?= $b['kd_buku'] ?>"><?= $b['judul_buku'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="buku[${count}][jumlah]" placeholder="Jumlah" required>
                    </div>
                `;
                $('#buku-list').append(newBuku);
            });
        });
    </script>
</body>

</html>