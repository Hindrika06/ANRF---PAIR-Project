<?php
$bodyClass = 'page-homepage-courses'; 
require_once 'config.php'; 

$error = '';
$institutes = [
    'uoh' => [
        'name' => 'University of Hyderabad',
        'short' => 'UoH',
        'logo' => '3.png',
        'role' => 'Hub Institution',
        'role_type' => 'hub'
    ],
    'cuk' => [
        'name' => 'Central University of Karnataka',
        'short' => 'CUK',
        'logo' => 'logos/cuk1.jpg',
        'role' => 'Spoke Partner',
        'role_type' => 'spoke'
    ],
    'kannur' => [
        'name' => 'Kannur University',
        'short' => 'Kannur',
        'logo' => 'logos/ku1.jpg',
        'role' => 'Spoke Partner',
        'role_type' => 'spoke'
    ],
    'mgu' => [
        'name' => 'Mahatma Gandhi University',
        'short' => 'MGU',
        'logo' => 'logos/mg1.jpg',
        'role' => 'Spoke Partner',
        'role_type' => 'spoke'
    ],
    'ou' => [
        'name' => 'Osmania University',
        'short' => 'OU',
        'logo' => 'logos/ou1.jpg',
        'role' => 'Spoke Partner',
        'role_type' => 'spoke'
    ],
    'svu' => [
        'name' => 'Sri Venkateswara University',
        'short' => 'SVU',
        'logo' => 'logos/gan1.jpg',
        'role' => 'Spoke Partner',
        'role_type' => 'spoke'
    ],
    'yvu' => [
        'name' => 'Yogi Vemana University',
        'short' => 'YVU',
        'logo' => 'logos/yu.jpg',
        'role' => 'Spoke Partner',
        'role_type' => 'spoke'
    ],
];

$instituteInternships = [];
$counts = [];
$totalInternshipsCount = 0;
$totalStudents = 0;
$totalDays = 0;

$reqFilter = $_GET['name'] ?? $_GET['institute'] ?? $_GET['prefix'] ?? 'all';
$initialFilter = 'all';
if ($reqFilter !== 'all') {
    $cleanReq = strtolower(trim($reqFilter));
    foreach ($institutes as $pfx => $info) {
        if ($cleanReq === $pfx || strtolower($info['name']) === $cleanReq || strtolower($info['short']) === $cleanReq) {
            $initialFilter = $pfx;
            break;
        }
    }
}

