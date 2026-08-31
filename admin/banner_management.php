<?php
require_once 'auth_check.php';
require_once 'role_access.php';
requireHubAdmin();

date_default_timezone_set('Asia/Kolkata');
require_once 'config/db.php';

// Resolve current institute prefix according to role access rules
// Super Admin can view specific or 'all'; Institute Admin is STRICTLY locked to $_SESSION['institute_prefix']
$prefix = resolveAdminPrefix();
$isSuper = isSuperAdmin();

// Generate CSRF Token for Form Security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = false;
$error   = '';

// 0. Self-healing DB Schema Check
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `homepage_banners` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL DEFAULT '',
            `short_description` TEXT DEFAULT NULL,
            `target_url` VARCHAR(1000) DEFAULT '',
            `start_datetime` DATETIME DEFAULT NULL,
            `end_datetime` DATETIME DEFAULT NULL,
            `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all',
            `image_path` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(500) DEFAULT '',
            `display_order` INT NOT NULL DEFAULT 10,
            `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_status_dates` (`status`, `start_datetime`, `end_datetime`),
            KEY `idx_display_order` (`display_order`),
            KEY `idx_inst_prefix` (`institute_prefix`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    $columns = $pdo->query("SHOW COLUMNS FROM `homepage_banners`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('title', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `id`");
    }
    if (!in_array('short_description', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `short_description` TEXT DEFAULT NULL AFTER `title`");
    }
    if (!in_array('target_url', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `target_url` VARCHAR(1000) DEFAULT '' AFTER `short_description`");
    }
    if (!in_array('start_datetime', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `start_datetime` DATETIME DEFAULT NULL AFTER `target_url`");
    }
    if (!in_array('end_datetime', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `end_datetime` DATETIME DEFAULT NULL AFTER `start_datetime`");
    }
    if (!in_array('institute_prefix', $columns)) {
        $pdo->exec("ALTER TABLE `homepage_banners` ADD COLUMN `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all' AFTER `end_datetime`");
        $pdo->exec("ALTER TABLE `homepage_banners` ADD INDEX `idx_inst_prefix` (`institute_prefix`)");
    }
} catch (PDOException $e) {
    // Silently handle schema check
}

