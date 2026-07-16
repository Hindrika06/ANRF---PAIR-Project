<?php
session_start();

require_once 'role_access.php';

// ── Check for 'username' and 'institute_prefix' ─────
if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix'])) {
    header("Location: index.php");
    exit();
}

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

// Table name built only from a whitelisted value above — safe from injection
$table = "{$prefix}_progress_reports";

// ── Database & logic ─────────────────────────────────────────────────────────
require_once 'config/db.php';

// Self-healing DB check: Ensure work_package_no column exists in progress reports table
try {
    $checkCol = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'work_package_no'");
    if ($checkCol->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `work_package_no` VARCHAR(100) NULL AFTER `task_no`");
    }
} catch (PDOException $e) {
    // Fail silently, or it will be handled when accessing/inserting
}

$success = false;
$error   = '';

// 1. HANDLE DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to delete records for this institute.';
    } else {
    try {
        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to delete record: ' . $e->getMessage();
    }
    }
}

// 2. SHOW QUICK SUCCESS MESSAGES POST-REDIRECT
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS (ADD OR UPDATE FROM MODALS)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $project_title     = trim($_POST['project_title']     ?? '');
    $pi_name           = trim($_POST['pi_name']           ?? '');
    $co_pi_name        = trim($_POST['co_pi_name']        ?? '');
    $task_no           = trim($_POST['task_no']           ?? '');
    $work_package_no   = trim($_POST['work_package_no']   ?? '');
    $approved_objects  = trim($_POST['approved_objects']  ?? '');
    $methodology       = trim($_POST['methodology']       ?? '');
    $summary_progress  = trim($_POST['summary_progress']  ?? '');
    $edit_id           = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to update records for this institute.';
    } else {
    // MANDATORY CHECKS REMOVED AS REQUESTED
    try {
        if ($edit_id) {
            // UPDATE RECORD
            $stmt = $pdo->prepare("
                UPDATE `$table` SET
                    project_title = :project_title, 
                    pi_name = :pi_name, 
                    co_pi_name = :co_pi_name, 
                    task_no = :task_no, 
                    work_package_no = :work_package_no, 
                    approved_objects = :approved_objects, 
                    methodology = :methodology, 
                    summary_progress = :summary_progress
                WHERE id = :id
            ");
            $stmt->execute([
                ':project_title'    => $project_title,
                ':pi_name'          => $pi_name,
                ':co_pi_name'       => $co_pi_name,
                ':task_no'          => $task_no,
                ':work_package_no'  => $work_package_no,
                ':approved_objects' => $approved_objects,
                ':methodology'      => $methodology,
                ':summary_progress' => $summary_progress,
                ':id'               => $edit_id
            ]);
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=updated");
            exit;
        } else {
            // INSERT NEW RECORD
            $stmt = $pdo->prepare("
                INSERT INTO `$table`
                    (project_title, pi_name, co_pi_name, task_no, 
                     work_package_no, approved_objects, methodology, 
                     summary_progress, created_at)
                VALUES
                    (:project_title, :pi_name, :co_pi_name, :task_no, 
                     :work_package_no, :approved_objects, :methodology, 
                     :summary_progress, NOW())
            ");
            $stmt->execute([
                ':project_title'    => $project_title,
                ':pi_name'          => $pi_name,
                ':co_pi_name'       => $co_pi_name,
                ':task_no'          => $task_no,
                ':work_package_no'  => $work_package_no,
                ':approved_objects' => $approved_objects,
                ':methodology'      => $methodology,
                ':summary_progress' => $summary_progress,
            ]);
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=inserted");
            exit;
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
    }
}

// 4. FETCH ALL DATA FOR THE TABLE
$reports = [];
try {
    $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY id DESC");
    $reports = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Could not load data records: ' . $e->getMessage();
}

$total_records = count($reports);

// 5. CALCULATE PROGRESS REPORT STATS
$total_reports   = $total_records;
$unique_pis      = [];
$unique_co_pis   = [];
$unique_tasks    = [];
$total_work_pkgs = 0;

foreach ($reports as $report) {
    if (!empty(trim($report['pi_name']))) {
        $unique_pis[trim($report['pi_name'])] = true;
    }
    if (!empty(trim($report['co_pi_name']))) {
        $unique_co_pis[trim($report['co_pi_name'])] = true;
    }
    if (!empty(trim($report['task_no']))) {
        $unique_tasks[trim($report['task_no'])] = true;
    }
    if (!empty(trim($report['work_package_no'] ?? ''))) {
        $total_work_pkgs++;
    }
}

$total_pi_count       = count($unique_pis);
$total_co_pi_count    = count($unique_co_pis);
$total_unique_tasks   = count($unique_tasks);
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
    /* ──── COMPACT REGISTRY TABLE STYLING ──── */
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

    .registry-meta-text {
        font-size: 12px;
        color: #334155;
    }

    .registry-sub-label {
        font-size: 11px;
        color: #64748b;
        display: block;
    }

    /* Compact Actions Structure Style Rules */
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

    /* ──── UNIFIED RED KPI STYLES (#bc2121) ──── */
    .kpi-widget-card {
        border-radius: 10px !important;
        padding: 20px 24px;
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        background-color: #024283 !important;
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

    #modalForm .form-label-grey {
        color: #666 !important;
        font-weight: 500;
    }
    #modalForm .form-control {
        color: #1a1a1a !important;
        font-weight: 500;
        border-color: #cbd5e1;
    }
    #modalForm .form-control:focus {
        color: #000 !important;
        border-color: #666;
    }
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Project Monitoring</a></li>
                    <li class="breadcrumb-item active">Progress Reports Dashboard</li>
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

            <!-- KPI STATS CARDS -->
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-file-lines"></i></div>
                            <span class="kpi-title-text">Total Reports</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_reports ?></span>
                                <span class="kpi-subtext">submitted</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-user-check"></i></div>
                            <span class="kpi-title-text">Principal Investigators</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_pi_count ?></span>
                                <span class="kpi-subtext">Unique PIs</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-users-gear"></i></div>
                            <span class="kpi-title-text">Co-Investigators</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_co_pi_count ?></span>
                                <span class="kpi-subtext">Unique Co-PIs</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-tasks"></i></div>
                            <span class="kpi-title-text">Active Tasks</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_unique_tasks ?></span>
                                <span class="kpi-subtext">Tracked</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROGRESS REPORTS TABLE -->
            <div class="col-lg-12 px-0">
                <div class="card registry-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 bg-white border-0">
                        <h4 class="card-title mb-0" style="color: #024283; font-weight: 700; font-size: 15px;">
                            <i class="fa-solid fa-file-lines me-2"></i>PROGRESS REPORTS LIST
                        </h4>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-success btn-sm text-white px-3"
                                data-bs-toggle="modal" data-bs-target="#reportModal" id="addNewBtn" style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Report
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-theme-sapphire">
                            <thead>
                                <tr>
                                    <th style="width: 4%; text-align: center; background-color: #bc2121 !important; color: #ffffff !important;">S.No</th>
                                    <th style="width: 35%; background-color: #bc2121 !important; color: #ffffff !important;">Project / Task Info</th>
                                    <th style="width: 20%; background-color: #bc2121 !important; color: #ffffff !important;">Investigators</th>
                                    <th style="width: 15%; background-color: #bc2121 !important; color: #ffffff !important;">Work Package</th>
                                    <th style="width: 12%; background-color: #bc2121 !important; color: #ffffff !important;">Modified</th>
                                    <th style="width: 10%; text-align: center; background-color: #bc2121 !important; color: #ffffff !important;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4" style="font-size: 13px;">No progress reports submitted yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $sno = 1;
                                    foreach ($reports as $report):
                                    ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="index-badge-circle"><?= $sno++ ?></span>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="registry-task-link">
                                                    <?= htmlspecialchars($report['task_no'] ?: 'TASK-UNASSIGNED') ?>
                                                </a>
                                                <span class="registry-main-title">
                                                    <?= htmlspecialchars($report['project_title'] ?: 'Untitled Project') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="registry-meta-text font-w600 text-dark d-block">
                                                    <strong>PI:</strong> <?= htmlspecialchars($report['pi_name'] ?: '—') ?>
                                                </span>
                                                <span class="registry-sub-label">
                                                    <strong>Co-PI:</strong> <?= htmlspecialchars($report['co_pi_name'] ?: '—') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="registry-meta-text">
                                                    <?= htmlspecialchars($report['work_package_no'] ?: '—') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-dark font-w600" style="font-size: 12px;">
                                                    <?= date('d M Y', strtotime($report['created_at'])) ?>
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-edit-yellow edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#reportModal"
                                                            data-id="<?= $report['id'] ?>"
                                                            data-title="<?= htmlspecialchars($report['project_title']) ?>"
                                                            data-pi="<?= htmlspecialchars($report['pi_name']) ?>"
                                                            data-copi="<?= htmlspecialchars($report['co_pi_name'] ?? '') ?>"
                                                            data-task="<?= htmlspecialchars($report['task_no']) ?>"
                                                            data-wp="<?= htmlspecialchars($report['work_package_no'] ?? '') ?>"
                                                            data-objects="<?= htmlspecialchars($report['approved_objects'] ?? '') ?>"
                                                            data-methodology="<?= htmlspecialchars($report['methodology'] ?? '') ?>"
                                                            data-summary="<?= htmlspecialchars($report['summary_progress'] ?? '') ?>"
                                                            title="Edit Record">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-delete-red delete-confirm-trigger"
                                                            data-id="<?= $report['id'] ?>"
                                                            title="Delete Record">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-info text-white edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#reportModal"
                                                            data-view-only="true"
                                                            data-id="<?= $report['id'] ?>"
                                                            data-title="<?= htmlspecialchars($report['project_title']) ?>"
                                                            data-pi="<?= htmlspecialchars($report['pi_name']) ?>"
                                                            data-copi="<?= htmlspecialchars($report['co_pi_name'] ?? '') ?>"
                                                            data-task="<?= htmlspecialchars($report['task_no']) ?>"
                                                            data-wp="<?= htmlspecialchars($report['work_package_no'] ?? '') ?>"
                                                            data-objects="<?= htmlspecialchars($report['approved_objects'] ?? '') ?>"
                                                            data-methodology="<?= htmlspecialchars($report['methodology'] ?? '') ?>"
                                                            data-summary="<?= htmlspecialchars($report['summary_progress'] ?? '') ?>"
                                                            title="View Details">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap p-2 px-3 bg-white border-top">
                        <p class="mb-0 text-muted small font-w500">Total: <?= $total_records ?> progress reports</p>
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

    <!-- ── ADD / EDIT MODAL ────────────────────────────────────────────── -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Project Progress Report Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="modalForm">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="modal_edit_id">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Task No</label>
                                <input type="text" name="task_no" id="modal_task_no" class="form-control" placeholder="e.g. TASK-001">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label form-label-grey">Project Title</label>
                                <input type="text" name="project_title" id="modal_project_title" class="form-control" placeholder="Full project title">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Principal Investigator (PI) Name</label>
                                <input type="text" name="pi_name" id="modal_pi_name" class="form-control" placeholder="PI name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Co-Principal Investigator (Co-PI) Name</label>
                                <input type="text" name="co_pi_name" id="modal_co_pi_name" class="form-control" placeholder="Co-PI name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Work Package Number</label>
                                <input type="text" name="work_package_no" id="modal_work_package_no" class="form-control" placeholder="e.g. WP-001">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Approved Objectives / Targets</label>
                                <textarea name="approved_objects" id="modal_approved_objects" rows="3" class="form-control" placeholder="List approved objectives"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Methodology / Approach Used</label>
                                <textarea name="methodology" id="modal_methodology" rows="3" class="form-control" placeholder="Describe methodology"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Summary of the Progress</label>
                                <textarea name="summary_progress" id="modal_summary_progress" rows="4" class="form-control" placeholder="Summarize progress made"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success text-white" id="modalSubmitBtn">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── DELETE CONFIRMATION MODAL ──────────────────────────────────── -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-dark" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2 text-dark">
                    Are you sure you want to permanently delete this progress report? This operation cannot be rolled back.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="modalDeleteExecutionLink" class="btn btn-sm btn-danger text-white">
                        Delete Record
                    </a>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    const addNewBtn = document.getElementById('addNewBtn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const modalTitle = document.getElementById('reportModalLabel');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalForm = document.getElementById('modalForm');

    const deleteTriggers = document.querySelectorAll('.delete-confirm-trigger');
    const modalDeleteExecutionLink = document.getElementById('modalDeleteExecutionLink');
    const bootstrapDeleteInstance = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    if(addNewBtn) {
        addNewBtn.addEventListener('click', function() {
            modalForm.reset(); 
            document.getElementById('modal_edit_id').value = ''; 
            modalTitle.innerText = "Project Progress Report Form";
            modalSubmitBtn.innerText = "Submit Report";
            modalSubmitBtn.style.display = "block";
            modalForm.querySelectorAll('input, textarea, select').forEach(el => {
                el.disabled = false;
                el.readOnly = false;
            });
        });
    }

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const isViewOnly = this.getAttribute('data-view-only') === 'true';
            if (isViewOnly) {
                modalTitle.innerText = "View Progress Report";
                modalSubmitBtn.style.display = "none";
                modalForm.querySelectorAll('input, textarea, select').forEach(el => {
                    el.disabled = true;
                    el.readOnly = true;
                });
            } else {
                modalTitle.innerText = "Edit Progress Report";
                modalSubmitBtn.style.display = "block";
                modalForm.querySelectorAll('input, textarea, select').forEach(el => {
                    el.disabled = false;
                    el.readOnly = false;
                });
            }
            
            document.getElementById('modal_edit_id').value = this.getAttribute('data-id');
            document.getElementById('modal_project_title').value = this.getAttribute('data-title');
            document.getElementById('modal_pi_name').value = this.getAttribute('data-pi');
            document.getElementById('modal_co_pi_name').value = this.getAttribute('data-copi');
            document.getElementById('modal_task_no').value = this.getAttribute('data-task');
            document.getElementById('modal_work_package_no').value = this.getAttribute('data-wp');
            document.getElementById('modal_approved_objects').value = this.getAttribute('data-objects');
            document.getElementById('modal_methodology').value = this.getAttribute('data-methodology');
            document.getElementById('modal_summary_progress').value = this.getAttribute('data-summary');
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