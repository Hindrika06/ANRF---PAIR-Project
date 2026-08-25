<?php
require_once __DIR__ . '/../config.php';
try {
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "DB Connection Successful! Total Tables: " . count($tables) . "\n";
    foreach ($tables as $t) {
        if (strpos($t, 'banner') !== false || strpos($t, 'announc') !== false || strpos($t, 'gallery') !== false || strpos($t, 'event') !== false || strpos($t, 'team') !== false) {
            echo " - $t\n";
        }
    }
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
