<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Starting schema upgrades for webinars tables...\n";
    $prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

    foreach ($prefixes as $prefix) {
        $table = "{$prefix}_webinars";
        echo "Checking table `$table`...\n";
        
        // 1. Check existing columns
        $existingColumns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
        
        // Columns to add: column_name => definition
        $columnsToAdd = [
            'taskno' => "VARCHAR(50) DEFAULT NULL AFTER `id`",
            'speaker_name' => "VARCHAR(255) NULL AFTER `title`",
            'affiliation' => "VARCHAR(255) DEFAULT NULL AFTER `speaker_name`",
            'link' => "VARCHAR(1000) DEFAULT NULL AFTER `webinar_date`",
            'description' => "TEXT DEFAULT NULL AFTER `link`",
            'publish_status' => "TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`"
        ];
        
        foreach ($columnsToAdd as $col => $definition) {
            if (!in_array($col, $existingColumns)) {
                $sql = "ALTER TABLE `$table` ADD COLUMN `$col` $definition";
                $pdo->exec($sql);
                echo "  -> Added column `$col` to table `$table`.\n";
            } else {
                echo "  -> Column `$col` already exists in table `$table`.\n";
            }
        }
        
        // If the table was just upgraded, copy old column data to new column data for backwards compatibility
        // investigator -> speaker_name, institute -> affiliation, content -> description
        if (in_array('investigator', $existingColumns)) {
            $pdo->exec("UPDATE `$table` SET `speaker_name` = `investigator` WHERE `speaker_name` IS NULL AND `investigator` IS NOT NULL");
            $pdo->exec("UPDATE `$table` SET `affiliation` = `institute` WHERE `affiliation` IS NULL AND `institute` IS NOT NULL");
            $pdo->exec("UPDATE `$table` SET `description` = `content` WHERE `description` IS NULL AND `content` IS NOT NULL");
            echo "  -> Back-filled new columns from old columns for table `$table`.\n";
        }
    }
    
    echo "\nStarting event migration...\n";
    
    $eventsToMigrate = [
        [
            'title' => 'Research Training Program: YVU-UoH-ANRF-PAIR Initiative',
            'description' => 'Successfully conducted a comprehensive 15-day research training program for 7 students from Yogi Vemana University (YVU). This program was held under the collaborative YVU-UoH-ANRF-PAIR initiative, focusing on advanced research methodologies and academic development.',
            'university_id' => 'all',
            'event_date' => '2025-03-30',
            'end_date' => '2025-04-13',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'venue' => 'Yogi Vemana University / University of Hyderabad',
            'event_type' => 'Research Training Program',
            'visibility' => 'public',
            'status' => 'completed',
            'publish_status' => 1,
            'coordinator' => 'ANRF-PAIR Team',
            'created_by' => 'super_admin'
        ],
        [
            'title' => 'Invited Lecture: Main Group Materials for Health, Energy, Environment',
            'description' => 'Delivered an invited lecture on “Main Group Materials for Health, Energy, Environment” during the Symposium on “Advanced Materials” held at IIT Kanpur on 6th–7th March 2026. The lecture highlighted recent advancements and emerging applications of main group materials in healthcare, sustainable energy solutions, and environmental technologies.',
            'university_id' => 'all',
            'event_date' => '2026-03-06',
            'end_date' => '2026-03-07',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'venue' => 'IIT Kanpur',
            'event_type' => 'Invited Lecture',
            'visibility' => 'public',
            'status' => 'completed',
            'publish_status' => 1,
            'coordinator' => 'ANRF-PAIR Team',
            'created_by' => 'super_admin'
        ]
    ];
    
    foreach ($eventsToMigrate as $event) {
        // Check if event already exists by title
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `events` WHERE `title` = ?");
        $checkStmt->execute([$event['title']]);
        $exists = $checkStmt->fetchColumn();
        
        if ($exists == 0) {
            $insertStmt = $pdo->prepare("INSERT INTO `events` (
                title, description, university_id, event_date, end_date, start_time, end_time, venue,
                event_type, visibility, status, publish_status, coordinator, created_by
            ) VALUES (
                :title, :description, :university_id, :event_date, :end_date, :start_time, :end_time, :venue,
                :event_type, :visibility, :status, :publish_status, :coordinator, :created_by
            )");
            $insertStmt->execute($event);
            echo "Migrated event: '{$event['title']}' successfully.\n";
        } else {
            echo "Event: '{$event['title']}' already exists in the database. Skipped.\n";
        }
    }
    
    echo "\nAll schema upgrades and event migrations completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed with error: " . $e->getMessage() . "\n";
    exit(1);
}
