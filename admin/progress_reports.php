<?php
require_once 'auth_check.php';
require_once 'role_access.php';
require_once 'config/db.php';

// Step 1: Resolve Institute Context using existing Admin Portal mechanism
$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact administrator.');
}

$table       = "{$prefix}_progress_reports";
$pubsTable   = "{$prefix}_progress_report_publications";
$eventsTable = "{$prefix}_progress_report_capacity_events";

$success_msg = '';
$error_msg   = '';

// Handle Flash Messages
if (isset($_GET['success_msg'])) {
    $msgType = $_GET['success_msg'];
    if ($msgType === 'submitted') {
        $success_msg = 'Progress report changes submitted successfully for Hub Admin approval.';
    } elseif ($msgType === 'updated' || $msgType === 'inserted') {
        $success_msg = 'Progress report project context saved successfully.';
    } elseif ($msgType === 'pub_saved') {
        $success_msg = 'Publication details updated successfully.';
    } elseif ($msgType === 'event_saved') {
        $success_msg = 'Capacity building event record saved successfully.';
    } elseif ($msgType === 'interns_updated') {
        $success_msg = 'Number of interns trained updated successfully.';
    } elseif ($msgType === 'deleted') {
        $success_msg = 'Record deleted successfully.';
    }
}

// ----------------------------------------------------------------------------
// POST FORM SUBMISSION HANDLER (WITH CSRF & STRICT SERVER AUTHORIZATION)
// ----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $formType = $_POST['form_type'] ?? '';

    if (!canEditInstitute($prefix)) {
        $error_msg = 'Unauthorized operation: You do not have permission to modify data for this institute.';
    } else {
        try {
            $is_super = isSuperAdmin();
            $approvalStatus = $is_super ? 'Approved' : 'Pending';

            // A. MAIN PROGRESS REPORT FORM (Create or Update Task Context)
            if ($formType === 'save_main_report') {
                $task_no          = trim($_POST['task_no'] ?? '');
                $project_title    = trim($_POST['project_title'] ?? '');
                $pi_name          = trim($_POST['pi_name'] ?? '');
                $co_pi_name       = trim($_POST['co_pi_name'] ?? '');
                $work_package_no  = trim($_POST['work_package_no'] ?? '');
                $approved_objects = trim($_POST['approved_objects'] ?? '');
                $methodology      = trim($_POST['methodology'] ?? '');
                $summary_progress = trim($_POST['summary_progress'] ?? '');
                $interns_count    = isset($_POST['interns_trained_count']) ? (int)$_POST['interns_trained_count'] : 0;
                $edit_id          = !empty($_POST['report_id']) ? (int)$_POST['report_id'] : null;

                if (empty($task_no)) {
                    throw new RuntimeException("Task ID is required.");
                }
                if (empty($project_title)) {
                    throw new RuntimeException("Project Title is required.");
                }

                if ($edit_id) {
                    // Verify report_id belongs to current institute table
                    $chk = $pdo->prepare("SELECT id FROM `$table` WHERE id = ?");
                    $chk->execute([$edit_id]);
                    if (!$chk->fetch()) {
                        throw new RuntimeException("Progress Report record #{$edit_id} not found for this institute.");
                    }

                    $stmt = $pdo->prepare("
                        UPDATE `$table` SET
                            task_no = :task_no,
                            project_title = :project_title,
                            pi_name = :pi_name,
                            co_pi_name = :co_pi_name,
                            work_package_no = :work_package_no,
                            approved_objects = :approved_objects,
                            methodology = :methodology,
                            summary_progress = :summary_progress,
                            interns_trained_count = :interns_trained_count,
                            approval_status = :approval_status
                        WHERE id = :id
                    ");
                    $payload = [
                        ':task_no'               => $task_no,
                        ':project_title'         => $project_title,
                        ':pi_name'               => $pi_name,
                        ':co_pi_name'            => $co_pi_name,
                        ':work_package_no'       => $work_package_no,
                        ':approved_objects'      => $approved_objects,
                        ':methodology'           => $methodology,
                        ':summary_progress'      => $summary_progress,
                        ':interns_trained_count' => $interns_count,
                        ':approval_status'       => $approvalStatus,
                        ':id'                    => $edit_id
                    ];
                    $stmt->execute($payload);

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $edit_id, 'UPDATE', $payload);
                        adminRedirect(['success_msg' => 'submitted', 'task_no' => $task_no, 'report_id' => $edit_id]);
                    } else {
                        adminRedirect(['success_msg' => 'updated', 'task_no' => $task_no, 'report_id' => $edit_id]);
                    }
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO `$table`
                            (task_no, project_title, pi_name, co_pi_name, work_package_no,
                             approved_objects, methodology, summary_progress, interns_trained_count, approval_status, created_at)
                        VALUES
                            (:task_no, :project_title, :pi_name, :co_pi_name, :work_package_no,
                             :approved_objects, :methodology, :summary_progress, :interns_trained_count, :approval_status, NOW())
                    ");
                    $payload = [
                        ':task_no'               => $task_no,
                        ':project_title'         => $project_title,
                        ':pi_name'               => $pi_name,
                        ':co_pi_name'            => $co_pi_name,
                        ':work_package_no'       => $work_package_no,
                        ':approved_objects'      => $approved_objects,
                        ':methodology'           => $methodology,
                        ':summary_progress'      => $summary_progress,
                        ':interns_trained_count' => $interns_count,
                        ':approval_status'       => $approvalStatus
                    ];
                    $stmt->execute($payload);
                    $new_id = $pdo->lastInsertId();

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $new_id, 'CREATE', $payload);
                        adminRedirect(['success_msg' => 'submitted', 'task_no' => $task_no, 'report_id' => $new_id]);
                    } else {
                        adminRedirect(['success_msg' => 'inserted', 'task_no' => $task_no, 'report_id' => $new_id]);
                    }
                }
            }

            // B. CAPACITY BUILDING EVENT SUBMISSION
            elseif ($formType === 'save_capacity_event') {
                $pr_id                  = (int)($_POST['progress_report_id'] ?? 0);
                $task_no                = trim($_POST['task_no'] ?? '');
                $event_id               = !empty($_POST['event_id']) ? (int)$_POST['event_id'] : null;
                $category               = in_array($_POST['category'] ?? '', ['Workshop_Conference', 'Training_Program']) ? $_POST['category'] : 'Workshop_Conference';
                $event_title            = trim($_POST['event_title'] ?? '');
                $event_date             = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
                $duration               = trim($_POST['duration'] ?? '');
                $venue_mode             = trim($_POST['venue_mode'] ?? '');
                $organizing_institution = trim($_POST['organizing_institution'] ?? '');
                $participant_input      = $_POST['participant_count'] ?? '0';
                $description            = trim($_POST['description'] ?? '');

                if ($pr_id <= 0) throw new RuntimeException("Invalid project context.");
                if (empty($event_title)) throw new RuntimeException("Event title is required.");
                if ($participant_input < 0 || !filter_var($participant_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
                    throw new RuntimeException("Participant Count must be a valid non-negative integer.");
                }
                $participant_count = (int)$participant_input;

                // Validate parent progress_report_id belongs to current institute table
                $chkPr = $pdo->prepare("SELECT id, task_no FROM `$table` WHERE id = ?");
                $chkPr->execute([$pr_id]);
                $prRow = $chkPr->fetch(PDO::FETCH_ASSOC);
                if (!$prRow) {
                    throw new RuntimeException("Invalid Progress Report ID #{$pr_id} for this institute.");
                }
                $task_no = $prRow['task_no'];

                $hasAppCol = false;
                try {
                    $chkCol = $pdo->query("SHOW COLUMNS FROM `$eventsTable` LIKE 'approval_status'");
                    if ($chkCol && $chkCol->rowCount() > 0) $hasAppCol = true;
                } catch (Exception $ex) {}

                $payload = [
                    ':category'               => $category,
                    ':title'                  => $event_title,
                    ':event_date'             => $event_date,
                    ':duration'               => $duration,
                    ':venue_mode'             => $venue_mode,
                    ':organizing_institution' => $organizing_institution,
                    ':participant_count'      => $participant_count,
                    ':description'            => $description
                ];
                if ($hasAppCol) {
                    $payload[':approval_status'] = $approvalStatus;
                }

                if ($event_id) {
                    $sql = "UPDATE `$eventsTable` SET
                                category = :category,
                                title = :title,
                                event_date = :event_date,
                                duration = :duration,
                                venue_mode = :venue_mode,
                                organizing_institution = :organizing_institution,
                                participant_count = :participant_count,
                                description = :description" . ($hasAppCol ? ", approval_status = :approval_status" : "") . "
                            WHERE id = :id AND progress_report_id = :pr_id";
                    $stmt = $pdo->prepare($sql);
                    $payload[':id']    = $event_id;
                    $payload[':pr_id'] = $pr_id;
                    $stmt->execute($payload);

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Progress Report Capacity Events', $eventsTable, $prefix, $event_id, 'UPDATE', $payload);
                        adminRedirect(['success_msg' => 'submitted', 'task_no' => $task_no, 'report_id' => $pr_id]);
                    } else {
                        adminRedirect(['success_msg' => 'event_saved', 'task_no' => $task_no, 'report_id' => $pr_id]);
                    }
                } else {
                    $cols = "progress_report_id, category, title, event_date, duration, venue_mode, organizing_institution, participant_count, description" . ($hasAppCol ? ", approval_status" : "");
                    $vals = ":pr_id, :category, :title, :event_date, :duration, :venue_mode, :organizing_institution, :participant_count, :description" . ($hasAppCol ? ", :approval_status" : "");
                    
                    $stmt = $pdo->prepare("INSERT INTO `$eventsTable` ($cols) VALUES ($vals)");
                    $payload[':pr_id'] = $pr_id;
                    $stmt->execute($payload);
                    $new_event_id = $pdo->lastInsertId();

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Progress Report Capacity Events', $eventsTable, $prefix, $new_event_id, 'CREATE', $payload);
                        adminRedirect(['success_msg' => 'submitted', 'task_no' => $task_no, 'report_id' => $pr_id]);
                    } else {
                        adminRedirect(['success_msg' => 'event_saved', 'task_no' => $task_no, 'report_id' => $pr_id]);
                    }
                }
            }

            // C. DESTRUCTIVE ACTIONS (SECURE POST DELETE HANDLERS)
            elseif ($formType === 'delete_main_report') {
                $del_id  = (int)($_POST['delete_id'] ?? 0);
                if ($del_id > 0) {
                    $chk = $pdo->prepare("SELECT id FROM `$table` WHERE id = ?");
                    $chk->execute([$del_id]);
                    if ($chk->fetch()) {
                        $pdo->prepare("DELETE FROM `$table` WHERE id = ?")->execute([$del_id]);
                        $pdo->prepare("DELETE FROM `$pubsTable` WHERE progress_report_id = ?")->execute([$del_id]);
                        $pdo->prepare("DELETE FROM `$eventsTable` WHERE progress_report_id = ?")->execute([$del_id]);
                    }
                }
                adminRedirect(['success_msg' => 'deleted', 'task_no' => null]);
            }
            elseif ($formType === 'delete_capacity_event') {
                $del_id  = (int)($_POST['delete_id'] ?? 0);
                $pr_id   = (int)($_POST['progress_report_id'] ?? 0);
                if ($del_id > 0 && $pr_id > 0) {
                    $chkPr = $pdo->prepare("SELECT id, task_no FROM `$table` WHERE id = ?");
                    $chkPr->execute([$pr_id]);
                    $prRow = $chkPr->fetch(PDO::FETCH_ASSOC);
                    if ($prRow) {
                        $pdo->prepare("DELETE FROM `$eventsTable` WHERE id = ? AND progress_report_id = ?")->execute([$del_id, $pr_id]);
                        adminRedirect(['success_msg' => 'deleted', 'task_no' => $prRow['task_no'], 'report_id' => $pr_id]);
                    }
                }
                adminRedirect(['success_msg' => 'deleted']);
            }
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }
    }
}

