<?php
require_once 'c:/Temp/ANRF---PAIR-Project/config.php';

function executePdfExport($session, $get) {
    $sessionExport = var_export($session, true);
    $getExport = var_export($get, true);
    $code = "if(session_status()===PHP_SESSION_NONE)session_start(); \$_SESSION = $sessionExport; \$_GET = $getExport; include 'admin/export_progress_report_pdf.php';";
    $tmpFile = 'c:/Temp/ANRF---PAIR-Project/scratch/runner_' . uniqid() . '.php';
    file_put_contents($tmpFile, "<?php\n" . $code);
    $output = shell_exec("C:\\xampp\\php\\php.exe \"$tmpFile\"");
    @unlink($tmpFile);
    return $output;
}

$insPub = $pdo->prepare("
    INSERT INTO `cuk_progress_report_publications`
        (progress_report_id, task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor, created_at)
    VALUES (4, 'Task 4.5', 'Quantum Computing Advances 2026', 'Dr. Rao', '10.1109/TQE.2026.101', '2026-05-15', 'IEEE Trans Quantum', 6.45, NOW())
");
$insPub->execute();
$tempPubId = $pdo->lastInsertId();

$sessPub = ['username' => 'superadmin', 'user_id' => 1, 'role' => 'super_admin', 'institute_prefix' => 'uoh', 'active_prefix' => 'cuk'];
$getPub  = ['id' => 4, 'prefix' => 'cuk'];
$pdfPubText = executePdfExport($sessPub, $getPub);

$pdo->exec("DELETE FROM `cuk_progress_report_publications` WHERE id = $tempPubId");

echo "PDF Output Length: " . strlen($pdfPubText) . "\n";
echo "Contains 'Quantum Computing Advances 2026': " . (strpos($pdfPubText, 'Quantum Computing Advances 2026') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'Dr. Rao': " . (strpos($pdfPubText, 'Dr. Rao') !== false ? 'YES' : 'NO') . "\n";
if (strpos($pdfPubText, 'Quantum Computing Advances 2026') === false) {
    echo "First 500 chars of output:\n" . substr($pdfPubText, 0, 500) . "\n";
}
