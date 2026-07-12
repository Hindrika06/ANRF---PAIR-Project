<?php
require_once 'config.php';
include 'header.php';

// Load all gallery events from all institute prefixes
$allEvents = [];
try {
    $tables = $pdo->query("SHOW TABLES LIKE '%_gallery_events'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $tbl) {
        $rows = $pdo->query("SELECT * FROM `$tbl` ORDER BY event_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $allEvents = array_merge($allEvents, $rows);
    }
    // Sort by event_date descending
    usort($allEvents, function($a, $b) {
        return strtotime($b['event_date'] ?? '1970-01-01') - strtotime($a['event_date'] ?? '1970-01-01');
    });
} catch (PDOException $e) {
    // silently skip
}
?>

<body class="page-homepage-courses" style="font-size: 16px; line-height: 1.6;">
<div class="wrapper">

<div id="page-content">
    <div class="container">
        <ol class="breadcrumb" style="font-size: 14px;">
            <li><a href="index.php">Home</a></li>
            <li class="active">Gallery</li>
        </ol>
    </div>

    <section id="course-detail" style="margin-top: 15px;">
        <div class="block" style="background-color: #fff;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="course-info">
                            <div class="overview-content">

                                <div>
                                    <h2 class="no-theme-underline" style="font-size: 24px !important; font-weight: bold !important; text-transform: uppercase !important; text-decoration: none !important; border: none !important; border-left: 5px solid #002b5c !important; padding-left: 12px !important; margin-top: 25px !important; margin-bottom: 20px !important; color: #002b5c !important; background: none !important; background-image: none !important; box-shadow: none !important; outline: none !important;">PHOTO GALLERY</h2>
                                </div>

                                <?php if (empty($allEvents)): ?>
                                <div style="background:#f9fafb; border-radius:12px; border: 2px dashed #d1d5db; padding: 60px 20px; text-align:center;">
                                    <i class="fas fa-images" style="font-size:2.8rem; color:#9ca3af;"></i>
                                    <h5 style="color:#6b7280; margin-top: 16px;">No gallery events available yet.</h5>
                                    <p style="color:#9ca3af; font-size:14px;">Events added through the admin portal will appear here.</p>
                                </div>
                                <?php else: ?>

                                <!-- Gallery Events Table -->
                                <div class="gallery-table-wrap">
                                    <table class="gallery-public-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 4%;">S.No</th>
                                                <th style="width: 34%;">Event Name</th>
                                                <th style="width: 20%;">Coordinator</th>
                                                <th style="width: 14%;">Date</th>
                                                <th style="width: 12%;">Category</th>
                                                <th style="width: 16%;">Photos</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach ($allEvents as $ev): ?>
                                            <tr>
                                                <td style="text-align:center;">
                                                    <span class="gallery-row-num"><?= $i++ ?></span>
                                                </td>
                                                <td>
                                                    <strong class="gallery-event-name"><?= htmlspecialchars($ev['event_name'] ?: 'Unnamed Event') ?></strong>
                                                    <?php if (!empty($ev['description'])): ?>
                                                    <span class="gallery-event-desc"><?= htmlspecialchars(mb_substr($ev['description'], 0, 85)) ?><?= mb_strlen($ev['description']) > 85 ? '…' : '' ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="gallery-coord"><?= htmlspecialchars($ev['coordinator_name'] ?: '—') ?></span>
                                                </td>
                                                <td>
                                                    <span class="gallery-date">
                                                        <?= !empty($ev['event_date']) ? date('d M Y', strtotime($ev['event_date'])) : '—' ?>
                                                    </span>
                                                    <?php if (!empty($ev['event_date']) && strtotime($ev['event_date']) > time()): ?>
                                                    <span class="gallery-upcoming-badge">Upcoming</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="gallery-cat-pill"><?= htmlspecialchars($ev['category'] ?: 'General') ?></span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($ev['photos_drive_link'])): ?>
                                                    <a href="<?= htmlspecialchars($ev['photos_drive_link']) ?>"
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="gallery-drive-link">
                                                        <i class="fab fa-google-drive"></i> View Photos
                                                    </a>
                                                    <?php else: ?>
                                                    <span style="font-size:12px; color:#94a3b8; font-style:italic;">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php endif; ?>

                            </div><!-- /overview-content -->
                        </div><!-- /course-info -->
                    </div>
                </div>
            </div>
        </div>
    </section>

</div><!-- /#page-content -->
</div><!-- /.wrapper -->

<style>
/* ── Heading overrides ── */
h2.no-theme-underline::after,
h2.no-theme-underline::before,
.overview-content h2::after,
.overview-content h2::before,
#course-detail h2::after,
#course-detail h2::before {
    display: none !important;
    content: none !important;
    border: none !important;
    background: none !important;
    height: 0 !important;
    width: 0 !important;
}

/* ── Gallery Table ── */
.gallery-table-wrap {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    margin-bottom: 40px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.gallery-public-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.gallery-public-table thead th {
    background-color: #002b5c;
    color: #fff;
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    padding: 12px 16px;
    border: none;
    white-space: nowrap;
}

.gallery-public-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.18s;
}
.gallery-public-table tbody tr:last-child { border-bottom: none; }
.gallery-public-table tbody tr:hover { background-color: #f8fafc; }

.gallery-public-table tbody td {
    padding: 13px 16px;
    vertical-align: middle;
    color: #334155;
}

/* Row number circle */
.gallery-row-num {
    width: 24px;
    height: 24px;
    background: #002b5c;
    color: #fff;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 10px;
}

/* Event name */
.gallery-event-name {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 3px;
    line-height: 1.3;
}
.gallery-event-desc {
    display: block;
    font-size: 11px;
    color: #64748b;
    line-height: 1.4;
}

/* Coordinator */
.gallery-coord {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
}

/* Date */
.gallery-date {
    font-size: 12px;
    font-weight: 600;
    color: #1e293b;
    display: block;
}
.gallery-upcoming-badge {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    background: #dcfce7;
    color: #15803d;
    padding: 2px 7px;
    border-radius: 10px;
    margin-top: 3px;
    text-transform: uppercase;
}

/* Category pill */
.gallery-cat-pill {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    color: #0d47a1;
    background: #e3f2fd;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

/* Drive link */
.gallery-drive-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #15803d;
    background: #dcfce7;
    padding: 5px 12px;
    border-radius: 6px;
    text-decoration: none !important;
    transition: background 0.2s;
    white-space: nowrap;
}
.gallery-drive-link:hover {
    background: #bbf7d0;
    color: #166534;
    text-decoration: none !important;
}

/* Responsive */
@media (max-width: 768px) {
    .gallery-public-table { font-size: 12px; }
    .gallery-public-table tbody td { padding: 10px 10px; }
}
</style>

</body>
<?php include 'footer.php'; ?>
