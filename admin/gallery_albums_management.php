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

// Self-healing database tables
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `gallery_albums` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `album_name` VARCHAR(255) NOT NULL,
            `album_date` DATE DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `gallery_photos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `album_id` INT NOT NULL,
            `photo_path` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(255) DEFAULT '',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_gallery_photos_album_custom` FOREIGN KEY (`album_id`) REFERENCES `gallery_albums` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
} catch (PDOException $e) {
    // Ignore error
}

$album_id = isset($_GET['album_id']) ? (int)$_GET['album_id'] : null;

// 1. HANDLE ALBUM DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete_album' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    try {
        // Fetch all photos in album to delete files from server
        $stmt = $pdo->prepare("SELECT photo_path FROM `gallery_photos` WHERE album_id = ?");
        $stmt->execute([$del_id]);
        $photos = $stmt->fetchAll();
        foreach ($photos as $ph) {
            if (!empty($ph['photo_path']) && file_exists('../' . $ph['photo_path'])) {
                @unlink('../' . $ph['photo_path']);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM `gallery_albums` WHERE id = ?");
        $stmt->execute([$del_id]);
        header("Location: gallery_albums_management.php?prefix=" . $prefix . "&success_msg=album_deleted");
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to delete album: ' . $e->getMessage();
    }
}

// 2. HANDLE PHOTO DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete_photo' && isset($_GET['photo_id'])) {
    $del_photo_id = (int)$_GET['photo_id'];
    try {
        $stmt = $pdo->prepare("SELECT photo_path FROM `gallery_photos` WHERE id = ?");
        $stmt->execute([$del_photo_id]);
        $p_path = $stmt->fetchColumn();
        if ($p_path && file_exists('../' . $p_path)) {
            @unlink('../' . $p_path);
        }

        $stmt = $pdo->prepare("DELETE FROM `gallery_photos` WHERE id = ?");
        $stmt->execute([$del_photo_id]);
        header("Location: gallery_albums_management.php?prefix=" . $prefix . "&album_id=" . $album_id . "&success_msg=photo_deleted");
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to delete photo: ' . $e->getMessage();
    }
}

