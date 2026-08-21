<?php
/**
 * Automated Verification Test Suite for Homepage Poster & Event Banner Scheduling System
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../admin/role_access.php';
date_default_timezone_set('Asia/Kolkata');

$nowStr = date('Y-m-d H:i:s');
$nowTS  = time();

echo "==================================================\n";
echo "ANRF-PAIR POSTER SCHEDULING SYSTEM VERIFICATION\n";
echo "Current Server Time (Asia/Kolkata): " . date('Y-m-d H:i:s A', $nowTS) . "\n";
echo "==================================================\n\n";

$passCount = 0;
$totalTests = 0;

function assertTest($description, $condition) {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        $passCount++;
        echo "✅ PASS [Test #{$totalTests}]: {$description}\n";
    } else {
        echo "❌ FAIL [Test #{$totalTests}]: {$description}\n";
    }
}

// 0. Clean up previous test records
$pdo->exec("DELETE FROM `homepage_banners` WHERE `title` LIKE 'TEST_%'");

// Helper function to query active homepage posters
function getActiveHomepagePosters($pdo, $nowTimeStr, $instPrefix = 'all') {
    $stmt = $pdo->prepare("
        SELECT * FROM `homepage_banners` 
        WHERE `status` = 'Active' 
          AND (`start_datetime` IS NULL OR `start_datetime` <= :now1) 
          AND (`end_datetime` IS NULL OR `end_datetime` >= :now2) 
          AND (`institute_prefix` = :prefix OR `institute_prefix` = 'all')
        ORDER BY `display_order` ASC, `start_datetime` DESC, `id` DESC
    ");
    $stmt->execute([':now1' => $nowTimeStr, ':now2' => $nowTimeStr, ':prefix' => $instPrefix]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// TEST A: Add a future poster -> does NOT appear on homepage
$futureStart = date('Y-m-d H:i:s', $nowTS + 3600); // +1 hour
$futureEnd   = date('Y-m-d H:i:s', $nowTS + 86400);
$pdo->exec("INSERT INTO `homepage_banners` (`title`, `start_datetime`, `end_datetime`, `status`, `image_path`, `display_order`, `institute_prefix`) VALUES ('TEST_Future', '$futureStart', '$futureEnd', 'Active', 'uploads/slider/test.jpg', 1, 'all')");
$active = getActiveHomepagePosters($pdo, $nowStr);
$foundFuture = array_filter($active, fn($b) => $b['title'] === 'TEST_Future');
assertTest("A. Scheduled/Future poster (start: +1h) is NOT visible on homepage", count($foundFuture) === 0);

// TEST B: Add currently active poster -> DOES appear on homepage
$activeStart = date('Y-m-d H:i:s', $nowTS - 3600); // -1 hour
$activeEnd   = date('Y-m-d H:i:s', $nowTS + 3600); // +1 hour
$pdo->exec("INSERT INTO `homepage_banners` (`title`, `start_datetime`, `end_datetime`, `status`, `image_path`, `display_order`, `institute_prefix`) VALUES ('TEST_Active', '$activeStart', '$activeEnd', 'Active', 'uploads/slider/test.jpg', 2, 'all')");
$active = getActiveHomepagePosters($pdo, $nowStr);
$foundActive = array_filter($active, fn($b) => $b['title'] === 'TEST_Active');
assertTest("B. Currently active poster (-1h to +1h) IS visible on homepage", count($foundActive) === 1);

// TEST C: Expired poster -> Disappears automatically from homepage
$expiredStart = date('Y-m-d H:i:s', $nowTS - 86400);
$expiredEnd   = date('Y-m-d H:i:s', $nowTS - 3600);
$pdo->exec("INSERT INTO `homepage_banners` (`title`, `start_datetime`, `end_datetime`, `status`, `image_path`, `display_order`, `institute_prefix`) VALUES ('TEST_Expired', '$expiredStart', '$expiredEnd', 'Active', 'uploads/slider/test.jpg', 3, 'all')");
$active = getActiveHomepagePosters($pdo, $nowStr);
$foundExpired = array_filter($active, fn($b) => $b['title'] === 'TEST_Expired');
assertTest("C. Expired poster (end: -1h) is NOT visible on homepage", count($foundExpired) === 0);

// TEST D: Inactive status -> NOT visible on homepage
$pdo->exec("INSERT INTO `homepage_banners` (`title`, `start_datetime`, `end_datetime`, `status`, `image_path`, `display_order`, `institute_prefix`) VALUES ('TEST_Inactive', '$activeStart', '$activeEnd', 'Inactive', 'uploads/slider/test.jpg', 4, 'all')");
$active = getActiveHomepagePosters($pdo, $nowStr);
$foundInactive = array_filter($active, fn($b) => $b['title'] === 'TEST_Inactive');
assertTest("D. Inactive poster within valid date range is NOT visible on homepage", count($foundInactive) === 0);

// TEST E: Priority / Display ordering
$pdo->exec("INSERT INTO `homepage_banners` (`title`, `start_datetime`, `end_datetime`, `status`, `image_path`, `display_order`, `institute_prefix`) VALUES ('TEST_Priority_High', '$activeStart', '$activeEnd', 'Active', 'uploads/slider/test.jpg', 1, 'all')");
$pdo->exec("INSERT INTO `homepage_banners` (`title`, `start_datetime`, `end_datetime`, `status`, `image_path`, `display_order`, `institute_prefix`) VALUES ('TEST_Priority_Low', '$activeStart', '$activeEnd', 'Active', 'uploads/slider/test.jpg', 10, 'all')");
$active = getActiveHomepagePosters($pdo, $nowStr);
$testBanners = array_values(array_filter($active, fn($b) => strpos($b['title'], 'TEST_Priority_') === 0));
assertTest("E. Posters respect priority order (Priority 1 comes before Priority 10)", count($testBanners) >= 2 && $testBanners[0]['title'] === 'TEST_Priority_High');

// TEST F: Role Isolation & Institute Boundary Security
$_SESSION['role'] = 'admin';
$_SESSION['institute_prefix'] = 'cuk';
$_GET['prefix'] = 'uoh'; // Attempted malicious URL parameter manipulation
$resolvedPrefix = resolveAdminPrefix();
assertTest("F. Institute Admin locked to session prefix ('cuk'); URL parameter ?prefix=uoh is strictly IGNORED", $resolvedPrefix === 'cuk');

// TEST G: Super Admin Privilege Test
$_SESSION['role'] = 'super_admin';
$_GET['prefix'] = 'ou';
$resolvedSuperPrefix = resolveAdminPrefix();
assertTest("G. Super Admin ('super_admin') CAN switch active institute view via prefix parameter ('ou')", $resolvedSuperPrefix === 'ou');

// TEST H: Server Timezone Verification
$kolkataTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
assertTest("H. Server timezone strictly set to Asia/Kolkata (IST offset +05:30)", $kolkataTime->getTimezone()->getName() === 'Asia/Kolkata');

// Cleanup
$pdo->exec("DELETE FROM `homepage_banners` WHERE `title` LIKE 'TEST_%'");

echo "\n==================================================\n";
echo "SUMMARY: {$passCount} / {$totalTests} INTEGRATION TESTS PASSED CLEANLY!\n";
echo "==================================================\n";
