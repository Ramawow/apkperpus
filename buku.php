<?php
require 'db.php';

$books = $pdo->query("SELECT * FROM buku")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($books);
?>
