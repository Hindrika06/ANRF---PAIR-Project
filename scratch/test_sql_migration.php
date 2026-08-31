<?php
require_once 'C:\Temp\ANRF---PAIR-Project\config.php';

try {
    $sql = file_get_contents('C:\Temp\ANRF---PAIR-Project\production_safe_migration.sql');
    $pdo->exec($sql);
    echo "SQL Migration executed successfully!\n";
} catch (Exception $e) {
    echo "SQL Migration error: " . $e->getMessage() . "\n";
}
