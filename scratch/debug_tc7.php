<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'c:/Temp/ANRF---PAIR-Project/config.php';

function getDecompressedPdfText($pdfBinary) {
    $text = $pdfBinary;
    if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdfBinary, $matches)) {
        foreach ($matches[1] as $stream) {
            $uncompressed = @gzuncompress($stream);
            if ($uncompressed !== false) {
                $text .= "\n" . $uncompressed;
            }
        }
    }
    return $text;
}

$insPub = $pdo->prepare("
    INSERT INTO `cuk_progress_report_publications`
        (progress_report_id, task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor, created_at)
    VALUES (4, 'Task 4.5', 'Quantum Computing Advances 2026', 'Dr. Rao', '10.1109/TQE.2026.101', '2026-05-15', 'IEEE Trans', 6.45, NOW())
");
$insPub->execute();
$tempPubId = $pdo->lastInsertId();

$_SESSION = ['username' => 'superadmin', 'user_id' => 1, 'role' => 'super_admin', 'institute_prefix' => 'uoh', 'active_prefix' => 'cuk'];
$_GET = ['id' => 4, 'prefix' => 'cuk'];

ob_start();
include 'c:/Temp/ANRF---PAIR-Project/admin/export_progress_report_pdf.php';
$pdfPubBinary = ob_get_clean();

$pdo->exec("DELETE FROM `cuk_progress_report_publications` WHERE id = $tempPubId");

$text = getDecompressedPdfText($pdfPubBinary);
echo "Decompressed text length: " . strlen($text) . "\n";
echo "Contains 'Quantum Computing': " . (strpos($text, 'Quantum Computing') !== false ? 'YES' : 'NO') . "\n";
echo "Decompressed text sample:\n" . substr($text, 0, 800) . "\n";
