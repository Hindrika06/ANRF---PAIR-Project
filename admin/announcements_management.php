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

// Clean Action URL (prevents GET action/id parameters from leaking into POST forms)
$cleanActionUrl = 'announcements_management.php' . (!empty($_GET['prefix']) ? '?prefix=' . urlencode($_GET['prefix']) : '');

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

// 1. HANDLE DELETE ACTION (POST ONLY PREFERRED, WITH GET FALLBACK)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['delete_id'])) {
    $userCsrf = $_POST['csrf_token'] ?? '';
    if (empty($userCsrf) || !hash_equals($_SESSION['csrf_token'], $userCsrf)) {
        $error = 'Security Error: Invalid or missing CSRF token.';
    } else {
        try {
            $deleteId = (int)$_POST['delete_id'];
            $stmt = $pdo->prepare("DELETE FROM `announcements` WHERE id = :id");
            $stmt->execute([':id' => $deleteId]);
            adminRedirect(['success_msg' => 'deleted']);
        } catch (PDOException $e) {
            $error = 'Failed to delete ticker: ' . $e->getMessage();
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $userCsrf = $_GET['csrf_token'] ?? '';
    if (empty($userCsrf) || !hash_equals($_SESSION['csrf_token'], $userCsrf)) {
        $error = 'Security Error: Invalid or missing CSRF token.';
    } else {
        try {
            $deleteId = (int)$_GET['id'];
            $stmt = $pdo->prepare("DELETE FROM `announcements` WHERE id = :id");
            $stmt->execute([':id' => $deleteId]);
            adminRedirect(['success_msg' => 'deleted']);
        } catch (PDOException $e) {
            $error = 'Failed to delete ticker: ' . $e->getMessage();
        }
    }
}

// Generate CSRF Token for Form Security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. SHOW QUICK SUCCESS MESSAGES POST-REDIRECT
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS (ADD OR EDIT)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    $userCsrf = $_POST['csrf_token'] ?? '';
    if (empty($userCsrf) || !hash_equals($_SESSION['csrf_token'], $userCsrf)) {
        $error = 'Security Error: Invalid or missing CSRF token.';
    } else {
        $title     = trim($_POST['title'] ?? '');
        $link      = trim($_POST['link'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $edit_id   = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

        if (empty($title)) {
            $error = 'Announcement text/title is required.';
        } else {
            try {
                $is_super = isSuperAdmin();
                $approvalStatus = $is_super ? 'Approved' : 'Pending';

                if ($edit_id) {
                    // Update existing record
                    $stmt = $pdo->prepare("UPDATE `announcements` SET title = :title, link = :link, is_active = :is_active, approval_status = :approval_status WHERE id = :id");
                    $params = [
                        ':title'           => $title,
                        ':link'            => $link,
                        ':is_active'       => $is_active,
                        ':approval_status' => $approvalStatus,
                        ':id'              => $edit_id
                    ];
                    $stmt->execute($params);

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Announcements', 'announcements', $prefix, $edit_id, 'UPDATE', $params);
                        adminRedirect(['success_msg' => 'submitted']);
                    } else {
                        adminRedirect(['success_msg' => 'updated']);
                    }
                } else {
                    // Insert new record
                    $stmt = $pdo->prepare("INSERT INTO `announcements` (title, link, is_active, approval_status) VALUES (:title, :link, :is_active, :approval_status)");
                    $params = [
                        ':title'           => $title,
                        ':link'            => $link,
                        ':is_active'       => $is_active,
                        ':approval_status' => $approvalStatus
                    ];
                    $stmt->execute($params);
                    $new_id = (int)$pdo->lastInsertId();

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Announcements', 'announcements', $prefix, $new_id, 'CREATE', $params);
                        adminRedirect(['success_msg' => 'submitted']);
                    } else {
                        adminRedirect(['success_msg' => 'inserted']);
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all announcements
$announcements = [];
try {
    $stmt = $pdo->query("SELECT * FROM `announcements` ORDER BY id DESC");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Ignore error
}

?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
    .btn-xs {
        padding: 4px 10px;
        font-size: 11.5px;
        font-weight: 600;
        border-radius: 4px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .action-btn-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: nowrap;
    }
</style>

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
                <i class="fa fa-check-circle me-2"></i>
                <strong>Success!</strong> Announcements updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-2"></i>
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="card-title mb-0">Ticker Announcements & "What's New"</h4>
                    <button type="button" class="btn btn-primary btn-sm px-3" onclick="openAddModal()">
                        <i class="fa fa-plus me-1"></i> Add Announcement
                    </button>
                </div>
                <div class="card-body">
                    <!-- Hidden POST Delete Form (Uses Explicit Action URL) -->
                    <form id="deleteAnnouncementForm" method="POST" action="<?= htmlspecialchars($cleanActionUrl) ?>" style="display: none;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="delete_id" id="delete_id_input" value="">
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Message / Title</th>
                                    <th>Link Target</th>
                                    <th style="width: 110px;">Status</th>
                                    <th style="width: 220px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($announcements)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No custom ticker announcements added. Fallback static ticker values will display on the home screen.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($announcements as $a):
                                        $jsonAnn = json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                                    ?>
                                    <tr>
                                        <td><strong class="text-dark"><?= htmlspecialchars($a['title']) ?></strong></td>
                                        <td>
                                            <?php if (!empty($a['link'])): ?>
                                                <a href="../<?= htmlspecialchars($a['link']) ?>" target="_blank" class="text-info font-w500">
                                                    <i class="fa fa-external-link me-1"></i><?= htmlspecialchars($a['link']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">(No link)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $a['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $a['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="action-btn-group">
                                                <button type="button" class="btn btn-info btn-xs text-white px-2" onclick='viewAnnouncement(<?= $jsonAnn ?>)'>
                                                    <i class="fa fa-eye me-1"></i>View
                                                </button>
                                                <button type="button" class="btn btn-warning btn-xs text-white px-2" onclick='openEditModal(<?= $jsonAnn ?>)'>
                                                    <i class="fa fa-pencil me-1"></i>Edit
                                                </button>
                                                <button type="button" class="btn btn-danger btn-xs text-white px-2" onclick="confirmDeleteAnnouncement(<?= (int)$a['id'] ?>)">
                                                    <i class="fa fa-trash me-1"></i>Delete
                                                </button>
                                            </div>
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

<!-- Modal Form (Uses Explicit Action URL) -->
<div class="modal fade" id="announcementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= htmlspecialchars($cleanActionUrl) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-w600 text-dark">Announcement Text <span class="text-danger">*</span></label>
                        <textarea name="title" id="title" class="form-control" rows="3" placeholder="📢 Enter headline text to show on homepage" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-w600 text-dark">Link Path / URL</label>
                        <input type="text" name="link" id="link" class="form-control" placeholder="e.g. event-detail.php?id=5 or a full URL">
                        <div class="form-text text-muted">Optional. Leave empty if the announcement shouldn't be clickable.</div>
                    </div>
                    <div class="mb-3 form-check form-switch" style="padding-left: 2.5em;">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label font-w600 text-dark" for="is_active">Publish immediately (Show in scrolling ticker)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Save Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var announcementModal;
    document.addEventListener("DOMContentLoaded", function() {
        var modalElem = document.getElementById('announcementModal');
        if (modalElem) {
            announcementModal = new bootstrap.Modal(modalElem);
        }
    });

    function openAddModal() {
        document.getElementById('edit_id').value = '';
        document.getElementById('title').value = '';
        document.getElementById('link').value = '';
        document.getElementById('is_active').checked = true;
        document.getElementById('modalTitle').innerText = 'Add Announcement';
        document.getElementById('modalSubmitBtn').innerText = 'Save Announcement';
        if (announcementModal) announcementModal.show();
    }

    function openEditModal(ann) {
        if (!ann) return;
        document.getElementById('edit_id').value = ann.id || '';
        document.getElementById('title').value = ann.title || '';
        document.getElementById('link').value = ann.link || '';
        document.getElementById('is_active').checked = (ann.is_active == 1 || ann.is_active === '1');
        document.getElementById('modalTitle').innerText = 'Edit Announcement';
        document.getElementById('modalSubmitBtn').innerText = 'Update Announcement';
        if (announcementModal) announcementModal.show();
    }

    function viewAnnouncement(ann) {
        if (!ann) return;
        openKpiRecordViewModal({
            moduleTitle: 'Ticker Announcement Details',
            recordTitle: ann.title || 'Announcement #' + ann.id,
            extraStatus: (ann.is_active == 1 || ann.is_active === '1') ? 'Active' : 'Inactive',
            fields: [
                { label: 'Announcement Title / Message', value: ann.title, type: 'longtext', icon: 'fa-solid fa-bullhorn' },
                { label: 'Target Link / URL', value: ann.link || 'None', type: ann.link ? 'link' : 'text', icon: 'fa-solid fa-link' },
                { label: 'Publication Status', value: (ann.is_active == 1 || ann.is_active === '1') ? 'Active (Displayed on Ticker)' : 'Inactive (Hidden)', type: 'badge', icon: 'fa-solid fa-toggle-on' },
                { label: 'Created At', value: ann.created_at || 'N/A', icon: 'fa-solid fa-clock' },
                { label: 'Last Updated', value: ann.updated_at || 'N/A', icon: 'fa-solid fa-pen-to-square' }
            ]
        });
    }

    function confirmDeleteAnnouncement(id) {
        if (typeof ANRFModal !== 'undefined' && ANRFModal.confirm) {
            ANRFModal.confirm({
                title: 'Delete Ticker?',
                message: 'Are you sure you want to delete this announcement ticker? This action cannot be undone.',
                confirmText: 'Delete',
                onConfirm: function() {
                    document.getElementById('delete_id_input').value = id;
                    document.getElementById('deleteAnnouncementForm').submit();
                }
            });
        } else if (confirm('Are you sure you want to delete this announcement ticker?')) {
            document.getElementById('delete_id_input').value = id;
            document.getElementById('deleteAnnouncementForm').submit();
        }
    }
</script>

<?php include 'includes/view_modal.php'; ?>
<?php include 'footer.php'; ?>