try {
    foreach ($institutes as $pfx => $info) {
        $tbl = "{$pfx}_internships";
        $list = [];
        try {
            $check = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($check > 0) {
                $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
                $whereConditions = [];
                if (in_array('approval_status', $cols, true)) {
                    $whereConditions[] = "(approval_status = 'Approved' OR approval_status IS NULL)";
                }
                if (in_array('publish_status', $cols, true)) {
                    $whereConditions[] = "(publish_status = 1 OR publish_status IS NULL)";
                }
                $whereClause = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";
                $stmt = $pdo->query("SELECT *, '$pfx' AS institute_prefix FROM `$tbl` $whereClause ORDER BY id DESC");
                $list = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
        } catch (Exception $e) {
            $list = [];
        }
        $instituteInternships[$pfx] = $list;
        $counts[$pfx] = count($list);
        $totalInternshipsCount += count($list);

        foreach ($list as $r) {
            $totalStudents += (int)($r['no_students_trained'] ?? 0);
            $totalDays     += (int)($r['no_days_trained'] ?? 0);
        }
    }
} catch (PDOException $e) {
    $error = 'Could not load internship records: ' . $e->getMessage();
}
?>

<?php include 'header.php';?>

<!-- Breadcrumb -->
<div class="container" style="padding-top: 15px;">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li><a href="#">Research &amp; Infrastructure</a></li>
        <li class="active">Internships &amp; Training Records</li>
    </ol>
</div>
<!-- end Breadcrumb -->

<!-- ── HERO SECTION ── -->
<section class="itr-hero">
    <div class="container">
        <div class="itr-hero-inner">
            <div class="itr-hero-content">
                <span class="itr-hero-eyebrow">ANRF&ndash;PAIR INITIATIVE</span>
                <h1 class="itr-hero-title">Internships &amp; Training Records</h1>
                <p class="itr-hero-subtitle">
                    A curated registry of student training cohorts and hands-on fellowships conducted independently across all 7 participating consortium institutions.
                </p>
            </div>

            <?php if ($totalInternshipsCount > 0): ?>
            <div class="itr-stats-grid">
                <div class="itr-stat-card itr-stat-blue">
                    <div class="itr-stat-icon"><i class="fa fa-folder-open"></i></div>
                    <div class="itr-stat-info">
                        <span class="itr-stat-number"><?= $totalInternshipsCount ?></span>
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
<div id="page-content" style="padding-top: 10px;">
    <div class="itr-section">
        <div class="container">

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- UNIVERSITY FILTER PILLS BAR -->
            <div class="univ-filter-container">
                <div class="univ-filter-label">
                    <i class="fa fa-university"></i>
                    <span>Filter By University:</span>
                </div>
                <div class="univ-filter-pills" id="univ-filter-pills">
                    <button class="univ-pill <?= $initialFilter === 'all' ? 'active' : '' ?>" data-univ="all">
                        <span class="univ-pill-name">All Universities</span>
                        <span class="univ-pill-count"><?= $totalInternshipsCount ?></span>
                    </button>
                    <?php foreach ($institutes as $pfx => $info): ?>
                        <button class="univ-pill <?= $initialFilter === $pfx ? 'active' : '' ?>" data-univ="<?= $pfx ?>">
                            <img src="<?= htmlspecialchars($info['logo']) ?>" alt="<?= htmlspecialchars($info['short']) ?>" class="univ-pill-logo">
                            <span class="univ-pill-name"><?= htmlspecialchars($info['short']) ?></span>
                            <span class="univ-pill-count"><?= $counts[$pfx] ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- UNIVERSITY-SEPARATED INTERNSHIP GROUPS -->
            <?php foreach ($institutes as $pfx => $info):
                $iList = $instituteInternships[$pfx] ?? [];
                $hasItems = !empty($iList);
            ?>
                <div class="university-internship-group <?= ($initialFilter !== 'all' && $initialFilter !== $pfx) ? 'univ-hidden' : '' ?>"
                     data-univ="<?= $pfx ?>"
                     id="group-<?= $pfx ?>">

                    <!-- University Section Header -->
                    <div class="univ-group-header">
                        <div class="univ-group-header-left">
                            <div class="univ-header-logo-wrap">
                                <img src="<?= htmlspecialchars($info['logo']) ?>" alt="<?= htmlspecialchars($info['name']) ?>" class="univ-header-logo">
                            </div>
                            <div class="univ-header-text">
                                <div class="univ-header-title-row">
                                    <h2 class="univ-header-name"><?= htmlspecialchars($info['name']) ?></h2>
                                    <span class="univ-role-badge <?= $info['role_type'] === 'hub' ? 'role-hub' : 'role-spoke' ?>">
                                        <?= htmlspecialchars($info['role']) ?>
                                    </span>
                                </div>
                                <p class="univ-header-sub">
                                    <i class="fa fa-graduation-cap"></i>
                                    <span><?= count($iList) ?></span> Training Program<?= count($iList) === 1 ? '' : 's' ?> hosted by this institution
                                </p>
                            </div>
                        </div>
                        <div class="univ-group-header-right">
                            <a href="institute.php?name=<?= urlencode($info['name']) ?>&tab=internships" class="univ-portal-link" title="Visit University Dashboard">
                                <span>Institute Portal</span>
                                <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <?php if ($hasItems): ?>
                        <div class="int-grid">
                            <?php foreach ($iList as $idx => $row):
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
                        <div class="int-empty-box">
                            <i class="fa fa-inbox" style="font-size: 24px; margin-bottom: 6px; display: block;"></i>
                            No training programs on record yet for <?= htmlspecialchars($info['name']) ?>.
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

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

/* ── Hero Section ── */
.itr-hero {
    background: linear-gradient(135deg, #1B3A6B 0%, #0F2342 100%);
    color: #FFFFFF;
    padding: 44px 0 40px;
    margin-top: 10px;
    position: relative;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(11, 79, 156, 0.12);
}

.itr-hero-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 32px;
    flex-wrap: wrap;
}

.itr-hero-content {
    max-width: 650px;
}

.itr-hero-eyebrow {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    color: #F87171;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.itr-hero-title {
    font-size: 32px;
    font-weight: 800;
    color: #FFFFFF;
    margin: 0 0 10px 0;
    line-height: 1.25;
}

.itr-hero-subtitle {
    font-size: 15px;
    color: #CBD5E1;
    margin: 0;
    line-height: 1.55;
}

/* Stats */
.itr-stats-grid {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.itr-stat-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 150px;
}

.itr-stat-icon {
    font-size: 24px;
    color: #60A5FA;
}

.itr-stat-red .itr-stat-icon {
    color: #F87171;
}

.itr-stat-number {
    display: block;
    font-size: 24px;
    font-weight: 800;
    color: #FFFFFF;
    line-height: 1;
}

.itr-stat-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #94A3B8;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-top: 4px;
}

