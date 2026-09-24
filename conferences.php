<?php
require_once 'config.php';

$institutes = [
    'uoh' => [
        'name'      => 'University of Hyderabad',
        'short'     => 'UoH',
        'role'      => 'Hub Institution',
        'role_type' => 'hub',
        'logo'      => '3.png',
    ],
    'cuk' => [
        'name'      => 'Central University of Karnataka',
        'short'     => 'CUK',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/cuk1.jpg',
    ],
    'kannur' => [
        'name'      => 'Kannur University',
        'short'     => 'Kannur',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/ku1.jpg',
    ],
    'mgu' => [
        'name'      => 'Mahatma Gandhi University',
        'short'     => 'MGU',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/mg1.jpg',
    ],
    'ou' => [
        'name'      => 'Osmania University',
        'short'     => 'OU',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/ou1.jpg',
    ],
    'svu' => [
        'name'      => 'Sri Venkateswara University',
        'short'     => 'SVU',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/gan1.jpg',
    ],
    'yvu' => [
        'name'      => 'Yogi Vemana University',
        'short'     => 'YVU',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/yu.jpg',
    ],
];

$prefixMap = [];
foreach ($institutes as $p => $meta) {
    $prefixMap[strtolower($meta['name'])]  = $p;
    $prefixMap[strtolower($meta['short'])] = $p;
    $prefixMap[$p]                         = $p;
}

$initialFilter = 'all';
$requestedInst = $_GET['name'] ?? $_GET['institute'] ?? $_GET['prefix'] ?? null;
if (!empty($requestedInst)) {
    $cleanReq = strtolower(trim($requestedInst));
    if (isset($prefixMap[$cleanReq])) {
        $initialFilter = $prefixMap[$cleanReq];
    }
}

$instituteConferences = [];
$counts = [];
$totalConferencesCount = 0;
$error = '';

try {
    foreach ($institutes as $p => $info) {
        $tbl = "{$p}_conferences";
        $instituteConferences[$p] = [];
        try {
            $check = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($check > 0) {
                $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
                $hasApproval = in_array('approval_status', $cols, true);
                $hasPublish  = in_array('publish_status', $cols, true);

                $whereConditions = [];
                if ($hasApproval) {
                    $whereConditions[] = "approval_status = 'Approved'";
                }
                if ($hasPublish) {
                    $whereConditions[] = "(publish_status = 1 OR publish_status IS NULL)";
                }

                $whereClause = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";
                $stmt = $pdo->query("SELECT *, '$p' AS institute_prefix FROM `$tbl` $whereClause");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    usort($rows, function($a, $b) {
                        $dateA = !empty($a['conf_date']) ? $a['conf_date'] : ($a['start_date'] ?? '');
                        $dateB = !empty($b['conf_date']) ? $b['conf_date'] : ($b['start_date'] ?? '');
                        $tA = !empty($dateA) ? strtotime($dateA) : 0;
                        $tB = !empty($dateB) ? strtotime($dateB) : 0;
                        return $tB <=> $tA;
                    });
                    $instituteConferences[$p] = $rows;
                }
            }
        } catch (Exception $e) {}
        $counts[$p] = count($instituteConferences[$p]);
        $totalConferencesCount += $counts[$p];
    }
} catch (PDOException $e) {
    $error = 'Could not load records: ' . $e->getMessage();
}

include 'header.php';
?>

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li><a href="conferences.php">Events</a></li>
        <li class="active">Conferences</li>
    </ol>
</div>
<!-- end Breadcrumb -->

