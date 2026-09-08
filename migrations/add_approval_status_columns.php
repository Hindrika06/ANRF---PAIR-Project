<?php
require_once __DIR__ . '/../config.php';

echo "=== MIGRATION: ADD approval_status COLUMN TO ALL KPI TABLES ===\n\n";

try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $addedCount = 0;
    $alreadyCount = 0;

    foreach ($tables as $t) {
        // Skip system/meta tables
        if (in_array($t, ['users', 'approval_requests', 'website_visitors'], true)) {
            continue;
        }

        $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('approval_status', $cols, true)) {
            $alterSql = "ALTER TABLE `$t` ADD COLUMN `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved'";
            $pdo->exec($alterSql);
            echo "[ADDED] Table '$t' -> approval_status column created.\n";
            $addedCount++;
        } else {
            echo "[OK] Table '$t' already has approval_status.\n";
            $alreadyCount++;
        }
    }

    echo "\n=== MIGRATION SUCCESSFUL! ===\n";
    echo "Added to $addedCount tables. Already existed in $alreadyCount tables.\n";

} catch (Exception $e) {
    echo "\n[ERROR] Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}
