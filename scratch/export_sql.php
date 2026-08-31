<?php
require_once __DIR__ . '/../config.php';

$users = $pdo->query("SELECT username, password, institute_prefix, role FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$sqlLines = ["-- SQL script to set exact requested credentials"];
foreach ($users as $u) {
    $usr = addslashes($u['username']);
    $pwd = addslashes($u['password']);
    $pfx = addslashes($u['institute_prefix']);
    $role = addslashes($u['role']);
    $sqlLines[] = "UPDATE `users` SET `password` = '$pwd', `role` = '$role', `institute_prefix` = '$pfx' WHERE `username` = '$usr';";
}

file_put_contents(__DIR__ . '/../update_passwords.sql', implode("\n", $sqlLines) . "\n");
echo "Updated update_passwords.sql successfully!\n";
