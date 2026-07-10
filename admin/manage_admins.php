<?php
session_start();
require_once 'config/db.php';
require_once 'role_access.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix'])) {
    header('Location: index.php');
    exit();
}

if (!isSuperAdmin()) {
    die('Only the super admin can manage other admin accounts.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $institute_prefix = trim($_POST['institute_prefix'] ?? '');

    if ($username === '' || $password === '' || !isValidPrefix($institute_prefix)) {
        $message = 'Please enter a valid username, password, and institute.';
    } else {
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $checkStmt->execute([$username]);
        if ($checkStmt->fetch()) {
            $message = 'This username is already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, institute_prefix, role, created_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$username, $hashedPassword, $institute_prefix, 'admin']);
            $message = 'Admin account created successfully.';
        }
    }
}

$stmt = $pdo->query('SELECT id, username, institute_prefix, created_at FROM users ORDER BY id');
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Admin Accounts</title>
    <link href="vendor/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>
<div class="content-body" style="margin-left: 260px; padding: 24px;">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Manage Admin Accounts</h4></div>
            <div class="card-body">
                <?php if ($message !== ''): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                <form method="post" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Institute</label>
                        <select name="institute_prefix" class="form-control" required>
                            <?php foreach ($adminAllowedPrefixes as $prefix): ?>
                                <option value="<?= htmlspecialchars($prefix) ?>"><?= htmlspecialchars(getInstituteLabel($prefix)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Create Admin</button>
                    </div>
                </form>
                <hr>
                <table class="table table-striped mt-3">
                    <thead>
                        <tr><th>ID</th><th>Username</th><th>Institute</th><th>Created</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?= (int)$admin['id'] ?></td>
                                <td><?= htmlspecialchars($admin['username']) ?></td>
                                <td><?= htmlspecialchars(getInstituteLabel($admin['institute_prefix'])) ?></td>
                                <td><?= htmlspecialchars($admin['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
