<?php
/**
 * Migration Script: Upgrade Progress Reports Schema across all Institute Prefixes
 * - Adds column `interns_trained_count` to `{prefix}_progress_reports`
 * - Creates `{prefix}_progress_report_publications`
 * - Creates `{prefix}_progress_report_capacity_events`
 */

require_once __DIR__ . '/../config.php';

try {
    echo "Starting Progress Reports schema migration...\n";

    $prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

    foreach ($prefixes as $p) {
        $reportsTbl = "{$p}_progress_reports";
        $pubsTbl    = "{$p}_progress_report_publications";
        $eventsTbl  = "{$p}_progress_report_capacity_events";

        // 1. Ensure main progress reports table exists for this prefix
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `$reportsTbl` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `project_title` VARCHAR(255) NOT NULL,
                `pi_name` VARCHAR(255) NOT NULL,
                `co_pi_name` VARCHAR(255) DEFAULT NULL,
                `task_no` VARCHAR(50) NOT NULL,
                `work_package_no` VARCHAR(100) DEFAULT NULL,
                `approved_objects` TEXT DEFAULT NULL,
                `methodology` TEXT DEFAULT NULL,
                `summary_progress` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        // 2. Add 'interns_trained_count' column if missing
        $cols = $pdo->query("SHOW COLUMNS FROM `$reportsTbl`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('interns_trained_count', $cols, true)) {
            $pdo->exec("ALTER TABLE `$reportsTbl` ADD COLUMN `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`");
            echo "Added column 'interns_trained_count' to $reportsTbl\n";
        }

        // 3. Create progress report publications child table
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

        // 4. Create capacity building events child table (Workshops, Conferences, Training Programs)
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
    }

    echo "Progress Reports schema migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
