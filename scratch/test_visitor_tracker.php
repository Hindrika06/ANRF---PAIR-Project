<?php
/**
 * scratch/test_visitor_tracker.php
 * Verification script for unique visitor counting functionality.
 */
require_once __DIR__ . '/../visitor_tracker.php';

echo "=== STARTING UNIQUE VISITOR TRACKING TESTS ===\n\n";

try {
    // Save current count
    $initialCount = trackAndGetUniqueVisitors($pdo);
    echo "1. Initial unique visitor count: {$initialCount}\n";

    // Test 1: First visit from a simulated unique test IP
    $testIp1 = '198.51.100.' . rand(1, 254);
    $count1 = trackAndGetUniqueVisitors($pdo, $testIp1);
    echo "2. Visit from new IP ({$testIp1}) -> Unique Count: {$count1}\n";

    if ($count1 !== $initialCount + 1) {
        echo "FAIL: Expected count to increase by 1 (Expected: " . ($initialCount + 1) . ", Got: {$count1})\n";
        exit(1);
    } else {
        echo "PASS: New IP incremented unique visitor count by exactly 1.\n";
    }

    // Test 2: Multiple repeat visits from the SAME IP
    for ($i = 1; $i <= 5; $i++) {
        $countRepeat = trackAndGetUniqueVisitors($pdo, $testIp1);
    }
    echo "3. Repeat visits (5x) from same IP ({$testIp1}) -> Unique Count: {$countRepeat}\n";

    if ($countRepeat !== $count1) {
        echo "FAIL: Expected count to remain {$count1}, but got {$countRepeat}\n";
        exit(1);
    } else {
        echo "PASS: Repeat visits from same IP did NOT increase unique count.\n";
    }

    // Test 3: Check database record for testIp1
    $stmt = $pdo->prepare("SELECT * FROM `website_visitors` WHERE `ip_address` = ?");
    $stmt->execute([$testIp1]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['visit_count'] == 6) {
        echo "PASS: Database record updated visit_count to {$row['visit_count']} while maintaining single unique row.\n";
    } else {
        echo "WARNING: Expected visit_count of 6, got " . ($row['visit_count'] ?? 'null') . "\n";
    }

    // Test 4: Visit from a SECOND unique test IP
    $testIp2 = '203.0.113.' . rand(1, 254);
    $count2 = trackAndGetUniqueVisitors($pdo, $testIp2);
    echo "4. Visit from second new IP ({$testIp2}) -> Unique Count: {$count2}\n";

    if ($count2 !== $count1 + 1) {
        echo "FAIL: Expected count to increase by 1 (Expected: " . ($count1 + 1) . ", Got: {$count2})\n";
        exit(1);
    } else {
        echo "PASS: Second new IP incremented unique visitor count by exactly 1.\n";
    }

    // Cleanup test IPs
    $delStmt = $pdo->prepare("DELETE FROM `website_visitors` WHERE `ip_address` IN (?, ?)");
    $delStmt->execute([$testIp1, $testIp2]);
    echo "\nTest IP cleanup completed.\n";

    $finalCount = trackAndGetUniqueVisitors($pdo);
    echo "Final unique visitor count restored to: {$finalCount}\n";
    echo "\n=== ALL VISITOR TRACKING TESTS PASSED SUCCESSFULLY ===\n";

} catch (Exception $e) {
    echo "ERROR during testing: " . $e->getMessage() . "\n";
    exit(1);
}
