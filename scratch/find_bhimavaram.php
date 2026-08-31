<?php
$dir = dirname(__DIR__);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$found = [];

foreach ($files as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strpos($path, 'vendor') !== false || strpos($path, '.git') !== false || strpos($path, 'scratch') !== false) continue;
    
    $content = file_get_contents($path);
    if (stripos($content, 'bhimavaram') !== false) {
        $found[] = str_replace(dirname(__DIR__) . '/', '', $path);
    }
}

echo "Files containing 'Bhimavaram':\n";
foreach ($found as $f) {
    echo "- $f\n";
}
