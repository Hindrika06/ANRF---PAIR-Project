<?php
require 'scratch/full_qa_suite.php';
echo "\n--- FAILED TEST ANALYSIS ---\n";
foreach ($testResults as $t) {
    if ($t['status'] === 'FAIL') {
        echo "ID: {$t['id']}\n";
        echo "Name: {$t['name']}\n";
        echo "Actual: {$t['actual']}\n";
        echo "Evidence: {$t['evidence']}\n";
        echo "-----------------------------------\n";
    }
}
