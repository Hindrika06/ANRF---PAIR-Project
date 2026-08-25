<?php
require_once __DIR__ . '/../admin/config/db.php';
date_default_timezone_set('Asia/Kolkata');

$nowStr = date('Y-m-d H:i:s');
echo "CURRENT SERVER TIME (IST): $nowStr\n\n";

$stmt = $pdo->query("SELECT * FROM homepage_banners ORDER BY id DESC");
$banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "HOMEPAGE BANNERS TABLE DUMP:\n";
echo "====================================================================================================\n";
foreach ($banners as $b) {
    $start = $b['start_datetime'] ?: 'NULL';
    $end = $b['end_datetime'] ?: 'NULL';
    
    // Eligibility calculation
    $isStatusActive = ($b['status'] === 'Active');
    $isStartOk = (empty($b['start_datetime']) || $nowStr >= $b['start_datetime']);
    $isEndOk = (empty($b['end_datetime']) || $nowStr < $b['end_datetime']);
    $eligible = ($isStatusActive && $isStartOk && $isEndOk) ? 'YES (SHOW ON HOME)' : 'NO (EXCLUDE)';

    echo sprintf("ID: #%-3d | Title: %-35s | Inst: %-6s | Status: %-8s | Start: %-19s | End: %-19s | Eligible: %s\n",
        $b['id'], substr($b['title'] ?: $b['caption'] ?: 'Untitled', 0, 35), $b['institute_prefix'], $b['status'], $start, $end, $eligible);
}
echo "====================================================================================================\n";
