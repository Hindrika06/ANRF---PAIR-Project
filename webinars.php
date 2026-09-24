<?php
// 1. INTEGRATE DATABASE CONNECTION VIA YOUR EXISTING CONFIG FILE
require_once 'config.php';

// Define the 7 consortium universities and their metadata
$institutes = [
    'uoh' => [
        'name'      => 'University of Hyderabad',
        'short'     => 'UoH',
        'role'      => 'Hub Institution',
        'role_type' => 'hub',
        'logo'      => '3.png',
        'color'     => '#024283',
        'badge_bg'  => '#eff6ff',
        'badge_txt' => '#1e40af'
    ],
    'cuk' => [
        'name'      => 'Central University of Karnataka',
        'short'     => 'CUK',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/cuk1.jpg',
        'color'     => '#0f766e',
        'badge_bg'  => '#f0fdfa',
        'badge_txt' => '#0f766e'
    ],
    'kannur' => [
        'name'      => 'Kannur University',
        'short'     => 'Kannur',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/ku1.jpg',
        'color'     => '#0369a1',
        'badge_bg'  => '#f0f9ff',
        'badge_txt' => '#0369a1'
    ],
    'mgu' => [
        'name'      => 'Mahatma Gandhi University',
        'short'     => 'MGU',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/mg1.jpg',
        'color'     => '#c2410c',
        'badge_bg'  => '#fff7ed',
        'badge_txt' => '#c2410c'
    ],
    'ou' => [
        'name'      => 'Osmania University',
        'short'     => 'OU',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/ou1.jpg',
        'color'     => '#9d174d',
        'badge_bg'  => '#fdf2f8',
        'badge_txt' => '#9d174d'
    ],
    'svu' => [
        'name'      => 'Sri Venkateswara University',
        'short'     => 'SVU',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/gan1.jpg',
        'color'     => '#b45309',
        'badge_bg'  => '#fffbeb',
        'badge_txt' => '#b45309'
    ],
    'yvu' => [
        'name'      => 'Yogi Vemana University',
        'short'     => 'YVU',
        'role'      => 'Spoke Institution',
        'role_type' => 'spoke',
        'logo'      => 'logos/yu.jpg',
        'color'     => '#15803d',
        'badge_bg'  => '#f0fdf4',
        'badge_txt' => '#15803d'
    ],
];

// Map university full names and aliases to prefix
$prefixMap = [];
foreach ($institutes as $p => $meta) {
    $prefixMap[strtolower($meta['name'])]  = $p;
    $prefixMap[strtolower($meta['short'])] = $p;
    $prefixMap[$p]                         = $p;
}

// Detect requested institute filter from GET params
$initialFilter = 'all';
$requestedInst = $_GET['name'] ?? $_GET['institute'] ?? $_GET['prefix'] ?? null;
if (!empty($requestedInst)) {
    $cleanReq = strtolower(trim($requestedInst));
    if (isset($prefixMap[$cleanReq])) {
        $initialFilter = $prefixMap[$cleanReq];
    }
}

// Fetch webinars separately for each university
$instituteWebinars = [];
$counts = [];
$totalWebinarsCount = 0;

try {
    foreach ($institutes as $p => $meta) {
        $tbl = "{$p}_webinars";
        $instituteWebinars[$p] = [];
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
                        $tA = !empty($a['webinar_date']) ? strtotime($a['webinar_date']) : 0;
                        $tB = !empty($b['webinar_date']) ? strtotime($b['webinar_date']) : 0;
                        return $tB <=> $tA;
                    });
                    $instituteWebinars[$p] = $rows;
                }
            }
        } catch (Exception $e) {}
        $counts[$p] = count($instituteWebinars[$p]);
        $totalWebinarsCount += $counts[$p];
    }
} catch (PDOException $e) {
    // Graceful error capture
}

include 'header.php';
?>

<div class="container">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li><a href="webinars.php">Events</a></li>
        <li class="active">Webinar Series</li>
    </ol>
</div>

