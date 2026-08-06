<?php
require_once __DIR__ . '/../config.php';
$stmt = $pdo->query("SELECT id, username, password, role FROM users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
