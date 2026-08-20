<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Starting cuk_webinars schema upgrade...\n";

    $table = 'cuk_webinars';

    $existingColumns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);

    $columnsToAdd = [
        'taskno'           => "VARCHAR(50) DEFAULT NULL AFTER `id`",
        'speaker_name'     => "VARCHAR(255) NULL AFTER `title`",
        'affiliation'      => "VARCHAR(255) DEFAULT NULL AFTER `speaker_name`",
        'link'             => "VARCHAR(1000) DEFAULT NULL AFTER `webinar_date`",
        'whatsapp_link'    => "VARCHAR(1000) DEFAULT NULL AFTER `link`",
        'description'      => "TEXT DEFAULT NULL AFTER `whatsapp_link`",
        'publish_status'   => "TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`",
        'keynote_speaker'  => "VARCHAR(255) DEFAULT NULL",
        'resource_persons' => "TEXT DEFAULT NULL",
        'conveners'        => "VARCHAR(255) DEFAULT NULL",
        'official_email'   => "VARCHAR(255) DEFAULT NULL",
        'contact_phone'    => "VARCHAR(255) DEFAULT NULL",
        'approval_status'  => "VARCHAR(50) NOT NULL DEFAULT 'Approved'"
    ];

    foreach ($columnsToAdd as $col => $definition) {
        if (!in_array($col, $existingColumns)) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $definition");
            echo "Added column `$col` to table `$table`.\n";
        } else {
            echo "Column `$col` already exists in table `$table`.\n";
        }
    }

    echo "cuk_webinars schema upgrade completed successfully.\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
