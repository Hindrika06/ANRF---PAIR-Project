<?php
require_once __DIR__ . '/../admin/config/db.php';

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "SEARCHING ALL TABLES FOR '2026-08-20' OR '20 August':\n";
echo "=======================================================\n";

foreach ($tables as $t) {
    $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
    $textCols = [];
    foreach ($cols as $c) {
        $textCols[] = "`$c` LIKE '%2026-08-20%' OR `$c` LIKE '%August 20%' OR `$c` LIKE '%20 August%'";
    }
    if (!empty($textCols)) {
        $sql = "SELECT * FROM `$t` WHERE " . implode(' OR ', $textCols);
        try {
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                echo "FOUND IN TABLE `$t` (" . count($rows) . " rows):\n";
                foreach ($rows as $r) {
                    print_r($r);
                }
                echo "-------------------------------------------------------\n";
            }
        } catch (Exception $e) {
            // Ignore syntax errors on non-string columns
        }
    }
}
