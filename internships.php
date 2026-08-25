<?php
$bodyClass = 'page-homepage-courses'; 
require_once 'config.php'; 

$internships = [];
$hasData = false;
$totalStudents = 0;
$totalDays = 0;

try {
    $prefixes    = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
    $internships = [];

    foreach ($prefixes as $p) {
        $tbl = "{$p}_internships";
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
                    $internships = array_merge($internships, $rows);
                }
            }
        } catch (Exception $e) {}
    }

    if (!empty($internships)) {
        usort($internships, function($a, $b) {
            $tA = !empty($a['created_at']) ? strtotime($a['created_at']) : 0;
            $tB = !empty($b['created_at']) ? strtotime($b['created_at']) : 0;
            if ($tA === $tB) {
                return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
            }
            return $tB <=> $tA;
        });
        $hasData = true;
        foreach ($internships as $r) {
            $totalStudents += (int)($r['no_students_trained'] ?? 0);
            $totalDays     += (int)($r['no_days_trained'] ?? 0);
        }
    }
} catch (PDOException $e) {
    error_log("Internships database error: " . $e->getMessage());
    echo "<div class='container' style='margin-top:20px;'><div class='alert alert-warning'>Unable to load internship records at this time. Please try again later.</div></div>";
}
?>

<?php include 'header.php';?>

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li class="active">Internships & Training Records</li>
    </ol>
</div>
<!-- end Breadcrumb -->

<!-- ── HERO SECTION ── -->
<section class="itr-hero">
    <div class="container">
        <div class="itr-hero-inner">
            <!-- LEFT: Hero Header (Stacked on separate lines, left-aligned) -->
            <div class="itr-hero-content">
                <span class="itr-hero-eyebrow">ANRF&ndash;PAIR INITIATIVE</span>
                <h1 class="itr-hero-title">Internships &amp; Training Records</h1>
                <p class="itr-hero-subtitle">
                    A curated log of internship and training cohorts conducted under the ANRF-PAIR initiative,
                    tracking faculty mentors, student participation, and key outcomes.
                </p>
            </div>

            <!-- RIGHT / STATS: Statistics Cards -->
            <?php if ($hasData): ?>
            <div class="itr-stats-grid">
                <div class="itr-stat-card itr-stat-blue">
                    <div class="itr-stat-icon"><i class="fa fa-folder-open"></i></div>
                    <div class="itr-stat-info">
                        <span class="itr-stat-number"><?= count($internships) ?></span>
                        <span class="itr-stat-label">Programs Logged</span>
                    </div>
                </div>
                <div class="itr-stat-card itr-stat-red">
                    <div class="itr-stat-icon"><i class="fa fa-users"></i></div>
                    <div class="itr-stat-info">
                        <span class="itr-stat-number"><?= $totalStudents ?></span>
                        <span class="itr-stat-label">Students Trained</span>
                    </div>
                </div>
                <div class="itr-stat-card itr-stat-blue">
                    <div class="itr-stat-icon"><i class="fa fa-clock-o"></i></div>
                    <div class="itr-stat-info">
                        <span class="itr-stat-number"><?= $totalDays ?></span>
                        <span class="itr-stat-label">Training Days</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── MAIN CONTENT ── -->
