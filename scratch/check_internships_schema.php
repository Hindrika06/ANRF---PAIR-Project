<?php
require_once 'c:/Temp/ANRF---PAIR-Project/admin/config/db.php';

$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

foreach ($prefixes as $p) {
    $tbl = "{$p}_internships";
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_ASSOC);
        echo "=== TABLE $tbl COLUMNS ===\n";
        foreach ($cols as $c) {
            echo "  - {$c['Field']} ({$c['Type']})\n";
        }
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$tbl`");
        echo "  Total Rows in DB: " . $countStmt->fetchColumn() . "\n\n";
    } catch (Exception $e) {
        echo "=== TABLE $tbl NOT FOUND ===\n\n";
    }
}
