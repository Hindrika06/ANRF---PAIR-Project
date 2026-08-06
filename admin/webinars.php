<?php
require_once 'auth_check.php';
require_once 'role_access.php';

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

// Table name built only from a whitelisted value above — safe from injection
$table = "{$prefix}_webinars";

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
            `speaker_name` VARCHAR(255) NOT NULL,
            `affiliation` VARCHAR(255) DEFAULT NULL,
            `webinar_date` DATETIME NOT NULL,
            `link` VARCHAR(1000) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `publish_status` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_webinar_date` (`webinar_date`)
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
    $taskno           = trim($_POST['taskno']           ?? '');
    $title            = trim($_POST['title']            ?? '');
    $speaker_name     = trim($_POST['speaker_name']     ?? '');
    $affiliation      = trim($_POST['affiliation']      ?? '');
    $webinar_date     = $_POST['webinar_date']          ?? '';
    $link             = trim($_POST['link']             ?? '');
    $whatsapp_link    = trim($_POST['whatsapp_link']    ?? '');
    $keynote_speaker  = trim($_POST['keynote_speaker']  ?? '');
    $resource_persons = trim($_POST['resource_persons'] ?? '');
    $conveners        = trim($_POST['conveners']        ?? '');
    $official_email   = trim($_POST['official_email']   ?? '');
    $contact_phone    = trim($_POST['contact_phone']    ?? '');
    $image            = trim($_POST['image']            ?? '');
    $description      = trim($_POST['description']      ?? '');
    $publish_status   = isset($_POST['publish_status']) ? 1 : 0;
    $edit_id          = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;
    $is_super         = isSuperAdmin();

    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to update records for this institute.';
    } elseif (empty($title) || empty($webinar_date)) {
        $error = 'Title and Webinar Date & Time are required fields.';
    } else {
        try {
            $existingCols = array_flip($pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN));
            $fieldCandidateMap = [
                'taskno'           => $taskno ?: null,
                'title'            => $title,
                'speaker_name'     => $speaker_name ?: null,
                'affiliation'      => $affiliation ?: null,
                'webinar_date'     => $webinar_date ?: null,
                'link'             => $link ?: null,
                'whatsapp_link'    => $whatsapp_link ?: null,
                'keynote_speaker'  => $keynote_speaker ?: null,
                'resource_persons' => $resource_persons ?: null,
                'conveners'        => $conveners ?: null,
                'official_email'   => $official_email ?: null,
                'contact_phone'    => $contact_phone ?: null,
                'image'            => $image ?: null,
                'description'      => $description ?: null,
                'publish_status'   => $publish_status,
                'approval_status'  => $is_super ? 'Approved' : 'Pending'
            ];

            $validPayload = [];
            foreach ($fieldCandidateMap as $col => $val) {
                if (isset($existingCols[$col])) {
                    $validPayload[$col] = $val;
                }
            }

            if ($edit_id) {
                // UPDATE RECORD
                $setSql = [];
                $params = [':id' => $edit_id];
                foreach ($validPayload as $col => $val) {
                    $setSql[] = "`$col` = :$col";
                    $params[":$col"] = $val;
                }
                $stmt = $pdo->prepare("UPDATE `$table` SET " . implode(', ', $setSql) . " WHERE id = :id");
                $stmt->execute($params);

                if (!$is_super) {
                    submitKpiApprovalRequest($pdo, 'Webinars', $table, $prefix, $edit_id, 'UPDATE', $validPayload);
                    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=submitted");
                } else {
                    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=updated");
                }
                exit;
            } else {
                // INSERT NEW RECORD
                $colsSql = implode(', ', array_map(function($c) { return "`$c`"; }, array_keys($validPayload)));
                $valsSql = implode(', ', array_map(function($c) { return ":$c"; }, array_keys($validPayload)));
                $params = [];
                foreach ($validPayload as $col => $val) {
                    $params[":$col"] = $val;
                }
                $stmt = $pdo->prepare("INSERT INTO `$table` ($colsSql) VALUES ($valsSql)");
                $stmt->execute($params);
                $new_id = $pdo->lastInsertId();

                if (!$is_super) {
                    submitKpiApprovalRequest($pdo, 'Webinars', $table, $prefix, $new_id, 'CREATE', $validPayload);
                    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=submitted");
                } else {
                    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=inserted");
                }
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// 4. FETCH CENTRALIZED DATA
$webinars = [];
try {
    $webinars = fetchCentralizedKpiDataset($pdo, 'webinars', $prefix, isSuperAdmin());
    usort($webinars, function($a, $b) {
        $tA = !empty($a['webinar_date']) ? strtotime($a['webinar_date']) : 0;
        $tB = !empty($b['webinar_date']) ? strtotime($b['webinar_date']) : 0;
        return $tB <=> $tA;
    });
} catch (Exception $e) {
    // Suppress error or handle gracefully
}

$total_records = count($webinars);
$upcoming_count = 0;
$current_time = time();
foreach ($webinars as $w) {
    if (!empty($w['webinar_date']) && strtotime($w['webinar_date']) > $current_time) {
        $upcoming_count++;
    }
}
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
        color: #024283 !important;
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
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.3);
        background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%) !important;
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
    .badge-pub {
        font-size: 10px;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 600;
    }
    .badge-pub-active {
        background-color: #10b981;
        color: #ffffff;
    }
    .badge-pub-draft {
        background-color: #94a3b8;
        color: #ffffff;
    }
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

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

            <!-- WIDGETS ROW -->
            <div class="row mb-4">
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-video"></i></div>
                            <span class="kpi-title-text">Total Webinars</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_records ?></span>
                                <span class="kpi-subtext">recorded</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-calendar-days"></i></div>
                            <span class="kpi-title-text">Upcoming Webinars</span>
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
                            <div class="kpi-icon-circle"><i class="fa-solid fa-graduation-cap"></i></div>
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
                            <i class="fa-solid fa-video me-2"></i>WEBINARS CMS MANAGEMENT
                        </h4>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-success btn-sm text-white px-3" data-bs-toggle="modal" data-bs-target="#webinarModal" id="addNewBtn" style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Webinar
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-theme-sapphire" data-paginate="true">
                            <thead>
                                <tr>
                                    <th style="width: 5%; min-width: 60px; text-align: center; background-color: #bc2121 !important; color: #ffffff !important;">S.No</th>
                                    <th style="width: 35%; min-width: 250px; background-color: #bc2121 !important; color: #ffffff !important;">Webinar Details</th>
                                    <th style="width: 25%; min-width: 180px; background-color: #bc2121 !important; color: #ffffff !important;">Speaker Info</th>
                                    <th style="width: 15%; min-width: 160px; white-space: nowrap; background-color: #bc2121 !important; color: #ffffff !important;">Date &amp; Time</th>
                                    <th style="width: 10%; min-width: 100px; text-align: center; white-space: nowrap; background-color: #bc2121 !important; color: #ffffff !important;">Status</th>
                                    <th style="width: 10%; min-width: 110px; text-align: center; white-space: nowrap; background-color: #bc2121 !important; color: #ffffff !important;">Action</th>
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
                                        // Dynamic schema fallback mapping to handle old vs new database columns defensively
                                        $speakerNameVal = htmlspecialchars($webinar['speaker_name'] ?? $webinar['investigator'] ?? '');
                                        $affiliationVal = htmlspecialchars($webinar['affiliation'] ?? $webinar['institute'] ?? '');
                                        $descriptionVal = htmlspecialchars($webinar['description'] ?? $webinar['content'] ?? '');
                                        $linkVal        = htmlspecialchars($webinar['link'] ?? '');
                                        $whatsappVal    = htmlspecialchars($webinar['whatsapp_link'] ?? '');
                                        $keynoteVal     = htmlspecialchars($webinar['keynote_speaker'] ?? '');
                                        $resourceVal    = htmlspecialchars($webinar['resource_persons'] ?? '');
                                        $convenersVal   = htmlspecialchars($webinar['conveners'] ?? '');
                                        $emailVal       = htmlspecialchars($webinar['official_email'] ?? '');
                                        $phoneVal       = htmlspecialchars($webinar['contact_phone'] ?? '');
                                         $imageVal       = htmlspecialchars($webinar['image'] ?? '');
                                        $publishStatus  = (int)($webinar['publish_status'] ?? 1);
                                        $approvalStatus = $webinar['approval_status'] ?? 'Approved';
                                        $instPrefix     = strtoupper($webinar['institute_prefix'] ?? $prefix);
                                    ?>
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <span class="index-badge-circle"><?= $rowCounter++ ?></span>
                                            </td>
                                            <td style="vertical-align: middle;">
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
                                                <span class="registry-task-link">
                                                    <?= htmlspecialchars($webinar['taskno'] ?: 'TASK-UNASSIGNED') ?>
                                                </span>
                                                <span class="registry-main-title">
                                                    <?= htmlspecialchars($webinar['title']) ?>
                                                </span>
                                                <?php if (!empty($linkVal)): ?>
                                                    <span class="d-block mt-1">
                                                        <a href="<?= $linkVal ?>" target="_blank" class="text-info" style="font-size: 12px;"><i class="fa fa-link me-1"></i> Join / Recording URL</a>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($whatsappVal)): ?>
                                                    <span class="d-block mt-1">
                                                        <a href="<?= $whatsappVal ?>" target="_blank" class="text-success" style="font-size: 12px;"><i class="fa fa-whatsapp me-1"></i> WhatsApp Group Link</a>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <span class="registry-meta-text font-w600 text-dark">
                                                    <?= $speakerNameVal ?>
                                                </span>
                                                <?php if ($affiliationVal !== ''): ?>
                                                    <span class="registry-sub-label">
                                                        <?= $affiliationVal ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="white-space: nowrap; vertical-align: middle;">
                                                <span class="text-dark font-w600" style="font-size: 12px;">
                                                    <?= $webinar['webinar_date'] ? date('d M Y, h:i A', strtotime($webinar['webinar_date'])) : '—' ?>
                                                </span>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <span class="badge badge-pub badge-pub-<?= ($publishStatus == 1) ? 'active' : 'draft' ?>">
                                                    <?= ($publishStatus == 1) ? 'Published' : 'Draft' ?>
                                                </span>
                                            </td>
                                            <td style="text-align: center; white-space: nowrap; vertical-align: middle;">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-edit-yellow edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#webinarModal"
                                                            data-id="<?= $webinar['id'] ?>"
                                                            data-taskno="<?= htmlspecialchars($webinar['taskno'] ?? '') ?>"
                                                            data-title="<?= htmlspecialchars($webinar['title']) ?>"
                                                            data-speaker_name="<?= $speakerNameVal ?>"
                                                            data-affiliation="<?= $affiliationVal ?>"
                                                            data-date="<?= $webinar['webinar_date'] ? date('Y-m-d\TH:i', strtotime($webinar['webinar_date'])) : '' ?>"
                                                            data-link="<?= $linkVal ?>"
                                                            data-whatsapp_link="<?= $whatsappVal ?>"
                                                            data-keynote_speaker="<?= $keynoteVal ?>"
                                                            data-resource_persons="<?= $resourceVal ?>"
                                                            data-conveners="<?= $convenersVal ?>"
                                                            data-official_email="<?= $emailVal ?>"
                                                            data-contact_phone="<?= $phoneVal ?>"
                                                            data-image="<?= $imageVal ?>"
                                                            data-description="<?= $descriptionVal ?>"
                                                            data-publish_status="<?= $publishStatus ?>"
                                                            title="Edit Record">
                                                         <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-delete-red delete-confirm-trigger"
                                                            data-id="<?= $webinar['id'] ?>"
                                                            title="Delete Record">
                                                         <i class="fa fa-trash"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-info text-white edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#webinarModal"
                                                            data-view-only="true"
                                                            data-id="<?= $webinar['id'] ?>"
                                                            data-taskno="<?= htmlspecialchars($webinar['taskno'] ?? '') ?>"
                                                            data-title="<?= htmlspecialchars($webinar['title']) ?>"
                                                            data-speaker_name="<?= $speakerNameVal ?>"
                                                            data-affiliation="<?= $affiliationVal ?>"
                                                            data-date="<?= $webinar['webinar_date'] ? date('Y-m-d\TH:i', strtotime($webinar['webinar_date'])) : '' ?>"
                                                            data-link="<?= $linkVal ?>"
                                                            data-whatsapp_link="<?= $whatsappVal ?>"
                                                            data-keynote_speaker="<?= $keynoteVal ?>"
                                                            data-resource_persons="<?= $resourceVal ?>"
                                                            data-conveners="<?= $convenersVal ?>"
                                                            data-official_email="<?= $emailVal ?>"
                                                            data-contact_phone="<?= $phoneVal ?>"
                                                            data-image="<?= $imageVal ?>"
                                                            data-description="<?= $descriptionVal ?>"
                                                            data-publish_status="<?= $publishStatus ?>"
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
                        <p class="mb-0 text-muted small font-w500">Total: <?= $total_records ?> webinars</p>
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
                    <h5 class="modal-title" id="webinarModalLabel">Webinar Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="modalForm">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="modal_edit_id">

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Task No</label>
                                <input type="text" name="taskno" id="modal_taskno" class="form-control" placeholder="e.g. WEB-SVU-2026-01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Webinar Title *</label>
                                <input type="text" name="title" id="modal_title" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Date &amp; Time *</label>
                                <input type="text" name="webinar_date" id="modal_webinar_date" class="form-control" placeholder="Select date &amp; time" autocomplete="off" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Speaker/Resource Person Name *</label>
                                <input type="text" name="speaker_name" id="modal_speaker_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Affiliation</label>
                                <input type="text" name="affiliation" id="modal_affiliation" class="form-control" placeholder="e.g. Dept of CS, IIT Bombay">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Keynote Speaker</label>
                                <input type="text" name="keynote_speaker" id="modal_keynote_speaker" class="form-control" placeholder="e.g. Prof. Tata Narasinga Rao">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Conveners</label>
                                <input type="text" name="conveners" id="modal_conveners" class="form-control" placeholder="e.g. Prof. G. Madhavi &amp; Prof. V.V.S.S. Srikanth">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registration / Recording Link</label>
                                <input type="url" name="link" id="modal_link" class="form-control" placeholder="https://zoom.us/... or registration link">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp Group Link</label>
                                <input type="url" name="whatsapp_link" id="modal_whatsapp_link" class="form-control" placeholder="https://chat.whatsapp.com/...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Official Email</label>
                                <input type="email" name="official_email" id="modal_official_email" class="form-control" placeholder="anrfpairsvu@gmail.com">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="contact_phone" id="modal_contact_phone" class="form-control" placeholder="e.g. 91 94400 96500">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Poster Image Path</label>
                                <input type="text" name="image" id="modal_image" class="form-control" placeholder="uploads/webinars/poster.jpg">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label class="form-label">Resource Persons</label>
                                <textarea name="resource_persons" id="modal_resource_persons" rows="2" class="form-control" placeholder="List of resource persons..."></textarea>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Publish Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="publish_status" id="modal_publish_status" value="1" checked>
                                    <label class="form-check-label text-dark font-w600" for="modal_publish_status">Publish immediately</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Brief Description</label>
                                <textarea name="description" id="modal_description" rows="3" class="form-control" placeholder="Brief outline of topics covered..."></textarea>
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
            <p>Copyright &copy; Designed &amp; Developed by <a href="https://bhimavaramdigitals.com/" target="_blank">Bhimavaram Digitals</a> 2026</p>
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
            document.getElementById('modal_publish_status').checked = true;
            modalTitle.innerText = "Add Webinar";
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
                modalTitle.innerText = "View Webinar Info";
                modalSubmitBtn.style.display = "none";
                modalForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = true;
                    el.readOnly = true;
                });
            } else {
                modalTitle.innerText = "Edit Webinar Info";
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

            const dateVal = this.getAttribute('data-date');
            if (dateVal) {
                webinarDatePicker.setDate(dateVal, true);
            } else {
                webinarDatePicker.clear();
            }

            document.getElementById('modal_speaker_name').value = this.getAttribute('data-speaker_name');
            document.getElementById('modal_affiliation').value = this.getAttribute('data-affiliation');
            document.getElementById('modal_link').value = this.getAttribute('data-link');
            document.getElementById('modal_whatsapp_link').value = this.getAttribute('data-whatsapp_link') || '';
            document.getElementById('modal_keynote_speaker').value = this.getAttribute('data-keynote_speaker') || '';
            document.getElementById('modal_resource_persons').value = this.getAttribute('data-resource_persons') || '';
            document.getElementById('modal_conveners').value = this.getAttribute('data-conveners') || '';
            document.getElementById('modal_official_email').value = this.getAttribute('data-official_email') || '';
            document.getElementById('modal_contact_phone').value = this.getAttribute('data-contact_phone') || '';
            document.getElementById('modal_image').value = this.getAttribute('data-image') || '';
            document.getElementById('modal_description').value = this.getAttribute('data-description');
            
            const pubStatus = this.getAttribute('data-publish_status');
            document.getElementById('modal_publish_status').checked = (pubStatus == 1);
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