<style>
    /* ── Section layout ── */
    .conf-section {
        padding: 24px 0 60px;
    }

    /* ── Page heading block ── */
    .conf-heading-block {
        text-align: center;
        margin: 0 0 32px;
    }
    .conf-heading-eyebrow {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: #b91c1c;
        margin-bottom: 8px;
    }
    .conf-heading-block h2.conf-heading {
        font-size: 30px !important;
        font-weight: 900 !important;
        color: #1a1a1a !important;
        letter-spacing: -0.5px !important;
        line-height: 1.1 !important;
        margin: 0 0 10px !important;
        text-transform: uppercase !important;
        border: none !important;
        padding: 0 !important;
    }
    .conf-heading-block h2.conf-heading::after,
    .conf-heading-block h2.conf-heading::before {
        display: none !important;
    }
    .conf-section-sub {
        font-size: 14.5px;
        color: #6b7280;
        margin: 0;
        line-height: 1.6;
    }

    /* ── University Filter Pills ── */
    .conf-univ-filter-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 28px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }
    .conf-univ-filter-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
    }
    .conf-univ-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .conf-univ-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        color: #334155;
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
    }
    .conf-univ-pill:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    .conf-univ-pill.active {
        background: #b91c1c;
        border-color: #b91c1c;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(185, 28, 28, 0.25);
    }
    .conf-univ-pill-logo {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        object-fit: contain;
        background: #ffffff;
        padding: 1px;
    }
    .conf-univ-pill-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        font-size: 11px;
        font-weight: 700;
        border-radius: 9px;
        background: #e2e8f0;
        color: #475569;
    }
    .conf-univ-pill.active .conf-univ-pill-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* ── University Group Header ── */
    .conf-group {
        margin-bottom: 40px;
        transition: all 0.3s ease;
    }
    .conf-group.univ-hidden {
        display: none;
    }
    .conf-group-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 5px solid #b91c1c;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 16px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .conf-group-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .conf-header-logo-wrap {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 50%;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2px;
        overflow: hidden;
    }
    .conf-header-logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 50%;
    }
    .conf-header-name {
        margin: 0 !important;
        font-family: 'Montserrat', sans-serif !important;
        font-size: 17px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        text-transform: none !important;
        border: none !important;
        padding: 0 !important;
    }
    .conf-header-name:after {
        display: none !important;
    }
    .conf-header-sub {
        margin: 0;
        font-size: 12px;
        color: #64748b;
    }

    /* ── Card ── */
    .conf-card {
        display: flex;
        flex-direction: row;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 22px;
        transition: box-shadow 0.22s ease, transform 0.22s ease;
    }
    .conf-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
        border-color: #cbd5e1;
    }

    /* ── Image col ── */
    .conf-card-image {
        position: relative;
        width: 250px;
        min-width: 250px;
        background: #111827;
        overflow: hidden;
        flex-shrink: 0;
    }
    .conf-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.35s ease;
    }
    .conf-card:hover .conf-card-image img {
        transform: scale(1.04);
    }
    .conf-no-image {
        width: 100%;
        height: 100%;
        min-height: 180px;
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #4b5563;
    }
    .conf-no-image svg {
        width: 32px;
        height: 32px;
        stroke: #4b5563;
        fill: none;
        stroke-width: 1.5;
    }
    .conf-no-image span {
        font-size: 11px;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #6b7280;
    }

    /* ── Date badge overlay ── */
    .conf-date-badge {
        position: absolute;
        bottom: 12px;
        left: 12px;
        background: #b91c1c;
        color: #ffffff;
        border-radius: 8px;
        padding: 4px 10px;
        text-align: center;
        line-height: 1.2;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
    }
    .conf-date-badge .day {
        display: block;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }
    .conf-date-badge .mon {
        display: block;
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ── Body ── */
    .conf-card-body {
        padding: 22px 26px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .conf-card-univ-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 2px 8px;
        border-radius: 12px;
        margin-bottom: 8px;
        align-self: flex-start;
    }
    .conf-card-univ-tag img {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        object-fit: contain;
    }
    .conf-card-title {
        font-size: 19px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 14px;
        line-height: 1.4;
    }
    .conf-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }
    .conf-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 4px 10px;
    }
    .conf-meta-item svg {
        width: 14px;
        height: 14px;
        stroke: #6b7280;
        fill: none;
        stroke-width: 1.8;
        flex-shrink: 0;
    }

    /* ── Empty state ── */
    .conf-empty {
        text-align: center;
        padding: 40px 20px;
        color: #64748b;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
    }

    @media (max-width: 768px) {
        .conf-card {
            flex-direction: column;
        }
        .conf-card-image {
            width: 100%;
            min-width: 100%;
            height: 180px;
        }
        .conf-group-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
</style>

<div id="page-content" style="padding-top: 15px;">
    <div class="container">
        <div class="row">
            <div class="col-md-12 conf-col-wrap">
                <div id="page-main">
                    <div class="conf-section">

                        <!-- Heading block -->
                        <div class="conf-heading-block">
                            <span class="conf-heading-eyebrow">Academic Events</span>
                            <h2 class="conf-heading">Conferences &amp; Symposia</h2>
                            <p class="conf-section-sub">Browse academic conferences, workshops, and symposia hosted across all ANRF–PAIR consortium institutions</p>
                        </div>

                        <!-- University Selector Pills -->
                        <div class="conf-univ-filter-container">
                            <div class="conf-univ-filter-label">
                                <i class="fa fa-university"></i>
                                <span>Filter By University:</span>
                            </div>
                            <div class="conf-univ-pills" id="conf-univ-pills">
                                <button class="conf-univ-pill <?= $initialFilter === 'all' ? 'active' : '' ?>" data-univ="all">
                                    <span>All Universities</span>
                                    <span class="conf-univ-pill-count"><?= $totalConferencesCount ?></span>
                                </button>
                                <?php foreach ($institutes as $pfx => $info): ?>
                                    <button class="conf-univ-pill <?= $initialFilter === $pfx ? 'active' : '' ?>" data-univ="<?= $pfx ?>">
                                        <img src="<?= htmlspecialchars($info['logo']) ?>" alt="<?= htmlspecialchars($info['short']) ?>" class="conf-univ-pill-logo">
                                        <span><?= htmlspecialchars($info['short']) ?></span>
                                        <span class="conf-univ-pill-count"><?= $counts[$pfx] ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php elseif ($totalConferencesCount === 0): ?>
                            <div class="conf-empty">
                                <p>No conferences have been registered yet.</p>
                            </div>
                        <?php else: ?>

                            <!-- RENDER CONFERENCES SEPARATED BY UNIVERSITY -->
                            <?php foreach ($institutes as $pfx => $info):
                                $confList = $instituteConferences[$pfx] ?? [];
                                $hasConfs = !empty($confList);
                            ?>
                                <div class="conf-group <?= ($initialFilter !== 'all' && $initialFilter !== $pfx) ? 'univ-hidden' : '' ?>"
                                     data-univ="<?= $pfx ?>"
                                     id="conf-group-<?= $pfx ?>">

                                    <div class="conf-group-header">
                                        <div class="conf-group-header-left">
                                            <div class="conf-header-logo-wrap">
                                                <img src="<?= htmlspecialchars($info['logo']) ?>" alt="<?= htmlspecialchars($info['name']) ?>" class="conf-header-logo">
                                            </div>
                                            <div>
                                                <h3 class="conf-header-name"><?= htmlspecialchars($info['name']) ?></h3>
                                                <span class="conf-header-sub"><?= htmlspecialchars($info['role']) ?> &bull; <?= count($confList) ?> Conference<?= count($confList) === 1 ? '' : 's' ?></span>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="institute.php?name=<?= urlencode($info['name']) ?>&tab=conferences" class="btn btn-default btn-xs" style="font-weight:600;">
                                                Institute Portal &rarr;
                                            </a>
                                        </div>
                                    </div>

                                    <?php if ($hasConfs): ?>
                                        <?php foreach ($confList as $conf):
                                            $startDateVal    = !empty($conf['conf_date']) ? $conf['conf_date'] : ($conf['start_date'] ?? '');
                                            $endDateVal      = !empty($conf['end_date']) ? $conf['end_date'] : '';
                                            $locationVal     = !empty($conf['location']) ? $conf['location'] : '';
                                            $organisersVal   = !empty($conf['organisers']) ? $conf['organisers'] : ($conf['organizer'] ?? '');
                                            $instituteVal    = !empty($conf['institute']) ? $conf['institute'] : '';
                                            $investigatorVal = !empty($conf['investigator']) ? $conf['investigator'] : ($conf['convener'] ?? '');
                                            $resourceVal     = !empty($conf['resource_person']) ? $conf['resource_person'] : '';
                                            $websiteVal      = !empty($conf['website_url']) ? $conf['website_url'] : '';
                                            $contentVal      = !empty($conf['content']) ? $conf['content'] : ($conf['description'] ?? '');
                                        ?>
                                            <div class="conf-card">
                                                <!-- Image / date strip -->
                                                <div class="conf-card-image">
                                                     <?php if (!empty($conf['image'])):
                                                         $imgPath = $conf['image'];
                                                         $imgSrc = file_exists($imgPath) ? $imgPath : (file_exists('admin/' . $imgPath) ? 'admin/' . $imgPath : htmlspecialchars($imgPath));
                                                     ?>
                                                         <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($conf['title']) ?>">
                                                    <?php else: ?>
                                                        <div class="conf-no-image">
                                                            <img src="<?= htmlspecialchars($info['logo']) ?>" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: contain; background: #fff; padding: 2px;">
                                                            <span><?= htmlspecialchars($info['short']) ?> Event</span>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($startDateVal)): ?>
                                                        <div class="conf-date-badge">
                                                            <span class="day"><?= date('d', strtotime($startDateVal)) ?></span>
                                                            <span class="mon"><?= date('M Y', strtotime($startDateVal)) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Content body -->
                                                <div class="conf-card-body">
                                                    <div class="conf-card-univ-tag">
                                                        <img src="<?= htmlspecialchars($info['logo']) ?>" alt="">
                                                        <span><?= htmlspecialchars($info['name']) ?></span>
                                                    </div>

                                                    <h3 class="conf-card-title"><?= htmlspecialchars($conf['title'] ?: 'Untitled Conference') ?></h3>

                                                    <div class="conf-meta-row">
                                                        <?php if (!empty($startDateVal)): ?>
                                                            <span class="conf-meta-item">
                                                                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                                                <?= date('d M Y', strtotime($startDateVal)) ?><?= (!empty($endDateVal) && $endDateVal !== $startDateVal) ? ' – ' . date('d M Y', strtotime($endDateVal)) : '' ?>
                                                            </span>
                                                        <?php endif; ?>

                                                        <?php if (!empty($locationVal)): ?>
                                                            <span class="conf-meta-item">
                                                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                                                <?= htmlspecialchars($locationVal) ?>
                                                            </span>
                                                        <?php endif; ?>

                                                        <?php if (!empty($organisersVal)): ?>
                                                            <span class="conf-meta-item">
                                                                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                                <?= htmlspecialchars($organisersVal) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (!empty($contentVal)): ?>
                                                        <p style="font-size: 13.5px; color: #4b5563; line-height: 1.6; margin-bottom: 14px;">
                                                            <?= nl2br(htmlspecialchars(mb_substr($contentVal, 0, 240) . (mb_strlen($contentVal) > 240 ? '...' : ''))) ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <?php if (!empty($websiteVal)): ?>
                                                        <div style="margin-top: auto; padding-top: 6px;">
                                                            <a href="<?= htmlspecialchars($websiteVal) ?>" target="_blank" class="btn btn-primary btn-sm" style="background: #b91c1c; border-color: #b91c1c; border-radius: 6px; font-weight: 600; padding: 6px 14px; font-size: 12px;">
                                                                <i class="fa fa-external-link me-1"></i> Registration &amp; Official Info
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="conf-empty" style="margin-bottom: 20px;">
                                            <p>No conferences currently listed for <?= htmlspecialchars($info['name']) ?>.</p>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>
                </div><!-- /#page-main -->
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->
    </div><!-- /.container -->
</div>

<?php include 'footer.php'; ?>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const pills = document.querySelectorAll(".conf-univ-pill");
    const groups = document.querySelectorAll(".conf-group");

    pills.forEach(pill => {
        pill.addEventListener("click", () => {
            pills.forEach(p => p.classList.remove("active"));
            pill.classList.add("active");
            const targetUniv = pill.getAttribute("data-univ");

            groups.forEach(group => {
                const groupUniv = group.getAttribute("data-univ");
                if (targetUniv === "all" || groupUniv === targetUniv) {
                    group.style.display = "block";
                } else {
                    group.style.display = "none";
                }
            });

            const url = new URL(window.location.href);
            if (targetUniv === "all") {
                url.searchParams.delete("prefix");
                url.searchParams.delete("name");
            } else {
                url.searchParams.set("prefix", targetUniv);
                url.searchParams.delete("name");
            }
            window.history.replaceState({}, "", url.toString());
        });
    });
});
</script>

</body>
</html>
