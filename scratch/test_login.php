<?php
require_once __DIR__ . '/../config.php';
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'superadmin@uoh.ac.in'");
$stmt->execute();
$u = $stmt->fetch();
echo "superadmin password match 'admin123': " . (password_verify('admin123', $u['password']) ? 'YES' : 'NO') . "\n";
