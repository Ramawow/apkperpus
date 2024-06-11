<?php
require 'db.php';

// INSERT Transaksi Peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}

// GET History Peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("
        SELECT 
            tp.no_transaksi, tp.tgl_peminjaman, tp.tgl_kembali, 
            p.nama_peminjam, 
            b.judul_buku, dt.jumlah 
        FROM transaksi_peminjaman tp
        JOIN peminjam p ON tp.kd_peminjam = p.kd_peminjam
        JOIN detail_transaksi_peminjaman dt ON tp.no_transaksi = dt.no_transaksi
        JOIN buku b ON dt.kd_buku = b.kd_buku
    ");
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($history);
}
?>
