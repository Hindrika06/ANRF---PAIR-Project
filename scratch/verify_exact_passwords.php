<?php
require_once __DIR__ . '/../config.php';

$expectedMap = [
    'Idsathyan@cuk.ac.in'             => 'cuk@admin123',
    'anupkesavan@kannuriuniv.ac.in'   => 'kannur@admin123',
    'radhakrishnanek@mgu.ac.in'       => 'mgu@admin123',
    'vijjulatha@osmania.ac.in'        => 'ou@admin123',
    'balaji.meriga@gmail.com'         => 'svu@admin123',
    'sarma7@yogivemanauniversity.ac.in'=> 'yvu@admin123',
    'admin@uoh.ac.in'                 => 'uoh@admin123',
    'superadmin@uoh.ac.in'            => 'superadmin@123',
    'superadmin'                      => 'superadmin@123'
];

$stmt = $pdo->query("SELECT id, username, role, institute_prefix, password FROM users ORDER BY id ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "========================================================\n";
echo "           CREDENTIAL VERIFICATION RESULTS             \n";
echo "========================================================\n\n";

foreach ($rows as $r) {
    $expPass = $expectedMap[$r['username']] ?? 'N/A';
    $ok = password_verify($expPass, $r['password']);
    echo sprintf("ID: %-2d | Role: %-11s | Prefix: %-6s | Username: %-36s | Password: %-18s | Status: %s\n",
        $r['id'], $r['role'], strtoupper($r['institute_prefix']), $r['username'], $expPass, ($ok ? "VERIFIED [OK]" : "FAILED [ERR]"));
}
