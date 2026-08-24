<?php
/**
 * Migration Script: Upgrade homepage_banners table schema
 * Adds title, short_description, target_url, start_datetime, end_datetime, and indexes.
 */

require_once __DIR__ . '/../config.php';

try {
    echo "Starting database migration for homepage_banners...\n";

    // 1. Create homepage_banners table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `homepage_banners` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `image_path` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(500) DEFAULT '',
            `display_order` INT NOT NULL DEFAULT 10,
            `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // Fetch existing columns
    $stmt = $pdo->query("SHOW COLUMNS FROM `homepage_banners`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Add 'title' column if missing
    if (!in_array('title', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `id`");
        // Populate title from caption if caption exists
        $pdo->exec("UPDATE `homepage_banners` SET `title` = `caption` WHERE `title` = '' AND `caption` IS NOT NULL AND `caption` != ''");
        echo "Added column 'title'\n";
    }

    // 3. Add 'short_description' column if missing
    if (!in_array('short_description', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `short_description` TEXT DEFAULT NULL AFTER `title`");
        echo "Added column 'short_description'\n";
    }

    // 4. Add 'target_url' column if missing
    if (!in_array('target_url', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `target_url` VARCHAR(1000) DEFAULT '' AFTER `short_description`");
        echo "Added column 'target_url'\n";
    }

    // 5. Add 'start_datetime' column if missing
    if (!in_array('start_datetime', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `start_datetime` DATETIME DEFAULT NULL AFTER `target_url`");
        echo "Added column 'start_datetime'\n";
    }

    // 6. Add 'end_datetime' column if missing
    if (!in_array('end_datetime', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `end_datetime` DATETIME DEFAULT NULL AFTER `start_datetime`");
        echo "Added column 'end_datetime'\n";
    }

    // 6b. Add 'institute_prefix' column if missing
    if (!in_array('institute_prefix', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all' AFTER `end_datetime`");
        $pdo->exec("ALTER TABLE `homepage_banners` ADD INDEX `idx_inst_prefix` (`institute_prefix`)");
        echo "Added column 'institute_prefix'\n";
    }

    // 7. Add indexes for performance
    $idxStmt = $pdo->query("SHOW INDEX FROM `homepage_banners`");
    $indexes = [];
    foreach ($idxStmt->fetchAll() as $idx) {
        $indexes[] = $idx['Key_name'];
    }

    if (!in_array('idx_status_dates', $indexes)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD INDEX `idx_status_dates` (`status`, `start_datetime`, `end_datetime`)");
        echo "Added index 'idx_status_dates'\n";
    }

    if (!in_array('idx_display_order', $indexes)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD INDEX `idx_display_order` (`display_order`)");
        echo "Added index 'idx_display_order'\n";
    }

    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
