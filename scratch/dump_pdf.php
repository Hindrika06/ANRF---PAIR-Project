<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'c:/Temp/ANRF---PAIR-Project/config.php';
require_once 'c:/Temp/ANRF---PAIR-Project/admin/role_access.php';

$_SESSION = ['username' => 'superadmin', 'user_id' => 1, 'role' => 'super_admin', 'institute_prefix' => 'uoh', 'active_prefix' => 'cuk'];
$_GET = ['id' => 4, 'prefix' => 'cuk'];

ob_start();
include 'c:/Temp/ANRF---PAIR-Project/admin/export_progress_report_pdf.php';
$pdfBinary = ob_get_clean();

function getDecompressedPdfText($pdfBinary) {
    $text = $pdfBinary;
    if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdfBinary, $matches)) {
        foreach ($matches[1] as $stream) {
            $uncompressed = @gzuncompress($stream);
            if ($uncompressed !== false) {
                $text .= "\n" . $uncompressed;
            } else {
                $text .= "\n" . $stream;
            }
        }
    }
    return $text;
}

$text = getDecompressedPdfText($pdfBinary);
echo "=== DUMPING PDF TEXT ===\n";
echo $text;
echo "\n=== END DUMP ===\n";
