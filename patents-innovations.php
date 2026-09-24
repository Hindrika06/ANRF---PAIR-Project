<?php
$bodyClass = 'page-homepage-courses';
// ── Database & logic ─────────────────────────────────────────────────────────
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

$institutePatents = [];
$counts = [];
$totalPatentsCount = 0;

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
        $tbl = "{$pfx}_patent";
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
        $institutePatents[$pfx] = $list;
        $counts[$pfx] = count($list);
        $totalPatentsCount += count($list);
    }
} catch (PDOException $e) {
    $error = 'Could not load patent records: ' . $e->getMessage();
}

// Helper to map status -> a CSS state class
if (!function_exists('patentStatusClass')) {
    function patentStatusClass($status) {
        switch ($status) {
            case 'Granted':   return 'is-granted';
            case 'Rejected':  return 'is-rejected';
            case 'Pending':   return 'is-pending';
            case 'Published': return 'is-published';
            default:          return 'is-filed';
        }
    }
}
?>

<!-- Header -->
<?php include 'header.php';?>
<!-- end Header -->

<!-- Breadcrumb -->
<div class="container" style="padding-top: 15px;">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li><a href="#">Resources &amp; Gallery</a></li>
        <li class="active">Patents &amp; Innovations</li>
    </ol>
</div>

<!-- Patent Registry Section -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .pr-section {
        --navy: #1B3A6B;
        --navy-deep: #142C52;
        --maroon: #B33A3A;
        --maroon-deep: #962F2F;
        --text-dark: #1A1A1A;
        --text-grey: #6B7280;
        --border-grey: #E5E7EB;
        --bg-soft: #F8F9FB;
        --green: #2E7D52;
        --green-bg: #E7F4ED;
        --rust: #B33A3A;
        --rust-bg: #FBEAEA;
        --amber: #B07A1E;
        --amber-bg: #FBF1DD;
        --blue: #2C5C8C;
        --blue-bg: #E8EFF6;

        background: #fff;
        padding: 40px 0 72px;
        font-family: 'Inter', -apple-system, sans-serif;
        color: var(--text-dark);
    }

    .pr-section__inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .pr-section__head {
        text-align: center;
        max-width: 760px;
        margin: 0 auto 28px;
    }

    .pr-section__eyebrow {
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--maroon);
        margin-bottom: 8px;
    }

    .pr-section__title {
        font-family: 'Poppins', sans-serif;
        font-size: 32px;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 10px;
        letter-spacing: -0.02em;
    }

    .pr-section__subtitle {
        font-size: 15px;
        color: var(--text-grey);
        line-height: 1.6;
        margin: 0;
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
    .university-patent-group {
        margin-bottom: 40px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        transition: opacity 0.3s ease;
    }

    .university-patent-group.univ-hidden {
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
        font-family: 'Poppins', sans-serif;
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

    /* ── Desktop table ───────────────────────────────────────────────────── */
    .pr-table-wrap {
        display: block;
        width: 100%;
        overflow-x: auto;
    }

    table.pr-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        font-size: 14px;
    }

    table.pr-table thead th {
        text-align: left;
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: #fff;
        background: var(--navy);
        padding: 14px 18px;
        white-space: nowrap;
    }

    table.pr-table tbody td {
        padding: 16px 18px;
        border-bottom: 1px solid var(--border-grey);
        vertical-align: top;
        color: var(--text-dark);
    }

    table.pr-table tbody tr:last-child td {
        border-bottom: none;
    }

    table.pr-table tbody tr:hover {
        background: var(--bg-soft);
    }

    .pr-badge-no {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--maroon);
        color: #fff;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 12px;
        flex-shrink: 0;
    }

    .pr-id {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 13.5px;
        color: var(--navy);
    }

    .pr-title {
        font-weight: 600;
        color: var(--text-dark);
        line-height: 1.4;
        margin: 4px 0 4px;
    }

    .pr-sub {
        display: block;
        font-size: 12.5px;
        color: var(--text-grey);
        line-height: 1.5;
    }

    .pr-sub--tag {
        display: inline-block;
        margin-top: 4px;
        color: var(--maroon);
        background: var(--rust-bg);
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.02em;
        padding: 2px 8px;
        border-radius: 100px;
    }

    .pr-status {
        display: inline-block;
        font-family: 'Poppins', sans-serif;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 100px;
        white-space: nowrap;
    }

    .pr-status.is-granted   { background: var(--green-bg); color: var(--green); }
    .pr-status.is-published { background: var(--blue-bg);  color: var(--blue); }
    .pr-status.is-filed     { background: var(--rust-bg);  color: var(--rust); }
    .pr-status.is-pending   { background: var(--amber-bg); color: var(--amber); }
    .pr-status.is-rejected  { background: #F3F4F6;         color: var(--text-grey); }

    /* ── Mobile cards ─────────────────────────────────────────────────────── */
    .pr-cards {
        display: none;
        padding: 16px;
    }

    .pr-card {
        background: #fff;
        border: 1px solid var(--border-grey);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
    }

    .pr-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .pr-card__id-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pr-card__title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 15px;
        color: var(--text-dark);
        line-height: 1.4;
        margin: 6px 0;
    }

    .pr-card__meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 14px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--border-grey);
    }

    .pr-card__field { min-width: 0; }

    .pr-card__label {
        display: block;
        font-size: 10.5px;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text-grey);
        margin-bottom: 2px;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
    }

    .pr-card__value {
        font-size: 13px;
        color: var(--text-dark);
        word-break: break-word;
    }

    .pr-empty-box {
        padding: 32px 20px;
        text-align: center;
        color: #64748b;
        font-size: 14px;
    }

    /* ── Responsive breakpoints ──────────────────────────────────────────── */
    @media (max-width: 860px) {
        .pr-table-wrap { display: none; }
        .pr-cards { display: block; }
        .pr-section__title { font-size: 26px; }
        .univ-group-header { flex-direction: column; align-items: flex-start; }
    }

    @media (max-width: 480px) {
        .pr-card__meta { grid-template-columns: 1fr; }
    }
