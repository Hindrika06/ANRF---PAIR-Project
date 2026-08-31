<?php
require_once __DIR__ . '/../config.php';

$users = [
    ['Idsathyan@cuk.ac.in', 'cuk@admin123', 'cuk'],
    ['anupkesavan@kannuriuniv.ac.in', 'kannur@admin123', 'kannur'],
    ['radhakrishnanek@mgu.ac.in', 'mgu@admin123', 'mgu'],
    ['vijjulatha@osmania.ac.in', 'ou@admin123', 'ou'],
    ['balaji.meriga@gmail.com', 'svu@admin123', 'svu'],
    ['sarma7@yogivemanauniversity.ac.in', 'yvu@admin123', 'yvu'],
    ['admin@uoh.ac.in', 'uoh@admin123', 'uoh'],
    ['superadmin@uoh.ac.in', 'superadmin@123', 'uoh']
];

foreach ($users as $u) {
    $stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
    $stmt->execute([$u[0]]);
    $row = $stmt->fetch();
    if ($row) {
        $ok = password_verify($u[1], $row['password']) || $u[1] === $row['password'];
        echo $u[0] . ' => ' . ($ok ? 'VALID' : 'INVALID') . "\n";
        if (!$ok) {
            // Update password hash to make sure login succeeds
            $newHash = password_hash($u[1], PASSWORD_DEFAULT);
            $up = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $up->execute([$newHash, $row['id']]);
            echo "  Updated password hash for " . $u[0] . "\n";
        }
    } else {
        echo $u[0] . ' => NOT FOUND, inserting...' . "\n";
        $newHash = password_hash($u[1], PASSWORD_DEFAULT);
        $role = str_contains($u[0], 'superadmin') ? 'superadmin' : 'admin';
        $ins = $pdo->prepare('INSERT INTO users (username, password, institute_prefix, role) VALUES (?, ?, ?, ?)');
        $ins->execute([$u[0], $newHash, $u[2], $role]);
    }
}