// 1. HANDLE DELETE ACTION WITH SERVER-SIDE AUTHORIZATION & CSRF VALIDATION (POST & GET FALLBACK)
if ((($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['delete_id'])) || (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']))) {
    try {
        $isPostDelete = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']));
        $token = $isPostDelete ? ($_POST['csrf_token'] ?? '') : ($_GET['csrf_token'] ?? '');
        
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new RuntimeException("Security Error: Invalid or missing CSRF token. Request rejected.");
        }

        $deleteId = $isPostDelete ? (int)$_POST['delete_id'] : (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new RuntimeException("Poster not found.");
        }

        // Server-side institute access authorization check
        if (!canEditInstitute($row['institute_prefix'])) {
            throw new RuntimeException("Security Error: You are not authorized to delete posters belonging to " . strtoupper($row['institute_prefix']));
        }

        if (!empty($row['image_path']) && file_exists('../' . $row['image_path'])) {
            @unlink('../' . $row['image_path']);
        }

        $stmt = $pdo->prepare("DELETE FROM `homepage_banners` WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
        adminRedirect(['success_msg' => 'deleted']);
    } catch (Exception $e) {
        $error = 'Failed to delete poster: ' . $e->getMessage();
    }
}

// 2. SHOW QUICK SUCCESS MESSAGES POST-REDIRECT
if (isset($_GET['success_msg'])) {
    $success = true;
}

/**
 * Image Optimization Helper using GD
 */
function optimizeAndSavePoster($tmpPath, $destFullPath, $mimeType) {
    list($width, $height) = @getimagesize($tmpPath);
    if (!$width || !$height) {
        return move_uploaded_file($tmpPath, $destFullPath) || copy($tmpPath, $destFullPath);
    }

    $srcImg = null;
    if (($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') && function_exists('imagecreatefromjpeg')) {
        $srcImg = @imagecreatefromjpeg($tmpPath);
    } elseif ($mimeType === 'image/png' && function_exists('imagecreatefrompng')) {
        $srcImg = @imagecreatefrompng($tmpPath);
    } elseif ($mimeType === 'image/webp' && function_exists('imagecreatefromwebp')) {
        $srcImg = @imagecreatefromwebp($tmpPath);
    }

    if (!$srcImg || !function_exists('imagecreatetruecolor')) {
        return move_uploaded_file($tmpPath, $destFullPath) || copy($tmpPath, $destFullPath);
    }

    $maxDim = 3840;
    $newW = $width;
    $newH = $height;

    if ($width > $maxDim || $height > $maxDim) {
        if ($width > $height) {
            $newW = $maxDim;
            $newH = (int)round(($height / $width) * $maxDim);
        } else {
            $newH = $maxDim;
            $newW = (int)round(($width / $height) * $maxDim);
        }
    }

    $dstImg = @imagecreatetruecolor($newW, $newH);
    if (!$dstImg) {
        imagedestroy($srcImg);
        return move_uploaded_file($tmpPath, $destFullPath) || copy($tmpPath, $destFullPath);
    }

    if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
        @imagealphablending($dstImg, false);
        @imagesavealpha($dstImg, true);
    }

    @imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $width, $height);

    $ext = strtolower(pathinfo($destFullPath, PATHINFO_EXTENSION));
    $saved = false;

    if (($ext === 'jpg' || $ext === 'jpeg') && function_exists('imagejpeg')) {
        $saved = @imagejpeg($dstImg, $destFullPath, 88);
    } elseif ($ext === 'png' && function_exists('imagepng')) {
        $saved = @imagepng($dstImg, $destFullPath, 6);
    } elseif ($ext === 'webp' && function_exists('imagewebp')) {
        $saved = @imagewebp($dstImg, $destFullPath, 88);
    }

    if (!$saved) {
        $saved = move_uploaded_file($tmpPath, $destFullPath) || copy($tmpPath, $destFullPath);
    }

    if ($srcImg) @imagedestroy($srcImg);
    if ($dstImg) @imagedestroy($dstImg);

    return $saved;
}

