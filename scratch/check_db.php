<?php
require_once __DIR__ . '/../config.php';

echo "=== USERS TABLE COLUMNS & DATA ===\n";
$stmt = $pdo->query("SELECT * FROM users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
