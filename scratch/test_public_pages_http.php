<?php
/**
 * Public Pages HTTP Verification Script
 * Fetches all public pages across all 7 university contexts over local Apache HTTP server.
 */

$baseUrl = 'http://localhost/ANRF---PAIR-Project/';
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

$pass = 0;
$fail = 0;

echo "=======================================================================\n";
echo "PUBLIC PAGES HTTP INTEGRATION QA SUITE\n";
echo "=======================================================================\n\n";

foreach ($pages as $p) {
    // Main URL
    $url = $baseUrl . $p;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $output = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $hasPhpError = (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false || strpos($output, 'Notice:') !== false);

    if ($code === 200 && !$hasPhpError) {
        echo "[PASS] Public Page: $p (HTTP $code)\n";
        $pass++;
    } else {
        echo "[FAIL] Public Page: $p (HTTP $code) - Error detected\n";
        $fail++;
    }

    // Test with institute query parameters
    foreach ($universities as $u) {
        $uUrl = $baseUrl . $p . '?prefix=' . $u;
        $ch = curl_init($uUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $uOutput = curl_exec($ch);
        $uCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $uPhpError = (strpos($uOutput, 'Fatal error') !== false || strpos($uOutput, 'Parse error') !== false || strpos($uOutput, 'Notice:') !== false);

        if ($uCode === 200 && !$uPhpError) {
            $pass++;
        } else {
            echo "[FAIL] Public Page Context ($u): $p?prefix=$u (HTTP $uCode)\n";
            $fail++;
        }
    }
}

echo "\n=======================================================================\n";
echo sprintf("TOTAL PUBLIC PAGE CHECKS: %d | PASSED: %d | FAILED: %d\n", ($pass + $fail), $pass, $fail);
echo "=======================================================================\n";
