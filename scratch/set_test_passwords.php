<?php
require_once 'c:/Temp/ANRF---PAIR-Project/admin/config/db.php';

$hash = password_hash('admin123', PASSWORD_BCRYPT);

// Update Spoke Admin CUK (Idsathyan@cuk.ac.in)
$pdo->prepare("UPDATE users SET password = ? WHERE username = 'Idsathyan@cuk.ac.in'")->execute([$hash]);

// Update Super Admin (superadmin)
$pdo->prepare("UPDATE users SET password = ? WHERE username = 'superadmin'")->execute([$hash]);

echo "Updated test user passwords to 'admin123' successfully.\n";