// ----------------------------------------------------------------------------
// DATA RETRIEVAL FOR TASK IDs ACROSS ALL INSTITUTE MODULE TABLES (DYNAMIC & COLLATION-SAFE)
// ----------------------------------------------------------------------------
$availableTaskNos = [];
try {
    $unionParts = ["SELECT CONVERT(task_no USING utf8mb4) AS task_no FROM `$table` WHERE task_no IS NOT NULL AND task_no != ''"];

    $kpiTablesCols = [
        "{$prefix}_publications" => 'task_no',
        "{$prefix}_conferences"  => 'taskno',
        "{$prefix}_webinars"     => 'taskno',
        "{$prefix}_internships"  => 'task_no',
        "{$prefix}_patents"      => 'task_no'
    ];

    foreach ($kpiTablesCols as $tName => $colName) {
        try {
            $chkCol = $pdo->query("SHOW COLUMNS FROM `$tName` LIKE '$colName'");
            if ($chkCol && $chkCol->rowCount() > 0) {
                $unionParts[] = "SELECT CONVERT($colName USING utf8mb4) AS task_no FROM `$tName` WHERE $colName IS NOT NULL AND $colName != ''";
            }
        } catch (Exception $ex) {}
    }

    $sqlUnion = implode(" UNION ", $unionParts) . " ORDER BY task_no ASC";
    $stmtTasks = $pdo->query($sqlUnion);
    $availableTaskNos = $stmtTasks->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    try {
        $stmtTasks = $pdo->query("SELECT DISTINCT task_no FROM `$table` WHERE task_no IS NOT NULL AND task_no != '' ORDER BY task_no ASC");
        $availableTaskNos = $stmtTasks->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $ex) {}
}

