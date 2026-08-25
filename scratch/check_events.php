<?php
require_once __DIR__ . '/../config.php';

echo "=== SHOW COLUMNS FROM events ===\n";
$stmt = $pdo->query("SHOW COLUMNS FROM `events`");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "{$col['Field']} - {$col['Type']} - Null:{$col['Null']} - Default:{$col['Default']}\n";
}

echo "\n=== EXISTING EVENTS IN DB ===\n";
$stmt = $pdo->query("SELECT id, title, university_id, event_date, end_date, start_time, end_time, status, publish_status FROM `events` ORDER BY id ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($events as $e) {
    echo "ID: {$e['id']} | Title: {$e['title']} | Uni: {$e['university_id']} | Date: {$e['event_date']} to {$e['end_date']} | Time: {$e['start_time']} to {$e['end_time']} | Status in DB: {$e['status']} | Pub: {$e['publish_status']}\n";
}
