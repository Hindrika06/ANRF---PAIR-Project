<?php
/**
 * Database Migration: Ensure All 7 University Progress Report Tables & Child Tables
 */
require_once 'c:/Temp/ANRF---PAIR-Project/config.php';

$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

foreach ($prefixes as $p) {
    $table       = "{$p}_progress_reports";
    $pubsTable   = "{$p}_progress_report_publications";
    $eventsTable = "{$p}_progress_report_capacity_events";

    try {
        // 1. Ensure progress_reports table exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `$table` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `project_title` VARCHAR(255) NOT NULL,
                `pi_name` VARCHAR(255) NOT NULL,
                `co_pi_name` VARCHAR(255) DEFAULT NULL,
                `task_no` VARCHAR(50) NOT NULL,
                `work_package_no` VARCHAR(100) DEFAULT NULL,
                `approved_objects` TEXT DEFAULT NULL,
                `methodology` TEXT DEFAULT NULL,
                `summary_progress` TEXT DEFAULT NULL,
                `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0,
                `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        // Check & Add work_package_no column
        $checkCol = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'work_package_no'");
        if ($checkCol->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `work_package_no` VARCHAR(100) NULL AFTER `task_no`");
        }

        // Check & Add interns_trained_count column
        $checkInterns = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'interns_trained_count'");
        if ($checkInterns->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`");
        }

        // 2. Ensure publications child table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `$pubsTable` (
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

        // 3. Ensure capacity building events child table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `$eventsTable` (
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

        echo "Successfully verified/created schema for institute prefix: $p\n";
    } catch (PDOException $e) {
        echo "Error setting up table for prefix $p: " . $e->getMessage() . "\n";
    }
}