<div id="page-content">
    <div class="itr-section">
        <div class="container">

            <!-- Section header -->
            <div class="itr-section-header">
                <div class="itr-sh-left">
                    <div>
                        <h2 class="itr-sh-title">All Programs</h2>
                        <p class="itr-sh-desc">Browse all logged internship and training cohorts below.</p>
                    </div>
                </div>
                <?php if ($hasData): ?>
                <span class="itr-sh-badge"><i class="fa fa-list-alt"></i> <?= count($internships) ?> record<?= count($internships) !== 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>

            <?php if ($hasData): ?>
            <div class="int-grid">
                <?php foreach ($internships as $idx => $row):
                    $studentsList = !empty($row['students_names']) ? array_map('trim', explode(',', $row['students_names'])) : [];
                    $days     = (int)($row['no_days_trained'] ?? 0);
                    $students = (int)($row['no_students_trained'] ?? 0);
                ?>
                <div class="int-card">
                    <div class="int-card-accent-bar"></div>
                    <div class="int-card-body">

                        <div class="int-card-header">
                            <div class="int-card-badge-num">#<?= sprintf('%02d', $idx + 1) ?></div>
                            <div class="int-card-main-title">
                                <h3 class="int-card-title"><?= htmlspecialchars($row['title'] ?? 'Untitled Program') ?></h3>
                                <?php if (!empty($row['project_investigator'])): ?>
                                <div class="int-card-mentor">
                                    <i class="fa fa-user-circle"></i>
                                    <span><strong>Mentor / PI:</strong> <?= htmlspecialchars($row['project_investigator']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($row['content'])): ?>
                        <div class="int-card-desc">
                            <?= nl2br(htmlspecialchars($row['content'])) ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($days || $students): ?>
                        <div class="int-card-badges">
                            <?php if ($days): ?>
                            <span class="int-badge int-badge-days">
                                <i class="fa fa-clock-o"></i> <?= $days ?> Days
                            </span>
                            <?php endif; ?>
                            <?php if ($students): ?>
                            <span class="int-badge int-badge-students">
                                <i class="fa fa-graduation-cap"></i> <?= $students ?> Students
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($studentsList)): ?>
                        <div class="int-roster">
                            <div class="int-roster-header">
                                <i class="fa fa-users"></i> Enrolled Students
                            </div>
                            <div class="int-roster-pills">
                                <?php foreach ($studentsList as $student): ?>
                                <span class="int-student-pill"><?= htmlspecialchars($student) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php else: ?>
            <div class="int-empty">
                <i class="fa fa-inbox"></i>
                <p>No training programs on record yet.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
/* ============================================================
   ANRF-PAIR INTERNSHIPS & TRAINING RECORDS THEME
   ============================================================ */
:root {
    --anrf-blue:       #0B4F9C;
    --anrf-blue-dark:  #1B3A6B;
    --anrf-blue-light: #EFF6FF;
    --anrf-red:        #BC2121;
    --anrf-red-dark:   #991B1B;
    --anrf-red-light:  #FEF2F2;
    --anrf-text:       #1E293B;
    --anrf-slate:      #475569;
    --anrf-slate-light:#64748B;
    --anrf-border:     #E2E8F0;
    --anrf-bg:         #F8FAFC;
    --anrf-white:      #FFFFFF;
}

/* Strict reset of decorative lines */
.itr-hero-title::before,
.itr-hero-title::after,
.itr-sh-title::before,
.itr-sh-title::after,
.itr-hero::before,
.itr-hero::after,
.itr-section-header::after {
    display: none !important;
    content: none !important;
    border: none !important;
    background: none !important;
    height: 0 !important;
    width: 0 !important;
}

/* ── HERO SECTION ── */
.itr-hero {
    background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
    border-bottom: none !important;
    padding: 35px 0 30px;
    margin-bottom: 0;
}

.itr-hero-inner {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.itr-hero-content {
    max-width: 800px;
    text-align: left;
}

.itr-hero-eyebrow {
    display: block;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--anrf-red);
    margin-bottom: 6px;
}

.itr-hero-title {
    font-size: 32px;
    font-weight: 800;
    color: var(--anrf-blue-dark);
    margin: 0 0 10px;
    line-height: 1.25;
    border: none !important;
    box-shadow: none !important;
}

.itr-hero-title::after,
.itr-hero-title::before,
.itr-sh-title::after,
.itr-sh-title::before {
    display: none !important;
    content: none !important;
    background: none !important;
    height: 0 !important;
    width: 0 !important;
    border: none !important;
}

.itr-hero-subtitle {

    font-size: 15.5px;
    color: var(--anrf-slate);
    line-height: 1.6;
    margin: 0;
    border: none !important;
}

/* ── STATS CARDS ── */
.itr-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.itr-stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--anrf-white);
    border: 1px solid var(--anrf-border);
    border-radius: 12px;
    padding: 18px 22px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.itr-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(11, 79, 156, 0.08);
}

