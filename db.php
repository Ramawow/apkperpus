<?php
$host = 'localhost';
$dbname = 'perpustakaan';
$user = 'root';
$pass = '';

// Menggunakan driver PDO MySQL
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname: " . $e->getMessage());
}
?>