/* ── University Filter Pills Bar ── */
.univ-filter-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    position: sticky;
    top: 80px;
    z-index: 40;
}

.univ-filter-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.univ-filter-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.univ-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 30px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    user-select: none;
}

.univ-pill:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
    transform: translateY(-1px);
}

.univ-pill.active {
    background: #1B3A6B;
    border-color: #1B3A6B;
    color: #ffffff;
    box-shadow: 0 3px 10px rgba(27, 58, 107, 0.25);
}

.univ-pill-logo {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: contain;
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    flex-shrink: 0;
}

.univ-pill-count {
    background: rgba(0, 0, 0, 0.06);
    color: inherit;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 12px;
    line-height: 1.2;
}

.univ-pill.active .univ-pill-count {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* ── University Group Section ── */
.university-internship-group {
    margin-bottom: 40px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    transition: opacity 0.3s ease;
}

.university-internship-group.univ-hidden {
    display: none !important;
}

.univ-group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    background: #f8fafc;
    border-bottom: 1.5px solid #e2e8f0;
    gap: 16px;
    flex-wrap: wrap;
}

.univ-group-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.univ-header-logo-wrap {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    flex-shrink: 0;
}

.univ-header-logo {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.univ-header-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.univ-header-name {
    margin: 0;
    font-family: 'Inter', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
}

.univ-role-badge {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 3px 8px;
    border-radius: 6px;
}

.role-hub {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.role-spoke {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.univ-header-sub {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}

.univ-portal-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #1B3A6B;
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    transition: all 0.2s ease;
}

.univ-portal-link:hover {
    background: #1B3A6B;
    color: #ffffff;
    border-color: #1B3A6B;
    text-decoration: none;
}

/* ── Program Cards Grid ── */
.int-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 20px;
    padding: 24px;
}

.int-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.int-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

.int-card-accent-bar {
    height: 4px;
    background: linear-gradient(90deg, #1B3A6B, #BC2121);
}

.int-card-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.int-card-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}

.int-card-badge-num {
    background: #EFF6FF;
    color: #1B3A6B;
    font-size: 12px;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 6px;
    flex-shrink: 0;
}

.int-card-title {
    font-size: 16px;
    font-weight: 700;
    color: #1E293B;
    margin: 0 0 6px 0;
    line-height: 1.35;
}

.int-card-mentor {
    font-size: 13px;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 6px;
}

.int-card-desc {
    font-size: 13.5px;
    color: #475569;
    line-height: 1.55;
    margin-bottom: 14px;
    flex-grow: 1;
}

.int-card-badges {
    display: flex;
    gap: 8px;
    margin-bottom: 14px;
}

.int-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
}

.int-badge-days {
    background: #EFF6FF;
    color: #1B3A6B;
}

.int-badge-students {
    background: #FEF2F2;
    color: #BC2121;
}

.int-roster {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    padding: 10px 12px;
}

.int-roster-header {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748B;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.int-roster-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.int-student-pill {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    font-size: 11.5px;
    color: #334155;
    padding: 2px 8px;
    border-radius: 4px;
}

.int-empty-box {
    padding: 32px 20px;
    text-align: center;
    color: #64748b;
    font-size: 14px;
}

@media (max-width: 768px) {
    .int-grid {
        grid-template-columns: 1fr;
        padding: 16px;
    }
    .univ-group-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pills = document.querySelectorAll('.univ-pill');
    const groups = document.querySelectorAll('.university-internship-group');

    pills.forEach(pill => {
        pill.addEventListener('click', function() {
            pills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            const selectedUniv = this.getAttribute('data-univ');

            groups.forEach(group => {
                const groupUniv = group.getAttribute('data-univ');
                if (selectedUniv === 'all' || groupUniv === selectedUniv) {
                    group.classList.remove('univ-hidden');
                } else {
                    group.classList.add('univ-hidden');
                }
            });
        });
    });
});
</script>

<!-- Footer -->
<?php include 'footer.php';?>