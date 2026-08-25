<?php
require_once __DIR__ . '/../admin/config/db.php';
$stmt = $pdo->query("SELECT id, username, password FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$passwordsToTest = ['admin123', 'admin', 'password', 'superadmin', '123456', 'uoh123', 'cuk123'];
foreach ($users as $u) {
    foreach ($passwordsToTest as $p) {
        if (password_verify($p, $u['password']) || $u['password'] === $p) {
            echo "MATCH! Username: {$u['username']} -> Password: $p\n";
        }
    }
}
