<?php
require_once 'auth_check.php';
require_once 'role_access.php';

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

$allowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

// ── Database & logic ─────────────────────────────────────────────────────────
require_once 'config/db.php';

$success = false;
$error   = '';

// 1. HANDLE DELETE
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (isSuperAdmin()) {
        $deletePrefix = $_GET['record_prefix'] ?? ($prefix !== 'all' ? $prefix : '');
    } else {
        $deletePrefix = $_SESSION['institute_prefix'] ?? '';
    }

    if (!in_array($deletePrefix, $allowedPrefixes, true) || !canEditInstitute($deletePrefix)) {
        $error = 'You are not allowed to delete records for this institute.';
    } else {
        try {
            $deleteTable = "{$deletePrefix}_publications";
            $stmt = $pdo->prepare("DELETE FROM `$deleteTable` WHERE id = :id");
            $stmt->execute([':id' => (int)$_GET['id']]);
            adminRedirect(['success_msg' => 'deleted']);
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
    $is_super             = isSuperAdmin();

    if ($is_super) {
        $targetPrefix = trim($_POST['target_prefix'] ?? ($prefix !== 'all' ? $prefix : 'uoh'));
        if (!in_array($targetPrefix, $allowedPrefixes, true)) {
            $targetPrefix = 'uoh';
        }
    } else {
        $targetPrefix = $_SESSION['institute_prefix'] ?? '';
        if (!in_array($targetPrefix, $allowedPrefixes, true)) {
            die('Invalid institute configuration.');
        }
    }

    $table = "{$targetPrefix}_publications";

    if ($task_no === '' || $publication_title === '' || $author_name === '' || $publication_journal === '' || $publication_date === '') {
        $error = 'Please fill in all required fields marked with *.';
    } else {
        if (!canEditInstitute($targetPrefix)) {
            $error = 'You are not allowed to update records for this institute.';
        } else {
        try {
            $approvalStatus = $is_super ? 'Approved' : 'Pending';
            $payload = [
                'task_no'             => $task_no,
                'publication_title'   => $publication_title,
                'author_name'         => $author_name,
                'doi_number'          => $doi_number,
                'publication_date'    => $publication_date ?: null,
                'publication_journal' => $publication_journal,
                'impact_factor'       => $impact_factor !== '' ? (float)$impact_factor : null,
                'approval_status'     => $approvalStatus
            ];

            if ($edit_id) {
                $stmt = $pdo->prepare("
                    UPDATE `$table` SET
                        task_no              = :task_no,
                        publication_title    = :publication_title,
                        author_name          = :author_name,
                        doi_number           = :doi_number,
                        publication_date     = :publication_date,
                        publication_journal  = :publication_journal,
                        impact_factor        = :impact_factor,
                        approval_status      = :approval_status
                    WHERE id = :id
                ");
                $payload['id'] = $edit_id;
                $stmt->execute($payload);

                if (!$is_super) {
                    submitKpiApprovalRequest($pdo, 'Publications', $table, $targetPrefix, $edit_id, 'UPDATE', $payload);
                    adminRedirect(['success_msg' => 'submitted']);
                } else {
                    adminRedirect(['success_msg' => 'updated']);
                }
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO `$table`
                        (task_no, publication_title, author_name, doi_number,
                         publication_date, publication_journal, impact_factor, approval_status, created_at)
                    VALUES
                        (:task_no, :publication_title, :author_name, :doi_number,
                         :publication_date, :publication_journal, :impact_factor, :approval_status, NOW())
                ");
                $stmt->execute($payload);
                $new_id = $pdo->lastInsertId();

                if (!$is_super) {
                    submitKpiApprovalRequest($pdo, 'Publications', $table, $targetPrefix, $new_id, 'CREATE', $payload);
                    adminRedirect(['success_msg' => 'submitted']);
                } else {
                    adminRedirect(['success_msg' => 'inserted']);
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
        }
    }
}

// 4. FETCH ALL CENTRALIZED RECORDS
$publications  = [];
$total_records = 0;
try {
    $publications  = fetchCentralizedKpiDataset($pdo, 'publications', $prefix, isSuperAdmin());
    usort($publications, function($a, $b) {
        return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
    });
    $total_records = count($publications);
} catch (Exception $e) {
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
        background-color: #bc2121 !important;
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
        background-color: #bc2121;
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
        color: #bc2121;
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
        color: #bc2121;
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
        background-color: #bc2121 !important;
        border-color: #bc2121 !important;
        color: #ffffff !important;
    }
    .pagination-theme-sapphire .page-link {
        color: #bc2121;
    }

    /* ──── KPI CARD COLORS (#1E88C7, #00897B, #F0932B, #7E57C2) ──── */
    .kpi-widget-card {
        border-radius: 8px !important;
        padding: 20px 20px !important;
        color: #ffffff !important;
        border: none !important;
        position: relative;
        overflow: hidden;
        min-height: 130px;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-widget-card::after {
        display: none !important;
        content: none !important;
    }
    .kpi-widget-card:hover {
        transform: translateY(-4px);
    }
    .kpi-color-1,
    .kpi-color-2,
    .kpi-color-3,
    .kpi-color-4 {
        background-color: #1E88C7 !important;
        box-shadow: 0 6px 18px rgba(30, 136, 199, 0.3) !important;
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
                    <div class="card kpi-widget-card kpi-color-1">
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
                    <div class="card kpi-widget-card kpi-color-1">
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
                    <div class="card kpi-widget-card kpi-color-1">
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
                    <div class="card kpi-widget-card kpi-color-1">
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
                        <h4 class="card-title mb-0" style="color: #bc2121; font-weight: 700; font-size: 15px;">
                            <i class="fa-solid fa-book-open me-2"></i>REGISTERED PUBLICATIONS LIST
                        </h4>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-sm text-white px-3"
                                data-bs-toggle="modal" data-bs-target="#publicationModal" id="addNewBtn"
                                style="border-radius: 6px; font-weight: 600; background: #09BD3C; border: none;">
                            <i class="fa fa-plus me-1"></i> Add Publication
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-theme-sapphire" data-paginate="true">
                            <thead>
                                <tr>
                                    <th style="width: 4%; text-align: center; background-color: #bc2121 !important; color: #ffffff !important;">S.No</th>
                                    <th style="width: 38%; background-color: #bc2121 !important; color: #ffffff !important;">Publication Info</th>
                                    <th style="width: 18%; background-color: #bc2121 !important; color: #ffffff !important;">Author / DOI</th>
                                    <th style="width: 13%; background-color: #bc2121 !important; color: #ffffff !important;">Journal</th>
                                    <th style="width: 10%; background-color: #bc2121 !important; color: #ffffff !important;">Date</th>
                                    <th style="width: 7%; text-align: center; background-color: #bc2121 !important; color: #ffffff !important;">Impact</th>
                                    <th style="width: 10%; text-align: center; background-color: #bc2121 !important; color: #ffffff !important;">Action</th>
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
                                        $approvalStatus = $pub['approval_status'] ?? 'Approved';
                                        $instPrefix     = strtoupper($pub['institute_prefix'] ?? $prefix);
                                    ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="index-badge-circle"><?= $sno++ ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="badge bg-secondary" style="font-size: 10px; font-weight: 700;"><?= htmlspecialchars($instPrefix) ?></span>
                                                    <?php if ($approvalStatus === 'Approved'): ?>
                                                        <span class="badge bg-success text-white" style="font-size: 10px;">Approved</span>
                                                    <?php elseif ($approvalStatus === 'Pending'): ?>
                                                        <span class="badge bg-warning text-dark" style="font-size: 10px;">Pending Approval</span>
                                                    <?php elseif ($approvalStatus === 'Rejected'): ?>
                                                        <span class="badge bg-danger text-white" style="font-size: 10px;">Rejected</span>
                                                    <?php endif; ?>
                                                </div>
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
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-info text-white view-kpi-btn"
                                                            data-record="<?= htmlspecialchars(json_encode($pub), ENT_QUOTES, 'UTF-8') ?>"
                                                            title="View Details">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    <?php if (canEditInstitute($pub['institute_prefix'] ?? $prefix)): ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-edit-yellow edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#publicationModal"
                                                            data-id="<?= $pub['id'] ?>"
                                                            data-record-prefix="<?= htmlspecialchars($pub['institute_prefix'] ?? $prefix) ?>"
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
                                                            data-record-prefix="<?= htmlspecialchars($pub['institute_prefix'] ?? $prefix) ?>"
                                                            title="Delete Record">
                                                        <i class="fa fa-trash"></i>
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
                        <input type="hidden" name="target_prefix" id="modal_target_prefix" value="<?= htmlspecialchars($prefix !== 'all' ? $prefix : 'uoh') ?>">

                        <?php if (isSuperAdmin()): ?>
                        <div class="row mb-3" id="target_prefix_wrapper">
                            <div class="col-md-12">
                                <label class="form-label form-label-grey">
                                    Target University / Institute <span class="text-danger">*</span>
                                </label>
                                <select id="modal_target_prefix_select" class="form-select" required>
                                    <?php foreach ($allowedPrefixes as $ap): ?>
                                        <option value="<?= $ap ?>" <?= ($prefix === $ap || ($prefix === 'all' && $ap === 'uoh')) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(getInstituteFullName($ap)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>

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

    <?php include 'includes/view_modal.php'; ?>

    <div class="footer">
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> ANRF&ndash;PAIR Project, University of Hyderabad. All rights reserved.</p>
        </div>
    </div>
</div>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="js/table-pagination.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ── READ-ONLY VIEW MODAL TRIGGER
    const viewKpiBtns = document.querySelectorAll('.view-kpi-btn');
    viewKpiBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.dataset.record) return;
            const rec = JSON.parse(this.dataset.record);
            const instPrefix = rec.institute_prefix || "<?= htmlspecialchars($prefix !== 'all' ? $prefix : 'uoh') ?>";
            const instName = (typeof getInstituteFullName === 'function') ? getInstituteFullName(instPrefix) : instPrefix.toUpperCase();

            openKpiRecordViewModal({
                moduleTitle: 'Publication Details',
                recordTitle: rec.publication_title || 'Untitled Publication',
                institutePrefix: instPrefix,
                instituteName: instName,
                approvalStatus: rec.approval_status || 'Approved',
                fields: [
                    { label: 'Task Number', value: rec.task_no, icon: 'fa-solid fa-list-check' },
                    { label: 'Publication Title', value: rec.publication_title, fullWidth: true, icon: 'fa-solid fa-book' },
                    { label: 'Primary Author', value: rec.author_name, icon: 'fa-solid fa-user-pen' },
                    { label: 'Journal Name', value: rec.publication_journal, icon: 'fa-solid fa-newspaper' },
                    { label: 'DOI Number', value: rec.doi_number, type: 'doi', icon: 'fa-solid fa-fingerprint' },
                    { label: 'Publication Date', value: rec.publication_date ? formatDate(rec.publication_date) : null, icon: 'fa-solid fa-calendar-days' },
                    { label: 'Impact Factor', value: (rec.impact_factor !== null && rec.impact_factor !== undefined && rec.impact_factor !== '') ? parseFloat(rec.impact_factor).toFixed(2) : null, icon: 'fa-solid fa-chart-line' },
                    { label: 'Created At', value: rec.created_at ? formatDate(rec.created_at) : null, icon: 'fa-solid fa-clock' }
                ]
            });
        });
    });

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
            const targetSelect = document.getElementById('modal_target_prefix_select');
            if (targetSelect) {
                targetSelect.disabled = false;
                const currentPrefix = "<?= htmlspecialchars($prefix) ?>";
                if (currentPrefix !== 'all') {
                    targetSelect.value = currentPrefix;
                } else {
                    targetSelect.value = 'uoh';
                }
                document.getElementById('modal_target_prefix').value = targetSelect.value;
            } else {
                document.getElementById('modal_target_prefix').value = "<?= htmlspecialchars($prefix !== 'all' ? $prefix : 'uoh') ?>";
            }
            modalTitle.innerText     = 'Publication Registration Form';
            modalSubmitBtn.innerText = 'Save Publication';
            modalSubmitBtn.style.display = "block";
            modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = false;
                el.readOnly = false;
            });
        });
    }

    const targetSelectElem = document.getElementById('modal_target_prefix_select');
    if (targetSelectElem) {
        targetSelectElem.addEventListener('change', function() {
            document.getElementById('modal_target_prefix').value = this.value;
        });
    }

    // ── EDIT
    editButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const isViewOnly = this.getAttribute('data-view-only') === 'true';
            const recordPrefix = this.dataset.recordPrefix || this.dataset.prefix || "<?= htmlspecialchars($prefix !== 'all' ? $prefix : 'uoh') ?>";

            document.getElementById('modal_edit_id').value       = this.dataset.id;
            document.getElementById('modal_target_prefix').value = recordPrefix;
            const targetSelect = document.getElementById('modal_target_prefix_select');
            if (targetSelect) {
                targetSelect.value = recordPrefix;
                targetSelect.disabled = true;
            }

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
                    if (el !== targetSelect) {
                        el.disabled = false;
                        el.readOnly = false;
                    }
                });
            }

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
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('action', 'delete');
            urlParams.set('id', this.dataset.id);
            const recordPrefix = this.dataset.recordPrefix || this.dataset.prefix;
            if (recordPrefix) {
                urlParams.set('record_prefix', recordPrefix);
            }
            modalDeleteExecutionLink.setAttribute('href', '?' + urlParams.toString());
            bootstrapDeleteInstance.show();
        });
    });

});
</script>
</body>
</html>