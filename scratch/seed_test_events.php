<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../events_helper.php';

date_default_timezone_set('Asia/Kolkata');
echo "Current IST Time: " . date('Y-m-d H:i:s') . "\n";

// 1. Check if ONGOING event exists or insert
$stmtCheckOngoing = $pdo->prepare("SELECT id FROM `events` WHERE title LIKE '%Biomaterials%' LIMIT 1");
$stmtCheckOngoing->execute();
$ongoingRow = $stmtCheckOngoing->fetch();

if (!$ongoingRow) {
    $stmtIns = $pdo->prepare("INSERT INTO `events` (
        title, description, university_id, event_date, end_date, start_time, end_time, venue,
        event_type, visibility, publish_status, coordinator, created_by
    ) VALUES (
        :title, :description, :university_id, :event_date, :end_date, :start_time, :end_time, :venue,
        :event_type, :visibility, :publish_status, :coordinator, :created_by
    )");
    $stmtIns->execute([
        ':title'          => 'NATIONAL SYMPOSIUM ON Advanced Biomaterials and Nanotechnology in Translational Medicine',
        ':description'    => 'A national symposium bringing together experts in biomedical engineering, nanomedicine, and translational healthcare technologies.',
        ':university_id'  => 'uoh',
        ':event_date'     => '2026-08-25',
        ':end_date'       => '2026-08-25',
        ':start_time'     => '09:00:00',
        ':end_time'       => '18:00:00',
        ':venue'          => 'CV Raman Auditorium, University of Hyderabad',
        ':event_type'     => 'Symposium',
        ':visibility'     => 'public',
        ':publish_status' => 1,
        ':coordinator'    => 'Prof. R. K. Sharma',
        ':created_by'     => 'uoh_admin'
    ]);
    echo "Inserted ONGOING event.\n";
} else {
    // Update dates to ensure ONGOING state today
    $stmtUpd = $pdo->prepare("UPDATE `events` SET event_date = '2026-08-25', end_date = '2026-08-25', start_time = '09:00:00', end_time = '18:00:00', publish_status = 1 WHERE id = ?");
    $stmtUpd->execute([$ongoingRow['id']]);
    echo "Updated ONGOING event ID {$ongoingRow['id']}.\n";
}

// 2. Check if UPCOMING event exists or insert
$stmtCheckUpcoming = $pdo->prepare("SELECT id FROM `events` WHERE title LIKE '%Molecular Biology%' LIMIT 1");
$stmtCheckUpcoming->execute();
$upcomingRow = $stmtCheckUpcoming->fetch();

if (!$upcomingRow) {
    $stmtIns = $pdo->prepare("INSERT INTO `events` (
        title, description, university_id, event_date, end_date, start_time, end_time, venue,
        event_type, visibility, publish_status, coordinator, created_by
    ) VALUES (
        :title, :description, :university_id, :event_date, :end_date, :start_time, :end_time, :venue,
        :event_type, :visibility, :publish_status, :coordinator, :created_by
    )");
    $stmtIns->execute([
        ':title'          => 'INTERNATIONAL CONFERENCE ON Frontier Research in Molecular Biology & Cancer Therapeutics',
        ':description'    => 'International conference covering novel drug targets, genomic profiling, molecular diagnostics, and cancer therapeutic strategies.',
        ':university_id'  => 'cuk',
        ':event_date'     => '2026-09-15',
        ':end_date'       => '2026-09-17',
        ':start_time'     => '09:30:00',
        ':end_time'       => '17:30:00',
        ':venue'          => 'Main Auditorium, Central University of Kerala',
        ':event_type'     => 'Conference',
        ':visibility'     => 'public',
        ':publish_status' => 1,
        ':coordinator'    => 'Dr. A. Nambiar',
        ':created_by'     => 'cuk_admin'
    ]);
    echo "Inserted UPCOMING event.\n";
} else {
    // Update dates to ensure UPCOMING state
    $stmtUpd = $pdo->prepare("UPDATE `events` SET event_date = '2026-09-15', end_date = '2026-09-17', start_time = '09:30:00', end_time = '17:30:00', publish_status = 1 WHERE id = ?");
    $stmtUpd->execute([$upcomingRow['id']]);
    echo "Updated UPCOMING event ID {$upcomingRow['id']}.\n";
}

// Ensure Event #5 is published and has past dates
$pdo->exec("UPDATE `events` SET event_date = '2026-07-22', end_date = '2026-07-23', start_time = '10:30:00', end_time = '17:00:00', publish_status = 1 WHERE id = 5");

// Sync all event statuses
syncAllEventStatuses($pdo);

echo "\n=== ALL EVENTS IN DB AFTER SYNC ===\n";
$stmt = $pdo->query("SELECT id, title, university_id, event_date, end_date, start_time, end_time, status, publish_status FROM `events` ORDER BY id ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($events as $e) {
    echo "ID: {$e['id']} | Title: " . substr($e['title'], 0, 45) . "... | Dates: {$e['event_date']} to {$e['end_date']} | Status in DB: {$e['status']} | Calc: " . getEventStatus($e) . "\n";
}
