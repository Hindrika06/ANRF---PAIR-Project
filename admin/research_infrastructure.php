<?php
require_once 'auth_check.php';
require_once 'role_access.php';

// Auth Guard: Only Super Admin / Hub Admin can access Research & Infrastructure
if (!isSuperAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

require_once 'config/db.php';

$success = false;
$error   = '';
$activeTab = $_GET['tab'] ?? 'research';

// Self-healing database tables creation
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `research_areas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `image_path` VARCHAR(255) DEFAULT NULL,
            `display_order` INT NOT NULL DEFAULT 10,
            `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `infrastructure_facilities` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `equipment_details` TEXT DEFAULT NULL,
            `image_path` VARCHAR(255) DEFAULT NULL,
            `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all',
            `display_order` INT NOT NULL DEFAULT 10,
            `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
} catch (PDOException $e) {
    // Ignore error
}

// 1. HANDLE DELETE ACTIONS
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $type = $_GET['type'] ?? '';

    if ($type === 'research') {
        if (!isSuperAdmin()) {
            $error = 'Only Super Admins can delete research areas.';
        } else {
            try {
                // Delete image
                $stmt = $pdo->prepare("SELECT image_path FROM `research_areas` WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if ($row && !empty($row['image_path']) && file_exists('../' . $row['image_path'])) {
                    @unlink('../' . $row['image_path']);
                }

                $stmt = $pdo->prepare("DELETE FROM `research_areas` WHERE id = ?");
                $stmt->execute([$id]);
                adminRedirect(['tab' => 'research', 'success_msg' => 'deleted']);
            } catch (PDOException $e) {
                $error = 'Failed to delete research area: ' . $e->getMessage();
            }
        }
    } elseif ($type === 'facility') {
        $deletePrefix = $_GET['record_prefix'] ?? $prefix;
        if (!isValidPrefix($deletePrefix) || !canEditInstitute($deletePrefix)) {
            $error = 'You are not allowed to delete facilities for this institute.';
        } else {
            try {
                // Delete image
                $stmt = $pdo->prepare("SELECT image_path FROM `infrastructure_facilities` WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if ($row && !empty($row['image_path']) && file_exists('../' . $row['image_path'])) {
                    @unlink('../' . $row['image_path']);
                }

                $stmt = $pdo->prepare("DELETE FROM `infrastructure_facilities` WHERE id = ?");
                $stmt->execute([$id]);
                adminRedirect(['tab' => 'infrastructure', 'success_msg' => 'deleted']);
            } catch (PDOException $e) {
                $error = 'Failed to delete facility: ' . $e->getMessage();
            }
        }
    }
}

