<?php
$bodyClass = 'page-homepage-courses';

require_once 'config.php';

$reports = [];
$hasData = false;

try {
    $prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
    $reports  = [];

    foreach ($prefixes as $p) {
        $tbl = "{$p}_progress_reports";
        $pubsTbl = "{$p}_progress_report_publications";
        $eventsTbl = "{$p}_progress_report_capacity_events";

        try {
            $check = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($check > 0) {
                $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
                $hasApproval = in_array('approval_status', $cols, true);
                $hasPublish  = in_array('publish_status', $cols, true);

                $whereConditions = [];
                if ($hasApproval) {
                    $whereConditions[] = "(approval_status = 'Approved' OR approval_status IS NULL)";
                }
                if ($hasPublish) {
                    $whereConditions[] = "(publish_status = 1 OR publish_status IS NULL)";
                }

                $whereClause = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";
                $stmt = $pdo->query("SELECT *, '$p' AS institute_prefix FROM `$tbl` $whereClause");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($rows) {
                    foreach ($rows as &$r) {
                        $prId = (int)$r['id'];
                        $r['publications'] = [];
                        $r['workshops']    = [];
                        $r['trainings']    = [];

                        // Fetch publications
                        try {
                            $stmtP = $pdo->prepare("SELECT * FROM `$pubsTbl` WHERE progress_report_id = :pr_id AND (approval_status = 'Approved' OR approval_status IS NULL) ORDER BY id DESC");
                            $stmtP->execute([':pr_id' => $prId]);
                            $r['publications'] = $stmtP->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $ex) {}

                        // Fetch capacity building events
                        try {
                            $stmtE = $pdo->prepare("SELECT * FROM `$eventsTbl` WHERE progress_report_id = :pr_id ORDER BY id DESC");
                            $stmtE->execute([':pr_id' => $prId]);
                            $evs = $stmtE->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($evs as $ev) {
                                if ($ev['category'] === 'Workshop_Conference') {
                                    $r['workshops'][] = $ev;
                                } else {
                                    $r['trainings'][] = $ev;
                                }
                            }
                        } catch (Exception $ex) {}
                    }
                    unset($r);

                    $reports = array_merge($reports, $rows);
                }
            }
        } catch (Exception $e) {}
    }

    if (!empty($reports)) {
        usort($reports, function($a, $b) {
            $tA = !empty($a['created_at']) ? strtotime($a['created_at']) : 0;
            $tB = !empty($b['created_at']) ? strtotime($b['created_at']) : 0;
            if ($tA === $tB) {
                return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
            }
            return $tB <=> $tA;
        });
        $hasData = true;
    }
} catch (PDOException $e) {
    echo "<div class='container' style='margin-top:20px;'><div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div></div>";
}
?>

<!-- Header -->
<?php include 'header.php';?>
<!-- end Header -->

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li class="active">Progress Reports</li>
    </ol>
</div>
<!-- end Breadcrumb -->