<div id="page-content" style="padding-top: 15px;">
    <div class="container">

        <!-- Clean Editorial Hero Banner -->
        <div class="webinar-hero-banner">
            <div class="hero-badge-container">
                <span class="hero-badge">Events & Knowledge Sharing</span>
            </div>
            <h1>ANRF–PAIR Webinar Series</h1>
            <p>Explore specialized academic seminars and scientific lectures organized separately across all 7 participating consortium institutions.</p>
        </div>

        <!-- UNIVERSITY SELECTOR / FILTER TABS BAR -->
        <div class="univ-filter-container">
            <div class="univ-filter-label">
                <i class="fa fa-university"></i>
                <span>Filter By University:</span>
            </div>
            <div class="univ-filter-pills" id="univ-filter-pills">
                <button class="univ-pill <?= $initialFilter === 'all' ? 'active' : '' ?>" data-univ="all">
                    <span class="univ-pill-name">All Universities</span>
                    <span class="univ-pill-count"><?= $totalWebinarsCount ?></span>
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

        <!-- Search & Filter Controls -->
        <div class="webinar-filter-bar">
            <div class="search-input-wrapper">
                <i class="fa fa-search search-icon"></i>
                <input type="text" id="webinar-search" placeholder="Search by topic, speaker, university, or keyword...">
            </div>
            <div class="filter-tabs">
                <button class="filter-btn active" data-filter="all">All Sessions</button>
                <button class="filter-btn" data-filter="upcoming">Upcoming</button>
                <button class="filter-btn" data-filter="past">Past / Recorded</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div id="page-main">
                    <section class="events" id="events">
                        <div class="section-content">

                            <?php if ($totalWebinarsCount > 0): ?>

                                <!-- RENDER WEBINARS SEPARATED BY UNIVERSITY -->
                                <?php foreach ($institutes as $pfx => $info):
                                    $webList = $instituteWebinars[$pfx] ?? [];
                                    $hasItems = !empty($webList);
                                ?>
                                    <div class="university-webinar-group <?= ($initialFilter !== 'all' && $initialFilter !== $pfx) ? 'univ-hidden' : '' ?>"
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
                                                        <i class="fa fa-video-camera"></i>
                                                        <span class="univ-section-count"><?= count($webList) ?></span> Webinar Session<?= count($webList) === 1 ? '' : 's' ?> hosted by this institution
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="univ-group-header-right">
                                                <a href="institute.php?name=<?= urlencode($info['name']) ?>&tab=webinars" class="univ-portal-link" title="Visit University Dashboard">
                                                    <span>Institute Portal</span>
                                                    <i class="fa fa-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- University Webinar Cards List -->
                                        <?php if ($hasItems): ?>
                                            <div class="webinar-list">
                                                <?php foreach ($webList as $row):
                                                    $timestamp = !empty($row['webinar_date']) ? strtotime($row['webinar_date']) : time();
                                                    $month = date('M', $timestamp);
                                                    $day   = date('d', $timestamp);
                                                    $year  = date('Y', $timestamp);

                                                    $isUpcoming = $timestamp >= time();
                                                    $typeAttr = $isUpcoming ? 'upcoming' : 'past';

                                                    $speakerVal     = !empty($row['speaker_name']) ? $row['speaker_name'] : ($row['investigator'] ?? '');
                                                    $affiliationVal = !empty($row['affiliation']) ? $row['affiliation'] : ($row['institute'] ?? '');
                                                    $descriptionVal = !empty($row['description']) ? $row['description'] : ($row['content'] ?? '');
                                                    $linkVal        = !empty($row['link']) ? $row['link'] : '';
                                                    $organisersVal  = !empty($row['organisers']) ? $row['organisers'] : '';

                                                    $displayDesc = $descriptionVal;
                                                    if (mb_strlen($displayDesc) > 190) {
                                                        $displayDesc = mb_substr($displayDesc, 0, 185) . '...';
                                                    }
                                                ?>
                                                    <article class="webinar-card"
                                                             data-univ="<?= $pfx ?>"
                                                             data-title="<?= htmlspecialchars(strtolower($row['title'] ?? '')) ?>"
                                                             data-speaker="<?= htmlspecialchars(strtolower($speakerVal)) ?>"
                                                             data-affiliation="<?= htmlspecialchars(strtolower($affiliationVal)) ?>"
                                                             data-description="<?= htmlspecialchars(strtolower($descriptionVal)) ?>"
                                                             data-organizer="<?= htmlspecialchars(strtolower($organisersVal)) ?>"
                                                             data-type="<?= $typeAttr ?>">

                                                        <!-- Date Badge (Calendar Style) -->
                                                        <div class="webinar-date-card">
                                                            <div class="webinar-date-header"><?= htmlspecialchars($month) ?></div>
                                                            <div class="webinar-date-body">
                                                                <span class="webinar-date-day"><?= htmlspecialchars($day) ?></span>
                                                                <span class="webinar-date-year"><?= htmlspecialchars($year) ?></span>
                                                            </div>
                                                        </div>

                                                        <!-- Main content -->
                                                        <div class="webinar-card-content">
                                                            <div class="webinar-header-tags">
                                                                <div class="webinar-status-badge <?= $isUpcoming ? 'badge-upcoming' : 'badge-past' ?>">
                                                                    <i class="fa <?= $isUpcoming ? 'fa-calendar-check-o' : 'fa-check-circle' ?>"></i>
                                                                    <span><?= $isUpcoming ? 'Upcoming Session' : 'Completed Session' ?></span>
                                                                </div>
                                                                <div class="card-univ-tag">
                                                                    <img src="<?= htmlspecialchars($info['logo']) ?>" alt="<?= htmlspecialchars($info['short']) ?>">
                                                                    <span><?= htmlspecialchars($info['name']) ?></span>
                                                                </div>
                                                            </div>

                                                            <h3 class="webinar-title"><?= htmlspecialchars($row['title'] ?? 'Untitled Webinar') ?></h3>

                                                            <div class="webinar-meta-grid">
                                                                <div class="meta-item">
                                                                    <i class="fa fa-clock-o meta-icon"></i>
                                                                    <span><?= htmlspecialchars(date('h:i A', $timestamp)) ?> (IST)</span>
                                                                </div>

                                                                <?php if (!empty($speakerVal)): ?>
                                                                    <div class="meta-item">
                                                                        <i class="fa fa-user-md meta-icon"></i>
                                                                        <span><strong>Speaker:</strong> <?= htmlspecialchars($speakerVal) ?></span>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php if (!empty($affiliationVal)): ?>
                                                                    <div class="meta-item">
                                                                        <i class="fa fa-university meta-icon"></i>
                                                                        <span><?= htmlspecialchars($affiliationVal) ?></span>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <?php if (!empty($displayDesc)): ?>
                                                                <p class="webinar-description"><?= nl2br(htmlspecialchars($displayDesc)) ?></p>
                                                            <?php endif; ?>

                                                            <div class="webinar-footer-details">
                                                                <?php if (!empty($organisersVal)): ?>
                                                                    <div class="webinar-tag">
                                                                        <span class="tag-label">Organized by</span>
                                                                        <span class="tag-value"><?= htmlspecialchars($organisersVal) ?></span>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php if (!empty($linkVal)): ?>
                                                                    <a href="<?= htmlspecialchars($linkVal) ?>" target="_blank" rel="noopener noreferrer" class="webinar-btn <?= $isUpcoming ? 'btn-join' : 'btn-recording' ?>">
                                                                        <i class="fa <?= $isUpcoming ? 'fa-video-camera' : 'fa-play-circle' ?>"></i>
                                                                        <span><?= $isUpcoming ? 'Register / Join Session' : 'View Session Link' ?></span>
                                                                        <i class="fa fa-external-link" style="font-size: 11px; margin-left: 2px;"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- Card Image Thumbnail -->
                                                        <div class="webinar-image-wrapper">
                                                             <?php if (!empty($row['image'])):
                                                                 $imgPath = $row['image'];
                                                                 $imgSrc = file_exists($imgPath) ? $imgPath : (file_exists('admin/' . $imgPath) ? 'admin/' . $imgPath : htmlspecialchars($imgPath));
                                                             ?>
                                                                 <img src="<?= $imgSrc ?>" alt="Webinar Presentation" class="webinar-thumbnail">
                                                            <?php else: ?>
                                                                <div class="webinar-fallback-thumbnail">
                                                                    <img src="<?= htmlspecialchars($info['logo']) ?>" alt="" style="width: 38px; height: 38px; border-radius: 50%; object-fit: contain; margin-bottom: 6px; background: #fff; padding: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                                                    <span><?= htmlspecialchars($info['short']) ?> Seminar</span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                    </article>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="univ-empty-box">
                                                <i class="fa fa-calendar-times-o"></i>
                                                <p>No webinars currently registered under <?= htmlspecialchars($info['name']) ?>.</p>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                <?php endforeach; ?>

                                <!-- Dynamic Client-side No Results State -->
                                <div id="no-results-message" style="display: none; text-align: center; padding: 50px 30px; border-radius: 16px; background: #ffffff; border: 1.5px dashed #cbd5e1; margin-top: 20px;">
                                    <i class="fa fa-search" style="font-size: 32px; color: #94a3b8; margin-bottom: 12px; display: block;"></i>
                                    <h4 style="margin: 0 0 6px; font-weight: 700; color: #334155;">No webinars match your filters</h4>
                                    <p style="margin: 0; color: #64748b; font-size: 14px;">Try modifying your keywords or choosing a different status filter.</p>
                                </div>

                            <?php else: ?>
                                <div class="alert alert-info" style="text-align: center; padding: 40px; border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569;">
                                    <i class="fa fa-calendar-o" style="font-size: 32px; margin-bottom: 12px; color: #94a3b8; display: block;"></i>
                                    <h4 style="margin: 0 0 6px; font-weight: 700; color: #334155;">No webinars in records</h4>
                                    <p style="margin: 0; color: #64748b; font-size: 14px;">There are currently no webinars loaded in the database.</p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php';?>

