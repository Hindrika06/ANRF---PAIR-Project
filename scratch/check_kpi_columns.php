<?php
require_once 'c:/Temp/ANRF---PAIR-Project/admin/config/db.php';

$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

foreach ($prefixes as $p) {
    echo "=== INSTITUTE $p ===\n";
    $kpiTables = [
        "{$p}_publications",
        "{$p}_conferences",
        "{$p}_webinars",
        "{$p}_internships",
        "{$p}_patents"
    ];
    foreach ($kpiTables as $tbl) {
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
            $taskCol = null;
            if (in_array('task_no', $cols)) $taskCol = 'task_no';
            elseif (in_array('taskno', $cols)) $taskCol = 'taskno';
            echo "  - Table $tbl: task column = " . ($taskCol ?: 'NONE') . "\n";
        } catch (Exception $e) {
            echo "  - Table $tbl: NOT FOUND\n";
        }
    }
}
