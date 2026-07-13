<?php 
require_once 'config.php'; 

$internships = [];
$hasData = false;
$totalStudents = 0;
$totalDays = 0;

try {
    $stmt = $pdo->query("SELECT * FROM uoh_internships ORDER BY created_at DESC, id DESC");
    $internships = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($internships)) {
        $hasData = true;
        foreach ($internships as $r) {
            $totalStudents += (int)($r['no_students_trained'] ?? 0);
            $totalDays     += (int)($r['no_days_trained'] ?? 0);
        }
    }
} catch (PDOException $e) {
    echo "<div class='container' style='margin-top:20px;'><div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div></div>";
}
?>

<body class="page-homepage-courses">
<div class="wrapper">
<?php include 'header.php';?>

<!-- ── HERO: Split layout ── -->
<section class="itr-hero">
    <div class="container">
        <div class="itr-hero-grid">

            <!-- LEFT: Text block -->
            <div class="itr-hero-left">
                <span class="itr-tag">
                    <i class="fa-solid fa-graduation-cap"></i>
                    ANRF-PAIR Initiative
                </span>
                <h1 class="itr-hero-title">Internships &amp; Training Records</h1>
                <p class="itr-hero-desc">
                    A curated log of internship and training cohorts conducted under the initiative,
                    tracking mentors, students, and real-world outcomes.
                </p>

            </div>

            <!-- RIGHT: Stat cards stack -->
            <?php if ($hasData): ?>
            <div class="itr-hero-right">
                <div class="itr-stat-card itr-sc-green">
                    <div class="itr-sc-icon"><i class="fa-solid fa-folder-open"></i></div>
                    <div class="itr-sc-body">
                        <span class="itr-sc-num"><?= count($internships) ?></span>
                        <span class="itr-sc-label">Programs Logged</span>
                    </div>
                </div>
                <div class="itr-stat-card itr-sc-amber">
                    <div class="itr-sc-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="itr-sc-body">
                        <span class="itr-sc-num"><?= $totalStudents ?></span>
                        <span class="itr-sc-label">Students Trained</span>
                    </div>
                </div>
                <div class="itr-stat-card itr-sc-violet">
                    <div class="itr-sc-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="itr-sc-body">
                        <span class="itr-sc-num"><?= $totalDays ?></span>
                        <span class="itr-sc-label">Training Days</span>
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
                    <span class="itr-sh-line"></span>
                    <div>
                        <h2 class="itr-sh-title">All Programs</h2>
                        <p class="itr-sh-sub">Browse all logged internship and training cohorts below.</p>
                    </div>
                </div>
                <?php if ($hasData): ?>
                <span class="itr-sh-count"><?= count($internships) ?> record<?= count($internships) !== 1 ? 's' : '' ?></span>
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
                    <div class="int-card-accent"></div>
                    <div class="int-card-inner">

                        <div class="int-card-top">
                            <div class="int-card-index"><?= $idx + 1 ?></div>
                            <div class="int-card-header">
                                <h2 class="int-card-title"><?= htmlspecialchars($row['title'] ?? 'Untitled Program') ?></h2>
                                <?php if (!empty($row['project_investigator'])): ?>
                                <p class="int-card-mentor">
                                    <i class="fa-solid fa-user-tie"></i>
                                    <?= htmlspecialchars($row['project_investigator']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($row['content'])): ?>
                        <p class="int-card-desc"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                        <?php endif; ?>

                        <?php if ($days || $students): ?>
                        <div class="int-card-metrics">
                            <?php if ($days): ?>
                            <div class="int-metric">
                                <i class="fa-regular fa-clock"></i>
                                <span class="int-metric-val"><?= $days ?></span>
                                <span class="int-metric-lbl">Training Days</span>
                            </div>
                            <?php endif; ?>
                            <?php if ($students): ?>
                            <div class="int-metric">
                                <i class="fa-solid fa-user-graduate"></i>
                                <span class="int-metric-val"><?= $students ?></span>
                                <span class="int-metric-lbl">Students Trained</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($studentsList)): ?>
                        <div class="int-roster">
                            <span class="int-roster-label"><i class="fa-solid fa-list-ul"></i> Enrolled Students</span>
                            <div class="int-roster-tags">
                                <?php foreach ($studentsList as $student): ?>
                                <span class="int-student-tag"><?= htmlspecialchars($student) ?></span>
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
                <i class="fa-solid fa-inbox"></i>
                <p>No training programs on record yet.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'footer.php';?>