// Resolve selected Task ID
$reqTaskNo = $_GET['task_no'] ?? null;
$reqReportId = isset($_GET['report_id']) ? (int)$_GET['report_id'] : null;

$activeTaskNo = null;
if (!empty($reqTaskNo) && $reqTaskNo !== '__new__') {
    if (in_array((string)$reqTaskNo, array_map('strval', $availableTaskNos), true)) {
        $activeTaskNo = (string)$reqTaskNo;
    }
}

if (!$activeTaskNo && !empty($availableTaskNos) && $reqTaskNo !== '__new__') {
    $activeTaskNo = (string)$availableTaskNos[0];
}

// Fetch all report instances for the activeTaskNo to safely handle historical duplicate Task IDs
$taskInstances = [];
$activeTask    = null;

if ($activeTaskNo) {
    try {
        $stmtInst = $pdo->prepare("SELECT * FROM `$table` WHERE task_no = :task_no ORDER BY id DESC");
        $stmtInst->execute([':task_no' => $activeTaskNo]);
        $taskInstances = $stmtInst->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($taskInstances)) {
            if ($reqReportId) {
                foreach ($taskInstances as $inst) {
                    if ((int)$inst['id'] === $reqReportId) {
                        $activeTask = $inst;
                        break;
                    }
                }
            }
            if (!$activeTask) {
                $activeTask = $taskInstances[0];
            }
        }
    } catch (Exception $e) {
        $activeTask = null;
    }
}