// 2. SHOW QUICK SUCCESS MESSAGES POST-REDIRECT
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';
    $edit_id  = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;
    $status   = $_POST['status'] ?? 'Active';
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 10;

    if ($formType === 'research') {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!isSuperAdmin()) {
            $error = 'Only Super Admins can manage research areas.';
        } elseif (empty($title) || empty($description)) {
            $error = 'Title and Description are required.';
        } else {
            try {
                $uploadDir = '../uploads/research/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $imagePath = null;

                if (!empty($_FILES['image']['name'])) {
                    $f = $_FILES['image'];
                    if ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= 3 * 1024 * 1024) {
                        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $destFileName = uniqid('res_', true) . '.' . $ext;
                            if (move_uploaded_file($f['tmp_name'], $uploadDir . $destFileName)) {
                                $imagePath = 'uploads/research/' . $destFileName;
                                if ($edit_id) {
                                    $stmt = $pdo->prepare("SELECT image_path FROM `research_areas` WHERE id = ?");
                                    $stmt->execute([$edit_id]);
                                    $oldImg = $stmt->fetchColumn();
                                    if ($oldImg && file_exists('../' . $oldImg)) {
                                        @unlink('../' . $oldImg);
                                    }
                                }
                            }
                        }
                    }
                }

                if ($edit_id) {
                    if ($imagePath) {
                        $stmt = $pdo->prepare("UPDATE `research_areas` SET title = :title, description = :description, image_path = :image_path, display_order = :display_order, status = :status WHERE id = :id");
                        $stmt->execute([':title' => $title, ':description' => $description, ':image_path' => $imagePath, ':display_order' => $display_order, ':status' => $status, ':id' => $edit_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE `research_areas` SET title = :title, description = :description, display_order = :display_order, status = :status WHERE id = :id");
                        $stmt->execute([':title' => $title, ':description' => $description, ':display_order' => $display_order, ':status' => $status, ':id' => $edit_id]);
                    }
                } else {
                    $stmt = $pdo->prepare("INSERT INTO `research_areas` (title, description, image_path, display_order, status) VALUES (:title, :description, :image_path, :display_order, :status)");
                    $stmt->execute([':title' => $title, ':description' => $description, ':image_path' => $imagePath, ':display_order' => $display_order, ':status' => $status]);
                }
                adminRedirect(['tab' => 'research', 'success_msg' => 'saved']);
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif ($formType === 'facility') {
        $name              = trim($_POST['name'] ?? '');
        $description       = trim($_POST['description'] ?? '');
        $equipment_details = trim($_POST['equipment_details'] ?? '');

        if (!canEditInstitute($prefix)) {
            $error = 'You are not allowed to manage facilities for this institute.';
        } elseif (empty($name) || empty($description)) {
            $error = 'Name and Description are required.';
        } else {
            try {
                $uploadDir = '../uploads/infrastructure/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $imagePath = null;

                if (!empty($_FILES['image']['name'])) {
                    $f = $_FILES['image'];
                    if ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= 5 * 1024 * 1024) {
                        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $destFileName = uniqid('infra_', true) . '.' . $ext;
                            if (move_uploaded_file($f['tmp_name'], $uploadDir . $destFileName)) {
                                $imagePath = 'uploads/infrastructure/' . $destFileName;
                                if ($edit_id) {
                                    $stmt = $pdo->prepare("SELECT image_path FROM `infrastructure_facilities` WHERE id = ?");
                                    $stmt->execute([$edit_id]);
                                    $oldImg = $stmt->fetchColumn();
                                    if ($oldImg && file_exists('../' . $oldImg)) {
                                        @unlink('../' . $oldImg);
                                    }
                                }
                            }
                        }
                    }
                }

                $is_super = isSuperAdmin();
                $approvalStatus = $is_super ? 'Approved' : 'Pending';

                if ($edit_id) {
                    if ($imagePath) {
                        $stmt = $pdo->prepare("UPDATE `infrastructure_facilities` SET name = :name, description = :description, equipment_details = :equipment_details, image_path = :image_path, display_order = :display_order, status = :status, approval_status = :approval_status WHERE id = :id");
                        $params = [':name' => $name, ':description' => $description, ':equipment_details' => $equipment_details, ':image_path' => $imagePath, ':display_order' => $display_order, ':status' => $status, ':approval_status' => $approvalStatus, ':id' => $edit_id];
                        $stmt->execute($params);
                    } else {
                        $stmt = $pdo->prepare("UPDATE `infrastructure_facilities` SET name = :name, description = :description, equipment_details = :equipment_details, display_order = :display_order, status = :status, approval_status = :approval_status WHERE id = :id");
                        $params = [':name' => $name, ':description' => $description, ':equipment_details' => $equipment_details, ':display_order' => $display_order, ':status' => $status, ':approval_status' => $approvalStatus, ':id' => $edit_id];
                        $stmt->execute($params);
                    }

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Research Infrastructure', 'infrastructure_facilities', $prefix, $edit_id, 'UPDATE', $params);
                        adminRedirect(['tab' => 'infrastructure', 'success_msg' => 'submitted']);
                    } else {
                        adminRedirect(['tab' => 'infrastructure', 'success_msg' => 'saved']);
                    }
                } else {
                    $stmt = $pdo->prepare("INSERT INTO `infrastructure_facilities` (name, description, equipment_details, image_path, institute_prefix, display_order, status, approval_status) VALUES (:name, :description, :equipment_details, :image_path, :institute_prefix, :display_order, :status, :approval_status)");
                    $params = [':name' => $name, ':description' => $description, ':equipment_details' => $equipment_details, ':image_path' => $imagePath, ':institute_prefix' => $prefix, ':display_order' => $display_order, ':status' => $status, ':approval_status' => $approvalStatus];
                    $stmt->execute($params);
                    $new_id = $pdo->lastInsertId();

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Research Infrastructure', 'infrastructure_facilities', $prefix, $new_id, 'CREATE', $params);
                        adminRedirect(['tab' => 'infrastructure', 'success_msg' => 'submitted']);
                    } else {
                        adminRedirect(['tab' => 'infrastructure', 'success_msg' => 'saved']);
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch lists
$researchAreas = [];
$facilities = [];
try {
    $stmt = $pdo->query("SELECT * FROM `research_areas` ORDER BY display_order ASC, id DESC");
    $researchAreas = $stmt->fetchAll();

    $facilities = fetchSingleTableKpiDataset($pdo, 'infrastructure_facilities', $prefix, isSuperAdmin());
    usort($facilities, function($a, $b) {
        return ($a['display_order'] ?? 10) <=> ($b['display_order'] ?? 10);
    });
} catch (PDOException $e) {
    // Ignore error
}

?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
    /* ──── RESEARCH & INFRASTRUCTURE TAB STYLING (#00897B) ──── */
    .custom-tab-1 .nav-tabs {
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .custom-tab-1 .nav-tabs .nav-link {
        color: #64748b !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        border: none !important;
        border-bottom: 3px solid transparent !important;
        background: transparent !important;
        padding: 10px 18px !important;
        transition: all 0.2s ease !important;
    }
    .custom-tab-1 .nav-tabs .nav-link i {
        color: #64748b !important;
        transition: color 0.2s ease !important;
    }
    .custom-tab-1 .nav-tabs .nav-link:hover {
        color: #00897B !important;
    }
    .custom-tab-1 .nav-tabs .nav-link:hover i {
        color: #00897B !important;
    }
    .custom-tab-1 .nav-tabs .nav-link.active {
        color: #00897B !important;
        border-bottom: 3px solid #00897B !important;
        background: #ffffff !important;
    }
    .custom-tab-1 .nav-tabs .nav-link.active i {
        color: #00897B !important;
    }
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">
            
            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Research & Infrastructure</a></li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Record updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- TABS NAVIGATION -->
            <div class="custom-tab-1 mb-4">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link <?= $activeTab === 'research' ? 'active' : '' ?>" href="research_infrastructure.php?prefix=<?= $prefix ?>&tab=research">
                            <i class="fas fa-flask me-2"></i> Research Areas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeTab === 'infrastructure' ? 'active' : '' ?>" href="research_infrastructure.php?prefix=<?= $prefix ?>&tab=infrastructure">
                            <i class="fas fa-microscope me-2"></i> Infrastructure & Facilities
                        </a>
                    </li>
                </ul>
            </div>

            <!-- TAB 1: RESEARCH AREAS -->
            <?php if ($activeTab === 'research'): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Project Key Research Areas (Global)</h4>
                    <?php if (isSuperAdmin()): ?>
                    <button type="button" class="btn text-white btn-sm" style="background-color: #09BD3C !important; border-color: #09BD3C !important; font-weight: 600;" data-bs-toggle="modal" data-bs-target="#researchModal" data-toggle="modal" data-target="#researchModal" onclick="openAddResearchModal()">
                        <i class="fa fa-plus me-1"></i> Add Research Area
                    </button>
                    <?php else: ?>
                    <span class="text-muted" style="font-size: 13px;"><i class="fa fa-lock"></i> Hub Admin Only</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 100px; background-color: #bc2121 !important; color: #ffffff !important;">Cover</th>
                                    <th style="background-color: #bc2121 !important; color: #ffffff !important;">Title</th>
                                    <th style="background-color: #bc2121 !important; color: #ffffff !important;">Description</th>
                                    <th style="width: 80px; background-color: #bc2121 !important; color: #ffffff !important;">Order</th>
                                    <th style="width: 100px; background-color: #bc2121 !important; color: #ffffff !important;">Status</th>
                                    <th style="width: 100px; text-align: center; background-color: #bc2121 !important; color: #ffffff !important;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($researchAreas)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No research areas configured. Fallback categories will display.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($researchAreas as $r): ?>
                                    <tr>
                                        <td>
                                            <?php if ($r['image_path']): ?>
                                                <img src="../<?= htmlspecialchars($r['image_path']) ?>" alt="Research Area" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <span class="text-muted" style="font-size: 11px;">No Photo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
                                        <td><small><?= htmlspecialchars(strlen($r['description']) > 150 ? substr($r['description'], 0, 150) . '...' : $r['description']) ?></small></td>
                                        <td><?= (int)$r['display_order'] ?></td>
                                        <td>
                                            <span class="badge <?= $r['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= htmlspecialchars($r['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-info btn-xs text-white me-1 view-research-btn" data-record="<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <?php if (isSuperAdmin()): ?>
                                                <button class="btn btn-warning btn-xs me-1" data-bs-toggle="modal" data-bs-target="#researchModal" onclick="openEditResearchModal(<?= htmlspecialchars(json_encode($r)) ?>)" title="Edit Record">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                                <a href="<?= $navUrl('research_infrastructure.php?action=delete&type=research&id=' . $r['id']) ?>" class="btn btn-danger btn-xs" title="Delete Record" onclick="event.preventDefault(); const targetUrl = this.href; ANRFModal.confirm({ title: 'Delete Research Area?', message: 'Are you sure you want to delete this research area?', confirmText: 'Delete', onConfirm: function() { window.location.href = targetUrl; } });">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- TAB 2: INFRASTRUCTURE & FACILITIES -->
            <?php if ($activeTab === 'infrastructure'): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Laboratory Infrastructure & Advanced Facilities</h4>
                    <button type="button" class="btn text-white btn-sm" style="background-color: #09BD3C !important; border-color: #09BD3C !important; font-weight: 600;" data-bs-toggle="modal" data-bs-target="#facilityModal" data-toggle="modal" data-target="#facilityModal" onclick="openAddFacilityModal()">
                        <i class="fa fa-plus me-1"></i> Add Facility
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 100px; background-color: #bc2121 !important; color: #ffffff !important;">Photo</th>
                                    <th style="background-color: #bc2121 !important; color: #ffffff !important;">Facility Name</th>
                                    <th style="background-color: #bc2121 !important; color: #ffffff !important;">Description &amp; Equipment Details</th>
                                    <th style="background-color: #bc2121 !important; color: #ffffff !important;">Owner</th>
                                    <th style="width: 80px; background-color: #bc2121 !important; color: #ffffff !important;">Order</th>
                                    <th style="width: 100px; background-color: #bc2121 !important; color: #ffffff !important;">Status</th>
                                    <th style="width: 100px; text-align: center; background-color: #bc2121 !important; color: #ffffff !important;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($facilities)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No infrastructure facilities configured for this prefix. Fallback facilities will display.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($facilities as $f): ?>
                                    <tr>
                                        <td>
                                            <?php if ($f['image_path']): ?>
                                                <img src="../<?= htmlspecialchars($f['image_path']) ?>" alt="Facility" style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <span class="text-muted" style="font-size: 11px;">No Photo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($f['name']) ?></strong></td>
                                        <td>
                                            <p style="font-size: 13px; margin-bottom: 5px;"><?= htmlspecialchars($f['description']) ?></p>
                                            <?php if ($f['equipment_details']): ?>
                                                <div style="font-size: 11px; color:#555; background: #f8fafc; padding: 4px 8px; border-radius: 4px; border-left: 3px solid #bc2121;">
                                                    <strong>Equipment list:</strong> <?= htmlspecialchars($f['equipment_details']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars(strtoupper($f['institute_prefix'])) ?></span></td>
                                        <td><?= (int)$f['display_order'] ?></td>
                                        <td>
                                            <span class="badge <?= $f['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= htmlspecialchars($f['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-info btn-xs text-white me-1 view-facility-btn" data-record="<?= htmlspecialchars(json_encode($f), ENT_QUOTES, 'UTF-8') ?>" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <?php if (canEditInstitute($f['institute_prefix'] ?? $prefix)): ?>
                                            <button class="btn btn-warning btn-xs me-1" data-bs-toggle="modal" data-bs-target="#facilityModal" onclick="openEditFacilityModal(<?= htmlspecialchars(json_encode($f)) ?>)" title="Edit Record">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <a href="<?= $navUrl('research_infrastructure.php?action=delete&type=facility&id=' . $f['id'] . '&record_prefix=' . urlencode($f['institute_prefix'] ?? $prefix)) ?>" class="btn btn-danger btn-xs" title="Delete Record" onclick="event.preventDefault(); const targetUrl = this.href; ANRFModal.confirm({ title: 'Delete Facility?', message: 'Are you sure you want to delete this facility?', confirmText: 'Delete', onConfirm: function() { window.location.href = targetUrl; } });">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal 1: Research Area Modal -->
<div class="modal fade" id="researchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="research">
                <input type="hidden" name="edit_id" id="res_edit_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="resModalTitle">Add Research Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Research Title *</label>
                        <input type="text" name="title" id="res_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detailed Description *</label>
                        <textarea name="description" id="res_description" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cover Photo (Optional)</label>
                        <input type="file" name="image" id="res_image" class="form-control" accept="image/*">
                        <div class="form-text text-muted">Upload an illustrative thumbnail (JPG, PNG, WEBP, max 3MB).</div>
                        <div id="resImagePreviewContainer" class="mt-2" style="display:none;">
                            <img id="resImagePreview" src="" alt="Preview" style="max-height: 100px; border-radius: 4px;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" id="res_display_order" class="form-control" value="10" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="res_status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn text-white" style="background-color: #064380 !important; border-color: #064380 !important; font-weight: 600;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background-color: #09BD3C !important; border-color: #09BD3C !important; font-weight: 600;">Save Research Area</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Facility Modal -->
<div class="modal fade" id="facilityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="facility">
                <input type="hidden" name="edit_id" id="fac_edit_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="facModalTitle">Add Infrastructure Facility</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Facility/Lab Name *</label>
                        <input type="text" name="name" id="fac_name" class="form-control" placeholder="e.g. Biomedical Imaging & Spectroscopy Laboratory" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" id="fac_description" class="form-control" rows="4" placeholder="Brief outline of the facility purpose, research alignment, etc." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Analytical Instruments & Equipment List</label>
                        <textarea name="equipment_details" id="fac_equipment" class="form-control" rows="3" placeholder="List key equipment, comma separated or line by line (e.g. High-performance MRI scanner, Spectrophotometer)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Facility Image / Photo</label>
                        <input type="file" name="image" id="fac_image" class="form-control" accept="image/*">
                        <div class="form-text text-muted">Upload a photo of the lab/equipment (JPG, JPEG, PNG, WEBP, max 5MB).</div>
                        <div id="facImagePreviewContainer" class="mt-2" style="display:none;">
                            <img id="facImagePreview" src="" alt="Preview" style="max-height: 100px; border-radius: 4px;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" id="fac_display_order" class="form-control" value="10" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="fac_status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn text-white" style="background-color: #064380 !important; border-color: #064380 !important; font-weight: 600;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background-color: #09BD3C !important; border-color: #09BD3C !important; font-weight: 600;">Save Facility</button>
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
    function showResearchModal(modalId) {
        var modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        if (window.bootstrap && window.bootstrap.Modal) {
            var instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            instance.show();
        } else if (window.jQuery && jQuery.fn.modal) {
            jQuery(modalEl).modal('show');
        }
    }

    function openAddResearchModal() {
        document.getElementById('res_edit_id').value = '';
        document.getElementById('res_title').value = '';
        document.getElementById('res_description').value = '';
        document.getElementById('res_display_order').value = '10';
        document.getElementById('res_status').value = 'Active';
        var p = document.getElementById('resImagePreviewContainer');
        if (p) p.style.display = 'none';
        document.getElementById('resModalTitle').innerText = 'Add Research Area';
        showResearchModal('researchModal');
    }

    function openEditResearchModal(res) {
        document.getElementById('res_edit_id').value = res.id;
        document.getElementById('res_title').value = res.title;
        document.getElementById('res_description').value = res.description;
        document.getElementById('res_display_order').value = res.display_order;
        document.getElementById('res_status').value = res.status;
        var p = document.getElementById('resImagePreviewContainer');
        if (res.image_path) {
            document.getElementById('resImagePreview').src = '../' + res.image_path;
            if (p) p.style.display = 'block';
        } else {
            if (p) p.style.display = 'none';
        }
        document.getElementById('resModalTitle').innerText = 'Edit Research Area';
        showResearchModal('researchModal');
    }

    function openAddFacilityModal() {
        document.getElementById('fac_edit_id').value = '';
        document.getElementById('fac_name').value = '';
        document.getElementById('fac_description').value = '';
        document.getElementById('fac_equipment').value = '';
        document.getElementById('fac_display_order').value = '10';
        document.getElementById('fac_status').value = 'Active';
        var p = document.getElementById('facImagePreviewContainer');
        if (p) p.style.display = 'none';
        document.getElementById('facModalTitle').innerText = 'Add Infrastructure Facility';
        showResearchModal('facilityModal');
    }

    function openEditFacilityModal(fac) {
        document.getElementById('fac_edit_id').value = fac.id;
        document.getElementById('fac_name').value = fac.name;
        document.getElementById('fac_description').value = fac.description;
        document.getElementById('fac_equipment').value = fac.equipment_details || '';
        document.getElementById('fac_display_order').value = fac.display_order;
        document.getElementById('fac_status').value = fac.status;
        var p = document.getElementById('facImagePreviewContainer');
        if (fac.image_path) {
            document.getElementById('facImagePreview').src = '../' + fac.image_path;
            if (p) p.style.display = 'block';
        } else {
            if (p) p.style.display = 'none';
        }
        document.getElementById('facModalTitle').innerText = 'Edit Infrastructure Facility';
        showResearchModal('facilityModal');
    }

    document.addEventListener("DOMContentLoaded", function() {
        const viewResBtns = document.querySelectorAll('.view-research-btn');
        viewResBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.dataset.record) return;
                const rec = JSON.parse(this.dataset.record);
                const imgUrl = rec.image_path ? ('../' + rec.image_path) : null;

                openKpiRecordViewModal({
                    moduleTitle: 'Research Area Details',
                    recordTitle: rec.title || 'Untitled Research Area',
                    extraStatus: rec.status ? `Status: ${rec.status}` : null,
                    fields: [
                        { label: 'Title', value: rec.title, fullWidth: true, icon: 'fa-solid fa-flask' },
                        { label: 'Display Order', value: rec.display_order, icon: 'fa-solid fa-sort' },
                        { label: 'Description', value: rec.description, type: 'longtext', icon: 'fa-solid fa-align-left' },
                        { label: 'Image', value: imgUrl, type: 'image', icon: 'fa-solid fa-image' }
                    ]
                });
            });
        });

        const viewFacBtns = document.querySelectorAll('.view-facility-btn');
        viewFacBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.dataset.record) return;
                const rec = JSON.parse(this.dataset.record);
                const instPrefix = rec.institute_prefix || "<?= htmlspecialchars($prefix !== 'all' ? $prefix : 'all') ?>";
                const imgUrl = rec.image_path ? ('../' + rec.image_path) : null;

                openKpiRecordViewModal({
                    moduleTitle: 'Infrastructure Facility Details',
                    recordTitle: rec.name || 'Untitled Facility',
                    institutePrefix: instPrefix,
                    extraStatus: rec.status ? `Status: ${rec.status}` : null,
                    fields: [
                        { label: 'Facility Name', value: rec.name, fullWidth: true, icon: 'fa-solid fa-microscope' },
                        { label: 'Display Order', value: rec.display_order, icon: 'fa-solid fa-sort' },
                        { label: 'Description', value: rec.description, type: 'longtext', icon: 'fa-solid fa-align-left' },
                        { label: 'Equipment Details', value: rec.equipment_details, type: 'longtext', icon: 'fa-solid fa-toolbox' },
                        { label: 'Facility Photo', value: imgUrl, type: 'image', icon: 'fa-solid fa-image' }
                    ]
                });
            });
        });
    });
</script>

<?php include 'includes/view_modal.php'; ?>
<?php include 'footer.php'; ?>
