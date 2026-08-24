<?php
/**
 * System Audit: Full Repository PHP Lint & Public Pages HTTP Status Verification
 */
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

$phpBinary = "C:\\xampp\\php\\php.exe";

echo "========================================================================================\n";
echo " ANRF-PAIR SYSTEM AUDIT: FULL REPOSITORY LINT & HTTP ENDPOINT VERIFICATION\n";
echo "========================================================================================\n";

// 1. PHP Version
$phpVersion = phpversion();
echo "PHP Version: $phpVersion\n";

// 2. Database Connection
require_once 'c:/Temp/ANRF---PAIR-Project/config.php';
try {
    $dbVer = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "MySQL Database Connected: MySQL $dbVer\n";
} catch (Exception $e) {
    echo "MySQL Connection Error: " . $e->getMessage() . "\n";
}

// 3. FPDF Library Verification
$fpdfFile = 'c:/Temp/ANRF---PAIR-Project/admin/vendor/fpdf/fpdf.php';
echo "FPDF Library Status: " . (file_exists($fpdfFile) ? "EXISTS (" . filesize($fpdfFile) . " bytes)" : "MISSING") . "\n";

// 4. Comprehensive PHP Lint across ALL project PHP files
echo "\n--- Scanning & Linting ALL Repository PHP Files ---\n";
$directory = new RecursiveDirectoryIterator('c:/Temp/ANRF---PAIR-Project');
$iterator = new RecursiveIteratorIterator($directory);
$phpFiles = [];

foreach ($iterator as $info) {
    if ($info->isFile() && strtolower($info->getExtension()) === 'php') {
        $path = $info->getPathname();
        if (strpos($path, 'vendor') !== false && strpos($path, 'admin\\vendor\\fpdf') === false && strpos($path, 'admin/vendor/fpdf') === false) {
            continue;
        }
        $phpFiles[] = $path;
    }
}

$totalPhpFiles = count($phpFiles);
$lintPassed = 0;
$lintFailed = 0;
$failedFiles = [];

foreach ($phpFiles as $file) {
    $cmd = "\"$phpBinary\" -l \"" . addslashes($file) . "\"";
    $output = shell_exec($cmd);
    if (strpos($output, 'No syntax errors detected') !== false) {
        $lintPassed++;
    } else {
        $lintFailed++;
        $failedFiles[] = $file;
    }
}

echo "Total PHP Files Scanned: $totalPhpFiles\n";
echo "PHP Lint Passed: $lintPassed\n";
echo "PHP Lint Failed: $lintFailed\n";
if (!empty($failedFiles)) {
    echo "Failed Files: " . implode(', ', $failedFiles) . "\n";
}

// 5. Git diff --check
echo "\n--- Git Code Hygiene Check ---\n";
$gitCheck = shell_exec('git diff --check');
if (empty(trim((string)$gitCheck))) {
    echo "Git Diff Check: CLEAN (0 trailing whitespace or indent errors)\n";
} else {
    echo "Git Diff Errors:\n$gitCheck\n";
}

// 6. Public Pages HTTP Status Verification
echo "\n--- Public & Admin Endpoints HTTP Status Verification ---\n";
$pages = [
    'Homepage' => 'http://127.0.0.1:8080/index.php',
    'About Us' => 'http://127.0.0.1:8080/about-us.php',
    'Participating Institutes' => 'http://127.0.0.1:8080/participating-institutions.php',
    'Publications & Reports' => 'http://127.0.0.1:8080/publications-reports.php',
    'Conferences' => 'http://127.0.0.1:8080/conferences.php',
    'Webinars' => 'http://127.0.0.1:8080/webinars.php',
    'Internships' => 'http://127.0.0.1:8080/internships.php',
    'Contact Us' => 'http://127.0.0.1:8080/contact-us.php',
    'Team' => 'http://127.0.0.1:8080/team.php',
    'Public Progress Reports' => 'http://127.0.0.1:8080/progress_reports.php',
    'Patents & Innovations' => 'http://127.0.0.1:8080/patents-innovations.php',
    'Admin Progress Reports' => 'http://127.0.0.1:8080/admin/progress_reports.php'
];

foreach ($pages as $name => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo str_pad($name, 28) . ": HTTP " . $httpCode . ($httpCode >= 200 && $httpCode < 400 ? " [OK]" : " [FAIL]") . "\n";
}

echo "========================================================================================\n";