// ----------------------------------------------------------------------------
// AUTO-AGGREGATION OF EXISTING SYSTEM KPI RECORDS FOR ACTIVE TASK ID
// ----------------------------------------------------------------------------
$systemKpiData = [
    'publications' => [],
    'conferences'  => [],
    'webinars'     => [],
    'internships'  => [],
    'patents'      => []
];

$derivedInternCount = 0;
$hasInternshipRecords = false;

if ($activeTaskNo) {
    // 1. System Publications
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_publications` WHERE task_no = :t ORDER BY id DESC");
        $stmt->execute([':t' => $activeTaskNo]);
        $systemKpiData['publications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // 2. System Conferences
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_conferences` WHERE taskno = :t ORDER BY id DESC");
        $stmt->execute([':t' => $activeTaskNo]);
        $systemKpiData['conferences'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // 3. System Webinars
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_webinars` WHERE taskno = :t ORDER BY id DESC");
        $stmt->execute([':t' => $activeTaskNo]);
        $systemKpiData['webinars'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // 4. System Internships & Derivation of Interns Count
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_internships` WHERE task_no = :t ORDER BY id DESC");
        $stmt->execute([':t' => $activeTaskNo]);
        $systemKpiData['internships'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($systemKpiData['internships'])) {
            $hasInternshipRecords = true;
            foreach ($systemKpiData['internships'] as $internRow) {
                $derivedInternCount += (int)($internRow['no_students_trained'] ?? 1);
            }
        }
    } catch (Exception $e) {}

    // 5. System Patents
    try {
        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_patents` WHERE task_no = :t ORDER BY id DESC");
        $stmt->execute([':t' => $activeTaskNo]);
        $systemKpiData['patents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

if (!$hasInternshipRecords && $activeTask) {
    $derivedInternCount = (int)($activeTask['interns_trained_count'] ?? 0);
}

// Fetch report-specific capacity events if report exists
$taskWorkshops  = [];
$taskTrainings  = [];

if ($activeTask) {
    $activePrId = (int)$activeTask['id'];
    try {
        $stmtE = $pdo->prepare("SELECT * FROM `$eventsTable` WHERE progress_report_id = :pr_id ORDER BY id DESC");
        $stmtE->execute([':pr_id' => $activePrId]);
        $allEv = $stmtE->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allEv as $ev) {
            if ($ev['category'] === 'Workshop_Conference') {
                $taskWorkshops[] = $ev;
            } else {
                $taskTrainings[] = $ev;
            }
        }
    } catch (Exception $ex) {}
}

$isNewTaskSelected = ($reqTaskNo === '__new__');
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
    .workflow-card {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        background: #ffffff !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02) !important;
    }
    .project-banner-card {
        background: linear-gradient(135deg, #024283 0%, #0f172a 100%) !important;
        color: #ffffff !important;
        border-radius: 10px;
        padding: 20px;
    }
    .btn-sapphire {
        background-color: #024283 !important;
        border-color: #024283 !important;
        color: #ffffff !important;
        font-weight: 600;
    }
    .btn-sapphire:hover {
        background-color: #012a55 !important;
    }
    .multi-instance-alert {
        background-color: #fefce8;
        border: 1px solid #fef08a;
        border-radius: 8px;
        padding: 10px 14px;
    }
    .kpi-section-title {
        border-left: 4px solid #024283;
        padding-left: 10px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
    }
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <!-- Canonical Institute Context Banner & Switcher (Reused from existing Admin Portal) -->
            <?php include 'institute_banner.php'; ?>

            <!-- Page Breadcrumb -->
            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Progress Reports</a></li>
                </ol>
            </div>

            <!-- Alerts -->
            <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i> <strong>Success!</strong> <?= htmlspecialchars($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-triangle me-2"></i> <strong>Error:</strong> <?= htmlspecialchars($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- =========================================================================
                 STEP 1 — TASK ID SELECTION & PROJECT CONTEXT BANNER
                 ========================================================================= -->
            <div class="card workflow-card mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label font-w700 text-uppercase text-muted mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                Task ID Selection
                            </label>
                            <h6 class="mb-0 font-w700 text-dark">Business Identifier:</h6>
                        </div>
                        <div class="col-md-9">
                            <select id="task_id_selector" class="form-select font-w700 text-dark" style="max-width: 500px; border-color: #024283;" onchange="switchTaskId(this.value)">
                                <?php if (empty($availableTaskNos)): ?>
                                    <option value="__new__" selected>+ Create New Task ID / Project Context</option>
                                <?php else: ?>
                                    <?php foreach ($availableTaskNos as $tNo): ?>
                                        <option value="<?= htmlspecialchars((string)$tNo) ?>" <?= ((string)$activeTaskNo === (string)$tNo && !$isNewTaskSelected) ? 'selected' : '' ?>>
                                            Task ID: <?= htmlspecialchars((string)$tNo) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__new__" <?= $isNewTaskSelected ? 'selected' : '' ?>>+ Create New Task ID / Project Context</option>
                                <?php endif; ?>
                            </select>

                            <!-- Multi-Instance Handling for Historical Duplicate Task IDs -->
                            <?php if (count($taskInstances) > 1): ?>
                            <div class="multi-instance-alert mt-3 d-flex align-items-center flex-wrap gap-2">
                                <span class="font-w700 text-dark fs-13"><i class="fa fa-history text-warning me-1"></i> Historical Reports for Task ID '<?= htmlspecialchars($activeTaskNo) ?>':</span>
                                <select class="form-select form-select-sm font-w700 text-dark" style="max-width: 420px; border-color: #eab308;" onchange="switchReportInstance(this.value)">
                                    <?php foreach ($taskInstances as $inst): ?>
                                        <option value="<?= $inst['id'] ?>" <?= ($activeTask && (int)$activeTask['id'] === (int)$inst['id']) ? 'selected' : '' ?>>
                                            Report #<?= $inst['id'] ?> — <?= date('d M Y', strtotime($inst['created_at'])) ?> [<?= htmlspecialchars($inst['approval_status'] ?: 'Approved') ?>] — <?= htmlspecialchars($inst['project_title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($activeTask && !$isNewTaskSelected): ?>
                        <!-- Active Project Summary Banner -->
                        <div class="project-banner-card mt-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-warning text-dark font-w700">
                                            <i class="fa fa-tag me-1"></i> TASK ID: <?= htmlspecialchars($activeTask['task_no']) ?>
                                        </span>
                                        <span class="badge bg-light text-dark font-w600">
                                            Report ID #<?= (int)$activeTask['id'] ?>
                                        </span>
                                        <span class="badge <?= ($activeTask['approval_status'] === 'Approved' ? 'bg-success' : ($activeTask['approval_status'] === 'Rejected' ? 'bg-danger' : 'bg-secondary')) ?> text-white font-w600">
                                            <?= htmlspecialchars($activeTask['approval_status'] ?: 'Approved') ?>
                                        </span>
                                    </div>
                                    <h4 class="text-white font-w700 mb-2"><?= htmlspecialchars($activeTask['project_title']) ?></h4>
                                    <div class="d-flex flex-wrap gap-4 text-white-50 fs-14">
                                        <span><strong class="text-white">PI:</strong> <?= htmlspecialchars($activeTask['pi_name'] ?: '—') ?></span>
                                        <span><strong class="text-white">Co-PI:</strong> <?= htmlspecialchars($activeTask['co_pi_name'] ?: '—') ?></span>
                                        <span><strong class="text-white">Work Package:</strong> <?= htmlspecialchars($activeTask['work_package_no'] ?: '—') ?></span>
                                        <span><strong class="text-white">Interns Trained:</strong> <?= $derivedInternCount ?> <?= $hasInternshipRecords ? '(Derived from KPI records)' : '(Progress Report Summary)' ?></span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?= buildNavUrl('export_progress_report_pdf.php?id=' . $activeTask['id'] . '&prefix=' . $prefix) ?>" target="_blank" class="btn btn-light btn-sm font-w700 text-dark">
                                        <i class="fa fa-file-pdf-o text-danger me-1"></i> Export PDF Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($activeTaskNo && !$activeTask): ?>
                        <!-- Notice when Task ID has no Progress Report entry yet -->
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fa fa-info-circle me-2"></i> No Progress Report entry exists yet for Task ID <strong>'<?= htmlspecialchars($activeTaskNo) ?>'</strong>. Complete the Progress Report form below to create one.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- =========================================================================
                 MODULE 1: PROGRESS REPORT FORM (NARRATIVE & PROJECT SPECIFIC DATA)
                 ========================================================================= -->
            <div id="sec_progress_report" class="card workflow-card mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 text-white font-w700"><i class="fa fa-file-text-o me-2"></i>Progress Report Module</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>">
                        <?= getCsrfInputField() ?>
                        <input type="hidden" name="form_type" value="save_main_report">
                        <input type="hidden" name="report_id" value="<?= htmlspecialchars($activeTask['id'] ?? '') ?>">

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label font-w700 text-dark">Task ID *</label>
                                <input type="text" name="task_no" class="form-control font-w700" value="<?= htmlspecialchars($activeTask['task_no'] ?? ($activeTaskNo ?: ($reqTaskNo === '__new__' ? '' : ''))) ?>" placeholder="e.g. CUK-PAIR-001" required <?= ($activeTask && !$isNewTaskSelected) ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label font-w700 text-dark">Project Title *</label>
                                <input type="text" name="project_title" class="form-control" value="<?= htmlspecialchars($activeTask['project_title'] ?? '') ?>" placeholder="Full title of the research project" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-w600 text-dark">Principal Investigator (PI)</label>
                                <input type="text" name="pi_name" class="form-control" value="<?= htmlspecialchars($activeTask['pi_name'] ?? '') ?>" placeholder="PI Name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-w600 text-dark">Co-Principal Investigator (Co-PI)</label>
                                <input type="text" name="co_pi_name" class="form-control" value="<?= htmlspecialchars($activeTask['co_pi_name'] ?? '') ?>" placeholder="Co-PI Name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-w600 text-dark">Work Package Number</label>
                                <input type="text" name="work_package_no" class="form-control" value="<?= htmlspecialchars($activeTask['work_package_no'] ?? '') ?>" placeholder="Work Package No">
                            </div>

                            <div class="col-12">
                                <label class="form-label font-w600 text-dark">Approved Objectives / Targets</label>
                                <textarea name="approved_objects" class="form-control" rows="3" placeholder="Specify approved objectives..."><?= htmlspecialchars($activeTask['approved_objects'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-w600 text-dark">Methodology / Approach Used</label>
                                <textarea name="methodology" class="form-control" rows="3" placeholder="Describe technical methodology..."><?= htmlspecialchars($activeTask['methodology'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-w600 text-dark">Summary of Progress</label>
                                <textarea name="summary_progress" class="form-control" rows="4" placeholder="Detailed progress report summary..."><?= htmlspecialchars($activeTask['summary_progress'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label font-w700 text-dark">Number of Interns Trained</label>
                                <?php if ($hasInternshipRecords): ?>
                                    <input type="text" class="form-control font-w700 text-dark bg-light" value="<?= $derivedInternCount ?> (Derived automatically from Internship KPI Module records)" readonly>
                                    <input type="hidden" name="interns_trained_count" value="<?= $derivedInternCount ?>">
                                    <small class="text-muted"><i class="fa fa-info-circle"></i> Auto-calculated from <?= count($systemKpiData['internships']) ?> internship record(s) in main Internship module for Task ID '<?= htmlspecialchars($activeTaskNo) ?>'.</small>
                                <?php else: ?>
                                    <input type="number" min="0" name="interns_trained_count" class="form-control font-w700 text-dark" value="<?= (int)($activeTask['interns_trained_count'] ?? 0) ?>">
                                    <small class="text-muted"><i class="fa fa-pencil me-1"></i> Progress Report-specific summary value (no individual internship records currently filed in main Internship KPI module for Task ID '<?= htmlspecialchars($activeTaskNo ?: '—') ?>').</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (canEditInstitute($prefix)): ?>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-sapphire px-4 py-2">
                                <i class="fa fa-save me-1"></i> Save Progress Report
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- =========================================================================
                 MODULE 2: EXISTING SYSTEM KPI RECORDS (AUTO-AGGREGATED FROM MAIN MODULES)
                 ========================================================================= -->
            <div class="card workflow-card mb-4">
                <div class="card-header bg-light py-3 border-0">
                    <h5 class="mb-0 font-w700 text-dark">
                        <i class="fa fa-database text-primary me-2"></i>Associated KPI Records for Task ID '<?= htmlspecialchars($activeTaskNo ?: '—') ?>'
                    </h5>
                    <small class="text-muted">Auto-aggregated KPI records linked to this Task ID across the ANRF–PAIR system.</small>
                </div>
                <div class="card-body p-4">

                    <!-- A. PUBLICATIONS -->
                    <h6 class="kpi-section-title"><i class="fa fa-book text-success me-2"></i>Publications (<?= count($systemKpiData['publications']) ?>)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Author(s)</th>
                                    <th>Journal</th>
                                    <th>DOI / Date</th>
                                    <th>Impact Factor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($systemKpiData['publications'])): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-3">No publication records found in main KPI module for Task ID '<?= htmlspecialchars($activeTaskNo ?: '—') ?>'.</td></tr>
                                <?php else: ?>
                                    <?php $pNo = 1; foreach ($systemKpiData['publications'] as $pub): ?>
                                        <tr>
                                            <td><?= $pNo++ ?></td>
                                            <td><strong class="text-dark"><?= htmlspecialchars($pub['publication_title']) ?></strong></td>
                                            <td><?= htmlspecialchars($pub['author_name']) ?></td>
                                            <td><?= htmlspecialchars($pub['publication_journal']) ?></td>
                                            <td><?= $pub['doi_number'] ? 'DOI: '.htmlspecialchars($pub['doi_number']) : '—' ?><br><small class="text-muted"><?= htmlspecialchars($pub['publication_date'] ?? '') ?></small></td>
                                            <td><span class="badge bg-light text-dark border"><?= $pub['impact_factor'] !== null ? $pub['impact_factor'] : '—' ?></span></td>
                                            <td><span class="badge bg-success text-white"><?= htmlspecialchars($pub['approval_status'] ?? 'Approved') ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- B. CONFERENCES -->
                    <h6 class="kpi-section-title"><i class="fa fa-users text-info me-2"></i>Conferences Conducted (<?= count($systemKpiData['conferences']) ?>)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Conference Title</th>
                                    <th>Date / Duration</th>
                                    <th>Venue / Mode</th>
                                    <th>Organizing Dept</th>
                                    <th>Participants</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($systemKpiData['conferences'])): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">No conference records found in main KPI module for Task ID '<?= htmlspecialchars($activeTaskNo ?: '—') ?>'.</td></tr>
                                <?php else: ?>
                                    <?php $cNo = 1; foreach ($systemKpiData['conferences'] as $conf): ?>
                                        <tr>
                                            <td><?= $cNo++ ?></td>
                                            <td><strong class="text-dark"><?= htmlspecialchars($conf['title'] ?? $conf['conference_name'] ?? '—') ?></strong></td>
                                            <td><?= htmlspecialchars($conf['event_date'] ?? $conf['conference_date'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($conf['venue_mode'] ?? $conf['venue'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($conf['organizing_dept'] ?? '—') ?></td>
                                            <td><span class="badge bg-info text-white"><?= (int)($conf['participant_count'] ?? 0) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- C. WEBINARS -->
                    <h6 class="kpi-section-title"><i class="fa fa-video-camera text-purple me-2"></i>Webinars (<?= count($systemKpiData['webinars']) ?>)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Webinar Title</th>
                                    <th>Speaker / Expert</th>
                                    <th>Date</th>
                                    <th>Participants</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($systemKpiData['webinars'])): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No webinar records found in main KPI module for Task ID '<?= htmlspecialchars($activeTaskNo ?: '—') ?>'.</td></tr>
                                <?php else: ?>
                                    <?php $wNo = 1; foreach ($systemKpiData['webinars'] as $web): ?>
                                        <tr>
                                            <td><?= $wNo++ ?></td>
                                            <td><strong class="text-dark"><?= htmlspecialchars($web['title'] ?? $web['webinar_title'] ?? '—') ?></strong></td>
                                            <td><?= htmlspecialchars($web['speaker_name'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($web['webinar_date'] ?? $web['event_date'] ?? '—') ?></td>
                                            <td><span class="badge bg-purple text-white"><?= (int)($web['participant_count'] ?? 0) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- D. INTERNSHIPS -->
                    <h6 class="kpi-section-title"><i class="fa fa-user-graduate text-warning me-2"></i>Internships (<?= count($systemKpiData['internships']) ?>)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Internship Title</th>
                                    <th>Investigator</th>
                                    <th>Students Trained</th>
                                    <th>Days Trained</th>
                                    <th>Student Names</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($systemKpiData['internships'])): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-3">No internship records found in main KPI module for Task ID '<?= htmlspecialchars($activeTaskNo ?: '—') ?>'.</td></tr>
                                <?php else: ?>
                                    <?php $iNo = 1; foreach ($systemKpiData['internships'] as $intern): ?>
                                        <tr>
                                            <td><?= $iNo++ ?></td>
                                            <td><strong class="text-dark"><?= htmlspecialchars($intern['title'] ?? '—') ?></strong></td>
                                            <td><?= htmlspecialchars($intern['project_investigator'] ?? '—') ?></td>
                                            <td><span class="badge bg-warning text-dark"><?= (int)($intern['no_students_trained'] ?? 1) ?></span></td>
                                            <td><?= htmlspecialchars($intern['no_days_trained'] ?? '—') ?> days</td>
                                            <td><small class="text-dark"><?= htmlspecialchars($intern['students_names'] ?? '—') ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- E. PATENTS -->
                    <h6 class="kpi-section-title"><i class="fa fa-certificate text-danger me-2"></i>Patents (<?= count($systemKpiData['patents']) ?>)</h6>
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Patent Title</th>
                                    <th>Application No</th>
                                    <th>Filing Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($systemKpiData['patents'])): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No patent records found in main KPI module for Task ID '<?= htmlspecialchars($activeTaskNo ?: '—') ?>'.</td></tr>
                                <?php else: ?>
                                    <?php $ptNo = 1; foreach ($systemKpiData['patents'] as $pat): ?>
                                        <tr>
                                            <td><?= $ptNo++ ?></td>
                                            <td><strong class="text-dark"><?= htmlspecialchars($pat['title'] ?? $pat['patent_title'] ?? '—') ?></strong></td>
                                            <td><?= htmlspecialchars($pat['application_no'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($pat['filing_date'] ?? '—') ?></td>
                                            <td><span class="badge bg-success text-white"><?= htmlspecialchars($pat['status'] ?? 'Filed') ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- =========================================================================
                 MODULE 3: REPORT-SPECIFIC CAPACITY BUILDING ATTACHMENTS
                 ========================================================================= -->
            <?php if ($activeTask): ?>
            <div class="card workflow-card mb-4">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white font-w700"><i class="fa fa-graduation-cap me-2"></i>Report-Specific Capacity Building Events</h5>
                    <?php if (canEditInstitute($prefix)): ?>
                    <button type="button" class="btn btn-light btn-sm font-w700" onclick="openAddEventModal('Workshop_Conference')">
                        <i class="fa fa-plus text-dark me-1"></i> Add Event
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Title</th>
                                    <th>Date / Duration</th>
                                    <th>Venue / Mode</th>
                                    <th>Participants</th>
                                    <th style="width: 100px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $allEvts = array_merge($taskWorkshops, $taskTrainings); ?>
                                <?php if (empty($allEvts)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-3">No report-specific capacity events recorded for Report #<?= (int)$activeTask['id'] ?>.</td></tr>
                                <?php else: ?>
                                    <?php $eNo = 1; foreach ($allEvts as $ev): ?>
                                        <tr>
                                            <td><?= $eNo++ ?></td>
                                            <td><span class="badge bg-secondary text-white"><?= htmlspecialchars(str_replace('_', ' / ', $ev['category'])) ?></span></td>
                                            <td><strong class="text-dark"><?= htmlspecialchars($ev['title']) ?></strong></td>
                                            <td><?= htmlspecialchars($ev['event_date'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($ev['venue_mode'] ?? '—') ?></td>
                                            <td><span class="badge bg-info text-white"><?= (int)$ev['participant_count'] ?></span></td>
                                            <td style="text-align: center;">
                                                <?php if (canEditInstitute($prefix)): ?>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-warning btn-xs" onclick='openEditEventModal(<?= json_encode($ev, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>" onsubmit="return confirm('Delete this event?');" style="display:inline;">
                                                        <?= getCsrfInputField() ?>
                                                        <input type="hidden" name="form_type" value="delete_capacity_event">
                                                        <input type="hidden" name="delete_id" value="<?= $ev['id'] ?>">
                                                        <input type="hidden" name="task_no" value="<?= htmlspecialchars($activeTask['task_no']) ?>">
                                                        <input type="hidden" name="progress_report_id" value="<?= (int)$activeTask['id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </div>
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
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL: CAPACITY BUILDING EVENT FORM
     ========================================================================= -->
<div class="modal fade" id="capacityEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>">
                <?= getCsrfInputField() ?>
                <input type="hidden" name="form_type" value="save_capacity_event">
                <input type="hidden" name="progress_report_id" id="modal_evt_pr_id" value="<?= htmlspecialchars($activeTask['id'] ?? '') ?>">
                <input type="hidden" name="task_no" id="modal_evt_task_no" value="<?= htmlspecialchars($activeTask['task_no'] ?? '') ?>">
                <input type="hidden" name="event_id" id="modal_evt_id" value="">

                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white font-w700" id="evtModalTitle">Capacity Building Event</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-w700 text-dark">Category *</label>
                            <select name="category" id="modal_evt_category" class="form-select font-w600 text-dark" required>
                                <option value="Workshop_Conference">Workshop / Conference</option>
                                <option value="Training_Program">Training Program</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-w700 text-dark" id="evtTitleLabel">Event Title *</label>
                            <input type="text" name="event_title" id="modal_evt_title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-w600 text-dark">Event Date</label>
                            <input type="date" name="event_date" id="modal_evt_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-w600 text-dark">Duration / Timings</label>
                            <input type="text" name="duration" id="modal_evt_duration" class="form-control" placeholder="e.g. 2 Days / 10 AM - 4 PM">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-w600 text-dark">Participant Count</label>
                            <input type="number" min="0" name="participant_count" id="modal_evt_participants" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-w600 text-dark">Venue / Mode</label>
                            <input type="text" name="venue_mode" id="modal_evt_venue" class="form-control" placeholder="e.g. Online / Main Auditorium">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-w600 text-dark">Organizing Institution</label>
                            <input type="text" name="organizing_institution" id="modal_evt_organizer" class="form-control" placeholder="Organizing body/department">
                        </div>
                        <div class="col-12">
                            <label class="form-label font-w600 text-dark">Description / Outcome</label>
                            <textarea name="description" id="modal_evt_desc" class="form-control" rows="3" placeholder="Brief summary or outcome..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white"><i class="fa fa-save me-1"></i> Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let capacityEventModalInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    capacityEventModalInstance = new bootstrap.Modal(document.getElementById('capacityEventModal'));
});

function switchTaskId(taskNoVal) {
    let urlParams = new URLSearchParams(window.location.search);
    urlParams.set('task_no', taskNoVal);
    urlParams.delete('report_id');
    window.location.search = urlParams.toString();
}

function switchReportInstance(reportIdVal) {
    let urlParams = new URLSearchParams(window.location.search);
    urlParams.set('report_id', reportIdVal);
    window.location.search = urlParams.toString();
}

function openAddEventModal(cat) {
    document.getElementById('modal_evt_id').value = '';
    document.getElementById('modal_evt_category').value = cat || 'Workshop_Conference';
    document.getElementById('modal_evt_title').value = '';
    document.getElementById('modal_evt_date').value = '';
    document.getElementById('modal_evt_duration').value = '';
    document.getElementById('modal_evt_participants').value = '0';
    document.getElementById('modal_evt_venue').value = '';
    document.getElementById('modal_evt_organizer').value = '';
    document.getElementById('modal_evt_desc').value = '';
    capacityEventModalInstance.show();
}

function openEditEventModal(e) {
    document.getElementById('modal_evt_id').value = e.id;
    document.getElementById('modal_evt_category').value = e.category || 'Workshop_Conference';
    document.getElementById('modal_evt_title').value = e.title || '';
    document.getElementById('modal_evt_date').value = e.event_date || '';
    document.getElementById('modal_evt_duration').value = e.duration || '';
    document.getElementById('modal_evt_participants').value = e.participant_count || 0;
    document.getElementById('modal_evt_venue').value = e.venue_mode || '';
    document.getElementById('modal_evt_organizer').value = e.organizing_institution || '';
    document.getElementById('modal_evt_desc').value = e.description || '';
    capacityEventModalInstance.show();
}
</script>
</body>
</html>
