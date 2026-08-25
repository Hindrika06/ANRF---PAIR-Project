<?php
require_once __DIR__ . '/../config.php';

$users = $pdo->query("SELECT id, username, role, institute_prefix, password FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$candidates = [
    'admin123',
    'Admin123',
    'Admin@123',
    'admin@123',
    'pair123',
    'anrf123',
    'anrfpair',
    'ANRFPAIR',
    'password123',
    'cuk123',
    'kannur123',
    'mgu123',
    'ou123',
    'svu123',
    'uoh123',
    'yvu123',
    'cuk',
    'kannur',
    'mgu',
    'ou',
    'svu',
    'uoh',
    'yvu',
    'Idsathyan',
    'anupkesavan',
    'radhakrishnanek',
    'vijjulatha',
    'balaji.meriga',
    'admin@uoh.ac.in',
    'superadmin@uoh.ac.in',
    'sarma7'
];

foreach ($users as $u) {
    $match = 'UNKNOWN';
    foreach ($candidates as $c) {
        if (password_verify($c, $u['password']) || $c === $u['password']) {
            $match = $c;
            break;
        }
    }
    echo sprintf("ID: %-2d | Role: %-12s | Prefix: %-7s | Username: %-36s | Password: %s\n", 
        $u['id'], $u['role'], strtoupper($u['institute_prefix']), $u['username'], $match);
}
