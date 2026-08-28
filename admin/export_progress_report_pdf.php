<?php
// Multi-Tab Server-Side Session Isolation Logic
$requestedTabToken = $_REQUEST['tab_token'] ?? $_SERVER['HTTP_X_TAB_TOKEN'] ?? null;
if (!empty($requestedTabToken) && preg_match('/^[a-f0-9]{64}$/i', $requestedTabToken)) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_id($requestedTabToken);
    session_start();
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth_check.php';
require_once 'config/db.php';
require_once 'role_access.php';
global $pdo;
require_once 'vendor/fpdf/fpdf.php';

if (!function_exists('safeExportExit')) {
    function safeExportExit($msg, $code = 400) {
        http_response_code($code);
        echo $msg;
        if (PHP_SAPI === 'cli') {
            return;
        } else {
            exit();
        }
    }
}

// 1. RESOLVE & SANITIZE INPUTS
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$requestedPrefix = $_GET['prefix'] ?? null;

if (!$reportId || $reportId <= 0) {
    safeExportExit('Invalid Progress Report ID.', 400);
    if (PHP_SAPI === 'cli') return;
}

// 2. UNIVERSITY CONTEXT RESOLUTION & SECURITY GUARD
$prefix = resolveAdminPrefix($requestedPrefix);
$allowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

if ($requestedPrefix && !isSuperAdmin() && strtolower($requestedPrefix) !== strtolower($prefix)) {
    safeExportExit('Unauthorized access: Cross-institute export is forbidden.', 403);
    if (PHP_SAPI === 'cli') return;
}

if (!$prefix || !in_array($prefix, $allowedPrefixes, true) || !canEditInstitute($prefix)) {
    safeExportExit('Unauthorized access: You are not permitted to export progress reports for this institute.', 403);
    if (PHP_SAPI === 'cli') return;
}

$table       = "{$prefix}_progress_reports";
$pubsTable   = "{$prefix}_progress_report_publications";
$eventsTable = "{$prefix}_progress_report_capacity_events";

// 3. FETCH PROGRESS REPORT DATA (100% READ-ONLY)
try {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = :id");
    $stmt->execute([':id' => $reportId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        safeExportExit('Progress Report not found.', 404);
        if (PHP_SAPI === 'cli') return;
    }
} catch (Exception $e) {
    safeExportExit('Database query failed.', 500);
    if (PHP_SAPI === 'cli') return;
}
