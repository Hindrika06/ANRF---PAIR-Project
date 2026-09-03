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

// 1. UNIVERSITY CONTEXT & AUTHENTICATION GUARD (STRICT SERVER-SIDE AUTHORIZATION)
$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);
$allowedPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

if (!in_array($prefix, $allowedPrefixes, true) || !canEditInstitute($prefix)) {
    header("Location: dashboard.php");
    exit();
}

// Ensure database schema is up-to-date
ensureProgressReportsSchema($pdo);

// 2. AJAX SEARCH ENDPOINT FOR KPI SELECTION MODALS (CURRENT INSTITUTE ONLY)
if (isset($_GET['action']) && $_GET['action'] === 'ajax_search_kpi') {
    header('Content-Type: application/json');
    $category = $_GET['category'] ?? '';
    $q        = $_GET['q'] ?? '';
    // Enforces candidate discovery strictly within current authorized institute ($prefix) across ALL Task IDs
    $results  = searchKpiCategoryRecords($pdo, $prefix, $category, $q);
    echo json_encode(['status' => 'success', 'data' => $results]);
    exit();
}

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg   = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// 3. FETCH ALL EXISTING REPORTS FOR THIS INSTITUTE
$allReports = getAllInstituteProgressReports($pdo, $prefix);

$selectedReportId = 0;
if (isset($_GET['report_id'])) {
    $selectedReportId = (int)$_GET['report_id'];
} elseif (isset($_GET['action']) && $_GET['action'] === 'new') {
    $selectedReportId = 0;
} elseif (!empty($allReports)) {
    $selectedReportId = (int)$allReports[0]['id'];
}