<!-- Page Content -->
<div id="page-content">
    <div class="pr-page">
        <div class="container">

            <div class="pr-header">
                <p class="pr-eyebrow">ANRF&ndash;PAIR Project</p>
                <div class="pr-title" role="heading" aria-level="1">Progress Reports</div>
                <p class="pr-subtitle">Status updates on active research tasks, covering objectives, methodology, published research, and capacity building outcomes.</p>
            </div>

            <?php if ($hasData): ?>
            <div class="pr-cards-list">
                <?php foreach ($reports as $idx => $row):
                    $instCode = strtoupper($row['institute_prefix'] ?? 'ANRF');
                    $pubs     = $row['publications'] ?? [];
                    $workshops = $row['workshops'] ?? [];
                    $trainings = $row['trainings'] ?? [];
                    $internsCount = (int)($row['interns_trained_count'] ?? 0);
                ?>
                    <div class="pr-report-card">
                        <!-- Card Header -->
                        <div class="pr-card-header">
                            <div class="pr-header-top">
                                <span class="pr-inst-badge"><?= htmlspecialchars($instCode) ?></span>
                                <?php if (!empty($row['task_no'])): ?>
                                    <span class="pr-task-badge"><i class="fa fa-list-check me-1"></i> <?= htmlspecialchars($row['task_no']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($row['work_package_no'])): ?>
                                    <span class="pr-wp-badge"><i class="fa fa-box-archive me-1"></i> <?= htmlspecialchars($row['work_package_no']) ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="pr-card-title"><?= htmlspecialchars($row['project_title'] ?? 'Untitled Project') ?></h3>
                            <div class="pr-card-authors">
                                <?php if (!empty($row['pi_name'])): ?>
                                    <span class="pr-author-item"><strong>PI:</strong> <?= htmlspecialchars($row['pi_name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($row['co_pi_name'])): ?>
                                    <span class="pr-author-item"><strong>Co-PI:</strong> <?= htmlspecialchars($row['co_pi_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Body: Core Grid -->
                        <div class="pr-card-body">
                            <div class="pr-grid-3">
                                <div class="pr-info-box">
                                    <h4 class="pr-box-heading"><i class="fa fa-bullseye me-1"></i> Approved Objectives</h4>
                                    <p class="pr-box-text"><?= !empty($row['approved_objects']) ? nl2br(htmlspecialchars($row['approved_objects'])) : '&mdash;' ?></p>
                                </div>
                                <div class="pr-info-box">
                                    <h4 class="pr-box-heading"><i class="fa fa-flask me-1"></i> Methodology</h4>
                                    <p class="pr-box-text"><?= !empty($row['methodology']) ? nl2br(htmlspecialchars($row['methodology'])) : '&mdash;' ?></p>
                                </div>
                                <div class="pr-info-box">
                                    <h4 class="pr-box-heading"><i class="fa fa-chart-line me-1"></i> Summary Progress</h4>
                                    <p class="pr-box-text"><?= !empty($row['summary_progress']) ? nl2br(htmlspecialchars($row['summary_progress'])) : '&mdash;' ?></p>
                                </div>
                            </div>

                            <hr class="pr-divider">

                            <!-- PUBLICATIONS SECTION -->
                            <div class="pr-section-block">
                                <h4 class="pr-section-title"><i class="fa fa-book me-2"></i> PUBLICATIONS</h4>
                                <?php if (empty($pubs)): ?>
                                    <p class="pr-empty-text"><i class="fa fa-info-circle me-1" style="color: #6b21a8 !important;"></i> No publication details added yet.</p>
                                <?php else: ?>
                                    <div class="pr-sub-table-wrap">
                                        <table class="pr-sub-table">
                                            <thead>
                                                <tr>
                                                    <th>Task No</th>
                                                    <th>Publication Title</th>
                                                    <th>Primary Author</th>
                                                    <th>Journal Name</th>
                                                    <th>DOI / Date</th>
                                                    <th>Impact Factor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pubs as $p): ?>
                                                    <tr>
                                                        <td><strong><?= htmlspecialchars($p['task_no'] ?: '—') ?></strong></td>
                                                        <td class="pr-title-col"><strong><?= htmlspecialchars($p['publication_title']) ?></strong></td>
                                                        <td><?= htmlspecialchars($p['author_name']) ?></td>
                                                        <td><?= htmlspecialchars($p['publication_journal']) ?></td>
                                                        <td>
                                                            <?php if (!empty($p['doi_number'])): ?>
                                                                <small class="d-block text-muted">DOI: <?= htmlspecialchars($p['doi_number']) ?></small>
                                                            <?php endif; ?>
                                                            <?php if (!empty($p['publication_date'])): ?>
                                                                <small class="text-muted"><?= htmlspecialchars($p['publication_date']) ?></small>
                                                            <?php endif; ?>
                                                            <?php if (empty($p['doi_number']) && empty($p['publication_date'])): ?>
                                                                &mdash;
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><span class="pr-if-badge"><?= !empty($p['impact_factor']) ? htmlspecialchars($p['impact_factor']) : '&mdash;' ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr class="pr-divider">

                            <!-- CAPACITY BUILDING SECTION -->
                            <div class="pr-section-block">
                                <h4 class="pr-section-title"><i class="fa fa-graduation-cap me-2"></i> CAPACITY BUILDING</h4>

                                <div class="pr-cb-grid">
                                    <!-- A. Workshops / Conferences Conducted -->
                                    <div class="pr-cb-box">
                                        <h5 class="pr-cb-subtitle">Workshops / Conferences Conducted</h5>
                                        <?php if (empty($workshops)): ?>
                                            <p class="pr-empty-text"><i class="fa fa-info-circle me-1" style="color: #6b21a8 !important;"></i> No workshops or conferences recorded yet.</p>
                                        <?php else: ?>
                                            <ul class="pr-cb-list">
                                                <?php foreach ($workshops as $w): ?>
                                                    <li class="pr-cb-item">
                                                        <div class="pr-cb-item-title"><?= htmlspecialchars($w['title']) ?></div>
                                                        <div class="pr-cb-item-meta">
                                                            <?php if (!empty($w['event_date'])): ?>
                                                                <span><i class="fa fa-calendar me-1"></i> <?= htmlspecialchars($w['event_date']) ?></span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($w['venue_mode'])): ?>
                                                                <span><i class="fa fa-location-dot me-1"></i> <?= htmlspecialchars($w['venue_mode']) ?></span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($w['participant_count'])): ?>
                                                                <span class="badge bg-info text-white"><i class="fa fa-users me-1"></i> <?= (int)$w['participant_count'] ?> Participants</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (!empty($w['description'])): ?>
                                                            <p class="pr-cb-item-desc"><?= htmlspecialchars($w['description']) ?></p>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>

                                    <!-- B. Training Programs Conducted -->
                                    <div class="pr-cb-box">
                                        <h5 class="pr-cb-subtitle">Training Programs Conducted</h5>
                                        <?php if (empty($trainings)): ?>
                                            <p class="pr-empty-text"><i class="fa fa-info-circle me-1" style="color: #6b21a8 !important;"></i> No training programs recorded yet.</p>
                                        <?php else: ?>
                                            <ul class="pr-cb-list">
                                                <?php foreach ($trainings as $t): ?>
                                                    <li class="pr-cb-item">
                                                        <div class="pr-cb-item-title"><?= htmlspecialchars($t['title']) ?></div>
                                                        <div class="pr-cb-item-meta">
                                                            <?php if (!empty($t['event_date']) || !empty($t['duration'])): ?>
                                                                <span><i class="fa fa-clock me-1"></i> <?= htmlspecialchars($t['event_date'] ?: '') ?> <?= htmlspecialchars($t['duration'] ? "({$t['duration']})" : '') ?></span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($t['venue_mode'])): ?>
                                                                <span><i class="fa fa-location-dot me-1"></i> <?= htmlspecialchars($t['venue_mode']) ?></span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($t['participant_count'])): ?>
                                                                <span class="badge bg-info text-white"><i class="fa fa-users me-1"></i> <?= (int)$t['participant_count'] ?> Participants</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (!empty($t['description'])): ?>
                                                            <p class="pr-cb-item-desc"><?= htmlspecialchars($t['description']) ?></p>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- C. Number of Interns Trained -->
                                <div class="pr-interns-banner">
                                    <div class="pr-interns-label">
                                        <strong>Number of Interns Trained:</strong>
                                    </div>
                                    <div class="pr-interns-count">
                                        <?= $internsCount ?>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="pr-empty">
                    <p>No progress reports on file yet.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<!-- end Page Content -->

