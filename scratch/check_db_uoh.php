<?php
require_once 'c:/Temp/ANRF---PAIR-Project/admin/config/db.php';

$stmt = $pdo->query("SELECT id, task_no, project_title, created_at FROM uoh_progress_reports");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== DB ROWS IN uoh_progress_reports ===\n";
print_r($rows);