// 4. POST FORM PROCESSING (SAVE, SUBMIT, APPROVE, REJECT, DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';

    if (in_array($action, ['save_report', 'submit_approval', 'approve_report', 'reject_report'], true)) {
        $reportId   = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
        $projTitle  = trim($_POST['project_title'] ?? '');
        $piName     = trim($_POST['pi_name'] ?? '');
        $coPiName   = trim($_POST['co_pi_name'] ?? '');
        $workPkg    = trim($_POST['work_package_no'] ?? '');
        $taskNo     = trim($_POST['task_no'] ?? '');
        $appObjects = trim($_POST['approved_objects'] ?? '');
        $method     = trim($_POST['methodology'] ?? '');
        $sumProg    = trim($_POST['summary_progress'] ?? '');

        // Selected KPI IDs (JSON format strings)
        $selPubsStr  = trim($_POST['selected_publication_ids'] ?? '[]');
        $selConfsStr = trim($_POST['selected_conference_ids'] ?? '[]');
        $selWebsStr  = trim($_POST['selected_webinar_ids'] ?? '[]');
        $selIntsStr  = trim($_POST['selected_internship_ids'] ?? '[]');
        $selPatsStr  = trim($_POST['selected_patent_ids'] ?? '[]');
        $selWshopsStr= trim($_POST['selected_workshop_ids'] ?? '[]');
        $selTrainsStr= trim($_POST['selected_training_ids'] ?? '[]');

        // Validate JSON
        $selPubs  = json_decode($selPubsStr, true) ?: [];
        $selConfs = json_decode($selConfsStr, true) ?: [];
        $selWebs  = json_decode($selWebsStr, true) ?: [];
        $selInts  = json_decode($selIntsStr, true) ?: [];
        $selPats  = json_decode($selPatsStr, true) ?: [];
        $selWshops= json_decode($selWshopsStr, true) ?: [];
        $selTrains= json_decode($selTrainsStr, true) ?: [];

        // Checkbox toggles
        $incPubs     = !empty($selPubs) ? 1 : 0;
        $incConfs    = !empty($selConfs) ? 1 : 0;
        $incWebs     = !empty($selWebs) ? 1 : 0;
        $incInts     = !empty($selInts) ? 1 : 0;
        $incPats     = !empty($selPats) ? 1 : 0;

        $incWshops   = isset($_POST['include_workshops']) ? 1 : 0;
        $incTrain    = isset($_POST['include_training']) ? 1 : 0;
        $incInterns  = isset($_POST['include_interns']) ? 1 : 0;
        $incCapacity = ($incWshops || $incTrain || $incInterns) ? 1 : 0;

        $internsCount = isset($_POST['interns_trained_count']) ? max(0, (int)$_POST['interns_trained_count']) : 0;

        if ($projTitle === '') {
            $_SESSION['error_msg'] = 'Project Title is required.';
            adminRedirect(['report_id' => $reportId]);
        }
        if ($piName === '') {
            $_SESSION['error_msg'] = 'Principal Investigator (PI) Name is required.';
            adminRedirect(['report_id' => $reportId]);
        }

        $table = "{$prefix}_progress_reports";

        // Fetch existing status
        $existingStatus = 'Pending';
        if ($reportId > 0) {
            try {
                $chkStmt = $pdo->prepare("SELECT approval_status FROM `$table` WHERE id = ?");
                $chkStmt->execute([$reportId]);
                $existingStatus = $chkStmt->fetchColumn() ?: 'Pending';
            } catch (Exception $e) {}
        }

        // STRICT ROLE PERMISSION GUARD
        if (!isSuperAdmin()) {
            // Normal Admin: CAN NEVER APPROVE DIRECTLY OR MARK APPROVED VIA POST MANIPULATION
            $approvalStatus = 'Pending';
        } else {
            // Super Admin: Moderation logic
            if ($action === 'approve_report') {
                $approvalStatus = 'Approved';
            } elseif ($action === 'reject_report') {
                $approvalStatus = 'Rejected';
            } else {
                $approvalStatus = $existingStatus ?: 'Approved';
            }
        }

        try {
            $params = [
                ':project_title'            => $projTitle,
                ':pi_name'                  => $piName,
                ':co_pi_name'               => $coPiName,
                ':task_no'                  => $taskNo,
                ':work_package_no'          => $workPkg,
                ':approved_objects'         => $appObjects,
                ':methodology'              => $method,
                ':summary_progress'         => $sumProg,
                ':selected_publication_ids' => json_encode(array_values(array_map('intval', $selPubs))),
                ':selected_conference_ids'  => json_encode(array_values(array_map('intval', $selConfs))),
                ':selected_webinar_ids'     => json_encode(array_values(array_map('intval', $selWebs))),
                ':selected_internship_ids'  => json_encode(array_values(array_map('intval', $selInts))),
                ':selected_patent_ids'      => json_encode(array_values(array_map('intval', $selPats))),
                ':selected_workshop_ids'    => json_encode(array_values(array_map('intval', $selWshops))),
                ':selected_training_ids'    => json_encode(array_values(array_map('intval', $selTrains))),
                ':include_publications'     => $incPubs,
                ':include_conferences'      => $incConfs,
                ':include_webinars'         => $incWebs,
                ':include_internships'      => $incInts,
                ':include_patents'          => $incPats,
                ':include_capacity_building' => $incCapacity,
                ':include_workshops'        => $incWshops,
                ':include_training'         => $incTrain,
                ':include_interns'          => $incInterns,
                ':interns_trained_count'    => $internsCount,
                ':approval_status'          => $approvalStatus,
            ];

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
                            `selected_publication_ids` = :selected_publication_ids,
                            `selected_conference_ids`  = :selected_conference_ids,
                            `selected_webinar_ids`     = :selected_webinar_ids,
                            `selected_internship_ids`  = :selected_internship_ids,
                            `selected_patent_ids`      = :selected_patent_ids,
                            `selected_workshop_ids`    = :selected_workshop_ids,
                            `selected_training_ids`    = :selected_training_ids,
                            `include_publications`     = :include_publications,
                            `include_conferences`      = :include_conferences,
                            `include_webinars`         = :include_webinars,
                            `include_internships`      = :include_internships,
                            `include_patents`          = :include_patents,
                            `include_capacity_building` = :include_capacity_building,
                            `include_workshops`        = :include_workshops,
                            `include_training`         = :include_training,
                            `include_interns`          = :include_interns,
                            `interns_trained_count`    = :interns_trained_count,
                            `approval_status`          = :approval_status
                        WHERE `id` = :id";
                $params[':id'] = $reportId;
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                if (!isSuperAdmin() && ($action === 'submit_approval' || $action === 'save_report')) {
                    submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $reportId, 'UPDATE', [
                        'task_no' => $taskNo,
                        'project_title' => $projTitle,
                        'approval_status' => 'Pending'
                    ]);
                }

                if ($action === 'approve_report' && isSuperAdmin()) {
                    $_SESSION['success_msg'] = "Progress Report #{$reportId} approved successfully.";
                } elseif ($action === 'reject_report' && isSuperAdmin()) {
                    $_SESSION['success_msg'] = "Progress Report #{$reportId} rejected.";
                } elseif ($action === 'submit_approval') {
                    $_SESSION['success_msg'] = "Progress Report #{$reportId} submitted for approval successfully.";
                } else {
                    $_SESSION['success_msg'] = "Progress Report #{$reportId} saved successfully.";
                }

                adminRedirect(['report_id' => $reportId]);
            } else {
                // Insert new report
                $sql = "INSERT INTO `$table` (
                            `project_title`, `pi_name`, `co_pi_name`, `task_no`, `work_package_no`,
                            `approved_objects`, `methodology`, `summary_progress`,
                            `selected_publication_ids`, `selected_conference_ids`, `selected_webinar_ids`,
                            `selected_internship_ids`, `selected_patent_ids`, `selected_workshop_ids`, `selected_training_ids`,
                            `include_publications`, `include_conferences`, `include_webinars`, `include_internships`, `include_patents`,
                            `include_capacity_building`, `include_workshops`, `include_training`, `include_interns`,
                            `interns_trained_count`, `approval_status`, `created_at`
                        ) VALUES (
                            :project_title, :pi_name, :co_pi_name, :task_no, :work_package_no,
                            :approved_objects, :methodology, :summary_progress,
                            :selected_publication_ids, :selected_conference_ids, :selected_webinar_ids,
                            :selected_internship_ids, :selected_patent_ids, :selected_workshop_ids, :selected_training_ids,
                            :include_publications, :include_conferences, :include_webinars, :include_internships, :include_patents,
                            :include_capacity_building, :include_workshops, :include_training, :include_interns,
                            :interns_trained_count, :approval_status, NOW()
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $newId = (int)$pdo->lastInsertId();

                if (!isSuperAdmin()) {
                    submitKpiApprovalRequest($pdo, 'Progress Reports', $table, $prefix, $newId, 'INSERT', [
                        'task_no' => $taskNo,
                        'project_title' => $projTitle,
                        'approval_status' => 'Pending'
                    ]);
                }

                if ($action === 'submit_approval') {
                    $_SESSION['success_msg'] = "Progress Report created and submitted for approval (Report #{$newId}).";
                } else {
                    $_SESSION['success_msg'] = "Progress Report draft created (Report #{$newId}).";
                }
                adminRedirect(['report_id' => $newId]);
            }
        } catch (Exception $e) {
            $_SESSION['error_msg'] = "Database error saving report: " . $e->getMessage();
            adminRedirect(['report_id' => $reportId]);
        }
    } elseif ($action === 'delete_report' && isSuperAdmin()) {
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
        adminRedirect(['action' => 'new']);
    }
}

// 5. LOAD ACTIVE REPORT DATA FOR DISPLAY
$activeReport = null;
if ($selectedReportId > 0) {
    foreach ($allReports as $r) {
        if ((int)$r['id'] === $selectedReportId) {
            $activeReport = $r;
            break;
        }
    }
    if (!$activeReport) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `{$prefix}_progress_reports` WHERE id = ?");
            $stmt->execute([$selectedReportId]);
            $activeReport = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
}

// Form Values
$valProjectTitle = $activeReport['project_title'] ?? '';
$valPiName       = $activeReport['pi_name'] ?? '';
$valCoPiName     = $activeReport['co_pi_name'] ?? '';
$valWorkPkg      = $activeReport['work_package_no'] ?? '';
$valTaskNo       = $activeReport['task_no'] ?? '';
$valAppObjects   = $activeReport['approved_objects'] ?? '';
$valMethodology  = $activeReport['methodology'] ?? '';
$valSumProgress  = $activeReport['summary_progress'] ?? '';
$reportStatus    = $activeReport['approval_status'] ?? 'Pending';

// Selected ID arrays
$pubIds   = json_decode($activeReport['selected_publication_ids'] ?? '[]', true) ?: [];
$confIds  = json_decode($activeReport['selected_conference_ids'] ?? '[]', true) ?: [];
$webIds   = json_decode($activeReport['selected_webinar_ids'] ?? '[]', true) ?: [];
$intIds   = json_decode($activeReport['selected_internship_ids'] ?? '[]', true) ?: [];
$patIds   = json_decode($activeReport['selected_patent_ids'] ?? '[]', true) ?: [];
$wshopIds = json_decode($activeReport['selected_workshop_ids'] ?? '[]', true) ?: [];
$trainIds = json_decode($activeReport['selected_training_ids'] ?? '[]', true) ?: [];

