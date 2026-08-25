<?php
require_once __DIR__ . '/../config.php';

$credentialsMap = [
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

echo "========================================================\n";
echo "    SETTING EXACT SPECIFIED CREDENTIALS IN DATABASE    \n";
echo "========================================================\n\n";

foreach ($credentialsMap as $user => $pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = :hash WHERE username = :user");
    $stmt->execute([':hash' => $hash, ':user' => $user]);
    echo "Updated [{$user}] -> Password: {$pass}\n";
}

// Update update_passwords.sql as well for persistence reference
$sqlContent = "-- SQL script to set exact requested credentials\n";
foreach ($credentialsMap as $user => $pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $sqlContent .= "UPDATE `users` SET `password` = '$hash' WHERE `username` = '$user';\n";
}
file_put_contents(__DIR__ . '/../update_passwords.sql', $sqlContent);

echo "\nDatabase updated and update_passwords.sql written successfully!\n";
