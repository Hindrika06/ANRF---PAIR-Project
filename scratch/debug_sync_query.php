<?php
require_once __DIR__ . '/../config.php';

date_default_timezone_set('Asia/Kolkata');
$nowStr = date('Y-m-d H:i:s');
echo "Current IST time: $nowStr\n";

$updateSqlCompleted = "
    UPDATE `events` 
    SET `status` = 'completed' 
    WHERE TIMESTAMP(
        IF(end_date IS NULL OR end_date = '' OR end_date = '0000-00-00', event_date, end_date), 
        IF(end_time IS NULL OR end_time = '', '23:59:59', end_time)
    ) < :now1
      AND `status` != 'completed'
";
$stmt1 = $pdo->prepare($updateSqlCompleted);
$stmt1->execute([':now1' => $nowStr]);
echo "Completed updated. Rows: " . $stmt1->rowCount() . "\n";

$updateSqlUpcoming = "
    UPDATE `events` 
    SET `status` = 'upcoming' 
    WHERE TIMESTAMP(
        event_date, 
        IF(start_time IS NULL OR start_time = '', '00:00:00', start_time)
    ) > :now2
      AND `status` != 'upcoming'
";
$stmt2 = $pdo->prepare($updateSqlUpcoming);
$stmt2->execute([':now2' => $nowStr]);
echo "Upcoming updated. Rows: " . $stmt2->rowCount() . "\n";

$updateSqlOngoing = "
    UPDATE `events` 
    SET `status` = 'ongoing' 
    WHERE TIMESTAMP(
        event_date, 
        IF(start_time IS NULL OR start_time = '', '00:00:00', start_time)
    ) <= :now3
      AND TIMESTAMP(
        IF(end_date IS NULL OR end_date = '' OR end_date = '0000-00-00', event_date, end_date), 
        IF(end_time IS NULL OR end_time = '', '23:59:59', end_time)
    ) >= :now4
      AND `status` != 'ongoing'
";
$stmt3 = $pdo->prepare($updateSqlOngoing);
$stmt3->execute([':now3' => $nowStr, ':now4' => $nowStr]);
echo "Ongoing updated. Rows: " . $stmt3->rowCount() . "\n";

$stmt = $pdo->query("SELECT id, title, event_date, end_date, start_time, end_time, status FROM `events` WHERE id IN (5, 134, 135) ORDER BY id ASC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($events as $e) {
    echo "ID: {$e['id']} | Title: " . substr($e['title'], 0, 40) . "... | Status in DB: {$e['status']}\n";
}
