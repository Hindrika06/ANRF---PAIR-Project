<?php
$downloadsSql = 'C:/Users/HP/Downloads/anrf (5).sql';
$dump = file_get_contents($downloadsSql);

$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

echo "=== PROGRESS REPORT ROWS IN DUMP ===\n";
foreach ($prefixes as $p) {
    $tbl = "{$p}_progress_reports";
    if (preg_match_all("/INSERT INTO `{$tbl}`[^\(]*\((.*?)\)\s*VALUES\s*(.*?);/s", $dump, $matches)) {
        echo "Table: $tbl\n";
        echo "Columns: " . $matches[1][0] . "\n";
        foreach ($matches[2] as $valGroup) {
            echo "Values: " . $valGroup . "\n";
        }
    }
}

echo "\n=== PROGRESS REPORT PUBLICATIONS ROWS IN DUMP ===\n";
foreach ($prefixes as $p) {
    $tbl = "{$p}_progress_report_publications";
    if (preg_match_all("/INSERT INTO `{$tbl}`[^\(]*\((.*?)\)\s*VALUES\s*(.*?);/s", $dump, $matches)) {
        echo "Table: $tbl\n";
        echo "Values: " . $matches[2][0] . "\n";
    }
}

echo "\n=== PROGRESS REPORT CAPACITY EVENTS ROWS IN DUMP ===\n";
foreach ($prefixes as $p) {
    $tbl = "{$p}_progress_report_capacity_events";
    if (preg_match_all("/INSERT INTO `{$tbl}`[^\(]*\((.*?)\)\s*VALUES\s*(.*?);/s", $dump, $matches)) {
        echo "Table: $tbl\n";
        echo "Values: " . $matches[2][0] . "\n";
    }
}
