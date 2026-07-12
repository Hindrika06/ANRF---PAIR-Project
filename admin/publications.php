<?php
session_start();

require_once 'role_access.php';

// ── Check for 'username' or 'institute_prefix' ─────
if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix'])) {
    header("Location: index.php");
    exit();
}

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

$table = "{$prefix}_publications";

// ── Database & logic ─────────────────────────────────────────────────────────
require_once 'config/db.php';

$success = false;
$error   = '';

// 1. HANDLE DELETE
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

// 2. POST-REDIRECT SUCCESS FLAG
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $task_no              = trim($_POST['task_no']            ?? '');
    $publication_title    = trim($_POST['publication_title']   ?? '');
    $author_name          = trim($_POST['author_name']         ?? '');
    $doi_number           = trim($_POST['doi_number']          ?? '');
    $publication_date     = $_POST['publication_date']         ?? '';
    $publication_journal  = trim($_POST['publication_journal'] ?? '');
    $impact_factor        = trim($_POST['impact_factor']       ?? '');
    $edit_id              = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if ($task_no === '' || $publication_title === '' || $author_name === '' || $publication_journal === '' || $publication_date === '') {
        $error = 'Please fill in all required fields marked with *.';
    } else {
        if (!canEditInstitute($prefix)) {
            $error = 'You are not allowed to update records for this institute.';
        } else {
        try {
            if ($edit_id) {
                $stmt = $pdo->prepare("
                    UPDATE `$table` SET
                        task_no              = :task_no,
                        publication_title    = :publication_title,
                        author_name          = :author_name,
                        doi_number           = :doi_number,
                        publication_date     = :publication_date,
                        publication_journal  = :publication_journal,
                        impact_factor        = :impact_factor
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':task_no'             => $task_no,
                    ':publication_title'   => $publication_title,
                    ':author_name'         => $author_name,
                    ':doi_number'          => $doi_number,
                    ':publication_date'    => $publication_date ?: null,
                    ':publication_journal' => $publication_journal,
                    ':impact_factor'       => $impact_factor !== '' ? (float)$impact_factor : null,
                    ':id'                  => $edit_id,
                ]);
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=updated");
                exit;
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO `$table`
                        (task_no, publication_title, author_name, doi_number,
                         publication_date, publication_journal, impact_factor, created_at)
                    VALUES
                        (:task_no, :publication_title, :author_name, :doi_number,
                         :publication_date, :publication_journal, :impact_factor, NOW())
                ");
                $stmt->execute([
                    ':task_no'             => $task_no,
                    ':publication_title'   => $publication_title,
                    ':author_name'         => $author_name,
                    ':doi_number'          => $doi_number,
                    ':publication_date'    => $publication_date ?: null,
                    ':publication_journal' => $publication_journal,
                    ':impact_factor'       => $impact_factor !== '' ? (float)$impact_factor : null,
                ]);
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=inserted");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
        }
    }
}

// 4. FETCH ALL RECORDS
$publications  = [];
$total_records = 0;
try {
    $stmt          = $pdo->query("SELECT * FROM `$table` ORDER BY id DESC");
    $publications  = $stmt->fetchAll();
    $total_records = count($publications);
} catch (PDOException $e) {
    $error = 'Could not load records: ' . $e->getMessage();
}

// 5. CALCULATE PUBLICATION STATS
$total_publications = $total_records;
$unique_authors      = [];
$unique_journals      = [];
$impact_sum           = 0.0;
$impact_count         = 0;

foreach ($publications as $pub) {
    if (!empty(trim($pub['author_name']))) {
        $unique_authors[trim($pub['author_name'])] = true;
    }
    if (!empty(trim($pub['publication_journal']))) {
        $unique_journals[trim($pub['publication_journal'])] = true;
    }
    if ($pub['impact_factor'] !== null && $pub['impact_factor'] !== '') {
        $impact_sum += (float)$pub['impact_factor'];
        $impact_count++;
    }
}
$total_authors  = count($unique_authors);
$total_journals = count($unique_journals);
$avg_impact     = $impact_count > 0 ? round($impact_sum / $impact_count, 2) : 0;
?>

