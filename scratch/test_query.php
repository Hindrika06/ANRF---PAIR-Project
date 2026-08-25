<?php
require_once __DIR__ . '/../config.php';

date_default_timezone_set('Asia/Kolkata');
$nowStr = date('Y-m-d H:i:s');
echo "NowStr: $nowStr\n";

$sql = "SELECT id, title, CONCAT(IFNULL(NULLIF(end_date, ''), event_date), ' ', end_time) as end_dt, status FROM `events` WHERE id = 5";
$row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
echo "End DT: {$row['end_dt']}\n";
echo "Is End DT < NowStr? " . ($row['end_dt'] < $nowStr ? 'YES' : 'NO') . "\n";

$updateSql = "UPDATE `events` SET `status` = 'completed' WHERE id = 5";
$pdo->exec($updateSql);

$row2 = $pdo->query("SELECT id, status FROM `events` WHERE id = 5")->fetch(PDO::FETCH_ASSOC);
echo "Updated DB Status: {$row2['status']}\n";
