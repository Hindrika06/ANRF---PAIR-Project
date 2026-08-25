<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'c:/Temp/ANRF---PAIR-Project/config.php';
require_once 'c:/Temp/ANRF---PAIR-Project/admin/role_access.php';

$pdo->exec("DELETE FROM `cuk_progress_report_publications` WHERE progress_report_id = 4");

$insPub = $pdo->prepare("
    INSERT INTO `cuk_progress_report_publications`
        (progress_report_id, task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor, created_at)
    VALUES (4, 'Task 4.5', 'Quantum Computing Advances 2026', 'Dr. Rao', '10.1109/TQE.2026.101', '2026-05-15', 'IEEE Trans Quantum', 6.45, NOW())
");
$insPub->execute();
$tempPubId = $pdo->lastInsertId();

$_SESSION = ['username' => 'superadmin', 'user_id' => 1, 'role' => 'super_admin', 'institute_prefix' => 'uoh', 'active_prefix' => 'cuk'];
$_GET = ['id' => 4, 'prefix' => 'cuk'];

ob_start();
include 'c:/Temp/ANRF---PAIR-Project/admin/export_progress_report_pdf.php';
$pdfPubText = ob_get_clean();

$pdo->exec("DELETE FROM `cuk_progress_report_publications` WHERE id = $tempPubId");

echo "PDF Output Length: " . strlen($pdfPubText) . "\n";
echo "Contains 'Quantum Computing Advances 2026': " . (strpos($pdfPubText, 'Quantum Computing Advances 2026') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'Dr. Rao': " . (strpos($pdfPubText, 'Dr. Rao') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'IEEE Trans Quantum': " . (strpos($pdfPubText, 'IEEE Trans Quantum') !== false ? 'YES' : 'NO') . "\n";
echo "Contains '10.1109/TQE.2026.101': " . (strpos($pdfPubText, '10.1109/TQE.2026.101') !== false ? 'YES' : 'NO') . "\n";
