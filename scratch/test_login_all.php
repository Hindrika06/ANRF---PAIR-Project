<?php
require_once __DIR__ . '/../config.php';

$testCases = [
    ['superadmin@uoh.ac.in', 'superadmin@123'],
    ['superadmin@uoh.ac.in', 'admin123'],
    ['superadmin', 'superadmin@123'],
    ['superadmin', 'admin123']
];

foreach ($testCases as $tc) {
    list($user, $pass) = $tc;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    $ok = $u && password_verify($pass, $u['password']);
    echo "User '$user' with pass '$pass': " . ($ok ? "SUCCESS" : "FAILED") . "\n";
}
