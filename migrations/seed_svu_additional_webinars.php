<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Starting additional SVU webinars data seeding...\n";

    $table = 'svu_webinars';

    $additionalWebinars = [
        [
            'taskno'         => 'WEB-SVU-2025-01',
            'title'          => 'Research Training Program: YVU-UoH-ANRF-PAIR Initiative',
            'speaker_name'   => 'ANRF-PAIR Team',
            'affiliation'    => 'Yogi Vemana University & University of Hyderabad',
            'webinar_date'   => '2025-03-30 09:00:00',
            'organisers'     => 'YVU-UoH-ANRF-PAIR Initiative',
            'institute'      => 'Yogi Vemana University / University of Hyderabad',
            'investigator'   => 'ANRF-PAIR Team',
            'description'    => 'Successfully conducted a comprehensive 15-day research training program for 7 students from Yogi Vemana University (YVU). This program was held under the collaborative YVU-UoH-ANRF-PAIR initiative, focusing on advanced research methodologies and academic development.',
            'content'        => 'Successfully conducted a comprehensive 15-day research training program for 7 students from Yogi Vemana University (YVU). This program was held under the collaborative YVU-UoH-ANRF-PAIR initiative, focusing on advanced research methodologies and academic development.',
            'image'          => null,
            'publish_status' => 1
        ],
        [
            'taskno'         => 'WEB-SVU-2026-02',
            'title'          => 'Invited Lecture: Main Group Materials for Health, Energy, Environment',
            'speaker_name'   => 'Invited Speaker',
            'affiliation'    => 'IIT Kanpur / ANRF-PAIR',
            'webinar_date'   => '2026-03-06 09:00:00',
            'organisers'     => 'IIT Kanpur Symposium on Advanced Materials',
            'institute'      => 'IIT Kanpur',
            'investigator'   => 'ANRF-PAIR Team',
<<<<<<< HEAD
            'description'    => 'Delivered an invited lecture on “Main Group Materials for Health, Energy, Environment” during the Symposium on “Advanced Materials” held at IIT Kanpur on 6th–7th March 2026. The lecture highlighted recent advancements and emerging applications of main group materials in healthcare, sustainable energy solutions, and environmental technologies.',
            'content'        => 'Delivered an invited lecture on “Main Group Materials for Health, Energy, Environment” during the Symposium on “Advanced Materials” held at IIT Kanpur on 6th–7th March 2026. The lecture highlighted recent advancements and emerging applications of main group materials in healthcare, sustainable energy solutions, and environmental technologies.',
=======
            'description'    => 'Delivered an invited lecture on Main Group Materials for Health, Energy, Environment during the Symposium on Advanced Materials held at IIT Kanpur on 6th-7th March 2026. The lecture highlighted recent advancements and emerging applications of main group materials in healthcare, sustainable energy solutions, and environmental technologies.',
            'content'        => 'Delivered an invited lecture on Main Group Materials for Health, Energy, Environment during the Symposium on Advanced Materials held at IIT Kanpur on 6th-7th March 2026. The lecture highlighted recent advancements and emerging applications of main group materials in healthcare, sustainable energy solutions, and environmental technologies.',
>>>>>>> f782f6d444b4ec1537c38314f459f6c614bde6a9
            'image'          => null,
            'publish_status' => 1
        ]
    ];

    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `title` = ?");
    $insertStmt = $pdo->prepare("INSERT INTO `$table` (
        `taskno`,
        `title`,
        `speaker_name`,
        `affiliation`,
        `webinar_date`,
        `organisers`,
        `institute`,
        `investigator`,
        `description`,
        `content`,
        `image`,
        `publish_status`
    ) VALUES (
        :taskno,
        :title,
        :speaker_name,
        :affiliation,
        :webinar_date,
        :organisers,
        :institute,
        :investigator,
        :description,
        :content,
        :image,
        :publish_status
    )");

    foreach ($additionalWebinars as $web) {
        $checkStmt->execute([$web['title']]);
        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            echo "Webinar '{$web['title']}' already exists in `{$table}`. Skipped.\n";
        } else {
            $insertStmt->execute([
                ':taskno'         => $web['taskno'],
                ':title'          => $web['title'],
                ':speaker_name'   => $web['speaker_name'],
                ':affiliation'    => $web['affiliation'],
                ':webinar_date'   => $web['webinar_date'],
                ':organisers'     => $web['organisers'],
                ':institute'      => $web['institute'],
                ':investigator'   => $web['investigator'],
                ':description'    => $web['description'],
                ':content'        => $web['content'],
                ':image'          => $web['image'],
                ':publish_status' => $web['publish_status']
            ]);
            $insertedId = $pdo->lastInsertId();
            echo "Inserted webinar '{$web['title']}' into `{$table}` with ID: {$insertedId}\n";
        }
    }

    echo "Additional webinars seeding completed successfully.\n";
} catch (Exception $e) {
    echo "Seeding error: " . $e->getMessage() . "\n";
    exit(1);
}