<style>
    :root {
        --pr-red: #B33A3A;
        --pr-red-dark: #8C2424;
        --pr-ink: #1F2937;
        --pr-slate: #4B5563;
        --pr-panel: #FAF5F4;
        --pr-hair: #E5E7EB;
    }

    .pr-page { padding: 10px 0 60px !important; background: #f8fafc !important; }

    .pr-header {
        max-width: 720px !important;
        margin: 10px auto 36px !important;
        text-align: center !important;
    }
    .pr-eyebrow {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        color: var(--pr-red) !important;
        margin: 0 0 10px !important;
    }
    .pr-title {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        font-weight: 700 !important;
        font-size: 32px !important;
        color: var(--pr-ink) !important;
        margin: 0 0 12px !important;
    }
    .pr-subtitle {
        font-size: 15px !important;
        color: var(--pr-slate) !important;
        line-height: 1.6 !important;
    }

    .pr-cards-list {
        max-width: 1180px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .pr-report-card {
        background: #ffffff;
        border: 1px solid var(--pr-hair);
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .pr-card-header {
        background: #024283;
        color: #ffffff;
        padding: 20px 24px;
    }

    .pr-header-top {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .pr-inst-badge {
        background: #bc2121;
        color: #fff;
        font-weight: 800;
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 4px;
        letter-spacing: 0.5px;
    }

    .pr-task-badge, .pr-wp-badge {
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 4px;
    }

    .pr-card-title {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 8px 0;
        color: #ffffff;
    }

    .pr-card-authors {
        font-size: 13.5px;
        color: rgba(255,255,255,0.9);
        display: flex;
        gap: 16px;
    }

    .pr-card-body {
        padding: 24px;
    }

    .pr-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }

    @media (max-width: 900px) {
        .pr-grid-3 {
            grid-template-columns: 1fr;
        }
    }

    .pr-info-box {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .pr-box-heading {
        font-size: 12.5px;
        font-weight: 800;
        color: #6b21a8;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin: 0 0 6px 0;
    }

    .pr-box-text {
        font-size: 13.5px;
        color: #1e293b;
        font-weight: 400;
        line-height: 1.6;
        margin: 0;
    }

    .pr-divider {
        margin: 24px 0;
        border: 0;
        border-top: 1px solid #cbd5e1;
    }

    .pr-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 14px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pr-empty-text {
        font-size: 13.5px;
        color: #1e293b;
        font-style: italic;
        font-weight: 500;
        background: #ffffff;
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        margin: 0;
        display: block;
    }

    .pr-sub-table-wrap {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        overflow: hidden;
    }

    .pr-sub-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .pr-sub-table thead th {
        background: #bc2121 !important;
        color: #ffffff !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.6px;
        padding: 11px 14px;
        border: none;
    }

    .pr-sub-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        color: #0f172a;
    }

    .pr-if-badge {
        background: #e0f2fe;
        color: #0369a1;
        font-weight: 700;
        font-size: 11.5px;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .pr-cb-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
        margin-bottom: 18px;
    }

    @media (max-width: 800px) {
        .pr-cb-grid {
            grid-template-columns: 1fr;
        }
    }

    .pr-cb-box {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 16px;
    }

    .pr-cb-subtitle {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 12px 0;
    }

    .pr-cb-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .pr-cb-item {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 10px;
    }

    .pr-cb-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .pr-cb-item-title {
        font-weight: 700;
        font-size: 13.5px;
        color: #024283;
    }

    .pr-cb-item-meta {
        font-size: 12px;
        color: #475569;
        display: flex;
        gap: 12px;
        margin: 4px 0;
        flex-wrap: wrap;
    }

    .pr-cb-item-desc {
        font-size: 13px;
        color: #1e293b;
        margin: 4px 0 0 0;
    }

    .pr-interns-banner {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pr-interns-label {
        font-size: 14.5px;
        color: #1e293b;
        font-weight: 700;
    }

    .pr-interns-count {
        font-size: 22px;
        font-weight: 800;
        color: #ffffff;
        background: #6b21a8;
        padding: 4px 16px;
        border-radius: 6px;
        border: 1px solid #581c87;
    }

    .pr-interns-count {
        font-size: 22px;
        font-weight: 800;
        color: #15803d;
        background: #ffffff;
        padding: 4px 16px;
        border-radius: 6px;
        border: 1.5px solid #4ade80;
    }

    .pr-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--pr-slate);
        border: 1px dashed var(--pr-hair);
        border-radius: 8px;
        max-width: 1180px;
        margin: 0 auto;
    }
</style>

<!-- Footer -->
<?php include 'footer.php';?>