</div>

<style>
/* ═══════════════════════════════════════════════════════════
   INTERNSHIPS — SPLIT HERO + EMERALD / AMBER REDESIGN
═══════════════════════════════════════════════════════════ */
:root {
    /* New palette */
    --itr-green:      #065f46;
    --itr-green-mid:  #059669;
    --itr-green-lt:   #d1fae5;
    --itr-amber:      #b45309;
    --itr-amber-lt:   #fef3c7;
    --itr-violet:     #5b21b6;
    --itr-violet-lt:  #ede9fe;

    --itr-text:   #1e293b;
    --itr-sub:    #64748b;
    --itr-border: #e2e8f0;
    --itr-bg:     #f8fafc;
    --itr-white:  #ffffff;
}

/* ══ HERO ══ */
.itr-hero {
    background: #f0fdf4;           /* very soft green tint */
    border-bottom: 3px solid var(--itr-green-mid);
    padding: 54px 0 50px;
    position: relative;
    overflow: hidden;
}
/* Subtle decorative circle */
.itr-hero::before {
    content: '';
    position: absolute;
    right: -120px; top: -80px;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(5,150,105,.08) 0%, transparent 70%);
    pointer-events: none;
}

.itr-hero-grid {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: 48px;
}

/* Left */
.itr-hero-left { max-width: 520px; }

.itr-tag {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--itr-green-lt);
    color: var(--itr-green);
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: .7px;
    text-transform: uppercase;
    padding: 5px 13px;
    border-radius: 20px;
    margin-bottom: 18px;
}

.itr-hero-title {
    font-size: 30px;
    font-weight: 900;
    color: var(--itr-text);
    line-height: 1.2;
    margin: 0 0 16px;
    letter-spacing: -.3px;
    white-space: nowrap;
}

.itr-hero-desc {
    font-size: 15px;
    color: var(--itr-sub);
    line-height: 1.75;
    margin: 0 0 24px;
}

.itr-hero-crumb {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    color: var(--itr-sub);
}
.itr-hero-crumb a {
    color: var(--itr-green);
    text-decoration: none;
    font-weight: 600;
}
.itr-hero-crumb a:hover { text-decoration: underline; }
.itr-crumb-sep { opacity: .4; font-size: 16px; }

/* Right — 3 stacked stat cards */
.itr-hero-right {
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-width: 230px;
}

.itr-stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 22px;
    border-radius: 12px;
    border: 1px solid transparent;
    transition: transform .18s ease;
}
.itr-stat-card:hover { transform: translateX(4px); }

