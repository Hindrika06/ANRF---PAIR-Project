<?php
/**
 * Detailed Public & Admin Pages End-to-End Simulation Script
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';

echo "==================================================\n";
echo "FULL WEBSITE PAGE RENDERING & QUERY SIMULATION\n";
echo "==================================================\n\n";

$publicPages = [
    'index.php',
    'about-us.php',
    'about-1.php',
    'institute.php?prefix=uoh',
    'institute.php?prefix=cuk',
    'participating-institutions.php',
    'team.php',
    'infrastructure-facilities.php',
    'gallery.php',
    'gallery-1.php',
    'events_activities.php',
    'event-detail.php?id=1',
    'whatsnew.php',
    'contact-us.php',
    'conferences.php',
    'webinars.php',
    'internships.php',
    'progress_reports.php',
    'publications-reports.php',
    'patents-innovations.php',
    'outcomes_impact.php',
    'collobrations.php',
    'quick_links.php',
    'downloads.php'
];

$adminPages = [
    'admin/dashboard.php?prefix=uoh',
    'admin/dashboard.php?prefix=cuk',
    'admin/approvals.php',
    'admin/publications.php?prefix=uoh',
    'admin/patents.php?prefix=uoh',
    'admin/conferences.php?prefix=uoh',
    'admin/webinars.php?prefix=uoh',
    'admin/internships.php?prefix=uoh',
    'admin/progress_reports.php?prefix=uoh',
    'admin/collaborations_management.php',
    'admin/research_infrastructure.php',
    'admin/sheets.php',
    'admin/gallery_albums_management.php?prefix=uoh',
    'admin/gallery_albums_management.php?prefix=cuk',
    'admin/gallery.php?prefix=uoh',
    'admin/gallery.php?prefix=cuk',
    'admin/event_calendar.php?prefix=uoh',
    'admin/banner_management.php?prefix=uoh',
    'admin/announcements_management.php',
    'admin/team_management.php',
    'admin/manage_admins.php'
];

$results = [];

// Simulate session for admin check
$_SESSION['username'] = 'superadmin@uoh.ac.in';
$_SESSION['role'] = 'super_admin';
$_SESSION['institute_prefix'] = 'uoh';

echo "--- Testing Public Pages ---\n";
foreach ($publicPages as $p) {
    $parts = parse_url($p);
    $path = $parts['path'];
    if (isset($parts['query'])) {
        parse_str($parts['query'], $_GET);
    } else {
        $_GET = [];
    }

    $fullP = __DIR__ . '/../' . $path;
    ob_start();
    $err = null;
    try {
        include $fullP;
    } catch (Throwable $e) {
        $err = $e->getMessage();
    }
    $out = ob_get_clean();

    $hasErr = ($err !== null) || strpos($out, 'Fatal error') !== false || strpos($out, 'SQLSTATE') !== false || strpos($out, 'Warning:') !== false;
    $status = $hasErr ? "FAIL" : "PASS";

    $results[] = ['page' => $p, 'type' => 'Public', 'status' => $status, 'error' => $err ?: ($hasErr ? 'Output contains error' : '')];
    echo sprintf("[%s] %-35s %s\n", $status, $p, $err ? "($err)" : "");
}

echo "\n--- Testing Admin Pages ---\n";
foreach ($adminPages as $p) {
    $parts = parse_url($p);
    $path = $parts['path'];
    if (isset($parts['query'])) {
        parse_str($parts['query'], $_GET);
    } else {
        $_GET = [];
    }

    $fullP = __DIR__ . '/../' . $path;
    ob_start();
    $err = null;
    try {
        include $fullP;
    } catch (Throwable $e) {
        $err = $e->getMessage();
    }
    $out = ob_get_clean();

    $hasErr = ($err !== null) || strpos($out, 'Fatal error') !== false || strpos($out, 'SQLSTATE') !== false;
    $status = $hasErr ? "FAIL" : "PASS";

    $results[] = ['page' => $p, 'type' => 'Admin', 'status' => $status, 'error' => $err ?: ($hasErr ? 'Output contains error' : '')];
    echo sprintf("[%s] %-35s %s\n", $status, $p, $err ? "($err)" : "");
}

echo "\n==================================================\n";
echo "PAGE RENDERING SUMMARY\n";
echo "==================================================\n";
$passCount = 0; $failCount = 0;
foreach ($results as $r) {
    if ($r['status'] === 'PASS') $passCount++;
    else $failCount++;
}
echo "Total Pages Evaluated: " . count($results) . "\n";
echo "Passed: $passCount | Failed: $failCount\n";
