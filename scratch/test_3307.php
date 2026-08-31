<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=anrf", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "SUCCESS! Connected to XAMPP MySQL on port 3307!\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in 'anrf' (" . count($tables) . "): " . implode(', ', array_slice($tables, 0, 10)) . "...\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
