<?php
// Multi-Tab Server-Side Session Isolation Logic
$requestedTabToken = $_REQUEST['tab_token'] ?? $_SERVER['HTTP_X_TAB_TOKEN'] ?? null;
if (!empty($requestedTabToken) && preg_match('/^[a-f0-9]{64}$/i', $requestedTabToken)) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_id($requestedTabToken);
    session_start();
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth_check.php';
require_once 'role_access.php';
require_once 'config/db.php';
require_once 'includes/progress_reports_helper.php';

global $pdo;

// 1. UNIVERSITY CONTEXT & AUTHENTICATION GUARD
$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);
$allowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

if (!in_array($prefix, $allowedPrefixes, true) || !canEditInstitute($prefix)) {
    header("Location: dashboard.php");
    exit();
}

// Ensure database schema is up-to-date
ensureProgressReportsSchema($pdo);

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg   = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// 2. DISCOVER TASK IDs & RESOLVE ACTIVE TASK ID
$availableTaskIds = getInstituteTaskIds($pdo, $prefix);
$selectedTaskId   = trim($_REQUEST['task_id'] ?? $_POST['task_no'] ?? '');

if ($selectedTaskId === '' && !empty($availableTaskIds)) {
    $selectedTaskId = $availableTaskIds[0];
}

// 3. FETCH EXISTING REPORTS FOR SELECTED TASK ID
$taskReports = ($selectedTaskId !== '') ? getReportsForTask($pdo, $prefix, $selectedTaskId) : [];

// Determine active Report ID
$selectedReportId = 0;
if (isset($_GET['report_id'])) {
    $selectedReportId = (int)$_GET['report_id'];
} elseif (isset($_GET['action']) && $_GET['action'] === 'new') {
    $selectedReportId = 0;
} elseif (!empty($taskReports)) {
    $selectedReportId = (int)$taskReports[0]['id'];
}

