<?php
require_once 'c:/Temp/ANRF---PAIR-Project/admin/config/db.php';

$stmt = $pdo->query("SELECT id, username, password, institute_prefix, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$passwords = ['admin123', 'admin', 'superadmin', '123456', 'password'];

foreach ($users as $u) {
    echo "User ID: {$u['id']} | Username: {$u['username']} | Role: {$u['role']} | Prefix: {$u['institute_prefix']}\n";
    foreach ($passwords as $p) {
        if (password_verify($p, $u['password']) || $p === $u['password']) {
            echo "  --> MATCHED PASSWORD: '$p'\n";
        }
    }
}