// 3. SHOW SUCCESS MESSAGE
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 4. HANDLE ALBUM SUBMISSIONS (ADD / UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'album') {
    $album_name = trim($_POST['album_name'] ?? '');
    $album_date = $_POST['album_date'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $edit_album_id = !empty($_POST['edit_album_id']) ? (int)$_POST['edit_album_id'] : null;

    if (empty($album_name)) {
        $error = 'Album Name is required.';
    } else {
        try {
            if ($edit_album_id) {
                $stmt = $pdo->prepare("UPDATE `gallery_albums` SET album_name = :name, album_date = :date, description = :desc WHERE id = :id");
                $stmt->execute([':name' => $album_name, ':date' => $album_date ?: null, ':desc' => $description, ':id' => $edit_album_id]);
                header("Location: gallery_albums_management.php?prefix=" . $prefix . "&success_msg=album_updated");
            } else {
                $stmt = $pdo->prepare("INSERT INTO `gallery_albums` (album_name, album_date, description, institute_prefix) VALUES (:name, :date, :desc, :prefix)");
                $stmt->execute([':name' => $album_name, ':date' => $album_date ?: null, ':desc' => $description, ':prefix' => $prefix]);
                header("Location: gallery_albums_management.php?prefix=" . $prefix . "&success_msg=album_created");
            }
            exit;
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// 5. HANDLE PHOTO UPLOAD SUBMISSIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'photos') {
    $caption = trim($_POST['caption'] ?? '');
    if (!$album_id) {
        $error = 'No active album selected.';
    } else {
        try {
            $uploadDir = '../uploads/gallery/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (empty($_FILES['photos']['name'][0])) {
                throw new RuntimeException("Please select at least one photo to upload.");
            }

            $filesCount = count($_FILES['photos']['name']);
            $uploadedCount = 0;

            for ($i = 0; $i < $filesCount; $i++) {
                $fName = $_FILES['photos']['name'][$i];
                $fTmp  = $_FILES['photos']['tmp_name'][$i];
                $fError = $_FILES['photos']['error'][$i];
                $fSize = $_FILES['photos']['size'][$i];

                if ($fError === UPLOAD_ERR_OK) {
                    if ($fSize > 5 * 1024 * 1024) {
                        continue; // Skip oversized files
                    }

                    $ext = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        continue; // Skip invalid extensions
                    }

                    $destFileName = uniqid('img_', true) . '.' . $ext;
                    if (move_uploaded_file($fTmp, $uploadDir . $destFileName)) {
                        $pPath = 'uploads/gallery/' . $destFileName;
                        $stmt = $pdo->prepare("INSERT INTO `gallery_photos` (album_id, photo_path, caption) VALUES (?, ?, ?)");
                        $stmt->execute([$album_id, $pPath, $caption]);
                        $uploadedCount++;
                    }
                }
            }

            header("Location: gallery_albums_management.php?prefix=" . $prefix . "&album_id=" . $album_id . "&success_msg=uploaded_" . $uploadedCount);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Fetch active albums list
$albums = [];
try {
    $where = isSuperAdmin() ? "1=1" : "(institute_prefix = '$prefix' OR institute_prefix = 'all')";
    $stmt = $pdo->query("SELECT * FROM `gallery_albums` WHERE $where ORDER BY album_date DESC, id DESC");
    $albums = $stmt->fetchAll();
} catch (PDOException $e) {
    // ignore
}

// Fetch photos if an album is selected
$activeAlbum = null;
$photos = [];
if ($album_id) {
    $stmt = $pdo->prepare("SELECT * FROM `gallery_albums` WHERE id = ?");
    $stmt->execute([$album_id]);
    $activeAlbum = $stmt->fetch();
    
    if ($activeAlbum) {
        $stmt = $pdo->prepare("SELECT * FROM `gallery_photos` WHERE album_id = ? ORDER BY id DESC");
        $stmt->execute([$album_id]);
        $photos = $stmt->fetchAll();
    }
}

$pageTitle = "Gallery Albums CMS | ANRF-PAIR";
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
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Gallery Albums</a></li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Action processed successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- LEFT COLUMN: ALBUMS LIST -->
                <div class="col-xl-5 col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Gallery Albums / Workshops</h4>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openAddAlbumModal()">
                                <i class="fa fa-plus me-1"></i> New Album
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php if (empty($albums)): ?>
                                    <div class="text-center text-muted p-4">No gallery albums created.</div>
                                <?php else: ?>
                                    <?php foreach ($albums as $al): ?>
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center mb-2 rounded border <?= $album_id === (int)$al['id'] ? 'bg-light border-primary' : '' ?>" style="padding: 15px;">
                                            <a href="gallery_albums_management.php?prefix=<?= $prefix ?>&album_id=<?= $al['id'] ?>" style="flex: 1; text-decoration: none; color: inherit;">
                                                <h5 class="mb-1" style="font-weight: 700; color: #1e3a8a;"><?= htmlspecialchars($al['album_name']) ?></h5>
                                                <small class="text-muted"><i class="fa fa-calendar-o mr-1"></i> <?= $al['album_date'] ? date('M d, Y', strtotime($al['album_date'])) : 'No Date' ?></small>
                                            </a>
                                            <div class="ms-2">
                                                <button class="btn btn-warning btn-xs" onclick="openEditAlbumModal(<?= htmlspecialchars(json_encode($al)) ?>)"><i class="fa fa-pencil"></i></button>
                                                <a href="gallery_albums_management.php?prefix=<?= $prefix ?>&action=delete_album&id=<?= $al['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Deleting this album will permanently delete all its photos! Continue?');"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: PHOTOS MANAGER -->
                <div class="col-xl-7 col-lg-12">
                    <?php if (!$album_id || !$activeAlbum): ?>
                        <div class="card bg-light border" style="border-style: dashed !important; border-width: 2px !important; min-height: 350px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-center p-4">
                                <i class="fas fa-images text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                                <h4 class="text-muted">Select an Album</h4>
                                <p class="text-muted">Choose a gallery album from the left column to upload and manage photos.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Album Detail & Photo Upload Form -->
                        <div class="card">
                            <div class="card-header border-bottom">
                                <div>
                                    <h4 class="card-title mb-1" style="color: #bc2121; font-weight:700;"><?= htmlspecialchars($activeAlbum['album_name']) ?></h4>
                                    <p class="text-muted mb-0" style="font-size: 13px;"><?= htmlspecialchars($activeAlbum['description'] ?: 'No description provided.') ?></p>
                                </div>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data" class="row align-items-end g-3 mb-4 bg-light p-3 rounded border">
                                    <input type="hidden" name="form_type" value="photos">
                                    <div class="col-md-5">
                                        <label class="form-label" style="font-weight:600;">Select Photos *</label>
                                        <input type="file" name="photos[]" class="form-control form-control-sm" multiple accept="image/*" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label" style="font-weight:600;">Caption (Optional)</label>
                                        <input type="text" name="caption" class="form-control form-control-sm" placeholder="Caption for these uploads">
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i> Upload</button>
                                    </div>
                                </form>

                                <h5 class="mb-3" style="font-weight:700; color: #334155;">Uploaded Photos (<?= count($photos) ?>)</h5>
                                <div class="row g-3">
                                    <?php if (empty($photos)): ?>
                                        <div class="col-12 text-center text-muted p-4 border rounded" style="border-style: dashed !important;">No photos uploaded to this album yet.</div>
                                    <?php else: ?>
                                        <?php foreach ($photos as $p): ?>
                                            <div class="col-sm-4 col-xs-6">
                                                <div class="card h-100 border p-1 position-relative" style="box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                                    <img src="../<?= htmlspecialchars($p['photo_path']) ?>" alt="Photo" style="height: 120px; object-fit: cover; border-radius: 4px;">
                                                    <div class="p-2" style="font-size: 11px;">
                                                        <span class="text-truncate d-block text-muted"><?= htmlspecialchars($p['caption'] ?: '(No Caption)') ?></span>
                                                    </div>
                                                    <a href="gallery_albums_management.php?prefix=<?= $prefix ?>&album_id=<?= $album_id ?>&action=delete_photo&photo_id=<?= $p['id'] ?>" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-2" style="padding: 2px 6px; border-radius: 50%; opacity: 0.85;" onclick="return confirm('Are you sure you want to delete this photo?');">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Form for Albums -->
<div class="modal fade" id="albumModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="form_type" value="album">
                <input type="hidden" name="edit_album_id" id="edit_album_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="albumModalTitle">Add Gallery Album</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Album / Workshop Title *</label>
                        <input type="text" name="album_name" id="album_name" class="form-control" required placeholder="e.g. Kick off Meeting and Workshop Photos">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event Date</label>
                        <input type="date" name="album_date" id="album_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Album Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Brief outline/summary of the workshop or event."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Album</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var albumModal;
    document.addEventListener("DOMContentLoaded", function() {
        albumModal = new bootstrap.Modal(document.getElementById('albumModal'));
    });

    function openAddAlbumModal() {
        document.getElementById('edit_album_id').value = '';
        document.getElementById('album_name').value = '';
        document.getElementById('album_date').value = '';
        document.getElementById('description').value = '';
        document.getElementById('albumModalTitle').innerText = 'Create Gallery Album';
        albumModal.show();
    }

    function openEditAlbumModal(album) {
        document.getElementById('edit_album_id').value = album.id;
        document.getElementById('album_name').value = album.album_name;
        document.getElementById('album_date').value = album.album_date || '';
        document.getElementById('description').value = album.description || '';
        document.getElementById('albumModalTitle').innerText = 'Edit Gallery Album';
        albumModal.show();
    }
</script>

<?php include 'footer.php'; ?>
