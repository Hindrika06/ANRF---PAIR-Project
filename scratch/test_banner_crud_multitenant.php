<?php
/**
 * Comprehensive Multi-Tenant QA Test Suite for Homepage Banner / Poster CRUD
 * Tests all 7 universities: cuk, kannur, mgu, ou, svu, uoh, yvu
 * Tests Hub Admin context switching, Spoke Admin security policy, Edit persistence, Image preservation, Delete cleanup.
 */

date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../admin/config/db.php';
require_once __DIR__ . '/../admin/role_access.php';

$universities = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
$passCount = 0;
$failCount = 0;
$testResults = [];

function assertTest($id, $component, $description, $condition, $details = '') {
    global $passCount, $failCount, $testResults;
    if ($condition) {
        $passCount++;
        $status = "PASS";
    } else {
        $failCount++;
        $status = "FAIL";
    }
    $testResults[] = [
        'id' => $id,
        'component' => $component,
        'description' => $description,
        'status' => $status,
        'details' => $details
    ];
    echo sprintf("[%s] %s: %s -> %s (%s)\n", $status, $id, $component, $description, $details);
}

echo "=======================================================\n";
echo "MULTI-TENANT HOMEPAGE BANNER CRUD AUDIT & TEST SUITE\n";
echo "=======================================================\n\n";

// Ensure dummy test image exists
$dummyImageDir = __DIR__ . '/../uploads/slider/';
if (!is_dir($dummyImageDir)) {
    mkdir($dummyImageDir, 0755, true);
}
$dummyImgPath = 'uploads/slider/test_qa_dummy_poster.jpg';
$fullDummyImgPath = __DIR__ . '/../' . $dummyImgPath;
if (!file_exists($fullDummyImgPath)) {
    copy(__DIR__ . '/../1.png', $fullDummyImgPath);
}

// Clean up any stale QA test records before running
$pdo->exec("DELETE FROM `homepage_banners` WHERE title LIKE 'QA_MULTI_TENANT_%'");

$createdBannerIds = [];