// 4. POST FORM PROCESSING (SAVE & DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_report') {
        $reportId   = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
        $taskNo     = trim($_POST['task_no'] ?? '');
        $projTitle  = trim($_POST['project_title'] ?? '');
        $piName     = trim($_POST['pi_name'] ?? '');
        $coPiName   = trim($_POST['co_pi_name'] ?? '');
        $workPkg    = trim($_POST['work_package_no'] ?? '');
        $appObjects = trim($_POST['approved_objects'] ?? '');
        $method     = trim($_POST['methodology'] ?? '');
        $sumProg    = trim($_POST['summary_progress'] ?? '');

        // Toggles
        $incPubs     = isset($_POST['include_publications']) ? 1 : 0;
        $incCapacity = isset($_POST['include_capacity_building']) ? 1 : 0;
        $incWshops   = ($incCapacity && isset($_POST['include_workshops'])) ? 1 : 0;
        $incTrain    = ($incCapacity && isset($_POST['include_training'])) ? 1 : 0;
        $incInterns  = ($incCapacity && isset($_POST['include_interns'])) ? 1 : 0;

        $wshopNarrative = trim($_POST['workshops_narrative'] ?? '');
        $trainNarrative = trim($_POST['training_narrative'] ?? '');

        if ($taskNo === '') {
            $_SESSION['error_msg'] = 'Task ID is required.';
            adminRedirect(['task_id' => $selectedTaskId, 'report_id' => $reportId]);
        }
        if ($projTitle === '') {
            $_SESSION['error_msg'] = 'Project Title is required.';
            adminRedirect(['task_id' => $taskNo, 'report_id' => $reportId]);
        }
        if ($piName === '') {
            $_SESSION['error_msg'] = 'Principal Investigator (PI) Name is required.';
            adminRedirect(['task_id' => $taskNo, 'report_id' => $reportId]);
        }

        // Derive intern count from KPI internships
        $internsCount = getKpiInternCountForTask($pdo, $prefix, $taskNo);

        // Approval status
        $approvalStatus = isSuperAdmin() ? 'Approved' : 'Pending';

        $table = "{$prefix}_progress_reports";

        try {
            if ($reportId > 0) {
                // Update existing report
                $sql = "UPDATE `$table` SET 
                            `project_title`            = :project_title,
                            `pi_name`                  = :pi_name,
                            `co_pi_name`               = :co_pi_name,
                            `task_no`                  = :task_no,
                            `work_package_no`          = :work_package_no,
                            `approved_objects`         = :approved_objects,
                            `methodology`              = :methodology,
                            `summary_progress`         = :summary_progress,
                            `interns_trained_count`    = :interns_trained_count,
                            `include_publications`     = :include_publications,
                            `include_capacity_building` = :include_capacity_building,
                            `include_workshops`        = :include_workshops,
                            `include_training`         = :include_training,
                            `include_interns`          = :include_interns,
                            `workshops_narrative`      = :workshops_narrative,
                            `training_narrative`       = :training_narrative,
                            `approval_status`          = :approval_status
                        WHERE `id` = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':project_title'            => $projTitle,
                    ':pi_name'                  => $piName,
                    ':co_pi_name'               => $coPiName,
                    ':task_no'                  => $taskNo,
                    ':work_package_no'          => $workPkg,
                    ':approved_objects'         => $appObjects,
                    ':methodology'              => $method,
                    ':summary_progress'         => $sumProg,
                    ':interns_trained_count'    => $internsCount,
                    ':include_publications'     => $incPubs,
                    ':include_capacity_building' => $incCapacity,
                    ':include_workshops'        => $incWshops,
                    ':include_training'         => $incTrain,
                    ':include_interns'          => $incInterns,
                    ':workshops_narrative'      => $wshopNarrative,
                    ':training_narrative'       => $trainNarrative,
                    ':approval_status'          => $approvalStatus,
                    ':id'                       => $reportId
                ]);

                if (!isSuperAdmin()) {
                    submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $reportId, 'UPDATE', [
                        'task_no' => $taskNo,
                        'project_title' => $projTitle,
                        'approval_status' => 'Pending'
                    ]);
                }

                $_SESSION['success_msg'] = "Progress Report #{$reportId} updated successfully (" . ($approvalStatus === 'Approved' ? 'Approved' : 'Pending Approval') . ").";
                adminRedirect(['task_id' => $taskNo, 'report_id' => $reportId]);
            } else {
                // Insert new report
                $sql = "INSERT INTO `$table` (
                            `task_no`, `project_title`, `pi_name`, `co_pi_name`, `work_package_no`,
                            `approved_objects`, `methodology`, `summary_progress`, `interns_trained_count`,
                            `include_publications`, `include_capacity_building`, `include_workshops`,
                            `include_training`, `include_interns`, `workshops_narrative`, `training_narrative`,
                            `approval_status`, `created_at`
                        ) VALUES (
                            :task_no, :project_title, :pi_name, :co_pi_name, :work_package_no,
                            :approved_objects, :methodology, :summary_progress, :interns_trained_count,
                            :include_publications, :include_capacity_building, :include_workshops,
                            :include_training, :include_interns, :workshops_narrative, :training_narrative,
                            :approval_status, NOW()
                        )";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':task_no'                  => $taskNo,
                    ':project_title'            => $projTitle,
                    ':pi_name'                  => $piName,
                    ':co_pi_name'               => $coPiName,
                    ':work_package_no'          => $workPkg,
                    ':approved_objects'         => $appObjects,
                    ':methodology'              => $method,
                    ':summary_progress'         => $sumProg,
                    ':interns_trained_count'    => $internsCount,
                    ':include_publications'     => $incPubs,
                    ':include_capacity_building' => $incCapacity,
                    ':include_workshops'        => $incWshops,
                    ':include_training'         => $incTrain,
                    ':include_interns'          => $incInterns,
                    ':workshops_narrative'      => $wshopNarrative,
                    ':training_narrative'       => $trainNarrative,
                    ':approval_status'          => $approvalStatus
                ]);

                $newId = (int)$pdo->lastInsertId();

                if (!isSuperAdmin()) {
                    submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $newId, 'INSERT', [
                        'task_no' => $taskNo,
                        'project_title' => $projTitle,
                        'approval_status' => 'Pending'
                    ]);
                }

                $_SESSION['success_msg'] = "Progress Report created successfully (Report #{$newId}).";
                adminRedirect(['task_id' => $taskNo, 'report_id' => $newId]);
            }
        } catch (Exception $e) {
            $_SESSION['error_msg'] = "Database error saving report: " . $e->getMessage();
            adminRedirect(['task_id' => $taskNo, 'report_id' => $reportId]);
        }
    } elseif ($action === 'delete_report') {
        $reportId = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
        if ($reportId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM `{$prefix}_progress_reports` WHERE id = ?");
                $stmt->execute([$reportId]);
                $_SESSION['success_msg'] = "Progress Report #{$reportId} deleted successfully.";
            } catch (Exception $e) {
                $_SESSION['error_msg'] = "Error deleting report: " . $e->getMessage();
            }
        }
        adminRedirect(['task_id' => $selectedTaskId, 'action' => 'new']);
    }
}

