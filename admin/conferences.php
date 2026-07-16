<?php
session_start();

require_once 'role_access.php';

// Check for 'username' and 'institute_prefix'
if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix'])) {
    header("Location: index.php");
    exit();
}

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

// Table name built only from a whitelisted value above — safe from injection
$table = "{$prefix}_conferences";

// Database & logic
require_once 'config/db.php';

$success = false;
$error   = '';

// Self-healing database table creation
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `$table` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `taskno` VARCHAR(50) DEFAULT NULL,
            `title` VARCHAR(255) NOT NULL,
            `organizer` VARCHAR(255) NOT NULL,
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `location` VARCHAR(255) NOT NULL,
            `submission_deadline` DATE DEFAULT NULL,
            `website_url` VARCHAR(1000) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_conf_dates` (`start_date`, `end_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
} catch (PDOException $e) {
    // Ignore error
}

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
    $taskno              = trim($_POST['taskno']              ?? '');
    $title               = trim($_POST['title']               ?? '');
    $organizer           = trim($_POST['organizer']           ?? '');
    $start_date          = $_POST['start_date']               ?? '';
    $end_date            = $_POST['end_date']                 ?? '';
    $location            = trim($_POST['location']            ?? '');
    $submission_deadline = $_POST['submission_deadline']     ?? '';
    $website_url         = trim($_POST['website_url']         ?? '');
    $edit_id             = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to update records for this institute.';
    } elseif (empty($title) || empty($organizer) || empty($start_date) || empty($end_date) || empty($location)) {
        $error = 'Title, Organizer, Start/End Dates, and Location/Venue are required fields.';
    } else {
        try {
            if ($edit_id) {
                // UPDATE RECORD
                $stmt = $pdo->prepare("
                    UPDATE `$table` SET
                        taskno = :taskno,
                        title = :title,
                        organizer = :organizer,
                        start_date = :start_date,
                        end_date = :end_date,
                        location = :location,
                        submission_deadline = :submission_deadline,
                        website_url = :website_url
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':taskno'              => $taskno ?: null,
                    ':title'               => $title,
                    ':organizer'           => $organizer,
                    ':start_date'          => $start_date,
                    ':end_date'            => $end_date,
                    ':location'            => $location,
                    ':submission_deadline' => $submission_deadline ?: null,
                    ':website_url'         => $website_url ?: null,
                    ':id'                  => $edit_id
                ]);
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=updated");
                exit;
            } else {
                // INSERT NEW RECORD
                $stmt = $pdo->prepare("
                    INSERT INTO `$table`
                        (taskno, title, organizer, start_date, end_date, location, submission_deadline, website_url)
                    VALUES
                        (:taskno, :title, :organizer, :start_date, :end_date, :location, :submission_deadline, :website_url)
                ");
                $stmt->execute([
                    ':taskno'              => $taskno ?: null,
                    ':title'               => $title,
                    ':organizer'           => $organizer,
                    ':start_date'          => $start_date,
                    ':end_date'            => $end_date,
                    ':location'            => $location,
                    ':submission_deadline' => $submission_deadline ?: null,
                    ':website_url'         => $website_url ?: null
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
$conferences = [];
try {
    $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY start_date DESC");
    $conferences = $stmt->fetchAll();
} catch (PDOException $e) {
    // Suppress error or handle gracefully
}

$total_records = count($conferences);
$upcoming_count = 0;
$current_date = date('Y-m-d');
foreach ($conferences as $c) {
    if (!empty($c['start_date']) && $c['start_date'] > $current_date) {
        $upcoming_count++;
    }
}
$pageTitle = "Conferences Management | ANRF-PAIR";
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">

<style>
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
        width: 100%;
    }
    .table-theme-sapphire thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 12.5px !important;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 12px 16px !important;
    }
    .table-theme-sapphire tbody td {
        padding: 14px 16px !important;
        border-bottom: 1px solid #edf2f7 !important;
        color: #1e293b;
        font-size: 13.5px;
        vertical-align: middle;
    }
    .index-badge-circle {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 11px;
    }
    .registry-task-link {
        font-weight: 700;
        color: #bc2121 !important;
        display: block;
        font-size: 11.5px;
        margin-bottom: 3px;
        text-transform: uppercase;
    }
    .registry-main-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 14px;
        display: block;
        line-height: 1.4;
    }
    .registry-meta-text {
        font-size: 13px;
        color: #334155;
    }
    .registry-sub-label {
        font-size: 11px;
        color: #64748b;
        display: block;
        margin-top: 2px;
    }
    .btn-action-compact {
        width: 28px;
        height: 28px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    .btn-action-edit-yellow {
        background-color: #fef08a !important;
        color: #854d0e !important;
        border: 1px solid #fef08a !important;
    }
    .btn-action-edit-yellow:hover {
        background-color: #fde047 !important;
    }
    .btn-action-delete-red {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
        border: 1px solid #fee2e2 !important;
    }
    .btn-action-delete-red:hover {
        background-color: #fca5a5 !important;
    }
    .kpi-widget-card {
        border-radius: 20px !important;
        padding: 20px 24px;
        color: #ffffff;
        border: none;
        box-shadow: 0 8px 24px rgba(14, 116, 144, 0.3);
        background: linear-gradient(135deg, #0e7490 0%, #06b6d4 100%) !important;
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
                    <li class="breadcrumb-item"><a href="#">Events Management</a></li>
                    <li class="breadcrumb-item active">Conferences Dashboard</li>
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

            <!-- WIDGETS ROW -->
            <div class="row mb-4">
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-hotel"></i></div>
                            <span class="kpi-title-text">Total Conferences</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_records ?></span>
                                <span class="kpi-subtext">tracked</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-clock"></i></div>
                            <span class="kpi-title-text">Upcoming Conferences</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $upcoming_count ?></span>
                                <span class="kpi-subtext">scheduled</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-earth-americas"></i></div>
                            <span class="kpi-title-text">Active Institute</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value" style="font-size: 20px;"><?= htmlspecialchars(strtoupper($prefix)) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- REGISTRY TABLE -->
            <div class="col-lg-12 px-0">
                <div class="card registry-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 bg-white border-0">
                        <h4 class="card-title mb-0" style="color: #024283; font-weight: 700; font-size: 15px;">
                            <i class="fa-solid fa-hotel me-2"></i>CONFERENCES CMS MANAGEMENT
                        </h4>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-success btn-sm text-white px-3" data-bs-toggle="modal" data-bs-target="#conferenceModal" id="addNewBtn" style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Conference
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-theme-sapphire" data-paginate="true">
                            <thead>
                                <tr>
                                    <th style="width: 5%; min-width: 60px; text-align: center;">S.No</th>
                                    <th style="width: 35%; min-width: 250px;">Conference Details</th>
                                    <th style="width: 20%; min-width: 180px;">Hosting/Participating Org</th>
                                    <th style="width: 15%; min-width: 140px;">Venue / Location</th>
                                    <th style="width: 15%; min-width: 160px; white-space: nowrap;">Dates &amp; Deadlines</th>
                                    <th style="width: 10%; min-width: 110px; text-align: center; white-space: nowrap;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($conferences)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4" style="font-size: 13px;">No conference records tracked yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $rowCounter = 1;
                                    foreach ($conferences as $conf):
                                        // Dynamic schema fallback mapping to handle old vs new database columns defensively
                                        $organizerVal = htmlspecialchars($conf['organizer'] ?? $conf['organisers'] ?? '');
                                        $locationVal  = htmlspecialchars($conf['location'] ?? $conf['institute'] ?? '');
                                        $startDateVal = $conf['start_date'] ?? $conf['conf_date'] ?? '';
                                        $endDateVal   = $conf['end_date'] ?? $conf['conf_date'] ?? '';
                                        $websiteVal   = htmlspecialchars($conf['website_url'] ?? '');
                                        $deadlineVal  = $conf['submission_deadline'] ?? '';
                                    ?>
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <span class="index-badge-circle"><?= $rowCounter++ ?></span>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <span class="registry-task-link">
                                                    <?= htmlspecialchars($conf['taskno'] ?: 'TASK-UNASSIGNED') ?>
                                                </span>
                                                <span class="registry-main-title">
                                                    <?= htmlspecialchars($conf['title']) ?>
                                                </span>
                                                <?php if (!empty($websiteVal)): ?>
                                                    <span class="d-block mt-1">
                                                        <a href="<?= $websiteVal ?>" target="_blank" class="text-info" style="font-size: 12px;"><i class="fa fa-external-link me-1"></i> Official Website</a>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <span class="registry-meta-text font-w600 text-dark">
                                                    <?= $organizerVal ?>
                                                </span>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <span class="text-dark" style="font-size: 13px;">
                                                    <i class="fa fa-map-marker text-danger me-1"></i><?= $locationVal ?>
                                                </span>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <div style="font-size: 12px;">
                                                    <strong>Duration:</strong><br>
                                                    <span class="text-muted">
                                                        <?= $startDateVal ? date('d M Y', strtotime($startDateVal)) : '—' ?> - <?= $endDateVal ? date('d M Y', strtotime($endDateVal)) : '—' ?>
                                                    </span>
                                                    <?php if ($deadlineVal): ?>
                                                        <br><strong>Sub. Deadline:</strong><br>
                                                        <span class="text-danger font-w600">
                                                            <?= date('d M Y', strtotime($deadlineVal)) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td style="text-align: center; white-space: nowrap; vertical-align: middle;">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-edit-yellow edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#conferenceModal"
                                                            data-id="<?= $conf['id'] ?>"
                                                            data-taskno="<?= htmlspecialchars($conf['taskno'] ?? '') ?>"
                                                            data-title="<?= htmlspecialchars($conf['title']) ?>"
                                                            data-organizer="<?= $organizerVal ?>"
                                                            data-start_date="<?= $startDateVal ?>"
                                                            data-end_date="<?= $endDateVal ?>"
                                                            data-location="<?= $locationVal ?>"
                                                            data-submission_deadline="<?= $deadlineVal ?>"
                                                            data-website_url="<?= $websiteVal ?>"
                                                            title="Edit Record">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-delete-red delete-confirm-trigger"
                                                            data-id="<?= $conf['id'] ?>"
                                                            title="Delete Record">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-info text-white edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#conferenceModal"
                                                            data-view-only="true"
                                                            data-id="<?= $conf['id'] ?>"
                                                            data-taskno="<?= htmlspecialchars($conf['taskno'] ?? '') ?>"
                                                            data-title="<?= htmlspecialchars($conf['title']) ?>"
                                                            data-organizer="<?= $organizerVal ?>"
                                                            data-start_date="<?= $startDateVal ?>"
                                                            data-end_date="<?= $endDateVal ?>"
                                                            data-location="<?= $locationVal ?>"
                                                            data-submission_deadline="<?= $deadlineVal ?>"
                                                            data-website_url="<?= $websiteVal ?>"
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
                        <p class="mb-0 text-muted small font-w500">Total: <?= $total_records ?> conferences</p>
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
    <div class="modal fade" id="conferenceModal" tabindex="-1" aria-labelledby="conferenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="conferenceModalLabel">Conference Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="modalForm">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="modal_edit_id">

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Task No</label>
                                <input type="text" name="taskno" id="modal_taskno" class="form-control" placeholder="e.g. TASK-2">
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label">Conference Title *</label>
                                <input type="text" name="title" id="modal_title" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hosting/Participating Organization *</label>
                                <input type="text" name="organizer" id="modal_organizer" class="form-control" placeholder="e.g. IEEE, Osmania University" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location/Venue *</label>
                                <input type="text" name="location" id="modal_location" class="form-control" placeholder="e.g. Hyderabad, India (or Online)" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Start Date *</label>
                                <input type="date" name="start_date" id="modal_start_date" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">End Date *</label>
                                <input type="date" name="end_date" id="modal_end_date" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Paper Submission Deadline</label>
                                <input type="date" name="submission_deadline" id="modal_submission_deadline" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Official Website URL</label>
                                <input type="url" name="website_url" id="modal_website_url" class="form-control" placeholder="https://conference-website.org">
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
                    Are you sure you want to permanently delete this conference record? This operation cannot be rolled back.
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
<script src="js/table-pagination.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const addNewBtn = document.getElementById('addNewBtn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const modalTitle = document.getElementById('conferenceModalLabel');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalForm = document.getElementById('modalForm');

    const deleteTriggers = document.querySelectorAll('.delete-confirm-trigger');
    const modalDeleteExecutionLink = document.getElementById('modalDeleteExecutionLink');
    const bootstrapDeleteInstance = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    if(addNewBtn) {
        addNewBtn.addEventListener('click', function() {
            modalForm.reset();
            document.getElementById('modal_edit_id').value = '';
            modalTitle.innerText = "Add Conference";
            modalSubmitBtn.innerText = "Save Records";
            modalSubmitBtn.style.display = "block";
            modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = false;
                el.readOnly = false;
            });
        });
    }

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const isViewOnly = this.getAttribute('data-view-only') === 'true';
            if (isViewOnly) {
                modalTitle.innerText = "View Conference Info";
                modalSubmitBtn.style.display = "none";
                modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = true;
                    el.readOnly = true;
                });
            } else {
                modalTitle.innerText = "Edit Conference Info";
                modalSubmitBtn.innerText = "Save Changes";
                modalSubmitBtn.style.display = "block";
                modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = false;
                    el.readOnly = false;
                });
            }

            document.getElementById('modal_edit_id').value = this.getAttribute('data-id');
            document.getElementById('modal_taskno').value = this.getAttribute('data-taskno');
            document.getElementById('modal_title').value = this.getAttribute('data-title');
            document.getElementById('modal_organizer').value = this.getAttribute('data-organizer');
            document.getElementById('modal_start_date').value = this.getAttribute('data-start_date');
            document.getElementById('modal_end_date').value = this.getAttribute('data-end_date');
            document.getElementById('modal_location').value = this.getAttribute('data-location');
            document.getElementById('modal_submission_deadline').value = this.getAttribute('data-submission_deadline');
            document.getElementById('modal_website_url').value = this.getAttribute('data-website_url');
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