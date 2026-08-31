<?php
function lintDirectory($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $errorCount = 0;
    $checkedCount = 0;

    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        if ($file->getExtension() !== 'php') continue;
        if (strpos($file->getPathname(), 'vendor') !== false) continue;

        $path = $file->getPathname();
        $checkedCount++;
        $cmd = sprintf('C:\xampp\php\php.exe -l "%s" 2>&1', $path);
        $output = shell_exec($cmd);

        if (strpos($output, 'No syntax errors detected') === false) {
            echo "SYNTAX ERROR in $path:\n$output\n";
            $errorCount++;
        }
    }

    echo "Linted $checkedCount PHP files. Total syntax errors: $errorCount\n";
}

lintDirectory('C:\Temp\ANRF---PAIR-Project');
