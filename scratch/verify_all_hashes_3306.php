<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=anrf;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$expectedMap = [
    'Idsathyan@cuk.ac.in'              => 'cuk@admin123',
    'anupkesavan@kannuriuniv.ac.in'    => 'kannur@admin123',
    'radhakrishnanek@mgu.ac.in'        => 'mgu@admin123',
    'vijjulatha@osmania.ac.in'         => 'ou@admin123',
    'balaji.meriga@gmail.com'          => 'svu@admin123',
    'sarma7@yogivemanauniversity.ac.in' => 'yvu@admin123',
    'admin@uoh.ac.in'                  => 'uoh@admin123',
    'superadmin@uoh.ac.in'             => 'superadmin@123',
    'superadmin'                       => 'superadmin@123'
];

$rows = $pdo->query("SELECT id, username, password FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

echo "========================================================\n";
echo "       INSPECTING PORT 3306 USER PASSWORDS             \n";
echo "========================================================\n\n";

foreach ($rows as $r) {
    $user = $r['username'];
    $expPass = $expectedMap[$user] ?? null;
    
    $isBcrypt = (substr($r['password'], 0, 4) === '$2y$' || substr($r['password'], 0, 4) === '$2a$' || substr($r['password'], 0, 4) === '$2b$');
    $verifyOk = $expPass ? (password_verify($expPass, $r['password']) || $expPass === $r['password']) : false;
    
    echo sprintf("ID: %-2d | Username: %-36s | IsBcrypt: %-3s | VerifyMatch: %s\n",
        $r['id'], $user, ($isBcrypt ? 'YES' : 'NO'), ($verifyOk ? 'YES [OK]' : 'NO [FAIL]'));
}
