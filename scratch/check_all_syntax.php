<?php
$dir = dirname(__DIR__);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$count = 0;
$errors = 0;

foreach ($files as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    if (strpos($path, 'vendor') !== false || strpos($path, 'scratch') !== false) continue;
    $count++;
    $out = [];
    $ret = 0;
    exec('php -l ' . escapeshellarg($path), $out, $ret);
    if ($ret !== 0) {
        echo "SYNTAX ERROR IN: " . $path . "\n";
        $errors++;
    }
}
echo "Checked $count PHP files. Total Syntax Errors: $errors\n";
