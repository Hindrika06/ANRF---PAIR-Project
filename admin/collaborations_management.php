<?php
session_start();

require_once 'role_access.php';

// Auth Guard
if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix'])) {
    header("Location: ../login.php");
    exit();
}

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

require_once 'config/db.php';

$success = false;
$error   = '';

// Self-healing: create table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `collaborations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `partner_name` VARCHAR(255) NOT NULL,
            `logo_path` VARCHAR(255) NOT NULL,
            `profile_description` TEXT DEFAULT NULL,
            `collab_type` ENUM('Academic', 'Research', 'Industry') NOT NULL DEFAULT 'Academic',
            `website_url` VARCHAR(255) DEFAULT '',
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

// 1. HANDLE DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to delete collaborations for this institute.';
    } else {
        try {
            // Delete logo file first
            $stmt = $pdo->prepare("SELECT logo_path FROM `collaborations` WHERE id = :id");
            $stmt->execute([':id' => (int)$_GET['id']]);
            $row = $stmt->fetch();
            if ($row && !empty($row['logo_path']) && file_exists('../' . $row['logo_path'])) {
                @unlink('../' . $row['logo_path']);
            }

            $stmt = $pdo->prepare("DELETE FROM `collaborations` WHERE id = :id");
            $stmt->execute([':id' => (int)$_GET['id']]);
            header("Location: collaborations_management.php?prefix=" . $prefix . "&success_msg=deleted");
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to delete collaboration: ' . $e->getMessage();
        }
    }
}

