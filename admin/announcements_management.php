<?php
require_once 'auth_check.php';
require_once 'role_access.php';

// Auth Guard: Only Super Admin can manage announcements ticker
if (!isSuperAdmin()) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/db.php';

$success = false;
$error   = '';

// Self-healing: create table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `announcements` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(500) NOT NULL,
            `link` VARCHAR(500) DEFAULT '',
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
} catch (PDOException $e) {
    // ignore database create errors
}

// 1. HANDLE DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM `announcements` WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        header("Location: announcements_management.php?success_msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to delete ticker: ' . $e->getMessage();
    }
}

// 2. SHOW QUICK SUCCESS MESSAGES POST-REDIRECT
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title'] ?? '');
    $link      = trim($_POST['link'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $edit_id   = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if (empty($title)) {
        $error = 'Announcement text/title is required.';
    } else {
        try {
            if ($edit_id) {
                // Update
                $stmt = $pdo->prepare("UPDATE `announcements` SET title = :title, link = :link, is_active = :is_active WHERE id = :id");
                $stmt->execute([
                    ':title'     => $title,
                    ':link'      => $link,
                    ':is_active' => $is_active,
                    ':id'        => $edit_id
                ]);
                header("Location: announcements_management.php?success_msg=updated");
                exit;
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO `announcements` (title, link, is_active) VALUES (:title, :link, :is_active)");
                $stmt->execute([
                    ':title'     => $title,
                    ':link'      => $link,
                    ':is_active' => $is_active
                ]);
                header("Location: announcements_management.php?success_msg=inserted");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch all announcements
$announcements = [];
try {
    $stmt = $pdo->query("SELECT * FROM `announcements` ORDER BY id DESC");
    $announcements = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignore error
}

$pageTitle = "Announcements Management | ANRF-PAIR";
?>
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
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Announcements</a></li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Announcements updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Ticker Announcements & "What's New"</h4>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
                        <i class="fa fa-plus me-1"></i> Add Announcement
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Message / Title</th>
                                    <th>Link Target</th>
                                    <th style="width: 100px;">Status</th>
                                    <th style="width: 120px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($announcements)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No custom ticker announcements added. Fallback static ticker values will display on the home screen.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($announcements as $a): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($a['title']) ?></strong></td>
                                        <td>
                                            <?php if (!empty($a['link'])): ?>
                                                <a href="../<?= htmlspecialchars($a['link']) ?>" target="_blank" class="text-info"><?= htmlspecialchars($a['link']) ?></a>
                                            <?php else: ?>
                                                <span class="text-muted">(No link)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $a['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $a['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-xs me-1" onclick="openEditModal(<?= htmlspecialchars(json_encode($a)) ?>)">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <a href="announcements_management.php?action=delete&id=<?= $a['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this ticker?');">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="announcementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Announcement Text *</label>
                        <textarea name="title" id="title" class="form-control" rows="3" placeholder="📢 Enter headline text to show on homepage" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link Path / URL</label>
                        <input type="text" name="link" id="link" class="form-control" placeholder="e.g. event-detail.php?id=5 or a full URL">
                        <div class="form-text text-muted">Leave empty if the announcement shouldn't be clickable.</div>
                    </div>
                    <div class="mb-3 form-check form-switch" style="padding-left: 2.5em;">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">Publish immediately (Show in scrolling ticker)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var announcementModal;
    document.addEventListener("DOMContentLoaded", function() {
        announcementModal = new bootstrap.Modal(document.getElementById('announcementModal'));
    });

    function openAddModal() {
        document.getElementById('edit_id').value = '';
        document.getElementById('title').value = '';
        document.getElementById('link').value = '';
        document.getElementById('is_active').checked = true;
        document.getElementById('modalTitle').innerText = 'Add Announcement';
        announcementModal.show();
    }

    function openEditModal(ann) {
        document.getElementById('edit_id').value = ann.id;
        document.getElementById('title').value = ann.title;
        document.getElementById('link').value = ann.link;
        document.getElementById('is_active').checked = ann.is_active == 1;
        document.getElementById('modalTitle').innerText = 'Edit Announcement';
        announcementModal.show();
    }
</script>

<?php include 'footer.php'; ?>
