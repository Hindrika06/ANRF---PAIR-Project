<?php
require_once 'c:/Temp/ANRF---PAIR-Project/admin/config/db.php';

$stmt = $pdo->query("SELECT id, task_no FROM kannur_progress_reports");
echo "=== KANNUR PROGRESS REPORTS ===\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmtPub = $pdo->query("SELECT id, task_no FROM kannur_publications");
echo "\n=== KANNUR PUBLICATIONS ===\n";
print_r($stmtPub->fetchAll(PDO::FETCH_ASSOC));
