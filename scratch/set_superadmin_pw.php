<?php
require_once __DIR__ . '/../admin/config/db.php';
$hash = password_hash('admin123', PASSWORD_BCRYPT);
$pdo->exec("UPDATE users SET password='$hash' WHERE username IN ('superadmin', 'superadmin@uoh.ac.in')");
echo "Superadmin password updated to 'admin123'\n";
