<?php
require_once 'auth_check.php';
require_once 'role_access.php';
require_once 'config/db.php';

$is_super = isSuperAdmin();
if (!$is_super) {
    header("Location: dashboard.php");
    exit();
}

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($action && $request_id > 0) {
        if (!$is_super) {
            $error_msg = 'Unauthorized. Only Super Admins can approve, reject, or delete requests.';
        } else {
            try {
                if ($action === 'delete_request') {
                    $pdo->bypass_interceptor = true;
                    $delStmt = $pdo->prepare("DELETE FROM `approval_requests` WHERE id = ?");
                    $delStmt->execute([$request_id]);
                    $pdo->bypass_interceptor = false;
                    $success_msg = 'Approval history deleted successfully.';
                } else {
                    // Fetch the approval request
                    $pdo->bypass_interceptor = true;
                    $stmt = $pdo->prepare("SELECT * FROM `approval_requests` WHERE id = ?");
                    $stmt->execute([$request_id]);
                    $request = $stmt->fetch();
                    $pdo->bypass_interceptor = false;

                    if (!$request) {
                        $error_msg = 'Request not found.';
                    } elseif ($request['status'] !== 'Pending') {
                        $error_msg = 'This request has already been processed.';
                    } else {
                        if ($action === 'approve') {
                            // 1. If deferred query exists, execute it
                            if (!empty($request['sql_query'])) {
                                try {
                                    $sql = $request['sql_query'];
                                    $params = $request['sql_params'] ? json_decode($request['sql_params'], true) : [];
                                    $deferredStmt = $pdo->prepare($sql);
                                    $deferredStmt->execute($params);
                                } catch (Exception $ex) {
                                    // Ignore if record already inserted or query fails
                                }
                            }
                            
                            // 2. Mark KPI request and target table row as Approved
                            approveKpiRequest($pdo, $request_id, $_SESSION['username']);
                            $success_msg = 'Request #' . $request_id . ' approved and changes published successfully.';
                        } elseif ($action === 'reject') {
                            $reason = trim($_POST['rejection_reason'] ?? '');
                            
                            // Mark KPI request and target table row as Rejected
                            rejectKpiRequest($pdo, $request_id, $_SESSION['username'], $reason);
                            $success_msg = 'Request #' . $request_id . ' has been rejected.';
                        }
                    }
                }
            } catch (Exception $e) {
                $error_msg = 'Error processing request: ' . $e->getMessage();
            }
        }
    }
}

// Fetch requests
$pendingRequests = [];
$historyRequests = [];
$myRequests = [];

try {
    $pdo->bypass_interceptor = true;
    if ($is_super) {
        // Super Admin fetches all requests
        $stmt = $pdo->query("SELECT * FROM `approval_requests` WHERE status = 'Pending' ORDER BY requested_at DESC");
        $pendingRequests = $stmt->fetchAll();

        $stmt = $pdo->query("SELECT * FROM `approval_requests` WHERE status != 'Pending' ORDER BY approved_at DESC LIMIT 150");
        $historyRequests = $stmt->fetchAll();
    } else {
        // Standard Admin fetches only their own requests
        $stmt = $pdo->prepare("SELECT * FROM `approval_requests` WHERE requested_by = ? ORDER BY requested_at DESC LIMIT 200");
        $stmt->execute([$_SESSION['username']]);
        $myRequests = $stmt->fetchAll();
    }
    $pdo->bypass_interceptor = false;
} catch (Exception $e) {
    $pdo->bypass_interceptor = false;
    $error_msg = 'Database error fetching requests: ' . $e->getMessage();
}

// Index requests for modal details JavaScript
$indexedRequests = [];
foreach (array_merge($pendingRequests, $historyRequests, $myRequests) as $r) {
    $indexedRequests[$r['id']] = $r;
}

