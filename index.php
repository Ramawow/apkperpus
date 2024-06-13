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
            $notif_message = "Data peminjam berhasil diedit";
        }
    } elseif ($action === 'delete_peminjam') {
        // Menghapus data peminjam
        // $kd_peminjam = $_POST['kd_peminjam'];
        // $stmt = $pdo->prepare("DELETE FROM detail_transaksi_peminjaman WHERE kd_peminjam = ?");
        // $stmt->execute([$kd_peminjam]);
        
        // $stmt = $pdo->prepare("DELETE FROM peminjam WHERE kd_peminjam = ?");
        // $stmt->execute([$kd_peminjam]);
        // $notif_message = "Data peminjam berhasil dihapus";

        // Delete Peminjam
        $kd_peminjam = $_POST['kd_peminjam'];
    try {
        // Cek apakah masih ada transaksi terkait yang belum selesai
        $stmt_check_trans = $pdo->prepare("SELECT COUNT(*) FROM transaksi_peminjaman WHERE kd_peminjam = ? AND tgl_kembali > NOW()");
        $stmt_check_trans->execute([$kd_peminjam]);
        $trans_count = $stmt_check_trans->fetchColumn();

        if ($trans_count > 0) {
            throw new Exception('Masih ada transaksi terkait dengan peminjam ini yang belum selesai.');
        }

        // Hapus detail transaksi terkait
        $stmt_delete_detail = $pdo->prepare("DELETE FROM detail_transaksi_peminjaman WHERE no_transaksi IN (SELECT no_transaksi FROM transaksi_peminjaman WHERE kd_peminjam = ?)");
        $stmt_delete_detail->execute([$kd_peminjam]);

        // Hapus transaksi terkait
        $stmt_delete_trans = $pdo->prepare("DELETE FROM transaksi_peminjaman WHERE kd_peminjam = ?");
        $stmt_delete_trans->execute([$kd_peminjam]);

        // Hapus peminjam
        $stmt_delete_peminjam = $pdo->prepare("DELETE FROM peminjam WHERE kd_peminjam = ?");
        $stmt_delete_peminjam->execute([$kd_peminjam]);

        $notif_message = 'Data peminjam berhasil dihapus';
    } catch (Exception $e) {
        $notif_message = 'Gagal menghapus data peminjam: ' . $e->getMessage();
        }
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
            $notif_message = "Data buku berhasil diedit";
        }
    } elseif ($action === 'delete_buku') {
        // Menghapus data buku
        // $kd_buku = $_POST['kd_buku'];
        // $stmt = $pdo->prepare("DELETE FROM detail_transaksi_peminjaman WHERE kd_buku = ?");
        // $stmt->execute([$kd_buku]);
        
        // $stmt = $pdo->prepare("DELETE FROM buku WHERE kd_buku = ?");
        // $stmt->execute([$kd_buku]);
        // $notif_message = "Data buku berhasil dihapus";
        $kd_buku = $_POST['kd_buku'];
        try {
            // Cek apakah masih ada transaksi terkait yang belum selesai
            $stmt_check_trans = $pdo->prepare("
                SELECT COUNT(*) 
                FROM detail_transaksi_peminjaman d 
                JOIN transaksi_peminjaman t 
                ON d.no_transaksi = t.no_transaksi 
                WHERE d.kd_buku = ? 
                AND t.tgl_kembali > NOW()
            ");
            $stmt_check_trans->execute([$kd_buku]);
            $trans_count = $stmt_check_trans->fetchColumn();
    
            if ($trans_count > 0) {
                throw new Exception('Masih ada transaksi terkait dengan buku ini yang belum selesai.');
            }
    
            // Hapus detail transaksi peminjaman terkait jika ada
            $stmt_delete_detail = $pdo->prepare("DELETE FROM detail_transaksi_peminjaman WHERE kd_buku = ?");
            $stmt_delete_detail->execute([$kd_buku]);
    
            // Jika tidak ada transaksi terkait, hapus buku
            $stmt_delete_buku = $pdo->prepare("DELETE FROM buku WHERE kd_buku = ?");
            $stmt_delete_buku->execute([$kd_buku]);
    
            $notif_message = 'Data buku berhasil dihapus';
        } catch (Exception $e) {
            $notif_message = 'Gagal menghapus data buku: ' . $e->getMessage();
        }

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
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container">
    <h1 class="text-center mt-4">Aplikasi Perpustakaan</h1>

    <?php if ($notif_message): ?> 
    <div class="alert alert-info text-center">
        <?= $notif_message ?>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Tambah/Update Peminjam</h2>
        </div>
        <div class="card-body">
            <form id="form-peminjam" method="POST">
                <input type="hidden" name="action" value="add_peminjam" id="action_peminjam">
                <input type="hidden" name="kd_peminjam" id="kd_peminjam">
                <div class="form-group">
                    <label for="nama_peminjam">Nama Peminjam</label>
                    <input type="text" name="nama_peminjam" id="nama_peminjam" class="form-control" placeholder="Nama Peminjam" required>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" name="alamat" id="alamat" class="form-control" placeholder="Alamat" required>
                </div>
                <div class="form-group">
                    <label for="tgl_daftar">Tanggal Daftar</label>
                    <input type="date" name="tgl_daftar" id="tgl_daftar" class="form-control" placeholder="Tanggal Daftar" required>
                </div>
                <div class="form-group">
                    <label for="no_telpon">No. Telpon</label>
                    <input type="text" name="no_telpon" id="no_telpon" class="form-control" placeholder="No. Telpon" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Daftar Peminjam</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Tanggal Daftar</th>
                        <th>No. Telpon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peminjam as $p): ?>
                        <tr>
                            <td><?= $p['nama_peminjam'] ?></td>
                            <td><?= $p['alamat'] ?></td>
                            <td><?= $p['tgl_daftar'] ?></td>
                            <td><?= $p['no_telpon'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-peminjam" data-peminjam='<?= json_encode($p) ?>'>Edit</button>
                                <form method="POST" class="d-inline" onsubmit="return confirmDeletePeminjam();">
                                    <input type="hidden" name="action" value="delete_peminjam">
                                    <input type="hidden" name="kd_peminjam" value="<?= $p['kd_peminjam'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Tambah/Update Buku</h2>
        </div>
        <div class="card-body">
            <form id="form-buku" method="POST">
                <input type="hidden" name="action" value="add_buku" id="action_buku">
                <input type="hidden" name="kd_buku" id="kd_buku">
                <div class="form-group">
                    <label for="judul_buku">Judul Buku</label>
                    <input type="text" name="judul_buku" id="judul_buku" class="form-control" placeholder="Judul Buku" required>
                </div>
                <div class="form-group">
                    <label for="nama_pengarang">Nama Pengarang</label>
                    <input type="text" name="nama_pengarang" id="nama_pengarang" class="form-control" placeholder="Nama Pengarang" required>
                </div>
                <div class="form-group">
                    <label for="nama_penerbit">Nama Penerbit</label>
                    <input type="text" name="nama_penerbit" id="nama_penerbit" class="form-control" placeholder="Nama Penerbit" required>
                </div>
                <div class="form-group">
                    <label for="tahun_terbit">Tahun Terbit</label>
                    <input type="text" name="tahun_terbit" id="tahun_terbit" class="form-control" placeholder="Tahun Terbit" required>
                </div>
                <div class="form-group">
                    <label for="jumlah_buku">Jumlah Buku</label>
                    <input type="text" name="jumlah_buku" id="jumlah_buku" class="form-control" placeholder="Jumlah Buku" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Daftar Buku</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Judul Buku</th>
                        <th>Nama Pengarang</th>
                        <th>Nama Penerbit</th>
                        <th>Tahun Terbit</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($buku as $b): ?>
                        <tr>
                            <td><?= $b['judul_buku'] ?></td>
                            <td><?= $b['nama_pengarang'] ?></td>
                            <td><?= $b['nama_penerbit'] ?></td>
                            <td><?= $b['tahun_terbit'] ?></td>
                            <td><?= $b['jumlah_buku'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-buku" data-buku='<?= json_encode($b) ?>'>Edit</button>
                                <form method="POST" class="d-inline" onsubmit="return confirmDeleteBuku('<?= $b['kd_buku'] ?>');">
                                    <input type="hidden" name="action" value="delete_buku">
                                    <input type="hidden" name="kd_buku" value="<?= $b['kd_buku'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Transaksi Peminjaman</h2>
        </div>
        <div class="card-body">
            <form id="form-transaksi" method="POST">
                <input type="hidden" name="action" value="transaksi_peminjaman">
                <div class="form-group">
                    <label for="kd_peminjam">Nama Peminjam</label>
                    <select name="kd_peminjam" id="kd_peminjam_transaksi" class="form-control" required>
                        <option value="">Pilih Peminjam</option>
                        <?php foreach ($peminjam as $p) : ?>
                            <option value="<?= $p['kd_peminjam'] ?>"><?= $p['nama_peminjam'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tgl_kembali">Tanggal Kembali</label>
                    <input type="date" name="tgl_kembali" id="tgl_kembali" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="buku">Buku yang Dipinjam</label>
                    <div id="buku-container">
                        <div class="buku-item form-row align-items-end">
                            <div class="col">
                                <select name="buku[0][kd_buku]" class="form-control" required>
                                    <option value="">Pilih Buku</option>
                                    <?php foreach ($buku as $b) : ?>
                                        <option value="<?= $b['kd_buku'] ?>"><?= $b['judul_buku'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col">
                                <input type="number" name="buku[0][jumlah]" class="form-control" placeholder="Jumlah" required>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-success add-book">+</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>


    <div class="card mb-4">
        <div class="card-header">
            <h2>Riwayat Peminjaman</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No Transaksi</th>
                        <th>Tanggal Peminjaman</th>
                        <th>Tanggal Kembali</th>
                        <th>Nama Peminjam</th>
                        <th>Judul Buku</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?= $h['no_transaksi'] ?></td>
                            <td><?= $h['tgl_peminjaman'] ?></td>
                            <td><?= $h['tgl_kembali'] ?></td>
                            <td><?= $h['nama_peminjam'] ?></td>
                            <td><?= $h['judul_buku'] ?></td>
                            <td><?= $h['jumlah'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    // Event untuk tombol edit peminjam
    $('.edit-peminjam').on('click', function() {
        var data = $(this).data('peminjam');
        $('#action_peminjam').val('update_peminjam');
        $('#kd_peminjam').val(data.kd_peminjam);
        $('#nama_peminjam').val(data.nama_peminjam);
        $('#alamat').val(data.alamat);
        $('#tgl_daftar').val(data.tgl_daftar);
        $('#no_telpon').val(data.no_telpon);
    });

    // Event untuk tombol edit buku
    $('.edit-buku').on('click', function() {
        var data = $(this).data('buku');
        $('#action_buku').val('update_buku');
        $('#kd_buku').val(data.kd_buku);
        $('#judul_buku').val(data.judul_buku);
        $('#nama_pengarang').val(data.nama_pengarang);
        $('#nama_penerbit').val(data.nama_penerbit);
        $('#tahun_terbit').val(data.tahun_terbit);
        $('#jumlah_buku').val(data.jumlah_buku);
    });

    // Event untuk tombol delete dengan konfirmasi untuk peminjam
    function confirmDeletePeminjam(kd_peminjam) {
        var confirmation = confirm("Apakah Anda yakin ingin menghapus peminjam ini?");
        if (confirmation) {
            var form = document.createElement('form');
            form.method = 'POST';
            var actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_peminjam';
            form.appendChild(actionInput);
            var idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'kd_peminjam';
            idInput.value = kd_peminjam;
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        } else {
            // Jika tombol "Batal" ditekan, tidak ada tindakan yang diambil
            console.log('Penghapusan peminjam dibatalkan.');
        }
    }

    // Event untuk tombol delete dengan konfirmasi untuk buku
    function confirmDeleteBuku(kd_buku) {
        var confirmation = confirm("Apakah Anda yakin ingin menghapus buku ini?");
        if (confirmation) {
            var form = document.createElement('form');
            form.method = 'POST';
            var actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_buku';
            form.appendChild(actionInput);
            var idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'kd_buku';
            idInput.value = kd_buku;
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        } else {
            // Jika tombol "Batal" ditekan, tidak ada tindakan yang diambil
            console.log('Penghapusan buku dibatalkan.');
        }
    }

    // Event untuk tombol delete dengan konfirmasi untuk peminjam di halaman index.php
    $(document).on('submit', 'form[action="delete_peminjam"]', function(e) {
        e.preventDefault();
        var confirmation = confirm("Apakah Anda yakin ingin menghapus peminjam ini?");
        if (confirmation) {
            $(this).unbind('submit').submit();
        } else {
            console.log('Penghapusan peminjam dibatalkan.');
        }
    });

    // Event untuk tombol delete dengan konfirmasi untuk buku di halaman index.php
    $(document).on('submit', 'form[action="delete_buku"]', function(e) {
        e.preventDefault();
        var confirmation = confirm("Apakah Anda yakin ingin menghapus buku ini?");
        if (confirmation) {
            $(this).unbind('submit').submit();
        } else {
            console.log('Penghapusan buku dibatalkan.');
        }
    });



    // Event untuk tombol tambah buku pada form transaksi
     // Tambah baris buku pada transaksi
    $(document).on('click', '.add-book', function() {
        var index = $('#buku-container .buku-item').length;
        var newBookItem = `
        <div class="buku-item form-row align-items-end">
            <div class="col">
                <select name="buku[${index}][kd_buku]" class="form-control" required>
                    <option value="">Pilih Buku</option>
                    <?php foreach ($buku as $b) : ?>
                        <option value="<?= $b['kd_buku'] ?>"><?= $b['judul_buku'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <input type="number" name="buku[${index}][jumlah]" class="form-control" placeholder="Jumlah" required>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-danger remove-book">-</button>
            </div>
        </div>`;
        $('#buku-container').append(newBookItem);
    });

    // Hapus baris buku pada transaksi
    $(document).on('click', '.remove-book', function() {
        $(this).closest('.buku-item').remove();
    });
});
</script>

</body>
</html>