// Checkboxes
$chkWshops   = isset($activeReport) ? !empty($activeReport['include_workshops']) : false;
$chkTraining = isset($activeReport) ? !empty($activeReport['include_training']) : false;
$chkInterns  = isset($activeReport) ? !empty($activeReport['include_interns']) : false;
$valInternsCount = (int)($activeReport['interns_trained_count'] ?? 0);

// Fetch ONLY explicitly selected full objects for rendering on main form
$selPubRows   = getKpiCategoryRecordsByIds($pdo, $prefix, 'publications', $pubIds);
$selConfRows  = getKpiCategoryRecordsByIds($pdo, $prefix, 'conferences', $confIds);
$selWebRows   = getKpiCategoryRecordsByIds($pdo, $prefix, 'webinars', $webIds);
$selIntRows   = getKpiCategoryRecordsByIds($pdo, $prefix, 'internships', $intIds);
$selPatRows   = getKpiCategoryRecordsByIds($pdo, $prefix, 'patents', $patIds);
$selWshopRows = getKpiCategoryRecordsByIds($pdo, $prefix, 'workshops', $wshopIds);
$selTrainRows = getKpiCategoryRecordsByIds($pdo, $prefix, 'trainings', $trainIds);

$instituteFullName = getInstituteFullName($prefix);

// ROLE-BASED STRICT THEME COLOR DEFINITION
$isSuper = isSuperAdmin();
$primaryThemeColor = $isSuper ? '#024283' : '#bc2121';
$btnThemeClass     = $isSuper ? 'btn-primary' : 'btn-theme-red';
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
.btn-theme-red {
    background-color: #bc2121 !important;
    border-color: #bc2121 !important;
    color: #ffffff !important;
}
.btn-theme-red:hover, .btn-theme-red:focus {
    background-color: #a01b1b !important;
    border-color: #a01b1b !important;
    color: #ffffff !important;
}
.btn-outline-theme-red {
    border-color: #bc2121 !important;
    color: #bc2121 !important;
}
.btn-outline-theme-red:hover {
    background-color: #bc2121 !important;
    color: #ffffff !important;
}
.progress-report-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    overflow: hidden;
}
.kpi-row-header {
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
/* ── EQUAL SIZE MINIMAL KPI SELECTION BUTTONS ── */
.btn-kpi-select,
button.btn-kpi-select {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 175px !important;
    width: 175px !important;
    height: 38px !important;
    padding: 0 14px !important;
    font-size: 0.825rem !important;
    font-weight: 600 !important;
    letter-spacing: 0.01em !important;
    border-radius: 8px !important;
    white-space: nowrap !important;
    text-align: center !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    background-color: #0856a4 !important;
    border: 1px solid #0856a4 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(8, 86, 164, 0.2) !important;
}
.btn-kpi-select:hover,
button.btn-kpi-select:hover {
    background-color: #064380 !important;
    border-color: #064380 !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(8, 86, 164, 0.35) !important;
}
.btn-kpi-select i {
    margin-right: 8px !important;
    font-size: 0.8rem !important;
    display: inline-block !important;
}

/* ── EXPERT UI DESIGN SYSTEM FOR KPI SELECTION MODAL ── */
#kpiSelectionModal .modal-content {
    border: none !important;
    border-radius: 16px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    overflow: hidden !important;
    background-color: #FFFFFF !important;
}

#kpiSelectionModal .modal-header {
    background: #bc2121 !important;
    padding: 1.25rem 1.5rem !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

#kpiSelectionModal .modal-title {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    letter-spacing: -0.01em !important;
    color: #FFFFFF !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
}

#kpiSelectionModal .btn-close {
    filter: brightness(0) invert(1) !important;
    opacity: 0.8 !important;
    transition: opacity 0.15s ease !important;
}

#kpiSelectionModal .btn-close:hover {
    opacity: 1 !important;
}

#kpiSelectionModal .kpi-search-group {
    border-radius: 10px !important;
    overflow: hidden !important;
    border: 1.5px solid #CBD5E1 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
    transition: all 0.2s ease-in-out !important;
    background-color: #FFFFFF !important;
}

#kpiSelectionModal .kpi-search-group:focus-within {
    border-color: #bc2121 !important;
    box-shadow: 0 0 0 3px rgba(188, 33, 33, 0.15) !important;
}

#kpiSelectionModal #kpiSearchInput {
    border: none !important;
    box-shadow: none !important;
    font-size: 0.925rem !important;
    color: #0F172A !important;
    background-color: #FFFFFF !important;
    padding: 0.65rem 1rem !important;
}

#kpiSelectionModal #kpiSearchInput::placeholder {
    color: #94A3B8 !important;
}

#kpiSelectionModal .kpi-search-btn {
    background-color: #bc2121 !important;
    border: none !important;
    color: #FFFFFF !important;
    font-weight: 600 !important;
    padding: 0.65rem 1.4rem !important;
    font-size: 0.875rem !important;
    transition: background-color 0.15s ease-in-out !important;
}

#kpiSelectionModal .kpi-search-btn:hover {
    background-color: #9f1a1a !important;
    color: #FFFFFF !important;
}

/* SEARCH ICON COLOR CONTROL */
#kpiSelectionModal i.fa-search,
#kpiSelectionModal .empty-search-icon {
    color: #bc2121 !important;
}

#kpiSelectionModal .kpi-candidate-item {
    border: 1px solid #E2E8F0 !important;
    border-radius: 10px !important;
    margin-bottom: 8px !important;
    padding: 0.9rem 1.1rem !important;
    background-color: #FFFFFF !important;
    transition: all 0.15s ease-in-out !important;
    cursor: pointer !important;
    user-select: none !important;
}

#kpiSelectionModal .kpi-candidate-item:hover {
    background-color: #F8FAFC !important;
    border-color: #CBD5E1 !important;
}

#kpiSelectionModal .kpi-candidate-item.is-selected {
    background-color: #FEF2F2 !important;
    border-color: #FCA5A5 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

#kpiSelectionModal .modal-kpi-checkbox {
    width: 1.15rem !important;
    height: 1.15rem !important;
    margin-top: 0.15rem !important;
    cursor: pointer !important;
    accent-color: #bc2121 !important;
}

