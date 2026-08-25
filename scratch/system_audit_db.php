<?php
/**
 * System-Wide Full Audit Script for Hub Admin & Public Pages CRUD
 */
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../admin/config/db.php';

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "=== DATABASE TABLES IN SYSTEM (" . count($tables) . ") ===\n";
foreach ($tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo str_pad($t, 35) . ": $count rows\n";
    } catch (Exception $e) {
        echo str_pad($t, 35) . ": ERROR (" . $e->getMessage() . ")\n";
    }
}
