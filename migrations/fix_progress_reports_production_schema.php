<?php
/**
 * Safe, Idempotent Migration Script: Fix Progress Reports Production Schema
 * 
 * Synchronizes production database schema with application code requirements across all 7 university prefixes:
 * ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu']
 * 
 * Performs forward-only ALTER TABLE and CREATE TABLE IF NOT EXISTS statements.
 * Zero data loss guarantee: No tables, columns, or rows are dropped or deleted.
 */

require_once __DIR__ . '/../config.php';

echo "========================================================================\n";
echo "Starting Progress Reports Schema Migration & Verification Across All Prefixes\n";
echo "========================================================================\n";

$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
$migratedCount = 0;
$skippedCount  = 0;

try {
    foreach ($prefixes as $p) {
        $reportsTbl = "{$p}_progress_reports";
        $pubsTbl    = "{$p}_progress_report_publications";
        $eventsTbl  = "{$p}_progress_report_capacity_events";

        echo "\n[Prefix: $p]\n";

        // 1. Ensure main progress reports table exists for this prefix
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `$reportsTbl` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `project_title` VARCHAR(500) NOT NULL,
                `pi_name` VARCHAR(255) NOT NULL,
                `co_pi_name` VARCHAR(255) DEFAULT NULL,
                `task_no` VARCHAR(50) NOT NULL,
                `work_package_no` VARCHAR(100) DEFAULT NULL,
                `approved_objects` TEXT DEFAULT NULL,
                `methodology` TEXT DEFAULT NULL,
                `summary_progress` TEXT DEFAULT NULL,
                `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        // Inspect existing columns
        $existingCols = $pdo->query("SHOW COLUMNS FROM `$reportsTbl`")->fetchAll(PDO::FETCH_COLUMN);

        // Add 'work_package_no' if missing
        if (!in_array('work_package_no', $existingCols, true)) {
            $pdo->exec("ALTER TABLE `$reportsTbl` ADD COLUMN `work_package_no` VARCHAR(100) NULL AFTER `task_no`");
            echo "  + Added column 'work_package_no' to `$reportsTbl`\n";
            $migratedCount++;
        } else {
            echo "  = Column 'work_package_no' already present in `$reportsTbl`\n";
            $skippedCount++;
        }

        // Add 'interns_trained_count' if missing
        if (!in_array('interns_trained_count', $existingCols, true)) {
            $pdo->exec("ALTER TABLE `$reportsTbl` ADD COLUMN `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`");
            echo "  + Added column 'interns_trained_count' to `$reportsTbl`\n";
            $migratedCount++;
        } else {
            echo "  = Column 'interns_trained_count' already present in `$reportsTbl`\n";
            $skippedCount++;
        }

        // 2. Ensure child table: progress_report_publications
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `$pubsTbl` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `progress_report_id` INT NOT NULL,
                `task_no` VARCHAR(50) DEFAULT NULL,
                `publication_title` VARCHAR(500) NOT NULL,
                `author_name` VARCHAR(255) NOT NULL,
                `doi_number` VARCHAR(255) DEFAULT NULL,
                `publication_date` DATE DEFAULT NULL,
                `publication_journal` VARCHAR(300) NOT NULL,
                `impact_factor` DECIMAL(6,3) DEFAULT NULL,
                `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_pr_id` (`progress_report_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        echo "  = Ensured child table `$pubsTbl`\n";

        // 3. Ensure child table: progress_report_capacity_events
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `$eventsTbl` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `progress_report_id` INT NOT NULL,
                `category` ENUM('Workshop_Conference', 'Training_Program') NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `event_date` DATE DEFAULT NULL,
                `duration` VARCHAR(100) DEFAULT NULL,
                `venue_mode` VARCHAR(255) DEFAULT NULL,
                `organizing_institution` VARCHAR(255) DEFAULT NULL,
                `participant_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `description` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_pr_events_id` (`progress_report_id`),
                KEY `idx_category` (`category`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        echo "  = Ensured child table `$eventsTbl`\n";
    }

    echo "\n========================================================================\n";
    echo "MIGRATION COMPLETE SUMMARY:\n";
    echo "Columns Added: $migratedCount | Idempotently Skipped (Already Existed): $skippedCount\n";
    echo "========================================================================\n";
} catch (PDOException $e) {
    echo "\n[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
