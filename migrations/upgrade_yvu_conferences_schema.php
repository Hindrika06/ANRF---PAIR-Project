<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Starting yvu_conferences schema upgrade...\n";

    $table = 'yvu_conferences';

    $existingColumns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);

    $columnsToAdd = [
        'end_date'                => "DATE NULL AFTER `conf_date`",
        'location'                => "VARCHAR(255) NULL AFTER `institute`",
        'publish_status'          => "TINYINT(1) NOT NULL DEFAULT 1 AFTER `content`",
        'qr_code_image'           => "VARCHAR(255) NULL AFTER `image`",
        'resource_person'         => "TEXT NULL",
        'chief_patron'            => "TEXT NULL",
        'patrons'                 => "TEXT NULL",
        'convener'                => "TEXT NULL",
        'organising_committee'    => "TEXT NULL",
        'registration_guidelines' => "TEXT NULL",
        'training_schedule'       => "TEXT NULL"
    ];

    foreach ($columnsToAdd as $col => $definition) {
        if (!in_array($col, $existingColumns)) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $definition");
            echo "Added column `$col` to table `$table`.\n";
        } else {
            echo "Column `$col` already exists in table `$table`.\n";
        }
    }

    echo "yvu_conferences schema upgrade completed successfully.\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
