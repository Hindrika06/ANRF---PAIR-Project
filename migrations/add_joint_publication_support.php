<?php
/**
 * Migration: Add Joint Publication Support
 * Run once: php migrations/add_joint_publication_support.php
 * Safe to run multiple times (idempotent).
 */
require_once __DIR__ . '/../admin/config/db.php';

$allowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
$errors   = [];
$messages = [];

// 1. Add publication_type column to each institute publication table
foreach ($allowedPrefixes as $prefix) {
    $tableName = "{$prefix}_publications";
    $tableCheck = $pdo->query("SHOW TABLES LIKE '{$tableName}'")->fetchAll();
    if (empty($tableCheck)) {
        $messages[] = "SKIP: Table `{$tableName}` does not exist.";
        continue;
    }
    $cols = $pdo->query("SHOW COLUMNS FROM `{$tableName}` LIKE 'publication_type'")->fetchAll();
    if (!empty($cols)) {
        $messages[] = "SKIP: `publication_type` already exists in `{$tableName}`.";
        continue;
    }
    try {
        $pdo->exec("ALTER TABLE `{$tableName}` ADD COLUMN `publication_type` ENUM('Single','Joint') NOT NULL DEFAULT 'Single' AFTER `approval_status`");
        $messages[] = "OK: Added `publication_type` to `{$tableName}`.";
    } catch (PDOException $e) {
        $errors[] = "ERROR on `{$tableName}`: " . $e->getMessage();
    }
}

// 2. Create publication_institutes table
$tableCheck = $pdo->query("SHOW TABLES LIKE 'publication_institutes'")->fetchAll();
if (!empty($tableCheck)) {
    $messages[] = "SKIP: `publication_institutes` already exists.";
} else {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `publication_institutes` (
          `id`               INT AUTO_INCREMENT PRIMARY KEY,
          `publication_id`   INT NOT NULL,
          `owner_prefix`     VARCHAR(20) NOT NULL COMMENT 'prefix_{_publications table',
          `institute_prefix` VARCHAR(20) NOT NULL COMMENT 'Participating institute',
          `relationship`     ENUM('OWNER','COLLABORATOR') NOT NULL DEFAULT 'OWNER',
          `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY `uq_pub_inst` (`publication_id`, `owner_prefix`, `institute_prefix`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='Joint Publication participating institutes'");
        $messages[] = "OK: Created `publication_institutes` table.";
    } catch (PDOException $e) {
        $errors[] = "ERROR creating `publication_institutes`: " . $e->getMessage();
    }
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== Joint Publication Migration ===\n\n";
foreach ($messages as $m) { echo $m . "\n"; }
if (!empty($errors)) {
    echo "\n--- ERRORS ---\n";
    foreach ($errors as $e) { echo $e . "\n"; }
    echo "\nCompleted WITH ERRORS.\n";
} else {
    echo "\nCompleted successfully.\n";
}