// 3. HANDLE FORM SUBMISSIONS (ADD OR UPDATE)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    try {
        // Validate CSRF Token
        $userCsrf = $_POST['csrf_token'] ?? '';
        if (empty($userCsrf) || !hash_equals($_SESSION['csrf_token'], $userCsrf)) {
            throw new RuntimeException("Security Error: Invalid CSRF token. Please refresh the page and try again.");
        }

        $title             = trim($_POST['title'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $target_url        = trim($_POST['target_url'] ?? '');
        $display_order     = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 10;
        $status            = in_array($_POST['status'] ?? 'Active', ['Active', 'Inactive']) ? $_POST['status'] : 'Active';
        $edit_id           = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

        // Server-Side Institute Isolation:
        // Super Admin can select target prefix; regular Spoke Admin is LOCKED strictly to their assigned $_SESSION['institute_prefix']
        if ($isSuper) {
            $targetPrefix = trim($_POST['institute_prefix'] ?? $prefix);
            if (!isValidPrefix($targetPrefix)) {
                $targetPrefix = ($prefix === 'all') ? 'all' : $prefix;
            }
        } else {
            // Institute Admin CANNOT modify target institute prefix — locked to assigned session
            $targetPrefix = $_SESSION['institute_prefix'];
        }

        $start_datetime = !empty($_POST['start_datetime']) ? date('Y-m-d H:i:s', strtotime($_POST['start_datetime'])) : null;
        $end_datetime   = !empty($_POST['end_datetime']) ? date('Y-m-d H:i:s', strtotime($_POST['end_datetime'])) : null;

        if (empty($title)) {
            throw new RuntimeException("Poster Title is required.");
        }

        if ($start_datetime && $end_datetime && strtotime($start_datetime) > strtotime($end_datetime)) {
            throw new RuntimeException("End Date & Time must be after Start Date & Time.");
        }

        $uploadDir = '../uploads/slider/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $imagePath = null;

        // File Upload Handling & Security Inspection
        if (!empty($_FILES['image']['name'])) {
            $f = $_FILES['image'];

            if ($f['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException("File upload failed with error code: " . $f['error']);
            }

            if ($f['size'] > 10 * 1024 * 1024) {
                throw new RuntimeException("File size exceeds 10 MB limit.");
            }

            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                throw new RuntimeException("Invalid file type. Only JPG, JPEG, PNG, and WEBP images are allowed.");
            }

            $mime = '';
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $f['tmp_name']);
                finfo_close($finfo);
            } elseif (function_exists('mime_content_type')) {
                $mime = mime_content_type($f['tmp_name']);
            }

            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!empty($mime) && !in_array($mime, $allowedMimes)) {
                throw new RuntimeException("Security Error: Uploaded file is not a valid image payload.");
            }

            if (!getimagesize($f['tmp_name'])) {
                throw new RuntimeException("Security Error: Uploaded file is corrupted or not a readable image.");
            }

            $destFileName = uniqid('poster_', true) . '.' . $ext;
            $destFullPath = $uploadDir . $destFileName;

            if (!optimizeAndSavePoster($f['tmp_name'], $destFullPath, $mime)) {
                throw new RuntimeException("Could not save the uploaded poster image.");
            }
            $imagePath = 'uploads/slider/' . $destFileName;

            // Clean up old image if editing
            if ($edit_id) {
                $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE id = :id");
                $stmt->execute([':id' => $edit_id]);
                $oldRow = $stmt->fetch();
                if ($oldRow) {
                    if (!canEditInstitute($oldRow['institute_prefix'])) {
                        throw new RuntimeException("Unauthorized: You do not have permission to edit this poster.");
                    }
                    if (!empty($oldRow['image_path']) && file_exists('../' . $oldRow['image_path'])) {
                        @unlink('../' . $oldRow['image_path']);
                    }
                }
            }
        }

        if ($edit_id) {
            // Check Authorization for Existing Record
            $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE id = :id");
            $stmt->execute([':id' => $edit_id]);
            $existing = $stmt->fetch();
            if (!$existing) {
                throw new RuntimeException("Poster record not found.");
            }
            if (!canEditInstitute($existing['institute_prefix'])) {
                throw new RuntimeException("Security Error: You are not authorized to modify posters belonging to " . strtoupper($existing['institute_prefix']));
            }

            if ($imagePath) {
                $stmt = $pdo->prepare("
                    UPDATE `homepage_banners`
                    SET `title` = :title,
                        `short_description` = :short_description,
                        `target_url` = :target_url,
                        `start_datetime` = :start_datetime,
                        `end_datetime` = :end_datetime,
                        `institute_prefix` = :institute_prefix,
                        `image_path` = :image_path,
                        `caption` = :caption,
                        `display_order` = :display_order,
                        `status` = :status
                    WHERE `id` = :id
                ");
                $stmt->execute([
                    ':title'             => $title,
                    ':short_description' => $short_description,
                    ':target_url'        => $target_url,
                    ':start_datetime'    => $start_datetime,
                    ':end_datetime'      => $end_datetime,
                    ':institute_prefix'  => $targetPrefix,
                    ':image_path'        => $imagePath,
                    ':caption'           => $title,
                    ':display_order'     => $display_order,
                    ':status'            => $status,
                    ':id'                => $edit_id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE `homepage_banners`
                    SET `title` = :title,
                        `short_description` = :short_description,
                        `target_url` = :target_url,
                        `start_datetime` = :start_datetime,
                        `end_datetime` = :end_datetime,
                        `institute_prefix` = :institute_prefix,
                        `caption` = :caption,
                        `display_order` = :display_order,
                        `status` = :status
                    WHERE `id` = :id
                ");
                $stmt->execute([
                    ':title'             => $title,
                    ':short_description' => $short_description,
                    ':target_url'        => $target_url,
                    ':start_datetime'    => $start_datetime,
                    ':end_datetime'      => $end_datetime,
                    ':institute_prefix'  => $targetPrefix,
                    ':caption'           => $title,
                    ':display_order'     => $display_order,
                    ':status'            => $status,
                    ':id'                => $edit_id
                ]);
            }
            adminRedirect(['success_msg' => 'updated']);
        } else {
            // Insert New Poster
            if (!$imagePath) {
                throw new RuntimeException("Poster image file is required for new entries.");
            }
            $stmt = $pdo->prepare("
                INSERT INTO `homepage_banners`
                (`title`, `short_description`, `target_url`, `start_datetime`, `end_datetime`, `institute_prefix`, `image_path`, `caption`, `display_order`, `status`)
                VALUES (:title, :short_description, :target_url, :start_datetime, :end_datetime, :institute_prefix, :image_path, :caption, :display_order, :status)
            ");
            $stmt->execute([
                ':title'             => $title,
                ':short_description' => $short_description,
                ':target_url'        => $target_url,
                ':start_datetime'    => $start_datetime,
                ':end_datetime'      => $end_datetime,
                ':institute_prefix'  => $targetPrefix,
                ':image_path'        => $imagePath,
                ':caption'           => $title,
                ':display_order'     => $display_order,
                ':status'            => $status
            ]);
            adminRedirect(['success_msg' => 'inserted']);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch banners for table display respecting role & institute context
$banners = [];
try {
    if ($isSuper && $prefix === 'all') {
        $stmt = $pdo->query("SELECT * FROM `homepage_banners` ORDER BY display_order ASC, start_datetime DESC, id DESC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `homepage_banners` WHERE institute_prefix = :prefix OR institute_prefix = 'all' ORDER BY display_order ASC, start_datetime DESC, id DESC");
        $stmt->execute([':prefix' => $prefix]);
    }
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silently catch fetch errors
}

$nowStr = date('Y-m-d H:i:s');
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
.status-badge-container {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
}
.live-badge {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.upcoming-badge {
    background: #fef9c3;
    color: #a16207;
    border: 1px solid #fef08a;
}
.expired-badge {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.inactive-badge {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
}
.poster-preview-card {
    position: relative;
    width: 100%;
    height: 220px;
    border-radius: 10px;
    overflow: hidden;
    background: #0f172a;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.6);
}
.poster-preview-bg {
    position: absolute;
    top: -10%;
    left: -10%;
    width: 120%;
    height: 120%;
    object-fit: cover;
    filter: blur(18px) brightness(0.45);
    transform: scale(1.1);
}
.poster-preview-img {
    position: relative;
    z-index: 2;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    margin: 0 auto;
    display: block;
}
.poster-preview-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 3;
    background: linear-gradient(transparent, rgba(15, 23, 42, 0.85));
    padding: 12px;
    color: #fff;
}
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Homepage Banners & Posters</a></li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Homepage banners & posters updated successfully.
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
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-0">Automatic Homepage Poster & Event Banner System</h4>
                        <small class="text-muted"><i class="fa fa-clock me-1"></i> Server Time (Asia/Kolkata): <strong><?= date('d-M-Y h:i A') ?></strong></small>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="openAddModalBtn" onclick="openAddModal()">
                        <i class="fa fa-plus me-1"></i> Add Event Poster / Slide
                    </button>
                </div>
                <div class="card-body">
                    <!-- Hidden POST Delete Form for CSRF Protection -->
                    <form id="deletePostForm" method="POST" action="<?= buildNavUrl('banner_management.php') ?>" style="display: none;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="delete_id" id="delete_id_input" value="">
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 90px;">Poster</th>
                                    <th>Title & Details</th>
                                    <th style="width: 110px;">Institute</th>
                                    <th style="width: 220px;">Active Schedule (IST)</th>
                                    <th style="width: 140px;">Calculated State</th>
                                    <th style="width: 90px; text-align: center;">Priority</th>
                                    <th style="width: 100px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($banners)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No custom posters configured for this context. The homepage will fall back to default assets or stay hidden if no active posters match current time.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($banners as $b):
                                        $titleText = !empty($b['title']) ? $b['title'] : ($b['caption'] ?: 'Untitled Poster');

                                        // Calculate live schedule status
                                        $stateLabel = '';
                                        $stateBadgeClass = '';

                                        if ($b['status'] === 'Inactive') {
                                            $stateLabel = '🔴 Inactive';
                                            $stateBadgeClass = 'inactive-badge';
                                        } else {
                                            $startTS = !empty($b['start_datetime']) ? strtotime($b['start_datetime']) : null;
                                            $endTS   = !empty($b['end_datetime']) ? strtotime($b['end_datetime']) : null;
                                            $nowTS   = strtotime($nowStr);

                                            if ($endTS && $endTS < $nowTS) {
                                                $stateLabel = '⚫ Expired';
                                                $stateBadgeClass = 'expired-badge';
                                            } elseif ($startTS && $startTS > $nowTS) {
                                                $stateLabel = '🟡 Scheduled';
                                                $stateBadgeClass = 'upcoming-badge';
                                            } else {
                                                $stateLabel = '🟢 Active';
                                                $stateBadgeClass = 'live-badge';
                                            }
                                        }

                                        $canModify = canEditInstitute($b['institute_prefix'] ?? 'all');
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="../<?= htmlspecialchars($b['image_path']) ?>" target="_blank">
                                                <img src="../<?= htmlspecialchars($b['image_path']) ?>" alt="Poster" style="width: 75px; height: 50px; object-fit: contain; background: #0f172a; border-radius: 6px; border: 1px solid #ddd; padding: 2px;">
                                            </a>
                                        </td>
                                        <td>
                                            <strong class="text-dark d-block"><?= htmlspecialchars($titleText) ?></strong>
                                            <?php if (!empty($b['short_description'])): ?>
                                                <small class="text-muted d-block text-truncate" style="max-width: 280px;"><?= htmlspecialchars($b['short_description']) ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($b['target_url'])): ?>
                                                <a href="<?= htmlspecialchars($b['target_url']) ?>" target="_blank" class="small text-primary text-decoration-none">
                                                    <i class="fa fa-external-link me-1"></i> Target Link
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars(getInstituteLabel($b['institute_prefix'] ?? 'all')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="d-block text-dark">
                                                <i class="fa fa-play-circle text-success me-1"></i> <strong>Start:</strong>
                                                <?= !empty($b['start_datetime']) ? date('d-M-Y h:i A', strtotime($b['start_datetime'])) : '<span class="text-muted">Immediate</span>' ?>
                                            </small>
                                            <small class="d-block text-dark mt-1">
                                                <i class="fa fa-stop-circle text-danger me-1"></i> <strong>End:</strong>
                                                <?= !empty($b['end_datetime']) ? date('d-M-Y h:i A', strtotime($b['end_datetime'])) : '<span class="text-muted">No Expiration</span>' ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="status-badge-container <?= $stateBadgeClass ?>">
                                                <?= $stateLabel ?>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            <span class="badge bg-light text-dark border"><?= (int)$b['display_order'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-info btn-xs me-1" onclick="viewBanner(<?= htmlspecialchars(json_encode($b)) ?>)">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                            <?php if ($canModify): ?>
                                                <button class="btn btn-warning btn-xs me-1" onclick="openEditModal(<?= htmlspecialchars(json_encode($b)) ?>)">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-danger btn-xs" onclick="confirmDeletePoster(<?= $b['id'] ?>)">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small"><i class="fa fa-lock me-1"></i> Read-Only</span>
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

        </div>
    </div>
</div>

<!-- Modal Form with Live Admin Preview -->
<div class="modal fade" id="slideModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" action="<?= buildNavUrl('banner_management.php') ?>" enctype="multipart/form-data" id="posterForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <?php if (!$isSuper): ?>
                <input type="hidden" name="institute_prefix" value="<?= htmlspecialchars($_SESSION['institute_prefix'] ?? $prefix) ?>">
                <?php endif; ?>

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalTitle">Configure Event Poster / Slide</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="editModeHeader" class="alert alert-info d-flex align-items-center justify-content-between mb-3 py-2 px-3" style="display: none;">
                        <div>
                            <i class="fa fa-edit me-1"></i> <strong>EDIT MODE:</strong> Editing Banner Record #<span id="editModeIdDisplay"></span>
                        </div>
                        <span class="badge bg-primary">Existing Image Preserved If No File Uploaded</span>
                    </div>

                    <div class="row">
                        <!-- Left Column: Form Controls -->
                        <div class="col-lg-7">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fa fa-sliders-h me-1"></i> Poster Metadata & Scheduling</h6>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Poster Title *</label>
                                <input type="text" name="title" id="title" class="form-control" placeholder="e.g. ANRF-PAIR Research Workshop 2026" required oninput="updateLivePreview()">
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Short Description (Optional)</label>
                                <textarea name="short_description" id="short_description" class="form-control" rows="2" placeholder="Brief tagline or description overlay for the poster..." oninput="updateLivePreview()"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Target URL / Read More Link (Optional)</label>
                                <input type="url" name="target_url" id="target_url" class="form-control" placeholder="https://example.com/register or event-detail.php" oninput="updateLivePreview()">
                            </div>

                            <?php if ($isSuper): ?>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Target Institute</label>
                                <select name="institute_prefix" id="institute_prefix" class="form-control">
                                    <option value="all">All Institutes (Combined View)</option>
                                    <?php foreach ($adminPrefixFullNames as $p => $lbl): if ($p === 'all') continue; ?>
                                        <option value="<?= $p ?>" <?= $p === $prefix ? 'selected' : '' ?>><?= htmlspecialchars($lbl) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold"><i class="fa fa-calendar-alt text-success me-1"></i> Start Date & Time (IST)</label>
                                    <input type="datetime-local" name="start_datetime" id="start_datetime" class="form-control" onchange="updateLivePreview()">
                                    <small class="text-muted">Poster becomes visible starting this time.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold"><i class="fa fa-calendar-times text-danger me-1"></i> End Date & Time (IST)</label>
                                    <input type="datetime-local" name="end_datetime" id="end_datetime" class="form-control" onchange="updateLivePreview()">
                                    <small class="text-muted">Poster automatically disappears after this time.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Display Priority / Order</label>
                                    <input type="number" name="display_order" id="display_order" class="form-control" value="10" required>
                                    <small class="text-muted">Lower numbers appear first (e.g. 1, 2, 3).</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Status</label>
                                    <select name="status" id="status" class="form-control" required onchange="updateLivePreview()">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold" id="imageLabel">Upload Poster Image *</label>
                                <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png,image/webp" onchange="previewSelectedImage(this)">
                                <small class="text-muted d-block mt-1">Supports JPG, PNG, WEBP of ANY dimensions (Portrait, Landscape, Square, A4). Max 10MB.</small>
                            </div>
                        </div>

                        <!-- Right Column: Interactive Admin Live Preview -->
                        <div class="col-lg-5 border-start ps-lg-4 mt-3 mt-lg-0">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fa fa-eye me-1"></i> Homepage Live Preview</h6>

                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <small class="text-muted">Calculated State Preview:</small>
                                <div id="previewStateBadge" class="status-badge-container live-badge">🟢 Active</div>
                            </div>

                            <!-- Dual-layer Container Preview matching Homepage -->
                            <div class="poster-preview-card d-flex align-items-center justify-content-center" id="previewContainer">
                                <img id="previewBg" src="../assets/img/1.jpg" class="poster-preview-bg" alt="Backdrop">
                                <img id="previewImg" src="../assets/img/1.jpg" class="poster-preview-img" alt="Artwork">
                                <div class="poster-preview-overlay">
                                    <h6 id="previewTitle" class="text-white fw-bold mb-1" style="font-size: 0.95rem;">Poster Title</h6>
                                    <small id="previewDesc" class="text-light d-block mb-1" style="font-size: 0.75rem;">Short description preview</small>
                                    <span id="previewBtn" class="badge bg-warning text-dark mt-1" style="font-size: 0.7rem;"><i class="fa fa-arrow-right me-1"></i> Read More</span>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3 py-2 px-3 small">
                                <i class="fa fa-info-circle me-1"></i> <strong>Normalization Guarantee:</strong> Non-standard posters (Portrait/A4) are presented with centered full readability over a matching backdrop blur glow. Text is never cropped or distorted.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa fa-save me-1"></i> Save Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Required Vendor Scripts for Bootstrap Modal & Admin UI -->
<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>

<script>
    var slideModalInstance = null;

    function getSlideModalInstance() {
        var modalEl = document.getElementById('slideModal');
        if (!modalEl) return null;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            slideModalInstance = bootstrap.Modal.getInstance(modalEl);
            if (!slideModalInstance) {
                slideModalInstance = new bootstrap.Modal(modalEl);
            }
        }
        return slideModalInstance;
    }

    function openAddModal() {
        document.getElementById('edit_id').value = '';
        var editHeader = document.getElementById('editModeHeader');
        if (editHeader) editHeader.style.display = 'none';

        document.getElementById('title').value = '';
        document.getElementById('short_description').value = '';
        document.getElementById('target_url').value = '';
        document.getElementById('start_datetime').value = '';
        document.getElementById('end_datetime').value = '';
        document.getElementById('display_order').value = '10';
        document.getElementById('status').value = 'Active';

        var instSelect = document.getElementById('institute_prefix');
        if (instSelect) {
            instSelect.value = '<?= $prefix ?>';
        }

        document.getElementById('image').required = true;
        document.getElementById('imageLabel').innerText = 'Upload Poster Image *';
        document.getElementById('modalTitle').innerText = 'Add Homepage Banner / Poster';
        var submitBtn = document.getElementById('submitBtn');
        if (submitBtn) submitBtn.innerHTML = '<i class="fa fa-plus-circle me-1"></i> Save Banner';

        document.getElementById('previewImg').src = '../assets/img/1.jpg';
        document.getElementById('previewBg').src = '../assets/img/1.jpg';
        updateLivePreview();

        var modal = getSlideModalInstance();
        if (modal) {
            modal.show();
        } else if (typeof jQuery !== 'undefined') {
            $('#slideModal').modal('show');
        }
    }

    function openEditModal(slide) {
        document.getElementById('edit_id').value = slide.id;
        var editHeader = document.getElementById('editModeHeader');
        if (editHeader) {
            editHeader.style.display = 'flex';
            document.getElementById('editModeIdDisplay').innerText = slide.id;
        }

        document.getElementById('title').value = slide.title || slide.caption || '';
        document.getElementById('short_description').value = slide.short_description || '';
        document.getElementById('target_url').value = slide.target_url || '';

        var instSelect = document.getElementById('institute_prefix');
        if (instSelect) {
            instSelect.value = slide.institute_prefix || 'all';
        }

        document.getElementById('start_datetime').value = slide.start_datetime ? slide.start_datetime.replace(' ', 'T').substring(0, 16) : '';
        document.getElementById('end_datetime').value = slide.end_datetime ? slide.end_datetime.replace(' ', 'T').substring(0, 16) : '';

        document.getElementById('display_order').value = slide.display_order;
        document.getElementById('status').value = slide.status;
        document.getElementById('image').required = false;
        document.getElementById('imageLabel').innerText = 'Replace Poster Image (Optional)';
        document.getElementById('modalTitle').innerText = 'Edit Homepage Banner / Poster (ID: #' + slide.id + ')';
        var submitBtn = document.getElementById('submitBtn');
        if (submitBtn) submitBtn.innerHTML = '<i class="fa fa-sync-alt me-1"></i> Update Banner';

        if (slide.image_path) {
            var fullImgSrc = '../' + slide.image_path;
            document.getElementById('previewImg').src = fullImgSrc;
            document.getElementById('previewBg').src = fullImgSrc;
        } else {
            document.getElementById('previewImg').src = '../assets/img/1.jpg';
            document.getElementById('previewBg').src = '../assets/img/1.jpg';
        }

        updateLivePreview();

        var modal = getSlideModalInstance();
        if (modal) {
            modal.show();
        } else if (typeof jQuery !== 'undefined') {
            $('#slideModal').modal('show');
        }
    }

    function previewSelectedImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('previewBg').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateLivePreview() {
        var title = document.getElementById('title').value || 'Poster Title Preview';
        var desc = document.getElementById('short_description').value || '';
        var link = document.getElementById('target_url').value || '';
        var status = document.getElementById('status').value;
        var startVal = document.getElementById('start_datetime').value;
        var endVal = document.getElementById('end_datetime').value;

        document.getElementById('previewTitle').innerText = title;
        document.getElementById('previewDesc').innerText = desc;
        document.getElementById('previewDesc').style.display = desc ? 'block' : 'none';
        document.getElementById('previewBtn').style.display = link ? 'inline-block' : 'none';

        var badgeEl = document.getElementById('previewStateBadge');
        var now = new Date();

        if (status === 'Inactive') {
            badgeEl.className = 'status-badge-container inactive-badge';
            badgeEl.innerHTML = '🔴 Inactive';
        } else {
            var start = startVal ? new Date(startVal) : null;
            var end = endVal ? new Date(endVal) : null;

            if (end && end < now) {
                badgeEl.className = 'status-badge-container expired-badge';
                badgeEl.innerHTML = '⚫ Expired';
            } else if (start && start > now) {
                badgeEl.className = 'status-badge-container upcoming-badge';
                badgeEl.innerHTML = '🟡 Scheduled';
            } else {
                badgeEl.className = 'status-badge-container live-badge';
                badgeEl.innerHTML = '🟢 Active';
            }
        }
    }

    function confirmDeletePoster(id) {
        ANRFModal.confirm({
            title: 'Delete Poster?',
            message: 'Are you sure you want to remove this homepage banner? This action cannot be undone.',
            confirmText: 'Delete',
            onConfirm: function() {
                document.getElementById('delete_id_input').value = id;
                document.getElementById('deletePostForm').submit();
            }
        });
    }

    function viewBanner(b) {
        var titleText = b.title || b.caption || 'Poster #' + b.id;
        openKpiRecordViewModal({
            moduleTitle: 'Homepage Banner / Poster Details',
            recordTitle: titleText,
            institutePrefix: b.institute_prefix || 'all',
            extraStatus: b.status || 'Active',
            fields: [
                { label: 'Poster Title', value: titleText, type: 'text', icon: 'fa-solid fa-heading' },
                { label: 'Short Description', value: b.short_description || 'None', type: 'longtext', icon: 'fa-solid fa-align-left' },
                { label: 'Target URL', value: b.target_url || 'None', type: b.target_url ? 'link' : 'text', icon: 'fa-solid fa-link' },
                { label: 'Poster Image', value: b.image_path, type: 'image', icon: 'fa-solid fa-image' },
                { label: 'Institute Prefix', value: (b.institute_prefix || 'all').toUpperCase(), type: 'badge', icon: 'fa-solid fa-building-columns' },
                { label: 'Start Date & Time (IST)', value: b.start_datetime || 'Immediate', icon: 'fa-solid fa-play' },
                { label: 'End Date & Time (IST)', value: b.end_datetime || 'No Expiration', icon: 'fa-solid fa-stop' },
                { label: 'Display Order', value: b.display_order || 10, icon: 'fa-solid fa-arrow-down-short-wide' },
                { label: 'Status', value: b.status || 'Active', type: 'badge', icon: 'fa-solid fa-toggle-on' },
                { label: 'Created At', value: b.created_at || 'N/A', icon: 'fa-solid fa-clock' }
            ]
        });
    }
</script>

<?php include 'includes/view_modal.php'; ?>
<?php include 'footer.php'; ?>
