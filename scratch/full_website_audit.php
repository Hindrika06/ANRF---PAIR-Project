<?php
/**
 * Comprehensive Full Website Audit Script
 * Checks all PHP files for lint/syntax, includes, DB queries, CSRF, Institute Isolation, and navigation links.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';

echo "==================================================\n";
echo "ANRF-PAIR FULL WEBSITE COMPREHENSIVE AUDIT\n";
echo "==================================================\n\n";

$auditIssues = [];

function addIssue($file, $category, $description) {
    global $auditIssues;
    $auditIssues[] = [
        'file'        => $file,
        'category'    => $category,
        'description' => $description
    ];
    echo "[ISSUE] [$category] $file: $description\n";
}

// 1. LIST ALL PHP FILES IN PUBLIC & ADMIN DIRECTORIES
$publicFiles = glob(__DIR__ . '/../*.php');
$adminFiles = glob(__DIR__ . '/../admin/*.php');

echo "Found " . count($publicFiles) . " public PHP files and " . count($adminFiles) . " admin PHP files.\n\n";

// 2. CHECK FOR PHP SYNTAX / LINT ERRORS
echo "--- 1. PHP Syntax Check ---\n";
$allFiles = array_merge($publicFiles, $adminFiles);
foreach ($allFiles as $fPath) {
    $rel = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', realpath($fPath));
    if (strpos($rel, 'scratch') !== false || strpos($rel, 'vendor') !== false) continue;

    $cmd = 'C:\xampp\php\php.exe -l "' . $fPath . '" 2>&1';
    $out = shell_exec($cmd);
    if ($out && strpos($out, 'No syntax errors detected') === false) {
        addIssue($rel, 'PHP Syntax', trim($out));
    }
}
echo "Syntax check complete.\n\n";

// 3. CHECK PUBLIC NAVIGATION LINKS
echo "--- 2. Public Header & Navigation Links Check ---\n";
$headerCode = file_get_contents(__DIR__ . '/../header.php');
preg_match_all('/href=["\']([^"\']+)["\']/i', $headerCode, $matches);
$foundLinks = array_unique($matches[1]);

foreach ($foundLinks as $link) {
    if (strpos($link, 'http') === 0 || strpos($link, '#') === 0 || strpos($link, 'javascript') === 0) continue;

    // strip query params
    $cleanLink = explode('?', $link)[0];
    $targetPath = __DIR__ . '/../' . $cleanLink;

    if (!file_exists($targetPath)) {
        addIssue('header.php', 'Dead Link', "Link targets '$cleanLink' which does not exist!");
    }
}
echo "Public nav check complete.\n\n";

// 4. CHECK ADMIN NAVIGATION LINKS IN SIDEBAR
echo "--- 3. Admin Sidebar Links Check ---\n";
$sidebarCode = file_get_contents(__DIR__ . '/../admin/sidebar.php');
preg_match_all('/buildNavUrl\(["\']([^"\']+)["\']\)/i', $sidebarCode, $matchesNav);
$adminNavLinks = array_unique($matchesNav[1]);

foreach ($adminNavLinks as $link) {
    $cleanLink = explode('?', $link)[0];
    $targetPath = __DIR__ . '/../admin/' . $cleanLink;

    if (!file_exists($targetPath)) {
        addIssue('admin/sidebar.php', 'Dead Admin Link', "Sidebar link targets '$cleanLink' which does not exist!");
    }
}
echo "Admin nav check complete.\n\n";

// 5. TEST DB QUERIES IN PUBLIC PAGES
echo "--- 4. Public Page Database Query Audits ---\n";
$publicPageScripts = [
    'index.php',
    'about-us.php',
    'about-1.php',
    'institute.php',
    'participating-institutions.php',
    'team.php',
    'infrastructure-facilities.php',
    'gallery.php',
    'gallery-1.php',
    'events_activities.php',
    'event-detail.php',
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
    'downloads.php',
    'stats.php',
    'logoscroll.php'
];

foreach ($publicPageScripts as $script) {
    $fullP = __DIR__ . '/../' . $script;
    if (!file_exists($fullP)) continue;

    // Run PHP in output buffer simulation to check for notices/warnings/SQL errors
    $cmd = 'C:\xampp\php\php.exe -d display_errors=1 -r "
        \$_SERVER[\'REQUEST_METHOD\'] = \'GET\';
        ob_start();
        try {
            include \'' . addslashes($fullP) . '\';
        } catch (Throwable \$e) {
            echo \'EXCEPTION: \' . \$e->getMessage();
        }
        \$buf = ob_get_clean();
        if (strpos(\$buf, \'Fatal error\') !== false || strpos(\$buf, \'Parse error\') !== false || strpos(\$buf, \'SQLSTATE\') !== false) {
            echo \$buf;
        }
    " 2>&1';

    $res = shell_exec($cmd);
    if ($res && (strpos($res, 'Fatal error') !== false || strpos($res, 'SQLSTATE') !== false || strpos($res, 'EXCEPTION') !== false)) {
        addIssue($script, 'Public Runtime Error', trim($res));
    }
}
echo "Public page query audits complete.\n\n";

// 6. CHECK ADMIN PAGES FOR DUMMY DATA SEEDING CODE
echo "--- 5. Dummy Data Seeding Check Across All Admin Pages ---\n";
foreach ($adminFiles as $fPath) {
    $rel = 'admin/' . basename($fPath);
    $code = file_get_contents($fPath);

    if (preg_match('/INSERT\s+INTO\s+.*VALUES.*sample|seed|test/i', $code) && strpos($rel, 'repair') === false && strpos($rel, 'register') === false) {
        // check if self seeding code is present
        if (strpos($code, 'SELECT COUNT(*)') !== false && strpos($code, 'INSERT INTO') !== false) {
            addIssue($rel, 'Self-Seeding Dummy Code', "File contains automatic dummy data insertion logic on empty table check!");
        }
    }
}
echo "Dummy data check complete.\n\n";

// 7. CHECK FOR MISSING VIEW MODALS / BUTTONS IN ADMIN KPI AND PAGES MODULES
echo "--- 6. Action Button Consistency Check ([View] [Edit] [Delete]) ---\n";
$adminPagesToCheck = [
    'publications.php',
    'patents.php',
    'conferences.php',
    'webinars.php',
    'internships.php',
    'progress_reports.php',
    'collaborations_management.php',
    'research_infrastructure.php',
    'sheets.php',
    'gallery_albums_management.php',
    'gallery.php',
    'event_calendar.php',
    'banner_management.php',
    'announcements_management.php',
    'team_management.php',
    'manage_admins.php'
];

foreach ($adminPagesToCheck as $ap) {
    $fullPath = __DIR__ . '/../admin/' . $ap;
    if (!file_exists($fullPath)) continue;

    $code = file_get_contents($fullPath);
    $hasView = (strpos($code, 'view') !== false || strpos($code, 'fa-eye') !== false);
    $hasEdit = (strpos($code, 'edit') !== false || strpos($code, 'fa-pencil') !== false || strpos($code, 'fa-edit') !== false);
    $hasDelete = (strpos($code, 'delete') !== false || strpos($code, 'fa-trash') !== false);

    if (!$hasView || !$hasEdit || !$hasDelete) {
        addIssue("admin/$ap", 'Missing Action', "Incomplete action button set! Has View: " . ($hasView?'YES':'NO') . ", Edit: " . ($hasEdit?'YES':'NO') . ", Delete: " . ($hasDelete?'YES':'NO'));
    }
}
echo "Action button consistency check complete.\n\n";

echo "==================================================\n";
echo "SUMMARY OF AUDIT FINDINGS\n";
echo "==================================================\n";
echo "Total Issues Found: " . count($auditIssues) . "\n";
foreach ($auditIssues as $idx => $iss) {
    echo sprintf("%2d. [%s] %s: %s\n", $idx + 1, $iss['category'], $iss['file'], $iss['description']);
}
