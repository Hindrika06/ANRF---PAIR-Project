<?php
$dir = dirname(__DIR__);

function scanDirectory($dir, &$results) {
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..' || $f === 'vendor' || $f === '.git' || $f === 'scratch' || $f === 'node_modules') continue;
        $path = $dir . '/' . $f;
        if (is_dir($path)) {
            scanDirectory($path, $results);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            if (preg_match_all('/<i\s+class=["\']([^"\']+)["\']/i', $content, $m)) {
                foreach ($m[1] as $cls) {
                    $results['icons'][$cls][] = str_replace(dirname(__DIR__) . '/', '', $path);
                }
            }
            if (preg_match_all('/<img\s+[^>]*src=["\']([^"\']+)["\']/i', $content, $m2)) {
                foreach ($m2[1] as $src) {
                    $results['imgs'][$src][] = str_replace(dirname(__DIR__) . '/', '', $path);
                }
            }
        }
    }
}

$results = ['icons' => [], 'imgs' => []];
scanDirectory($dir, $results);

echo "=== ICON CLASSES FOUND (" . count($results['icons']) . " unique classes) ===\n";
foreach (array_slice($results['icons'], 0, 30) as $cls => $files) {
    $filesCount = count(array_unique($files));
    echo "- $cls (used in $filesCount files)\n";
}

echo "\n=== IMAGES FOUND (" . count($results['imgs']) . " unique srcs) ===\n";
foreach ($results['imgs'] as $src => $files) {
    $filesCount = count(array_unique($files));
    echo "- $src (used in $filesCount files)\n";
}
