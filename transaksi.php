<?php
require 'db.php';

$transactions = $pdo->query("
    SELECT 
        tp.no_transaksi, tp.tgl_peminjaman, tp.tgl_kembali, 
        p.nama_peminjam, 
        b.judul_buku, dt.jumlah 
    FROM transaksi_peminjaman tp
    JOIN peminjam p ON tp.kd_peminjam = p.kd_peminjam
    JOIN detail_transaksi_peminjaman dt ON tp.no_transaksi = dt.no_transaksi
    JOIN buku b ON dt.kd_buku = b.kd_buku
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($transactions);
?>
