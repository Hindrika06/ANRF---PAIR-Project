<?php
require_once __DIR__ . '/../config.php';

$desiredCredentials = [
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
echo "       RE-HASHING & RE-SETTING ALL EXACT CREDENTIALS    \n";
echo "========================================================\n\n";

foreach ($desiredCredentials as $username => $plainPass) {
    // Generate fresh password hash using PASSWORD_DEFAULT (bcrypt)
    $hash = password_hash($plainPass, PASSWORD_DEFAULT);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?) OR username = ?");
    $stmt->execute([$username, $username]);
    $userRow = $stmt->fetch();

    if ($userRow) {
        $updateStmt = $pdo->prepare("UPDATE users SET password = :hash WHERE id = :id");
        $updateStmt->execute([':hash' => $hash, ':id' => $userRow['id']]);
        echo "Updated User ID {$userRow['id']} ($username) -> Hash set for '$plainPass'\n";
    } else {
        echo "WARNING: User '$username' not found in database!\n";
    }
}

echo "\n--------------------------------------------------------\n";
echo "         VERIFYING LOGIN AUTHENTICATION NOW            \n";
echo "--------------------------------------------------------\n";

$passCount = 0;
$failCount = 0;

foreach ($desiredCredentials as $username => $plainPass) {
    $stmt = $pdo->prepare("SELECT id, username, password, institute_prefix, role FROM users WHERE LOWER(username) = LOWER(?) OR username = ?");
    $stmt->execute([$username, $username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && (password_verify($plainPass, $row['password']) || $plainPass === $row['password'])) {
        $passCount++;
        echo " [PASS] Username: '$username' | Password: '$plainPass' | Role: {$row['role']} | Prefix: {$row['institute_prefix']}\n";
    } else {
        $failCount++;
        echo " [FAIL] Username: '$username' | Password: '$plainPass'\n";
    }
}

echo "\n========================================================\n";
echo "SUMMARY: TOTAL {$passCount} PASSED | {$failCount} FAILED\n";
echo "========================================================\n";
