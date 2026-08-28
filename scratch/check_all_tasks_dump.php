<?php
$dump = file_get_contents('C:/Users/HP/Downloads/anrf (5).sql');
$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

foreach ($prefixes as $p) {
    echo "=== INSTITUTE: " . strtoupper($p) . " ===\n";
    $tables = [
        "{$p}_progress_reports" => 'task_no',
        "{$p}_publications"     => 'task_no',
        "{$p}_webinars"         => 'taskno',
        "{$p}_conferences"      => 'taskno',
        "{$p}_internships"      => 'task_no',
        "{$p}_patents"          => 'task_no',
    ];

    $allTasks = [];
    $prTasks = [];

    foreach ($tables as $tbl => $col) {
        if (preg_match_all("/INSERT INTO `{$tbl}`[^\(]*\((.*?)\)\s*VALUES\s*(.*?);/s", $dump, $m)) {
            $colsStr = $m[1][0];
            $cols = array_map(function($c) { return trim($c, " `\t\n\r"); }, explode(',', $colsStr));
            $colIdx = array_search($col, $cols);

            if ($colIdx !== false) {
                foreach ($m[2] as $valChunk) {
                    // Split rows
                    $rows = explode('),', $valChunk);
                    foreach ($rows as $r) {
                        $r = trim($r, " ()\t\n\r;");
                        if (empty($r)) continue;
                        // parse CSV row safely
                        $fields = str_getcsv($r, ',', "'");
                        if (isset($fields[$colIdx])) {
                            $val = trim($fields[$colIdx]);
                            if (!empty($val)) {
                                $allTasks[$val][] = $tbl;
                                if ($tbl === "{$p}_progress_reports") {
                                    $prTasks[$val] = true;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    echo "Progress Report Tasks: " . implode(', ', array_keys($prTasks)) . "\n";
    echo "All Found Tasks across modules:\n";
    foreach ($allTasks as $t => $tbls) {
        $inPr = isset($prTasks[$t]) ? 'YES' : 'NO';
        echo "  - Task ID: '$t' | In Progress Reports: $inPr | Found in: " . implode(', ', array_unique($tbls)) . "\n";
    }
    echo "\n";
}
