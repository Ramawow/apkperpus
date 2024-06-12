<?php
require 'db.php';

$borrowers = $pdo->query("SELECT * FROM peminjam")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($borrowers);
?>
