<?php
require_once 'auth_check.php';
require_once 'role_access.php';
require_once 'config/db.php';

// Step 1: Institute Context Resolution
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
                $edit_id          = !empty($_POST['report_id']) ? (int)$_POST['report_id'] : null;

                if (empty($task_no)) {
                    throw new RuntimeException("Task ID is required.");
                }
                if (empty($project_title)) {
                    throw new RuntimeException("Project Title is required.");
                }

                $is_super = isSuperAdmin();
                $approvalStatus = $is_super ? 'Approved' : 'Pending';

                if ($edit_id) {
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
                            approval_status = :approval_status
                        WHERE id = :id
                    ");
                    $payload = [
                        ':task_no'          => $task_no,
                        ':project_title'    => $project_title,
                        ':pi_name'          => $pi_name,
                        ':co_pi_name'       => $co_pi_name,
                        ':work_package_no'  => $work_package_no,
                        ':approved_objects' => $approved_objects,
                        ':methodology'      => $methodology,
                        ':summary_progress' => $summary_progress,
                        ':approval_status'  => $approvalStatus,
                        ':id'               => $edit_id
                    ];
                    $stmt->execute($payload);

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $edit_id, 'UPDATE', $payload);
                        adminRedirect(['success_msg' => 'submitted', 'task_no' => $task_no]);
                    } else {
                        adminRedirect(['success_msg' => 'updated', 'task_no' => $task_no]);
                    }
                } else {
                    // Check duplicate Task ID
                    $chk = $pdo->prepare("SELECT id FROM `$table` WHERE task_no = ?");
                    $chk->execute([$task_no]);
                    if ($chk->fetch()) {
                        throw new RuntimeException("Task ID '{$task_no}' already exists under this institute. Please select it from the dropdown.");
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO `$table`
                            (task_no, project_title, pi_name, co_pi_name, work_package_no,
                             approved_objects, methodology, summary_progress, interns_trained_count, approval_status, created_at)
                        VALUES
                            (:task_no, :project_title, :pi_name, :co_pi_name, :work_package_no,
                             :approved_objects, :methodology, :summary_progress, 0, :approval_status, NOW())
                    ");
                    $payload = [
                        ':task_no'          => $task_no,
                        ':project_title'    => $project_title,
                        ':pi_name'          => $pi_name,
                        ':co_pi_name'       => $co_pi_name,
                        ':work_package_no'  => $work_package_no,
                        ':approved_objects' => $approved_objects,
                        ':methodology'      => $methodology,
                        ':summary_progress' => $summary_progress,
                        ':approval_status'  => $approvalStatus
                    ];
                    $stmt->execute($payload);
                    $new_id = $pdo->lastInsertId();

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $new_id, 'CREATE', $payload);
                        adminRedirect(['success_msg' => 'submitted', 'task_no' => $task_no]);
                    } else {
                        adminRedirect(['success_msg' => 'inserted', 'task_no' => $task_no]);
                    }
                }
            }

            // B. PUBLICATION DETAILS SUBMISSION
            elseif ($formType === 'save_publication') {
                $pr_id               = (int)($_POST['progress_report_id'] ?? 0);
                $task_no             = trim($_POST['task_no'] ?? '');
                $pub_id              = !empty($_POST['pub_id']) ? (int)$_POST['pub_id'] : null;
                $publication_title   = trim($_POST['publication_title'] ?? '');
                $author_name         = trim($_POST['author_name'] ?? '');
                $doi_number          = trim($_POST['doi_number'] ?? '');
                $publication_date    = !empty($_POST['publication_date']) ? $_POST['publication_date'] : null;
                $publication_journal = trim($_POST['publication_journal'] ?? '');
                $impact_factor       = ($_POST['impact_factor'] !== '') ? (float)$_POST['impact_factor'] : null;

                if ($pr_id <= 0) throw new RuntimeException("Invalid project context.");
                if (empty($publication_title)) throw new RuntimeException("Publication Title is required.");
                if (empty($author_name)) throw new RuntimeException("Author name is required.");

                if ($pub_id) {
                    $stmt = $pdo->prepare("
                        UPDATE `$pubsTable` SET
                            task_no = :task_no,
                            publication_title = :publication_title,
                            author_name = :author_name,
                            doi_number = :doi_number,
                            publication_date = :publication_date,
                            publication_journal = :publication_journal,
                            impact_factor = :impact_factor
                        WHERE id = :id AND progress_report_id = :pr_id
                    ");
                    $stmt->execute([
                        ':task_no'             => $task_no,
                        ':publication_title'   => $publication_title,
                        ':author_name'         => $author_name,
                        ':doi_number'          => $doi_number,
                        ':publication_date'    => $publication_date,
                        ':publication_journal' => $publication_journal,
                        ':impact_factor'       => $impact_factor,
                        ':id'                  => $pub_id,
                        ':pr_id'               => $pr_id
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO `$pubsTable`
                            (progress_report_id, task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor)
                        VALUES
                            (:pr_id, :task_no, :publication_title, :author_name, :doi_number, :publication_date, :publication_journal, :impact_factor)
                    ");
                    $stmt->execute([
                        ':pr_id'               => $pr_id,
                        ':task_no'             => $task_no,
                        ':publication_title'   => $publication_title,
                        ':author_name'         => $author_name,
                        ':doi_number'          => $doi_number,
                        ':publication_date'    => $publication_date,
                        ':publication_journal' => $publication_journal,
                        ':impact_factor'       => $impact_factor
                    ]);
                }
                adminRedirect(['success_msg' => 'pub_saved', 'task_no' => $task_no]);
            }

            // C. CAPACITY BUILDING EVENT SUBMISSION
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

                if ($event_id) {
                    $stmt = $pdo->prepare("
                        UPDATE `$eventsTable` SET
                            category = :category,
                            title = :title,
                            event_date = :event_date,
                            duration = :duration,
                            venue_mode = :venue_mode,
                            organizing_institution = :organizing_institution,
                            participant_count = :participant_count,
                            description = :description
                        WHERE id = :id AND progress_report_id = :pr_id
                    ");
                    $stmt->execute([
                        ':category'               => $category,
                        ':title'                  => $event_title,
                        ':event_date'             => $event_date,
                        ':duration'               => $duration,
                        ':venue_mode'             => $venue_mode,
                        ':organizing_institution' => $organizing_institution,
                        ':participant_count'      => $participant_count,
                        ':description'            => $description,
                        ':id'                     => $event_id,
                        ':pr_id'                  => $pr_id
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO `$eventsTable`
                            (progress_report_id, category, title, event_date, duration, venue_mode, organizing_institution, participant_count, description)
                        VALUES
                            (:pr_id, :category, :title, :event_date, :duration, :venue_mode, :organizing_institution, :participant_count, :description)
                    ");
                    $stmt->execute([
                        ':pr_id'                  => $pr_id,
                        ':category'               => $category,
                        ':title'                  => $event_title,
                        ':event_date'             => $event_date,
                        ':duration'               => $duration,
                        ':venue_mode'             => $venue_mode,
                        ':organizing_institution' => $organizing_institution,
                        ':participant_count'      => $participant_count,
                        ':description'            => $description
                    ]);
                }
                adminRedirect(['success_msg' => 'event_saved', 'task_no' => $task_no]);
            }

            // D. UPDATE INTERNS TRAINED COUNT
            elseif ($formType === 'save_interns_count') {
                $pr_id                 = (int)($_POST['progress_report_id'] ?? 0);
                $task_no               = trim($_POST['task_no'] ?? '');
                $interns_trained_input = $_POST['interns_trained_count'] ?? '0';

                if ($pr_id <= 0) throw new RuntimeException("Invalid project context.");
                if ($interns_trained_input < 0 || !filter_var($interns_trained_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
                    throw new RuntimeException("Interns count must be a non-negative integer.");
                }
                $interns_trained_count = (int)$interns_trained_input;

                $stmt = $pdo->prepare("UPDATE `$table` SET interns_trained_count = :count WHERE id = :id");
                $stmt->execute([':count' => $interns_trained_count, ':id' => $pr_id]);
                adminRedirect(['success_msg' => 'interns_updated', 'task_no' => $task_no]);
            }

            // E. DESTRUCTIVE ACTIONS (SECURE POST DELETE HANDLERS)
            elseif ($formType === 'delete_main_report') {
                $del_id  = (int)($_POST['delete_id'] ?? 0);
                $task_no = trim($_POST['task_no'] ?? '');
                if ($del_id > 0) {
                    $pdo->prepare("DELETE FROM `$table` WHERE id = ?")->execute([$del_id]);
                    $pdo->prepare("DELETE FROM `$pubsTable` WHERE progress_report_id = ?")->execute([$del_id]);
                    $pdo->prepare("DELETE FROM `$eventsTable` WHERE progress_report_id = ?")->execute([$del_id]);
                }
                adminRedirect(['success_msg' => 'deleted', 'task_no' => null]);
            }
            elseif ($formType === 'delete_publication') {
                $del_id  = (int)($_POST['delete_id'] ?? 0);
                $task_no = trim($_POST['task_no'] ?? '');
                if ($del_id > 0) {
                    $pdo->prepare("DELETE FROM `$pubsTable` WHERE id = ?")->execute([$del_id]);
                }
                adminRedirect(['success_msg' => 'deleted', 'task_no' => $task_no]);
            }
            elseif ($formType === 'delete_capacity_event') {
                $del_id  = (int)($_POST['delete_id'] ?? 0);
                $task_no = trim($_POST['task_no'] ?? '');
                if ($del_id > 0) {
                    $pdo->prepare("DELETE FROM `$eventsTable` WHERE id = ?")->execute([$del_id]);
                }
                adminRedirect(['success_msg' => 'deleted', 'task_no' => $task_no]);
            }
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }
    }
}

// ----------------------------------------------------------------------------
// DATA RETRIEVAL FOR TASK IDs & SELECTED TASK CONTEXT
// ----------------------------------------------------------------------------
$availableTasks = [];
try {
    $stmtTasks = $pdo->query("SELECT id, task_no, project_title, pi_name, co_pi_name, work_package_no, approved_objects, methodology, summary_progress, interns_trained_count, created_at FROM `$table` ORDER BY task_no ASC, id DESC");
    $availableTasks = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $availableTasks = [];
}

// Resolve selected Task ID
$reqTaskNo = $_GET['task_no'] ?? null;
$activeTask = null;

if (!empty($reqTaskNo) && $reqTaskNo !== '__new__') {
    foreach ($availableTasks as $t) {
        if ($t['task_no'] === $reqTaskNo) {
            $activeTask = $t;
            break;
        }
    }
}

if (!$activeTask && !empty($availableTasks) && $reqTaskNo !== '__new__') {
    // Default to the first task if not specified
    $activeTask = $availableTasks[0];
}

// If active task exists, fetch its child publications and capacity building events
$taskPubs      = [];
$taskWorkshops = [];
$taskTrainings = [];

if ($activeTask) {
    $activePrId = (int)$activeTask['id'];

    // Publications
    try {
        $stmtP = $pdo->prepare("SELECT * FROM `$pubsTable` WHERE progress_report_id = :pr_id ORDER BY id DESC");
        $stmtP->execute([':pr_id' => $activePrId]);
        $taskPubs = $stmtP->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $ex) {}

    // Capacity Events
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
    .badge-locked {
        background-color: #475569 !important;
        color: #ffffff !important;
        font-weight: 600;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .project-banner-card {
        background: linear-gradient(135deg, #024283 0%, #0f172a 100%) !important;
        color: #ffffff !important;
        border-radius: 10px;
        padding: 20px;
    }
    .component-check-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 18px;
        transition: all 0.2s ease;
    }
    .component-check-box:hover {
        border-color: #024283;
        background: #f1f5f9;
    }
    .capacity-sub-box {
        background: #faf5ff;
        border: 1px solid #e9d5ff;
        border-radius: 8px;
        padding: 14px 18px;
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
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

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
                 STEP 1 — INSTITUTE SELECTION
                 ========================================================================= -->
            <div class="card workflow-card mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3">
                            <label class="form-label font-w700 text-uppercase text-muted mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                Step 1 — Institute Context
                            </label>
                            <h6 class="mb-0 font-w700 text-dark">Selected Institute:</h6>
                        </div>
                        <div class="col-md-9">
                            <?php if (isSuperAdmin()): ?>
                                <!-- Hub Admin / Super Admin Dropdown Selector -->
                                <div class="d-flex align-items-center gap-3">
                                    <select class="form-select font-w700 text-dark" style="max-width: 400px; border-color: #024283;" onchange="switchInstitute(this.value)">
                                        <?php foreach ($adminAllowedPrefixes as $p): ?>
                                            <option value="<?= $p ?>" <?= ($prefix === $p) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(getInstituteFullName($p)) ?> (<?= strtoupper($p) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="badge bg-primary fs-12 px-3 py-2"><i class="fa fa-user-shield me-1"></i> Hub Admin Access</span>
                                </div>
                            <?php else: ?>
                                <!-- Spoke Admin Locked View -->
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-16 font-w700 text-dark border px-3 py-2 rounded bg-light">
                                        <?= htmlspecialchars(getInstituteFullName($prefix)) ?> (<?= strtoupper($prefix) ?>)
                                    </span>
                                    <span class="badge-locked"><i class="fa fa-lock me-1"></i> Institute Locked</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =========================================================================
                 STEP 2 — TASK ID SELECTION & PROJECT CONTEXT BANNER
                 ========================================================================= -->
            <div class="card workflow-card mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label font-w700 text-uppercase text-muted mb-1" style="font-size: 11px; letter-spacing: 0.5px;">
                                Step 2 — Select Task ID
                            </label>
                            <h6 class="mb-0 font-w700 text-dark">Business Identifier:</h6>
                        </div>
                        <div class="col-md-9">
                            <select id="task_id_selector" class="form-select font-w700 text-dark" style="max-width: 500px; border-color: #024283;" onchange="switchTaskId(this.value)">
                                <?php if (empty($availableTasks)): ?>
                                    <option value="__new__" selected>+ Create New Task ID / Project Context</option>
                                <?php else: ?>
                                    <?php foreach ($availableTasks as $t): ?>
                                        <option value="<?= htmlspecialchars($t['task_no']) ?>" <?= ($activeTask && $activeTask['task_no'] === $t['task_no'] && !$isNewTaskSelected) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['task_no']) ?> — <?= htmlspecialchars($t['project_title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__new__" <?= $isNewTaskSelected ? 'selected' : '' ?>>+ Create New Task ID / Project Context</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($activeTask && !$isNewTaskSelected): ?>
                        <!-- Active Project Summary Banner -->
                        <div class="project-banner-card mt-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-warning text-dark font-w700 mb-2">
                                        <i class="fa fa-tag me-1"></i> TASK ID: <?= htmlspecialchars($activeTask['task_no']) ?>
                                    </span>
                                    <h4 class="text-white font-w700 mb-2"><?= htmlspecialchars($activeTask['project_title']) ?></h4>
                                    <div class="d-flex flex-wrap gap-4 text-white-50 fs-14">
                                        <span><strong class="text-white">PI:</strong> <?= htmlspecialchars($activeTask['pi_name'] ?: '—') ?></span>
                                        <span><strong class="text-white">Co-PI:</strong> <?= htmlspecialchars($activeTask['co_pi_name'] ?: '—') ?></span>
                                        <span><strong class="text-white">Work Package:</strong> <?= htmlspecialchars($activeTask['work_package_no'] ?: '—') ?></span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?= buildNavUrl('export_progress_report_pdf.php?id=' . $activeTask['id'] . '&prefix=' . $prefix) ?>" target="_blank" class="btn btn-light btn-sm font-w700 text-dark">
                                        <i class="fa fa-file-pdf-o text-danger me-1"></i> Export PDF Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- =========================================================================
                 STEP 3 — COMPONENT CHECKBOXES
                 ========================================================================= -->
            <div class="card workflow-card mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 font-w700 text-dark"><i class="fa fa-check-square-o text-primary me-2"></i>Select Information to Manage</h5>
                    <small class="text-muted">Check options below to enable and manage specific project report modules.</small>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Checkbox 1: Progress Report -->
                        <div class="col-md-4">
                            <div class="component-check-box">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input me-2 cursor-pointer" type="checkbox" id="chk_progress_report" onchange="toggleComponentUI()" checked>
                                    <label class="form-check-label font-w700 text-dark cursor-pointer" for="chk_progress_report">
                                        <i class="fa fa-file-text-o text-primary me-1"></i> Progress Report
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Checkbox 2: Publication Details -->
                        <div class="col-md-4">
                            <div class="component-check-box">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input me-2 cursor-pointer" type="checkbox" id="chk_publications" onchange="toggleComponentUI()" checked>
                                    <label class="form-check-label font-w700 text-dark cursor-pointer" for="chk_publications">
                                        <i class="fa fa-book text-success me-1"></i> Add Publication Details
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Checkbox 3: Capacity Building Parent -->
                        <div class="col-md-4">
                            <div class="component-check-box">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input me-2 cursor-pointer" type="checkbox" id="chk_capacity_building" onchange="toggleCapacitySubCheckboxes()" checked>
                                    <label class="form-check-label font-w700 text-dark cursor-pointer" for="chk_capacity_building">
                                        <i class="fa fa-graduation-cap text-purple me-1"></i> Capacity Building
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Capacity Building Sub-checkboxes -->
                        <div class="col-12" id="capacity_sub_wrapper">
                            <div class="capacity-sub-box">
                                <label class="form-label font-w700 text-purple uppercase mb-2 fs-12">Capacity Building Categories:</label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="chk_workshops" onchange="toggleComponentUI()" checked>
                                            <label class="form-check-label font-w600 text-dark cursor-pointer" for="chk_workshops">
                                                <i class="fa fa-users text-info me-1"></i> Workshops / Conferences Conducted
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="chk_trainings" onchange="toggleComponentUI()" checked>
                                            <label class="form-check-label font-w600 text-dark cursor-pointer" for="chk_trainings">
                                                <i class="fa fa-chalkboard-teacher text-warning me-1"></i> Training Programs Conducted
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="chk_interns" onchange="toggleComponentUI()" checked>
                                            <label class="form-check-label font-w600 text-dark cursor-pointer" for="chk_interns">
                                                <i class="fa fa-user-graduate text-success me-1"></i> Number of Interns Trained
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =========================================================================
                 MODULE 1: PROGRESS REPORT FORM
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
                                <input type="text" name="task_no" class="form-control font-w700" value="<?= htmlspecialchars($activeTask['task_no'] ?? ($reqTaskNo === '__new__' ? '' : '')) ?>" placeholder="e.g. CUK-PAIR-001" required <?= ($activeTask && !$isNewTaskSelected) ? 'readonly' : '' ?>>
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
                 MODULE 2: PUBLICATIONS CRUD
                 ========================================================================= -->
            <div id="sec_publications" class="card workflow-card mb-4">
                <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white font-w700"><i class="fa fa-book me-2"></i>Publication Details</h5>
                    <?php if ($activeTask && canEditInstitute($prefix)): ?>
                    <button type="button" class="btn btn-light btn-sm font-w700" onclick="openAddPubModal()">
                        <i class="fa fa-plus text-success me-1"></i> Add Publication
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (!$activeTask): ?>
                        <div class="p-4 text-center text-muted">Select or save a Task ID context above to manage publications.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Publication Title</th>
                                        <th>Author(s)</th>
                                        <th>Journal</th>
                                        <th>DOI / Date</th>
                                        <th>Impact Factor</th>
                                        <th style="width: 100px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($taskPubs)): ?>
                                        <tr><td colspan="7" class="text-center text-muted py-4">No publications added yet for this Task ID.</td></tr>
                                    <?php else: ?>
                                        <?php $pNo = 1; foreach ($taskPubs as $pub): ?>
                                            <tr>
                                                <td><?= $pNo++ ?></td>
                                                <td><strong class="text-dark"><?= htmlspecialchars($pub['publication_title']) ?></strong></td>
                                                <td><?= htmlspecialchars($pub['author_name']) ?></td>
                                                <td><?= htmlspecialchars($pub['publication_journal']) ?></td>
                                                <td>
                                                    <small class="d-block text-dark"><?= $pub['doi_number'] ? 'DOI: '.htmlspecialchars($pub['doi_number']) : '—' ?></small>
                                                    <small class="text-muted"><?= htmlspecialchars($pub['publication_date'] ?? '') ?></small>
                                                </td>
                                                <td><span class="badge bg-light text-dark border"><?= $pub['impact_factor'] !== null ? $pub['impact_factor'] : '—' ?></span></td>
                                                <td style="text-align: center;">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" class="btn btn-warning btn-xs" onclick='openEditPubModal(<?= json_encode($pub, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                                                            <i class="fa fa-pencil"></i>
                                                        </button>
                                                        <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>" onsubmit="return confirm('Delete this publication entry?');" style="display:inline;">
                                                            <?= getCsrfInputField() ?>
                                                            <input type="hidden" name="form_type" value="delete_publication">
                                                            <input type="hidden" name="delete_id" value="<?= $pub['id'] ?>">
                                                            <input type="hidden" name="task_no" value="<?= htmlspecialchars($activeTask['task_no']) ?>">
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
                    <?php endif; ?>
                </div>
            </div>

            <!-- =========================================================================
                 MODULE 3A: WORKSHOPS / CONFERENCES CRUD
                 ========================================================================= -->
            <div id="sec_workshops" class="card workflow-card mb-4">
                <div class="card-header bg-info text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white font-w700"><i class="fa fa-users me-2"></i>Workshops / Conferences Conducted</h5>
                    <?php if ($activeTask && canEditInstitute($prefix)): ?>
                    <button type="button" class="btn btn-light btn-sm font-w700" onclick="openAddEventModal('Workshop_Conference')">
                        <i class="fa fa-plus text-info me-1"></i> Add Workshop / Conference
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (!$activeTask): ?>
                        <div class="p-4 text-center text-muted">Select or save a Task ID context above to manage events.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Title</th>
                                        <th>Date / Duration</th>
                                        <th>Venue / Mode</th>
                                        <th>Organizing Inst.</th>
                                        <th>Participants</th>
                                        <th style="width: 100px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($taskWorkshops)): ?>
                                        <tr><td colspan="7" class="text-center text-muted py-4">No workshops/conferences recorded yet for this Task ID.</td></tr>
                                    <?php else: ?>
                                        <?php $wNo = 1; foreach ($taskWorkshops as $ws): ?>
                                            <tr>
                                                <td><?= $wNo++ ?></td>
                                                <td><strong class="text-dark"><?= htmlspecialchars($ws['title']) ?></strong></td>
                                                <td><?= htmlspecialchars($ws['event_date'] ?? '—') ?></td>
                                                <td><?= htmlspecialchars($ws['venue_mode'] ?? '—') ?></td>
                                                <td><?= htmlspecialchars($ws['organizing_institution'] ?? '—') ?></td>
                                                <td><span class="badge bg-info text-white"><?= (int)$ws['participant_count'] ?></span></td>
                                                <td style="text-align: center;">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" class="btn btn-warning btn-xs" onclick='openEditEventModal(<?= json_encode($ws, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                                                            <i class="fa fa-pencil"></i>
                                                        </button>
                                                        <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>" onsubmit="return confirm('Delete this event?');" style="display:inline;">
                                                            <?= getCsrfInputField() ?>
                                                            <input type="hidden" name="form_type" value="delete_capacity_event">
                                                            <input type="hidden" name="delete_id" value="<?= $ws['id'] ?>">
                                                            <input type="hidden" name="task_no" value="<?= htmlspecialchars($activeTask['task_no']) ?>">
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
                    <?php endif; ?>
                </div>
            </div>

            <!-- =========================================================================
                 MODULE 3B: TRAINING PROGRAMS CRUD
                 ========================================================================= -->
            <div id="sec_trainings" class="card workflow-card mb-4">
                <div class="card-header bg-warning text-dark py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark font-w700"><i class="fa fa-chalkboard-teacher me-2"></i>Training Programs Conducted</h5>
                    <?php if ($activeTask && canEditInstitute($prefix)): ?>
                    <button type="button" class="btn btn-dark btn-sm font-w700" onclick="openAddEventModal('Training_Program')">
                        <i class="fa fa-plus me-1"></i> Add Training Program
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (!$activeTask): ?>
                        <div class="p-4 text-center text-muted">Select or save a Task ID context above to manage training programs.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Program Title</th>
                                        <th>Date / Duration</th>
                                        <th>Venue / Mode</th>
                                        <th>Organizing Inst.</th>
                                        <th>Participants</th>
                                        <th style="width: 100px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($taskTrainings)): ?>
                                        <tr><td colspan="7" class="text-center text-muted py-4">No training programs recorded yet for this Task ID.</td></tr>
                                    <?php else: ?>
                                        <?php $tNo = 1; foreach ($taskTrainings as $tr): ?>
                                            <tr>
                                                <td><?= $tNo++ ?></td>
                                                <td><strong class="text-dark"><?= htmlspecialchars($tr['title']) ?></strong></td>
                                                <td><?= htmlspecialchars($tr['event_date'] ?? '—') ?></td>
                                                <td><?= htmlspecialchars($tr['venue_mode'] ?? '—') ?></td>
                                                <td><?= htmlspecialchars($tr['organizing_institution'] ?? '—') ?></td>
                                                <td><span class="badge bg-warning text-dark"><?= (int)$tr['participant_count'] ?></span></td>
                                                <td style="text-align: center;">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" class="btn btn-warning btn-xs" onclick='openEditEventModal(<?= json_encode($tr, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                                                            <i class="fa fa-pencil"></i>
                                                        </button>
                                                        <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>" onsubmit="return confirm('Delete this training program?');" style="display:inline;">
                                                            <?= getCsrfInputField() ?>
                                                            <input type="hidden" name="form_type" value="delete_capacity_event">
                                                            <input type="hidden" name="delete_id" value="<?= $tr['id'] ?>">
                                                            <input type="hidden" name="task_no" value="<?= htmlspecialchars($activeTask['task_no']) ?>">
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
                    <?php endif; ?>
                </div>
            </div>

            <!-- =========================================================================
                 MODULE 3C: NUMBER OF INTERNS TRAINED
                 ========================================================================= -->
            <div id="sec_interns" class="card workflow-card mb-4">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 text-white font-w700"><i class="fa fa-user-graduate me-2"></i>Number of Interns Trained</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!$activeTask): ?>
                        <div class="text-muted">Select or save a Task ID context above to update interns trained count.</div>
                    <?php else: ?>
                        <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>" class="row align-items-center g-3">
                            <?= getCsrfInputField() ?>
                            <input type="hidden" name="form_type" value="save_interns_count">
                            <input type="hidden" name="progress_report_id" value="<?= $activeTask['id'] ?>">
                            <input type="hidden" name="task_no" value="<?= htmlspecialchars($activeTask['task_no']) ?>">

                            <div class="col-md-4">
                                <label class="form-label font-w700 text-dark">Number of Interns Trained *</label>
                                <input type="number" min="0" name="interns_trained_count" class="form-control form-control-lg font-w700 text-dark" value="<?= (int)($activeTask['interns_trained_count'] ?? 0) ?>" required>
                            </div>
                            <?php if (canEditInstitute($prefix)): ?>
                            <div class="col-md-4 align-self-end">
                                <button type="submit" class="btn btn-success btn-lg font-w700 px-4">
                                    <i class="fa fa-check me-1"></i> Update Intern Count
                                </button>
                            </div>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL: PUBLICATION FORM
     ========================================================================= -->
<div class="modal fade" id="publicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>">
                <?= getCsrfInputField() ?>
                <input type="hidden" name="form_type" value="save_publication">
                <input type="hidden" name="progress_report_id" id="modal_pub_pr_id" value="<?= htmlspecialchars($activeTask['id'] ?? '') ?>">
                <input type="hidden" name="task_no" id="modal_pub_task_no" value="<?= htmlspecialchars($activeTask['task_no'] ?? '') ?>">
                <input type="hidden" name="pub_id" id="modal_pub_id" value="">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white font-w700" id="pubModalTitle">Publication Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label font-w700 text-dark">Publication Title *</label>
                            <input type="text" name="publication_title" id="modal_pub_title" class="form-control" placeholder="Title of published paper/article" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-w600 text-dark">Author(s) *</label>
                            <input type="text" name="author_name" id="modal_pub_author" class="form-control" placeholder="Authors list" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-w600 text-dark">Journal Name *</label>
                            <input type="text" name="publication_journal" id="modal_pub_journal" class="form-control" placeholder="Journal name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-w600 text-dark">DOI Number</label>
                            <input type="text" name="doi_number" id="modal_pub_doi" class="form-control" placeholder="10.xxxx/xxxx">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-w600 text-dark">Publication Date</label>
                            <input type="date" name="publication_date" id="modal_pub_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-w600 text-dark">Impact Factor</label>
                            <input type="number" step="0.001" name="impact_factor" id="modal_pub_impact" class="form-control" placeholder="e.g. 4.520">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save me-1"></i> Save Publication</button>
                </div>
            </form>
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
                <input type="hidden" name="category" id="modal_evt_category" value="Workshop_Conference">

                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white font-w700" id="evtModalTitle">Capacity Building Event</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
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
let pubModalInstance = null;
let capacityEventModalInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    pubModalInstance = new bootstrap.Modal(document.getElementById('publicationModal'));
    capacityEventModalInstance = new bootstrap.Modal(document.getElementById('capacityEventModal'));
    toggleCapacitySubCheckboxes();
    toggleComponentUI();
});

function switchInstitute(prefixVal) {
    let urlParams = new URLSearchParams(window.location.search);
    urlParams.set('prefix', prefixVal);
    urlParams.delete('task_no');
    window.location.search = urlParams.toString();
}

function switchTaskId(taskNoVal) {
    let urlParams = new URLSearchParams(window.location.search);
    urlParams.set('task_no', taskNoVal);
    window.location.search = urlParams.toString();
}

function toggleCapacitySubCheckboxes() {
    let chkParent = document.getElementById('chk_capacity_building');
    let subWrapper = document.getElementById('capacity_sub_wrapper');
    if (chkParent && subWrapper) {
        if (chkParent.checked) {
            subWrapper.style.display = 'block';
        } else {
            subWrapper.style.display = 'none';
        }
    }
    toggleComponentUI();
}

function toggleComponentUI() {
    let showReport    = document.getElementById('chk_progress_report').checked;
    let showPubs      = document.getElementById('chk_publications').checked;
    let showCapParent = document.getElementById('chk_capacity_building').checked;

    let showWorkshops = showCapParent && document.getElementById('chk_workshops').checked;
    let showTrainings = showCapParent && document.getElementById('chk_trainings').checked;
    let showInterns   = showCapParent && document.getElementById('chk_interns').checked;

    document.getElementById('sec_progress_report').style.display = showReport ? 'block' : 'none';
    document.getElementById('sec_publications').style.display    = showPubs ? 'block' : 'none';
    document.getElementById('sec_workshops').style.display       = showWorkshops ? 'block' : 'none';
    document.getElementById('sec_trainings').style.display       = showTrainings ? 'block' : 'none';
    document.getElementById('sec_interns').style.display         = showInterns ? 'block' : 'none';
}

function openAddPubModal() {
    document.getElementById('modal_pub_id').value = '';
    document.getElementById('modal_pub_title').value = '';
    document.getElementById('modal_pub_author').value = '';
    document.getElementById('modal_pub_journal').value = '';
    document.getElementById('modal_pub_doi').value = '';
    document.getElementById('modal_pub_date').value = '';
    document.getElementById('modal_pub_impact').value = '';
    document.getElementById('pubModalTitle').innerText = 'Add Publication Details';
    pubModalInstance.show();
}

function openEditPubModal(p) {
    document.getElementById('modal_pub_id').value = p.id;
    document.getElementById('modal_pub_title').value = p.publication_title || '';
    document.getElementById('modal_pub_author').value = p.author_name || '';
    document.getElementById('modal_pub_journal').value = p.publication_journal || '';
    document.getElementById('modal_pub_doi').value = p.doi_number || '';
    document.getElementById('modal_pub_date').value = p.publication_date || '';
    document.getElementById('modal_pub_impact').value = p.impact_factor || '';
    document.getElementById('pubModalTitle').innerText = 'Edit Publication Details';
    pubModalInstance.show();
}

function openAddEventModal(cat) {
    document.getElementById('modal_evt_id').value = '';
    document.getElementById('modal_evt_category').value = cat;
    document.getElementById('modal_evt_title').value = '';
    document.getElementById('modal_evt_date').value = '';
    document.getElementById('modal_evt_duration').value = '';
    document.getElementById('modal_evt_participants').value = '0';
    document.getElementById('modal_evt_venue').value = '';
    document.getElementById('modal_evt_organizer').value = '';
    document.getElementById('modal_evt_desc').value = '';

    if (cat === 'Workshop_Conference') {
        document.getElementById('evtModalTitle').innerText = 'Add Workshop / Conference';
        document.getElementById('evtTitleLabel').innerText = 'Workshop / Conference Title *';
    } else {
        document.getElementById('evtModalTitle').innerText = 'Add Training Program';
        document.getElementById('evtTitleLabel').innerText = 'Training Program Title *';
    }
    capacityEventModalInstance.show();
}

function openEditEventModal(e) {
    document.getElementById('modal_evt_id').value = e.id;
    document.getElementById('modal_evt_category').value = e.category;
    document.getElementById('modal_evt_title').value = e.title || '';
    document.getElementById('modal_evt_date').value = e.event_date || '';
    document.getElementById('modal_evt_duration').value = e.duration || '';
    document.getElementById('modal_evt_participants').value = e.participant_count || 0;
    document.getElementById('modal_evt_venue').value = e.venue_mode || '';
    document.getElementById('modal_evt_organizer').value = e.organizing_institution || '';
    document.getElementById('modal_evt_desc').value = e.description || '';

    if (e.category === 'Workshop_Conference') {
        document.getElementById('evtModalTitle').innerText = 'Edit Workshop / Conference';
        document.getElementById('evtTitleLabel').innerText = 'Workshop / Conference Title *';
    } else {
        document.getElementById('evtModalTitle').innerText = 'Edit Training Program';
        document.getElementById('evtTitleLabel').innerText = 'Training Program Title *';
    }
    capacityEventModalInstance.show();
}
</script>
</body>
</html>