<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">

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

    .registry-tag-pill {
        display: inline-block;
        font-size: 9px;
        font-weight: 600;
        color: #b93c3c;
        background-color: #fdf2f2;
        padding: 2px 8px;
        border-radius: 20px;
        text-transform: capitalize;
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
        background-color: #bc2121 !important;
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

\

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">IPR Management</a></li>
                    <li class="breadcrumb-item active">Publications Dashboard</li>
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
                            <div class="kpi-icon-circle"><i class="fa-solid fa-book-open"></i></div>
                            <span class="kpi-title-text">Total Publications</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_publications ?></span>
                                <span class="kpi-subtext">tracked</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-user-pen"></i></div>
                            <span class="kpi-title-text">Authors</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_authors ?></span>
                                <span class="kpi-subtext">Unique</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-newspaper"></i></div>
                            <span class="kpi-title-text">Journals</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_journals ?></span>
                                <span class="kpi-subtext">Unique</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-chart-line"></i></div>
                            <span class="kpi-title-text">Avg Impact Factor</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $avg_impact ?></span>
                                <span class="kpi-subtext">Average</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 px-0">
                <div class="card registry-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 bg-white border-0">
                        <h4 class="card-title mb-0" style="color: #024283; font-weight: 700; font-size: 15px;">
                            <i class="fa-solid fa-book-open me-2"></i>REGISTERED PUBLICATIONS LIST
                        </h4>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-success btn-sm text-white px-3"
                                data-bs-toggle="modal" data-bs-target="#publicationModal" id="addNewBtn" style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Publication
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-theme-sapphire">
                            <thead>
                                <tr>
                                    <th style="width: 4%; text-align: center;">S.No</th>
                                    <th style="width: 38%;">Publication Info</th>
                                    <th style="width: 18%;">Author / DOI</th>
                                    <th style="width: 13%;">Journal</th>
                                    <th style="width: 10%;">Date</th>
                                    <th style="width: 7%; text-align: center;">Impact</th>
                                    <th style="width: 10%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($publications)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4" style="font-size: 13px;">No publications registered yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $sno = 1;
                                    foreach ($publications as $pub):
                                    ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="index-badge-circle"><?= $sno++ ?></span>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="registry-task-link">
                                                    <?= htmlspecialchars($pub['task_no'] ?: 'TASK-UNASSIGNED') ?>
                                                </a>
                                                <span class="registry-main-title">
                                                    <?= htmlspecialchars($pub['publication_title'] ?: 'Untitled Publication') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="registry-meta-text font-w600 text-dark d-block">
                                                    <?= htmlspecialchars($pub['author_name'] ?: '—') ?>
                                                </span>
                                                <span class="registry-sub-label">
                                                    DOI: <?= htmlspecialchars($pub['doi_number'] ?: '—') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="registry-meta-text">
                                                    <?= htmlspecialchars($pub['publication_journal'] ?: '—') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-dark font-w600" style="font-size: 12px;">
                                                    <?= $pub['publication_date'] ? date('d M Y', strtotime($pub['publication_date'])) : '—' ?>
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <span class="status-pill-custom status-granted">
                                                    <?= $pub['impact_factor'] !== null && $pub['impact_factor'] !== ''
                                                        ? number_format((float)$pub['impact_factor'], 2)
                                                        : '—' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-edit-yellow edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#publicationModal"
                                                            data-id="<?= $pub['id'] ?>"
                                                            data-task-no="<?= htmlspecialchars($pub['task_no'] ?? '') ?>"
                                                            data-title="<?= htmlspecialchars($pub['publication_title']) ?>"
                                                            data-author="<?= htmlspecialchars($pub['author_name']) ?>"
                                                            data-doi="<?= htmlspecialchars($pub['doi_number'] ?? '') ?>"
                                                            data-date="<?= $pub['publication_date'] ?? '' ?>"
                                                            data-journal="<?= htmlspecialchars($pub['publication_journal']) ?>"
                                                            data-impact="<?= htmlspecialchars($pub['impact_factor'] ?? '') ?>"
                                                            title="Edit Record">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-delete-red delete-confirm-trigger"
                                                            data-id="<?= $pub['id'] ?>"
                                                            title="Delete Record">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-info text-white edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#publicationModal"
                                                            data-view-only="true"
                                                            data-id="<?= $pub['id'] ?>"
                                                            data-task-no="<?= htmlspecialchars($pub['task_no'] ?? '') ?>"
                                                            data-title="<?= htmlspecialchars($pub['publication_title']) ?>"
                                                            data-author="<?= htmlspecialchars($pub['author_name']) ?>"
                                                            data-doi="<?= htmlspecialchars($pub['doi_number'] ?? '') ?>"
                                                            data-date="<?= $pub['publication_date'] ?? '' ?>"
                                                            data-journal="<?= htmlspecialchars($pub['publication_journal']) ?>"
                                                            data-impact="<?= htmlspecialchars($pub['impact_factor'] ?? '') ?>"
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

    <!-- ── ADD / EDIT MODAL ────────────────────────────────────────────── -->
    <div class="modal fade" id="publicationModal" tabindex="-1"
         aria-labelledby="publicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="publicationModalLabel">Publication Registration Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST" id="modalForm">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="modal_edit_id">

                        <!-- Task No / Publication Title -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">
                                    Task No <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="task_no" id="modal_task_no"
                                       class="form-control" placeholder="e.g. TASK-001" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label form-label-grey">
                                    Publication Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="publication_title" id="modal_publication_title"
                                       class="form-control" placeholder="Full publication title" required>
                            </div>
                        </div>

                        <!-- Author Name / DOI -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">
                                    Author Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="author_name" id="modal_author_name"
                                       class="form-control" placeholder="Primary author" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">DOI Number</label>
                                <input type="text" name="doi_number" id="modal_doi_number"
                                       class="form-control" placeholder="e.g. 10.1000/xyz123">
                            </div>
                        </div>

                        <!-- Date / Journal -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">
                                    Publication Date <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="publication_date" id="modal_publication_date"
                                       class="form-control" placeholder="Select date" autocomplete="off" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">
                                    Publication Journal <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="publication_journal" id="modal_publication_journal"
                                       class="form-control" placeholder="Journal name" required>
                            </div>
                        </div>

                        <!-- Impact Factor -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Impact Factor</label>
                                <input type="number" name="impact_factor" id="modal_impact_factor"
                                       class="form-control" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success text-white" id="modalSubmitBtn">
                            Save Publication
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── DELETE CONFIRMATION MODAL ──────────────────────────────────── -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1"
         aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-dark" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2 text-dark">
                    Are you sure you want to permanently delete this publication record?
                    This cannot be undone.
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
            <p>Copyright &copy; Designed &amp; Developed by
                <a href="https://dexignlab.com/" target="_blank">DexignLab</a> 2023
            </p>
        </div>
    </div>
</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const addNewBtn      = document.getElementById('addNewBtn');
    const editButtons    = document.querySelectorAll('.edit-btn');
    const modalTitle     = document.getElementById('publicationModalLabel');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalForm      = document.getElementById('modalForm');

    const deleteTriggers           = document.querySelectorAll('.delete-confirm-trigger');
    const modalDeleteExecutionLink = document.getElementById('modalDeleteExecutionLink');
    const bootstrapDeleteInstance  = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    // Calendar picker for Publication Date. dateFormat matches what the
    // old native date input produced (Y-m-d), so the PHP/SQL side needs
    // no changes; altInput just shows a friendlier label to the admin.
    const pubDatePicker = flatpickr("#modal_publication_date", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d M, Y"
    });

    // ── ADD NEW
    if (addNewBtn) {
        addNewBtn.addEventListener('click', function () {
            modalForm.reset();
            pubDatePicker.clear();
            document.getElementById('modal_edit_id').value = '';
            modalTitle.innerText     = 'Publication Registration Form';
            modalSubmitBtn.innerText = 'Save Publication';
            modalSubmitBtn.style.display = "block";
            modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = false;
                el.readOnly = false;
            });
        });
    }

    // ── EDIT
    editButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const isViewOnly = this.getAttribute('data-view-only') === 'true';
            if (isViewOnly) {
                modalTitle.innerText     = 'View Publication Info';
                modalSubmitBtn.style.display = "none";
                modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = true;
                    el.readOnly = true;
                });
            } else {
                modalTitle.innerText     = 'Edit Publication Info';
                modalSubmitBtn.innerText = 'Save Changes';
                modalSubmitBtn.style.display = "block";
                modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = false;
                    el.readOnly = false;
                });
            }

            document.getElementById('modal_edit_id').value             = this.dataset.id;
            document.getElementById('modal_task_no').value             = this.dataset.taskNo;
            document.getElementById('modal_publication_title').value   = this.dataset.title;
            document.getElementById('modal_author_name').value         = this.dataset.author;
            document.getElementById('modal_doi_number').value          = this.dataset.doi;

            if (this.dataset.date) {
                pubDatePicker.setDate(this.dataset.date, true);
            } else {
                pubDatePicker.clear();
            }

            document.getElementById('modal_publication_journal').value = this.dataset.journal;
            document.getElementById('modal_impact_factor').value       = this.dataset.impact;
        });
    });

    // ── DELETE
    deleteTriggers.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            modalDeleteExecutionLink.setAttribute('href', '?action=delete&id=' + this.dataset.id);
            bootstrapDeleteInstance.show();
        });
    });

});
</script>
</body>
</html>