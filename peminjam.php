<?php
require 'db.php';

// INSERT Peminjam
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_peminjam = $_POST['nama_peminjam'];
    $alamat = $_POST['alamat'];
    $no_telpon = $_POST['no_telpon'];

    $stmt = $pdo->prepare("INSERT INTO peminjam (nama_peminjam, alamat, no_telpon) VALUES (?, ?, ?)");
    $stmt->execute([$nama_peminjam, $alamat, $no_telpon]);

    echo "Peminjam added successfully!";
}

// UPDATE Peminjam
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $kd_peminjam = $_PUT['kd_peminjam'];
    $nama_peminjam = $_PUT['nama_peminjam'];
    $alamat = $_PUT['alamat'];
    $no_telpon = $_PUT['no_telpon'];

    $stmt = $pdo->prepare("UPDATE peminjam SET nama_peminjam = ?, alamat = ?, no_telpon = ? WHERE kd_peminjam = ?");
    $stmt->execute([$nama_peminjam, $alamat, $no_telpon, $kd_peminjam]);

    echo "Peminjam updated successfully!";
}

// DELETE Peminjam
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $kd_peminjam = $_DELETE['kd_peminjam'];

    $stmt = $pdo->prepare("DELETE FROM peminjam WHERE kd_peminjam = ?");
    $stmt->execute([$kd_peminjam]);

    echo "Peminjam deleted successfully!";
}

// GET All Peminjam
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM peminjam");
    $stmt->execute();
    $peminjam = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($peminjam);
}
?>
