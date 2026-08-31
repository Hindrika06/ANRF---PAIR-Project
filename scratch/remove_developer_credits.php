<?php
$dir = dirname(__DIR__);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$updated = 0;

$searchTarget = ' Developed by <a href="https://bhimavaramdigitals.com/" target="_blank" rel="noopener noreferrer" class="footer-dev-link">Bhimavaram Digitals ↗</a>';
$searchTarget2 = ' Developed by <a href="https://bhimavaramdigitals.com/" target="_blank" rel="noopener noreferrer" class="footer-dev-link">Bhimavaram Digitals</a>';

foreach ($files as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strpos($path, 'vendor') !== false || strpos($path, '.git') !== false || strpos($path, 'scratch') !== false) continue;

    $content = file_get_contents($path);
    if (stripos($content, 'bhimavaram') !== false) {
        $newContent = str_replace([$searchTarget, $searchTarget2], '', $content);
        
        // Regexp fallback for any remaining variation
        $newContent = preg_replace('/\s*Developed by\s*<a[^>]*bhimavaramdigitals[^>]*>.*?<\/a>/is', '', $newContent);
        
        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Updated: " . str_replace($dir . '/', '', $path) . "\n";
            $updated++;
        }
    }
}

echo "Finished updating. Total files cleaned: $updated\n";
