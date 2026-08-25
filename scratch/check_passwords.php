<?php
require_once __DIR__ . '/../admin/config/db.php';
$stmt = $pdo->query("SELECT id, username, password, role, institute_prefix FROM users WHERE username IN ('superadmin@uoh.ac.in', 'superadmin', 'admin@uoh.ac.in')");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "Username: {$u['username']} | Role: {$u['role']} | Password hash/text: {$u['password']}\n";
    echo "Verify 'admin123': " . (password_verify('admin123', $u['password']) || $u['password'] === 'admin123' ? "MATCH" : "NO MATCH") . "\n";
}
