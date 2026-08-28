<?php
require_once 'c:/Temp/ANRF---PAIR-Project/admin/config/db.php';

function getTaskKpiSummary($pdo, $prefix, $taskNo) {
    $summary = [
        'publications' => [],
        'conferences' => [],
        'webinars' => [],
        'internships' => [],
        'patents' => []
    ];

    // Publications
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_publications` WHERE task_no = :t ORDER BY id DESC");
        $stmt->execute([':t' => $taskNo]);
        $summary['publications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Conferences
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_conferences` WHERE taskno = :t ORDER BY id DESC");
        $stmt->execute([':t' => $taskNo]);
        $summary['conferences'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Webinars
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_webinars` WHERE taskno = :t ORDER BY id DESC");
        $stmt->execute([':t' => $taskNo]);
        $summary['webinars'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Internships
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_internships` WHERE task_no = :t ORDER BY id DESC");
        $stmt->execute([':t' => $taskNo]);
        $summary['internships'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Patents
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_patents` WHERE task_no = :t ORDER BY id DESC");
        $stmt->execute([':t' => $taskNo]);
        $summary['patents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    return $summary;
}

echo "=== CUK Task 4.5 KPI Summary ===\n";
print_r(getTaskKpiSummary($pdo, 'cuk', 'Task 4.5'));

echo "\n=== UOH Task 2 KPI Summary ===\n";
print_r(getTaskKpiSummary($pdo, 'uoh', '2'));
