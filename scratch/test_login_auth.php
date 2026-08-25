<?php
require_once __DIR__ . '/../config.php';

$requestedCredentials = [
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
echo "      TESTING LOGIN AUTHENTICATION FOR ALL CREDENTIALS  \n";
echo "========================================================\n\n";

foreach ($requestedCredentials as $inputUser => $inputPass) {
    $stmt = $pdo->prepare("SELECT id, username, password, institute_prefix, role FROM users WHERE LOWER(username) = LOWER(?) OR username = ?");
    $stmt->execute([$inputUser, $inputUser]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "❌ USER NOT FOUND: '$inputUser'\n";
        continue;
    }

    $authSuccess = (password_verify($inputPass, $row['password']) || $inputPass === $row['password']);
    
    if ($authSuccess) {
        echo "✅ AUTH SUCCESS: '$inputUser' (DB User: '{$row['username']}', Role: '{$row['role']}', Prefix: '{$row['institute_prefix']}')\n";
    } else {
        echo "❌ PASSWORD MISMATCH: '$inputUser' (DB User: '{$row['username']}', Input Pass: '$inputPass')\n";
    }
}