?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
/* ── HUB ADMIN APPROVAL BUTTON COLORS & SIZING ── */
.btn-appr-details,
.btn-appr-approve,
.btn-appr-reject {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 36px !important;
    padding: 0 14px !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    white-space: nowrap !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
}
.btn-appr-details i,
.btn-appr-approve i,
.btn-appr-reject i {
    margin-right: 6px !important;
    font-size: 0.78rem !important;
}
.btn-appr-details {
    background: #024283 !important;
    color: #ffffff !important;
    border: 1.5px solid #024283 !important;
    box-shadow: 0 2px 6px rgba(2, 66, 131, 0.2) !important;
}
.btn-appr-details:hover {
    background: #0856a4 !important;
    border-color: #0856a4 !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(2, 66, 131, 0.3) !important;
}
.btn-appr-approve {
    background: #09BD3C !important;
    color: #ffffff !important;
    border: 1.5px solid #09BD3C !important;
    box-shadow: 0 2px 6px rgba(9, 189, 60, 0.2) !important;
}
.btn-appr-approve:hover {
    background: #07a033 !important;
    border-color: #07a033 !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(9, 189, 60, 0.35) !important;
}
.btn-appr-reject {
    background: #bc2121 !important;
    color: #ffffff !important;
    border: 1.5px solid #bc2121 !important;
    box-shadow: 0 2px 6px rgba(188, 33, 33, 0.2) !important;
}
.btn-appr-reject:hover {
    background: #a01b1b !important;
    border-color: #a01b1b !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(188, 33, 33, 0.35) !important;
}

/* ── BADGES ── */
.badge-inst-hub {
    display: inline-block !important;
    background-color: #024283 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    font-size: 0.75rem !important;
    border-radius: 6px !important;
    padding: 4px 10px !important;
    letter-spacing: 0.03em !important;
}
.badge-act-create {
    display: inline-block !important;
    background-color: #dcfce7 !important;
    color: #15803d !important;
    border: 1px solid #bbf7d0 !important;
    font-weight: 700 !important;
    font-size: 0.75rem !important;
    border-radius: 6px !important;
    padding: 4px 10px !important;
}
.badge-act-update {
    display: inline-block !important;
    background-color: #fef3c7 !important;
    color: #b45309 !important;
    border: 1px solid #fde68a !important;
    font-weight: 700 !important;
    font-size: 0.75rem !important;
    border-radius: 6px !important;
    padding: 4px 10px !important;
}
.badge-act-delete {
    display: inline-block !important;
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
    border: 1px solid #fecaca !important;
    font-weight: 700 !important;
    font-size: 0.75rem !important;
    border-radius: 6px !important;
    padding: 4px 10px !important;
}

/* ── HIGH VISIBILITY TABLE TEXT & HEADER BACKGROUND ── */
.table-header-custom th {
    background-color: #e2e8f0 !important;
    color: #0f172a !important;
    font-weight: 700 !important;
    font-size: 0.78rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    border-bottom: 2px solid #cbd5e1 !important;
    padding: 12px 16px !important;
    vertical-align: middle !important;
}
.btn-view-log {
    background: #064380 !important;
    color: #ffffff !important;
    border: 1.5px solid #064380 !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 6px rgba(6, 67, 128, 0.2) !important;
    transition: all 0.2s ease !important;
}
.btn-view-log:hover {
    background: #043260 !important;
    border-color: #043260 !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(6, 67, 128, 0.35) !important;
}

.btn-delete-log {
    background: #bc2121 !important;
    color: #ffffff !important;
    border: 1.5px solid #bc2121 !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 6px rgba(188, 33, 33, 0.2) !important;
    transition: all 0.2s ease !important;
}
.btn-delete-log:hover {
    background: #a01b1b !important;
    border-color: #a01b1b !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(188, 33, 33, 0.35) !important;
}

