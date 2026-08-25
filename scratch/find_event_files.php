<?php
function scanDirRecursive($dir) {
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        if (is_dir($file)) {
            if (basename($file) !== 'vendor' && basename($file) !== 'scratch' && basename($file) !== '.git') {
                scanDirRecursive($file);
            }
        } else if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($file);
            if (stripos($content, 'events') !== false) {
                echo "File: $file\n";
                $lines = explode("\n", $content);
                foreach ($lines as $i => $line) {
                    if (preg_match('/(events|upcoming|ongoing|completed)/i', $line)) {
                        $trimmed = trim($line);
                        if (strlen($trimmed) > 120) $trimmed = substr($trimmed, 0, 120) . '...';
                        echo "  L" . ($i+1) . ": " . $trimmed . "\n";
                    }
                }
            }
        }
    }
}

scanDirRecursive(str_replace('\\', '/', __DIR__ . '/..'));
