<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=anrf;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$accounts = [
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

echo "========================================================\n";
echo "      UPDATING BCRYPT PASSWORDS FOR ALL 9 ACCOUNTS     \n";
echo "========================================================\n\n";

foreach ($accounts as $user => $pass) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = :hash WHERE username = :user");
    $stmt->execute([':hash' => $hash, ':user' => $user]);
    echo "Updated [{$user}]\n";
}

echo "\n--------------------------------------------------------\n";
echo "                 VERIFYING ALL HASHES NOW              \n";
echo "--------------------------------------------------------\n";

$passCount = 0;
$failCount = 0;

foreach ($accounts as $user => $pass) {
    $stmt = $pdo->prepare("SELECT id, username, password, role, institute_prefix FROM users WHERE username = :user");
    $stmt->execute([':user' => $user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($pass, $row['password'])) {
        $passCount++;
        echo sprintf(" ✅ VERIFIED: %-36s | Role: %-11s | Prefix: %s\n", $user, $row['role'], strtoupper($row['institute_prefix']));
    } else {
        $failCount++;
        echo sprintf(" ❌ FAILED:   %-36s\n", $user);
    }
}

echo "\n========================================================\n";
echo sprintf("TOTAL VERIFIED: %d | FAILED: %d\n", $passCount, $failCount);
echo "========================================================\n";
