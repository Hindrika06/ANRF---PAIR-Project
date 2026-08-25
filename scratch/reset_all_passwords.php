<?php
require_once __DIR__ . '/../config.php';

$newHash = password_hash('admin123', PASSWORD_BCRYPT);
$pdo->exec("UPDATE users SET password = '$newHash'");

echo "========================================================\n";
echo " ALL USER PASSWORDS UPDATED SUCCESSFULLY TO: admin123  \n";
echo "========================================================\n";
