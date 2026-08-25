<?php
require_once __DIR__ . '/../config.php';

echo "========================================================\n";
echo "           DATABASE USERS TABLE SCHEMA & STRUCTURE      \n";
echo "========================================================\n\n";

$columns = $pdo->query("DESCRIBE `users`")->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo sprintf("Column: %-20s | Type: %-25s | Null: %-5s | Default: %s\n", 
        $col['Field'], $col['Type'], $col['Null'], $col['Default'] ?? 'NULL');
}

echo "\n--------------------------------------------------------\n";
echo "              USERS TABLE RECORD COUNT & ROLES          \n";
echo "--------------------------------------------------------\n";

$rows = $pdo->query("SELECT id, username, role, institute_prefix, LENGTH(password) as hash_len, CHAR_LENGTH(username) as name_len FROM `users` ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    $hasLeadingSpace = ($r['username'] !== trim($r['username']));
    $hasNewlines = (strpos($r['username'], "\r") !== false || strpos($r['username'], "\n") !== false);
    
    echo sprintf("ID: %-3d | Username: %-36s | Role: %-11s | Prefix: %-6s | HashLen: %-3d | RawLen: %-3d | WhitespaceIssue: %s\n",
        $r['id'], 
        $r['username'], 
        $r['role'] ?? 'NULL', 
        $r['institute_prefix'] ?? 'NULL', 
        $r['hash_len'], 
        $r['name_len'],
        ($hasLeadingSpace || $hasNewlines) ? 'YES! [TRIM ERROR]' : 'NO'
    );
}

echo "\n========================================================\n";
