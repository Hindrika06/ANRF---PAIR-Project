<?php
require_once 'auth_check.php';
require_once 'config/db.php';
require_once 'role_access.php';

if (!isSuperAdmin()) {
    header("Location: dashboard.php");
    exit();
}

// Generate CSRF Token for Form Security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$success = false;

// 1. HANDLE DELETE ACTION (POST ONLY with CSRF validation)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $userCsrf = $_POST['csrf_token'] ?? '';
    if (empty($userCsrf) || !hash_equals($_SESSION['csrf_token'], $userCsrf)) {
        $message = 'Security Error: Invalid or missing CSRF token.';
    } else {
        $delId = (int)$_POST['id'];
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$delId]);
        $uRole = $stmt->fetchColumn();

        if (in_array($uRole, ['super_admin', 'superadmin', 'hub_admin'], true)) {
            $message = 'Error: Cannot delete Hub Super Admin account.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$delId]);
            adminRedirect(['success_msg' => 'deleted']);
        }
    }
}

if (isset($_GET['success_msg'])) {
    $success = true;
}

// 2. HANDLE FORM SUBMISSIONS (ADD OR UPDATE)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    $userCsrf = $_POST['csrf_token'] ?? '';
    if (empty($userCsrf) || !hash_equals($_SESSION['csrf_token'], $userCsrf)) {
        $message = 'Security Error: Invalid or missing CSRF token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $institute_prefix = trim($_POST['institute_prefix'] ?? '');
        $edit_id = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

        if ($username === '' || !isValidPrefix($institute_prefix)) {
            $message = 'Please enter a valid username and institute prefix.';
        } else {
            if ($edit_id) {
                // Update Existing Spoke Admin
                $checkStmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
                $checkStmt->execute([$username, $edit_id]);
                if ($checkStmt->fetch()) {
                    $message = 'This username is already taken by another user.';
                } else {
                    if ($password !== '') {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('UPDATE users SET username = ?, password = ?, institute_prefix = ? WHERE id = ?');
                        $stmt->execute([$username, $hashedPassword, $institute_prefix, $edit_id]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE users SET username = ?, institute_prefix = ? WHERE id = ?');
                        $stmt->execute([$username, $institute_prefix, $edit_id]);
                    }
                    adminRedirect(['success_msg' => 'updated']);
                }
            } else {
                // Create New Spoke Admin
                if ($password === '') {
                    $message = 'Password is required for new accounts.';
                } else {
                    $checkStmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
                    $checkStmt->execute([$username]);
                    if ($checkStmt->fetch()) {
                        $message = 'This username is already registered.';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('INSERT INTO users (username, password, institute_prefix, role, created_at) VALUES (?, ?, ?, ?, NOW())');
                        $stmt->execute([$username, $hashedPassword, $institute_prefix, 'admin']);
                        adminRedirect(['success_msg' => 'inserted']);
                    }
                }
            }
        }
    }
}

$stmt = $pdo->query('SELECT id, username, institute_prefix, role, created_at FROM users ORDER BY id ASC');
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Spoke Admin Accounts</title>
    <link href="vendor/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>
