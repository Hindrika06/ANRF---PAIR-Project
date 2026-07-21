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

    // Populate Flow Cytometry Workshop record
    $stmt = $pdo->prepare("UPDATE `events` SET
        `title` = 'WORKSHOP ON Flow Cytometry: Principles, Applications, and Hands-on Training for Biomedical Research',
        `description` = 'Organized by ANRF-PAIR Project in association with BD Bioscience, India.',
        `university_id` = 'yvu',
        `event_date` = '2026-07-22',
        `end_date` = '2026-07-23',
        `start_time` = '10:30:00',
        `end_time` = '17:00:00',
        `venue` = 'Tallapaka Annamacharya Senate Hall, YVU',
        `event_type` = 'Workshop',
        `image` = 'uploads/events/flow_cytometry_poster.jpg',
        `qr_code_image` = 'uploads/events/registration_qr.jpg',
        `resource_person` = 'Mr. Karuna Kumar Kondaveeti, Sr. Application Specialist, BD Bioscience, India',
        `organizer` = 'ANRF-PAIR Project in association with BD Bioscience, India.',
        `chief_patron` = 'Prof. Bellamkonda Raja Shekhar - Honorable Vice Chancellor, YVU',
        `patrons` = 'Prof P. Padma - Registrar, YVU | Prof. T. Srinivas - Principal, YVUC',
        `convener` = 'Prof. L. Dakshayani, Dept. Of Genetics & Genomics',
        `organising_committee` = 'All ANRF PAIR Investigators',
        `registration_guidelines` = 'All are welcome for the theory Lecture.\nHands on training limited to 15 members only.\nIt will be First come, First Served.\nA Certificate will be provided only for Hands on training participants.',
        `training_schedule` = 'Day 1 (22 July 2026):\n• 11:00 AM to 1:00 PM: Basics of Flow Cytometry & BD Accuri™c6 Plus Hardware Overview\n• 2:00 PM to 5:00 PM: Software Overview, Instrument Maintenance & Quality Control, Four-color Immunophenotyping\n\nDay 2 (23 July 2026):\n• 10:00 AM to 1:00 PM: DNA Cell Cycle Analysis, Apoptosis Assay, Live/Dead Cell Assays\n• 2:00 PM to 5:00 PM: Hands-On Practice by participants',
        `visibility` = 'public',
        `status` = 'upcoming',
        `publish_status` = 1,
        `coordinator` = 'Prof. L. Dakshayani',
        `updated_at` = NOW()
        WHERE `id` = 4 OR `event_type` = 'Workshop' LIMIT 1");
    $stmt->execute();

    echo "Flow Cytometry Workshop data updated successfully in database.\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
