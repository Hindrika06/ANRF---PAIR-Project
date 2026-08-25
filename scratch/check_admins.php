<?php
require_once __DIR__ . '/../admin/config/db.php';
$stmt = $pdo->query("SELECT id, username, email, role, institute_prefix FROM admins");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