.itr-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.itr-stat-blue .itr-stat-icon {
    background: var(--anrf-blue-light);
    color: var(--anrf-blue);
}

.itr-stat-red .itr-stat-icon {
    background: var(--anrf-red-light);
    color: var(--anrf-red);
}

.itr-stat-number {
    display: block;
    font-size: 26px;
    font-weight: 800;
    line-height: 1.1;
    color: var(--anrf-text);
}

.itr-stat-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--anrf-slate-light);
    margin-top: 4px;
}

/* ── SECTION CONTENT ── */
.itr-section {
    padding: 35px 0 60px;
    background: #ffffff;
}

.itr-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    gap: 16px;
    border-bottom: none !important;
}

.itr-sh-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.itr-sh-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--anrf-blue-dark);
    margin: 0 0 2px;
    border: none !important;
}

.itr-sh-desc {
    font-size: 13.5px;
    color: var(--anrf-slate);
    margin: 0;
    border: none !important;
}

.itr-sh-badge {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--anrf-blue);
    background: var(--anrf-blue-light);
    border: 1px solid #BFDBFE;
    padding: 6px 14px;
    border-radius: 20px;
    white-space: nowrap;
}

/* ── CARDS GRID ── */
.int-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.int-card {
    display: flex;
    background: var(--anrf-white);
    border: 1px solid var(--anrf-border);
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.int-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(11, 79, 156, 0.1);
    border-color: #CBD5E1;
}

.int-card-accent-bar {
    width: 5px;
    flex-shrink: 0;
    background: linear-gradient(180deg, var(--anrf-blue) 0%, var(--anrf-red) 100%);
}

.int-card-body {
    flex: 1;
    padding: 24px 26px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.int-card-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.int-card-badge-num {
    background: var(--anrf-blue-light);
    color: var(--anrf-blue);
    font-size: 12px;
    font-weight: 800;
    padding: 4px 9px;
    border-radius: 6px;
    border: 1px solid #DBEAFE;
    flex-shrink: 0;
    margin-top: 2px;
}

.int-card-main-title {
    flex: 1;
}

.int-card-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--anrf-blue-dark);
    margin: 0 0 6px;
    line-height: 1.35;
    border: none !important;
}

.int-card-mentor {
    font-size: 13.5px;
    color: var(--anrf-slate);
    display: flex;
    align-items: center;
    gap: 6px;
}

.int-card-mentor i {
    color: var(--anrf-red);
}

.int-card-desc {
    font-size: 14px;
    color: #334155;
    line-height: 1.65;
    margin: 0;
}

/* ── BADGES ── */
.int-card-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.int-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 20px;
    line-height: 1.3;
}

.int-badge-days {
    background: var(--anrf-blue-light);
    color: var(--anrf-blue);
    border: 1px solid #BFDBFE;
}

.int-badge-students {
    background: var(--anrf-red-light);
    color: var(--anrf-red);
    border: 1px solid #FECACA;
}

/* ── ROSTER ── */
.int-roster {
    border-top: 1px solid var(--anrf-border);
    padding-top: 14px;
    margin-top: 2px;
}

.int-roster-header {
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--anrf-blue-dark);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.int-roster-header i {
    color: var(--anrf-red);
}

.int-roster-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.int-student-pill {
    font-size: 12px;
    font-weight: 500;
    color: var(--anrf-slate);
    background: #F1F5F9;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 3px 11px;
}

.int-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--anrf-slate-light);
    background: var(--anrf-bg);
    border: 1px dashed var(--anrf-border);
    border-radius: 12px;
}

.int-empty i {
    font-size: 40px;
    color: var(--anrf-slate-light);
    margin-bottom: 12px;
    display: block;
}

.int-empty p {
    font-size: 15px;
    margin: 0;
}

/* ── RESPONSIVE ── */
@media (max-width: 991px) {
    .itr-stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .int-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767px) {
    .itr-stats-grid {
        grid-template-columns: 1fr;
    }
    .itr-hero-title {
        font-size: 26px;
    }
    .itr-section-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php include 'footer.php';?>