</div>

<!-- Interactive Client-side Filter Logic -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("webinar-search");
    const statusBtns = document.querySelectorAll(".filter-btn");
    const univPills = document.querySelectorAll(".univ-pill");
    const groups = document.querySelectorAll(".university-webinar-group");
    const cards = document.querySelectorAll(".webinar-card");
    const noResults = document.getElementById("no-results-message");

    let activeUniv = "<?= $initialFilter ?>";
    let activeStatus = "all";
    let searchQuery = "";

    function filterAll() {
        let totalVisibleInAllGroups = 0;

        groups.forEach(group => {
            const groupUniv = group.getAttribute("data-univ");
            const matchesUniv = (activeUniv === "all") || (groupUniv === activeUniv);

            if (!matchesUniv) {
                group.style.display = "none";
                return;
            }

            // Group is eligible; now check cards inside this group
            let visibleInGroup = 0;
            const groupCards = group.querySelectorAll(".webinar-card");

            groupCards.forEach(card => {
                const title = card.getAttribute("data-title") || "";
                const speaker = card.getAttribute("data-speaker") || "";
                const affil = card.getAttribute("data-affiliation") || "";
                const desc = card.getAttribute("data-description") || "";
                const organizer = card.getAttribute("data-organizer") || "";
                const cardType = card.getAttribute("data-type") || "";

                const matchesSearch = !searchQuery ||
                    title.includes(searchQuery) ||
                    speaker.includes(searchQuery) ||
                    affil.includes(searchQuery) ||
                    desc.includes(searchQuery) ||
                    organizer.includes(searchQuery) ||
                    groupUniv.includes(searchQuery);

                const matchesStatus = (activeStatus === "all") || (cardType === activeStatus);

                if (matchesSearch && matchesStatus) {
                    card.style.display = "flex";
                    card.style.opacity = "1";
                    card.style.transform = "scale(1)";
                    visibleInGroup++;
                    totalVisibleInAllGroups++;
                } else {
                    card.style.display = "none";
                    card.style.opacity = "0";
                    card.style.transform = "scale(0.98)";
                }
            });

            // If search is active and group has 0 matches, hide the group
            if (searchQuery && visibleInGroup === 0) {
                group.style.display = "none";
            } else {
                group.style.display = "block";
            }
        });

        if (noResults) {
            noResults.style.display = (totalVisibleInAllGroups === 0) ? "block" : "none";
        }
    }

    // University Pill Click Event
    univPills.forEach(pill => {
        pill.addEventListener("click", () => {
            univPills.forEach(p => p.classList.remove("active"));
            pill.classList.add("active");
            activeUniv = pill.getAttribute("data-univ");

            // Update browser URL query parameter without full reload
            const url = new URL(window.location.href);
            if (activeUniv === "all") {
                url.searchParams.delete("prefix");
                url.searchParams.delete("name");
            } else {
                url.searchParams.set("prefix", activeUniv);
                url.searchParams.delete("name");
            }
            window.history.replaceState({}, "", url.toString());

            filterAll();
        });
    });

    // Status Filter Tabs Click Event
    statusBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            statusBtns.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            activeStatus = btn.getAttribute("data-filter");
            filterAll();
        });
    });

    // Search Input Event
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            searchQuery = e.target.value.toLowerCase().trim();
            filterAll();
        });
    }

    // Initial Filter Run
    filterAll();
});
</script>

