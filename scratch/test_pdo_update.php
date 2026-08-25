<?php
require_once __DIR__ . '/../config.php';

date_default_timezone_set('Asia/Kolkata');
$nowStr = date('Y-m-d H:i:s');

try {
    $stmt = $pdo->query("SELECT id, event_date, end_date, start_time, end_time, status FROM `events`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $startDate = !empty($r['event_date']) ? $r['event_date'] : date('Y-m-d');
        $endDate   = (!empty($r['end_date']) && $r['end_date'] !== '0000-00-00') ? $r['end_date'] : $startDate;
        $startTime = !empty($r['start_time']) ? $r['start_time'] : '00:00:00';
        $endTime   = !empty($r['end_time']) ? $r['end_time'] : '23:59:59';

        $startDT = $startDate . ' ' . $startTime;
        $endDT   = $endDate . ' ' . $endTime;

        if ($nowStr < $startDT) {
            $newStatus = 'upcoming';
        } elseif ($nowStr > $endDT) {
            $newStatus = 'completed';
        } else {
            $newStatus = 'ongoing';
        }

        if ($r['status'] !== $newStatus) {
            $upd = $pdo->prepare("UPDATE `events` SET status = ? WHERE id = ?");
            $upd->execute([$newStatus, $r['id']]);
            echo "Updated event ID {$r['id']} status from '{$r['status']}' to '$newStatus'\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nFinal DB Statuses:\n";
$stmt = $pdo->query("SELECT id, title, event_date, end_date, start_time, end_time, status FROM `events` ORDER BY id ASC");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $e) {
    echo "ID: {$e['id']} | Status in DB: {$e['status']}\n";
}
