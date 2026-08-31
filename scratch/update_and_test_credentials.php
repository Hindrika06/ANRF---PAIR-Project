<?php
require_once __DIR__ . '/../config.php';

$credentials = [
    [
        'username' => 'Idsathyan@cuk.ac.in',
        'password' => 'cuk@admin123',
        'prefix'   => 'cuk',
        'role'     => 'admin'
    ],
    [
        'username' => 'anupkesavan@kannuriuniv.ac.in',
        'password' => 'kannur@admin123',
        'prefix'   => 'kannur',
        'role'     => 'admin'
    ],
    [
        'username' => 'radhakrishnanek@mgu.ac.in',
        'password' => 'mgu@admin123',
        'prefix'   => 'mgu',
        'role'     => 'admin'
    ],
    [
        'username' => 'vijjulatha@osmania.ac.in',
        'password' => 'ou@admin123',
        'prefix'   => 'ou',
        'role'     => 'admin'
    ],
    [
        'username' => 'balaji.meriga@gmail.com',
        'password' => 'svu@admin123',
        'prefix'   => 'svu',
        'role'     => 'admin'
    ],
    [
        'username' => 'sarma7@yogivemanauniversity.ac.in',
        'password' => 'yvu@admin123',
        'prefix'   => 'yvu',
        'role'     => 'admin'
    ],
    [
        'username' => 'admin@uoh.ac.in',
        'password' => 'uoh@admin123',
        'prefix'   => 'uoh',
        'role'     => 'admin'
    ],
    [
        'username' => 'superadmin@uoh.ac.in',
        'password' => 'superadmin@123',
        'prefix'   => 'uoh',
        'role'     => 'super_admin'
    ]
];

echo "=== UPDATING CREDENTIALS IN DATABASE ===\n\n";

foreach ($credentials as $cred) {
    $user = $cred['username'];
    $pass = $cred['password'];
    $prefix = $cred['prefix'];
    $role = $cred['role'];
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $update = $pdo->prepare("UPDATE users SET password = ?, role = ?, institute_prefix = ? WHERE username = ?");
        $update->execute([$hash, $role, $prefix, $user]);
        echo "[UPDATED] User: $user | Role: $role | Prefix: $prefix\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (username, password, institute_prefix, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $insert->execute([$user, $hash, $prefix, $role]);
        echo "[INSERTED] User: $user | Role: $role | Prefix: $prefix\n";
    }
}

echo "\n=== VERIFYING ALL CREDENTIALS ===\n\n";

$allPassed = true;

foreach ($credentials as $cred) {
    $user = $cred['username'];
    $pass = $cred['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "[FAIL] User $user not found in DB!\n";
        $allPassed = false;
        continue;
    }

    if (password_verify($pass, $row['password'])) {
        echo "[OK] Username: $user | Role: {$row['role']} | Prefix: {$row['institute_prefix']} | Password verified!\n";
    } else {
        echo "[FAIL] Username: $user | Password verification failed!\n";
        $allPassed = false;
    }
}

if ($allPassed) {
    echo "\n=== ALL CREDENTIALS UPDATED & VERIFIED SUCCESSFULLY! ===\n";
} else {
    echo "\n[ERROR] Verification failed for some credentials.\n";
}
