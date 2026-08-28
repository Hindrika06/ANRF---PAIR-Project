<?php
$schemaFile = __DIR__ . '/../schema.sql';
if (!file_exists($schemaFile)) {
    echo "schema.sql not found\n";
    exit(1);
}

$sql = file_get_contents($schemaFile);
preg_match_all('/CREATE TABLE (IF NOT EXISTS )?`([^`]+)` \((.*?)\) ENGINE/s', $sql, $matches, PREG_SET_ORDER);

echo "=== ALL TABLES IN SCHEMA.SQL ===\n";
$tablesWithTaskNo = [];
$progressReportTables = [];
$childTables = [];

foreach ($matches as $m) {
    $tableName = $m[2];
    $body = $m[3];
    echo "- $tableName\n";

    if (strpos($tableName, '_progress_reports') !== false) {
        $progressReportTables[] = $tableName;
    }
    if (strpos($body, 'task_no') !== false || strpos($body, 'taskno') !== false) {
        $tablesWithTaskNo[] = $tableName;
    }
    if (strpos($body, 'progress_report_id') !== false) {
        $childTables[] = $tableName;
    }
}

echo "\n=== PROGRESS REPORT TABLES ===\n";
print_r($progressReportTables);

echo "\n=== TABLES CONTAINING task_no / taskno ===\n";
print_r($tablesWithTaskNo);

echo "\n=== TABLES CONTAINING progress_report_id ===\n";
print_r($childTables);

// Check if anrf (5).sql exists in Downloads
$downloadsSql = 'C:/Users/HP/Downloads/anrf (5).sql';
if (file_exists($downloadsSql)) {
    echo "\n=== ANALYZING C:/Users/HP/Downloads/anrf (5).sql ===\n";
    $dump = file_get_contents($downloadsSql);
    
    $prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
    foreach ($prefixes as $p) {
        $tbl = "{$p}_progress_reports";
        if (preg_match_all("/INSERT INTO `{$tbl}`[^\(]*\(.*?\)\s*VALUES\s*(.*?);/s", $dump, $rows)) {
            echo "Data inserts found for $tbl: " . count($rows[0]) . " rows\n";
        }
    }
} else {
    echo "\nDownloads SQL dump file not found at '$downloadsSql'\n";
}
