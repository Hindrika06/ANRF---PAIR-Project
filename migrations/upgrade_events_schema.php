<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Starting events table schema migration...\n";

    $columnsToAdd = [
        "end_date" => "DATE NULL AFTER `event_date`",
        "qr_code_image" => "VARCHAR(255) NULL AFTER `image`",
        "resource_person" => "TEXT NULL",
        "organizer" => "TEXT NULL",
        "chief_patron" => "TEXT NULL",
        "patrons" => "TEXT NULL",
        "convener" => "TEXT NULL",
        "organising_committee" => "TEXT NULL",
        "registration_guidelines" => "TEXT NULL",
        "training_schedule" => "TEXT NULL"
    ];

    $existingColumns = $pdo->query("SHOW COLUMNS FROM `events`")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($columnsToAdd as $col => $definition) {
        if (!in_array($col, $existingColumns)) {
            $sql = "ALTER TABLE `events` ADD COLUMN `$col` $definition";
            $pdo->exec($sql);
            echo "Added column `$col` to `events` table.\n";
        } else {
            echo "Column `$col` already exists.\n";
        }
    }

    echo "Events table schema upgrade completed successfully.\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