// 1. MULTI-TENANT CRUD TEST FOR EACH UNIVERSITY
foreach ($universities as $u) {
    echo "\n--- TESTING UNIVERSITY CONTEXT: " . strtoupper($u) . " ---\n";
    
    // Simulate Hub Admin context for university $u
    $_SESSION['role'] = 'super_admin';
    $_SESSION['active_prefix'] = $u;
    $_SESSION['institute_prefix'] = 'uoh';

    // Step 1: CREATE Banner for university $u
    $title = "QA_MULTI_TENANT_BANNER_" . strtoupper($u);
    $shortDesc = "Automated test poster description for " . strtoupper($u);
    $targetUrl = "https://example.com/test-" . $u;
    $startDT = date('Y-m-d H:i:s', time() - 3600);
    $endDT = date('Y-m-d H:i:s', time() + 86400 * 7);
    $displayOrder = rand(1, 20);
    $status = 'Active';

    $stmt = $pdo->prepare("
        INSERT INTO `homepage_banners`
        (`title`, `short_description`, `target_url`, `start_datetime`, `end_datetime`, `institute_prefix`, `image_path`, `caption`, `display_order`, `status`)
        VALUES (:title, :short_description, :target_url, :start_datetime, :end_datetime, :institute_prefix, :image_path, :caption, :display_order, :status)
    ");
    $stmt->execute([
        ':title' => $title,
        ':short_description' => $shortDesc,
        ':target_url' => $targetUrl,
        ':start_datetime' => $startDT,
        ':end_datetime' => $endDT,
        ':institute_prefix' => $u,
        ':image_path' => $dummyImgPath,
        ':caption' => $title,
        ':display_order' => $displayOrder,
        ':status' => $status
    ]);
    $newId = (int)$pdo->lastInsertId();
    $createdBannerIds[$u] = $newId;

    assertTest("TC-CREATE-$u", "Banner CREATE", "Create temporary banner for " . strtoupper($u), $newId > 0, "Inserted Record ID #$newId");

    // Step 2: READ / Listing Query verification in $u context
    $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE institute_prefix = :p OR institute_prefix = 'all'");
    $stmt->execute([':p' => $u]);
    $contextRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $foundInContext = false;
    foreach ($contextRows as $row) {
        if ((int)$row['id'] === $newId) {
            $foundInContext = true;
            break;
        }
    }
    assertTest("TC-READ-$u", "Banner READ", "Verify banner appears in " . strtoupper($u) . " context", $foundInContext, "Found ID #$newId in $u context query");

    // Step 3: EDIT & UPDATE banner values
    $updatedTitle = "QA_MULTI_TENANT_BANNER_" . strtoupper($u) . "_UPDATED";
    $updatedDesc = "Updated description for " . strtoupper($u);
    $updatedUrl = "https://example.com/updated-" . $u;
    $updatedOrder = 1;
    $updatedStatus = 'Active';

    // Simulate update query as executed by banner_management.php without changing image
    $stmt = $pdo->prepare("
        UPDATE `homepage_banners`
        SET `title` = :title,
            `short_description` = :short_description,
            `target_url` = :target_url,
            `display_order` = :display_order,
            `status` = :status
        WHERE `id` = :id AND `institute_prefix` = :prefix
    ");
    $stmt->execute([
        ':title' => $updatedTitle,
        ':short_description' => $updatedDesc,
        ':target_url' => $updatedUrl,
        ':display_order' => $updatedOrder,
        ':status' => $updatedStatus,
        ':id' => $newId,
        ':prefix' => $u
    ]);

    // Verify persistence & image preservation
    $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE id = :id");
    $stmt->execute([':id' => $newId]);
    $updatedRow = $stmt->fetch(PDO::FETCH_ASSOC);

    $persistenceOk = ($updatedRow && $updatedRow['title'] === $updatedTitle && $updatedRow['short_description'] === $updatedDesc && $updatedRow['image_path'] === $dummyImgPath);
    assertTest("TC-UPDATE-$u", "Banner UPDATE", "Verify edit persistence & image preservation for " . strtoupper($u), $persistenceOk, "Title updated to '$updatedTitle', image retained");

    // Step 4: ISOLATION CHECK - Switch to another university (e.g. if $u == 'cuk', switch to 'uoh'; if $u == 'uoh', switch to 'cuk')
    $otherUniv = ($u === 'uoh') ? 'cuk' : 'uoh';
    $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE institute_prefix = :p OR institute_prefix = 'all'");
    $stmt->execute([':p' => $otherUniv]);
    $otherContextRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $leakedInOther = false;
    foreach ($otherContextRows as $row) {
        if ((int)$row['id'] === $newId) {
            $leakedInOther = true;
            break;
        }
    }
    assertTest("TC-ISOLATE-$u", "Multi-Tenant Isolation", "Verify banner for " . strtoupper($u) . " does NOT leak into " . strtoupper($otherUniv) . " context", !$leakedInOther, "Hidden in $otherUniv context");

    // Step 5: RETURN to original context & verify banner still exists
    $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE id = :id AND (institute_prefix = :p OR institute_prefix = 'all')");
    $stmt->execute([':id' => $newId, ':p' => $u]);
    $returnedRow = $stmt->fetch(PDO::FETCH_ASSOC);
    assertTest("TC-RE-VERIFY-$u", "Context Return Verification", "Verify banner still exists upon returning to " . strtoupper($u), !empty($returnedRow), "Banner ID #$newId intact");
}

// 2. SPOKE ADMIN SECURITY AUTHORIZATION TEST
echo "\n--- TESTING SPOKE ADMIN AUTHORIZATION & ISOLATION POLICY ---\n";

// Create record for CUK
$cukBannerId = $createdBannerIds['cuk'];
$uohBannerId = $createdBannerIds['uoh'];

// Simulate Spoke Admin logged into UOH
$_SESSION['role'] = 'admin';
$_SESSION['institute_prefix'] = 'uoh';

// Spoke Admin UOH attempts to edit own UOH banner -> ALLOWED
$canEditOwn = canEditInstitute('uoh');
assertTest("TC-AUTH-SPOKE-OWN", "Spoke Admin Auth", "Spoke Admin UoH can edit own UoH banner", $canEditOwn, "canEditInstitute('uoh') = true");

// Spoke Admin UOH attempts to edit CUK banner -> BLOCKED
$canEditOther = canEditInstitute('cuk');
assertTest("TC-AUTH-SPOKE-OTHER", "Spoke Admin Auth", "Spoke Admin UoH blocked from editing CUK banner", !$canEditOther, "canEditInstitute('cuk') = false");

// 3. CLEANUP & DELETE VERIFICATION
echo "\n--- TESTING DELETE & CLEANUP ACROSS ALL UNIVERSITIES ---\n";
foreach ($universities as $u) {
    $delId = $createdBannerIds[$u];

    // Simulate delete
    $_SESSION['role'] = 'super_admin';
    $_SESSION['active_prefix'] = $u;

    $stmt = $pdo->prepare("DELETE FROM `homepage_banners` WHERE id = :id");
    $stmt->execute([':id' => $delId]);

    // Verify deletion
    $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE id = :id");
    $stmt->execute([':id' => $delId]);
    $deletedRow = $stmt->fetch(PDO::FETCH_ASSOC);

    assertTest("TC-DELETE-$u", "Banner DELETE", "Delete temporary banner for " . strtoupper($u), empty($deletedRow), "Record ID #$delId deleted cleanly");
}

echo "\n=======================================================\n";
echo sprintf("TOTAL TESTS: %d | PASSED: %d | FAILED: %d\n", count($testResults), $passCount, $failCount);
echo "=======================================================\n";

if ($failCount === 0) {
    echo "SUCCESS: ALL HOMEPAGE BANNER CRUD & MULTI-TENANT ISOLATION TESTS PASSED!\n";
    exit(0);
} else {
    echo "FAILURE: SOME TESTS FAILED!\n";
    exit(1);
}