</style>

<div class="pr-section">
    <div class="pr-section__inner">

        <div class="pr-section__head">
            <p class="pr-section__eyebrow">Consortium Intellectual Property</p>
            <h1 class="pr-section__title">Patent Registry &amp; Innovations</h1>
            <p class="pr-section__subtitle">Patents filed, published, and granted across all 7 participating consortium institutions under the ANRF–PAIR initiative.</p>
        </div>

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
                    <span class="univ-pill-count"><?= $totalPatentsCount ?></span>
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

        <!-- UNIVERSITY-SEPARATED PATENT REGISTRIES -->
        <?php foreach ($institutes as $pfx => $info):
            $pList = $institutePatents[$pfx] ?? [];
            $hasItems = !empty($pList);
        ?>
            <div class="university-patent-group <?= ($initialFilter !== 'all' && $initialFilter !== $pfx) ? 'univ-hidden' : '' ?>"
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
                                <i class="fa fa-lightbulb-o"></i>
                                <span><?= count($pList) ?></span> Patent Filing<?= count($pList) === 1 ? '' : 's' ?> registered by this institution
                            </p>
                        </div>
                    </div>
                    <div class="univ-group-header-right">
                        <a href="institute.php?name=<?= urlencode($info['name']) ?>&tab=patents" class="univ-portal-link" title="Visit University Dashboard">
                            <span>Institute Portal</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <?php if ($hasItems): ?>
                    <!-- Desktop table -->
                    <div class="pr-table-wrap">
                        <table class="pr-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Patent</th>
                                    <th>Inventor(s)</th>
                                    <th>Identifiers</th>
                                    <th>Country</th>
                                    <th>Filed</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sno = 1; foreach ($pList as $patent): ?>
                                    <tr>
                                        <td><span class="pr-badge-no"><?= $sno++ ?></span></td>
                                        <td>
                                            <div class="pr-id"><?= htmlspecialchars($patent['patent_id']) ?></div>
                                            <div class="pr-title"><?= htmlspecialchars($patent['patent_title'] ?: 'Untitled Patent') ?></div>
                                            <?php if (!empty($patent['technology_area'])): ?>
                                                <span class="pr-sub--tag"><?= htmlspecialchars($patent['technology_area']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($patent['inventor_name'] ?: '-') ?></strong>
                                            <?php if (!empty($patent['co_inventors'])): ?>
                                                <span class="pr-sub">Co-inventors: <?= htmlspecialchars($patent['co_inventors']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="pr-sub">App: <?= htmlspecialchars($patent['application_no'] ?: '—') ?></span>
                                            <?php if (!empty($patent['patent_no'])): ?>
                                                <span class="pr-sub">Grant No: <?= htmlspecialchars($patent['patent_no']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($patent['country'] ?: 'India') ?></td>
                                        <td><?= !empty($patent['filing_date']) ? date('d M Y', strtotime($patent['filing_date'])) : '—' ?></td>
                                        <td>
                                            <span class="pr-status <?= patentStatusClass($patent['status']) ?>"><?= htmlspecialchars($patent['status']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile record cards -->
                    <div class="pr-cards">
                        <?php $sno = 1; foreach ($pList as $patent): ?>
                            <div class="pr-card">
                                <div class="pr-card__top">
                                    <div class="pr-card__id-row">
                                        <span class="pr-badge-no"><?= $sno++ ?></span>
                                        <span class="pr-id"><?= htmlspecialchars($patent['patent_id']) ?></span>
                                    </div>
                                    <span class="pr-status <?= patentStatusClass($patent['status']) ?>"><?= htmlspecialchars($patent['status']) ?></span>
                                </div>

                                <div class="pr-card__title"><?= htmlspecialchars($patent['patent_title'] ?: 'Untitled Patent') ?></div>
                                <?php if (!empty($patent['technology_area'])): ?>
                                    <span class="pr-sub--tag"><?= htmlspecialchars($patent['technology_area']) ?></span>
                                <?php endif; ?>

                                <div class="pr-card__meta">
                                    <div class="pr-card__field">
                                        <span class="pr-card__label">Inventor</span>
                                        <span class="pr-card__value"><?= htmlspecialchars($patent['inventor_name'] ?: '—') ?></span>
                                    </div>
                                    <?php if (!empty($patent['co_inventors'])): ?>
                                    <div class="pr-card__field">
                                        <span class="pr-card__label">Co-Inventor(s)</span>
                                        <span class="pr-card__value"><?= htmlspecialchars($patent['co_inventors']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="pr-card__field">
                                        <span class="pr-card__label">Application No.</span>
                                        <span class="pr-card__value"><?= htmlspecialchars($patent['application_no'] ?: '—') ?></span>
                                    </div>
                                    <div class="pr-card__field">
                                        <span class="pr-card__label">Country</span>
                                        <span class="pr-card__value"><?= htmlspecialchars($patent['country'] ?: 'India') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="pr-empty-box">
                        <i class="fa fa-folder-open-o" style="font-size: 24px; margin-bottom: 6px; display: block;"></i>
                        No patents filed yet for <?= htmlspecialchars($info['name']) ?>.
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pills = document.querySelectorAll('.univ-pill');
    const groups = document.querySelectorAll('.university-patent-group');

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
