<?php
require_once __DIR__ . '/../config.php';
$stmt = $pdo->query("SELECT id, username, role, institute_prefix FROM users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
