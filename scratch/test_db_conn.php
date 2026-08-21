<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "SUCCESS: Connected to 127.0.0.1:3307!\n";
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases: " . implode(', ', $dbs) . "\n";
    
    if (in_array('anrf', $dbs)) {
        echo "Database 'anrf' exists!\n";
        $pdo->query("USE anrf");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables in 'anrf': " . count($tables) . " tables found.\n";
    } else {
        echo "Database 'anrf' does NOT exist yet.\n";
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
