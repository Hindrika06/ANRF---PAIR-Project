<?php 
$bodyClass = 'page-homepage-courses';
$activePage = '';
require_once 'config.php';

$institutes = [
    'uoh' => [
        'name'      => 'University of Hyderabad',
        'short'     => 'UoH',
        'role'      => 'Hub Institution',
        'logo'      => '3.png',
    ],
    'cuk' => [
        'name'      => 'Central University of Karnataka',
        'short'     => 'CUK',
        'role'      => 'Spoke Institution',
        'logo'      => 'logos/cuk1.jpg',
    ],
    'kannur' => [
        'name'      => 'Kannur University',
        'short'     => 'Kannur',
        'role'      => 'Spoke Institution',
        'logo'      => 'logos/ku1.jpg',
    ],
    'mgu' => [
        'name'      => 'Mahatma Gandhi University',
        'short'     => 'MGU',
        'role'      => 'Spoke Institution',
        'logo'      => 'logos/mg1.jpg',
    ],
    'ou' => [
        'name'      => 'Osmania University',
        'short'     => 'OU',
        'role'      => 'Spoke Institution',
        'logo'      => 'logos/ou1.jpg',
    ],
    'svu' => [
        'name'      => 'Sri Venkateswara University',
        'short'     => 'SVU',
        'role'      => 'Spoke Institution',
        'logo'      => 'logos/gan1.jpg',
    ],
    'yvu' => [
        'name'      => 'Yogi Vemana University',
        'short'     => 'YVU',
        'role'      => 'Spoke Institution',
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

$institutePubs = [];
$counts = [];
$totalPubsCount = 0;

try {
    foreach ($institutes as $p => $info) {
        $tbl = "{$p}_publications";
        $institutePubs[$p] = [];
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
                $stmt = $pdo->query("SELECT *, '$p' AS institute_prefix FROM `$tbl` $whereClause ORDER BY id DESC");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    $institutePubs[$p] = $rows;
                }
            }
        } catch (Exception $e) {}
        $counts[$p] = count($institutePubs[$p]);
        $totalPubsCount += $counts[$p];
    }
} catch (PDOException $e) {}

include 'header.php';
?>

<div id="page-content">
    <div class="container">
        <ol class="breadcrumb" style="font-size: 14px;">
            <li><a href="index.php">Home</a></li>
            <li><a href="publications-reports.php">Research</a></li>
            <li class="active">Publications &amp; Reports</li>
        </ol>
    </div>

   <section id="course-detail" style="margin-top: 15px;">
    <div class="block" style="background-color: #fff;">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="course-info">
                        <div class="overview-content">

                            <div>
                                <h2 class="no-theme-underline" style="font-size: 24px !important; font-weight: bold !important; text-transform: uppercase !important; text-decoration: none !important; border: none !important; border-left: 5px solid #002b5c !important; padding-left: 12px !important; margin-top: 15px !important; margin-bottom: 20px !important; color: #002b5c !important; background: none !important; background-image: none !important; box-shadow: none !important; outline: none !important;">PUBLICATIONS &amp; RESEARCH OUTPUT</h2>
                            </div>

                            <!-- University Filter Pills Bar -->
                            <div class="pub-univ-filter-container">
                                <div class="pub-univ-filter-label">
                                    <i class="fa fa-university"></i>
                                    <span>Filter By University:</span>
                                </div>
                                <div class="pub-univ-pills">
                                    <button class="pub-univ-pill <?= $initialFilter === 'all' ? 'active' : '' ?>" data-univ="all">
                                        <span>All Universities</span>
                                        <span class="pub-univ-pill-count"><?= $totalPubsCount ?></span>
                                    </button>
                                    <?php foreach ($institutes as $pfx => $info): ?>
                                        <button class="pub-univ-pill <?= $initialFilter === $pfx ? 'active' : '' ?>" data-univ="<?= $pfx ?>">
                                            <img src="<?= htmlspecialchars($info['logo']) ?>" alt="<?= htmlspecialchars($info['short']) ?>" class="pub-univ-pill-logo">
                                            <span><?= htmlspecialchars($info['short']) ?></span>
                                            <span class="pub-univ-pill-count"><?= $counts[$pfx] ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="publications-container">
                                <?php if ($totalPubsCount > 0): ?>
                                    <?php foreach ($institutes as $pfx => $info):
                                        $pubList = $institutePubs[$pfx] ?? [];
                                        $hasPubs = !empty($pubList);
                                    ?>
                                        <div class="univ-pub-group <?= ($initialFilter !== 'all' && $initialFilter !== $pfx) ? 'univ-hidden' : '' ?>"
                                             data-univ="<?= $pfx ?>"
                                             id="pub-group-<?= $pfx ?>">

                                            <div class="univ-pub-header">
                                                <div class="univ-pub-header-left">
                                                    <div class="univ-pub-logo-wrap">
                                                        <img src="<?= htmlspecialchars($info['logo']) ?>" alt="<?= htmlspecialchars($info['name']) ?>">
                                                    </div>
                                                    <div>
                                                        <h3 class="univ-pub-name"><?= htmlspecialchars($info['name']) ?></h3>
                                                        <span class="univ-pub-sub"><?= htmlspecialchars($info['role']) ?> &bull; <?= count($pubList) ?> Publication<?= count($pubList) === 1 ? '' : 's' ?></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <a href="institute.php?name=<?= urlencode($info['name']) ?>&tab=publications" class="btn btn-default btn-xs" style="font-weight:600;">
                                                        Institute Profile &rarr;
                                                    </a>
                                                </div>
                                            </div>

                                            <?php if ($hasPubs): ?>
                                                <ul class="pub-list">
                                                    <?php foreach ($pubList as $row):
                                                        $title = trim($row['publication_title'] ?? '');
                                                        $doiUrl = !empty($row['doi_number']) ? (strpos($row['doi_number'], 'http') === 0 ? $row['doi_number'] : 'https://doi.org/' . $row['doi_number']) : null;
                                                        $pubYear = !empty($row['publication_date']) ? date('Y', strtotime($row['publication_date'])) : '';
                                                    ?>
                                                        <li class="pub-item">
                                                            <strong><?= htmlspecialchars($row['author_name'] ?? '') ?></strong>. 
                                                            <span class="pub-title-text"><?= htmlspecialchars($title) ?>.</span>
                                                            <?php if (!empty($row['publication_journal'])): ?>
                                                                <em><?= htmlspecialchars($row['publication_journal']) ?></em>
                                                            <?php endif; ?>
                                                            <?php if ($pubYear): ?>
                                                                (<?= htmlspecialchars($pubYear) ?>).
                                                            <?php endif; ?>
                                                            <?php if (!empty($row['impact_factor']) && $row['impact_factor'] > 0): ?>
                                                                <span class="badge-impact">[IF: <?= htmlspecialchars($row['impact_factor']) ?>]</span>
                                                            <?php endif; ?>
                                                            <?php if ($doiUrl): ?>
                                                                <a href="<?= htmlspecialchars($doiUrl) ?>" target="_blank" class="pub-link">doi: <?= htmlspecialchars($row['doi_number']) ?></a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($row['task_no'])): ?>
                                                                <span class="badge-task">[<?= htmlspecialchars($row['task_no']) ?>]</span>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="text-muted" style="padding: 15px; background: #f8fafc; border-radius: 8px;">No publications currently recorded under <?= htmlspecialchars($info['name']) ?>.</p>
                                            <?php endif; ?>

                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No publications found in the database.</p>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</div>

