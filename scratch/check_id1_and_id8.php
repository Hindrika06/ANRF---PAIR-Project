<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=anrf;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$row1 = $pdo->query("SELECT password FROM users WHERE id = 1")->fetchColumn();
$row8 = $pdo->query("SELECT password FROM users WHERE id = 8")->fetchColumn();

echo "ID 1 (Idsathyan@cuk.ac.in):\n";
echo "  Verify 'cuk@admin123': " . (password_verify('cuk@admin123', $row1) ? "YES" : "NO") . "\n";
echo "  Verify 'admin123':     " . (password_verify('admin123', $row1) ? "YES" : "NO") . "\n";
echo "  Verify 'cuk123':       " . (password_verify('cuk123', $row1) ? "YES" : "NO") . "\n";

echo "\nID 8 (superadmin@uoh.ac.in):\n";
echo "  Verify 'superadmin@123': " . (password_verify('superadmin@123', $row8) ? "YES" : "NO") . "\n";
echo "  Verify 'admin123':       " . (password_verify('admin123', $row8) ? "YES" : "NO") . "\n";
