<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../events_helper.php';

syncAllEventStatuses($pdo);

$stmt = $pdo->query("SELECT id, title, event_date, end_date, start_time, end_time, status FROM `events` WHERE id IN (5, 134, 135) ORDER BY id ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($events as $e) {
    echo "ID: {$e['id']} | Title: " . substr($e['title'], 0, 45) . "... | Status in DB: {$e['status']} | Calc: " . getEventStatus($e) . "\n";
}
