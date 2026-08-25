<?php
/**
 * Generate Real Sample Progress Report PDF for User Inspection
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once 'c:/Temp/ANRF---PAIR-Project/config.php';
require_once 'c:/Temp/ANRF---PAIR-Project/admin/role_access.php';

// Clean existing child records for report 4
$pdo->exec("DELETE FROM `cuk_progress_report_publications` WHERE progress_report_id = 4");
$pdo->exec("DELETE FROM `cuk_progress_report_capacity_events` WHERE progress_report_id = 4");

// Insert rich sample child publication
$insPub = $pdo->prepare("
    INSERT INTO `cuk_progress_report_publications`
        (progress_report_id, task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor, created_at)
    VALUES (4, 'Task 4.5', 'High-Efficiency Quantum Photonic Sensors for Environmental Monitoring', 'Dr. A. A. V. Rao, Prof. S. K. Sharma', '10.1109/TQE.2026.1049281', '2026-04-12', 'IEEE Transactions on Quantum Engineering', 7.82, NOW())
");
$insPub->execute();
$tempPubId = $pdo->lastInsertId();

// Insert rich sample workshop
$insWs = $pdo->prepare("
    INSERT INTO `cuk_progress_report_capacity_events`
        (progress_report_id, category, title, event_date, duration, venue_mode, organizing_institution, participant_count, description, created_at)
    VALUES (4, 'Workshop_Conference', 'National Workshop on Advanced Quantum Materials & Supercomputing', '2026-05-20', '3 Days', 'Auditorium B, CUK Campus (Hybrid)', 'ANRF-PAIR & Dept of Physics, CUK', 145, 'Intensive hands-on training on quantum materials simulation and supercomputing cluster setup.', NOW())
");
$insWs->execute();
$tempWsId = $pdo->lastInsertId();

// Insert rich sample training program
$insTr = $pdo->prepare("
    INSERT INTO `cuk_progress_report_capacity_events`
        (progress_report_id, category, title, event_date, duration, venue_mode, organizing_institution, participant_count, description, created_at)
    VALUES (4, 'Training_Program', 'Specialized Student Training on Parallel Python CUDA Computing', '2026-06-15', '5 Days', 'Advanced Computing Lab / Zoom', 'ANRF-PAIR Hub Center', 90, 'Hands-on parallel programming, GPU acceleration, and CUDA kernel development for research scholars.', NOW())
");
$insTr->execute();
$tempTrId = $pdo->lastInsertId();

// Update intern count to 15
$pdo->exec("UPDATE `cuk_progress_reports` SET interns_trained_count = 15 WHERE id = 4");

// Set active session context for export
$_SESSION = [
    'username' => 'superadmin',
    'user_id' => 1,
    'role' => 'super_admin',
    'institute_prefix' => 'uoh',
    'active_prefix' => 'cuk'
];
$_GET = [
    'id' => 4,
    'prefix' => 'cuk'
];

ob_start();
include 'c:/Temp/ANRF---PAIR-Project/admin/export_progress_report_pdf.php';
$pdfBinary = ob_get_clean();

// Restore DB to original baseline state
$pdo->exec("DELETE FROM `cuk_progress_report_publications` WHERE id = $tempPubId");
$pdo->exec("DELETE FROM `cuk_progress_report_capacity_events` WHERE id IN ($tempWsId, $tempTrId)");
$pdo->exec("UPDATE `cuk_progress_reports` SET interns_trained_count = 0 WHERE id = 4");

$artifactPath = 'C:/Users/HP/.gemini/antigravity-ide/brain/d4796759-319c-4b83-9536-c1a8784a191c/ANRF_PAIR_Sample_Progress_Report_CUK_Task_4_5.pdf';
$scratchPath  = 'c:/Temp/ANRF---PAIR-Project/scratch/ANRF_PAIR_Sample_Progress_Report_CUK_Task_4_5.pdf';

file_put_contents($artifactPath, $pdfBinary);
file_put_contents($scratchPath, $pdfBinary);

echo "Sample PDF Generated Successfully!\n";
echo "Artifact Path: $artifactPath\n";
echo "File Size: " . strlen($pdfBinary) . " bytes\n";
echo "Header: " . substr($pdfBinary, 0, 8) . "\n";
echo "Trailer check: " . (strpos($pdfBinary, '%%EOF') !== false ? "VALID (%%EOF present)" : "INVALID") . "\n";
