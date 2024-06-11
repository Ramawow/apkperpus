<?php
require 'db.php';

// INSERT Buku
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_buku = $_POST['judul_buku'];
    $nama_pengarang = $_POST['nama_pengarang'];
    $nama_penerbit = $_POST['nama_penerbit'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $jumlah_buku = $_POST['jumlah_buku'];

    $stmt = $pdo->prepare("INSERT INTO buku (judul_buku, nama_pengarang, nama_penerbit, tahun_terbit, jumlah_buku) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$judul_buku, $nama_pengarang, $nama_penerbit, $tahun_terbit, $jumlah_buku]);

    echo "Buku added successfully!";
}

// UPDATE Buku
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $kd_buku = $_PUT['kd_buku'];
    $judul_buku = $_PUT['judul_buku'];
    $nama_pengarang = $_PUT['nama_pengarang'];
    $nama_penerbit = $_PUT['nama_penerbit'];
    $tahun_terbit = $_PUT['tahun_terbit'];
    $jumlah_buku = $_PUT['jumlah_buku'];

    $stmt = $pdo->prepare("UPDATE buku SET judul_buku = ?, nama_pengarang = ?, nama_penerbit = ?, tahun_terbit = ?, jumlah_buku = ? WHERE kd_buku = ?");
    $stmt->execute([$judul_buku, $nama_pengarang, $nama_penerbit, $tahun_terbit, $jumlah_buku, $kd_buku]);

    echo "Buku updated successfully!";
}

// DELETE Buku
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $kd_buku = $_DELETE['kd_buku'];

    $stmt = $pdo->prepare("DELETE FROM buku WHERE kd_buku = ?");
    $stmt->execute([$kd_buku]);

    echo "Buku deleted successfully!";
}

// GET All Buku
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM buku");
    $stmt->execute();
    $buku = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($buku);
}
?>
