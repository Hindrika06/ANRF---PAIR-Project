<?php
$files = [
    'admin/conferences.php',
    'admin/event_calendar.php',
    'admin/gallery.php',
    'admin/internships.php',
    'admin/patents.php',
    'admin/publications.php',
    'admin/team_management.php',
    'admin/webinars.php'
];

$dir = dirname(__DIR__);
foreach ($files as $rel) {
    $path = $dir . '/' . $rel;
    if (!file_exists($path)) continue;
    $lines = file($path);
    foreach ($lines as $i => $line) {
        if (stripos($line, 'bhimavaram') !== false) {
            echo "$rel Line " . ($i + 1) . ": " . trim($line) . "\n";
        }
    }
}
