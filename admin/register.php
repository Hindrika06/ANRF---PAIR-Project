<?php
session_start();
// SEC-02 Remediation: Public Spoke Admin registration is strictly disabled.
// Spoke Admin account creation is managed exclusively by Hub Super Admin in manage_admins.php.
$_SESSION['login_error'] = 'Public registration is disabled. Spoke Admin accounts are created by Hub Super Admin.';
header("Location: index.php");
exit();
?>