// 2. SHOW QUICK SUCCESS MESSAGES POST-REDIRECT
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS (ADD OR UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partner_name        = trim($_POST['partner_name'] ?? '');
    $profile_description = trim($_POST['profile_description'] ?? '');
    $collab_type         = $_POST['collab_type'] ?? 'Academic';
    $website_url         = trim($_POST['website_url'] ?? '');
    $display_order       = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 10;
    $status              = $_POST['status'] ?? 'Active';
    $edit_id             = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if (empty($partner_name)) {
        $error = 'Partner Name is required.';
    } elseif (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to edit collaborations for this institute.';
    } else {
        try {
            $uploadDir = '../uploads/collaborations/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $logoPath = null;

            // Handle Logo File Upload
            if (!empty($_FILES['logo']['name'])) {
                $f = $_FILES['logo'];

                if ($f['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException("File upload failed with error code: " . $f['error']);
                }

                if ($f['size'] > 3 * 1024 * 1024) {
                    throw new RuntimeException("Logo file size exceeds 3 MB limit.");
                }

                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'svg', 'webp'])) {
                    throw new RuntimeException("Invalid file type. Only JPG, JPEG, PNG, SVG, and WEBP are allowed.");
                }

                $destFileName = uniqid('collab_', true) . '.' . $ext;
                $destFullPath = $uploadDir . $destFileName;

                if (!move_uploaded_file($f['tmp_name'], $destFullPath)) {
                    throw new RuntimeException("Could not save the uploaded logo file.");
                }
                $logoPath = 'uploads/collaborations/' . $destFileName;

                // Delete old logo file if editing
                if ($edit_id) {
                    $stmt = $pdo->prepare("SELECT logo_path FROM `collaborations` WHERE id = :id");
                    $stmt->execute([':id' => $edit_id]);
                    $oldRow = $stmt->fetch();
                    if ($oldRow && !empty($oldRow['logo_path']) && file_exists('../' . $oldRow['logo_path'])) {
                        @unlink('../' . $oldRow['logo_path']);
                    }
                }
            }

            if ($edit_id) {
                // Update
                if ($logoPath) {
                    $stmt = $pdo->prepare("UPDATE `collaborations` SET partner_name = :partner_name, logo_path = :logo_path, profile_description = :profile_description, collab_type = :collab_type, website_url = :website_url, display_order = :display_order, status = :status WHERE id = :id");
                    $stmt->execute([
                        ':partner_name'        => $partner_name,
                        ':logo_path'           => $logoPath,
                        ':profile_description' => $profile_description,
                        ':collab_type'         => $collab_type,
                        ':website_url'         => $website_url,
                        ':display_order'       => $display_order,
                        ':status'              => $status,
                        ':id'                  => $edit_id
                    ]);
                } else {
                    $stmt = $pdo->prepare("UPDATE `collaborations` SET partner_name = :partner_name, profile_description = :profile_description, collab_type = :collab_type, website_url = :website_url, display_order = :display_order, status = :status WHERE id = :id");
                    $stmt->execute([
                        ':partner_name'        => $partner_name,
                        ':profile_description' => $profile_description,
                        ':collab_type'         => $collab_type,
                        ':website_url'         => $website_url,
                        ':display_order'       => $display_order,
                        ':status'              => $status,
                        ':id'                  => $edit_id
                    ]);
                }
                header("Location: collaborations_management.php?prefix=" . $prefix . "&success_msg=updated");
                exit;
            } else {
                // Insert
                if (!$logoPath) {
                    throw new RuntimeException("Partner logo is required for new entries.");
                }
                $stmt = $pdo->prepare("INSERT INTO `collaborations` (partner_name, logo_path, profile_description, collab_type, website_url, institute_prefix, display_order, status) VALUES (:partner_name, :logo_path, :profile_description, :collab_type, :website_url, :institute_prefix, :display_order, :status)");
                $stmt->execute([
                    ':partner_name'        => $partner_name,
                    ':logo_path'           => $logoPath,
                    ':profile_description' => $profile_description,
                    ':collab_type'         => $collab_type,
                    ':website_url'         => $website_url,
                    ':institute_prefix'    => $prefix,
                    ':display_order'       => $display_order,
                    ':status'              => $status
                ]);
                header("Location: collaborations_management.php?prefix=" . $prefix . "&success_msg=inserted");
                exit;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Fetch collaborations list (filtered by prefix, or global for super admin)
$collaborations = [];
try {
    $where = isSuperAdmin() ? "1=1" : "(institute_prefix = '$prefix' OR institute_prefix = 'all')";
    $stmt = $pdo->query("SELECT * FROM `collaborations` WHERE $where ORDER BY display_order ASC, id DESC");
    $collaborations = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignore error
}

$pageTitle = "Collaborations Management | ANRF-PAIR";
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
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Collaborations</a></li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Collaborations updated successfully.
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
                    <h4 class="card-title">Institutional Partners & Industry Collaborations</h4>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
                        <i class="fa fa-plus me-1"></i> Add Collaboration
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Logo</th>
                                    <th>Partner Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Order</th>
                                    <th style="width: 100px;">Status</th>
                                    <th style="width: 100px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($collaborations)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No institutional collaborations listed. Frontend page falls back to static description list.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($collaborations as $c): ?>
                                    <tr>
                                        <td>
                                            <img src="../<?= htmlspecialchars($c['logo_path']) ?>" alt="Logo" style="width: 50px; height: 50px; object-fit: contain; border-radius: 4px; border: 1px solid #ddd; background: #fff;">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($c['partner_name']) ?></strong>
                                            <?php if (!empty($c['website_url'])): ?>
                                                <br><a href="<?= htmlspecialchars($c['website_url']) ?>" target="_blank" class="text-info" style="font-size: 12px;"><i class="fa fa-external-link"></i> Website</a>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($c['collab_type']) ?></span></td>
                                        <td><small><?= htmlspecialchars($c['profile_description'] ? (strlen($c['profile_description']) > 100 ? substr($c['profile_description'], 0, 100) . '...' : $c['profile_description']) : 'No description') ?></small></td>
                                        <td><?= (int)$c['display_order'] ?></td>
                                        <td>
                                            <span class="badge <?= $c['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= htmlspecialchars($c['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-xs me-1" onclick="openEditModal(<?= htmlspecialchars(json_encode($c)) ?>)">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <a href="collaborations_management.php?prefix=<?= $prefix ?>&action=delete&id=<?= $c['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this collaboration?');">
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
<div class="modal fade" id="collabModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Collaboration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Partner / Institution Name *</label>
                            <input type="text" name="partner_name" id="partner_name" class="form-control" placeholder="e.g. Osmania University or Tata Consultancy Services" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Collaboration Type</label>
                            <select name="collab_type" id="collab_type" class="form-control" required>
                                <option value="Academic">Academic Institution</option>
                                <option value="Research">Research Organization</option>
                                <option value="Industry">Industry Partner</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" id="logoLabel">Upload Logo Image *</label>
                        <input type="file" name="logo" id="logo" class="form-control" accept="image/*">
                        <div class="form-text text-muted">Upload partner brand logo (JPG, JPEG, PNG, WEBP, or SVG, max size 3MB).</div>
                        <div id="logoPreviewContainer" class="mt-2" style="display: none;">
                            <span class="text-muted d-block mb-1">Current Logo:</span>
                            <img id="logoPreview" src="" alt="Preview" style="max-height: 80px; border-radius: 4px; border: 1px solid #ddd; background: #fff; padding: 4px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Partner Profile Description</label>
                        <textarea name="profile_description" id="profile_description" class="form-control" rows="4" placeholder="Brief outline of collaboration goals, joint projects, or institutional profiles."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website / Partner Link URL</label>
                            <input type="url" name="website_url" id="website_url" class="form-control" placeholder="https://partner-website.org">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" id="display_order" class="form-control" value="10" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Partner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var collabModal;
    document.addEventListener("DOMContentLoaded", function() {
        collabModal = new bootstrap.Modal(document.getElementById('collabModal'));
    });

    function openAddModal() {
        document.getElementById('edit_id').value = '';
        document.getElementById('partner_name').value = '';
        document.getElementById('profile_description').value = '';
        document.getElementById('collab_type').value = 'Academic';
        document.getElementById('website_url').value = '';
        document.getElementById('display_order').value = '10';
        document.getElementById('status').value = 'Active';
        document.getElementById('logo').required = true;
        document.getElementById('logoLabel').innerText = 'Upload Logo Image *';
        document.getElementById('logoPreviewContainer').style.display = 'none';
        document.getElementById('modalTitle').innerText = 'Add Collaboration Partner';
        collabModal.show();
    }

    function openEditModal(collab) {
        document.getElementById('edit_id').value = collab.id;
        document.getElementById('partner_name').value = collab.partner_name;
        document.getElementById('profile_description').value = collab.profile_description;
        document.getElementById('collab_type').value = collab.collab_type;
        document.getElementById('website_url').value = collab.website_url;
        document.getElementById('display_order').value = collab.display_order;
        document.getElementById('status').value = collab.status;
        document.getElementById('logo').required = false;
        document.getElementById('logoLabel').innerText = 'Replace Logo Image';
        
        if (collab.logo_path) {
            document.getElementById('logoPreview').src = '../' + collab.logo_path;
            document.getElementById('logoPreviewContainer').style.display = 'block';
        } else {
            document.getElementById('logoPreviewContainer').style.display = 'none';
        }
        
        document.getElementById('modalTitle').innerText = 'Edit Collaboration Partner';
        collabModal.show();
    }
</script>

<?php include 'footer.php'; ?>
