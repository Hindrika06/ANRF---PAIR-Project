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
$table = "{$prefix}_patent";

// ── Database & logic ─────────────────────────────────────────────────────────
require_once 'config/db.php';

$success = false;
$error   = '';

// Auto-generate Patent ID helper for standard additions
function generatePatentId($pdo, $table): string {
    $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
    $count = (int)$stmt->fetchColumn();
    return 'PAT-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
}

$patentId = generatePatentId($pdo, $table);

// 1. HANDLE DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to delete records for this institute.';
    } else {
    try {
        // Optional: Clean up associated server-side assets if they exist
        $stmt = $pdo->prepare("SELECT patent_file FROM `$table` WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        $row = $stmt->fetch();
        if ($row) {
            if (!empty($row['patent_file']) && file_exists($row['patent_file'])) @unlink($row['patent_file']);
        }

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

    $task_no          = trim($_POST['task_no']          ?? '');
    $req_patent_id    = trim($_POST['patent_id']        ?? '');
    $patent_title     = trim($_POST['patent_title']     ?? '');
    $inventor_name    = trim($_POST['inventor_name']    ?? '');
    $co_inventors     = trim($_POST['co_inventors']     ?? '');
    $application_no   = trim($_POST['application_no']   ?? '');
    $patent_no        = trim($_POST['patent_no']        ?? '');
    $country          = trim($_POST['country']          ?? '');
    $filing_date      = $_POST['filing_date']           ?? '';
    $publication_date = $_POST['publication_date']      ?? '';
    $grant_date       = $_POST['grant_date']            ?? '';
    $status           = trim($_POST['status']           ?? 'Filed');
    $technology_area  = trim($_POST['technology_area']  ?? '');
    $abstract         = trim($_POST['abstract']         ?? '');
    $edit_id          = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to update records for this institute.';
    } else {
    try {
        // Direct target folder
        $uploadDir = 'uploads/patents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadFile = function (string $field) use ($uploadDir): ?string {
            if (empty($_FILES[$field]['name'])) return null;
            $f = $_FILES[$field];
            if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException("Upload error: " . $f['error']);
            if ($f['size'] > 10 * 1024 * 1024) throw new RuntimeException("File exceeds 10 MB limit.");
            $ext  = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $dest = $uploadDir . uniqid('pat_', true) . '.' . $ext;

            if (!move_uploaded_file($f['tmp_name'], $dest))
                throw new RuntimeException("Could not save uploaded file. Check folder write configurations.");
            return $dest;
        };

        $patentFile = $uploadFile('patent_file');

        if ($edit_id) {
            // UPDATE EXISTING PATENT RECORD
            $query = "UPDATE `$table` SET 
                        task_no = :task_no,
                        patent_title = :patent_title, 
                        inventor_name = :inventor_name, 
                        co_inventors = :co_inventors, 
                        application_no = :application_no, 
                        patent_no = :patent_no, 
                        country = :country, 
                        filing_date = :filing_date, 
                        publication_date = :publication_date, 
                        grant_date = :grant_date, 
                        status = :status, 
                        technology_area = :technology_area, 
                        abstract = :abstract";

            $params = [
                ':task_no'          => $task_no,
                ':patent_title'     => $patent_title,
                ':inventor_name'    => $inventor_name,
                ':co_inventors'     => $co_inventors,
                ':application_no'   => $application_no,
                ':patent_no'        => $patent_no,
                ':country'          => $country,
                ':filing_date'      => $filing_date      ?: null,
                ':publication_date' => $publication_date ?: null,
                ':grant_date'       => $grant_date       ?: null,
                ':status'           => $status,
                ':technology_area'  => $technology_area,
                ':abstract'         => $abstract,
                ':id'               => $edit_id
            ];

            if ($patentFile) {
                $query .= ", patent_file = :patent_file";
                $params[':patent_file'] = $patentFile;
            }

            $query .= " WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=updated");
            exit;
        } else {
            // INSERT NEW PATENT RECORD
            $final_id = !empty($req_patent_id) ? $req_patent_id : $patentId;
            $stmt = $pdo->prepare("
                INSERT INTO `$table`
                    (task_no, patent_id, patent_title, inventor_name, co_inventors,
                     application_no, patent_no, country,
                     filing_date, publication_date, grant_date,
                     status, technology_area, abstract,
                     patent_file, created_at)
                VALUES
                    (:task_no, :patent_id, :patent_title, :inventor_name, :co_inventors,
                     :application_no, :patent_no, :country,
                     :filing_date, :publication_date, :grant_date,
                     :status, :technology_area, :abstract,
                     :patent_file, NOW())
            ");
            $stmt->execute([
                ':task_no'          => $task_no,
                ':patent_id'        => $final_id,
                ':patent_title'     => $patent_title,
                ':inventor_name'    => $inventor_name,
                ':co_inventors'     => $co_inventors,
                ':application_no'   => $application_no,
                ':patent_no'        => $patent_no,
                ':country'          => $country,
                ':filing_date'      => $filing_date      ?: null,
                ':publication_date' => $publication_date ?: null,
                ':grant_date'       => $grant_date       ?: null,
                ':status'           => $status,
                ':technology_area'  => $technology_area,
                ':abstract'         => $abstract,
                ':patent_file'      => $patentFile,
            ]);

            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=inserted");
            exit;
        }
    } catch (RuntimeException $e) {
        $error = "System Upload Notice: " . $e->getMessage();
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
    }
}

// 4. FETCH ALL RECORDS FOR TABLE PRESENTATION
$patents = [];
try {
    $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY id DESC");
    $patents = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Could not load data records: ' . $e->getMessage();
}

$total_records = count($patents);

// 5. CALCULATE PATENT STATS
$total_patents = $total_records;
$granted_count = 0;
$pending_count = 0;
$unique_inventors = [];

foreach ($patents as $p) {
    if ($p['status'] === 'Granted') {
        $granted_count++;
    }
    if ($p['status'] === 'Pending' || $p['status'] === 'Filed') {
        $pending_count++;
    }
    if (!empty(trim($p['inventor_name']))) {
        $unique_inventors[trim($p['inventor_name'])] = true;
    }
}
$total_inventors = count($unique_inventors);
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

    .status-pill-custom {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
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
        background-color: #fff8e1;
        color: #b26a00;
    }
    .status-pill-custom.status-pending::before {
        background-color: #b26a00;
    }

    .status-pill-custom.status-rejected {
        background-color: #fdecea;
        color: #c62828;
    }
    .status-pill-custom.status-rejected::before {
        background-color: #c62828;
    }

    .status-pill-custom.status-default {
        background-color: #f1f3f4;
        color: #5f6368;
    }
    .status-pill-custom.status-default::before {
        background-color: #5f6368;
    }

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

    .kpi-widget-card {
        border-radius: 10px !important;
        padding: 20px 24px;
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        background-color: #7c3aed !important;
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

    .data-details-subtext {
        font-size: 11px;
        color: #666;
        display: block;
        margin-top: 2px;
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
                    <li class="breadcrumb-item active">Patent Dashboard</li>
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
                            <div class="kpi-icon-circle"><i class="fa-solid fa-certificate"></i></div>
                            <span class="kpi-title-text">Total Patents</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_patents ?></span>
                                <span class="kpi-subtext">tracked</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-circle-check"></i></div>
                            <span class="kpi-title-text">Granted</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $granted_count ?></span>
                                <span class="kpi-subtext">Approved</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-hourglass-half"></i></div>
                            <span class="kpi-title-text">Pending / Filed</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $pending_count ?></span>
                                <span class="kpi-subtext">In progress</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-user-pen"></i></div>
                            <span class="kpi-title-text">Inventors</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_inventors ?></span>
                                <span class="kpi-subtext">Unique</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 px-0">
                <div class="card registry-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 bg-white border-0">
                        <h4 class="card-title mb-0" style="color: #024283; font-weight: 700; font-size: 15px;">
                            <i class="fa-solid fa-certificate me-2"></i>REGISTERED PATENTS LIST
                        </h4>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-success btn-sm text-white px-3" data-bs-toggle="modal" data-bs-target="#patentModal" id="addNewBtn" style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Patent
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-theme-sapphire">
                            <thead>
                                <tr>
                                    <th style="width: 4%; text-align: center;">S.No</th>
                                    <th style="width: 38%;">Patent Details &amp; Inventors</th>
                                    <th style="width: 25%;">Identifiers</th>
                                    <th style="width: 12%; text-align: center;">Status</th>
                                    <th style="width: 11%; text-align: center;">Files</th>
                                    <th style="width: 10%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($patents)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4" style="font-size: 13px;">No patents registered yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $sno = 1;
                                    foreach ($patents as $patent):
                                        $statusClass = 'status-default';
                                        if ($patent['status'] === 'Granted')  $statusClass = 'status-granted';
                                        if ($patent['status'] === 'Pending')  $statusClass = 'status-pending';
                                        if ($patent['status'] === 'Filed')    $statusClass = 'status-pending';
                                        if ($patent['status'] === 'Rejected') $statusClass = 'status-rejected';
                                    ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="index-badge-circle"><?= $sno++ ?></span>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);" class="registry-task-link">
                                                    <?= htmlspecialchars($patent['task_no'] ?: 'TASK-UNASSIGNED') ?>
                                                </a>
                                                <span class="registry-main-title">
                                                    <?= htmlspecialchars($patent['patent_title'] ?: 'Untitled Patent') ?>
                                                </span>
                                                <?php if (!empty($patent['technology_area'])): ?>
                                                    <span class="registry-tag-pill"><?= htmlspecialchars($patent['technology_area']) ?></span>
                                                <?php endif; ?>
                                                <span class="registry-sub-label">
                                                    <strong>Inventor:</strong> <?= htmlspecialchars($patent['inventor_name'] ?: '—') ?>
                                                    <?php if (!empty($patent['co_inventors'])): ?>
                                                        | <strong>Co:</strong> <?= htmlspecialchars($patent['co_inventors']) ?>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="registry-meta-text font-w600 text-dark d-block">
                                                    ID: <?= htmlspecialchars($patent['patent_id']) ?>
                                                </span>
                                                <span class="registry-sub-label">
                                                    App No: <?= htmlspecialchars($patent['application_no'] ?: '—') ?>
                                                </span>
                                                <span class="registry-sub-label">
                                                    Pat No: <?= htmlspecialchars($patent['patent_no'] ?: '—') ?>
                                                    <?= !empty($patent['country']) ? ' · ' . htmlspecialchars($patent['country']) : '' ?>
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <span class="status-pill-custom <?= $statusClass ?>"><?= htmlspecialchars($patent['status']) ?></span>
                                            </td>
                                            <td style="text-align: center;">
                                                <?php if (!empty($patent['patent_file']) && file_exists($patent['patent_file'])): ?>
                                                    <a href="<?= htmlspecialchars($patent['patent_file']) ?>" target="_blank" class="status-pill-custom status-granted text-decoration-none">View PDF</a>
                                                <?php else: ?>
                                                    <span class="status-pill-custom status-default">No File</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-edit-yellow edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#patentModal"
                                                            data-id="<?= $patent['id'] ?>"
                                                            data-taskno="<?= htmlspecialchars($patent['task_no'] ?? '') ?>"
                                                            data-patid="<?= htmlspecialchars($patent['patent_id']) ?>"
                                                            data-title="<?= htmlspecialchars($patent['patent_title']) ?>"
                                                            data-inventor="<?= htmlspecialchars($patent['inventor_name']) ?>"
                                                            data-coinventors="<?= htmlspecialchars($patent['co_inventors'] ?? '') ?>"
                                                            data-appno="<?= htmlspecialchars($patent['application_no'] ?? '') ?>"
                                                            data-patno="<?= htmlspecialchars($patent['patent_no'] ?? '') ?>"
                                                            data-country="<?= htmlspecialchars($patent['country'] ?? '') ?>"
                                                            data-filing="<?= $patent['filing_date'] ?? '' ?>"
                                                            data-pub="<?= $patent['publication_date'] ?? '' ?>"
                                                            data-grant="<?= $patent['grant_date'] ?? '' ?>"
                                                            data-status="<?= htmlspecialchars($patent['status']) ?>"
                                                            data-tech="<?= htmlspecialchars($patent['technology_area'] ?? '') ?>"
                                                            data-abstract="<?= htmlspecialchars($patent['abstract'] ?? '') ?>"
                                                            title="Edit Record">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-delete-red delete-confirm-trigger"
                                                            data-id="<?= $patent['id'] ?>"
                                                            title="Delete Record">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-info text-white edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#patentModal"
                                                            data-view-only="true"
                                                            data-id="<?= $patent['id'] ?>"
                                                            data-taskno="<?= htmlspecialchars($patent['task_no'] ?? '') ?>"
                                                            data-patid="<?= htmlspecialchars($patent['patent_id']) ?>"
                                                            data-title="<?= htmlspecialchars($patent['patent_title']) ?>"
                                                            data-inventor="<?= htmlspecialchars($patent['inventor_name']) ?>"
                                                            data-coinventors="<?= htmlspecialchars($patent['co_inventors'] ?? '') ?>"
                                                            data-appno="<?= htmlspecialchars($patent['application_no'] ?? '') ?>"
                                                            data-patno="<?= htmlspecialchars($patent['patent_no'] ?? '') ?>"
                                                            data-country="<?= htmlspecialchars($patent['country'] ?? '') ?>"
                                                            data-filing="<?= $patent['filing_date'] ?? '' ?>"
                                                            data-pub="<?= $patent['publication_date'] ?? '' ?>"
                                                            data-grant="<?= $patent['grant_date'] ?? '' ?>"
                                                            data-status="<?= htmlspecialchars($patent['status']) ?>"
                                                            data-tech="<?= htmlspecialchars($patent['technology_area'] ?? '') ?>"
                                                            data-abstract="<?= htmlspecialchars($patent['abstract'] ?? '') ?>"
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

    <div class="modal fade" id="patentModal" tabindex="-1" aria-labelledby="patentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patentModalLabel">Patent Registration Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="modalForm">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="modal_edit_id">

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Task No</label>
                                <input type="text" name="task_no" id="modal_task_no" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Patent ID</label>
                                <input type="text" name="patent_id" id="modal_patent_id" class="form-control" value="<?= htmlspecialchars($patentId) ?>" readonly>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label form-label-grey">Patent Title</label>
                                <input type="text" name="patent_title" id="modal_patent_title" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Inventor Name</label>
                                <input type="text" name="inventor_name" id="modal_inventor_name" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Co-Inventor(s)</label>
                                <input type="text" name="co_inventors" id="modal_co_inventors" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Application Number</label>
                                <input type="text" name="application_no" id="modal_application_no" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Patent Number</label>
                                <input type="text" name="patent_no" id="modal_patent_no" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Country</label>
                                <input type="text" name="country" id="modal_country" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Filing Date</label>
                                <input type="date" name="filing_date" id="modal_filing_date" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Publication Date</label>
                                <input type="date" name="publication_date" id="modal_publication_date" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Grant Date</label>
                                <input type="date" name="grant_date" id="modal_grant_date" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Patent Status</label>
                                <select name="status" id="modal_status" class="form-control default-select">
                                    <option value="Filed">Filed</option>
                                    <option value="Published">Published</option>
                                    <option value="Granted">Granted</option>
                                    <option value="Rejected">Rejected</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Technology Area</label>
                                <input type="text" name="technology_area" id="modal_technology_area" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Abstract</label>
                                <textarea name="abstract" id="modal_abstract" rows="4" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Patent Document (PDF)</label>
                                <input type="file" name="patent_file" class="form-control" accept=".pdf">
                                <small class="text-muted">PDF only · Max 10 MB</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success text-white" id="modalSubmitBtn">Upload Patent</button>
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
                    Are you sure you want to permanently delete this patent record? This operation cannot be rolled back.
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    const addNewBtn = document.getElementById('addNewBtn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const modalTitle = document.getElementById('patentModalLabel');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalForm = document.getElementById('modalForm');
    const defaultPatentId = "<?= htmlspecialchars($patentId) ?>";

    const deleteTriggers = document.querySelectorAll('.delete-confirm-trigger');
    const modalDeleteExecutionLink = document.getElementById('modalDeleteExecutionLink');
    const bootstrapDeleteInstance = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    if(addNewBtn) {
        addNewBtn.addEventListener('click', function() {
            modalForm.reset();
            document.getElementById('modal_edit_id').value = '';
            document.getElementById('modal_task_no').value = '';
            document.getElementById('modal_patent_id').value = defaultPatentId;
            modalTitle.innerText = "Patent Registration Form";
            modalSubmitBtn.innerText = "Save Patent";
            modalSubmitBtn.style.display = "block";
            modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = false;
                el.readOnly = false;
            });

            if(jQuery('.default-select').length > 0) {
                jQuery('#modal_status').val('Filed').selectpicker('refresh');
            }
        });
    }

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const isViewOnly = this.getAttribute('data-view-only') === 'true';
            if (isViewOnly) {
                modalTitle.innerText = "View Patent Info";
                modalSubmitBtn.style.display = "none";
                modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = true;
                    el.readOnly = true;
                });
            } else {
                modalTitle.innerText = "Edit Patent Info";
                modalSubmitBtn.innerText = "Save Changes";
                modalSubmitBtn.style.display = "block";
                modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = false;
                    el.readOnly = false;
                });
            }

            document.getElementById('modal_edit_id').value = this.getAttribute('data-id');
            document.getElementById('modal_task_no').value = this.getAttribute('data-taskno');
            document.getElementById('modal_patent_id').value = this.getAttribute('data-patid');
            document.getElementById('modal_patent_title').value = this.getAttribute('data-title');
            document.getElementById('modal_inventor_name').value = this.getAttribute('data-inventor');
            document.getElementById('modal_co_inventors').value = this.getAttribute('data-coinventors');
            document.getElementById('modal_application_no').value = this.getAttribute('data-appno');
            document.getElementById('modal_patent_no').value = this.getAttribute('data-patno');
            document.getElementById('modal_country').value = this.getAttribute('data-country');
            document.getElementById('modal_filing_date').value = this.getAttribute('data-filing');
            document.getElementById('modal_publication_date').value = this.getAttribute('data-pub');
            document.getElementById('modal_grant_date').value = this.getAttribute('data-grant');
            document.getElementById('modal_technology_area').value = this.getAttribute('data-tech');
            document.getElementById('modal_abstract').value = this.getAttribute('data-abstract');

            const statusVal = this.getAttribute('data-status') || 'Filed';
            document.getElementById('modal_status').value = statusVal;
            if(jQuery('.default-select').length > 0) {
                jQuery('#modal_status').selectpicker('refresh');
            }
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