<?php
require_once 'auth_check.php';
require_once 'role_access.php';

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

$table = "{$prefix}_gallery_events";

// ── Database ────────────────────────────────────────────────────────────────
require_once 'config/db.php';

$success = false;
$error   = '';

// Self-healing: create table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `$table` (
            `id`                INT AUTO_INCREMENT PRIMARY KEY,
            `event_name`        VARCHAR(300) NOT NULL,
            `coordinator_name`  VARCHAR(200) DEFAULT '',
            `event_date`        DATE NULL,
            `photos_drive_link` VARCHAR(1000) DEFAULT '',
            `category`          VARCHAR(100)  DEFAULT 'General',
            `description`       TEXT,
            `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Seed sample data if table is empty
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    if ((int)$count === 0) {
        $seeds = [
            ['Annual Research Symposium 2024', 'Dr. Meena Iyer',     '2024-11-15', 'https://drive.google.com/drive/folders/sample1', 'Symposium',  'Annual research showcase bringing together all project PIs.'],
            ['Lab Inauguration Ceremony',       'Prof. K. Ramachandran', '2024-09-02', 'https://drive.google.com/drive/folders/sample2', 'Ceremony',   'Inauguration of the new Biomedical Instrumentation Lab.'],
            ['International Conference on Medical AI', 'Dr. Aris Thorne', '2025-01-20', 'https://drive.google.com/drive/folders/sample3', 'Conference', 'Participation in ICMAI 2025 with paper presentations.'],
            ['Student Training Workshop – Bioinformatics', 'Dr. Leila Hasan', '2025-03-10', 'https://drive.google.com/drive/folders/sample4', 'Workshop', '3-day hands-on bioinformatics workshop for graduate students.'],
            ['Project Review Meeting – Q2 2025', 'Prof. Ishaan Gupta', '2025-04-05', 'https://drive.google.com/drive/folders/sample5', 'Meeting', 'Quarterly review with ANRF–PAIR project committee.'],
            ['Field Visit – AIIMS Delhi',         'Dr. Sanya Mehta',   '2025-02-22', 'https://drive.google.com/drive/folders/sample6', 'Field Visit', 'Team visit to AIIMS for collaborative clinical research.'],
        ];
        $ins = $pdo->prepare("INSERT INTO `$table` (event_name, coordinator_name, event_date, photos_drive_link, category, description) VALUES (?,?,?,?,?,?)");
        foreach ($seeds as $s) {
            $ins->execute($s);
        }
    }
} catch (PDOException $e) {
    // ignore table creation errors silently
}

// 1. HANDLE DELETE
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

// 2. POST-REDIRECT SUCCESS
if (isset($_GET['success_msg'])) {
    $success = true;
}

// 3. HANDLE FORM SUBMISSIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name        = trim($_POST['event_name']        ?? '');
    $coordinator_name  = trim($_POST['coordinator_name']  ?? '');
    $event_date        = $_POST['event_date']             ?? '';
    $photos_drive_link = trim($_POST['photos_drive_link'] ?? '');
    $category          = trim($_POST['category']          ?? 'General');
    $description       = trim($_POST['description']       ?? '');
    $edit_id           = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    if (!canEditInstitute($prefix)) {
        $error = 'You are not allowed to update records for this institute.';
    } else {
        try {
            if ($edit_id) {
                $stmt = $pdo->prepare("
                    UPDATE `$table` SET
                        event_name        = :event_name,
                        coordinator_name  = :coordinator_name,
                        event_date        = :event_date,
                        photos_drive_link = :photos_drive_link,
                        category          = :category,
                        description       = :description
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':event_name'        => $event_name,
                    ':coordinator_name'  => $coordinator_name,
                    ':event_date'        => $event_date ?: null,
                    ':photos_drive_link' => $photos_drive_link,
                    ':category'          => $category,
                    ':description'       => $description,
                    ':id'                => $edit_id,
                ]);
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=updated");
                exit;
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO `$table`
                        (event_name, coordinator_name, event_date, photos_drive_link, category, description, created_at)
                    VALUES
                        (:event_name, :coordinator_name, :event_date, :photos_drive_link, :category, :description, NOW())
                ");
                $stmt->execute([
                    ':event_name'        => $event_name,
                    ':coordinator_name'  => $coordinator_name,
                    ':event_date'        => $event_date ?: null,
                    ':photos_drive_link' => $photos_drive_link,
                    ':category'          => $category,
                    ':description'       => $description,
                ]);
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success_msg=inserted");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// 4. FETCH ALL RECORDS
$events = [];
try {
    $stmt   = $pdo->query("SELECT * FROM `$table` ORDER BY id DESC");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Could not load data records: ' . $e->getMessage();
}

$total_records = count($events);

// 5. STATS
$total_events      = $total_records;
$unique_coords     = [];
$upcoming_count    = 0;
$categories_count  = [];
$current_time      = time();

foreach ($events as $ev) {
    if (!empty(trim($ev['coordinator_name']))) {
        $unique_coords[trim($ev['coordinator_name'])] = true;
    }
    if (!empty($ev['event_date']) && strtotime($ev['event_date']) > $current_time) {
        $upcoming_count++;
    }
    $cat = $ev['category'] ?: 'General';
    $categories_count[$cat] = true;
}
$total_coordinators = count($unique_coords);
$total_categories   = count($categories_count);
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">

<style>
    /* ──── REGISTRY TABLE STYLING ──── */
    .registry-card {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04) !important;
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
        padding: 11px 16px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155;
    }

    /* Index badge */
    .index-badge-circle {
        width: 24px;
        height: 24px;
        background-color: #024283;
        color: #ffffff;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 10px;
    }

    /* Category pill */
    .cat-pill {
        display: inline-block;
        font-size: 9px;
        font-weight: 700;
        color: #0d47a1;
        background-color: #e3f2fd;
        padding: 2px 9px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Drive link button */
    .drive-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: #15803d;
        background: #dcfce7;
        padding: 4px 10px;
        border-radius: 6px;
        text-decoration: none !important;
        transition: background 0.2s;
    }
    .drive-link-btn:hover {
        background: #bbf7d0;
        color: #166534;
    }
    .no-drive-text {
        font-size: 11px;
        color: #94a3b8;
        font-style: italic;
    }

    /* Action buttons */
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
    .btn-action-compact:hover { transform: scale(1.06); }
    .btn-action-edit-yellow  { background-color: #ffca28 !important; color: #1a1a1a !important; }
    .btn-action-edit-yellow:hover { background-color: #ffb300 !important; }
    .btn-action-delete-red   { background-color: #ef4444 !important; color: #ffffff !important; }
    .btn-action-delete-red:hover { background-color: #dc2626 !important; }

    /* Pagination */
    .pagination-theme-sapphire .page-item.active .page-link {
        background-color: #024283 !important;
        border-color: #024283 !important;
        color: #fff !important;
    }
    .pagination-theme-sapphire .page-link { color: #024283; }

    /* KPI cards – teal-green theme */
    .kpi-widget-card {
        border-radius: 20px !important;
        padding: 20px 24px;
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        background: #0f766e !important;
    }
    .kpi-card-body { display: flex; flex-direction: column; align-items: center; text-align: center; width: 100%; }
    .kpi-icon-circle {
        width: 44px; height: 44px;
        background-color: rgba(255,255,255,0.22);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px;
    }
    .kpi-icon-circle i { font-size: 18px; color: #fff !important; }
    .kpi-title-text { font-size: 13px; font-weight: 500; opacity: .95; margin-bottom: 6px; }
    .kpi-metric-row { display: flex; align-items: baseline; justify-content: center; gap: 6px; }
    .kpi-metric-value { font-size: 28px; font-weight: 700; line-height: 1; }
    .kpi-subtext { font-size: 11px; opacity: .8; font-weight: 400; }

    /* Main title text */
    .registry-main-title {
        font-size: 13px; font-weight: 600;
        color: #1e293b; line-height: 1.3;
        display: block; margin-bottom: 3px;
    }
    .registry-sub-label { font-size: 11px; color: #64748b; display: block; }
    .registry-meta-text  { font-size: 12px; color: #334155; }
</style>


<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <div class="page-titles">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Events & Media</a></li>
                    <li class="breadcrumb-item active">Gallery Dashboard</li>
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

            <!-- ── KPI CARDS ── -->
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-camera"></i></div>
                            <span class="kpi-title-text">Total Events</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_events ?></span>
                                <span class="kpi-subtext">tracked</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-user-tie"></i></div>
                            <span class="kpi-title-text">Coordinators</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_coordinators ?></span>
                                <span class="kpi-subtext">Unique</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-calendar-check"></i></div>
                            <span class="kpi-title-text">Upcoming Events</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $upcoming_count ?></span>
                                <span class="kpi-subtext">Scheduled</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card kpi-widget-card">
                        <div class="kpi-card-body">
                            <div class="kpi-icon-circle"><i class="fa-solid fa-layer-group"></i></div>
                            <span class="kpi-title-text">Categories</span>
                            <div class="kpi-metric-row">
                                <span class="kpi-metric-value"><?= $total_categories ?></span>
                                <span class="kpi-subtext">Types</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── EVENTS TABLE ── -->
            <div class="col-lg-12 px-0">
                <div class="card registry-card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 bg-white border-0">
                        <h4 class="card-title mb-0" style="color: #024283; font-weight: 700; font-size: 15px;">
                            <i class="fa-solid fa-images me-2"></i>GALLERY EVENTS REGISTRY
                        </h4>
                        <?php if (canEditInstitute($prefix)): ?>
                        <button type="button" class="btn btn-success btn-sm text-white px-3"
                                data-bs-toggle="modal" data-bs-target="#galleryModal" id="addNewBtn"
                                style="border-radius: 4px; font-weight: 600;">
                            <i class="fa fa-plus me-1"></i> Add Gallery Event
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-theme-sapphire">
                            <thead>
                                <tr>
                                    <th style="width: 4%; text-align: center;">S.No</th>
                                    <th style="width: 30%;">Event Name</th>
                                    <th style="width: 18%;">Coordinator</th>
                                    <th style="width: 13%;">Date</th>
                                    <th style="width: 12%;">Category</th>
                                    <th style="width: 15%;">Photos Drive Link</th>
                                    <th style="width: 8%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($events)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4" style="font-size: 13px;">
                                            No gallery events recorded yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $rowCounter = 1; foreach ($events as $ev): ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="index-badge-circle"><?= $rowCounter++ ?></span>
                                            </td>
                                            <td>
                                                <span class="registry-main-title">
                                                    <?= htmlspecialchars($ev['event_name'] ?: 'Unnamed Event') ?>
                                                </span>
                                                <?php if (!empty($ev['description'])): ?>
                                                <span class="registry-sub-label">
                                                    <?= htmlspecialchars(mb_substr($ev['description'], 0, 70)) ?><?= mb_strlen($ev['description']) > 70 ? '…' : '' ?>
                                                </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="registry-meta-text font-w600 text-dark">
                                                    <?= htmlspecialchars($ev['coordinator_name'] ?: '—') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-dark font-w600" style="font-size: 12px;">
                                                    <?= !empty($ev['event_date']) ? date('d M Y', strtotime($ev['event_date'])) : '—' ?>
                                                </span>
                                                <?php
                                                    if (!empty($ev['event_date'])) {
                                                        $future = strtotime($ev['event_date']) > time();
                                                        echo $future
                                                            ? '<span class="registry-sub-label" style="color:#16a34a;">Upcoming</span>'
                                                            : '<span class="registry-sub-label">Completed</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="cat-pill"><?= htmlspecialchars($ev['category'] ?: 'General') ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($ev['photos_drive_link'])): ?>
                                                <a href="<?= htmlspecialchars($ev['photos_drive_link']) ?>"
                                                   target="_blank" class="drive-link-btn">
                                                    <i class="fa-brands fa-google-drive"></i> Open Folder
                                                </a>
                                                <?php else: ?>
                                                <span class="no-drive-text">No link added</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <?php if (canEditInstitute($prefix)): ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-edit-yellow edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#galleryModal"
                                                            data-id="<?= $ev['id'] ?>"
                                                            data-event="<?= htmlspecialchars($ev['event_name']) ?>"
                                                            data-coordinator="<?= htmlspecialchars($ev['coordinator_name'] ?? '') ?>"
                                                            data-date="<?= $ev['event_date'] ?? '' ?>"
                                                            data-drive="<?= htmlspecialchars($ev['photos_drive_link'] ?? '') ?>"
                                                            data-category="<?= htmlspecialchars($ev['category'] ?? 'General') ?>"
                                                            data-description="<?= htmlspecialchars($ev['description'] ?? '') ?>"
                                                            title="Edit Record">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-action-delete-red delete-confirm-trigger"
                                                            data-id="<?= $ev['id'] ?>"
                                                            title="Delete Record">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button"
                                                            class="btn btn-action-compact btn-info text-white edit-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#galleryModal"
                                                            data-view-only="true"
                                                            data-id="<?= $ev['id'] ?>"
                                                            data-event="<?= htmlspecialchars($ev['event_name']) ?>"
                                                            data-coordinator="<?= htmlspecialchars($ev['coordinator_name'] ?? '') ?>"
                                                            data-date="<?= $ev['event_date'] ?? '' ?>"
                                                            data-drive="<?= htmlspecialchars($ev['photos_drive_link'] ?? '') ?>"
                                                            data-category="<?= htmlspecialchars($ev['category'] ?? 'General') ?>"
                                                            data-description="<?= htmlspecialchars($ev['description'] ?? '') ?>"
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
                        <p class="mb-0 text-muted small font-w500">Total: <?= $total_records ?> gallery event(s)</p>
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

    <!-- ── ADD / EDIT MODAL ── -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg,#024283,#1565c0); color:#fff; border:none;">
                    <h5 class="modal-title" id="galleryModalLabel" style="color:#fff;">
                        <i class="fa-solid fa-camera me-2"></i>Gallery Event Form
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="modalForm">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="modal_edit_id">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label form-label-grey">Event Name <span class="text-danger">*</span></label>
                                <input type="text" name="event_name" id="modal_event_name" class="form-control" placeholder="e.g. Annual Research Symposium 2025" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label form-label-grey">Category</label>
                                <input type="text" name="category" id="modal_category" class="form-control" list="category-list" placeholder="e.g. Symposium, Workshop">
                                <datalist id="category-list">
                                    <option value="Symposium">
                                    <option value="Conference">
                                    <option value="Workshop">
                                    <option value="Ceremony">
                                    <option value="Field Visit">
                                    <option value="Meeting">
                                    <option value="Awards">
                                    <option value="General">
                                </datalist>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Coordinator Name</label>
                                <input type="text" name="coordinator_name" id="modal_coordinator_name" class="form-control" placeholder="e.g. Dr. Meena Iyer">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label form-label-grey">Date of Event</label>
                                <input type="text" name="event_date" id="modal_event_date" class="form-control" placeholder="Select date" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">
                                    <i class="fa-brands fa-google-drive me-1 text-success"></i>
                                    Photos Drive Link
                                </label>
                                <input type="url" name="photos_drive_link" id="modal_photos_drive_link" class="form-control" placeholder="https://drive.google.com/drive/folders/…">
                                <small class="text-muted">Paste the Google Drive shared folder URL for this event's photos.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label form-label-grey">Event Description / Notes</label>
                                <textarea name="description" id="modal_description" rows="3" class="form-control" placeholder="Brief description of the event…"></textarea>
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

    <!-- ── DELETE CONFIRMATION MODAL ── -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-dark" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2 text-dark" style="font-size: 13px;">
                    Are you sure you want to permanently delete this gallery event record? This cannot be undone.
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const addNewBtn    = document.getElementById('addNewBtn');
    const editButtons  = document.querySelectorAll('.edit-btn');
    const modalTitle   = document.getElementById('galleryModalLabel');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalForm    = document.getElementById('modalForm');

    const deleteTriggers = document.querySelectorAll('.delete-confirm-trigger');
    const modalDeleteLink = document.getElementById('modalDeleteExecutionLink');
    const bsDeleteModal  = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));

    const datePicker = flatpickr("#modal_event_date", {
        enableTime: false,
        dateFormat: "Y-m-d",
        altInput:   true,
        altFormat:  "d M, Y"
    });

    function enableForm() {
        modalForm.querySelectorAll('input, textarea, select').forEach(el => {
            el.disabled = false;
            el.readOnly = false;
        });
    }
    function disableForm() {
        modalForm.querySelectorAll('input, textarea, select').forEach(el => {
            el.disabled = true;
            el.readOnly = true;
        });
    }

    if (addNewBtn) {
        addNewBtn.addEventListener('click', function () {
            modalForm.reset();
            datePicker.clear();
            document.getElementById('modal_edit_id').value = '';
            modalTitle.innerHTML = '<i class="fa-solid fa-camera me-2"></i>Add Gallery Event';
            modalSubmitBtn.innerText = 'Save Event';
            modalSubmitBtn.style.display = 'block';
            enableForm();
        });
    }

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const isViewOnly = this.getAttribute('data-view-only') === 'true';

            document.getElementById('modal_edit_id').value           = this.getAttribute('data-id');
            document.getElementById('modal_event_name').value        = this.getAttribute('data-event');
            document.getElementById('modal_coordinator_name').value  = this.getAttribute('data-coordinator');
            document.getElementById('modal_photos_drive_link').value = this.getAttribute('data-drive');
            document.getElementById('modal_category').value          = this.getAttribute('data-category');
            document.getElementById('modal_description').value       = this.getAttribute('data-description');

            const dateVal = this.getAttribute('data-date');
            if (dateVal) {
                datePicker.setDate(dateVal, true);
            } else {
                datePicker.clear();
            }

            if (isViewOnly) {
                modalTitle.innerHTML = '<i class="fa-solid fa-eye me-2"></i>View Gallery Event';
                modalSubmitBtn.style.display = 'none';
                disableForm();
            } else {
                modalTitle.innerHTML = '<i class="fa-solid fa-pencil me-2"></i>Edit Gallery Event';
                modalSubmitBtn.innerText = 'Save Changes';
                modalSubmitBtn.style.display = 'block';
                enableForm();
            }
        });
    });

    deleteTriggers.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            modalDeleteLink.setAttribute('href', '?action=delete&id=' + id);
            bsDeleteModal.show();
        });
    });
});
</script>
</body>
</html>
