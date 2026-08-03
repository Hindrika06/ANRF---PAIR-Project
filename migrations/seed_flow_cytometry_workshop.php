<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Starting Flow Cytometry Workshop data seeding...\n";

    $title = 'WORKSHOP ON Flow Cytometry: Principles, Applications, and Hands-on Training for Biomedical Research';

    // 1. Check if workshop record already exists to prevent duplicate insertion
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `events` WHERE `title` = ?");
    $checkStmt->execute([$title]);
    $exists = $checkStmt->fetchColumn();

    if ($exists > 0) {
        echo "Workshop record already exists in the database. Skipping insertion.\n";
        exit(0);
    }

    // 2. Prepared parameterized INSERT statement
    $stmt = $pdo->prepare("INSERT INTO `events` (
        `title`,
        `description`,
        `university_id`,
        `event_date`,
        `end_date`,
        `start_time`,
        `end_time`,
        `venue`,
        `event_type`,
        `image`,
        `qr_code_image`,
        `resource_person`,
        `organizer`,
        `chief_patron`,
        `patrons`,
        `convener`,
        `organising_committee`,
        `registration_guidelines`,
        `training_schedule`,
        `visibility`,
        `status`,
        `publish_status`,
        `coordinator`,
        `created_by`
    ) VALUES (
        :title,
        :description,
        :university_id,
        :event_date,
        :end_date,
        :start_time,
        :end_time,
        :venue,
        :event_type,
        :image,
        :qr_code_image,
        :resource_person,
        :organizer,
        :chief_patron,
        :patrons,
        :convener,
        :organising_committee,
        :registration_guidelines,
        :training_schedule,
        :visibility,
        :status,
        :publish_status,
        :coordinator,
        :created_by
    )");

    $params = [
        ':title' => $title,
        ':description' => 'Organized by ANRF-PAIR Project in association with BD Bioscience, India.',
        ':university_id' => 'yvu',
        ':event_date' => '2026-07-22',
        ':end_date' => '2026-07-23',
        ':start_time' => '10:30:00',
        ':end_time' => '17:00:00',
        ':venue' => 'Tallapaka Annamacharya Senate Hall, YVU',
        ':event_type' => 'Workshop',
        ':image' => 'uploads/events/flow_cytometry_poster.jpg',
        ':qr_code_image' => 'uploads/events/registration_qr.jpg',
        ':resource_person' => 'Mr. Karuna Kumar Kondaveeti, Sr. Application Specialist, BD Bioscience, India',
        ':organizer' => 'ANRF-PAIR Project in association with BD Bioscience, India.',
        ':chief_patron' => 'Prof. Bellamkonda Raja Shekhar - Honorable Vice Chancellor, YVU',
        ':patrons' => "Prof P. Padma - Registrar, YVU\nProf. T. Srinivas - Principal, YVUC",
        ':convener' => 'Prof. L. Dakshayani, Dept. of Genetics & Genomics',
        ':organising_committee' => 'All ANRF PAIR Investigators',
        ':registration_guidelines' => "All are welcome for the theory lecture.\nHands-on training limited to 15 members only.\nFirst come, first served.\nA certificate will be provided only for hands-on training participants.",
        ':training_schedule' => "DAY 1 – 22 JULY 2026\n11:00 AM to 1:00 PM\n- Basics of Flow Cytometry\n- BD Accuri C6 Plus hardware overview\n\n2:00 PM to 5:00 PM\n- Software Overview\n- Instrument Maintenance & Quality Control\n- Four-color Immunophenotyping\n\nDAY 2 – 23 JULY 2026\n10:00 AM to 1:00 PM\n- DNA Cell Cycle Analysis\n- Apoptosis Assay\n- Live/Dead Cell Assays\n\n2:00 PM to 5:00 PM\n- Hands-On Practice by participants",
        ':visibility' => 'public',
        ':status' => 'upcoming',
        ':publish_status' => 1,
        ':coordinator' => 'Prof. L. Dakshayani',
        ':created_by' => 'super_admin'
    ];

    $stmt->execute($params);
    $insertedId = $pdo->lastInsertId();

    echo "Flow Cytometry Workshop data inserted successfully with ID: {$insertedId}\n";
} catch (Exception $e) {
    echo "Seeding error: " . $e->getMessage() . "\n";
    exit(1);
}