.itr-sc-green  { background: var(--itr-green-lt);  border-color: #a7f3d0; }
.itr-sc-amber  { background: var(--itr-amber-lt);  border-color: #fcd34d; }
.itr-sc-violet { background: var(--itr-violet-lt); border-color: #c4b5fd; }

.itr-sc-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.itr-sc-green  .itr-sc-icon { background: #a7f3d0; color: var(--itr-green); }
.itr-sc-amber  .itr-sc-icon { background: #fcd34d; color: var(--itr-amber); }
.itr-sc-violet .itr-sc-icon { background: #c4b5fd; color: var(--itr-violet); }

.itr-sc-num {
    display: block;
    font-size: 26px;
    font-weight: 900;
    line-height: 1;
    color: var(--itr-text);
}
.itr-sc-label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .55px;
    margin-top: 3px;
}
.itr-sc-green  .itr-sc-label { color: var(--itr-green); }
.itr-sc-amber  .itr-sc-label { color: var(--itr-amber); }
.itr-sc-violet .itr-sc-label { color: var(--itr-violet); }

/* ══ SECTION ══ */
.itr-section { background: var(--itr-bg); padding: 52px 0 70px; }

/* Section Header */
.itr-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30px;
    gap: 20px;
}
.itr-sh-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.itr-sh-line {
    display: inline-block;
    width: 5px;
    height: 44px;
    border-radius: 3px;
    background: linear-gradient(180deg, var(--itr-green-mid), #34d399);
    flex-shrink: 0;
}
.itr-sh-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--itr-text);
    margin: 0 0 3px;
}
.itr-sh-sub {
    font-size: 13.5px;
    color: var(--itr-sub);
    margin: 0;
}
.itr-sh-count {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--itr-green);
    background: var(--itr-green-lt);
    border: 1px solid #a7f3d0;
    padding: 6px 14px;
    border-radius: 20px;
    white-space: nowrap;
}

/* ══ GRID ══ */
.int-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

/* ══ CARD ══ (kept, just accent color updated) */
.int-card {
    display: flex;
    background: var(--itr-white);
    border-radius: 12px;
    border: 1px solid var(--itr-border);
    box-shadow: 0 2px 12px rgba(0,0,0,.04);
    overflow: hidden;
    transition: transform .2s ease, box-shadow .2s ease;
}
.int-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(5, 150, 105, .12);
}
.int-card-accent {
    width: 5px;
    flex-shrink: 0;
    background: linear-gradient(180deg, var(--itr-green-mid), #34d399);
}
.int-card-inner {
    flex: 1;
    padding: 26px 28px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.int-card-top { display: flex; align-items: flex-start; gap: 14px; }
.int-card-index {
    width: 30px; height: 30px;
    flex-shrink: 0;
    background: var(--itr-green-lt);
    color: var(--itr-green);
    font-size: 13px; font-weight: 800;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    margin-top: 2px;
}
.int-card-title {
    font-size: 17px; font-weight: 700;
    color: var(--itr-text);
    margin: 0 0 6px; line-height: 1.4;
}
.int-card-mentor {
    font-size: 13px; color: var(--itr-sub);
    margin: 0; display: flex; align-items: center; gap: 6px;
}
.int-card-desc {
    font-size: 14px; color: #475569;
    line-height: 1.7; margin: 0;
}
.int-card-metrics { display: flex; gap: 10px; flex-wrap: wrap; }
.int-metric {
    display: flex; align-items: center; gap: 8px;
    background: var(--itr-bg);
    border: 1px solid var(--itr-border);
    border-radius: 8px; padding: 8px 14px;
}
.int-metric i { font-size: 14px; color: var(--itr-green-mid); }
.int-metric-val { font-size: 20px; font-weight: 800; color: var(--itr-text); line-height: 1; }
.int-metric-lbl {
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px; color: var(--itr-sub);
}
.int-roster { border-top: 1px solid var(--itr-border); padding-top: 14px; }
.int-roster-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px;
    color: var(--itr-green); margin-bottom: 10px;
}
.int-roster-tags { display: flex; flex-wrap: wrap; gap: 7px; }
.int-student-tag {
    font-size: 12.5px; font-weight: 500;
    color: var(--itr-green);
    background: var(--itr-green-lt);
    border-radius: 20px; padding: 4px 12px;
}
.int-empty { text-align: center; padding: 80px 20px; color: var(--itr-sub); }
.int-empty i { font-size: 48px; margin-bottom: 16px; display: block; opacity: .4; }
.int-empty p { font-size: 16px; margin: 0; }

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .itr-hero-grid { grid-template-columns: 1fr; }
    .itr-hero-right { flex-direction: row; flex-wrap: wrap; min-width: 0; }
    .itr-stat-card { flex: 1; min-width: 140px; }
    .itr-hero-title { font-size: 28px; }
}
@media (max-width: 700px) {
    .int-grid { grid-template-columns: 1fr; }
    .itr-hero-right { flex-direction: column; }
    .itr-section-header { flex-direction: column; align-items: flex-start; }
}
</style>