<style>
    /* Strict overrides to completely crush template-generated heading lines/borders */
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

    /* Filter Pills */
    .pub-univ-filter-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 25px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .pub-univ-filter-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
    }
    .pub-univ-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .pub-univ-pill {
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
    .pub-univ-pill:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    .pub-univ-pill.active {
        background: #002b5c;
        border-color: #002b5c;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 43, 92, 0.25);
    }
    .pub-univ-pill-logo {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        object-fit: contain;
        background: #ffffff;
        padding: 1px;
    }
    .pub-univ-pill-count {
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
    .pub-univ-pill.active .pub-univ-pill-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* University Group Header */
    .univ-pub-group {
        margin-bottom: 35px;
        transition: all 0.3s ease;
    }
    .univ-pub-group.univ-hidden {
        display: none;
    }
    .univ-pub-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 5px solid #002b5c;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.02);
    }
    .univ-pub-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .univ-pub-logo-wrap {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 50%;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        padding: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .univ-pub-logo-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 50%;
    }
    .univ-pub-name {
        margin: 0 !important;
        font-family: 'Montserrat', sans-serif !important;
        font-size: 16px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        text-transform: none !important;
        border: none !important;
        padding: 0 !important;
    }
    .univ-pub-name:after {
        display: none !important;
    }
    .univ-pub-sub {
        font-size: 12px;
        color: #64748b;
    }

    .pub-list {
        list-style-type: decimal; 
        margin-left: 20px;
        font-size: 14.5px;
    }
    .pub-item {
        margin-bottom: 16px;
        line-height: 1.6;
        color: #334155;
    }
    .pub-title-text {
        font-weight: 500;
        color: #0f172a;
    }
    .pub-list a.pub-link {
        color: #024283;
        text-decoration: none !important;
        font-size: 13px;
    }
    .pub-list a.pub-link:hover {
        text-decoration: underline !important; 
    }
    .badge-task {
        font-size: 10.5px;
        color: #64748b;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 6px;
    }
    .badge-impact {
        font-size: 11px;
        color: #15803d;
        font-weight: 700;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 4px;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const pills = document.querySelectorAll(".pub-univ-pill");
    const groups = document.querySelectorAll(".univ-pub-group");

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

<?php include 'footer.php';?>
