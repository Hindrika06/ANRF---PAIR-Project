<?php
require_once __DIR__ . '/../config.php';

$users = $pdo->query("SELECT id, username, role, institute_prefix, password FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$passCandidates = [
    'admin123',
    'superadmin123',
    'admin',
    'password',
    'cuk123',
    'kannur123',
    'mgu123',
    'ou123',
    'svu123',
    'uoh123',
    'yvu123',
    '123456'
];

echo "========================================================\n";
echo "            USER ACCOUNT CREDENTIAL VERIFIER            \n";
echo "========================================================\n\n";

foreach ($users as $u) {
    $foundPass = null;
    foreach ($passCandidates as $cand) {
        if (password_verify($cand, $u['password'])) {
            $foundPass = $cand;
            break;
        }
    }
    echo "ID: " . str_pad($u['id'], 2) . " | Role: " . str_pad($u['role'], 12) . " | Univ: " . str_pad(strtoupper($u['institute_prefix']), 6) . " | Username: " . str_pad($u['username'], 32) . " | Password: " . ($foundPass ?: "UNKNOWN HASH") . "\n";
}
