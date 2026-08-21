<?php
/**
 * migrations/create_website_visitors_table.php
 * Migration script to create the website_visitors table for unique IP visitor tracking.
 */
require_once __DIR__ . '/../config.php';

try {
    echo "Starting website_visitors table creation migration...\n";

    $sql = "CREATE TABLE IF NOT EXISTS `website_visitors` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `ip_address` VARCHAR(45) NOT NULL UNIQUE,
        `first_visit_at` DATETIME NOT NULL,
        `last_visit_at` DATETIME NOT NULL,
        `visit_count` INT NOT NULL DEFAULT 1,
        INDEX `idx_ip` (`ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);

    echo "Table `website_visitors` created or already exists.\n";
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
