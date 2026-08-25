<?php
require_once 'c:/Temp/ANRF---PAIR-Project/config.php';
$pdo->exec("DELETE FROM `cuk_progress_report_publications` WHERE progress_report_id = 4");
$pdo->exec("DELETE FROM `cuk_progress_report_capacity_events` WHERE progress_report_id = 4");
echo "Cleaned orphan rows for report 4.\n";
