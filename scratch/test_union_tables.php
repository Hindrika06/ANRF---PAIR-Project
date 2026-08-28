<?php
require_once 'c:/Temp/ANRF---PAIR-Project/admin/config/db.php';

$prefix = 'kannur';
$table = "{$prefix}_progress_reports";

$sqlUnion = "
    SELECT CONVERT(task_no USING utf8mb4) AS task_no FROM `$table` WHERE task_no IS NOT NULL AND task_no != ''
    UNION
    SELECT CONVERT(task_no USING utf8mb4) AS task_no FROM `{$prefix}_publications` WHERE task_no IS NOT NULL AND task_no != ''
    UNION
    SELECT CONVERT(taskno USING utf8mb4) AS task_no FROM `{$prefix}_conferences` WHERE taskno IS NOT NULL AND taskno != ''
    UNION
    SELECT CONVERT(taskno USING utf8mb4) AS task_no FROM `{$prefix}_webinars` WHERE taskno IS NOT NULL AND taskno != ''
    UNION
    SELECT CONVERT(task_no USING utf8mb4) AS task_no FROM `{$prefix}_internships` WHERE task_no IS NOT NULL AND task_no != ''
    UNION
    SELECT CONVERT(task_no USING utf8mb4) AS task_no FROM `{$prefix}_patents` WHERE task_no IS NOT NULL AND task_no != ''
    ORDER BY task_no ASC
";

try {
    $stmtTasks = $pdo->query($sqlUnion);
    $availableTaskNos = $stmtTasks->fetchAll(PDO::FETCH_COLUMN);
    echo "Combined UNION OK:\n";
    print_r($availableTaskNos);
} catch (Exception $e) {
    echo "Combined UNION FAILED: " . $e->getMessage() . "\n";
}