<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">
            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Manage Spoke Admins</a></li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Success!</strong> Spoke Admin account updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($message !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Error:</strong> <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Spoke University Admin Accounts</h4>
                    <button class="btn btn-primary btn-sm" onclick="openAddAdminModal()">
                        <i class="fa fa-plus me-1"></i> Add Spoke Admin
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username / Email</th>
                                    <th>Assigned Institute</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                    <th style="width: 120px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$admin['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($admin['username']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars(getInstituteLabel($admin['institute_prefix'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= ($admin['role'] === 'super_admin' || $admin['role'] === 'superadmin') ? 'bg-primary' : 'bg-info' ?>">
                                                <?= htmlspecialchars(getRoleDisplayName($admin['role'])) ?>
                                            </span>
                                        </td>
                                        <td><small><?= htmlspecialchars($admin['created_at']) ?></small></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-info btn-xs me-1" onclick="viewAdmin(<?= htmlspecialchars(json_encode($admin)) ?>)">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-warning btn-xs me-1" onclick="openEditAdminModal(<?= htmlspecialchars(json_encode($admin)) ?>)">
                                                <i class="fa fa-pencil"></i> Edit
                                            </button>
                                            <?php if (!in_array($admin['role'], ['super_admin', 'superadmin', 'hub_admin'], true)): ?>
                                             <form method="POST" action="<?= buildNavUrl('manage_admins.php') ?>" style="display:inline-block;" id="deleteAdminForm_<?= (int)$admin['id'] ?>">
                                                 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                 <input type="hidden" name="action" value="delete">
                                                 <input type="hidden" name="id" value="<?= (int)$admin['id'] ?>">
                                                 <button type="button" class="btn btn-danger btn-xs" onclick="ANRFModal.confirm({ title: 'Delete Admin Account?', message: 'Are you sure you want to delete this Spoke Admin account? They will lose login access.', confirmText: 'Delete Account', onConfirm: function() { document.getElementById('deleteAdminForm_<?= (int)$admin['id'] ?>').submit(); } });">
                                                     <i class="fa fa-trash"></i> Delete
                                                 </button>
                                             </form>
                                             <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Form for Create / Edit Admin -->
<div class="modal fade" id="adminModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="adminForm" action="<?= buildNavUrl('manage_admins.php') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="edit_id" id="edit_id" value="">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="adminModalTitle">Add Spoke Admin Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="adminEditHeader" class="alert alert-info py-2 px-3 mb-3" style="display:none;">
                        <i class="fa fa-edit me-1"></i> <strong>EDIT MODE:</strong> Updating Spoke Admin Account #<span id="adminEditIdDisplay"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Username / Email *</label>
                        <input type="text" name="username" id="admin_username" class="form-control" placeholder="e.g. admin@uoh.ac.in" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold" id="passwordLabel">Password *</label>
                        <input type="password" name="password" id="admin_password" class="form-control" placeholder="Enter password...">
                        <small class="text-muted" id="passwordHelp">For new accounts, password is required.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Assigned Institute *</label>
                        <select name="institute_prefix" id="admin_institute_prefix" class="form-control" required>
                            <?php foreach ($adminAllowedPrefixes as $p): ?>
                                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars(getInstituteFullName($p)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="adminSubmitBtn"><i class="fa fa-plus-circle me-1"></i> Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>

<script>
    var adminModalInstance = null;

    function getAdminModalInstance() {
        var modalEl = document.getElementById('adminModal');
        if (!modalEl) return null;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            adminModalInstance = bootstrap.Modal.getInstance(modalEl);
            if (!adminModalInstance) {
                adminModalInstance = new bootstrap.Modal(modalEl);
            }
        }
        return adminModalInstance;
    }

    function openAddAdminModal() {
        document.getElementById('edit_id').value = '';
        document.getElementById('adminEditHeader').style.display = 'none';
        document.getElementById('admin_username').value = '';
        document.getElementById('admin_password').value = '';
        document.getElementById('admin_password').required = true;
        document.getElementById('passwordLabel').innerText = 'Password *';
        document.getElementById('passwordHelp').innerText = 'Password is required for new accounts.';
        document.getElementById('adminModalTitle').innerText = 'Add Spoke Admin Account';
        document.getElementById('adminSubmitBtn').innerHTML = '<i class="fa fa-plus-circle me-1"></i> Create Account';

        var modal = getAdminModalInstance();
        if (modal) modal.show();
        else if (typeof jQuery !== 'undefined') $('#adminModal').modal('show');
    }

    function openEditAdminModal(admin) {
        document.getElementById('edit_id').value = admin.id;
        document.getElementById('adminEditHeader').style.display = 'block';
        document.getElementById('adminEditIdDisplay').innerText = admin.id;

        document.getElementById('admin_username').value = admin.username || '';
        document.getElementById('admin_password').value = '';
        document.getElementById('admin_password').required = false;
        document.getElementById('passwordLabel').innerText = 'New Password (Optional)';
        document.getElementById('passwordHelp').innerText = 'Leave blank to keep existing password.';

        document.getElementById('admin_institute_prefix').value = admin.institute_prefix || 'cuk';
        document.getElementById('adminModalTitle').innerText = 'Edit Spoke Admin Account (ID: #' + admin.id + ')';
        document.getElementById('adminSubmitBtn').innerHTML = '<i class="fa fa-sync-alt me-1"></i> Update Account';

        var modal = getAdminModalInstance();
        if (modal) modal.show();
        else if (typeof jQuery !== 'undefined') $('#adminModal').modal('show');
    }

    function viewAdmin(admin) {
        openKpiRecordViewModal({
            moduleTitle: 'Spoke Admin Account Details',
            recordTitle: admin.username,
            institutePrefix: admin.institute_prefix || 'all',
            extraStatus: 'User #' + admin.id + ' (' + admin.role + ')',
            fields: [
                { label: 'Username / Email', value: admin.username, type: 'text', icon: 'fa-solid fa-user' },
                { label: 'Assigned Institute', value: (admin.institute_prefix || '').toUpperCase(), type: 'badge', icon: 'fa-solid fa-building-columns' },
                { label: 'Role Level', value: (admin.role === 'super_admin' || admin.role === 'superadmin') ? 'Hub Super Admin' : 'Spoke Institute Admin', type: 'text', icon: 'fa-solid fa-shield-alt' },
                { label: 'Account Created', value: admin.created_at || 'N/A', icon: 'fa-solid fa-clock' }
            ]
        });
    }
</script>
<?php include 'includes/view_modal.php'; ?>
</body>
</html>

