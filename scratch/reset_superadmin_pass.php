<?php
require_once __DIR__ . '/../admin/config/db.php';

$hash = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE username = 'superadmin' OR username = 'superadmin@uoh.ac.in'");
$stmt->execute([':pass' => $hash]);

echo "SUCCESS: Updated superadmin & superadmin@uoh.ac.in password to 'admin123'\n";
