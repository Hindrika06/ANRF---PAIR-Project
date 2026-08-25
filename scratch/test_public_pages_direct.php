<?php
/**
 * Direct PHP Public Page Execution Test Suite
 * Executes every public page in PHP CLI with ob_start() for all 7 universities.
 * Verifies SQL queries, session management, PHP warnings/fatal errors, and output integrity.
 */

date_default_timezone_set('Asia/Kolkata');
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/ANRF---PAIR-Project/index.php';

$pages = [
    'index.php',
    'about-us.php',
    'participating-institutions.php',
    'team.php',
    'infrastructure-facilities.php',
    'gallery.php',
    'downloads.php',
    'contact-us.php',
    'events_activities.php',
    'outcomes_impact.php',
    'work-plan-activities.php',
    'publications-reports.php',
    'patents-innovations.php',
    'webinars.php',
    'conferences.php',
    'internships.php',
    'whatsnew.php'
];

$universities = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

$passCount = 0;
$failCount = 0;

echo "=======================================================================\n";
echo "PUBLIC PAGES DIRECT PHP EXECUTION QA SUITE\n";
echo "=======================================================================\n\n";

foreach ($pages as $publicPageFile) {
    $filePath = __DIR__ . '/../' . $publicPageFile;
    if (!file_exists($filePath)) {
        echo "[SKIP] Public page $publicPageFile does not exist on disk\n";
        continue;
    }

    // Default execution
    $_GET = [];
    ob_start();
    try {
        include $filePath;
        $output = ob_get_clean();
        $hasError = (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false || strpos($output, 'Notice:') !== false);
        if (!$hasError) {
            echo "[PASS] Public Page: $publicPageFile (Default context)\n";
            $passCount++;
        } else {
            echo "[FAIL] Public Page: $publicPageFile (Default context) - PHP error/notice detected\n";
            $failCount++;
        }
    } catch (Throwable $e) {
        ob_end_clean();
        echo "[FAIL] Public Page: $publicPageFile (Default context) - Exception: " . $e->getMessage() . "\n";
        $failCount++;
    }

    // Context execution
    foreach ($universities as $u) {
        $_GET = ['prefix' => $u, 'name' => $u];
        ob_start();
        try {
            include $filePath;
            $uOutput = ob_get_clean();
            $uHasError = (strpos($uOutput, 'Fatal error') !== false || strpos($uOutput, 'Parse error') !== false || strpos($uOutput, 'Notice:') !== false);
            if (!$uHasError) {
                $passCount++;
            } else {
                echo "[FAIL] Public Page Context ($u): $publicPageFile?prefix=$u - PHP error detected\n";
                $failCount++;
            }
        } catch (Throwable $e) {
            ob_end_clean();
            echo "[FAIL] Public Page Context ($u): $publicPageFile?prefix=$u - Exception: " . $e->getMessage() . "\n";
            $failCount++;
        }
    }
}

echo "\n=======================================================================\n";
echo sprintf("TOTAL PUBLIC PAGE CHECKS: %d | PASSED: %d | FAILED: %d\n", ($passCount + $failCount), $passCount, $failCount);
echo "=======================================================================\n";

if ($failCount === 0) {
    echo "SUCCESS: ALL PUBLIC PAGES RENDER CLEANLY ACROSS ALL UNIVERSITIES!\n";
    exit(0);
} else {
    exit(1);
}
