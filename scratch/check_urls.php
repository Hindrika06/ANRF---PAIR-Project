<?php
$urls = [
    'http://localhost/ANRF---PAIR-Project/',
    'http://localhost/ANRF---PAIR-Project/login.php',
    'http://localhost/ANRF---PAIR-Project/admin/publications.php',
    'http://localhost/ANRF---PAIR-Project/admin/login.php',
    'http://localhost/ANRF-PAIR-Project/',
    'http://localhost/ANRF-PAIR-Project/admin/publications.php',
    'http://localhost/anrf/',
    'http://localhost/anrf/admin/publications.php'
];

foreach ($urls as $u) {
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $h = @get_headers($u, false, $ctx);
    echo $u . " -> " . ($h ? $h[0] : "NO RESPONSE") . "\n";
}
