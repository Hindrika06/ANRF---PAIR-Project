<?php
require_once 'auth_check.php';
require_once 'role_access.php';

// Auth Guard: Only Super Admin can access team management
if (!isSuperAdmin()) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/db.php';

$success = false;
$error   = '';

// 1. HANDLE DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        // Fetch image path first to delete the file
        $stmt = $pdo->prepare("SELECT profile_image FROM `team` WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        $row = $stmt->fetch();
        if ($row && !empty($row['profile_image']) && file_exists('../' . $row['profile_image'])) {
            @unlink('../' . $row['profile_image']);
        }

        $stmt = $pdo->prepare("DELETE FROM `team` WHERE id = :id");
        $stmt->execute([':id' => (int)$_GET['id']]);
        header("Location: team_management.php?success_msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to delete record: ' . $e->getMessage();
    }
}

// 2. SHOW QUICK SUCCESS MESSAGES POST-REDIRECT
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS (ADD OR UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name'] ?? '');
    $designation    = trim($_POST['designation'] ?? '');
    $department     = trim($_POST['department'] ?? '');
    $university     = trim($_POST['university'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $biography      = trim($_POST['biography'] ?? '');
    $linkedin       = trim($_POST['linkedin'] ?? '');
    $google_scholar = trim($_POST['google_scholar'] ?? '');
    $orcid          = trim($_POST['orcid'] ?? '');
    $research_area  = trim($_POST['research_area'] ?? '');
    $display_order  = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 10;
    $status         = $_POST['status'] ?? 'Active';
    $edit_id        = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if (empty($full_name) || empty($designation)) {
        $error = 'Full Name and Designation are required.';
    } else {
        try {
            $uploadDir = '../uploads/team/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $profileImage = null;

            // Handle Profile Image Upload
            if (!empty($_FILES['profile_image']['name'])) {
                $f = $_FILES['profile_image'];

                if ($f['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException("File upload failed with code: " . $f['error']);
                }

                if ($f['size'] > 5 * 1024 * 1024) {
                    throw new RuntimeException("File exceeds maximum allowed 5 MB limit.");
                }

                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    throw new RuntimeException("Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed.");
                }

                $destFileName = uniqid('member_', true) . '.' . $ext;
                $destFullPath = $uploadDir . $destFileName;

                if (!move_uploaded_file($f['tmp_name'], $destFullPath)) {
                    throw new RuntimeException("Could not save the uploaded profile image.");
                }
                $profileImage = 'uploads/team/' . $destFileName;

                // Delete old photo if editing
                if ($edit_id) {
                    $stmt = $pdo->prepare("SELECT profile_image FROM `team` WHERE id = :id");
                    $stmt->execute([':id' => $edit_id]);
                    $oldRow = $stmt->fetch();
                    if ($oldRow && !empty($oldRow['profile_image']) && file_exists('../' . $oldRow['profile_image'])) {
                        @unlink('../' . $oldRow['profile_image']);
                    }
                }
            }

            if ($edit_id) {
                // Update
                if ($profileImage) {
                    $stmt = $pdo->prepare("UPDATE `team` SET full_name = :full_name, designation = :designation, department = :department, university = :university, profile_image = :profile_image, biography = :biography, email = :email, phone = :phone, linkedin = :linkedin, google_scholar = :google_scholar, orcid = :orcid, research_area = :research_area, display_order = :display_order, status = :status WHERE id = :id");
                    $stmt->execute([
                        ':full_name'      => $full_name,
                        ':designation'    => $designation,
                        ':department'     => $department,
                        ':university'     => $university,
                        ':profile_image'  => $profileImage,
                        ':biography'      => $biography,
                        ':email'          => $email,
                        ':phone'          => $phone,
                        ':linkedin'       => $linkedin,
                        ':google_scholar' => $google_scholar,
                        ':orcid'          => $orcid,
                        ':research_area'  => $research_area,
                        ':display_order'  => $display_order,
                        ':status'         => $status,
                        ':id'             => $edit_id
                    ]);
                } else {
                    $stmt = $pdo->prepare("UPDATE `team` SET full_name = :full_name, designation = :designation, department = :department, university = :university, biography = :biography, email = :email, phone = :phone, linkedin = :linkedin, google_scholar = :google_scholar, orcid = :orcid, research_area = :research_area, display_order = :display_order, status = :status WHERE id = :id");
                    $stmt->execute([
                        ':full_name'      => $full_name,
                        ':designation'    => $designation,
                        ':department'     => $department,
                        ':university'     => $university,
                        ':biography'      => $biography,
                        ':email'          => $email,
                        ':phone'          => $phone,
                        ':linkedin'       => $linkedin,
                        ':google_scholar' => $google_scholar,
                        ':orcid'          => $orcid,
                        ':research_area'  => $research_area,
                        ':display_order'  => $display_order,
                        ':status'         => $status,
                        ':id'             => $edit_id
                    ]);
                }
                header("Location: team_management.php?success_msg=updated");
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO `team` (full_name, designation, department, university, profile_image, biography, email, phone, linkedin, google_scholar, orcid, research_area, display_order, status) VALUES (:full_name, :designation, :department, :university, :profile_image, :biography, :email, :phone, :linkedin, :google_scholar, :orcid, :research_area, :display_order, :status)");
                $stmt->execute([
                    ':full_name'      => $full_name,
                    ':designation'    => $designation,
                    ':department'     => $department,
                    ':university'     => $university,
                    ':profile_image'  => $profileImage,
                    ':biography'      => $biography,
                    ':email'          => $email,
                    ':phone'          => $phone,
                    ':linkedin'       => $linkedin,
                    ':google_scholar' => $google_scholar,
                    ':orcid'          => $orcid,
                    ':research_area'  => $research_area,
                    ':display_order'  => $display_order,
                    ':status'         => $status
                ]);
                header("Location: team_management.php?success_msg=added");
            }
            exit;
        } catch (Exception $e) {
            $error = 'Execution Error: ' . $e->getMessage();
        }
    }
}

// 4. PREPARE SELECT FILTERS AND SEARCH
$search = trim($_GET['search'] ?? '');
$filter_uni = trim($_GET['university'] ?? '');
$filter_status = trim($_GET['status'] ?? '');

$where_clauses = [];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(full_name LIKE :search OR designation LIKE :search OR department LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($filter_uni !== '') {
    $where_clauses[] = "university = :filter_uni";
    $params[':filter_uni'] = $filter_uni;
}

if ($filter_status !== '') {
    $where_clauses[] = "status = :filter_status";
    $params[':filter_status'] = $filter_status;
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

try {
    // Total count query
    $count_sql = "SELECT COUNT(*) FROM `team` $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // Main records query (sorted by display order ASC, then full name ASC)
    $select_sql = "SELECT * FROM `team` $where_sql ORDER BY display_order ASC, full_name ASC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($select_sql);
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $records = $stmt->fetchAll();

    // Fetch KPI stats
    $total_members = $pdo->query("SELECT COUNT(*) FROM `team`")->fetchColumn();
    $active_count = $pdo->query("SELECT COUNT(*) FROM `team` WHERE status = 'Active'")->fetchColumn();
    $inactive_count = $pdo->query("SELECT COUNT(*) FROM `team` WHERE status = 'Inactive'")->fetchColumn();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include 'nav_header.php';
include 'header.php';
include 'sidebar.php';
include 'loader.php';
?>

<link rel="stylesheet" href="vendor/bootstrap-select/css/bootstrap-select.min.css">
<style>
    /* Styling matched to existing Admin Dashboard system */
    .btn-success {
        background-color: #059669 !important;
        border-color: #059669 !important;
    }
    .btn-success:hover {
        background-color: #047857 !important;
        border-color: #047857 !important;
    }
    .registry-card {
        border-radius: 12px !important;
        border: none !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03) !important;
        overflow: hidden;
        margin-bottom: 30px;
    }
    .table-responsive {
        border-radius: 0 0 12px 12px;
        overflow: hidden;
    }
    .table-theme-sapphire thead th {
        background: #024283 !important;
        color: #ffffff !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.05em;
        border: none !important;
        padding: 16px 20px !important;
    }
    .table-theme-sapphire tbody td {
        padding: 16px 20px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155;
        font-size: 13px;
    }
    .badge-status-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 50px;
        text-transform: uppercase;
    }
    .badge-status-active {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
    }
    .badge-status-inactive {
        background-color: #f1f5f9 !important;
        color: #64748b !important;
    }
    .pagination-theme-sapphire .page-item.active .page-link {
        background-color: #024283 !important;
        border-color: #024283 !important;
        color: #ffffff !important;
    }
    .pagination-theme-sapphire .page-link {
        color: #024283;
    }
    /* Unified KPI widgets style */
    .kpi-widget-card {
        border-radius: 20px !important;
        padding: 20px 24px;
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
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
    .view-team .filter-container-block {
        display: none !important;
    }
</style>

<div id="main-wrapper" class="view-team">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Super Admin Control</a></li>
                    <li class="breadcrumb-item active">Team Management</li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i>
                <strong>Success!</strong> Team list updated cleanly.
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

            <!-- KPI Row -->
            <div class="row mb-4">
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-users"></i></div>
                            <span class="kpi-title-text">Total Team Members</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_members ?></span>
                                <span class="kpi-subtext">tracked</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-user-check"></i></div>
                            <span class="kpi-title-text">Active Members</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $active_count ?></span>
                                <span class="kpi-subtext">publicly visible</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-user-slash"></i></div>
                            <span class="kpi-title-text">Inactive Members</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $inactive_count ?></span>
                                <span class="kpi-subtext">hidden draft</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Grid Registry -->
            <div class="col-lg-12 px-0">
                <div class="card registry-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 bg-white border-0">
                        <h4 class="card-title mb-0" style="color: #024283; font-weight: 700; font-size: 15px;">
                            <i class="fa-solid fa-id-card me-2"></i>TEAM DIRECTORY MEMBERS
                        </h4>
                        <button type="button" class="btn btn-success btn-sm text-white px-3" data-bs-toggle="modal" data-bs-target="#memberModal" id="addNewBtn" style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Team Member
                        </button>
                    </div>

                    <!-- Search Filter Block -->
                    <?php 
                    $current_page = basename($_SERVER['PHP_SELF']);
                    if ($current_page !== 'team_management.php' && $current_page !== 'event_calendar.php'): 
                    ?>
                    <div class="p-3 border-bottom bg-light filter-container-block">
                        <form method="GET" action="team_management.php" class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, designation, department..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="university" class="form-select form-select-sm">
                                    <option value="">All Universities</option>
                                    <option value="University of Hyderabad" <?= ($filter_uni === 'University of Hyderabad') ? 'selected' : '' ?>>University of Hyderabad</option>
                                    <?php foreach ($adminPrefixFullNames as $name): ?>
                                        <option value="<?= htmlspecialchars($name) ?>" <?= ($filter_uni === $name) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <option value="Active" <?= ($filter_status === 'Active') ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= ($filter_status === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm px-3 flex-fill">Apply Filters</button>
                                <a href="team_management.php" class="btn btn-secondary btn-sm px-3 flex-fill">Clear</a>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-responsive-md table-theme-sapphire mb-0">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Profile Photo</th>
                                    <th>Full Name</th>
                                    <th>Designation & Org</th>
                                    <th>Department</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No team members found matching the search criteria.</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $sno = $offset + 1;
                                foreach ($records as $row): 
                                    $photo = !empty($row['profile_image']) && file_exists('../' . $row['profile_image']) ? '../' . $row['profile_image'] : 'images/profile/pic1.jpg';
                                ?>
                                <tr>
                                    <td><strong><?= $sno++ ?></strong></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($photo) ?>" alt="Avatar" style="width:45px; height:45px; object-fit:cover; border-radius:50%; border:2px solid #ddd;">
                                    </td>
                                    <td>
                                        <span class="text-dark font-w600 d-block"><?= htmlspecialchars($row['full_name']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="d-block font-w500 text-dark"><?= htmlspecialchars($row['designation']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($row['university']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['department'] ?: '—') ?></td>
                                    <td><span class="badge light bg-primary text-white">Order: <?= $row['display_order'] ?></span></td>
                                    <td>
                                        <span class="badge badge-status-pill badge-status-<?= strtolower($row['status']) ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" class="btn btn-primary btn-xs sharp" 
                                                    data-bs-toggle="modal" data-bs-target="#memberModal"
                                                    onclick='editMember(<?= json_encode($row) ?>)'>
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-xs sharp"
                                                    onclick="confirmDelete(<?= $row['id'] ?>)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white border-0 py-3">
                        <nav class="d-flex justify-content-between align-items-center">
                            <span class="text-muted font-w500" style="font-size: 13px;">Showing Page <?= $page ?> of <?= $total_pages ?></span>
                            <ul class="pagination pagination-gutter pagination-theme-sapphire mb-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&university=<?= urlencode($filter_uni) ?>&status=<?= urlencode($filter_status) ?>">
                                        <i class="fa fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&university=<?= urlencode($filter_uni) ?>&status=<?= urlencode($filter_status) ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&university=<?= urlencode($filter_uni) ?>&status=<?= urlencode($filter_status) ?>">
                                        <i class="fa fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- ── ADD / EDIT MEMBER MODAL ── -->
<div class="modal fade" id="memberModal" tabindex="-1" aria-labelledby="memberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="memberModalLabel">Member Registration Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="modalForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="modal_edit_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="modal_full_name" class="form-control" required placeholder="Enter full name">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Designation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" id="modal_designation" class="form-control" required placeholder="e.g. Principal Investigator, Assistant Professor">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Department / Center</label>
                            <input type="text" name="department" id="modal_department" class="form-control" placeholder="e.g. School of Computer Sciences">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">University / Organisation</label>
                            <select name="university" id="modal_university" class="form-select">
                                <option value="University of Hyderabad">University of Hyderabad</option>
                                <?php foreach ($adminPrefixFullNames as $name): ?>
                                    <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Email Address (Optional)</label>
                            <input type="email" name="email" id="modal_email" class="form-control" placeholder="e.g. member@university.edu">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Phone / Contact (Optional)</label>
                            <input type="text" name="phone" id="modal_phone" class="form-control" placeholder="e.g. +91 9876543210">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label text-dark font-w600">Biography / Research Summary (Optional)</label>
                            <textarea name="biography" id="modal_biography" class="form-control" rows="3" placeholder="Brief biography or details about project activities..."></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-dark font-w600">LinkedIn Profile Link</label>
                            <input type="url" name="linkedin" id="modal_linkedin" class="form-control" placeholder="https://linkedin.com/in/... ">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-dark font-w600">Google Scholar Profile Link</label>
                            <input type="url" name="google_scholar" id="modal_google_scholar" class="form-control" placeholder="https://scholar.google.com/... ">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-dark font-w600">ORCID ID Link / Code</label>
                            <input type="text" name="orcid" id="modal_orcid" class="form-control" placeholder="e.g. 0000-0002-1825-0097">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Primary Research Area</label>
                            <input type="text" name="research_area" id="modal_research_area" class="form-control" placeholder="e.g. Artificial Intelligence, Protein Crystallography">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Display Sorting Order <span class="text-danger">*</span></label>
                            <input type="number" name="display_order" id="modal_display_order" class="form-control" required value="10" min="1">
                            <div class="form-text">Lower numbers appear first on the page (e.g. 1=Director, 5=PI, 20=Student).</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Profile Photo Upload</label>
                            <input type="file" name="profile_image" id="modal_profile_image" class="form-control" accept="image/*">
                            <div class="form-text">Optional. Max 5MB (JPG, PNG, WEBP). Overwrites previous image.</div>
                        </div>

                        <div class="col-md-6 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="status" value="Active" id="modal_status" checked>
                                <label class="form-check-label text-dark font-w600" for="modal_status">Active Status (Visible to public)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success text-white" id="modalSubmitBtn">Save Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── DELETE CONFIRMATION MODAL ── -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-dark" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-2 text-dark">
                Are you sure you want to permanently delete this team member? This will remove them from the public Team list immediately.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="modalDeleteExecutionLink" class="btn btn-sm btn-danger text-white">Delete Member</a>
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

<script>
    const modalTitle     = document.getElementById('memberModalLabel');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalForm      = document.getElementById('modalForm');
    const modalDeleteExecutionLink = document.getElementById('modalDeleteExecutionLink');
    const bootstrapDeleteInstance  = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    // Prepare clean form on Add click
    document.getElementById('addNewBtn').addEventListener('click', function() {
        modalForm.reset();
        document.getElementById('modal_edit_id').value = '';
        modalTitle.innerText = 'Add Team Member';
        modalSubmitBtn.innerText = 'Save Member';
        document.getElementById('modal_status').checked = true;
    });

    function editMember(row) {
        modalForm.reset();
        modalTitle.innerText = 'Edit Team Member';
        modalSubmitBtn.innerText = 'Update Member';

        document.getElementById('modal_edit_id').value = row.id;
        document.getElementById('modal_full_name').value = row.full_name;
        document.getElementById('modal_designation').value = row.designation;
        document.getElementById('modal_department').value = row.department;
        document.getElementById('modal_university').value = row.university;
        document.getElementById('modal_email').value = row.email;
        document.getElementById('modal_phone').value = row.phone;
        document.getElementById('modal_biography').value = row.biography;
        document.getElementById('modal_linkedin').value = row.linkedin;
        document.getElementById('modal_google_scholar').value = row.google_scholar;
        document.getElementById('modal_orcid').value = row.orcid;
        document.getElementById('modal_research_area').value = row.research_area;
        document.getElementById('modal_display_order').value = row.display_order;
        document.getElementById('modal_status').checked = (row.status === 'Active');
    }

    function confirmDelete(id) {
        modalDeleteExecutionLink.href = 'team_management.php?action=delete&id=' + id;
        bootstrapDeleteInstance.show();
    }
</script>
</body>
</html>
