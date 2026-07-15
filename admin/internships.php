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
$table = "{$prefix}_internships";

// ── Database & logic ─────────────────────────────────────────────────────────
require_once 'config/db.php';

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

    $task_no              = trim($_POST['task_no'] ?? '');
    $title                = trim($_POST['title'] ?? '');
    $project_investigator = trim($_POST['project_investigator'] ?? '');
    $no_students_trained  = trim($_POST['no_students_trained'] ?? '');
    $students_names       = trim($_POST['students_names'] ?? ''); 
    $no_days_trained      = trim($_POST['no_days_trained'] ?? '');
    $content              = trim($_POST['content'] ?? '');
    $edit_id              = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to update records for this institute.';
    } else {
    try {
        if ($edit_id) {
            $stmt = $pdo->prepare("
                UPDATE `$table` SET
                    task_no = :task_no,
                    title = :title, 
                    project_investigator = :project_investigator, 
                    no_students_trained = :no_students_trained, 
                    students_names = :students_names, 
                    no_days_trained = :no_days_trained, 
                    content = :content
                WHERE id = :id
            ");
            $stmt->execute([
                ':task_no'              => $task_no,
                ':title'                => $title,
                ':project_investigator' => $project_investigator,
                ':no_students_trained'  => (int)$no_students_trained ?: 0,
                ':students_names'       => $students_names,
                ':no_days_trained'      => (int)$no_days_trained ?: null,
                ':content'              => $content,
                ':id'                   => $edit_id
            ]);
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=updated");
            exit;
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO `$table`
                    (task_no, title, project_investigator, no_students_trained, 
                     students_names, no_days_trained, content, created_at)
                VALUES
                    (:task_no, :title, :project_investigator, :no_students_trained, 
                     :students_names, :no_days_trained, :content, NOW())
            ");
            $stmt->execute([
                ':task_no'              => $task_no,
                ':title'                => $title,
                ':project_investigator' => $project_investigator,
                ':no_students_trained'  => (int)$no_students_trained ?: 0,
                ':students_names'       => $students_names,
                ':no_days_trained'      => (int)$no_days_trained ?: null,
                ':content'              => $content,
            ]);
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=inserted");
            exit;
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
    }
}

// 4. FETCH DATA
$internships = [];
try {
    $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY id DESC");
    $internships = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Could not load data records: ' . $e->getMessage();
}

$total_records = count($internships);

// 5. CALCULATE STATS
$total_programs = $total_records;
$total_students_trained = 0;
$total_days_trained = 0;
$unique_investigators = [];

foreach ($internships as $item) {
    $total_students_trained += (int)$item['no_students_trained'];
    $total_days_trained += (int)$item['no_days_trained'];
    if (!empty(trim($item['project_investigator']))) {
        $unique_investigators[trim($item['project_investigator'])] = true;
    }
}
$total_pis = count($unique_investigators);
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

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

    /* Custom Status Pill (used here for Duration) */
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

    /* Students names truncation inside registry row */
    .registry-student-names {
        font-size: 12px;
        font-weight: 500;
        color: #334155;
        max-width: 260px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: middle;
    }

    /* ──── UNIFIED ORANGE KPI WIDGET STYLES (unchanged for internships) ──── */
    .kpi-widget-card {
        border-radius: 20px !important;
        padding: 20px 24px;
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        background-color: #FFA500 !important;
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

            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">IPR &amp; Research</a></li>
                    <li class="breadcrumb-item active">Internships Dashboard</li>
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

            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-graduation-cap"></i></div>
                            <span class="kpi-title-text">Total Programs</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_programs ?></span>
                                <span class="kpi-subtext">tracked</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-users"></i></div>
                            <span class="kpi-title-text">Students Trained</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_students_trained ?></span>
                                <span class="kpi-subtext">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-clock"></i></div>
                            <span class="kpi-title-text">Total Days</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_days_trained ?></span>
                                <span class="kpi-subtext">Cumulative</span>
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
                                <span class="kpi-subtext">Faculty PI's</span>
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
                            <i class="fa-solid fa-graduation-cap me-2"></i>INTERNSHIPS &amp; TRAINING RECORDS
                        </h4>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-success btn-sm text-white px-3" data-bs-toggle="modal" data-bs-target="#internshipModal" id="addNewBtn" style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Internship
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-theme-sapphire">
                            <thead>
                                <tr>
                                    <th style="width: 4%; text-align: center;">S.No</th>
                                    <th style="width: 36%;">Program Info</th>
                                    <th style="width: 32%;">Tracked Students</th>
                                    <th style="width: 13%;">Duration</th>
                                    <th style="width: 15%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($internships)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4" style="font-size: 13px;">No internship records tracked yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $rowCounter = 1;
                                    foreach ($internships as $item):
                                    ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="index-badge-circle"><?= $rowCounter++ ?></span>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="registry-task-link">
                                                    <?= !empty($item['task_no']) ? htmlspecialchars($item['task_no']) : 'TASK-UNASSIGNED' ?>
                                                </a>
                                                <span class="registry-main-title">
                                                    <?= htmlspecialchars($item['title'] ?: 'Untitled Program') ?>
                                                </span>
                                                <?php if (!empty($item['project_investigator'])): ?>
                                                    <span class="registry-tag-pill"><?= htmlspecialchars($item['project_investigator']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="status-pill-custom status-granted"><?= (int)$item['no_students_trained'] ?> Students</span>
                                                    <span class="registry-student-names" title="<?= htmlspecialchars($item['students_names'] ?? '') ?>">
                                                        <?= !empty($item['students_names']) ? htmlspecialchars($item['students_names']) : '—' ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-dark font-w600" style="font-size: 12px;">
                                                    <?= $item['no_days_trained'] ? htmlspecialchars($item['no_days_trained']) . ' Days' : '—' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-edit-yellow edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#internshipModal"
                                                            data-id="<?= $item['id'] ?>"
                                                            data-task="<?= htmlspecialchars($item['task_no'] ?? '') ?>"
                                                            data-title="<?= htmlspecialchars($item['title']) ?>"
                                                            data-pi="<?= htmlspecialchars($item['project_investigator']) ?>"
                                                            data-trained="<?= (int)$item['no_students_trained'] ?>"
                                                            data-days="<?= $item['no_days_trained'] ?? '' ?>"
                                                            data-names="<?= htmlspecialchars($item['students_names'] ?? '') ?>"
                                                            data-content="<?= htmlspecialchars($item['content'] ?? '') ?>"
                                                            title="Edit Record">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-delete-red delete-confirm-trigger"
                                                            data-id="<?= $item['id'] ?>"
                                                            title="Delete Record">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-info text-white edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#internshipModal"
                                                            data-view-only="true"
                                                            data-id="<?= $item['id'] ?>"
                                                            data-task="<?= htmlspecialchars($item['task_no'] ?? '') ?>"
                                                            data-title="<?= htmlspecialchars($item['title']) ?>"
                                                            data-pi="<?= htmlspecialchars($item['project_investigator']) ?>"
                                                            data-trained="<?= (int)$item['no_students_trained'] ?>"
                                                            data-days="<?= $item['no_days_trained'] ?? '' ?>"
                                                            data-names="<?= htmlspecialchars($item['students_names'] ?? '') ?>"
                                                            data-content="<?= htmlspecialchars($item['content'] ?? '') ?>"
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
    
    <div class="modal fade" id="internshipModal" tabindex="-1" aria-labelledby="internshipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="internshipModalLabel">Internship &amp; Training Records Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="modalForm">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="modal_edit_id">
                        <input type="hidden" name="students_names" id="hidden_students_names">

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label form-label-grey">Task No</label>
                                <input type="text" name="task_no" id="modal_task_no" class="form-control">
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label form-label-grey">Internship / Training Title</label>
                                <input type="text" name="title" id="modal_title" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Project Investigator</label>
                                <input type="text" name="project_investigator" id="modal_project_investigator" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">No. of Students Trained</label>
                                <input type="number" name="no_students_trained" id="modal_no_students_trained" class="form-control" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">No. of Days Trained</label>
                                <input type="number" name="no_days_trained" id="modal_no_days_trained" class="form-control" min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label form-label-grey mb-0">Students' Names</label>
                                    <button type="button" class="btn btn-primary btn-xxs" id="addStudentRowBtn">
                                        <i class="fa fa-plus me-1"></i>Add Student
                                    </button>
                                </div>
                                <div id="studentRowsContainer" class="d-flex flex-column gap-2"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Internship Content / Project Scope</label>
                                <textarea name="content" id="modal_content" rows="4" class="form-control"></textarea>
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

    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-dark" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2 text-dark" style="font-size: 13px;">
                    Are you sure you want to permanently delete this internship record? This operation cannot be rolled back.
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
            <p>Copyright &copy; Designed &amp; Developed by <a href="https://bhimavaramdigitals.com/" target="_blank">Bhimavaram Digitals</a> 2026</p>
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
    const modalTitle = document.getElementById('internshipModalLabel');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalForm = document.getElementById('modalForm');

    const deleteTriggers = document.querySelectorAll('.delete-confirm-trigger');
    const modalDeleteExecutionLink = document.getElementById('modalDeleteExecutionLink');
    const bootstrapDeleteInstance = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    const studentRowsContainer = document.getElementById('studentRowsContainer');
    const addStudentRowBtn = document.getElementById('addStudentRowBtn');
    const hiddenStudentsInput = document.getElementById('hidden_students_names');
    const noStudentsTrainedInput = document.getElementById('modal_no_students_trained');

    function createStudentRowElement(value = '') {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center gap-2 student-input-item';
        div.innerHTML = `
            <input type="text" class="form-control single-student-name-input" value="${value.trim()}">
        `;
        return div;
    }

    function reindexStudentPlaceholders() {
        const structuralInputs = studentRowsContainer.querySelectorAll('.single-student-name-input');
        structuralInputs.forEach((input, index) => {
            input.setAttribute('placeholder', `Student Name ${index + 1}`);
        });
    }

    function updateTrainedStudentsCount() {
        const structuralRows = studentRowsContainer.querySelectorAll('.single-student-name-input');
        if(structuralRows.length > 0) {
            noStudentsTrainedInput.value = structuralRows.length;
        } else {
            noStudentsTrainedInput.value = '';
        }
    }

    if(addStudentRowBtn) {
        addStudentRowBtn.addEventListener('click', function() {
            studentRowsContainer.appendChild(createStudentRowElement(''));
            reindexStudentPlaceholders();
            updateTrainedStudentsCount();
        });
    }

    function setStudentFieldsData(commaSeparatedString) {
        studentRowsContainer.innerHTML = ''; 
        if(!commaSeparatedString || commaSeparatedString.trim() === '') {
            studentRowsContainer.appendChild(createStudentRowElement(''));
            reindexStudentPlaceholders();
            return;
        }
        
        const individualNames = commaSeparatedString.split(',');
        individualNames.forEach(name => {
            if(name.trim() !== '') {
                studentRowsContainer.appendChild(createStudentRowElement(name));
            }
        });
        reindexStudentPlaceholders();
    }

    if(addNewBtn) {
        addNewBtn.addEventListener('click', function() {
            modalForm.reset(); 
            document.getElementById('modal_edit_id').value = ''; 
            modalTitle.innerText = "Internship & Training Records Form";
            modalSubmitBtn.innerText = "Save Records";
            modalSubmitBtn.style.display = "block";
            addStudentRowBtn.style.display = "block";
            modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = false;
                el.readOnly = false;
            });
            setStudentFieldsData('');
        });
    }

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            modalTitle.innerText = "Edit Internship Info";
            modalSubmitBtn.innerText = "Save Changes";
            
            document.getElementById('modal_edit_id').value = this.getAttribute('data-id');
            document.getElementById('modal_task_no').value = this.getAttribute('data-task');
            document.getElementById('modal_title').value = this.getAttribute('data-title');
            document.getElementById('modal_project_investigator').value = this.getAttribute('data-pi');
            document.getElementById('modal_no_students_trained').value = this.getAttribute('data-trained');
            document.getElementById('modal_no_days_trained').value = this.getAttribute('data-days');
            document.getElementById('modal_content').value = this.getAttribute('data-content');
            
            const currentNamesStr = this.getAttribute('data-names') || '';
            setStudentFieldsData(currentNamesStr);

            const isViewOnly = this.getAttribute('data-view-only') === 'true';
            if (isViewOnly) {
                modalTitle.innerText = "View Internship Info";
                modalSubmitBtn.style.display = "none";
                addStudentRowBtn.style.display = "none";
                modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = true;
                    el.readOnly = true;
                });
            } else {
                modalSubmitBtn.style.display = "block";
                addStudentRowBtn.style.display = "block";
                modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = false;
                    el.readOnly = false;
                });
            }
        });
    });

    if(modalForm) {
        modalForm.addEventListener('submit', function(e) {
            const allInputs = studentRowsContainer.querySelectorAll('.single-student-name-input');
            const valuesArray = [];
            
            allInputs.forEach(input => {
                const cleanedValue = input.value.trim();
                if(cleanedValue !== '') {
                    valuesArray.push(cleanedValue);
                }
            });
            
            hiddenStudentsInput.value = valuesArray.join(', ');
        });
    }

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