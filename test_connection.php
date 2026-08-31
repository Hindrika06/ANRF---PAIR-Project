<?php
/**
 * Database Connection Test Script
 * Verifies: PHP -> PDO -> MySQL -> anrf database
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== ANRF PAIR Project - Database Connection Test ===\n\n";

try {
    require_once __DIR__ . '/config.php';

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("PDO instance '\$pdo' is not initialized in config.php");
    }

    echo "[1] PDO Connection: SUCCESS\n";

    // Get MySQL server version
    $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "[2] MySQL Server Version: $serverVersion\n";

    // Query active database
    $currentDb = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "[3] Connected Database: $currentDb\n";

    if ($currentDb !== DB_NAME) {
        throw new Exception("Connected to '$currentDb', expected '" . DB_NAME . "'");
    }

    // Check tables count
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $tableCount = count($tables);
    echo "[4] Total Tables in '$currentDb': $tableCount tables\n";

    // Test a query on key table: users
    $userCount = $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    echo "[5] Query Test ('users' table): $userCount records found\n";

    // Test a query on key table: cuk_conferences
    $confCount = $pdo->query("SELECT COUNT(*) FROM `cuk_conferences`")->fetchColumn();
    echo "[6] Query Test ('cuk_conferences' table): $confCount records found\n";

    echo "\n=== ALL CHECKS PASSED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "\n[ERROR] Test Failed: " . $e->getMessage() . "\n";
    exit(1);
}
