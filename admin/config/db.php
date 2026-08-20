<?php
// Set default timezone for the application
date_default_timezone_set('Asia/Kolkata');

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'anrf';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

require_once __DIR__ . '/approval_helper.php';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        // Fallback to default local XAMPP credentials if custom env credentials fail
        $pdo = new PDO("mysql:host=localhost;dbname=anrf;charset=utf8mb4", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        die("Connection failed: " . $e->getMessage());
    }
}