<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Starting Flow Cytometry Workshop seeding into yvu_conferences...\n";

    $table = 'yvu_conferences';
    $title = 'WORKSHOP ON Flow Cytometry: Principles, Applications, and Hands-on Training for Biomedical Research';

    // 1. Check if conference record already exists to prevent duplicate insertion
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `title` = ?");
    $checkStmt->execute([$title]);
    $exists = $checkStmt->fetchColumn();

    if ($exists > 0) {
        echo "Conference record already exists in `{$table}`. Skipping insertion.\n";
        exit(0);
    }

    $content = "Workshop on Flow Cytometry: Principles, Applications, and Hands-on Training for Biomedical Research organized by ANRF-PAIR Project in association with BD Bioscience, India at Yogi Vemana University (YVU).\n\nTime: 10:30 AM – 5:00 PM\nResource Person: Mr. Karuna Kumar Kondaveeti, Sr. Application Specialist, BD Bioscience, India\nChief Patron: Prof. Bellamkonda Raja Shekhar - Honorable Vice Chancellor, YVU\nPatrons: Prof P. Padma - Registrar, YVU | Prof. T. Srinivas - Principal, YVUC\nConvener: Prof. L. Dakshayani, Dept. of Genetics & Genomics\nOrganising Committee: All ANRF PAIR Investigators\n\nRegistration Guidelines:\n- All are welcome for the theory lecture.\n- Hands-on training limited to 15 members only.\n- First come, first served.\n- A certificate will be provided only for hands-on training participants.\n\nTraining Schedule:\nDAY 1 – 22 JULY 2026\n11:00 AM to 1:00 PM\n- Basics of Flow Cytometry\n- BD Accuri C6 Plus hardware overview\n2:00 PM to 5:00 PM\n- Software Overview\n- Instrument Maintenance & Quality Control\n- Four-color Immunophenotyping\n\nDAY 2 – 23 JULY 2026\n10:00 AM to 1:00 PM\n- DNA Cell Cycle Analysis\n- Apoptosis Assay\n- Live/Dead Cell Assays\n2:00 PM to 5:00 PM\n- Hands-On Practice by participants";

    // 2. Prepared parameterized INSERT
    $stmt = $pdo->prepare("INSERT INTO `$table` (
        `taskno`,
        `title`,
        `conf_date`,
        `end_date`,
        `organisers`,
        `institute`,
        `investigator`,
        `location`,
        `image`,
        `qr_code_image`,
        `content`,
        `resource_person`,
        `chief_patron`,
        `patrons`,
        `convener`,
        `organising_committee`,
        `registration_guidelines`,
        `training_schedule`,
        `publish_status`
    ) VALUES (
        :taskno,
        :title,
        :conf_date,
        :end_date,
        :organisers,
        :institute,
        :investigator,
        :location,
        :image,
        :qr_code_image,
        :content,
        :resource_person,
        :chief_patron,
        :patrons,
        :convener,
        :organising_committee,
        :registration_guidelines,
        :training_schedule,
        :publish_status
    )");

    $params = [
        ':taskno'                  => 'CONF-YVU-2026-01',
        ':title'                   => $title,
        ':conf_date'               => '2026-07-22',
        ':end_date'                => '2026-07-23',
        ':organisers'              => 'ANRF-PAIR Project in association with BD Bioscience, India.',
        ':institute'               => 'Yogi Vemana University (YVU), Kadapa',
        ':investigator'            => 'Prof. L. Dakshayani, Dept. of Genetics & Genomics',
        ':location'                => 'Tallapaka Annamacharya Senate Hall, YVU',
        ':image'                   => 'uploads/events/flow_cytometry_poster.jpg',
        ':qr_code_image'           => 'uploads/events/registration_qr.jpg',
        ':content'                 => $content,
        ':resource_person'         => 'Mr. Karuna Kumar Kondaveeti, Sr. Application Specialist, BD Bioscience, India',
        ':chief_patron'            => 'Prof. Bellamkonda Raja Shekhar - Honorable Vice Chancellor, YVU',
        ':patrons'                 => "Prof P. Padma - Registrar, YVU\nProf. T. Srinivas - Principal, YVUC",
        ':convener'                => 'Prof. L. Dakshayani, Dept. of Genetics & Genomics',
        ':organising_committee'    => 'All ANRF PAIR Investigators',
        ':registration_guidelines' => "All are welcome for the theory lecture.\nHands-on training limited to 15 members only.\nFirst come, first served.\nA certificate will be provided only for hands-on training participants.",
        ':training_schedule'       => "DAY 1 – 22 JULY 2026\n11:00 AM to 1:00 PM\n- Basics of Flow Cytometry\n- BD Accuri C6 Plus hardware overview\n\n2:00 PM to 5:00 PM\n- Software Overview\n- Instrument Maintenance & Quality Control\n- Four-color Immunophenotyping\n\nDAY 2 – 23 JULY 2026\n10:00 AM to 1:00 PM\n- DNA Cell Cycle Analysis\n- Apoptosis Assay\n- Live/Dead Cell Assays\n\n2:00 PM to 5:00 PM\n- Hands-On Practice by participants",
        ':publish_status'          => 1
    ];

    $stmt->execute($params);
    $insertedId = $pdo->lastInsertId();

    echo "Flow Cytometry Workshop inserted successfully into `{$table}` with ID: {$insertedId}\n";
} catch (Exception $e) {
    echo "Seeding error: " . $e->getMessage() . "\n";
    exit(1);
}
