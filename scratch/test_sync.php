<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../events_helper.php';

date_default_timezone_set('Asia/Kolkata');
echo "Current Server IST Time: " . date('Y-m-d H:i:s') . "\n";

echo "Running syncAllEventStatuses()...\n";
syncAllEventStatuses($pdo);

$stmt = $pdo->query("SELECT id, title, event_date, end_date, start_time, end_time, status FROM `events` ORDER BY id ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($events as $e) {
    $calcStatus = getEventStatus($e);
    echo "ID: {$e['id']} | Title: " . substr($e['title'], 0, 40) . "... | Dates: {$e['event_date']} ({$e['start_time']}) to {$e['end_date']} ({$e['end_time']}) | DB Status: {$e['status']} | Calc Status: {$calcStatus}\n";
}
