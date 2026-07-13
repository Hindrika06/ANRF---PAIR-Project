<?php
session_start();

require_once 'role_access.php';

// Auth Guard: Only Super Admin can manage homepage banners
if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix']) || !isSuperAdmin()) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/db.php';

$success = false;
$error   = '';

// Self-healing: create table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `homepage_banners` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `image_path` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(500) DEFAULT '',
            `display_order` INT NOT NULL DEFAULT 10,
            `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
} catch (PDOException $e) {
    // silently catch database table creation error
}

// 1. HANDLE DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        // Fetch image path to delete the file
        $stmt = $pdo->prepare("SELECT image_path FROM `homepage_banners` WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        $row = $stmt->fetch();
        if ($row && !empty($row['image_path']) && file_exists('../' . $row['image_path'])) {
            @unlink('../' . $row['image_path']);
        }

        $stmt = $pdo->prepare("DELETE FROM `homepage_banners` WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        header("Location: banner_management.php?success_msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to delete banner: ' . $e->getMessage();
    }
}

// 2. SHOW QUICK SUCCESS MESSAGES POST-REDIRECT
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS (ADD OR UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption       = trim($_POST['caption'] ?? '');
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 10;
    $status        = $_POST['status'] ?? 'Active';
    $edit_id       = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    try {
        $uploadDir = '../uploads/slider/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $imagePath = null;

        // Handle Image Upload
        if (!empty($_FILES['image']['name'])) {
            $f = $_FILES['image'];

            if ($f['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException("File upload failed with error code: " . $f['error']);
            }

            if ($f['size'] > 5 * 1024 * 1024) {
                throw new RuntimeException("File size exceeds 5 MB limit.");
            }

            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                throw new RuntimeException("Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed.");
            }

            $destFileName = uniqid('slide_', true) . '.' . $ext;
            $destFullPath = $uploadDir . $destFileName;

            if (!move_uploaded_file($f['tmp_name'], $destFullPath)) {
                throw new RuntimeException("Could not save the uploaded banner image.");
            }
            $imagePath = 'uploads/slider/' . $destFileName;

            // Delete old banner image if editing
            if ($edit_id) {
                $stmt = $pdo->prepare("SELECT image_path FROM `homepage_banners` WHERE id = :id");
                $stmt->execute([':id' => $edit_id]);
                $oldRow = $stmt->fetch();
                if ($oldRow && !empty($oldRow['image_path']) && file_exists('../' . $oldRow['image_path'])) {
                    @unlink('../' . $oldRow['image_path']);
                }
            }
        }

        if ($edit_id) {
            // Update Banner
            if ($imagePath) {
                $stmt = $pdo->prepare("UPDATE `homepage_banners` SET image_path = :image_path, caption = :caption, display_order = :display_order, status = :status WHERE id = :id");
                $stmt->execute([
                    ':image_path'    => $imagePath,
                    ':caption'       => $caption,
                    ':display_order' => $display_order,
                    ':status'        => $status,
                    ':id'            => $edit_id
                ]);
            } else {
                $stmt = $pdo->prepare("UPDATE `homepage_banners` SET caption = :caption, display_order = :display_order, status = :status WHERE id = :id");
                $stmt->execute([
                    ':caption'       => $caption,
                    ':display_order' => $display_order,
                    ':status'        => $status,
                    ':id'            => $edit_id
                ]);
            }
            header("Location: banner_management.php?success_msg=updated");
            exit;
        } else {
            // Insert New Banner
            if (!$imagePath) {
                throw new RuntimeException("Banner image is required for new entries.");
            }
            $stmt = $pdo->prepare("INSERT INTO `homepage_banners` (image_path, caption, display_order, status) VALUES (:image_path, :caption, :display_order, :status)");
            $stmt->execute([
                ':image_path'    => $imagePath,
                ':caption'       => $caption,
                ':display_order' => $display_order,
                ':status'        => $status
            ]);
            header("Location: banner_management.php?success_msg=inserted");
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch all banners
$banners = [];
try {
    $stmt = $pdo->query("SELECT * FROM `homepage_banners` ORDER BY display_order ASC, id DESC");
    $banners = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignore error
}

$pageTitle = "Homepage Banner Management | ANRF-PAIR";
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
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Homepage Banners</a></li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Banners updated successfully.
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
                    <h4 class="card-title">Homepage Slides & Banners</h4>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
                        <i class="fa fa-plus me-1"></i> Add New Slide
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Preview</th>
                                    <th>Caption</th>
                                    <th style="width: 120px;">Display Order</th>
                                    <th style="width: 100px;">Status</th>
                                    <th style="width: 100px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($banners)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No custom slides uploaded. The system currently falls back to `assets/img/1.jpg`.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($banners as $b): ?>
                                    <tr>
                                        <td>
                                            <img src="../<?= htmlspecialchars($b['image_path']) ?>" alt="Slide" style="width: 70px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                                        </td>
                                        <td><?= htmlspecialchars($b['caption'] ?: '(No Caption)') ?></td>
                                        <td><?= (int)$b['display_order'] ?></td>
                                        <td>
                                            <span class="badge <?= $b['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= htmlspecialchars($b['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-xs me-1" onclick="openEditModal(<?= htmlspecialchars(json_encode($b)) ?>)">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <a href="banner_management.php?action=delete&id=<?= $b['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this slide?');">
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
<div class="modal fade" id="slideModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Homepage Slide</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" id="imageLabel">Upload Image *</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                        <div id="imageHelp" class="form-text text-muted">Upload a horizontal banner photo (Recommended: 1920x800, max size 5MB).</div>
                        <div id="imagePreviewContainer" class="mt-2" style="display: none;">
                            <span class="text-muted d-block mb-1">Current Banner:</span>
                            <img id="imagePreview" src="" alt="Preview" style="max-height: 150px; border-radius: 6px; border: 1px solid #ddd;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Overlay Caption</label>
                        <input type="text" name="caption" id="caption" class="form-control" placeholder="Optional overlay heading text">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" id="display_order" class="form-control" value="10" required>
                        </div>
                        <div class="col-md-6 mb-3">
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
                    <button type="submit" class="btn btn-primary">Save Slide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var slideModal;
    document.addEventListener("DOMContentLoaded", function() {
        slideModal = new bootstrap.Modal(document.getElementById('slideModal'));
    });

    function openAddModal() {
        document.getElementById('edit_id').value = '';
        document.getElementById('caption').value = '';
        document.getElementById('display_order').value = '10';
        document.getElementById('status').value = 'Active';
        document.getElementById('image').required = true;
        document.getElementById('imageLabel').innerText = 'Upload Image *';
        document.getElementById('imagePreviewContainer').style.display = 'none';
        document.getElementById('modalTitle').innerText = 'Add Homepage Slide';
        slideModal.show();
    }

    function openEditModal(slide) {
        document.getElementById('edit_id').value = slide.id;
        document.getElementById('caption').value = slide.caption;
        document.getElementById('display_order').value = slide.display_order;
        document.getElementById('status').value = slide.status;
        document.getElementById('image').required = false;
        document.getElementById('imageLabel').innerText = 'Replace Image';
        
        if (slide.image_path) {
            document.getElementById('imagePreview').src = '../' + slide.image_path;
            document.getElementById('imagePreviewContainer').style.display = 'block';
        } else {
            document.getElementById('imagePreviewContainer').style.display = 'none';
        }
        
        document.getElementById('modalTitle').innerText = 'Edit Homepage Slide';
        slideModal.show();
    }
</script>

<?php include 'footer.php'; ?>
