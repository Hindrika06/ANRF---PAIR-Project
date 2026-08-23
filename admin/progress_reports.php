<?php
require_once 'auth_check.php';
require_once 'role_access.php';

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

$table = "{$prefix}_progress_reports";
$pubsTable = "{$prefix}_progress_report_publications";
$eventsTable = "{$prefix}_progress_report_capacity_events";

require_once 'config/db.php';

// Self-healing DB check for columns and child tables across active prefix
try {
    $checkCol = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'work_package_no'");
    if ($checkCol->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `work_package_no` VARCHAR(100) NULL AFTER `task_no`");
    }
    $checkInterns = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'interns_trained_count'");
    if ($checkInterns->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`");
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `$pubsTable` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `progress_report_id` INT NOT NULL,
            `task_no` VARCHAR(50) DEFAULT NULL,
            `publication_title` VARCHAR(500) NOT NULL,
            `author_name` VARCHAR(255) NOT NULL,
            `doi_number` VARCHAR(255) DEFAULT NULL,
            `publication_date` DATE DEFAULT NULL,
            `publication_journal` VARCHAR(300) NOT NULL,
            `impact_factor` DECIMAL(6,3) DEFAULT NULL,
            `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_pr_id` (`progress_report_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `$eventsTable` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `progress_report_id` INT NOT NULL,
            `category` ENUM('Workshop_Conference', 'Training_Program') NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `event_date` DATE DEFAULT NULL,
            `duration` VARCHAR(100) DEFAULT NULL,
            `venue_mode` VARCHAR(255) DEFAULT NULL,
            `organizing_institution` VARCHAR(255) DEFAULT NULL,
            `participant_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `description` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_pr_events_id` (`progress_report_id`),
            KEY `idx_category` (`category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
} catch (PDOException $e) {
    // Failsafe
}

$success = false;
$error   = '';

// 1. HANDLE MAIN PROGRESS REPORT DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deletePrefix = $_GET['record_prefix'] ?? $prefix;
    if (!isValidPrefix($deletePrefix) || !canEditInstitute($deletePrefix)) {
        $error = 'You are not allowed to delete records for this institute.';
    } else {
        try {
            $deleteTable = "{$deletePrefix}_progress_reports";
            $deletePubsTable = "{$deletePrefix}_progress_report_publications";
            $deleteEventsTable = "{$deletePrefix}_progress_report_capacity_events";
            $id = (int)$_GET['id'];

            $stmt = $pdo->prepare("DELETE FROM `$deleteTable` WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Clean up associated child records
            $stmt = $pdo->prepare("DELETE FROM `$deletePubsTable` WHERE progress_report_id = :id");
            $stmt->execute([':id' => $id]);

            $stmt = $pdo->prepare("DELETE FROM `$deleteEventsTable` WHERE progress_report_id = :id");
            $stmt->execute([':id' => $id]);

            adminRedirect(['success_msg' => 'deleted']);
        } catch (PDOException $e) {
            $error = 'Failed to delete record: ' . $e->getMessage();
        }
    }
}

// 2. HANDLE CHILD RECORD DELETIONS
if (isset($_GET['action']) && isset($_GET['sub_id'])) {
    $subAction = $_GET['action'];
    $subId = (int)$_GET['sub_id'];
    $recPrefix = $_GET['record_prefix'] ?? $prefix;

    if (!isValidPrefix($recPrefix) || !canEditInstitute($recPrefix)) {
        $error = 'Unauthorized operation for this institute.';
    } else {
        try {
            if ($subAction === 'delete_pub') {
                $targetTable = "{$recPrefix}_progress_report_publications";
                $stmt = $pdo->prepare("DELETE FROM `$targetTable` WHERE id = :id");
                $stmt->execute([':id' => $subId]);
                adminRedirect(['success_msg' => 'deleted_pub']);
            } elseif ($subAction === 'delete_capacity_event') {
                $targetTable = "{$recPrefix}_progress_report_capacity_events";
                $stmt = $pdo->prepare("DELETE FROM `$targetTable` WHERE id = :id");
                $stmt->execute([':id' => $subId]);
                adminRedirect(['success_msg' => 'deleted_event']);
            }
        } catch (PDOException $e) {
            $error = 'Failed to delete sub-record: ' . $e->getMessage();
        }
    }
}

// SHOW SUCCESS FLASH MESSAGES
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE ALL FORM SUBMISSIONS (MAIN REPORT, PUBLICATIONS, CAPACITY EVENTS, INTERNS COUNT)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? 'main_report';

    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to update records for this institute.';
    } else {
        try {
            // A. MAIN PROGRESS REPORT FORM SUBMISSION
            if ($formType === 'main_report') {
                $project_title         = trim($_POST['project_title'] ?? '');
                $pi_name               = trim($_POST['pi_name'] ?? '');
                $co_pi_name            = trim($_POST['co_pi_name'] ?? '');
                $task_no               = trim($_POST['task_no'] ?? '');
                $work_package_no       = trim($_POST['work_package_no'] ?? '');
                $approved_objects      = trim($_POST['approved_objects'] ?? '');
                $methodology           = trim($_POST['methodology'] ?? '');
                $summary_progress      = trim($_POST['summary_progress'] ?? '');
                $interns_trained_input = $_POST['interns_trained_count'] ?? '0';

                if ($interns_trained_input < 0 || !filter_var($interns_trained_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
                    throw new RuntimeException("Number of Interns Trained must be a valid non-negative integer (0 or greater).");
                }
                $interns_trained_count = (int)$interns_trained_input;

                $edit_id        = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;
                $is_super       = isSuperAdmin();
                $approvalStatus = $is_super ? 'Approved' : 'Pending';

                $payload = [
                    ':project_title'         => $project_title,
                    ':pi_name'               => $pi_name,
                    ':co_pi_name'            => $co_pi_name,
                    ':task_no'               => $task_no,
                    ':work_package_no'       => $work_package_no,
                    ':approved_objects'      => $approved_objects,
                    ':methodology'           => $methodology,
                    ':summary_progress'      => $summary_progress,
                    ':interns_trained_count' => $interns_trained_count,
                    ':approval_status'       => $approvalStatus
                ];

                if ($edit_id) {
                    $stmt = $pdo->prepare("
                        UPDATE `$table` SET
                            project_title = :project_title,
                            pi_name = :pi_name,
                            co_pi_name = :co_pi_name,
                            task_no = :task_no,
                            work_package_no = :work_package_no,
                            approved_objects = :approved_objects,
                            methodology = :methodology,
                            summary_progress = :summary_progress,
                            interns_trained_count = :interns_trained_count,
                            approval_status = :approval_status
                        WHERE id = :id
                    ");
                    $payload[':id'] = $edit_id;
                    $stmt->execute($payload);

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $edit_id, 'UPDATE', $payload);
                        adminRedirect(['success_msg' => 'submitted']);
                    } else {
                        adminRedirect(['success_msg' => 'updated']);
                    }
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO `$table`
                            (project_title, pi_name, co_pi_name, task_no, work_package_no,
                             approved_objects, methodology, summary_progress, interns_trained_count, approval_status, created_at)
                        VALUES
                            (:project_title, :pi_name, :co_pi_name, :task_no, :work_package_no,
                             :approved_objects, :methodology, :summary_progress, :interns_trained_count, :approval_status, NOW())
                    ");
                    $stmt->execute($payload);
                    $new_id = $pdo->lastInsertId();

                    if (!$is_super) {
                        submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $new_id, 'CREATE', $payload);
                        adminRedirect(['success_msg' => 'submitted']);
                    } else {
                        adminRedirect(['success_msg' => 'inserted']);
                    }
                }
            }

            // B. PUBLICATION DETAILS SUBMISSION (ATTACHED TO PROGRESS REPORT)
            elseif ($formType === 'publication_details') {
                $pr_id               = (int)($_POST['progress_report_id'] ?? 0);
                $pub_id              = !empty($_POST['pub_edit_id']) ? (int)$_POST['pub_edit_id'] : null;
                $pub_task_no         = trim($_POST['pub_task_no'] ?? '');
                $publication_title   = trim($_POST['publication_title'] ?? '');
                $author_name         = trim($_POST['author_name'] ?? '');
                $doi_number          = trim($_POST['doi_number'] ?? '');
                $publication_date    = !empty($_POST['publication_date']) ? $_POST['publication_date'] : null;
                $publication_journal = trim($_POST['publication_journal'] ?? '');
                $impact_factor       = ($_POST['impact_factor'] !== '') ? (float)$_POST['impact_factor'] : null;

                if ($pr_id <= 0) {
                    throw new RuntimeException("Invalid Progress Report reference.");
                }
                if (empty($publication_title)) {
                    throw new RuntimeException("Publication Title is required.");
                }
                if (empty($author_name)) {
                    throw new RuntimeException("Primary Author Name is required.");
                }
                if (empty($publication_journal)) {
                    throw new RuntimeException("Journal Name is required.");
                }

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
                        ':task_no'             => $pub_task_no,
                        ':publication_title'   => $publication_title,
                        ':author_name'         => $author_name,
                        ':doi_number'          => $doi_number,
                        ':publication_date'    => $publication_date,
                        ':publication_journal' => $publication_journal,
                        ':impact_factor'       => $impact_factor,
                        ':id'                  => $pub_id,
                        ':pr_id'               => $pr_id
                    ]);
                    adminRedirect(['success_msg' => 'pub_updated']);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO `$pubsTable`
                            (progress_report_id, task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor)
                        VALUES
                            (:pr_id, :task_no, :publication_title, :author_name, :doi_number, :publication_date, :publication_journal, :impact_factor)
                    ");
                    $stmt->execute([
                        ':pr_id'               => $pr_id,
                        ':task_no'             => $pub_task_no,
                        ':publication_title'   => $publication_title,
                        ':author_name'         => $author_name,
                        ':doi_number'          => $doi_number,
                        ':publication_date'    => $publication_date,
                        ':publication_journal' => $publication_journal,
                        ':impact_factor'       => $impact_factor
                    ]);
                    adminRedirect(['success_msg' => 'pub_inserted']);
                }
            }

            // C. CAPACITY BUILDING EVENT SUBMISSION (WORKSHOP / CONFERENCE OR TRAINING PROGRAM)
            elseif ($formType === 'capacity_building_event') {
                $pr_id                  = (int)($_POST['progress_report_id'] ?? 0);
                $event_id               = !empty($_POST['event_edit_id']) ? (int)$_POST['event_edit_id'] : null;
                $category               = in_array($_POST['category'] ?? '', ['Workshop_Conference', 'Training_Program']) ? $_POST['category'] : 'Workshop_Conference';
                $event_title            = trim($_POST['event_title'] ?? '');
                $event_date             = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
                $duration               = trim($_POST['duration'] ?? '');
                $venue_mode             = trim($_POST['venue_mode'] ?? '');
                $organizing_institution = trim($_POST['organizing_institution'] ?? '');
                $participant_input      = $_POST['participant_count'] ?? '0';
                $description            = trim($_POST['description'] ?? '');

                if ($pr_id <= 0) {
                    throw new RuntimeException("Invalid Progress Report reference.");
                }
                if (empty($event_title)) {
                    throw new RuntimeException("Event / Program Title is required.");
                }
                if ($participant_input < 0 || !filter_var($participant_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
                    throw new RuntimeException("Participant Count must be a valid non-negative integer (0 or greater).");
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
                    adminRedirect(['success_msg' => 'event_updated']);
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
                    adminRedirect(['success_msg' => 'event_inserted']);
                }
            }

            // D. UPDATE INTERNS TRAINED COUNT
            elseif ($formType === 'update_interns_count') {
                $pr_id                 = (int)($_POST['progress_report_id'] ?? 0);
                $interns_trained_input = $_POST['interns_trained_count'] ?? '0';

                if ($pr_id <= 0) {
                    throw new RuntimeException("Invalid Progress Report reference.");
                }
                if ($interns_trained_input < 0 || !filter_var($interns_trained_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
                    throw new RuntimeException("Number of Interns Trained must be a valid non-negative integer (0 or greater).");
                }
                $interns_trained_count = (int)$interns_trained_input;

                $stmt = $pdo->prepare("UPDATE `$table` SET interns_trained_count = :count WHERE id = :id");
                $stmt->execute([':count' => $interns_trained_count, ':id' => $pr_id]);
                adminRedirect(['success_msg' => 'interns_updated']);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// 4. FETCH CENTRALIZED PROGRESS REPORTS DATA & ATTACH CHILD RECORDS
$reports = [];
try {
    $reports = fetchCentralizedKpiDataset($pdo, 'progress_reports', $prefix, isSuperAdmin());
    usort($reports, function($a, $b) {
        return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
    });

    // Populate publications and capacity building events for each report
    foreach ($reports as &$rep) {
        $p = $rep['institute_prefix'] ?? $prefix;
        $prId = (int)$rep['id'];
        $pPubsTbl   = "{$p}_progress_report_publications";
        $pEventsTbl = "{$p}_progress_report_capacity_events";

        $rep['publications'] = [];
        $rep['workshops']    = [];
        $rep['trainings']    = [];

        try {
            $stmtPubs = $pdo->prepare("SELECT * FROM `$pPubsTbl` WHERE progress_report_id = :pr_id ORDER BY id DESC");
            $stmtPubs->execute([':pr_id' => $prId]);
            $rep['publications'] = $stmtPubs->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {}

        try {
            $stmtEvents = $pdo->prepare("SELECT * FROM `$pEventsTbl` WHERE progress_report_id = :pr_id ORDER BY id DESC");
            $stmtEvents->execute([':pr_id' => $prId]);
            $allEv = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allEv as $ev) {
                if ($ev['category'] === 'Workshop_Conference') {
                    $rep['workshops'][] = $ev;
                } else {
                    $rep['trainings'][] = $ev;
                }
            }
        } catch (Exception $ex) {}
    }
    unset($rep);

} catch (Exception $e) {
    $error = 'Could not load data records: ' . $e->getMessage();
}

$total_records = count($reports);
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

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
        color: #bc2121;
        text-decoration: none;
        display: inline-block;
        margin-bottom: 2px;
    }
    .sub-count-badge {
        font-size: 10.5px;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 600;
    }
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Progress Reports</a></li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Progress report information updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card registry-card">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="card-title text-dark font-w700 mb-0">Progress Reports Registry</h4>
                        <small class="text-muted">Manage progress updates, attached publications, and capacity building metrics.</small>
                    </div>
                    <?php if (canEditInstitute($prefix)): ?>
                    <button type="button" class="btn btn-primary btn-sm" id="addNewBtn" data-bs-toggle="modal" data-bs-target="#reportModal">
                        <i class="fa fa-plus me-1"></i> Add Progress Report
                    </button>
                    <?php endif; ?>
                </div>

                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-theme-sapphire align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center;">#</th>
                                    <th>Task No & Project Title</th>
                                    <th>PI / Co-PI</th>
                                    <th>Work Package</th>
                                    <th style="width: 220px;">Associated Sub-Records</th>
                                    <th style="width: 110px;">Date</th>
                                    <th style="width: 120px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4" style="font-size: 13px;">No progress reports submitted yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $sno = 1;
                                    foreach ($reports as $report):
                                        $pubCount = count($report['publications']);
                                        $wsCount  = count($report['workshops']);
                                        $trCount  = count($report['trainings']);
                                        $internCount = (int)($report['interns_trained_count'] ?? 0);
                                    ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="index-badge-circle"><?= $sno++ ?></span>
                                            </td>
                                            <td>
                                                <span class="registry-task-link">
                                                    <?= htmlspecialchars($report['task_no'] ?: 'TASK-UNASSIGNED') ?>
                                                </span>
                                                <span class="registry-main-title d-block font-w700 text-dark">
                                                    <?= htmlspecialchars($report['project_title'] ?: 'Untitled Project') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="registry-meta-text font-w600 text-dark d-block">
                                                    <strong>PI:</strong> <?= htmlspecialchars($report['pi_name'] ?: '—') ?>
                                                </span>
                                                <span class="registry-sub-label text-muted d-block small">
                                                    <strong>Co-PI:</strong> <?= htmlspecialchars($report['co_pi_name'] ?: '—') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="registry-meta-text">
                                                    <?= htmlspecialchars($report['work_package_no'] ?: '—') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <span class="sub-count-badge bg-primary text-white" title="Publications attached">
                                                        <i class="fa fa-book me-1"></i> Pubs: <?= $pubCount ?>
                                                    </span>
                                                    <span class="sub-count-badge bg-info text-white" title="Workshops & Conferences">
                                                        <i class="fa fa-users me-1"></i> Events: <?= ($wsCount + $trCount) ?>
                                                    </span>
                                                    <span class="sub-count-badge bg-success text-white" title="Interns Trained">
                                                        <i class="fa fa-user-graduate me-1"></i> Interns: <?= $internCount ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-dark font-w600" style="font-size: 12px;">
                                                    <?= date('d M Y', strtotime($report['created_at'])) ?>
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <!-- View & Manage Sub-records Button -->
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-primary text-white open-manage-modal-btn"
                                                            data-report="<?= htmlspecialchars(json_encode($report), ENT_QUOTES, 'UTF-8') ?>"
                                                            title="View Details & Manage Sections">
                                                        <i class="fa fa-folder-open me-1"></i> Manage
                                                    </button>
                                                    <?php if (canEditInstitute($report['institute_prefix'] ?? $prefix)): ?>
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
                                                            data-interns="<?= (int)($report['interns_trained_count'] ?? 0) ?>"
                                                            title="Edit Core Progress Report">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-delete-red delete-confirm-trigger"
                                                            data-id="<?= $report['id'] ?>"
                                                            data-record-prefix="<?= htmlspecialchars($report['institute_prefix'] ?? $prefix) ?>"
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
                        <p class="mb-0 text-muted small font-w500">Total: <?= $total_records ?> progress reports</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ── MAIN ADD / EDIT PROGRESS REPORT MODAL ────────────────────────────── -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="reportModalLabel">Project Progress Report Form</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="modalForm">
                <input type="hidden" name="form_type" value="main_report">
                <input type="hidden" name="edit_id" id="modal_edit_id">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Task No *</label>
                            <input type="text" name="task_no" id="modal_task_no" class="form-control" placeholder="e.g. TASK-001" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label font-weight-bold">Project Title *</label>
                            <input type="text" name="project_title" id="modal_project_title" class="form-control" placeholder="Full project title" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Principal Investigator (PI) Name *</label>
                            <input type="text" name="pi_name" id="modal_pi_name" class="form-control" placeholder="PI name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Co-Principal Investigator (Co-PI) Name</label>
                            <input type="text" name="co_pi_name" id="modal_co_pi_name" class="form-control" placeholder="Co-PI name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Work Package Number</label>
                            <input type="text" name="work_package_no" id="modal_work_package_no" class="form-control" placeholder="e.g. WP-001">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Number of Interns Trained</label>
                            <input type="number" name="interns_trained_count" id="modal_interns_trained_count" class="form-control" min="0" value="0" placeholder="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Approved Objectives / Targets</label>
                        <textarea name="approved_objects" id="modal_approved_objects" rows="3" class="form-control" placeholder="List approved objectives"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Methodology / Approach Used</label>
                        <textarea name="methodology" id="modal_methodology" rows="3" class="form-control" placeholder="Describe methodology"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Summary of the Progress</label>
                        <textarea name="summary_progress" id="modal_summary_progress" rows="4" class="form-control" placeholder="Summarize progress made"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── COMPREHENSIVE VIEW & MANAGE DETAILS MODAL (PUBLICATIONS & CAPACITY BUILDING) ── -->
<div class="modal fade" id="manageReportDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title text-white mb-0" id="manageModalTitle">Progress Report & Associated Sections</h5>
                    <small class="text-light opacity-75" id="manageModalSubtitle">Task & Project Details</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Overview Cards -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-primary mb-2"><i class="fa fa-info-circle me-1"></i> Core Report Overview</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block">PI Name:</small>
                                <strong id="detPiName" class="text-dark">—</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Co-PI Name:</small>
                                <strong id="detCoPiName" class="text-dark">—</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Work Package:</small>
                                <strong id="detWpNo" class="text-dark">—</strong>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Approved Objectives:</small>
                                <div id="detObjects" class="small text-dark text-break">—</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Methodology:</small>
                                <div id="detMethodology" class="small text-dark text-break">—</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Summary Progress:</small>
                                <div id="detProgress" class="small text-dark text-break">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- ══════════════════════════════════════════════════════
                     SECTION: PUBLICATIONS
                ══════════════════════════════════════════════════════ -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa fa-book text-danger me-2"></i> PUBLICATIONS</h5>
                    <?php if (canEditInstitute($prefix)): ?>
                    <button type="button" class="btn btn-danger btn-sm" onclick="openAddPubModal()">
                        <i class="fa fa-plus me-1"></i> Add Publication Details
                    </button>
                    <?php endif; ?>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Task No</th>
                                <th>Publication Title</th>
                                <th>Primary Author</th>
                                <th>Journal Name</th>
                                <th>DOI / Date</th>
                                <th>Impact Factor</th>
                                <th style="width: 100px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="detPubsTableBody">
                            <!-- Rendered dynamically -->
                        </tbody>
                    </table>
                </div>

                <hr class="my-4">

                <!-- ══════════════════════════════════════════════════════
                     SECTION: CAPACITY BUILDING
                ══════════════════════════════════════════════════════ -->
                <h5 class="fw-bold text-dark mb-3"><i class="fa fa-chalkboard-user text-primary me-2"></i> CAPACITY BUILDING</h5>

                <!-- A. Workshops / Conferences Conducted -->
                <div class="card mb-4 border border-light">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="fw-bold mb-0 text-dark">A. Workshops / Conferences Conducted</h6>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddEventModal('Workshop_Conference')">
                            <i class="fa fa-plus me-1"></i> Add Workshop / Conference
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Date</th>
                                        <th>Venue / Mode</th>
                                        <th>Organizing Institution</th>
                                        <th>Participants</th>
                                        <th>Description</th>
                                        <th style="width: 90px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="detWorkshopsTableBody">
                                    <!-- Rendered dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- B. Training Programs Conducted -->
                <div class="card mb-4 border border-light">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h6 class="fw-bold mb-0 text-dark">B. Training Programs Conducted</h6>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddEventModal('Training_Program')">
                            <i class="fa fa-plus me-1"></i> Add Training Program
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Date / Duration</th>
                                        <th>Venue / Mode</th>
                                        <th>Participants</th>
                                        <th>Description</th>
                                        <th style="width: 90px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="detTrainingsTableBody">
                                    <!-- Rendered dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- C. Number of Interns Trained -->
                <div class="card border border-light bg-light">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">C. Number of Interns Trained</h6>
                            <small class="text-muted">Total number of students and researchers trained during this progress report period.</small>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-success fs-5 px-3 py-2" id="detInternsBadge">0</span>
                            <?php if (canEditInstitute($prefix)): ?>
                            <button type="button" class="btn btn-sm btn-outline-dark" onclick="openUpdateInternsModal()">
                                <i class="fa fa-pencil me-1"></i> Edit Count
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ── SUB-MODAL 1: ADD / EDIT PUBLICATION DETAILS ──────────────────────── -->
<div class="modal fade" id="pubModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="pubModalTitle">Add Publication Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="form_type" value="publication_details">
                <input type="hidden" name="progress_report_id" id="pub_pr_id">
                <input type="hidden" name="pub_edit_id" id="pub_edit_id">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Task Number</label>
                            <input type="text" name="pub_task_no" id="pub_task_no" class="form-control" placeholder="e.g. TASK-001">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label font-weight-bold">Publication Title *</label>
                            <input type="text" name="publication_title" id="pub_title" class="form-control" placeholder="Full paper title" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Primary Author Name *</label>
                            <input type="text" name="author_name" id="pub_author" class="form-control" placeholder="Author name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Journal Name *</label>
                            <input type="text" name="publication_journal" id="pub_journal" class="form-control" placeholder="e.g. IEEE Transactions on Biomedical Engineering" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">DOI Number</label>
                            <input type="text" name="doi_number" id="pub_doi" class="form-control" placeholder="10.1016/j.bspc.2026.105432">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Publication Date</label>
                            <input type="date" name="publication_date" id="pub_date" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Impact Factor</label>
                            <input type="number" step="0.001" min="0" name="impact_factor" id="pub_impact" class="form-control" placeholder="e.g. 4.750">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fa fa-save me-1"></i> Save Publication</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── SUB-MODAL 2: ADD / EDIT CAPACITY BUILDING EVENT ──────────────────── -->
<div class="modal fade" id="capacityEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="capacityEventModalTitle">Add Capacity Building Event</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="form_type" value="capacity_building_event">
                <input type="hidden" name="progress_report_id" id="event_pr_id">
                <input type="hidden" name="event_edit_id" id="event_edit_id">
                <input type="hidden" name="category" id="event_category" value="Workshop_Conference">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold" id="eventTitleLabel">Title *</label>
                        <input type="text" name="event_title" id="event_title" class="form-control" placeholder="Name of workshop, conference, or training program" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Date</label>
                            <input type="date" name="event_date" id="event_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Duration / Timings</label>
                            <input type="text" name="duration" id="event_duration" class="form-control" placeholder="e.g. 2 Days / 10 AM - 4 PM">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Venue / Mode</label>
                            <input type="text" name="venue_mode" id="event_venue_mode" class="form-control" placeholder="e.g. Online / Auditorium B, UoH">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Organizing Institution</label>
                            <input type="text" name="organizing_institution" id="event_organizer" class="form-control" placeholder="e.g. ANRF-PAIR Project & Dept of Physics">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Number of Participants</label>
                        <input type="number" name="participant_count" id="event_participants" class="form-control" min="0" value="0" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Brief Description / Outcome</label>
                        <textarea name="description" id="event_description" rows="3" class="form-control" placeholder="Brief summary of event objectives and participant outcomes..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Save Event Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── SUB-MODAL 3: UPDATE INTERNS TRAINED COUNT ───────────────────────── -->
<div class="modal fade" id="internsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title text-white" id="internsModalTitle">Number of Interns Trained</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="form_type" value="update_interns_count">
                <input type="hidden" name="progress_report_id" id="interns_pr_id">

                <div class="modal-body py-3">
                    <label class="form-label font-weight-bold">Number of Interns Trained *</label>
                    <input type="number" name="interns_trained_count" id="interns_count_input" class="form-control" min="0" required placeholder="0">
                    <small class="text-muted d-block mt-1">Must be a valid non-negative integer.</small>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save me-1"></i> Save Count</button>
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

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>

<script>
var activeReportData = null;
var pubModalInstance = null;
var capacityEventModalInstance = null;
var internsModalInstance = null;
var manageReportDetailsModalInstance = null;

document.addEventListener("DOMContentLoaded", function() {

    const manageModalEl = document.getElementById('manageReportDetailsModal');
    if (manageModalEl) manageReportDetailsModalInstance = new bootstrap.Modal(manageModalEl);

    const pubModalEl = document.getElementById('pubModal');
    if (pubModalEl) pubModalInstance = new bootstrap.Modal(pubModalEl);

    const capModalEl = document.getElementById('capacityEventModal');
    if (capModalEl) capacityEventModalInstance = new bootstrap.Modal(capModalEl);

    const intModalEl = document.getElementById('internsModal');
    if (intModalEl) internsModalInstance = new bootstrap.Modal(intModalEl);

    // OPEN MANAGE DETAILS MODAL
    const openManageBtns = document.querySelectorAll('.open-manage-modal-btn');
    openManageBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.dataset.report) return;
            activeReportData = JSON.parse(this.dataset.report);
            renderManageModal(activeReportData);
            if (manageReportDetailsModalInstance) manageReportDetailsModalInstance.show();
        });
    });

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
            modalTitle.innerText = "Edit Progress Report";
            modalSubmitBtn.style.display = "block";
            modalForm.querySelectorAll('input, textarea, select').forEach(el => {
                el.disabled = false;
                el.readOnly = false;
            });

            document.getElementById('modal_edit_id').value = this.getAttribute('data-id');
            document.getElementById('modal_project_title').value = this.getAttribute('data-title');
            document.getElementById('modal_pi_name').value = this.getAttribute('data-pi');
            document.getElementById('modal_co_pi_name').value = this.getAttribute('data-copi');
            document.getElementById('modal_task_no').value = this.getAttribute('data-task');
            document.getElementById('modal_work_package_no').value = this.getAttribute('data-wp');
            document.getElementById('modal_approved_objects').value = this.getAttribute('data-objects');
            document.getElementById('modal_methodology').value = this.getAttribute('data-methodology');
            document.getElementById('modal_summary_progress').value = this.getAttribute('data-summary');
            document.getElementById('modal_interns_trained_count').value = this.getAttribute('data-interns') || 0;
        });
    });

    deleteTriggers.forEach(triggerBtn => {
        triggerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const recordId = this.getAttribute('data-id');
            const recordPrefix = this.getAttribute('data-record-prefix') || this.getAttribute('data-prefix');
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('action', 'delete');
            urlParams.set('id', recordId);
            if (recordPrefix) {
                urlParams.set('record_prefix', recordPrefix);
            }
            modalDeleteExecutionLink.setAttribute('href', '?' + urlParams.toString());
            bootstrapDeleteInstance.show();
        });
    });
});

function renderManageModal(rep) {
    document.getElementById('manageModalTitle').innerText = (rep.task_no ? rep.task_no + ': ' : '') + (rep.project_title || 'Progress Report');
    document.getElementById('manageModalSubtitle').innerText = 'PI: ' + (rep.pi_name || '—') + ' | Institute: ' + (rep.institute_prefix || '').toUpperCase();

    document.getElementById('detPiName').innerText = rep.pi_name || '—';
    document.getElementById('detCoPiName').innerText = rep.co_pi_name || '—';
    document.getElementById('detWpNo').innerText = rep.work_package_no || '—';
    document.getElementById('detObjects').innerText = rep.approved_objects || '—';
    document.getElementById('detMethodology').innerText = rep.methodology || '—';
    document.getElementById('detProgress').innerText = rep.summary_progress || '—';

    // RENDER PUBLICATIONS
    const pubTbody = document.getElementById('detPubsTableBody');
    pubTbody.innerHTML = '';
    const pubs = rep.publications || [];

    if (pubs.length === 0) {
        pubTbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No publication details added yet.</td></tr>';
    } else {
        pubs.forEach(p => {
            var tr = document.createElement('tr');
            tr.innerHTML = `
                <td><small class="fw-bold">${p.task_no || '—'}</small></td>
                <td><strong>${escapeHtml(p.publication_title)}</strong></td>
                <td>${escapeHtml(p.author_name)}</td>
                <td>${escapeHtml(p.publication_journal)}</td>
                <td><small class="d-block">${p.doi_number ? 'DOI: ' + escapeHtml(p.doi_number) : ''}</small><small class="text-muted">${p.publication_date || ''}</small></td>
                <td><span class="badge bg-light text-dark border">${p.impact_factor ? p.impact_factor : '—'}</span></td>
                <td style="text-align: center;">
                    ${ (rep.can_edit !== false) ? `
                        <button class="btn btn-warning btn-xs me-1" onclick='openEditPubModal(${JSON.stringify(p)})'><i class="fa fa-pencil"></i></button>
                        <a href="?action=delete_pub&sub_id=${p.id}&record_prefix=${rep.institute_prefix || ''}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this publication entry?');"><i class="fa fa-trash"></i></a>
                    ` : '<span class="text-muted small"><i class="fa fa-lock"></i></span>' }
                </td>
            `;
            pubTbody.appendChild(tr);
        });
    }

    // RENDER WORKSHOPS / CONFERENCES
    const wsTbody = document.getElementById('detWorkshopsTableBody');
    wsTbody.innerHTML = '';
    const workshops = rep.workshops || [];

    if (workshops.length === 0) {
        wsTbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No workshops or conferences recorded yet.</td></tr>';
    } else {
        workshops.forEach(w => {
            var tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${escapeHtml(w.title)}</strong></td>
                <td><small>${w.event_date || '—'}</small></td>
                <td>${escapeHtml(w.venue_mode || '—')}</td>
                <td>${escapeHtml(w.organizing_institution || '—')}</td>
                <td><span class="badge bg-info text-white">${w.participant_count || 0}</span></td>
                <td><small class="text-muted">${escapeHtml(w.description || '—')}</small></td>
                <td style="text-align: center;">
                    ${ (rep.can_edit !== false) ? `
                        <button class="btn btn-warning btn-xs me-1" onclick='openEditEventModal(${JSON.stringify(w)})'><i class="fa fa-pencil"></i></button>
                        <a href="?action=delete_capacity_event&sub_id=${w.id}&record_prefix=${rep.institute_prefix || ''}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this workshop entry?');"><i class="fa fa-trash"></i></a>
                    ` : '<span class="text-muted small"><i class="fa fa-lock"></i></span>' }
                </td>
            `;
            wsTbody.appendChild(tr);
        });
    }

    // RENDER TRAINING PROGRAMS
    const trTbody = document.getElementById('detTrainingsTableBody');
    trTbody.innerHTML = '';
    const trainings = rep.trainings || [];

    if (trainings.length === 0) {
        trTbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No training programs recorded yet.</td></tr>';
    } else {
        trainings.forEach(t => {
            var tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${escapeHtml(t.title)}</strong></td>
                <td><small>${t.event_date || ''} ${t.duration ? '(' + escapeHtml(t.duration) + ')' : ''}</small></td>
                <td>${escapeHtml(t.venue_mode || '—')}</td>
                <td><span class="badge bg-info text-white">${t.participant_count || 0}</span></td>
                <td><small class="text-muted">${escapeHtml(t.description || '—')}</small></td>
                <td style="text-align: center;">
                    ${ (rep.can_edit !== false) ? `
                        <button class="btn btn-warning btn-xs me-1" onclick='openEditEventModal(${JSON.stringify(t)})'><i class="fa fa-pencil"></i></button>
                        <a href="?action=delete_capacity_event&sub_id=${t.id}&record_prefix=${rep.institute_prefix || ''}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this training program entry?');"><i class="fa fa-trash"></i></a>
                    ` : '<span class="text-muted small"><i class="fa fa-lock"></i></span>' }
                </td>
            `;
            trTbody.appendChild(tr);
        });
    }

    // RENDER INTERNS TRAINED COUNT
    document.getElementById('detInternsBadge').innerText = rep.interns_trained_count || 0;
}

function openAddPubModal() {
    if (!activeReportData) return;
    document.getElementById('pub_pr_id').value = activeReportData.id;
    document.getElementById('pub_edit_id').value = '';
    document.getElementById('pub_task_no').value = activeReportData.task_no || '';
    document.getElementById('pub_title').value = '';
    document.getElementById('pub_author').value = activeReportData.pi_name || '';
    document.getElementById('pub_journal').value = '';
    document.getElementById('pub_doi').value = '';
    document.getElementById('pub_date').value = '';
    document.getElementById('pub_impact').value = '';
    document.getElementById('pubModalTitle').innerText = 'Add Publication Details';

    if (pubModalInstance) pubModalInstance.show();
}

function openEditPubModal(p) {
    document.getElementById('pub_pr_id').value = p.progress_report_id;
    document.getElementById('pub_edit_id').value = p.id;
    document.getElementById('pub_task_no').value = p.task_no || '';
    document.getElementById('pub_title').value = p.publication_title || '';
    document.getElementById('pub_author').value = p.author_name || '';
    document.getElementById('pub_journal').value = p.publication_journal || '';
    document.getElementById('pub_doi').value = p.doi_number || '';
    document.getElementById('pub_date').value = p.publication_date || '';
    document.getElementById('pub_impact').value = p.impact_factor || '';
    document.getElementById('pubModalTitle').innerText = 'Edit Publication Details';

    if (pubModalInstance) pubModalInstance.show();
}

function openAddEventModal(cat) {
    if (!activeReportData) return;
    document.getElementById('event_pr_id').value = activeReportData.id;
    document.getElementById('event_edit_id').value = '';
    document.getElementById('event_category').value = cat;
    document.getElementById('event_title').value = '';
    document.getElementById('event_date').value = '';
    document.getElementById('event_duration').value = '';
    document.getElementById('event_venue_mode').value = '';
    document.getElementById('event_organizer').value = '';
    document.getElementById('event_participants').value = '0';
    document.getElementById('event_description').value = '';

    if (cat === 'Workshop_Conference') {
        document.getElementById('capacityEventModalTitle').innerText = 'Add Workshop / Conference';
        document.getElementById('eventTitleLabel').innerText = 'Workshop / Conference Name *';
    } else {
        document.getElementById('capacityEventModalTitle').innerText = 'Add Training Program';
        document.getElementById('eventTitleLabel').innerText = 'Training Program Name *';
    }

    if (capacityEventModalInstance) capacityEventModalInstance.show();
}

function openEditEventModal(e) {
    document.getElementById('event_pr_id').value = e.progress_report_id;
    document.getElementById('event_edit_id').value = e.id;
    document.getElementById('event_category').value = e.category;
    document.getElementById('event_title').value = e.title || '';
    document.getElementById('event_date').value = e.event_date || '';
    document.getElementById('event_duration').value = e.duration || '';
    document.getElementById('event_venue_mode').value = e.venue_mode || '';
    document.getElementById('event_organizer').value = e.organizing_institution || '';
    document.getElementById('event_participants').value = e.participant_count || 0;
    document.getElementById('event_description').value = e.description || '';

    if (e.category === 'Workshop_Conference') {
        document.getElementById('capacityEventModalTitle').innerText = 'Edit Workshop / Conference';
        document.getElementById('eventTitleLabel').innerText = 'Workshop / Conference Name *';
    } else {
        document.getElementById('capacityEventModalTitle').innerText = 'Edit Training Program';
        document.getElementById('eventTitleLabel').innerText = 'Training Program Name *';
    }

    if (capacityEventModalInstance) capacityEventModalInstance.show();
}

function openUpdateInternsModal() {
    if (!activeReportData) return;
    document.getElementById('interns_pr_id').value = activeReportData.id;
    document.getElementById('interns_count_input').value = activeReportData.interns_trained_count || 0;
    if (internsModalInstance) internsModalInstance.show();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
</body>
</html>