<style>
    /* Kill any template underlines */
    .webinar-section__head h2::after,
    .webinar-section__head h2::before,
    h2.no-theme-underline::after,
    h2.no-theme-underline::before {
        display: none !important;
        content: none !important;
        border: none !important;
        background: none !important;
        height: 0 !important;
        width: 0 !important;
    }

    /* Clean Editorial Hero Banner */
    .webinar-hero-banner {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border: 1px solid #e2e8f0;
        border-left: 5px solid #024283;
        padding: 30px 35px;
        border-radius: 16px;
        margin-bottom: 24px;
        text-align: left;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
    }
    .webinar-hero-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 380px;
        height: 380px;
        background: radial-gradient(circle, rgba(2, 66, 131, 0.03) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-badge-container {
        display: block;
        margin-bottom: 10px;
    }
    .hero-badge {
        background: rgba(179, 58, 58, 0.08);
        color: #b33a3a;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        display: inline-block;
        border: 1px solid rgba(179, 58, 58, 0.15);
    }
    .webinar-hero-banner h1 {
        display: block !important;
        font-family: 'Montserrat', sans-serif !important;
        font-size: 28px !important;
        font-weight: 800 !important;
        color: #024283 !important;
        margin: 10px 0 10px 0 !important;
        letter-spacing: -0.5px !important;
        text-transform: uppercase !important;
    }
    .webinar-hero-banner h1:after {
        display: none !important;
    }
    .webinar-hero-banner p {
        font-size: 14.5px;
        color: #475569;
        max-width: 800px;
        margin: 0;
        line-height: 1.6;
    }

    /* UNIVERSITY SELECTOR PILLS BAR */
    .univ-filter-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    }
    .univ-filter-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 12px;
    }
    .univ-filter-label i {
        color: #024283;
        font-size: 14px;
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
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        color: #334155;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
    }
    .univ-pill:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-1px);
    }
    .univ-pill.active {
        background: #024283;
        border-color: #024283;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(2, 66, 131, 0.25);
    }
    .univ-pill-logo {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        object-fit: contain;
        background: #ffffff;
        padding: 1px;
        border: 1px solid rgba(0,0,0,0.08);
    }
    .univ-pill-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        font-size: 11px;
        font-weight: 700;
        border-radius: 10px;
        background: #e2e8f0;
        color: #475569;
    }
    .univ-pill.active .univ-pill-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* Filter Bar style */
    .webinar-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
        background: #ffffff;
        padding: 14px 20px;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        border: 1px solid #e2e8f0;
    }
    .search-input-wrapper {
        position: relative;
        flex: 1;
        max-width: 500px;
    }
    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
    }
    #webinar-search {
        width: 100%;
        padding: 10px 16px 10px 42px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.2s ease;
    }
    #webinar-search:focus {
        outline: none;
        border-color: #024283;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(2, 66, 131, 0.08);
    }
    .filter-tabs {
        display: flex;
        gap: 6px;
    }
    .filter-btn {
        background: #f1f5f9;
        color: #475569;
        border: none;
        padding: 9px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-btn:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .filter-btn.active {
        background: #024283;
        color: #ffffff;
    }

    /* UNIVERSITY SECTION GROUPING */
    .university-webinar-group {
        margin-bottom: 45px;
        transition: all 0.3s ease;
    }
    .university-webinar-group.univ-hidden {
        display: none;
    }

    .univ-group-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 5px solid #024283;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 18px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .univ-group-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .univ-header-logo-wrap {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 50%;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3px;
        overflow: hidden;
    }
    .univ-header-logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 50%;
    }
    .univ-header-text {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .univ-header-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .univ-header-name {
        margin: 0 !important;
        font-family: 'Montserrat', sans-serif !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -0.3px !important;
        text-transform: none !important;
        border: none !important;
        padding: 0 !important;
    }
    .univ-header-name:after {
        display: none !important;
    }
    .univ-role-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 3px 8px;
        border-radius: 6px;
    }
    .role-hub {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .role-spoke {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .univ-header-sub {
        margin: 0;
        font-size: 12.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .univ-header-sub i {
        color: #024283;
    }
    .univ-portal-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 700;
        color: #024283;
        background: #f1f5f9;
        padding: 8px 14px;
        border-radius: 8px;
        text-decoration: none !important;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .univ-portal-link:hover {
        background: #024283;
        color: #ffffff;
        border-color: #024283;
    }

    .univ-empty-box {
        text-align: center;
        padding: 35px 20px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        color: #64748b;
        font-size: 13.5px;
    }
    .univ-empty-box i {
        font-size: 24px;
        margin-bottom: 8px;
        color: #94a3b8;
        display: block;
    }

    /* Webinar Cards */
    .webinar-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .webinar-card {
        display: flex;
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 22px;
        gap: 22px;
        align-items: stretch;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .webinar-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }
    .webinar-card::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: transparent;
        transition: background 0.25s ease;
    }
    .webinar-card[data-type="upcoming"]:hover::after {
        background: #22c55e;
    }
    .webinar-card[data-type="past"]:hover::after {
        background: #024283;
    }

    /* Date Badges */
    .webinar-date-card {
        display: flex;
        flex-direction: column;
        width: 74px;
        height: 84px;
        border-radius: 12px;
        overflow: hidden;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        flex-shrink: 0;
        text-align: center;
        background: #ffffff;
    }
    .webinar-date-header {
        background: #b33a3a;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding: 4px 0;
        line-height: 1.2;
    }
    .webinar-date-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: #f8fafc;
    }
    .webinar-date-day {
        font-size: 22px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1;
    }
    .webinar-date-year {
        font-size: 10px;
        font-weight: 600;
        color: #64748b;
        margin-top: 2px;
    }

    /* Card Contents */
    .webinar-card-content {
        flex-grow: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }
    .webinar-header-tags {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .webinar-status-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 6px;
    }
    .badge-upcoming {
        background: rgba(34, 197, 94, 0.1);
        color: #166534;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }
    .badge-past {
        background: rgba(2, 66, 131, 0.08);
        color: #024283;
        border: 1px solid rgba(2, 66, 131, 0.15);
    }
    .card-univ-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .card-univ-tag img {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        object-fit: contain;
    }

    .webinar-title {
        font-family: 'Montserrat', sans-serif !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.4 !important;
        margin: 0 0 10px 0 !important;
        text-transform: none !important;
        transition: color 0.2s ease;
    }
    .webinar-card:hover .webinar-title {
        color: #024283 !important;
    }
    .webinar-meta-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 12px;
        align-items: center;
    }
    .meta-item {
        display: flex;
        align-items: center;
        font-size: 13px;
        color: #475569;
        gap: 6px;
    }
    .meta-icon {
        color: #b33a3a;
        font-size: 13px;
    }
    .webinar-description {
        font-size: 13.5px;
        color: #475569;
        line-height: 1.6;
        margin: 0 0 16px 0;
    }
    .webinar-footer-details {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    /* Tag styles */
    .webinar-tag {
        display: inline-flex;
        align-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        font-size: 12px;
    }
    .tag-label {
        background: #f1f5f9;
        color: #475569;
        padding: 5px 10px;
        font-weight: 600;
        border-right: 1px solid #e2e8f0;
    }
    .tag-value {
        background: #ffffff;
        color: #1e293b;
        padding: 5px 12px;
        font-weight: 500;
    }

    /* Button styles */
    .webinar-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }
    .btn-join {
        background: #22c55e;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
    }
    .btn-join:hover {
        background: #16a34a;
        transform: translateY(-1px);
    }
    .btn-recording {
        background: #024283;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(2, 66, 131, 0.15);
    }
    .btn-recording:hover {
        background: #0d2c54;
        transform: translateY(-1px);
    }

    /* Image Wrapper */
    .webinar-image-wrapper {
        width: 180px;
        height: 125px;
        border-radius: 10px;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        background: #f8fafc;
    }
    .webinar-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .webinar-card:hover .webinar-thumbnail {
        transform: scale(1.05);
    }

    /* Fallback Image */
    .webinar-fallback-thumbnail {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        color: #64748b;
        text-align: center;
        padding: 10px;
    }
    .webinar-fallback-thumbnail span {
        font-size: 10.5px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    /* Responsive Breakdown */
    @media (max-width: 991px) {
        .univ-group-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        .webinar-card {
            flex-direction: column;
            align-items: stretch;
            padding: 18px;
            gap: 14px;
        }
        .webinar-date-card {
            flex-direction: row;
            width: auto;
            height: auto;
            border: none;
            box-shadow: none;
            background: transparent;
            align-items: center;
            gap: 8px;
        }
        .webinar-date-header {
            padding: 4px 10px;
            border-radius: 6px;
        }
        .webinar-date-body {
            flex-direction: row;
            background: transparent;
            gap: 4px;
        }
        .webinar-date-day {
            font-size: 16px;
        }
        .webinar-date-year {
            font-size: 13px;
            margin-top: 0;
        }
        .webinar-image-wrapper {
            width: 100%;
            height: 160px;
            order: -1;
        }
        .webinar-filter-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .search-input-wrapper {
            max-width: 100%;
        }
        .filter-tabs {
            justify-content: flex-start;
            overflow-x: auto;
        }
    }
</style>

</body>
</html>
