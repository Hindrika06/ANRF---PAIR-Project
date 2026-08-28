<?php
$dump = file_get_contents('C:/Users/HP/Downloads/anrf (5).sql');
$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

$taskSourceReport = [];

foreach ($prefixes as $p) {
    $prTable = "{$p}_progress_reports";
    $otherTables = [
        "{$p}_publications",
        "{$p}_webinars",
        "{$p}_conferences",
        "{$p}_internships",
        "{$p}_patents"
    ];

    // Task IDs in progress_reports
    $prTasks = [];
    if (preg_match_all("/INSERT INTO `{$prTable}`[^\(]*\((.*?)\)\s*VALUES\s*(.*?);/s", $dump, $m)) {
        // extract task_no column index
        $cols = array_map('trim', explode(',', str_replace('`', '', $m[1][0])));
        $taskIdx = array_search('task_no', $cols);
        if ($taskIdx !== false) {
            // parse values
            foreach ($m[2] as $valGroup) {
                // simple split by commas accounting for quotes
                preg_match_all("/'([^']*)'|(\d+)/", $valGroup, $valMatches);
                // Or just search task_no values
            }
        }
    }

    echo "Checking prefix: $p\n";
}
