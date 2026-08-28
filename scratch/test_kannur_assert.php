<?php
require_once 'c:/Temp/ANRF---PAIR-Project/scratch/run_full_uat_suite.php';

$res = httpReq("$baseUrl/admin/progress_reports.php?tab_token=$superToken&prefix=kannur&task_no=19", $superCookies);

echo "HTML CONTAINS NOTICE: " . (strpos($res['body'], 'No Progress Report entry exists yet') !== false ? 'YES' : 'NO') . "\n";
echo "HTML CONTAINS VALUE 19: " . (strpos($res['body'], 'value="19"') !== false ? 'YES' : 'NO') . "\n";

if (strpos($res['body'], 'No Progress Report entry exists yet') === false) {
    echo "--- HTML BODY NEAR ALERT ---\n";
    echo substr($res['body'], strpos($res['body'], 'workflow-card'), 2000);
}
