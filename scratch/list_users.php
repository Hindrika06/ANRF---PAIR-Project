<?php
require_once __DIR__ . '/../config.php';
$stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "========================================================\n";
echo "           ANRF-PAIR PORTAL SYSTEM USER ACCOUNTS        \n";
echo "========================================================\n\n";

foreach ($users as $u) {
    echo "ID: " . str_pad($u['id'], 3) . " | Role: " . str_pad($u['role'], 12) . " | Prefix: " . str_pad($u['institute_prefix'], 8) . " | Username: " . $u['username'] . "\n";
}
