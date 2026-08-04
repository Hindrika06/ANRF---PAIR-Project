<?php
/**
 * Safe One-Time Database Migration Script
 * Adds `approval_status` column to all KPI tables without losing existing data.
 * Existing records are updated/defaulted to 'Approved'.
 */

require_once __DIR__ . '/../config.php';

$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

$kpiTableSuffixes = [
    'conferences',
    'webinars',
    'publications',
    'patent',
    'internships',
    'progress_reports'
];

$singleTables = [
    'collaborations',
    'infrastructure_facilities'
];

$allTables = [];

foreach ($prefixes as $p) {
    foreach ($kpiTableSuffixes as $s) {
        $allTables[] = "{$p}_{$s}";
    }
}

foreach ($singleTables as $st) {
    $allTables[] = $st;
}

echo "=== STARTING SAFE MIGRATION ===\n";

foreach ($allTables as $table) {
    try {
        // Check if table exists
        $check = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount();
        if ($check === 0) {
            echo "Table `$table` does not exist. Skipping.\n";
            continue;
        }

        // Check if column approval_status exists
        $cols = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('approval_status', $cols, true)) {
            echo "Adding `approval_status` to `$table`...\n";
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved'");
            echo "  Successfully added `approval_status` to `$table`.\n";
        } else {
            echo "Column `approval_status` already exists in `$table`.\n";
        }

        // Ensure any NULL or legacy records are explicitly set to 'Approved'
        $updated = $pdo->exec("UPDATE `$table` SET `approval_status` = 'Approved' WHERE `approval_status` IS NULL OR `approval_status` = ''");
        if ($updated > 0) {
            echo "  Updated $updated legacy records in `$table` to 'Approved'.\n";
        }
    } catch (Exception $e) {
        echo "Error processing table `$table`: " . $e->getMessage() . "\n";
    }
}

echo "=== MIGRATION COMPLETE ===\n";
