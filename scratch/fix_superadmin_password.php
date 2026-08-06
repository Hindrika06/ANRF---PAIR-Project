<?php
require_once __DIR__ . '/../config.php';

$newPassword = 'superadmin@123';
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'superadmin@uoh.ac.in'");
$stmt->execute([$newHash]);

echo "Updated password for superadmin@uoh.ac.in to '$newPassword'.\n";

// Also check if superadmin username can be 'superadmin'
$stmt2 = $pdo->prepare("SELECT * FROM users WHERE username = 'superadmin'");
$stmt2->execute();
$row = $stmt2->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    // Add alias or duplicate account for username 'superadmin' if needed
    $stmt3 = $pdo->prepare("INSERT INTO users (username, password, institute_prefix, role) VALUES (?, ?, 'uoh', 'super_admin')");
    $stmt3->execute(['superadmin', $newHash]);
    echo "Created user 'superadmin' with role 'super_admin'.\n";
} else {
    $stmt4 = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'superadmin'");
    $stmt4->execute([$newHash]);
    echo "Updated password for 'superadmin'.\n";
}
