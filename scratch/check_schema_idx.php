<?php
$schema = file_get_contents('c:/Temp/ANRF---PAIR-Project/schema.sql');
$prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

foreach ($prefixes as $p) {
    $tbl = "{$p}_progress_reports";
    if (preg_match("/CREATE TABLE IF NOT EXISTS `{$tbl}`.*?\)\s*ENGINE/s", $schema, $m)) {
        echo "Table $tbl def:\n" . $m[0] . "\n\n";
    }
}
