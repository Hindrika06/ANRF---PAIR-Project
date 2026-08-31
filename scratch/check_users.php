<?php
require_once __DIR__ . '/../config.php';

try {
    $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in 'users':\n";
    foreach ($cols as $c) {
        echo " - " . $c['Field'] . " (" . $c['Type'] . ")\n";
    }

    echo "\nAll User Records:\n";
    $users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        print_r($u);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
