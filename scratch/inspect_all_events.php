<?php
require_once __DIR__ . '/../config.php';

$stmt = $pdo->query("SELECT id, title, event_date, end_date, start_time, end_time FROM `events`");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo "ID: {$r['id']} | event_date: '{$r['event_date']}' | end_date: '{$r['end_date']}' | start_time: '{$r['start_time']}' | end_time: '{$r['end_time']}'\n";
}
