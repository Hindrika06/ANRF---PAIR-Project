<?php
session_start();

require_once 'role_access.php';

// ── Check if user is logged in ─────
if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix'])) {
    header("Location: index.php");
    exit();
}

$is_super = isSuperAdmin();
$user_prefix = $_SESSION['institute_prefix'];

// ── Database & logic ─────────────────────────────────────────────────────────
require_once 'config/db.php';

$success = false;
$error   = '';

// 1. HANDLE DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!$is_super) {
        $error = 'Only Super Admins are allowed to delete events.';
    } else {
        try {
            // Fetch image path first to delete the file from the server
            $stmt = $pdo->prepare("SELECT image FROM `events` WHERE id = :id");
            $stmt->execute([':id' => (int)$_GET['id']]);
            $row = $stmt->fetch();
            if ($row && !empty($row['image']) && file_exists('../' . $row['image'])) {
                @unlink('../' . $row['image']);
            }

            $stmt = $pdo->prepare("DELETE FROM `events` WHERE id = :id");
            $stmt->execute([':id' => (int)$_GET['id']]);
            header("Location: event_calendar.php?success_msg=deleted");
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
    if (!$is_super) {
        $error = 'Only Super Admins are allowed to edit or add events.';
    } else {
        $title          = trim($_POST['title'] ?? '');
        $description    = trim($_POST['description'] ?? '');
        $university_id  = trim($_POST['university_id'] ?? 'all');
        $event_date     = $_POST['event_date'] ?? '';
        $start_time     = $_POST['start_time'] ?? '';
        $end_time       = $_POST['end_time'] ?? '';
        $venue          = trim($_POST['venue'] ?? '');
        $event_type     = trim($_POST['event_type'] ?? '');
        $visibility     = $_POST['visibility'] ?? 'public';
        $status         = $_POST['status'] ?? 'upcoming';
        $publish_status = isset($_POST['publish_status']) ? 1 : 0;
        $coordinator    = trim($_POST['coordinator'] ?? '');
        $edit_id        = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

        if (empty($title) || empty($event_date) || empty($start_time) || empty($end_time) || empty($venue)) {
            $error = 'Please fill in all required fields (Title, Date, Start Time, End Time, Venue).';
        } else {
            try {
                $uploadDir = '../uploads/events/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $imageFile = null;

                // Handle banner/image upload
                if (!empty($_FILES['image']['name'])) {
                    $f = $_FILES['image'];

                    if ($f['error'] !== UPLOAD_ERR_OK) {
                        throw new RuntimeException("File upload failed with server error code: " . $f['error']);
                    }

                    if ($f['size'] > 10 * 1024 * 1024) {
                        throw new RuntimeException("File exceeds maximum allowed 10 MB limit.");
                    }

                    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        throw new RuntimeException("Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed.");
                    }

                    $destFileName = uniqid('event_', true) . '.' . $ext;
                    $destFullPath = $uploadDir . $destFileName;

                    if (!move_uploaded_file($f['tmp_name'], $destFullPath)) {
                        throw new RuntimeException("Could not save the uploaded file.");
                    }
                    $imageFile = 'uploads/events/' . $destFileName;

                    // Delete old image if editing
                    if ($edit_id) {
                        $stmt = $pdo->prepare("SELECT image FROM `events` WHERE id = :id");
                        $stmt->execute([':id' => $edit_id]);
                        $oldRow = $stmt->fetch();
                        if ($oldRow && !empty($oldRow['image']) && file_exists('../' . $oldRow['image'])) {
                            @unlink('../' . $oldRow['image']);
                        }
                    }
                }

                if ($edit_id) {
                    // Update query
                    if ($imageFile) {
                        $stmt = $pdo->prepare("UPDATE `events` SET title = :title, description = :description, university_id = :university_id, event_date = :event_date, start_time = :start_time, end_time = :end_time, venue = :venue, event_type = :event_type, image = :image, visibility = :visibility, status = :status, publish_status = :publish_status, coordinator = :coordinator WHERE id = :id");
                        $stmt->execute([
                            ':title'          => $title,
                            ':description'    => $description,
                            ':university_id'  => $university_id,
                            ':event_date'     => $event_date,
                            ':start_time'     => $start_time,
                            ':end_time'       => $end_time,
                            ':venue'          => $venue,
                            ':event_type'     => $event_type,
                            ':image'          => $imageFile,
                            ':visibility'     => $visibility,
                            ':status'         => $status,
                            ':publish_status' => $publish_status,
                            ':coordinator'    => $coordinator,
                            ':id'             => $edit_id
                        ]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE `events` SET title = :title, description = :description, university_id = :university_id, event_date = :event_date, start_time = :start_time, end_time = :end_time, venue = :venue, event_type = :event_type, visibility = :visibility, status = :status, publish_status = :publish_status, coordinator = :coordinator WHERE id = :id");
                        $stmt->execute([
                            ':title'          => $title,
                            ':description'    => $description,
                            ':university_id'  => $university_id,
                            ':event_date'     => $event_date,
                            ':start_time'     => $start_time,
                            ':end_time'       => $end_time,
                            ':venue'          => $venue,
                            ':event_type'     => $event_type,
                            ':visibility'     => $visibility,
                            ':status'         => $status,
                            ':publish_status' => $publish_status,
                            ':coordinator'    => $coordinator,
                            ':id'             => $edit_id
                        ]);
                    }
                    header("Location: event_calendar.php?success_msg=updated");
                } else {
                    // Insert query
                    $stmt = $pdo->prepare("INSERT INTO `events` (title, description, university_id, event_date, start_time, end_time, venue, event_type, image, visibility, status, publish_status, coordinator, created_by) VALUES (:title, :description, :university_id, :event_date, :start_time, :end_time, :venue, :event_type, :image, :visibility, :status, :publish_status, :coordinator, :created_by)");
                    $stmt->execute([
                        ':title'          => $title,
                        ':description'    => $description,
                        ':university_id'  => $university_id,
                        ':event_date'     => $event_date,
                        ':start_time'     => $start_time,
                        ':end_time'       => $end_time,
                        ':venue'          => $venue,
                        ':event_type'     => $event_type,
                        ':image'          => $imageFile,
                        ':visibility'     => $visibility,
                        ':status'         => $status,
                        ':publish_status' => $publish_status,
                        ':coordinator'    => $coordinator,
                        ':created_by'     => $_SESSION['username']
                    ]);
                    header("Location: event_calendar.php?success_msg=added");
                }
                exit;
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// 4. PREPARE SELECT FILTERS AND SEARCH
$search = trim($_GET['search'] ?? '');
$filter_uni = trim($_GET['university_id'] ?? '');
$filter_date = trim($_GET['event_date'] ?? '');
$filter_status = trim($_GET['status'] ?? '');

$where_clauses = [];
$params = [];

// Regular admin restriction: can only see events for 'all' or their own university prefix
if (!$is_super) {
    $where_clauses[] = "(university_id = 'all' OR university_id = :user_prefix)";
    $params[':user_prefix'] = $user_prefix;
}

if ($search !== '') {
    $where_clauses[] = "title LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

if ($filter_uni !== '') {
    if (!$is_super && $filter_uni !== $user_prefix && $filter_uni !== 'all') {
        // regular admin trying to filter to other university -> lock to user prefix or all
        $where_clauses[] = "university_id = 'none'"; 
    } else {
        $where_clauses[] = "university_id = :filter_uni";
        $params[':filter_uni'] = $filter_uni;
    }
}

if ($filter_date !== '') {
    $where_clauses[] = "event_date = :filter_date";
    $params[':filter_date'] = $filter_date;
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
    $count_sql = "SELECT COUNT(*) FROM `events` $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // Main records query
    $select_sql = "SELECT * FROM `events` $where_sql ORDER BY event_date DESC, start_time DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($select_sql);
    
    // Bind limit & offset as parameters because PDO mode requires it
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $records = $stmt->fetchAll();

    // Fetch counts for KPIs
    $total_events = $pdo->query("SELECT COUNT(*) FROM `events`")->fetchColumn();
    $upcoming_count = $pdo->query("SELECT COUNT(*) FROM `events` WHERE status = 'upcoming'")->fetchColumn();
    $ongoing_count = $pdo->query("SELECT COUNT(*) FROM `events` WHERE status = 'ongoing'")->fetchColumn();
    $completed_count = $pdo->query("SELECT COUNT(*) FROM `events` WHERE status = 'completed'")->fetchColumn();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include 'nav_header.php';
include 'header.php';
include 'sidebar.php';
include 'loader.php';
?>

<link rel="stylesheet" href="vendor/bootstrap-select/css/bootstrap-select.min.css">
<!-- Include flatpickr for dates -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    /* Premium visual overrides matching current design */
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
    /* Use solid backgrounds from whitelisted modules */
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
    .badge-status {
        font-size: 11px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 50px;
        text-transform: uppercase;
    }
    .badge-upcoming {
        background-color: #dbeafe !important;
        color: #1e40af !important;
    }
    .badge-ongoing {
        background-color: #fef3c7 !important;
        color: #92400e !important;
    }
    .badge-completed {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
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
    .pagination-theme-sapphire .page-item.active .page-link {
        background-color: #024283 !important;
        border-color: #024283 !important;
        color: #ffffff !important;
    }
    .pagination-theme-sapphire .page-link {
        color: #024283;
    }
    /* Unified KPI styling */
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
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Events Management</a></li>
                    <li class="breadcrumb-item active">Event Calendar</li>
                </ol>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i>
                <strong>Success!</strong> Event calendar updated cleanly.
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

            <!-- KPI Cards Row -->
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card" style="background: linear-gradient(135deg, #024283 0%, #1e40af 100%) !important;">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-calendar-days"></i></div>
                            <span class="kpi-title-text">Total Events</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_events ?></span>
                                <span class="kpi-subtext">managed</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card" style="background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%) !important;">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-hourglass-start"></i></div>
                            <span class="kpi-title-text">Upcoming Events</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $upcoming_count ?></span>
                                <span class="kpi-subtext">scheduled</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-circle-play"></i></div>
                            <span class="kpi-title-text">Ongoing Events</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $ongoing_count ?></span>
                                <span class="kpi-subtext">live now</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card" style="background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-calendar-check"></i></div>
                            <span class="kpi-title-text">Completed Events</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $completed_count ?></span>
                                <span class="kpi-subtext">finished</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Card -->
            <div class="col-lg-12 px-0">
                <div class="card registry-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 bg-white border-0">
                        <h4 class="card-title mb-0" style="color: #024283; font-weight: 700; font-size: 15px;">
                            <i class="fa-solid fa-list-check me-2"></i>EVENT RECORDS
                        </h4>
                        <?php if ($is_super): ?>
                        <button type="button" class="btn btn-success btn-sm text-white px-3" data-bs-toggle="modal" data-bs-target="#eventModal" id="addNewBtn" style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Event
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Search Filter Form -->
                    <div class="p-3 border-bottom bg-light">
                        <form method="GET" action="event_calendar.php" class="row g-2">
                            <?php if (!$is_super && isset($_GET['prefix'])): ?>
                                <input type="hidden" name="prefix" value="<?= htmlspecialchars($_GET['prefix']) ?>">
                            <?php endif; ?>
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="university_id" class="form-select form-select-sm">
                                    <option value="">All Universities</option>
                                    <option value="all" <?= ($filter_uni === 'all') ? 'selected' : '' ?>>Shared (All)</option>
                                    <?php foreach ($adminPrefixFullNames as $key => $name): ?>
                                        <?php if ($is_super || $key === $user_prefix): ?>
                                        <option value="<?= $key ?>" <?= ($filter_uni === $key) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="event_date" id="filter_date" class="form-control form-control-sm" placeholder="Filter by date..." value="<?= htmlspecialchars($filter_date) ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <option value="upcoming" <?= ($filter_status === 'upcoming') ? 'selected' : '' ?>>Upcoming</option>
                                    <option value="ongoing" <?= ($filter_status === 'ongoing') ? 'selected' : '' ?>>Ongoing</option>
                                    <option value="completed" <?= ($filter_status === 'completed') ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm px-3 flex-fill">Apply Filters</button>
                                <a href="event_calendar.php" class="btn btn-secondary btn-sm px-3 flex-fill">Clear</a>
                            </div>
                        </form>
                    </div>

                    <!-- Events Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-responsive-md table-theme-sapphire mb-0">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Event Title</th>
                                    <th>University</th>
                                    <th>Date & Time</th>
                                    <th>Venue</th>
                                    <th>Status</th>
                                    <th>Publisher</th>
                                    <th>Created By</th>
                                    <?php if ($is_super): ?>
                                    <th class="text-end">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="<?= $is_super ? 9 : 8 ?>" class="text-center py-4 text-muted">No calendar events found matching the filters.</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $sno = $offset + 1;
                                foreach ($records as $row): 
                                    $time_str = date("g:i A", strtotime($row['start_time'])) . ' - ' . date("g:i A", strtotime($row['end_time']));
                                    $uni_label = ($row['university_id'] === 'all') ? 'All Universities' : getInstituteLabel($row['university_id']);
                                    $date_label = date("d M Y", strtotime($row['event_date']));
                                ?>
                                <tr>
                                    <td><strong><?= $sno++ ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($row['image']) && file_exists('../' . $row['image'])): ?>
                                                <img src="../<?= htmlspecialchars($row['image']) ?>" alt="Banner" style="width:40px; height:40px; object-fit:cover; border-radius:4px; margin-right:10px;">
                                            <?php endif; ?>
                                            <div>
                                                <span class="text-dark font-w600"><?= htmlspecialchars($row['title']) ?></span>
                                                <small class="d-block text-muted" style="max-width:280px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?= htmlspecialchars($row['description']) ?>">
                                                    <?= htmlspecialchars($row['description']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge light bg-info text-white"><?= $uni_label ?></span></td>
                                    <td>
                                        <div class="text-dark font-w600"><?= $date_label ?></div>
                                        <small class="text-muted"><?= $time_str ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['venue']) ?></td>
                                    <td>
                                        <span class="badge badge-status badge-<?= $row['status'] ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-pub badge-pub-<?= ($row['publish_status'] == 1) ? 'active' : 'draft' ?>">
                                            <?= ($row['publish_status'] == 1) ? 'Published' : 'Draft' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="d-block"><?= htmlspecialchars($row['created_by']) ?></span>
                                        <small class="text-muted"><?= date("d/m/Y", strtotime($row['created_at'])) ?></small>
                                    </td>
                                    <?php if ($is_super): ?>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" class="btn btn-primary btn-xs sharp" 
                                                    data-bs-toggle="modal" data-bs-target="#eventModal"
                                                    onclick='editEvent(<?= json_encode($row) ?>)'>
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-xs sharp"
                                                    onclick="confirmDelete(<?= $row['id'] ?>)">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <?php endif; ?>
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
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&university_id=<?= urlencode($filter_uni) ?>&event_date=<?= urlencode($filter_date) ?>&status=<?= urlencode($filter_status) ?>">
                                        <i class="fa fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&university_id=<?= urlencode($filter_uni) ?>&event_date=<?= urlencode($filter_date) ?>&status=<?= urlencode($filter_status) ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&university_id=<?= urlencode($filter_uni) ?>&event_date=<?= urlencode($filter_date) ?>&status=<?= urlencode($filter_status) ?>">
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

<?php if ($is_super): ?>
<!-- ── ADD / EDIT EVENT MODAL ────────────────────────────────────────── -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Event Registration Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="modalForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="modal_edit_id">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-dark font-w600">Event Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="modal_title" class="form-control" required placeholder="Enter event title">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label text-dark font-w600">Event Description</label>
                            <textarea name="description" id="modal_description" class="form-control" rows="3" placeholder="Enter event description"></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Target University <span class="text-danger">*</span></label>
                            <select name="university_id" id="modal_university_id" class="form-select" required>
                                <option value="all">All Universities</option>
                                <?php foreach ($adminPrefixFullNames as $key => $name): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Event Date <span class="text-danger">*</span></label>
                            <input type="text" name="event_date" id="modal_event_date" class="form-control" required placeholder="Select Date">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-dark font-w600">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="modal_start_time" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-dark font-w600">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="modal_end_time" class="form-control" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label text-dark font-w600">Event Type</label>
                            <input type="text" name="event_type" id="modal_event_type" class="form-control" placeholder="e.g. Workshop, Seminar">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Venue <span class="text-danger">*</span></label>
                            <input type="text" name="venue" id="modal_venue" class="form-control" required placeholder="Enter venue location">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Coordinator / Host Name</label>
                            <input type="text" name="coordinator" id="modal_coordinator" class="form-control" placeholder="e.g. Dr. Anil Kumar">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Visibility</label>
                            <select name="visibility" id="modal_visibility" class="form-select">
                                <option value="public">Public</option>
                                <option value="university_only">University Only</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Status</label>
                            <select name="status" id="modal_status" class="form-select">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label text-dark font-w600">Banner / Image File</label>
                            <input type="file" name="image" id="modal_image" class="form-control" accept="image/*">
                            <div class="form-text">Optional. Max 10MB (JPG, PNG, WEBP)</div>
                        </div>

                        <div class="col-md-6 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="publish_status" id="modal_publish_status" checked>
                                <label class="form-check-label text-dark font-w600" for="modal_publish_status">Publish Immediately</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success text-white" id="modalSubmitBtn">Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── DELETE CONFIRMATION MODAL ────────────────────────────────────── -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-dark" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-2 text-dark">
                Are you sure you want to permanently delete this calendar event? This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="modalDeleteExecutionLink" class="btn btn-sm btn-danger text-white">Delete</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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

<!-- Flatpickr Script & Init -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr("#filter_date", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        <?php if ($is_super): ?>
        flatpickr("#modal_event_date", {
            dateFormat: "Y-m-d",
            allowInput: true
        });
        <?php endif; ?>
    });

    <?php if ($is_super): ?>
    const modalTitle     = document.getElementById('eventModalLabel');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalForm      = document.getElementById('modalForm');
    const modalDeleteExecutionLink = document.getElementById('modalDeleteExecutionLink');
    const bootstrapDeleteInstance  = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    // Prepare clean form on Add click
    document.getElementById('addNewBtn').addEventListener('click', function() {
        modalForm.reset();
        document.getElementById('modal_edit_id').value = '';
        modalTitle.innerText = 'Event Registration Form';
        modalSubmitBtn.innerText = 'Save Event';
        document.getElementById('modal_publish_status').checked = true;
    });

    function editEvent(row) {
        modalForm.reset();
        modalTitle.innerText = 'Edit Calendar Event';
        modalSubmitBtn.innerText = 'Update Event';

        document.getElementById('modal_edit_id').value = row.id;
        document.getElementById('modal_title').value = row.title;
        document.getElementById('modal_description').value = row.description;
        document.getElementById('modal_university_id').value = row.university_id;
        document.getElementById('modal_event_date').value = row.event_date;
        document.getElementById('modal_start_time').value = row.start_time;
        document.getElementById('modal_end_time').value = row.end_time;
        document.getElementById('modal_event_type').value = row.event_type;
        document.getElementById('modal_venue').value = row.venue;
        document.getElementById('modal_coordinator').value = row.coordinator;
        document.getElementById('modal_visibility').value = row.visibility;
        document.getElementById('modal_status').value = row.status;
        document.getElementById('modal_publish_status').checked = (row.publish_status == 1);
    }

    function confirmDelete(id) {
        modalDeleteExecutionLink.href = 'event_calendar.php?action=delete&id=' + id;
        bootstrapDeleteInstance.show();
    }
    <?php endif; ?>
</script>
</body>
</html>
