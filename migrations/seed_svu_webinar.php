<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Starting SVU Smart Nano Biosensors Webinar data seeding...\n";

    $table = 'svu_webinars';
    $title = 'Webinar on Smart Nano Biosensors for Rapid Healthcare Diagnostics';
    $webinarDate = '2026-05-20 10:00:00';

    // 1. Check if webinar record already exists to prevent duplicate insertion
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `title` = ? OR (`webinar_date` = ? AND `title` LIKE '%Smart Nano Biosensors%')");
    $checkStmt->execute([$title, $webinarDate]);
    $exists = $checkStmt->fetchColumn();

    if ($exists > 0) {
        echo "Webinar record already exists in `{$table}`. Skipping insertion.\n";
        exit(0);
    }

    $description = "This webinar aims to provide insights into recent advances in smart nano biosensors, rapid healthcare diagnostics, electrochemical sensing technologies and emerging nanomaterial-based biomedical applications.\n\nKeynote Speaker: Prof. Tata Narasinga Rao (Vice-Chancellor, SV University)\n\nResource Persons:\n• Prof. V.V.S.S. Srikanth (University of Hyderabad)\n• Prof. G. Madhavi (SV University)\n• Prof. A. Rajanikanth (University of Hyderabad)\n• Prof. Baiju Vijayan (Kannur University)\n• Prof. L.S. Sharma (Yogi Vemana University)\n• Dr. Y.V. Manohara Reddy (Delhi University)\n\nConveners: Prof. G. Madhavi & Prof. V.V.S.S. Srikanth\nRegistration Fee: No Registration Fee\nCertificate: e-Certificates will be provided to the registered participants.\nOfficial Email: anrfpairsvu@gmail.com\nContact: 91 94400 96500\nWhatsApp Group: https://chat.whatsapp.com/I5TsRTkwkw8KIBfJw31pZC";

    $resourcePersonsText = "Prof. V.V.S.S. Srikanth — University of Hyderabad\nProf. G. Madhavi — SV University\nProf. A. Rajanikanth — University of Hyderabad\nProf. Baiju Vijayan — Kannur University\nProf. L.S. Sharma — Yogi Vemana University\nDr. Y.V. Manohara Reddy — Delhi University";

    // 2. Execute parameterized INSERT
    $stmt = $pdo->prepare("INSERT INTO `$table` (
        `taskno`,
        `title`,
        `speaker_name`,
        `affiliation`,
        `webinar_date`,
        `organisers`,
        `institute`,
        `investigator`,
        `image`,
        `content`,
        `link`,
        `whatsapp_link`,
        `description`,
        `publish_status`,
        `keynote_speaker`,
        `resource_persons`,
        `conveners`,
        `official_email`,
        `contact_phone`
    ) VALUES (
        :taskno,
        :title,
        :speaker_name,
        :affiliation,
        :webinar_date,
        :organisers,
        :institute,
        :investigator,
        :image,
        :content,
        :link,
        :whatsapp_link,
        :description,
        :publish_status,
        :keynote_speaker,
        :resource_persons,
        :conveners,
        :official_email,
        :contact_phone
    )");

    $params = [
        ':taskno'           => 'WEB-SVU-2026-01',
        ':title'            => $title,
        ':speaker_name'     => 'Prof. Tata Narasinga Rao (Keynote Speaker)',
        ':affiliation'      => 'Sri Venkateswara University, Tirupati',
        ':webinar_date'     => $webinarDate,
        ':organisers'       => 'SVU ANRF-PAIR Project Group',
        ':institute'        => 'Sri Venkateswara University, Tirupati',
        ':investigator'     => 'Prof. G. Madhavi & Prof. V.V.S.S. Srikanth',
        ':image'            => 'uploads/webinars/smart_nano_biosensors_webinar.jpg',
        ':content'          => $description,
        ':link'             => 'https://forms.gle/4CbPrh75gUiolg869',
        ':whatsapp_link'    => 'https://chat.whatsapp.com/I5TsRTkwkw8KIBfJw31pZC',
        ':description'      => $description,
        ':publish_status'   => 1,
        ':keynote_speaker'  => 'Prof. Tata Narasinga Rao (Vice-Chancellor, SV University)',
        ':resource_persons' => $resourcePersonsText,
        ':conveners'        => 'Prof. G. Madhavi & Prof. V.V.S.S. Srikanth',
        ':official_email'   => 'anrfpairsvu@gmail.com',
        ':contact_phone'    => '91 94400 96500'
    ];

    $stmt->execute($params);
    $insertedId = $pdo->lastInsertId();

    echo "SVU Smart Nano Biosensors Webinar inserted successfully into `{$table}` with ID: {$insertedId}\n";
} catch (Exception $e) {
    echo "Seeding error: " . $e->getMessage() . "\n";
    exit(1);
}
