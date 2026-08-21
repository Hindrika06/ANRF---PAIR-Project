<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Starting CUK Radiomics & Predictive Modeling Webinar data seeding...\n";

    $table = 'cuk_webinars';
    $title = 'Advanced Radiomics and Predictive Modeling: Mapping Tumor Invasiveness and Patient Survival';
    $webinarDate = '2026-08-20 16:00:00';

    // 1. Check if webinar record already exists to prevent duplicate insertion
    $checkStmt = $pdo->prepare("SELECT id FROM `$table` WHERE `title` = ? OR (`webinar_date` = ? AND `title` LIKE '%Advanced Radiomics%')");
    $checkStmt->execute([$title, $webinarDate]);
    $existingId = $checkStmt->fetchColumn();

    $description = "ANRF-PAIR Project Webinar on Advanced Radiomics and Predictive Modeling: Mapping Tumor Invasiveness and Patient Survival\n\nSpeaker: Prof. H. P. Rani\nDesignation: Professor\nDepartment: Department of Mathematics\nInstitution: National Institute of Technology Warangal\n\nDate: 20th August 2026 (Thursday)\nTime: 4:00 PM – 5:00 PM\nMode: Online\n\nMeeting Information:\nGoogle Meet Link: https://meet.google.com/rnz-mbbv-ohq\nPhone Dial-in (US): +1 408-831-0282 (PIN: 777 332 746#)\n\nProject Investigator: Prof. Sathyanarayana\nOrganisers: Prof. G. Janardhana Reddy, Dr. Krishna Chaithanya\nCoordinator: Dr. Ponnam Anjaneyulu";

    $payload = [
        'taskno'           => 'WEB-CUK-2026-01',
        'title'            => $title,
        'speaker_name'     => 'Prof. H. P. Rani',
        'affiliation'      => 'Professor, Department of Mathematics, National Institute of Technology Warangal',
        'webinar_date'     => $webinarDate,
        'organisers'       => 'Prof. G. Janardhana Reddy, Dr. Krishna Chaithanya',
        'institute'        => 'Central University of Karnataka (CUK)',
        'investigator'     => 'Prof. Sathyanarayana',
        'image'            => 'uploads/webinars/cuk_radiomics_webinar_poster.jpg',
        'content'          => $description,
        'link'             => 'https://meet.google.com/rnz-mbbv-ohq',
        'whatsapp_link'    => null,
        'description'      => $description,
        'publish_status'   => 1,
        'keynote_speaker'  => 'Prof. H. P. Rani',
        'resource_persons' => 'Prof. H. P. Rani (NIT Warangal)',
        'conveners'        => 'Dr. Ponnam Anjaneyulu',
        'official_email'   => null,
        'contact_phone'    => '+1 408-831-0282 (PIN: 777 332 746#)',
        'approval_status'  => 'Approved'
    ];

    $existingCols = array_flip($pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN));
    $validPayload = [];
    foreach ($payload as $col => $val) {
        if (isset($existingCols[$col])) {
            $validPayload[$col] = $val;
        }
    }

    if ($existingId) {
        echo "Webinar record already exists in `{$table}` with ID: {$existingId}. Updating existing record...\n";
        $setSql = [];
        $params = [':id' => $existingId];
        foreach ($validPayload as $col => $val) {
            $setSql[] = "`$col` = :$col";
            $params[":$col"] = $val;
        }
        $updateStmt = $pdo->prepare("UPDATE `$table` SET " . implode(', ', $setSql) . " WHERE id = :id");
        $updateStmt->execute($params);
        echo "Successfully updated CUK Webinar record (ID: {$existingId}).\n";
    } else {
        echo "Inserting new webinar record into `{$table}`...\n";
        $colsSql = implode(', ', array_map(function($c) { return "`$c`"; }, array_keys($validPayload)));
        $valsSql = implode(', ', array_map(function($c) { return ":$c"; }, array_keys($validPayload)));
        $params = [];
        foreach ($validPayload as $col => $val) {
            $params[":$col"] = $val;
        }
        $insertStmt = $pdo->prepare("INSERT INTO `$table` ($colsSql) VALUES ($valsSql)");
        $insertStmt->execute($params);
        $insertedId = $pdo->lastInsertId();
        echo "CUK Webinar inserted successfully into `{$table}` with ID: {$insertedId}\n";
    }

} catch (Exception $e) {
    echo "Seeding error: " . $e->getMessage() . "\n";
    exit(1);
}
