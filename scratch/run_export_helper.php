<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

$jsonFile = $argv[1] ?? 'c:/Temp/ANRF---PAIR-Project/scratch/export_args.json';
if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
    if (!empty($data['session'])) {
        $_SESSION = $data['session'];
    }
    if (!empty($data['get'])) {
        $_GET = $data['get'];
    }
}

require_once 'c:/Temp/ANRF---PAIR-Project/admin/export_progress_report_pdf.php';