#kpiSelectionModal .task-badge-pill {
    background-color: #F1F5F9 !important;
    color: #334155 !important;
    border: 1px solid #CBD5E1 !important;
    font-weight: 600 !important;
    font-size: 0.725rem !important;
    padding: 0.2rem 0.55rem !important;
    border-radius: 6px !important;
}

#kpiSelectionModal #kpiSelectedCountBadge {
    font-size: 0.825rem !important;
    font-weight: 600 !important;
    padding: 0.5rem 1rem !important;
    border-radius: 20px !important;
    letter-spacing: 0.01em !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.4rem !important;
    transition: all 0.2s ease-in-out !important;
}

#kpiSelectionModal #kpiSelectedCountBadge.badge-zero {
    background-color: #F1F5F9 !important;
    color: #64748B !important;
    border: 1px solid #E2E8F0 !important;
}

#kpiSelectionModal #kpiSelectedCountBadge.badge-active {
    background-color: #FEE2E2 !important;
    color: #bc2121 !important;
    border: 1px solid #FCA5A5 !important;
    font-weight: 700 !important;
}

#kpiSelectionModal .modal-footer {
    padding: 1rem 1.5rem !important;
    background-color: #F8FAFC !important;
    border-top: 1px solid #E2E8F0 !important;
}

#kpiSelectionModal .btn-kpi-cancel,
#kpiSelectionModal button.btn-kpi-cancel {
    background-color: #FFFFFF !important;
    color: #475569 !important;
    border: 1.5px solid #CBD5E1 !important;
    font-weight: 600 !important;
    padding: 0.55rem 1.35rem !important;
    border-radius: 8px !important;
    font-size: 0.875rem !important;
    transition: all 0.15s ease-in-out !important;
}

#kpiSelectionModal .btn-kpi-cancel:hover,
#kpiSelectionModal button.btn-kpi-cancel:hover {
    background-color: #F1F5F9 !important;
    color: #0F172A !important;
    border-color: #94A3B8 !important;
}

#kpiSelectionModal .btn-kpi-confirm,
#kpiSelectionModal button.btn-kpi-confirm {
    background-color: #bc2121 !important;
    color: #FFFFFF !important;
    border: none !important;
    font-weight: 600 !important;
    padding: 0.55rem 1.5rem !important;
    border-radius: 8px !important;
    font-size: 0.875rem !important;
    box-shadow: 0 2px 4px rgba(188, 33, 33, 0.25) !important;
    transition: all 0.15s ease-in-out !important;
}

#kpiSelectionModal .btn-kpi-confirm:hover,
#kpiSelectionModal button.btn-kpi-confirm:hover {
    background-color: #9f1a1a !important;
    color: #FFFFFF !important;
    box-shadow: 0 4px 10px rgba(188, 33, 33, 0.35) !important;
}

