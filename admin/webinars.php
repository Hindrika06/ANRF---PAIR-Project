<?php
session_start();

// ── Check for 'username' and 'institute_prefix' ─────
if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix'])) {
    header("Location: index.php");
    exit();
}

// ── Resolve which institute's tables this user can access ─────────────────
$allowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
$prefix = $_SESSION['institute_prefix'];

if (!in_array($prefix, $allowedPrefixes, true)) {
    die('Invalid institute configuration. Please contact admin.');
}

// Table name built only from a whitelisted value above — safe from injection
$table = "{$prefix}_webinars";

// ── Database & logic ─────────────────────────────────────────────────────────
require_once 'config/db.php';

$success = false;
$error   = '';

// 1. HANDLE DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        // Fetch image path first to delete the file from the server
        $stmt = $pdo->prepare("SELECT image FROM `$table` WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        $row = $stmt->fetch();
        if ($row && !empty($row['image']) && file_exists($row['image'])) {
            @unlink($row['image']);
        }

        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to delete record: ' . $e->getMessage();
    }
}

// 2. SHOW QUICK SUCCESS MESSAGES POST-REDIRECT
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS (ADD OR UPDATE FROM MODALS)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $taskno       = trim($_POST['taskno']        ?? '');
    $title        = trim($_POST['title']        ?? '');
    $webinar_date = $_POST['webinar_date']      ?? '';
    $organisers   = trim($_POST['organisers']   ?? '');
    $institute    = trim($_POST['institute']    ?? '');
    $investigator = trim($_POST['investigator'] ?? '');
    $content      = trim($_POST['content']      ?? ''); // Description
    $edit_id      = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    try {
        $uploadDir = 'uploads/banners/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $imageFile = null;

        // Check if a file was selected for upload
        if (!empty($_FILES['image']['name'])) {
            $f = $_FILES['image'];

            if ($f['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException("File upload failed with server error code: " . $f['error']);
            }

            if ($f['size'] > 10 * 1024 * 1024) {
                throw new RuntimeException("File exceeds maximum allowed 10 MB limit.");
            }

            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                throw new RuntimeException("Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed.");
            }

            // Generate unique name to prevent collisions/overwrites
            $destFileName = uniqid('banner_', true) . '.' . $ext;
            $destFullPath = $uploadDir . $destFileName;

            if (!move_uploaded_file($f['tmp_name'], $destFullPath)) {
                throw new RuntimeException("Could not save the uploaded banner file. Check folder write configurations.");
            }

            $imageFile = $destFullPath;
        }

        if ($edit_id) {
            // UPDATE EXISTING WEBINAR RECORD
            if ($imageFile) {
                // Remove old banner asset file from server if a new one is uploaded
                $oldStmt = $pdo->prepare("SELECT image FROM `$table` WHERE id = :id");
                $oldStmt->execute([':id' => $edit_id]);
                $oldRow = $oldStmt->fetch();
                if ($oldRow && !empty($oldRow['image']) && file_exists($oldRow['image'])) {
                    @unlink($oldRow['image']);
                }

                $stmt = $pdo->prepare("
                    UPDATE `$table` SET
                        taskno = :taskno,
                        title = :title,
                        webinar_date = :webinar_date,
                        organisers = :organisers,
                        institute = :institute,
                        investigator = :investigator,
                        image = :image,
                        content = :content
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':taskno'       => $taskno,
                    ':title'        => $title,
                    ':webinar_date' => $webinar_date ?: null,
                    ':organisers'   => $organisers,
                    ':institute'    => $institute,
                    ':investigator' => $investigator,
                    ':image'        => $imageFile,
                    ':content'      => $content,
                    ':id'           => $edit_id
                ]);
            } else {
                // Keep the old image path if file selection was left blank during update
                $stmt = $pdo->prepare("
                    UPDATE `$table` SET
                        taskno = :taskno,
                        title = :title,
                        webinar_date = :webinar_date,
                        organisers = :organisers,
                        institute = :institute,
                        investigator = :investigator,
                        content = :content
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':taskno'       => $taskno,
                    ':title'        => $title,
                    ':webinar_date' => $webinar_date ?: null,
                    ':organisers'   => $organisers,
                    ':institute'    => $institute,
                    ':investigator' => $investigator,
                    ':content'      => $content,
                    ':id'           => $edit_id
                ]);
            }
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=updated");
            exit;
        } else {
            // INSERT NEW WEBINAR RECORD
            $stmt = $pdo->prepare("
                INSERT INTO `$table`
                    (taskno, title, webinar_date, organisers, institute,
                     investigator, image, content, created_at)
                VALUES
                    (:taskno, :title, :webinar_date, :organisers, :institute,
                     :investigator, :image, :content, NOW())
            ");
            $stmt->execute([
                ':taskno'       => $taskno,
                ':title'        => $title,
                ':webinar_date' => $webinar_date ?: null,
                ':organisers'   => $organisers,
                ':institute'    => $institute,
                ':investigator' => $investigator,
                ':image'        => $imageFile,
                ':content'      => $content,
            ]);
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=inserted");
            exit;
        }
    } catch (RuntimeException $e) {
        $error = "Upload System Error: " . $e->getMessage();
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// 4. FETCH ALL DATA FOR THE TABLE
$webinars = [];
try {
    $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY id DESC");
    $webinars = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Could not load data records: ' . $e->getMessage();
}

$total_records = count($webinars);

// 5. CALCULATE WEBINAR STATS
$total_webinars = $total_records;
$unique_organisers = [];
$unique_investigators = [];
$upcoming_count = 0;
$current_time = time();

foreach ($webinars as $w) {
    if (!empty(trim($w['organisers']))) {
        $unique_organisers[trim($w['organisers'])] = true;
    }
    if (!empty(trim($w['investigator']))) {
        $unique_investigators[trim($w['investigator'])] = true;
    }
    if (!empty($w['webinar_date'])) {
        if (strtotime($w['webinar_date']) > $current_time) {
            $upcoming_count++;
        }
    }
}
$total_organisers = count($unique_organisers);
$total_pis = count($unique_investigators);
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">

<style>
    /* ──── COMPACT REGISTRY TABLE STYLING (matches Conferences page) ──── */
    .registry-card {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.01) !important;
        overflow: hidden;
        background: #ffffff;
    }

    .table-theme-sapphire {
        margin-bottom: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-theme-sapphire thead th {
        background-color: #024283 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.8px !important;
        padding: 12px 16px !important;
        border: none !important;
    }

    .table-theme-sapphire tbody tr:hover {
        background-color: #f8fafc !important;
    }

    .table-theme-sapphire tbody td {
        padding: 10px 16px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155;
    }

    /* Index Circle */
    .index-badge-circle {
        width: 22px;
        height: 22px;
        background-color: #b93c3c;
        color: #ffffff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 10px;
    }

    .registry-task-link {
        font-size: 12px;
        font-weight: 700;
        color: #024283;
        text-decoration: none;
        display: inline-block;
        margin-bottom: 2px;
    }

    .registry-task-link:hover {
        text-decoration: underline;
    }

    .registry-main-title {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.3;
        display: block;
        margin-bottom: 4px;
    }

    .registry-tag-pill {
        display: inline-block;
        font-size: 9px;
        font-weight: 600;
        color: #b93c3c;
        background-color: #fdf2f2;
        padding: 2px 8px;
        border-radius: 20px;
        text-transform: capitalize;
    }

    .registry-meta-text {
        font-size: 12px;
        color: #334155;
    }

    .registry-sub-label {
        font-size: 11px;
        color: #64748b;
        display: block;
    }

    /* Custom Status Pill */
    .status-pill-custom {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 10px;
    }

    .status-pill-custom::before {
        content: '';
        width: 5px;
        height: 5px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-pill-custom.status-granted {
        background-color: #e6f4ea;
        color: #137333;
    }
    .status-pill-custom.status-granted::before {
        background-color: #137333;
    }

    .status-pill-custom.status-pending {
        background-color: #f1f3f4;
        color: #5f6368;
    }
    .status-pill-custom.status-pending::before {
        background-color: #5f6368;
    }

    /* Compact Actions */
    .btn-action-compact {
        width: 30px !important;
        height: 30px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 4px !important;
        font-size: 13px !important;
        border: none !important;
        transition: transform 0.15s ease;
    }
    .btn-action-compact:hover {
        transform: scale(1.05);
    }

    .btn-action-edit-yellow {
        background-color: #ffca28 !important;
        color: #1a1a1a !important;
    }
    .btn-action-edit-yellow:hover {
        background-color: #ffb300 !important;
    }

    .btn-action-delete-red {
        background-color: #ef4444 !important;
        color: #ffffff !important;
    }
    .btn-action-delete-red:hover {
        background-color: #dc2626 !important;
    }

    .pagination-theme-sapphire .page-item.active .page-link {
        background-color: #024283 !important;
        border-color: #024283 !important;
        color: #ffffff !important;
    }
    .pagination-theme-sapphire .page-link {
        color: #024283;
    }

    /* ──── UNIFIED BLUE KPI WIDGET STYLES (unchanged for webinars) ──── */
    .kpi-widget-card {
        border-radius: 20px !important;
        padding: 20px 24px;
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        background-color: #53a1f1 !important;
    }
    .kpi-card-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 100%;
    }
    .kpi-icon-circle {
        width: 44px;
        height: 44px;
        background-color: rgba(255, 255, 255, 0.25);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
    }
    .kpi-icon-circle i {
        font-size: 18px;
        color: #ffffff !important;
    }
    .kpi-title-text {
        font-size: 13px;
        font-weight: 500;
        opacity: 0.95;
        margin-bottom: 6px;
    }
    .kpi-metric-row {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 6px;
    }
    .kpi-metric-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
    }
    .kpi-subtext {
        font-size: 11px;
        opacity: 0.8;
        font-weight: 400;
    }
