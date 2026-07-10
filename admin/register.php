<?php
session_start();
require_once 'config/db.php';
require_once 'role_access.php';

$error = '';
success:
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $institute_prefix = trim($_POST['institute_prefix'] ?? '');

    if ($email === '' || $password === '' || $confirm_password === '' || !isValidPrefix($institute_prefix)) {
        $error = 'Please complete all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, institute_prefix, role, created_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$email, $hashedPassword, $institute_prefix, 'admin']);
            header('Location: index.php?registered=1');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register | Spoken Institute</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="vendor/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        body { background:#024283; font-family:Segoe UI,sans-serif; }
        .card { border:none; padding:25px; box-shadow:0 10px 30px rgba(0,0,0,.15); }
        h3 { color:#bc2121; font-weight:bold; }
        h4 { color:#555; margin-bottom:25px; text-transform:uppercase; }
        .form-control { height:48px; border-radius:0; }
        .btn-primary { width:100%; height:48px; border-radius:0; background:#bc2121; border:none; font-weight:600; }
        .btn-primary:hover { background:#991818; }
        .alert-error { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; padding:10px; margin-bottom:15px; text-align:center; }
    </style>
</head>
<body>
<div class="fix-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-6">
                <div class="card">
                    <div class="text-center mb-4">
                        <img src="logo/logo.png" class="logo-auth" alt="">
                        <h3>SPOKEN INSTITUTE</h3>
                        <h4>Register Admin Account</h4>
                    </div>
                    <?php if ($error !== ''): ?>
                        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
                        </div>
                        <div class="mb-3">
                            <label>Institute</label>
                            <select name="institute_prefix" class="form-control" required>
                                <option value="">Select Institute</option>
                                <?php foreach ($adminAllowedPrefixes as $prefix): ?>
                                    <option value="<?= htmlspecialchars($prefix) ?>"><?= htmlspecialchars(getInstituteLabel($prefix)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
                        </div>
                        <div class="mb-4">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                    <div style="margin-top:16px; text-align:center;">
                        <span style="color:#ffffff; opacity:.85;">Already registered?</span>
                        <a href="index.php" style="color:#ffffff; text-decoration:underline;">Sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
