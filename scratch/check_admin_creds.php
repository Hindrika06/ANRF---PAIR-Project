<?php
require_once __DIR__ . '/../admin/config/db.php';

$stmt = $pdo->query("SELECT id, username, password, role, institute_prefix FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "DATABASE USERS LIST:\n";
foreach ($users as $u) {
    echo sprintf("ID: %d | User: %s | Role: %s | Prefix: %s | Hash: %s\n", 
        $u['id'], $u['username'], $u['role'], $u['institute_prefix'], substr($u['password'], 0, 15) . '...');
}