</style>


<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Events Management</a></li>
                    <li class="breadcrumb-item active">Webinars Dashboard</li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i>
                <strong>Success!</strong> Action processed successfully.
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

            <!-- ── BLUE STATS WIDGETS ROW ── -->
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-video"></i></div>
                            <span class="kpi-title-text">Total Webinars</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_webinars ?></span>
                                <span class="kpi-subtext">tracked</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-calendar-days"></i></div>
                            <span class="kpi-title-text">Upcoming Events</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $upcoming_count ?></span>
                                <span class="kpi-subtext">Scheduled</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-building-columns"></i></div>
                            <span class="kpi-title-text">Total Organisers</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_organisers ?></span>
                                <span class="kpi-subtext">Agencies</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-user-tie"></i></div>
                            <span class="kpi-title-text">Investigators</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_pis ?></span>
                                <span class="kpi-subtext">Faculty Hosts</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── REGISTRY-STYLE RECORDS TABLE (matches Conferences page) ── -->
            <div class="col-lg-12 px-0">
                <div class="card registry-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 bg-white border-0">
                        <h4 class="card-title mb-0" style="color: #024283; font-weight: 700; font-size: 15px;">
                            <i class="fa-solid fa-video me-2"></i>WEBINARS &amp; EVENTS RECORDS
                        </h4>
                        <button type="button" class="btn btn-success btn-sm text-white px-3" data-bs-toggle="modal" data-bs-target="#webinarModal" id="addNewBtn" style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Webinar
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-theme-sapphire">
                            <thead>
                                <tr>
                                    <th style="width: 4%; text-align: center;">S.No</th>
                                    <th style="width: 46%;">Webinar Info</th>
                                    <th style="width: 22%;">Organisers / Hosts</th>
                                    <th style="width: 13%;">Date</th>
                                    <th style="width: 15%; text-align: center;">Status</th>
                                    <th style="width: 10%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($webinars)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4" style="font-size: 13px;">No webinar records tracked yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $rowCounter = 1;
                                    foreach ($webinars as $webinar):
                                    ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="index-badge-circle"><?= $rowCounter++ ?></span>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="registry-task-link">
                                                    <?= htmlspecialchars($webinar['taskno'] ?: 'TASK-UNASSIGNED') ?>
                                                </a>
                                                <span class="registry-main-title">
                                                    <?= htmlspecialchars($webinar['title'] ?: 'Untitled Webinar') ?>
                                                </span>
                                                <?php if (!empty($webinar['institute'])): ?>
                                                    <span class="registry-tag-pill"><?= htmlspecialchars($webinar['institute']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="registry-meta-text font-w600 text-dark">
                                                    <?= htmlspecialchars($webinar['investigator'] ?: '—') ?>
                                                </span>
                                                <span class="registry-sub-label">
                                                    with <?= htmlspecialchars($webinar['organisers'] ?: 'Independent') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-dark font-w600" style="font-size: 12px;">
                                                    <?= $webinar['webinar_date'] ? date('d M Y, h:i A', strtotime($webinar['webinar_date'])) : '—' ?>
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <?php if (!empty($webinar['image']) && file_exists($webinar['image'])): ?>
                                                    <a href="<?= $webinar['image'] ?>" target="_blank" class="status-pill-custom status-granted text-decoration-none">Granted Banner</a>
                                                <?php else: ?>
                                                    <span class="status-pill-custom status-pending">No Banner</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-edit-yellow edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#webinarModal"
                                                            data-id="<?= $webinar['id'] ?>"
                                                            data-taskno="<?= htmlspecialchars($webinar['taskno'] ?? '') ?>"
                                                            data-title="<?= htmlspecialchars($webinar['title']) ?>"
                                                            data-date="<?= $webinar['webinar_date'] ? date('Y-m-d\TH:i', strtotime($webinar['webinar_date'])) : '' ?>"
                                                            data-organisers="<?= htmlspecialchars($webinar['organisers']) ?>"
                                                            data-institute="<?= htmlspecialchars($webinar['institute'] ?? '') ?>"
                                                            data-investigator="<?= htmlspecialchars($webinar['investigator'] ?? '') ?>"
                                                            data-content="<?= htmlspecialchars($webinar['content'] ?? '') ?>"
                                                            title="Edit Record">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-delete-red delete-confirm-trigger"
                                                            data-id="<?= $webinar['id'] ?>"
                                                            title="Delete Record">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap p-2 px-3 bg-white border-top">
                        <p class="mb-0 text-muted small font-w500">Total: <?= $total_records ?> dashboard assets</p>
                        <nav aria-label="Pagination control block">
                          <ul class="pagination pagination-sm mb-0 pagination-theme-sapphire">
                            <li class="page-item"><a class="page-link" href="javascript:void(0);"><i class="fa-solid fa-angle-left"></i></a></li>
                            <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                            <li class="page-item"><a class="page-link" href="javascript:void(0);"><i class="fa-solid fa-angle-right"></i></a></li>
                          </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Input Form Overlay -->
    <div class="modal fade" id="webinarModal" tabindex="-1" aria-labelledby="webinarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="webinarModalLabel">Webinar Creation Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="modalForm">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="modal_edit_id">

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label form-label-grey">Task No</label>
                                <input type="text" name="taskno" id="modal_taskno" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Webinar Title</label>
                                <input type="text" name="title" id="modal_title" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label form-label-grey">Date</label>
                                <input type="text" name="webinar_date" id="modal_webinar_date" class="form-control" placeholder="Select date &amp; time" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Organisers</label>
                                <input type="text" name="organisers" id="modal_organisers" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Institute</label>
                                <input type="text" name="institute" id="modal_institute" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Investigator</label>
                                <input type="text" name="investigator" id="modal_investigator" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">JPG, PNG, or WEBP only · Max 10 MB</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Description</label>
                                <textarea name="content" id="modal_content" rows="5" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success text-white" id="modalSubmitBtn">Save Records</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal Box -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-dark" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2 text-dark" style="font-size: 13px;">
                    Are you sure you want to permanently delete this webinar record? This operation cannot be rolled back.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="modalDeleteExecutionLink" class="btn btn-sm btn-danger text-white">Delete Record</a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="copyright">
            <p>Copyright &copy; Designed &amp; Developed by <a href="https://dexignlab.com/" target="_blank">DexignLab</a> 2023</p>
        </div>
    </div>
</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const addNewBtn = document.getElementById('addNewBtn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const modalTitle = document.getElementById('webinarModalLabel');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalForm = document.getElementById('modalForm');

    const deleteTriggers = document.querySelectorAll('.delete-confirm-trigger');
    const modalDeleteExecutionLink = document.getElementById('modalDeleteExecutionLink');
    const bootstrapDeleteInstance = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    const webinarDatePicker = flatpickr("#modal_webinar_date", {
        enableTime: true,
        dateFormat: "Y-m-d\\TH:i",
        altInput: true,
        altFormat: "d M, Y - h:i K",
        time_24hr: false
    });

    if(addNewBtn) {
        addNewBtn.addEventListener('click', function() {
            modalForm.reset();
            webinarDatePicker.clear();
            document.getElementById('modal_edit_id').value = '';
            modalTitle.innerText = "Webinar Creation Form";
            modalSubmitBtn.innerText = "Save Records";
        });
    }

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            modalTitle.innerText = "Edit Webinar Info";
            modalSubmitBtn.innerText = "Save Changes";

            document.getElementById('modal_edit_id').value = this.getAttribute('data-id');
            document.getElementById('modal_taskno').value = this.getAttribute('data-taskno');
            document.getElementById('modal_title').value = this.getAttribute('data-title');

            const dateVal = this.getAttribute('data-date');
            if (dateVal) {
                webinarDatePicker.setDate(dateVal, true);
            } else {
                webinarDatePicker.clear();
            }

            document.getElementById('modal_organisers').value = this.getAttribute('data-organisers');
            document.getElementById('modal_institute').value = this.getAttribute('data-institute');
            document.getElementById('modal_investigator').value = this.getAttribute('data-investigator');
            document.getElementById('modal_content').value = this.getAttribute('data-content');
        });
    });

    deleteTriggers.forEach(triggerBtn => {
        triggerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const recordId = this.getAttribute('data-id');
            modalDeleteExecutionLink.setAttribute('href', '?action=delete&id=' + recordId);
            bootstrapDeleteInstance.show();
        });
    });
});
</script>
</body>
</html>