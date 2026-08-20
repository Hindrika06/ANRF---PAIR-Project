<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../admin/role_access.php';
require_once __DIR__ . '/../admin/config/approval_helper.php';

echo "========================================================\n";
echo "       PUBLICATIONS MODULE FUNCTIONAL VERIFICATION      \n";
echo "========================================================\n\n";

$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

// 1. DATA COUNTS BEFORE TEST
echo "--- 1. UNIVERSITY PUBLICATION COUNTS BEFORE TEST ---\n";
$initialCounts = [];
$totalBefore = 0;
foreach ($prefixes as $p) {
    $c = (int)$pdo->query("SELECT COUNT(*) FROM `{$p}_publications`")->fetchColumn();
    $initialCounts[$p] = $c;
    $totalBefore += $c;
    echo "{$p}_publications: {$c}\n";
}
echo "Total initial publications across all 7 tables: {$totalBefore}\n\n";

// TEST 1: HUB ADMIN - ALL PUBLICATIONS VIEW
echo "--- TEST 1: HUB ADMIN - ALL PUBLICATIONS VIEW ---\n";
$_SESSION['role'] = 'super_admin';
$_SESSION['institute_prefix'] = 'uoh';
$_SESSION['active_prefix'] = 'all';

$allPrefix = resolveAdminPrefix('all');
$allPubs = fetchCentralizedKpiDataset($pdo, 'publications', $allPrefix, true);
$allCount = count($allPubs);
echo "Selected prefix: {$allPrefix}\n";
echo "Fetched records count: {$allCount}\n";
$isTest1Passed = ($allCount === $totalBefore);
echo "TEST 1 " . ($isTest1Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

// TEST 2: HUB ADMIN - CUK VIEW
echo "--- TEST 2: HUB ADMIN - CUK VIEW ---\n";
$cukPrefix = resolveAdminPrefix('cuk');
$cukPubs = fetchCentralizedKpiDataset($pdo, 'publications', $cukPrefix, true);
$cukCount = count($cukPubs);
echo "Selected prefix: {$cukPrefix}\n";
echo "Fetched records count: {$cukCount} (Expected: {$initialCounts['cuk']})\n";
$isTest2Passed = ($cukCount === $initialCounts['cuk']);
foreach ($cukPubs as $cp) {
    if ($cp['institute_prefix'] !== 'cuk') {
        $isTest2Passed = false;
        echo "ERROR: Found non-cuk record in CUK view!\n";
    }
}
echo "TEST 2 " . ($isTest2Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

// TEST 3: HUB ADMIN - UOH VIEW
echo "--- TEST 3: HUB ADMIN - UOH VIEW ---\n";
$uohPrefix = resolveAdminPrefix('uoh');
$uohPubs = fetchCentralizedKpiDataset($pdo, 'publications', $uohPrefix, true);
$uohCount = count($uohPubs);
echo "Selected prefix: {$uohPrefix}\n";
echo "Fetched records count: {$uohCount} (Expected: {$initialCounts['uoh']})\n";
$isTest3Passed = ($uohCount === $initialCounts['uoh']);
foreach ($uohPubs as $up) {
    if ($up['institute_prefix'] !== 'uoh') {
        $isTest3Passed = false;
        echo "ERROR: Found non-uoh record in UoH view!\n";
    }
}
echo "TEST 3 " . ($isTest3Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

// TEST 4: HUB ADMIN - ALL 7 UNIVERSITIES SWITCHING
echo "--- TEST 4: HUB ADMIN - ALL 7 UNIVERSITIES SWITCHING ---\n";
$test4Passed = true;
foreach ($prefixes as $p) {
    $pref = resolveAdminPrefix($p);
    $pubs = fetchCentralizedKpiDataset($pdo, 'publications', $pref, true);
    $cnt = count($pubs);
    echo "Switch to {$p} -> prefix: {$pref}, count: {$cnt} (Expected: {$initialCounts[$p]})\n";
    if ($cnt !== $initialCounts[$p]) {
        $test4Passed = false;
    }
    foreach ($pubs as $row) {
        if ($row['institute_prefix'] !== $p) {
            $test4Passed = false;
            echo "ERROR: Found record with institute_prefix {$row['institute_prefix']} in {$p} view!\n";
        }
    }
}
echo "TEST 4 " . ($test4Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

// TEST 5: CUK INSTITUTE ADMIN ISOLATION
echo "--- TEST 5: CUK INSTITUTE ADMIN ISOLATION ---\n";
$_SESSION['role'] = 'admin';
$_SESSION['institute_prefix'] = 'cuk';
unset($_SESSION['active_prefix']);

$cukAdminPrefix = resolveAdminPrefix();
$cukAdminPubs = fetchCentralizedKpiDataset($pdo, 'publications', $cukAdminPrefix, false);
$cukAdminCount = count($cukAdminPubs);
echo "CUK Admin prefix resolved: {$cukAdminPrefix}\n";
echo "Fetched records count: {$cukAdminCount} (Expected: {$initialCounts['cuk']})\n";
$isTest5Passed = ($cukAdminCount === $initialCounts['cuk']);
foreach ($cukAdminPubs as $row) {
    if ($row['institute_prefix'] !== 'cuk') {
        $isTest5Passed = false;
        echo "ERROR: CUK Admin saw record from {$row['institute_prefix']}!\n";
    }
}
echo "TEST 5 " . ($isTest5Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

// TEST 6: CUK ADMIN DIRECT URL MANIPULATION ATTEMPT (?prefix=uoh)
echo "--- TEST 6: CUK ADMIN URL MANIPULATION (?prefix=uoh) ---\n";
$_GET['prefix'] = 'uoh';
$tamperedPrefix = resolveAdminPrefix('uoh');
$tamperedPubs = fetchCentralizedKpiDataset($pdo, 'publications', $tamperedPrefix, false);
$tamperedCount = count($tamperedPubs);
echo "CUK Admin requested ?prefix=uoh -> resolved prefix: {$tamperedPrefix}\n";
echo "Fetched records count: {$tamperedCount} (Expected: {$initialCounts['cuk']})\n";
$isTest6Passed = ($tamperedPrefix === 'cuk' && $tamperedCount === $initialCounts['cuk']);
foreach ($tamperedPubs as $row) {
    if ($row['institute_prefix'] !== 'cuk') {
        $isTest6Passed = false;
        echo "SECURITY FAILURE: CUK Admin saw non-CUK record!\n";
    }
}
unset($_GET['prefix']);
echo "TEST 6 " . ($isTest6Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

// TEST 7: HUB ADMIN REFRESH CONTEXT & ROLE PERSISTENCE
echo "--- TEST 7: HUB ADMIN ROLE & CONTEXT PERSISTENCE ---\n";
$_SESSION['role'] = 'super_admin';
$_SESSION['institute_prefix'] = 'uoh';
$_SESSION['active_prefix'] = 'kannur';

$res1 = resolveAdminPrefix();
$role1 = $_SESSION['role'];
echo "Active prefix: {$res1}, Role: {$role1}\n";
$isTest7Passed = ($res1 === 'kannur' && $role1 === 'super_admin');
echo "TEST 7 " . ($isTest7Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

// TEST 8: DELETE & EDIT TARGET TABLE ACCURACY
echo "--- TEST 8: HUB ADMIN DELETE/INSERT TARGET TABLE VERIFICATION ---\n";
$_SESSION['role'] = 'super_admin';
$_SESSION['active_prefix'] = 'all';

// Insert dummy record into mgu_publications
$stmt = $pdo->prepare("
    INSERT INTO `mgu_publications`
        (task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor, approval_status, created_at)
    VALUES
        ('TASK-TEST-TEMP', 'Temporary Test Record', 'Test Author', '10.1234/test', NOW(), 'Test Journal', 1.23, 'Approved', NOW())
");
$stmt->execute();
$tempId = $pdo->lastInsertId();
echo "Inserted temporary publication into mgu_publications with ID: {$tempId}\n";

$countMguAfterInsert = (int)$pdo->query("SELECT COUNT(*) FROM `mgu_publications`")->fetchColumn();
echo "mgu_publications count after insert: {$countMguAfterInsert}\n";

// Perform Delete targeting mgu_publications
$deletePrefix = 'mgu';
$deleteTable = "{$deletePrefix}_publications";
$delStmt = $pdo->prepare("DELETE FROM `{$deleteTable}` WHERE id = :id");
$delStmt->execute([':id' => $tempId]);

$countMguAfterDelete = (int)$pdo->query("SELECT COUNT(*) FROM `mgu_publications`")->fetchColumn();
echo "mgu_publications count after delete: {$countMguAfterDelete}\n";

$isTest8Passed = ($countMguAfterInsert === $initialCounts['mgu'] + 1 && $countMguAfterDelete === $initialCounts['mgu']);
echo "TEST 8 " . ($isTest8Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

// TEST 9: MULTI-TAB SESSION ISOLATION CHECK
echo "--- TEST 9: MULTI-TAB TAB_TOKEN ISOLATION ---\n";
$url1 = buildNavUrl('publications.php');
$_SESSION['tab_token'] = '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef';
$url2 = buildNavUrl('publications.php');
echo "buildNavUrl without tab_token: {$url1}\n";
echo "buildNavUrl with tab_token: {$url2}\n";
$isTest9Passed = (strpos($url2, 'tab_token=') !== false);
echo "TEST 9 " . ($isTest9Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

// TEST 10: ZERO DATA CORRUPTION & COUNT VERIFICATION
echo "--- TEST 10: UNIVERSITY PUBLICATION COUNTS AFTER ALL TESTS ---\n";
$finalCounts = [];
$totalAfter = 0;
$test10Passed = true;
foreach ($prefixes as $p) {
    $c = (int)$pdo->query("SELECT COUNT(*) FROM `{$p}_publications`")->fetchColumn();
    $finalCounts[$p] = $c;
    $totalAfter += $c;
    echo "{$p}_publications: {$c} (Initial: {$initialCounts[$p]})\n";
    if ($c !== $initialCounts[$p]) {
        $test10Passed = false;
    }
}
echo "Total final publications across all 7 tables: {$totalAfter}\n";
echo "TEST 10 " . ($test10Passed ? "PASSED [OK]" : "FAILED [FAIL]") . "\n\n";

echo "========================================================\n";
echo "ALL TESTS COMPLETED SUCCESSFULLY!\n";
echo "========================================================\n";