.btn-close-custom {
    background: #064380 !important;
    color: #ffffff !important;
    border: 1.5px solid #064380 !important;
    font-weight: 600 !important;
    padding: 6px 20px !important;
    border-radius: 6px !important;
    box-shadow: 0 2px 6px rgba(6, 67, 128, 0.2) !important;
    transition: all 0.2s ease !important;
}
.btn-close-custom:hover {
    background: #043260 !important;
    border-color: #043260 !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(6, 67, 128, 0.35) !important;
}
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">
            
            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Approvals</a></li>
                </ol>
            </div>

            <!-- Messages -->
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> <?= htmlspecialchars($success_msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (window.ANRFToast) {
                            ANRFToast.show({
                                title: 'Approval Updated',
                                message: <?= json_encode($success_msg) ?>,
                                type: 'success',
                                duration: 6000
                            });
                        }
                    });
                </script>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <?= htmlspecialchars($error_msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (window.ANRFToast) {
                            ANRFToast.show({
                                title: 'Approval Error',
                                message: <?= json_encode($error_msg) ?>,
                                type: 'danger',
                                duration: 6000
                            });
                        }
                    });
                </script>
            <?php endif; ?>

            <?php if ($is_super): ?>
                <!-- ============================================================
                     SUPER ADMIN WORKSPACE
                     ============================================================ -->
                
                <!-- Pending Approvals Card -->
                <div class="card mb-4">
                    <div class="card-header text-white py-3 d-flex align-items-center" style="background: linear-gradient(135deg, #024283, #0856a4) !important;">
                        <h4 class="card-title mb-0 text-white font-weight-bold" style="color: #ffffff !important; font-size: 1.05rem;">
                            <i class="fas fa-clock me-2"></i> Pending Approvals (<?= count($pendingRequests) ?>)
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-header-custom">
                                    <tr>
                                        <th>ID</th>
                                        <th>Requester</th>
                                        <th>Institute</th>
                                        <th>Module</th>
                                        <th>Action</th>
                                        <th>Date Submitted</th>
                                        <th style="width: 280px; text-align: center;">Review Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pendingRequests)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">All clear! No pending approval requests.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pendingRequests as $r): ?>
                                            <tr>
                                                <td><strong class="text-dark-visible"><?= $r['id'] ?></strong></td>
                                                <td><span class="text-dark-visible"><?= htmlspecialchars($r['requested_by']) ?></span></td>
                                                <td><span class="badge-inst-hub"><?= htmlspecialchars(strtoupper($r['institute_prefix'])) ?></span></td>
                                                <td><span class="text-dark-visible"><?= htmlspecialchars($r['module_name']) ?></span></td>
                                                <td>
                                                    <?php if ($r['action_type'] === 'CREATE'): ?>
                                                        <span class="badge-act-create">CREATE</span>
                                                    <?php elseif ($r['action_type'] === 'UPDATE'): ?>
                                                        <span class="badge-act-update">UPDATE</span>
                                                    <?php else: ?>
                                                        <span class="badge-act-delete"><?= htmlspecialchars($r['action_type']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="text-muted-visible"><?= htmlspecialchars($r['requested_at']) ?></span></td>
                                                <td>
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <button class="btn btn-appr-details" onclick="showDetails(<?= $r['id'] ?>)">
                                                            <i class="fas fa-eye me-1.5" style="font-size: 0.75rem;"></i> View Details
                                                        </button>
                                                        <button type="button" class="btn btn-appr-approve" onclick="openApproveModal(<?= $r['id'] ?>)">
                                                            <i class="fas fa-check me-1.5" style="font-size: 0.75rem;"></i> Approve
                                                        </button>
                                                        <button type="button" class="btn btn-appr-reject" onclick="openRejectModal(<?= $r['id'] ?>)">
                                                            <i class="fas fa-times me-1.5" style="font-size: 0.75rem;"></i> Reject
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- History Audit Trail Card -->
                <div class="card">
                    <div class="card-header text-white py-3 d-flex align-items-center" style="background: linear-gradient(90deg, #0B4A8B, #1565C0) !important;">
                        <h4 class="card-title mb-0" style="color: #ffffff !important; font-weight: 700;">Approved Logs</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-header-custom">
                                    <tr>
                                        <th>ID</th>
                                        <th>Requester</th>
                                        <th>Module</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                        <th>Reviewed By</th>
                                        <th>Date Reviewed</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($historyRequests)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No historical approval actions recorded.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($historyRequests as $r): ?>
                                            <tr>
                                                <td><?= $r['id'] ?></td>
                                                <td><?= htmlspecialchars($r['requested_by']) ?></td>
                                                <td><?= htmlspecialchars($r['module_name']) ?></td>
                                                <td>
                                                    <span class="badge <?= $r['action_type'] === 'CREATE' ? 'bg-success text-white' : ($r['action_type'] === 'UPDATE' ? 'bg-warning text-dark' : 'bg-danger text-white') ?>">
                                                        <?= htmlspecialchars($r['action_type']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $r['status'] === 'Approved' ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                                                        <?= htmlspecialchars($r['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($r['approved_by'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($r['approved_at'] ?? '-') ?></td>
                                                <td>
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button class="btn btn-xxs btn-view-log text-white" onclick="showDetails(<?= $r['id'] ?>)">View Log</button>
                                                        <button type="button" class="btn btn-xxs btn-delete-log text-white" onclick="openDeleteModal(<?= $r['id'] ?>)"><i class="fa fa-trash me-1"></i> Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- ============================================================
                     STANDARD ADMIN WORKSPACE
                     ============================================================ -->
                
                <div class="card">
                    <div class="card-header text-white py-3 d-flex align-items-center" style="background: linear-gradient(90deg, #0B4A8B, #1565C0) !important;">
                        <h4 class="card-title mb-0" style="color: #ffffff !important; font-weight: 700;">My Approval Requests Status Tracker</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-header-custom">
                                    <tr>
                                        <th>ID</th>
                                        <th>Module</th>
                                        <th>Action Type</th>
                                        <th>Submitted At</th>
                                        <th>Status</th>
                                        <th>Reviewed At</th>
                                        <th>Review Details / Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($myRequests)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">You have not submitted any approval requests yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($myRequests as $r): ?>
                                            <tr>
                                                <td><?= $r['id'] ?></td>
                                                <td><strong><?= htmlspecialchars($r['module_name']) ?></strong></td>
                                                <td>
                                                    <span class="badge <?= $r['action_type'] === 'CREATE' ? 'bg-success text-white' : ($r['action_type'] === 'UPDATE' ? 'bg-warning text-dark' : 'bg-danger text-white') ?>">
                                                        <?= htmlspecialchars($r['action_type']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($r['requested_at']) ?></td>
                                                <td>
                                                    <span class="badge <?= $r['status'] === 'Pending' ? 'bg-warning text-dark' : ($r['status'] === 'Approved' ? 'bg-success text-white' : 'bg-danger text-white') ?>">
                                                        <?= htmlspecialchars($r['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($r['approved_at'] ?? '-') ?></td>
                                                <td>
                                                    <?php if ($r['status'] === 'Rejected'): ?>
                                                        <div class="text-danger font-weight-bold" style="font-size: 13px;">
                                                            <strong>Rejection Reason:</strong> <?= htmlspecialchars($r['rejection_reason'] ?: 'No details provided.') ?>
                                                        </div>
                                                    <?php elseif ($r['status'] === 'Approved'): ?>
                                                        <div class="text-success font-weight-bold" style="font-size: 13px;">
                                                            Approved by <?= htmlspecialchars($r['approved_by']) ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Awaiting Hub Admin review</span>
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

<!-- ============================================================
     MODALS
     ============================================================ -->

<!-- Request Details Comparison Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight: 700;">Request Details Comparison</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: 0; font-size: 20px;">&times;</button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <!-- Loaded dynamically by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-close-custom text-white" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Form for ANRFModal Submissions -->
<form id="approvalActionForm" method="POST" action="approvals.php" style="display: none;">
    <input type="hidden" name="action" id="approval_action">
    <input type="hidden" name="id" id="approval_id">
    <input type="hidden" name="rejection_reason" id="approval_rejection_reason">
</form>


<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>

<script>
const requestsData = <?= json_encode($indexedRequests, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function showDetails(requestId) {
    const req = requestsData[requestId];
    if (!req) return;
    
    let html = `
        <table class="table table-bordered mb-4">
            <tr><th style="width: 30%;">Request ID</th><td>#${req.id}</td></tr>
            <tr><th>Requested By</th><td>${escapeHtml(req.requested_by)}</td></tr>
            <tr><th>Module</th><td>${escapeHtml(req.module_name)}</td></tr>
            <tr><th>Action</th><td><span class="badge ${getActionBadgeClass(req.action_type)}">${escapeHtml(req.action_type)}</span></td></tr>
            <tr><th>Date & Time</th><td>${escapeHtml(req.requested_at)}</td></tr>
            <tr><th>Status</th><td><span class="badge ${req.status === 'Approved' ? 'bg-success text-white' : (req.status === 'Rejected' ? 'bg-danger text-white' : 'bg-warning text-dark')}">${escapeHtml(req.status)}</span></td></tr>
            ${req.rejection_reason ? `<tr><th>Rejection Reason</th><td class="text-danger font-weight-bold">${escapeHtml(req.rejection_reason)}</td></tr>` : ''}
        </table>
    `;
    
    const oldData = req.old_data ? JSON.parse(req.old_data) : null;
    const newData = req.new_data ? JSON.parse(req.new_data) : null;
    
    html += `<h5 class="mt-4 mb-2" style="font-weight: 700;">Data Comparison</h5>`;
    html += `<div class="table-responsive"><table class="table table-bordered table-striped table-hover">`;
    html += `<thead><tr class="table-light"><th>Field</th><th>Original Value</th><th>Proposed Value</th></tr></thead><tbody>`;
    
    if (req.action_type === 'CREATE') {
        if (newData) {
            for (const [key, value] of Object.entries(newData)) {
                if (key === 'created_at' || key === 'updated_at') continue;
                html += `<tr>
                    <td class="font-weight-bold" style="width: 30%;">${escapeHtml(key)}</td>
                    <td class="text-muted">-</td>
                    <td class="text-success font-weight-bold">${escapeHtml(value !== null ? value : 'NULL')}</td>
                </tr>`;
            }
        }
    } else if (req.action_type === 'DELETE') {
        if (oldData) {
            for (const [key, value] of Object.entries(oldData)) {
                html += `<tr>
                    <td class="font-weight-bold" style="width: 30%;">${escapeHtml(key)}</td>
                    <td class="text-danger" style="text-decoration: line-through;">${escapeHtml(value !== null ? value : 'NULL')}</td>
                    <td class="text-muted">-</td>
                </tr>`;
            }
        }
    } else if (req.action_type === 'UPDATE') {
        const allKeys = new Set([...Object.keys(oldData || {}), ...Object.keys(newData || {})]);
        allKeys.forEach(key => {
            if (key === 'created_at' || key === 'updated_at') return;
            const oldVal = oldData && oldData[key] !== undefined ? oldData[key] : null;
            const newVal = newData && newData[key] !== undefined ? newData[key] : null;
            
            const isDifferent = String(oldVal) !== String(newVal);
            const rowStyle = isDifferent ? 'style="background-color: #fffbeb;"' : ''; 
            
            html += `<tr ${rowStyle}>
                <td class="font-weight-bold" style="width: 30%;">${escapeHtml(key)}</td>
                <td class="${isDifferent ? 'text-danger font-weight-bold' : 'text-muted'}">${escapeHtml(oldVal !== null ? oldVal : 'NULL')}</td>
                <td class="${isDifferent ? 'text-success font-weight-bold' : ''}">${escapeHtml(newVal !== null ? newVal : 'NULL')}</td>
            </tr>`;
        });
    }
    
    html += `</tbody></table></div>`;
    
    document.getElementById('detailsModalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

function openApproveModal(id) {
    ANRFModal.showApprove({
        onConfirm: function () {
            document.getElementById('approval_action').value = 'approve';
            document.getElementById('approval_id').value = id;
            document.getElementById('approval_rejection_reason').value = '';
            document.getElementById('approvalActionForm').submit();
        }
    });
}

function openDeleteModal(id) {
    ANRFModal.confirm({
        type: 'reject',
        title: 'Delete Approval Record?',
        message: 'This will remove only the approval history entry.\nPublished KPI data will remain unchanged.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        showTextarea: false,
        onConfirm: function () {
            document.getElementById('approval_action').value = 'delete_request';
            document.getElementById('approval_id').value = id;
            document.getElementById('approval_rejection_reason').value = '';
            document.getElementById('approvalActionForm').submit();
        }
    });
}

function openRejectModal(id) {
    ANRFModal.showReject({
        onConfirm: function (reason) {
            document.getElementById('approval_action').value = 'reject';
            document.getElementById('approval_id').value = id;
            document.getElementById('approval_rejection_reason').value = reason;
            document.getElementById('approvalActionForm').submit();
        }
    });
}


function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function getActionBadgeClass(action) {
    if (action === 'CREATE') return 'bg-success text-white';
    if (action === 'UPDATE') return 'bg-warning text-dark';
    if (action === 'DELETE') return 'bg-danger text-white';
    return 'bg-secondary text-white';
}
</script>

<?php include 'footer.php'; ?>
