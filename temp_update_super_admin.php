<?php
$host = '127.0.0.1';
$db = 'anrf';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE username = ?');
    $stmt->execute([$hash, 'admin@uoh.ac.in']);
    $stmt = $pdo->prepare('SELECT username, role, institute_prefix FROM users WHERE username = ?');
    $stmt->execute(['admin@uoh.ac.in']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