/* ── BOTTOM ACTION BUTTONS ── */
.btn-action-draft {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 42px !important;
    min-width: 150px !important;
    padding: 0 22px !important;
    font-size: 0.88rem !important;
    font-weight: 600 !important;
    background: #ffffff !important;
    color: #475569 !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 8px !important;
    transition: all 0.2s ease !important;
}
.btn-action-draft:hover {
    background: #f8fafc !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}
.btn-action-submit {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 42px !important;
    min-width: 190px !important;
    padding: 0 22px !important;
    font-size: 0.88rem !important;
    font-weight: 600 !important;
    background: #09BD3C !important;
    color: #ffffff !important;
    border: 1.5px solid #09BD3C !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(9, 189, 60, 0.25) !important;
    transition: all 0.2s ease !important;
}
.btn-action-submit:hover {
    background: #07a033 !important;
    border-color: #07a033 !important;
    color: #ffffff !important;
    box-shadow: 0 6px 16px rgba(9, 189, 60, 0.35) !important;
    transform: translateY(-1px) !important;
}
.btn-action-save-super {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 42px !important;
    min-width: 155px !important;
    padding: 0 20px !important;
    font-size: 0.88rem !important;
    font-weight: 600 !important;
    background: #024283 !important;
    color: #ffffff !important;
    border: 1.5px solid #024283 !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(2, 66, 131, 0.22) !important;
    transition: all 0.2s ease !important;
}
.btn-action-save-super:hover {
    background: #0856a4 !important;
    border-color: #0856a4 !important;
    color: #ffffff !important;
    box-shadow: 0 6px 16px rgba(8, 86, 164, 0.32) !important;
    transform: translateY(-1px) !important;
}
.btn-action-approve-super {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 42px !important;
    min-width: 140px !important;
    padding: 0 20px !important;
    font-size: 0.88rem !important;
    font-weight: 600 !important;
    background: #09BD3C !important;
    color: #ffffff !important;
    border: 1.5px solid #09BD3C !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(9, 189, 60, 0.22) !important;
    transition: all 0.2s ease !important;
}
.btn-action-approve-super:hover {
    background: #07a033 !important;
    border-color: #07a033 !important;
    color: #ffffff !important;
    box-shadow: 0 6px 16px rgba(9, 189, 60, 0.32) !important;
    transform: translateY(-1px) !important;
}
.btn-action-reject-super {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 42px !important;
    min-width: 140px !important;
    padding: 0 20px !important;
    font-size: 0.88rem !important;
    font-weight: 600 !important;
    background: #bc2121 !important;
    color: #ffffff !important;
    border: 1.5px solid #bc2121 !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(188, 33, 33, 0.22) !important;
    transition: all 0.2s ease !important;
}
.btn-action-reject-super:hover {
    background: #a01b1b !important;
    border-color: #a01b1b !important;
    color: #ffffff !important;
    box-shadow: 0 6px 16px rgba(160, 27, 27, 0.32) !important;
    transform: translateY(-1px) !important;
}
.btn-action-super {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 42px !important;
    min-width: 140px !important;
    padding: 0 20px !important;
    font-size: 0.88rem !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    transition: all 0.2s ease !important;
}
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">
            <?php include 'institute_banner.php'; ?>

            <!-- Notifications -->
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <strong>Success:</strong> <?= htmlspecialchars($success_msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>Error:</strong> <?= htmlspecialchars($error_msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ONE SINGLE COMPACT FORM CARD FOR THE ENTIRE PROGRESS REPORT -->
            <div class="card progress-report-card shadow-sm mb-5" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                <div class="card-header py-3" style="background: <?= $isSuper ? 'linear-gradient(135deg, #024283, #0856a4)' : '#bc2121' ?> !important; border-bottom: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title text-white mb-0 font-weight-bold" style="font-size: 1.05rem; color: #ffffff !important;">
                            <i class="fas fa-file-alt me-2"></i> Progress Report Details
                        </h4>
                        <div>
                            <?php if ($reportStatus === 'Approved'): ?>
                                <span class="badge rounded-pill px-3 py-2 ms-1" style="background-color: #dcfce7 !important; color: #166534 !important; font-size: 12px; font-weight: 700; border: 1px solid #bbf7d0;"><i class="fas fa-check-circle me-1"></i> Approved</span>
                            <?php elseif ($reportStatus === 'Rejected'): ?>
                                <span class="badge rounded-pill px-3 py-2 ms-1" style="background-color: #fee2e2 !important; color: #991b1b !important; font-size: 12px; font-weight: 700; border: 1px solid #fecaca;"><i class="fas fa-times-circle me-1"></i> Rejected</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>" id="progressReportForm">
                        <?= getCsrfInputField() ?>
                        <input type="hidden" name="report_id" value="<?= $selectedReportId ?>">

                        <!-- REPORT DETAILS -->
                        <h5 class="font-weight-bold mb-3 border-bottom pb-2" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                            <i class="fas fa-info-circle me-2" style="color: <?= $primaryThemeColor ?>;"></i> Report Details
                        </h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-dark mb-1">Project Title <span class="text-danger">*</span></label>
                                <input type="text" name="project_title" class="form-control" required placeholder="Enter complete project title..." value="<?= htmlspecialchars($valProjectTitle) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label font-weight-bold text-dark mb-1">Work Package</label>
                                <input type="text" name="work_package_no" class="form-control" placeholder="e.g. Work Package 1" value="<?= htmlspecialchars($valWorkPkg) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label font-weight-bold text-dark mb-1">Principal Investigator (PI) <span class="text-danger">*</span></label>
                                <input type="text" name="pi_name" class="form-control" required placeholder="Enter PI Name..." value="<?= htmlspecialchars($valPiName) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label font-weight-bold text-dark mb-1">Co-PI</label>
                                <input type="text" name="co_pi_name" class="form-control" placeholder="Enter Co-PI Name..." value="<?= htmlspecialchars($valCoPiName) ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label font-weight-bold text-dark mb-1">Task ID <small class="text-muted">(Text metadata)</small></label>
                                <input type="text" name="task_no" class="form-control" placeholder="e.g. Task 4.5" value="<?= htmlspecialchars($valTaskNo) ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-weight-bold text-dark mb-1">Approved Objectives</label>
                            <textarea name="approved_objects" class="form-control" rows="3" placeholder="Enter approved project objectives..."><?= htmlspecialchars($valAppObjects) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-weight-bold text-dark mb-1">Methodology</label>
                            <textarea name="methodology" class="form-control" rows="3" placeholder="Describe the research methodology adopted..."><?= htmlspecialchars($valMethodology) ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label font-weight-bold text-dark mb-1">Summary of Progress</label>
                            <textarea name="summary_progress" class="form-control" rows="4" placeholder="Provide a summary of progress achieved..."><?= htmlspecialchars($valSumProgress) ?></textarea>
                        </div>

                        <hr class="my-4">

                        <!-- KPI SELECTION -->
                        <h5 class="font-weight-bold mb-3 border-bottom pb-2" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                            <i class="fas fa-chart-line me-2" style="color: <?= $primaryThemeColor ?>;"></i> Key Performance Indicators
                        </h5>

                        <!-- Publications -->
                        <div class="mb-4 border rounded bg-white" style="border-radius: 10px !important; border-color: #e2e8f0 !important; overflow: hidden;">
                            <div class="d-flex justify-content-between align-items-center p-3 kpi-row-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <label class="form-label font-weight-bold text-dark mb-0 fs-6">Publications</label>
                                <button type="button" class="btn <?= $btnThemeClass ?> btn-kpi-select" onclick="openKpiModal('publications')">
                                    <i class="fas fa-plus me-2" style="font-size: 0.8rem; margin-right: 8px !important;"></i> Select Publications
                                </button>
                            </div>
                            <input type="hidden" name="selected_publication_ids" id="selected_publication_ids" value='<?= json_encode($pubIds) ?>'>
                            <div id="selected_publications_display" class="p-3"></div>
                        </div>

                        <!-- Conferences -->
                        <div class="mb-4 border rounded bg-white" style="border-radius: 10px !important; border-color: #e2e8f0 !important; overflow: hidden;">
                            <div class="d-flex justify-content-between align-items-center p-3 kpi-row-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <label class="form-label font-weight-bold text-dark mb-0 fs-6">Conferences</label>
                                <button type="button" class="btn <?= $btnThemeClass ?> btn-kpi-select" onclick="openKpiModal('conferences')">
                                    <i class="fas fa-plus me-2" style="font-size: 0.8rem; margin-right: 8px !important;"></i> Select Conferences
                                </button>
                            </div>
                            <input type="hidden" name="selected_conference_ids" id="selected_conference_ids" value='<?= json_encode($confIds) ?>'>
                            <div id="selected_conferences_display" class="p-3"></div>
                        </div>

                        <!-- Webinars -->
                        <div class="mb-4 border rounded bg-white" style="border-radius: 10px !important; border-color: #e2e8f0 !important; overflow: hidden;">
                            <div class="d-flex justify-content-between align-items-center p-3 kpi-row-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <label class="form-label font-weight-bold text-dark mb-0 fs-6">Webinars</label>
                                <button type="button" class="btn <?= $btnThemeClass ?> btn-kpi-select" onclick="openKpiModal('webinars')">
                                    <i class="fas fa-plus me-2" style="font-size: 0.8rem; margin-right: 8px !important;"></i> Select Webinars
                                </button>
                            </div>
                            <input type="hidden" name="selected_webinar_ids" id="selected_webinar_ids" value='<?= json_encode($webIds) ?>'>
                            <div id="selected_webinars_display" class="p-3"></div>
                        </div>

                        <!-- Internships -->
                        <div class="mb-4 border rounded bg-white" style="border-radius: 10px !important; border-color: #e2e8f0 !important; overflow: hidden;">
                            <div class="d-flex justify-content-between align-items-center p-3 kpi-row-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <label class="form-label font-weight-bold text-dark mb-0 fs-6">Internships</label>
                                <button type="button" class="btn <?= $btnThemeClass ?> btn-kpi-select" onclick="openKpiModal('internships')">
                                    <i class="fas fa-plus me-2" style="font-size: 0.8rem; margin-right: 8px !important;"></i> Select Internships
                                </button>
                            </div>
                            <input type="hidden" name="selected_internship_ids" id="selected_internship_ids" value='<?= json_encode($intIds) ?>'>
                            <div id="selected_internships_display" class="p-3"></div>
                        </div>

                        <!-- Patents -->
                        <div class="mb-4 border rounded bg-white" style="border-radius: 10px !important; border-color: #e2e8f0 !important; overflow: hidden;">
                            <div class="d-flex justify-content-between align-items-center p-3 kpi-row-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <label class="form-label font-weight-bold text-dark mb-0 fs-6">Patents</label>
                                <button type="button" class="btn <?= $btnThemeClass ?> btn-kpi-select" onclick="openKpiModal('patents')">
                                    <i class="fas fa-plus me-2" style="font-size: 0.8rem; margin-right: 8px !important;"></i> Select Patents
                                </button>
                            </div>
                            <input type="hidden" name="selected_patent_ids" id="selected_patent_ids" value='<?= json_encode($patIds) ?>'>
                            <div id="selected_patents_display" class="p-3"></div>
                        </div>

                        <hr class="my-4">

                        <!-- CAPACITY BUILDING -->
                        <h5 class="font-weight-bold mb-3 border-bottom pb-2" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                            <i class="fas fa-graduation-cap me-2" style="color: <?= $primaryThemeColor ?>;"></i> Capacity Building
                        </h5>

                        <div class="mb-3 border rounded p-3 bg-white" style="border-radius: 10px !important; border-color: #e2e8f0 !important; background: #ffffff !important; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="include_workshops" id="chkWorkshops" value="1" <?= $chkWshops ? 'checked' : '' ?> onchange="toggleCapacitySection('workshopsGroup', this.checked)">
                                <label class="form-check-label font-weight-bold text-dark me-2" for="chkWorkshops" style="cursor: pointer;">
                                    Workshops / Conferences Conducted
                                </label>
                            </div>
                            <div id="workshopsGroup" class="ms-4 mt-2" style="display: <?= $chkWshops ? 'block' : 'none' ?>;">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <span class="text-muted small">Select workshop/conference records conducted under capacity building:</span>
                                    <button type="button" class="btn <?= $btnThemeClass ?> btn-kpi-select" onclick="openKpiModal('workshops')">
                                        <i class="fas fa-plus me-2" style="font-size: 0.8rem; margin-right: 8px !important;"></i> Select Workshops
                                    </button>
                                </div>
                                <input type="hidden" name="selected_workshop_ids" id="selected_workshop_ids" value='<?= json_encode($wshopIds) ?>'>
                                <div id="selected_workshops_display" class="mt-2"></div>
                            </div>
                        </div>

                        <div class="mb-3 border rounded p-3 bg-white" style="border-radius: 10px !important; border-color: #e2e8f0 !important; background: #ffffff !important; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="include_training" id="chkTraining" value="1" <?= $chkTraining ? 'checked' : '' ?> onchange="toggleCapacitySection('trainingGroup', this.checked)">
                                <label class="form-check-label font-weight-bold text-dark me-2" for="chkTraining" style="cursor: pointer;">
                                    Training Programs Conducted
                                </label>
                            </div>
                            <div id="trainingGroup" class="ms-4 mt-2" style="display: <?= $chkTraining ? 'block' : 'none' ?>;">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <span class="text-muted small">Select training program / webinar records conducted:</span>
                                    <button type="button" class="btn <?= $btnThemeClass ?> btn-kpi-select" onclick="openKpiModal('trainings')">
                                        <i class="fas fa-plus me-2" style="font-size: 0.8rem; margin-right: 8px !important;"></i> Select Trainings
                                    </button>
                                </div>
                                <input type="hidden" name="selected_training_ids" id="selected_training_ids" value='<?= json_encode($trainIds) ?>'>
                                <div id="selected_trainings_display" class="mt-2"></div>
                            </div>
                        </div>

                        <div class="mb-4 border rounded p-3 bg-white" style="border-radius: 10px !important; border-color: #e2e8f0 !important; background: #ffffff !important; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="include_interns" id="chkInterns" value="1" <?= $chkInterns ? 'checked' : '' ?> onchange="toggleCapacitySection('internsGroup', this.checked)">
                                <label class="form-check-label font-weight-bold text-dark me-2" for="chkInterns" style="cursor: pointer;">
                                    Number of Interns Trained
                                </label>
                            </div>
                            <div id="internsGroup" class="ms-4 mt-2" style="display: <?= $chkInterns ? 'block' : 'none' ?>;">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <label class="form-label font-weight-bold text-dark mb-0">Number of Interns Trained:</label>
                                    </div>
                                    <div class="col-auto">
                                        <input type="number" min="0" name="interns_trained_count" class="form-control font-weight-bold" style="width: 150px; border-radius: 6px;" value="<?= $valInternsCount ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- FORM ACTIONS AT BOTTOM OF THE ONE FORM CARD -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <?php if ($selectedReportId > 0 && isSuperAdmin()): ?>
                                    <button type="button" class="btn btn-outline-danger btn-action-super" onclick="confirmDeleteReport();">
                                        <i class="fas fa-trash-alt me-1.5"></i> Delete Report
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-3 flex-wrap align-items-center">
                                <?php if (!isSuperAdmin()): ?>
                                    <!-- SPOKE ADMIN BUTTONS -->
                                    <button type="submit" name="action" value="submit_approval" class="btn btn-action-submit">
                                        <i class="fas fa-paper-plane me-2"></i> Submit for Approval
                                    </button>
                                <?php else: ?>
                                    <!-- SUPER ADMIN / HUB ADMIN BUTTONS -->
                                    <button type="submit" name="action" value="save_report" class="btn btn-action-save-super">
                                        <i class="fas fa-save me-2"></i> Save Changes
                                    </button>
                                    <?php if ($reportStatus !== 'Approved'): ?>
                                        <button type="submit" name="action" value="approve_report" class="btn btn-action-approve-super">
                                            <i class="fas fa-check-circle me-2"></i> Approve
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($reportStatus !== 'Rejected'): ?>
                                        <button type="submit" name="action" value="reject_report" class="btn btn-action-reject-super">
                                            <i class="fas fa-times-circle me-2"></i> Reject
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($reportStatus === 'Approved' && $selectedReportId > 0): ?>
                                        <a href="<?= buildNavUrl('export_progress_report_pdf.php') ?>&id=<?= $selectedReportId ?>" target="_blank" class="btn btn-primary btn-action-super">
                                            <i class="fas fa-file-pdf me-2"></i> Export PDF
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <?php if ($selectedReportId > 0 && isSuperAdmin()): ?>
                <form method="POST" action="<?= buildNavUrl('progress_reports.php') ?>" id="deleteReportForm" style="display:none;">
                    <?= getCsrfInputField() ?>
                    <input type="hidden" name="action" value="delete_report">
                    <input type="hidden" name="report_id" value="<?= $selectedReportId ?>">
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- REUSABLE KPI SELECTION MODAL -->
<div class="modal fade" id="kpiSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kpiModalTitle">
                    <i class="fas fa-list me-2"></i> Select Records
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Search Box -->
                <div class="input-group kpi-search-group mb-4">
                    <input type="text" id="kpiSearchInput" class="form-control" placeholder="Search records by title, PI, or keywords..." onkeyup="if(event.key === 'Enter') performKpiSearch();">
                    <button class="btn kpi-search-btn" type="button" onclick="performKpiSearch()">
                        <i class="fas fa-search me-1.5"></i> Search
                    </button>
                </div>

                <!-- Record List Container -->
                <div id="kpiModalList" class="list-group" style="max-height: 380px; overflow-y: auto;">
                    <div class="text-center p-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Loading records...</div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <div>
                    <span id="kpiSelectedCountBadge" class="badge badge-zero">0 selected</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-kpi-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-kpi-confirm" onclick="confirmKpiSelection()">Add Selected</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// INITIAL DATA FROM SERVER (EXPLICITLY SELECTED ITEMS ONLY)
var currentPrefix = <?= json_encode($prefix) ?>;
var initialData = {
    publications: <?= json_encode($selPubRows) ?>,
    conferences: <?= json_encode($selConfRows) ?>,
    webinars: <?= json_encode($selWebRows) ?>,
    internships: <?= json_encode($selIntRows) ?>,
    patents: <?= json_encode($selPatRows) ?>,
    workshops: <?= json_encode($selWshopRows) ?>,
    trainings: <?= json_encode($selTrainRows) ?>
};

// GLOBAL SELECTION STATE (dictionary of item objects keyed by category)
var selectedItemsMap = {
    publications: {},
    conferences: {},
    webinars: {},
    internships: {},
    patents: {},
    workshops: {},
    trainings: {}
};

// Initialize selectedItemsMap strictly from explicitly saved server data
for (var cat in initialData) {
    if (initialData.hasOwnProperty(cat) && Array.isArray(initialData[cat])) {
        initialData[cat].forEach(function(row) {
            selectedItemsMap[cat][row.id] = formatRowObj(cat, row);
        });
    }
}

function formatRowObj(cat, r) {
    var title = '', subtitle = '', task_no = r.task_no || r.taskno || '';
    if (cat === 'publications') {
        title = r.publication_title || 'Untitled Publication';
        var parts = [];
        if (r.author_name) parts.push("Author: " + r.author_name);
        if (r.publication_journal) parts.push("Journal: " + r.publication_journal);
        if (r.publication_date) parts.push("Date: " + r.publication_date);
        subtitle = parts.join(" | ");
    } else if (cat === 'conferences' || cat === 'workshops') {
        title = r.title || 'Untitled Event';
        var parts = [];
        if (r.organisers || r.organizer) parts.push("Organizer: " + (r.organisers || r.organizer));
        if (r.conf_date || r.start_date) parts.push("Date: " + (r.conf_date || r.start_date));
        if (r.institute) parts.push("Venue: " + r.institute);
        subtitle = parts.join(" | ");
    } else if (cat === 'webinars' || cat === 'trainings') {
        title = r.title || 'Untitled Webinar / Training';
        var parts = [];
        if (r.speaker_name) parts.push("Speaker: " + r.speaker_name);
        if (r.webinar_date) parts.push("Date: " + r.webinar_date);
        subtitle = parts.join(" | ");
    } else if (cat === 'internships') {
        title = r.title || 'Untitled Internship';
        var parts = [];
        if (r.project_investigator) parts.push("PI: " + r.project_investigator);
        if (r.no_students_trained) parts.push("Interns: " + r.no_students_trained);
        subtitle = parts.join(" | ");
    } else if (cat === 'patents') {
        title = r.patent_title || 'Untitled Patent';
        var parts = [];
        if (r.inventor_name) parts.push("Inventor: " + r.inventor_name);
        if (r.patent_number) parts.push("Patent No: " + r.patent_number);
        subtitle = parts.join(" | ");
    }
    return { id: parseInt(r.id), title: title, subtitle: subtitle, task_no: task_no };
}

// RENDER DISPLAY ON LOAD (NO UNSELECTED OR AUTOMATIC DATA RENDERED)
document.addEventListener('DOMContentLoaded', function() {
    ['publications', 'conferences', 'webinars', 'internships', 'patents', 'workshops', 'trainings'].forEach(function(c) {
        renderCategoryDisplay(c);
    });
});

function renderCategoryDisplay(cat) {
    var container = document.getElementById('selected_' + cat + '_display');
    var hiddenInput = document.getElementById('selected_' + (cat === 'workshops' ? 'workshop' : (cat === 'trainings' ? 'training' : cat.replace(/s$/, ''))) + '_ids');
    
    if (!hiddenInput) {
        hiddenInput = document.getElementById('selected_' + cat + '_ids');
    }

    var items = Object.values(selectedItemsMap[cat]);
    var ids = items.map(function(it) { return it.id; });

    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(ids);
    }

    if (!container) return;

    if (items.length === 0) {
        container.innerHTML = '<div class="text-muted small py-2"><i class="fas fa-info-circle me-1"></i> No records selected yet.</div>';
        return;
    }

    var html = '<div class="list-group gap-2">';
    items.forEach(function(it, idx) {
        html += '<div class="list-group-item d-flex justify-content-between align-items-center rounded-3 border p-2.5" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">';
        html += '  <div>';
        html += '    <div class="font-weight-bold text-dark mb-0.5"><i class="fas fa-check-circle text-success me-1.5"></i> ' + escapeHtml(it.title) + (it.task_no ? ' <span class="badge ms-1.5" style="background-color: #e2e8f0; color: #334155; font-weight: 600; font-size: 0.72rem;">Task: ' + escapeHtml(it.task_no) + '</span>' : '') + '</div>';
        if (it.subtitle) {
            html += '    <div class="small text-muted ms-4">' + escapeHtml(it.subtitle) + '</div>';
        }
        html += '  </div>';
        html += '  <div>';
        html += '    <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold px-2.5 py-1" style="font-size: 0.75rem; border-radius: 6px;" onclick="removeItem(\'' + cat + '\', ' + it.id + ')">';
        html += '      <i class="fas fa-trash-alt me-1"></i> Remove';
        html += '    </button>';
        html += '  </div>';
        html += '</div>';
    });
    html += '</div>';
    container.innerHTML = html;
}

function removeItem(cat, id) {
    if (selectedItemsMap[cat] && selectedItemsMap[cat][id]) {
        delete selectedItemsMap[cat][id];
        renderCategoryDisplay(cat);
    }
}

function toggleCapacitySection(groupId, isChecked) {
    var el = document.getElementById(groupId);
    if (el) {
        el.style.display = isChecked ? 'block' : 'none';
    }
}

// MODAL VARIABLES & FUNCTIONS
var activeModalCategory = '';
var candidateItems = [];

var catIcons = {
    publications: 'fa-book-open',
    conferences: 'fa-users',
    webinars: 'fa-video',
    internships: 'fa-user-graduate',
    patents: 'fa-award',
    workshops: 'fa-chalkboard-teacher',
    trainings: 'fa-laptop-code'
};

function openKpiModal(category) {
    activeModalCategory = category;
    var iconClass = catIcons[category] || 'fa-list';
    document.getElementById('kpiModalTitle').innerHTML = '<i class="fas ' + iconClass + ' me-2"></i> Select ' + capitalize(category);
    document.getElementById('kpiSearchInput').value = '';
    
    var modalEl = document.getElementById('kpiSelectionModal');
    var modal = new bootstrap.Modal(modalEl);
    modal.show();

    performKpiSearch();
}

function performKpiSearch() {
    var q = document.getElementById('kpiSearchInput').value.trim();
    var listEl = document.getElementById('kpiModalList');
    listEl.innerHTML = '<div class="text-center p-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Loading records for ' + escapeHtml(currentPrefix.toUpperCase()) + '...</div>';

    var url = 'progress_reports.php?action=ajax_search_kpi&prefix=' + encodeURIComponent(currentPrefix) + '&category=' + encodeURIComponent(activeModalCategory) + '&q=' + encodeURIComponent(q);

    fetch(url)
        .then(function(res) { return res.json(); })
        .then(function(json) {
            if (json.status === 'success') {
                candidateItems = json.data || [];
                renderModalCandidates();
            } else {
                listEl.innerHTML = '<div class="alert alert-danger m-3">Failed to load records.</div>';
            }
        })
        .catch(function(err) {
            listEl.innerHTML = '<div class="alert alert-danger m-3">Error fetching records.</div>';
        });
}

function renderModalCandidates() {
    var listEl = document.getElementById('kpiModalList');
    if (!candidateItems || candidateItems.length === 0) {
        listEl.innerHTML = '<div class="text-center p-5 text-muted"><i class="fas fa-search fa-2x mb-3 d-block empty-search-icon" style="color: #bc2121 !important;"></i> No matching records found for ' + escapeHtml(currentPrefix.toUpperCase()) + '.</div>';
        updateModalBadge();
        return;
    }

    var selectedMap = selectedItemsMap[activeModalCategory] || {};
    var html = '';

    candidateItems.forEach(function(item) {
        var isChecked = !!selectedMap[item.id];
        html += '<label class="list-group-item kpi-candidate-item d-flex align-items-start gap-3 style-pointer ' + (isChecked ? 'is-selected' : '') + '">';
        html += '  <input class="form-check-input flex-shrink-0 modal-kpi-checkbox" type="checkbox" data-id="' + item.id + '" ' + (isChecked ? 'checked' : '') + ' onchange="onModalCheckboxChange(this)">';
        html += '  <div class="flex-grow-1">';
        html += '    <div class="font-weight-bold text-dark fs-6 mb-1">' + escapeHtml(item.title) + (item.task_no ? ' <span class="badge task-badge-pill ms-2">Task: ' + escapeHtml(item.task_no) + '</span>' : '') + '</div>';
        if (item.subtitle) {
            html += '    <div class="small text-muted">' + escapeHtml(item.subtitle) + '</div>';
        }
        html += '  </div>';
        html += '</label>';
    });

    listEl.innerHTML = html;
    updateModalBadge();
}

function onModalCheckboxChange(cb) {
    var label = cb.closest('.kpi-candidate-item');
    if (label) {
        if (cb.checked) {
            label.classList.add('is-selected');
        } else {
            label.classList.remove('is-selected');
        }
    }
    updateModalBadge();
}

function updateModalBadge() {
    var checkboxes = document.querySelectorAll('.modal-kpi-checkbox:checked');
    var count = checkboxes.length;
    var badge = document.getElementById('kpiSelectedCountBadge');
    if (!badge) return;
    
    if (count > 0) {
        badge.className = 'badge badge-active';
        badge.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + count + ' selected';
    } else {
        badge.className = 'badge badge-zero';
        badge.innerHTML = '0 selected';
    }
}

function confirmKpiSelection() {
    var checkboxes = document.querySelectorAll('.modal-kpi-checkbox');
    var newMap = Object.assign({}, selectedItemsMap[activeModalCategory] || {});

    checkboxes.forEach(function(cb) {
        var id = parseInt(cb.getAttribute('data-id'));
        if (cb.checked) {
            var itemObj = candidateItems.find(function(it) { return it.id === id; });
            if (itemObj) {
                newMap[id] = itemObj;
            } else if (selectedItemsMap[activeModalCategory] && selectedItemsMap[activeModalCategory][id]) {
                newMap[id] = selectedItemsMap[activeModalCategory][id];
            }
        } else {
            delete newMap[id];
        }
    });

    selectedItemsMap[activeModalCategory] = newMap;
    renderCategoryDisplay(activeModalCategory);

    var modalEl = document.getElementById('kpiSelectionModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
}

function confirmDeleteReport() {
    if (confirm("Are you sure you want to delete Progress Report #<?= $selectedReportId ?>? This action cannot be undone.")) {
        document.getElementById('deleteReportForm').submit();
    }
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>
<?php include 'footer.php'; ?>
