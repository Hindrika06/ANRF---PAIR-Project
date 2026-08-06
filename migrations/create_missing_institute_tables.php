<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Checking and creating missing institute database tables...\n";
    $prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
    $types    = ['conferences', 'internships', 'patent', 'progress_reports', 'publications', 'webinars'];

    // Collect reference schemas from existing tables
    $templates = [];
    foreach ($types as $t) {
        foreach ($prefixes as $p) {
            $sampleTable = "{$p}_{$t}";
            $stmt = $pdo->query("SHOW TABLES LIKE '{$sampleTable}'");
            if ($stmt->fetch()) {
                $createStmt = $pdo->query("SHOW CREATE TABLE `{$sampleTable}`")->fetch(PDO::FETCH_ASSOC);
                $templates[$t] = [
                    'sample' => $sampleTable,
                    'sql'    => $createStmt['Create Table']
                ];
                break;
            }
        }
    }

    $createdCount = 0;
    foreach ($prefixes as $p) {
        foreach ($types as $t) {
            $targetTable = "{$p}_{$t}";
            $stmt = $pdo->query("SHOW TABLES LIKE '{$targetTable}'");
            if (!$stmt->fetch()) {
                if (isset($templates[$t])) {
                    $templateSql = $templates[$t]['sql'];
                    $sampleName  = $templates[$t]['sample'];
                    $newSql      = preg_replace("/CREATE TABLE `{$sampleName}`/", "CREATE TABLE `{$targetTable}`", $templateSql);
                    $pdo->exec($newSql);
                    echo "  [+] Created table `{$targetTable}`.\n";
                    $createdCount++;
                }
            }
        }
    }

    echo "Finished checking tables. Total missing tables created: {$createdCount}\n";
} catch (Exception $e) {
    echo "Migration failed with error: " . $e->getMessage() . "\n";
    exit(1);
}
