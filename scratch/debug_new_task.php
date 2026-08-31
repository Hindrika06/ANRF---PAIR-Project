<?php
require_once 'c:/Temp/ANRF---PAIR-Project/scratch/run_full_uat_suite.php';

$newTaskReq = httpReq("$baseUrl/admin/progress_reports.php?tab_token=$superToken&prefix=kannur&task_no=19", $superCookies);
echo "=== NEW TASK HTML BODY SNIPPET ===\n";
echo substr($newTaskReq['body'], strpos($newTaskReq['body'], 'Task ID Selection'), 1500);