// 5. LOAD ACTIVE REPORT DATA FOR DISPLAY
$activeReport = null;
if ($selectedReportId > 0) {
    foreach ($taskReports as $r) {
        if ((int)$r['id'] === $selectedReportId) {
            $activeReport = $r;
            break;
        }
    }
    if (!$activeReport) {
        // Fallback fetch if not in initial list
        try {
            $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_progress_reports` WHERE id = ?");
            $stmt->execute([$selectedReportId]);
            $activeReport = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
}

// Form Field Values
$valProjectTitle = $activeReport['project_title'] ?? '';
$valPiName       = $activeReport['pi_name'] ?? '';
$valCoPiName     = $activeReport['co_pi_name'] ?? '';
$valWorkPkg      = $activeReport['work_package_no'] ?? '';
$valAppObjects   = $activeReport['approved_objects'] ?? '';
$valMethodology  = $activeReport['methodology'] ?? '';
$valSumProgress  = $activeReport['summary_progress'] ?? '';
$valWshopNarr    = $activeReport['workshops_narrative'] ?? '';
$valTrainNarr    = $activeReport['training_narrative'] ?? '';

// Checkboxes (Default to 1 if new report)
$chkPubs     = isset($activeReport) ? !empty($activeReport['include_publications']) : true;
$chkCapacity = isset($activeReport) ? !empty($activeReport['include_capacity_building']) : true;
$chkWshops   = isset($activeReport) ? !empty($activeReport['include_workshops']) : true;
$chkTraining = isset($activeReport) ? !empty($activeReport['include_training']) : true;
$chkInterns  = isset($activeReport) ? !empty($activeReport['include_interns']) : true;

// 6. FETCH KPI REFERENCE DATA FOR PREVIEWS (SINGLE SOURCE OF TRUTH)
$kpiPubs     = ($selectedTaskId !== '') ? getKpiPublicationsForTask($pdo, $prefix, $selectedTaskId) : [];
$kpiConfs    = ($selectedTaskId !== '') ? getKpiConferencesForTask($pdo, $prefix, $selectedTaskId) : [];
$kpiWebs     = ($selectedTaskId !== '') ? getKpiWebinarsForTask($pdo, $prefix, $selectedTaskId) : [];
$kpiInterns  = ($selectedTaskId !== '') ? getKpiInternCountForTask($pdo, $prefix, $selectedTaskId) : 0;

$instituteFullName = getInstituteFullName($prefix);
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <!-- Page Title & Breadcrumb -->
            <div class="row page-titles">
                <div class="col-md-6 col-12 align-self-center">
                    <h3 class="text-themecolor font-weight-bold mb-0">Progress Reports</h3>
                    <small class="text-muted">Report Generator & Narrative Management</small>
                </div>
                <div class="col-md-6 col-12 align-self-center text-md-end">
                    <ol class="breadcrumb d-inline-flex bg-transparent p-0 m-0">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Progress Reports</li>
                    </ol>
                </div>
            </div>

            <!-- Messages -->
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <strong>Success:</strong> <?= htmlspecialchars($success_msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>Error:</strong> <?= htmlspecialchars($error_msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- TASK ID SELECTOR CARD -->
            <div class="card border-0 shadow-sm mb-4" style="border-top: 4px solid #0f4c81 !important;">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-5 mb-2 mb-md-0">
                            <label class="form-label font-weight-bold text-dark mb-1">
                                <i class="fas fa-tasks text-primary me-1"></i> Select Task ID (From KPI Data):
                            </label>
                            <form method="GET" action="<?= buildNavUrl('progress_reports.php') ?>" id="taskSelectForm">
                                <?php if (isSuperAdmin()): ?>
                                    <input type="hidden" name="prefix" value="<?= htmlspecialchars($prefix) ?>">
                                <?php endif; ?>
                                <?php if (!empty($_SESSION['tab_token'])): ?>
                                    <input type="hidden" name="tab_token" value="<?= htmlspecialchars($_SESSION['tab_token']) ?>">
                                <?php endif; ?>

                                <div class="input-group">
                                    <select name="task_id" class="form-select form-select-lg font-weight-bold border-primary" onchange="document.getElementById('taskSelectForm').submit();">
                                        <?php if (empty($availableTaskIds)): ?>
                                            <option value="">No Task IDs Found</option>
                                        <?php else: ?>
                                            <?php foreach ($availableTaskIds as $tId): ?>
                                                <option value="<?= htmlspecialchars($tId) ?>" <?= ($tId === $selectedTaskId) ? 'selected' : '' ?>>
                                                    Task ID: <?= htmlspecialchars($tId) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </form>
                        </div>

                        <!-- KPI Source Badges -->
                        <div class="col-lg-8 col-md-7 text-md-end">
                            <div class="d-inline-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                                <span class="badge bg-light text-dark border p-2">
                                    <i class="fas fa-book text-info me-1"></i> Publications KPI: <strong><?= count($kpiPubs) ?></strong>
                                </span>
                                <span class="badge bg-light text-dark border p-2">
                                    <i class="fas fa-chalkboard-teacher text-success me-1"></i> Conferences KPI: <strong><?= count($kpiConfs) ?></strong>
                                </span>
                                <span class="badge bg-light text-dark border p-2">
                                    <i class="fas fa-video text-warning me-1"></i> Trainings KPI: <strong><?= count($kpiWebs) ?></strong>
                                </span>
                                <span class="badge bg-light text-dark border p-2">
                                    <i class="fas fa-user-graduate text-primary me-1"></i> Interns Trained KPI: <strong><?= $kpiInterns ?></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($selectedTaskId === ''): ?>
                <div class="card border-0 shadow-sm p-5 text-center">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h4>No Task IDs Available</h4>
                    <p class="text-muted mb-0">No KPI records with a Task ID were found for <strong><?= htmlspecialchars($instituteFullName) ?></strong>. Enter KPI records (Publications, Conferences, etc.) first to create reports.</p>
                </div>
            <?php else: ?>

                <!-- EXISTING REPORTS SWITCHER BAR -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 bg-white p-3 rounded shadow-sm border">
                    <div>
                        <span class="text-dark font-weight-bold me-2"><i class="fas fa-history text-secondary me-1"></i> Reports for Task ID "<?= htmlspecialchars($selectedTaskId) ?>":</span>
                        <?php if (empty($taskReports)): ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-info-circle me-1"></i> No report exists for this Task ID yet</span>
                        <?php else: ?>
                            <div class="btn-group" role="group">
                                <?php foreach ($taskReports as $r): ?>
                                    <a href="<?= buildNavUrl('progress_reports.php') ?>&task_id=<?= urlencode($selectedTaskId) ?>&report_id=<?= $r['id'] ?>" 
                                       class="btn btn-sm <?= ((int)$r['id'] === $selectedReportId) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                                        <i class="fas fa-file-alt me-1"></i> Report #<?= $r['id'] ?>
                                        <small>(<?= !empty($r['created_at']) ? date('M d, Y', strtotime($r['created_at'])) : 'Draft' ?>)</small>
                                        <?php if (($r['approval_status'] ?? '') === 'Approved'): ?>
                                            <span class="badge bg-success ms-1">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark ms-1">Pending</span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <a href="<?= buildNavUrl('progress_reports.php') ?>&task_id=<?= urlencode($selectedTaskId) ?>&action=new" 
                           class="btn btn-sm btn-outline-success">
                            <i class="fas fa-plus-circle me-1"></i> Create New Report for Task <?= htmlspecialchars($selectedTaskId) ?>
                        </a>
                    </div>
                </div>

                <!-- MAIN REPORT EDITOR FORM -->
                <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>" id="progressReportForm">
                    <?= getCsrfInputField() ?>
                    <input type="hidden" name="action" value="save_report">
                    <input type="hidden" name="report_id" value="<?= $selectedReportId ?>">
                    <input type="hidden" name="task_no" value="<?= htmlspecialchars($selectedTaskId) ?>">

                    <!-- SECTION 1: PROJECT INFORMATION -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header text-white py-3" style="background: linear-gradient(90deg, #0f4c81, #1e6fa8) !important;">
                            <h5 class="card-title text-white mb-0 font-weight-bold">
                                <i class="fas fa-info-circle me-2"></i> Project Information — Task ID: <?= htmlspecialchars($selectedTaskId) ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Project Title <span class="text-danger">*</span></label>
                                    <input type="text" name="project_title" class="form-control" required placeholder="Enter project title..." value="<?= htmlspecialchars($valProjectTitle) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Work Package No / Details</label>
                                    <input type="text" name="work_package_no" class="form-control" placeholder="e.g. WP-01 (or leave blank)" value="<?= htmlspecialchars($valWorkPkg) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Principal Investigator (PI) <span class="text-danger">*</span></label>
                                    <input type="text" name="pi_name" class="form-control" required placeholder="Enter PI name..." value="<?= htmlspecialchars($valPiName) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Co-Principal Investigator (Co-PI)</label>
                                    <input type="text" name="co_pi_name" class="form-control" placeholder="Enter Co-PI name (if applicable)..." value="<?= htmlspecialchars($valCoPiName) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: PROGRESS REPORT NARRATIVE -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light py-3 border-bottom">
                            <h5 class="card-title text-dark mb-0 font-weight-bold">
                                <i class="fas fa-align-left text-primary me-2"></i> Progress Report Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-dark">Approved Objectives</label>
                                <textarea name="approved_objects" class="form-control" rows="4" placeholder="Enter the approved project objectives..."><?= htmlspecialchars($valAppObjects) ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-dark">Methodology</label>
                                <textarea name="methodology" class="form-control" rows="4" placeholder="Describe the methodology adopted..."><?= htmlspecialchars($valMethodology) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold text-dark">Summary of Progress</label>
                                <textarea name="summary_progress" class="form-control" rows="5" placeholder="Provide a detailed summary of progress achieved during this reporting period..."><?= htmlspecialchars($valSumProgress) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: ADD TO THIS REPORT (OPTIONAL SECTIONS & LIVE KPI PREVIEWS) -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="card-title text-dark mb-0 font-weight-bold">
                                <i class="fas fa-layer-group text-success me-2"></i> Add To This Report
                            </h5>
                            <small class="text-muted">Select additional sections to include in the generated report</small>
                        </div>
                        <div class="card-body">

                            <!-- OPTION A: PUBLICATIONS -->
                            <div class="border rounded p-3 mb-4 bg-white shadow-xs">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="include_publications" id="chkPubs" value="1" <?= $chkPubs ? 'checked' : '' ?> onchange="toggleSectionVisibility('pubsPreview', this.checked)">
                                    <label class="form-check-label font-weight-bold text-dark fs-5 me-2" for="chkPubs">
                                        <i class="fas fa-book text-info me-1"></i> Publications
                                    </label>
                                    <span class="badge bg-info text-white">Derived from KPI Data</span>
                                </div>

                                <div id="pubsPreview" class="ms-4 mt-3" style="display: <?= $chkPubs ? 'block' : 'none' ?>;">
                                    <?php if (empty($kpiPubs)): ?>
                                        <div class="alert alert-light border text-muted mb-0">
                                            <i class="fas fa-info-circle me-1"></i> No publication KPI records exist for Task ID "<strong><?= htmlspecialchars($selectedTaskId) ?></strong>". You can add publications in the <a href="publications.php" target="_blank" class="fw-bold">Publications KPI module</a>, and they will automatically appear here.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 40px;">#</th>
                                                        <th>Publication Title</th>
                                                        <th>Author(s)</th>
                                                        <th>Journal</th>
                                                        <th>Date</th>
                                                        <th>DOI</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $idx = 1; foreach ($kpiPubs as $pub): ?>
                                                        <tr>
                                                            <td><?= $idx++ ?></td>
                                                            <td class="fw-bold text-dark"><?= htmlspecialchars($pub['publication_title'] ?? '') ?></td>
                                                            <td><?= htmlspecialchars($pub['author_name'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($pub['publication_journal'] ?? '-') ?></td>
                                                            <td><?= !empty($pub['publication_date']) ? date('d M Y', strtotime($pub['publication_date'])) : '-' ?></td>
                                                            <td><?= htmlspecialchars($pub['doi_number'] ?? '-') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-check-circle text-success me-1"></i> These publication records will be automatically included in the generated report PDF.</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- OPTION B: CAPACITY BUILDING -->
                            <div class="border rounded p-3 bg-white shadow-xs">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="include_capacity_building" id="chkCapacity" value="1" <?= $chkCapacity ? 'checked' : '' ?> onchange="toggleSectionVisibility('capacityGroup', this.checked)">
                                    <label class="form-check-label font-weight-bold text-dark fs-5 me-2" for="chkCapacity">
                                        <i class="fas fa-graduation-cap text-success me-1"></i> Capacity Building
                                    </label>
                                </div>

                                <div id="capacityGroup" class="ms-4 mt-3" style="display: <?= $chkCapacity ? 'block' : 'none' ?>;">

                                    <!-- B.1 WORKSHOPS / CONFERENCES -->
                                    <div class="card card-body bg-light border mb-3">
                                        <div class="form-check form-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="include_workshops" id="chkWshops" value="1" <?= $chkWshops ? 'checked' : '' ?> onchange="toggleSectionVisibility('wshopsPreview', this.checked)">
                                            <label class="form-check-label font-weight-bold text-dark me-2" for="chkWshops">
                                                Workshops / Conferences Conducted
                                            </label>
                                        </div>

                                        <div id="wshopsPreview" style="display: <?= $chkWshops ? 'block' : 'none' ?>;" class="mt-2">
                                            <div class="mb-2">
                                                <label class="form-label text-muted small font-weight-bold mb-1">Optional Workshop Summary / Notes:</label>
                                                <textarea name="workshops_narrative" class="form-control form-control-sm" rows="2" placeholder="Add specific report notes regarding workshops or conferences conducted..."><?= htmlspecialchars($valWshopNarr) ?></textarea>
                                            </div>

                                            <?php if (empty($kpiConfs)): ?>
                                                <div class="small text-muted italic"><i class="fas fa-info-circle me-1"></i> No conference KPI records found for Task ID "<?= htmlspecialchars($selectedTaskId) ?>".</div>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered bg-white mb-0 small">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Title</th>
                                                                <th>Organizer</th>
                                                                <th>Date</th>
                                                                <th>Location</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $cIdx = 1; foreach ($kpiConfs as $conf): ?>
                                                                <tr>
                                                                    <td><?= $cIdx++ ?></td>
                                                                    <td class="fw-bold"><?= htmlspecialchars($conf['title'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($conf['organizer'] ?? ($conf['organisers'] ?? '-')) ?></td>
                                                                    <td><?= !empty($conf['start_date']) ? date('d M Y', strtotime($conf['start_date'])) : (!empty($conf['conf_date']) ? date('d M Y', strtotime($conf['conf_date'])) : '-') ?></td>
                                                                    <td><?= htmlspecialchars($conf['location'] ?? '-') ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- B.2 TRAINING PROGRAMS -->
                                    <div class="card card-body bg-light border mb-3">
                                        <div class="form-check form-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="include_training" id="chkTraining" value="1" <?= $chkTraining ? 'checked' : '' ?> onchange="toggleSectionVisibility('trainingPreview', this.checked)">
                                            <label class="form-check-label font-weight-bold text-dark me-2" for="chkTraining">
                                                Training Programs Conducted
                                            </label>
                                        </div>

                                        <div id="trainingPreview" style="display: <?= $chkTraining ? 'block' : 'none' ?>;" class="mt-2">
                                            <div class="mb-2">
                                                <label class="form-label text-muted small font-weight-bold mb-1">Optional Training Summary / Notes:</label>
                                                <textarea name="training_narrative" class="form-control form-control-sm" rows="2" placeholder="Add specific report notes regarding training programs conducted..."><?= htmlspecialchars($valTrainNarr) ?></textarea>
                                            </div>

                                            <?php if (empty($kpiWebs)): ?>
                                                <div class="small text-muted italic"><i class="fas fa-info-circle me-1"></i> No webinar/training KPI records found for Task ID "<?= htmlspecialchars($selectedTaskId) ?>".</div>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered bg-white mb-0 small">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Title</th>
                                                                <th>Speaker</th>
                                                                <th>Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $wIdx = 1; foreach ($kpiWebs as $web): ?>
                                                                <tr>
                                                                    <td><?= $wIdx++ ?></td>
                                                                    <td class="fw-bold"><?= htmlspecialchars($web['title'] ?? '') ?></td>
                                                                    <td><?= htmlspecialchars($web['speaker_name'] ?? '-') ?></td>
                                                                    <td><?= !empty($web['webinar_date']) ? date('d M Y', strtotime($web['webinar_date'])) : '-' ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- B.3 NUMBER OF INTERNS TRAINED -->
                                    <div class="card card-body bg-light border mb-0">
                                        <div class="form-check form-checkbox mb-2">
                                            <input class="form-check-input" type="checkbox" name="include_interns" id="chkInterns" value="1" <?= $chkInterns ? 'checked' : '' ?> onchange="toggleSectionVisibility('internsPreview', this.checked)">
                                            <label class="form-check-label font-weight-bold text-dark me-2" for="chkInterns">
                                                Number of Interns Trained
                                            </label>
                                        </div>

                                        <div id="internsPreview" style="display: <?= $chkInterns ? 'block' : 'none' ?>;" class="mt-2">
                                            <div class="d-flex align-items-center gap-3 bg-white p-3 rounded border">
                                                <div class="display-6 font-weight-bold text-primary"><?= $kpiInterns ?></div>
                                                <div>
                                                    <div class="font-weight-bold text-dark">Total Interns Trained</div>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-check-circle text-success me-1"></i> Derived automatically from Internship KPI records (SUM of <code>no_students_trained</code> for Task ID: <?= htmlspecialchars($selectedTaskId) ?>)
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- FORM ACTIONS -->
                    <div class="card border-0 shadow-sm mb-5">
                        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <?php if ($selectedReportId > 0): ?>
                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDeleteReport();">
                                        <i class="fas fa-trash-alt me-1"></i> Delete Report
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success btn-lg px-4 font-weight-bold">
                                    <i class="fas fa-save me-2"></i> Save Report
                                </button>

                                <?php if ($selectedReportId > 0): ?>
                                    <a href="<?= buildNavUrl('export_progress_report_pdf.php') ?>&id=<?= $selectedReportId ?>" 
                                       target="_blank" 
                                       class="btn btn-primary btn-lg px-4 font-weight-bold">
                                        <i class="fas fa-file-pdf me-2"></i> Generate Report (PDF)
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary btn-lg px-4" disabled title="Save report first to generate PDF">
                                        <i class="fas fa-file-pdf me-2"></i> Generate Report (PDF)
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Hidden Delete Form -->
                <?php if ($selectedReportId > 0): ?>
                    <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>" id="deleteReportForm" style="display:none;">
                        <?= getCsrfInputField() ?>
                        <input type="hidden" name="action" value="delete_report">
                        <input type="hidden" name="report_id" value="<?= $selectedReportId ?>">
                        <input type="hidden" name="task_no" value="<?= htmlspecialchars($selectedTaskId) ?>">
                    </form>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function toggleSectionVisibility(elementId, isVisible) {
    var el = document.getElementById(elementId);
    if (el) {
        el.style.display = isVisible ? 'block' : 'none';
    }
}

function confirmDeleteReport() {
    if (confirm("Are you sure you want to delete Progress Report #<?= $selectedReportId ?>? This action cannot be undone.")) {
        document.getElementById('deleteReportForm').submit();
    }
}
</script>

<?php include 'footer.php'; ?>
