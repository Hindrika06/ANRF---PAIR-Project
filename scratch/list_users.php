<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($users, JSON_PRETTY_PRINT);
