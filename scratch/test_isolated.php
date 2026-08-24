<?php
require_once 'c:/Temp/ANRF---PAIR-Project/config.php';

// Insert child publication
$insPub = $pdo->prepare("
    INSERT INTO `cuk_progress_report_publications`
        (progress_report_id, task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor, created_at)
    VALUES (4, 'Task 4.5', 'Quantum Test Title 2026', 'Dr. Test Author', '10.1000/doi999', '2026-05-15', 'IEEE Quantum Journal', 6.45, NOW())
");
$insPub->execute();
$tempPubId = $pdo->lastInsertId();

// Run PDF export in isolated CLI call
$cmd = "C:\\xampp\\php\\php.exe -r \"if(session_status()===PHP_SESSION_NONE)session_start(); \$_SESSION=['username'=>'superadmin','user_id'=>1,'role'=>'super_admin','institute_prefix'=>'uoh','active_prefix'=>'cuk']; \$_GET=['id'=>4,'prefix'=>'cuk']; include 'admin/export_progress_report_pdf.php';\"";
$pdfBinary = shell_exec($cmd);

// Clean up
$pdo->exec("DELETE FROM `cuk_progress_report_publications` WHERE id = $tempPubId");

echo "PDF Bytes: " . strlen($pdfBinary) . "\n";
echo "Contains 'Quantum Test Title 2026': " . (strpos($pdfBinary, 'Quantum Test Title 2026') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'Dr. Test Author': " . (strpos($pdfBinary, 'Dr. Test Author') !== false ? 'YES' : 'NO') . "\n";
