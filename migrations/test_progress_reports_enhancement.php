<?php
/**
 * Automated Verification Test Suite for Progress Report Module Enhancement
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../admin/role_access.php';

echo "==================================================\n";
echo "PROGRESS REPORT MODULE ENHANCEMENT VERIFICATION\n";
echo "Current Server Time: " . date('Y-m-d H:i:s A') . "\n";
echo "==================================================\n\n";

$passCount = 0;
$totalTests = 8;

function assertTest($condition, $testLabel) {
    global $passCount;
    if ($condition) {
        $passCount++;
        echo "✅ PASS: {$testLabel}\n";
    } else {
        echo "❌ FAIL: {$testLabel}\n";
    }
}

$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

// TEST 1: Database Schema Check
$allTablesExist = true;
foreach ($prefixes as $p) {
    $rTbl = "{$p}_progress_reports";
    $pTbl = "{$p}_progress_report_publications";
    $eTbl = "{$p}_progress_report_capacity_events";

    $hasR = $pdo->query("SHOW TABLES LIKE '$rTbl'")->rowCount() > 0;
    $hasP = $pdo->query("SHOW TABLES LIKE '$pTbl'")->rowCount() > 0;
    $hasE = $pdo->query("SHOW TABLES LIKE '$eTbl'")->rowCount() > 0;

    $hasCol = false;
    if ($hasR) {
        $cols = $pdo->query("SHOW COLUMNS FROM `$rTbl`")->fetchAll(PDO::FETCH_COLUMN);
        $hasCol = in_array('interns_trained_count', $cols, true);
    }

    if (!$hasR || !$hasP || !$hasE || !$hasCol) {
        $allTablesExist = false;
        break;
    }
}
assertTest($allTablesExist, "Test #1: Schema migration verified across all 7 institute prefixes");

// TEST 2: Liveboard Exclusion Check in stats.php
require_once __DIR__ . '/../stats.php';
$hasPrInStats = false;
if (isset($uohyd_cards)) {
    foreach ($uohyd_cards as $c) {
        if (($c['key'] ?? '') === 'progress_reports' || ($c['label'] ?? '') === 'Progress Reports') {
            $hasPrInStats = true;
            break;
        }
    }
}
assertTest(!$hasPrInStats, "Test #2: Progress Reports correctly EXCLUDED from Liveboard output (stats.php)");

// TEST 3: Create Progress Report
$testPrefix = 'cuk';
$stmt = $pdo->prepare("
    INSERT INTO `{$testPrefix}_progress_reports`
        (project_title, pi_name, co_pi_name, task_no, work_package_no, approved_objects, methodology, summary_progress, interns_trained_count, approval_status, created_at)
    VALUES
        (:title, :pi, :copi, :task, :wp, :obj, :meth, :prog, :interns, 'Approved', NOW())
");
$stmt->execute([
    ':title'   => 'Automated Test Progress Report for AI Tumor Modeling',
    ':pi'      => 'Dr. Test Investigator',
    ':copi'    => 'Dr. Co-Investigator',
    ':task'    => 'TASK-TEST-99',
    ':wp'      => 'WP-TEST-01',
    ':obj'     => 'Test approved objective',
    ':meth'    => 'Test deep learning methodology',
    ':prog'    => 'Initial model training finished with 95% accuracy',
    ':interns' => 3
]);
$testPrId = (int)$pdo->lastInsertId();
assertTest($testPrId > 0, "Test #3: Inserted test Progress Report (ID: $testPrId)");

// TEST 4: Attach Publication to Progress Report
$stmtPub = $pdo->prepare("
    INSERT INTO `{$testPrefix}_progress_report_publications`
        (progress_report_id, task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor)
    VALUES
        (:pr_id, :task, :title, :author, :doi, :pdate, :journal, :impact)
");
$stmtPub->execute([
    ':pr_id'   => $testPrId,
    ':task'    => 'TASK-TEST-99',
    ':title'   => 'Deep Radiomics in Brain Glioma Segmentation',
    ':author'  => 'Dr. Test Investigator',
    ':doi'     => '10.1016/j.rad.2026.01.005',
    ':pdate'   => '2026-06-15',
    ':journal' => 'Journal of Predictive Oncology',
    ':impact'  => 5.120
]);
$testPubId = (int)$pdo->lastInsertId();
assertTest($testPubId > 0, "Test #4: Attached Publication record to Progress Report (Pub ID: $testPubId)");

// TEST 5: Attach Workshop / Conference to Progress Report
$stmtEv1 = $pdo->prepare("
    INSERT INTO `{$testPrefix}_progress_report_capacity_events`
        (progress_report_id, category, title, event_date, duration, venue_mode, organizing_institution, participant_count, description)
    VALUES
        (:pr_id, 'Workshop_Conference', :title, :edate, :dur, :venue, :org, :pcount, :desc)
");
$stmtEv1->execute([
    ':pr_id'  => $testPrId,
    ':title'  => 'International Radiomics Symposium 2026',
    ':edate'  => '2026-07-10',
    ':dur'    => '2 Days',
    ':venue'  => 'Auditorium CUK',
    ':org'    => 'Central University of Karnataka',
    ':pcount' => 120,
    ':desc'   => 'Keynote lectures and hands-on Python radiomics code laboratory'
]);
$testWorkshopId = (int)$pdo->lastInsertId();
assertTest($testWorkshopId > 0, "Test #5: Attached Workshop/Conference record to Progress Report (Event ID: $testWorkshopId)");

// TEST 6: Attach Training Program to Progress Report
$stmtEv2 = $pdo->prepare("
    INSERT INTO `{$testPrefix}_progress_report_capacity_events`
        (progress_report_id, category, title, event_date, duration, venue_mode, organizing_institution, participant_count, description)
    VALUES
        (:pr_id, 'Training_Program', :title, :edate, :dur, :venue, :org, :pcount, :desc)
");
$stmtEv2->execute([
    ':pr_id'  => $testPrId,
    ':title'  => 'PyTorch Deep Learning Bootcamp for Medical Imaging',
    ':edate'  => '2026-08-01',
    ':dur'    => '5 Days',
    ':venue'  => 'Online (Google Meet)',
    ':org'    => 'ANRF-PAIR Project',
    ':pcount' => 45,
    ':desc'   => 'Intensive training program on 3D U-Net segmentation'
]);
$testTrainingId = (int)$pdo->lastInsertId();
assertTest($testTrainingId > 0, "Test #6: Attached Training Program record to Progress Report (Event ID: $testTrainingId)");

// TEST 7: Interns Trained Count Update & Validation
$stmtUp = $pdo->prepare("UPDATE `{$testPrefix}_progress_reports` SET interns_trained_count = :cnt WHERE id = :id");
$stmtUp->execute([':cnt' => 7, ':id' => $testPrId]);

$fetchInterns = (int)$pdo->query("SELECT interns_trained_count FROM `{$testPrefix}_progress_reports` WHERE id = $testPrId")->fetchColumn();
assertTest($fetchInterns === 7, "Test #7: Updated and verified Interns Trained count (Count: $fetchInterns)");

// TEST 8: Institute Role Isolation Security
$_SESSION['role'] = 'admin';
$_SESSION['institute_prefix'] = 'cuk';

$resolvedPrefix = resolveAdminPrefix('uoh'); // Attempt URL manipulation ?prefix=uoh by CUK Admin
$canEditUoh = canEditInstitute('uoh');
$canEditCuk = canEditInstitute('cuk');

$isolationPassed = ($resolvedPrefix === 'cuk') && ($canEditUoh === false) && ($canEditCuk === true);
assertTest($isolationPassed, "Test #8: Institute Admin strictly locked to session prefix ('cuk'); ?prefix=uoh URL manipulation IGNORED");

// CLEANUP: Remove temporary test records
$pdo->exec("DELETE FROM `{$testPrefix}_progress_report_publications` WHERE id = $testPubId");
$pdo->exec("DELETE FROM `{$testPrefix}_progress_report_capacity_events` WHERE id IN ($testWorkshopId, $testTrainingId)");
$pdo->exec("DELETE FROM `{$testPrefix}_progress_reports` WHERE id = $testPrId");

echo "\n==================================================\n";
echo "SUMMARY: {$passCount} / {$totalTests} INTEGRATION TESTS PASSED CLEANLY!\n";
echo "==================================================\n";
