<?php
$dump = file_get_contents('C:/Users/HP/Downloads/anrf (5).sql');
if (preg_match_all("/INSERT INTO `users`[^\(]*\((.*?)\)\s*VALUES\s*(.*?);/s", $dump, $m)) {
    echo "Users columns: " . $m[1][0] . "\n";
    echo "Users values: " . $m[2][0] . "\n";
}
