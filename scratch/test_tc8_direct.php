<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'c:/Temp/ANRF---PAIR-Project/config.php';
require_once 'c:/Temp/ANRF---PAIR-Project/admin/role_access.php';

$pdo->exec("DELETE FROM `cuk_progress_report_capacity_events` WHERE progress_report_id = 4");

$insWs = $pdo->prepare("
    INSERT INTO `cuk_progress_report_capacity_events`
        (progress_report_id, category, title, event_date, duration, venue_mode, organizing_institution, participant_count, description, created_at)
    VALUES (4, 'Workshop_Conference', 'Advanced Machine Learning Workshop 2026', '2026-06-10', '2 Days', 'Auditorium A, CUK Campus', 'ANRF-PAIR & Dept of Physics', 85, 'Hands-on AI training', NOW())
");
$insWs->execute();
$tempWsId = $pdo->lastInsertId();

$insTr = $pdo->prepare("
    INSERT INTO `cuk_progress_report_capacity_events`
        (progress_report_id, category, title, event_date, duration, venue_mode, organizing_institution, participant_count, description, created_at)
    VALUES (4, 'Training_Program', 'High Performance Python Parallel Computing Course', '2026-07-01', '3 Days', 'Online / Zoom', 'ANRF-PAIR & CUK Lab', 120, 'Parallel GPU acceleration', NOW())
");
$insTr->execute();
$tempTrId = $pdo->lastInsertId();

$_SESSION = ['username' => 'superadmin', 'user_id' => 1, 'role' => 'super_admin', 'institute_prefix' => 'uoh', 'active_prefix' => 'cuk'];
$_GET = ['id' => 4, 'prefix' => 'cuk'];

ob_start();
include 'c:/Temp/ANRF---PAIR-Project/admin/export_progress_report_pdf.php';
$pdfCapText = ob_get_clean();

$pdo->exec("DELETE FROM `cuk_progress_report_capacity_events` WHERE id IN ($tempWsId, $tempTrId)");

echo "PDF Cap Output Length: " . strlen($pdfCapText) . "\n";
echo "Contains 'Advanced Machine Learning Workshop 2026': " . (strpos($pdfCapText, 'Advanced Machine Learning Workshop 2026') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'High Performance': " . (strpos($pdfCapText, 'High Performance') !== false ? 'YES' : 'NO') . "\n";
echo "Contains '85': " . (strpos($pdfCapText, '85') !== false ? 'YES' : 'NO') . "\n";
echo "Contains '120': " . (strpos($pdfCapText, '120') !== false ? 'YES' : 'NO') . "\n";

if (strpos($pdfCapText, 'High Performance') === false) {
    echo "Dump of text:\n" . substr($pdfCapText, strpos($pdfCapText, 'CAPACITY'), 1500) . "\n";
}
