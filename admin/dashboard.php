<?php
require_once 'auth_check.php';
require_once 'role_access.php';

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

require_once 'config/db.php';

// Function to safely query counts
if (!function_exists('getTableCount')) {
    function getTableCount($pdo, $tableName, $whereClause = "") {
        try {
            $sql = "SELECT COUNT(*) FROM `$tableName`" . ($whereClause ? " WHERE $whereClause" : "");
            return (int)$pdo->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}

// Fetch counts
$is_super = isSuperAdmin();
$pendingApprovalsCount = 0;
if ($is_super) {
    $pendingApprovalsCount = getTableCount($pdo, "approval_requests", "status = 'Pending'");
}

$countPublications  = getTableCount($pdo, "{$prefix}_publications");
$countPatents       = getTableCount($pdo, "{$prefix}_patent");
$countConferences   = getTableCount($pdo, "{$prefix}_conferences");
$countWebinars      = getTableCount($pdo, "{$prefix}_webinars");
$countInternships   = getTableCount($pdo, "{$prefix}_internships");
$countReports       = getTableCount($pdo, "{$prefix}_progress_reports");
$countGallery       = getTableCount($pdo, "{$prefix}_gallery_events") + getTableCount($pdo, "gallery_albums", "institute_prefix = '$prefix' OR institute_prefix = 'all'");

$countTeam            = getTableCount($pdo, "team", "status = 'Active'");
$countEvents          = getTableCount($pdo, "events", $is_super ? "" : "university_id = '$prefix' OR university_id = 'all'");
$countBanners         = getTableCount($pdo, "homepage_banners", "status = 'Active'");
$countAnnouncements   = getTableCount($pdo, "announcements", "is_active = 1");
$countCollaborations  = getTableCount($pdo, "collaborations", "status = 'Active'");
$countResearch        = getTableCount($pdo, "research_areas", "status = 'Active'");
$countInfrastructure  = getTableCount($pdo, "infrastructure_facilities", "status = 'Active'");
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
    /* ══════════════════════════════════════════════════════
       PREMIUM MODERN SAAS KPI CARD THEME
       Inspired by Stripe, Vercel, Linear, Notion & Framer
    ══════════════════════════════════════════════════════ */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    
    .unified-dash {
        padding-bottom: 50px;
        background-color: #f8fafc;
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* ── Header Welcome Style ── */
    .dash-header-minimal {
        padding: 16px 0 24px 0;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 28px;
    }
    .dash-header-minimal h2 {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 6px 0;
        letter-spacing: -0.5px;
    }
    .dash-header-minimal p {
        font-size: 14px;
        color: #64748b;
        margin: 0;
    }
    .dash-header-minimal strong {
        color: #b21e1e;
        font-weight: 700;
    }

    /* ── Section Headers ── */
    .sec-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        margin-top: 10px;
    }
    .sec-line-left {
        width: 4px;
        height: 16px;
        background: #b21e1e;
        border-radius: 4px;
    }
    .sec-title {
        font-size: 12.5px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin: 0;
    }
    .sec-line-right {
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    /* ── 4-Column Responsive Grid ── */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }

    @media (max-width: 1200px) {
        .kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
    }

      /* ── Single-Color Identity SaaS KPI Card Design ── */
    .kpi-card {
        background: #FFFFFF;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--kpi-accent);
        border-radius: 18px;
        padding: 20px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 125px;
        text-decoration: none !important;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        transition: transform 250ms cubic-bezier(0.4, 0, 0.2, 1), box-shadow 250ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12), 0 0 20px var(--kpi-glow);
        border-top-color: var(--kpi-accent);
    }

    .kpi-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 2;
    }

    /* Top Left Icon Container */
    .kpi-icon-container {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--kpi-icon-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 250ms ease;
        z-index: 2;
    }

    .kpi-card:hover .kpi-icon-container {
        transform: scale(1.05);
    }

    .kpi-body {
        z-index: 2;
        margin-top: 12px;
    }

    .kpi-number {
        font-size: 28px;
        font-weight: 700;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--kpi-accent);
        line-height: 1;
        letter-spacing: -0.5px;
    }

    .kpi-title {
        font-size: 14px;
        font-weight: 600;
        color: #1E293B;
        margin-top: 4px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .kpi-link {
        font-size: 12px;
        font-weight: 600;
        color: var(--kpi-accent);
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: opacity 250ms ease;
    }

    .kpi-link-arrow {
        display: inline-block;
        transition: transform 250ms cubic-bezier(0.4, 0, 0.2, 1);
        width: 12px;
        height: 12px;
    }

    .kpi-card:hover .kpi-link-arrow {
        transform: translateX(4px);
    }

    /* Approved Single-Color Assignments */
    .kpi-publications {
        --kpi-accent: #1E88C7;
        --kpi-icon-bg: rgba(30, 136, 199, 0.12);
        --kpi-glow: rgba(30, 136, 199, 0.15);
    }

    .kpi-patents {
        --kpi-accent: #00897B;
        --kpi-icon-bg: rgba(0, 137, 123, 0.12);
        --kpi-glow: rgba(0, 137, 123, 0.15);
    }

    .kpi-conferences {
        --kpi-accent: #7E57C2;
        --kpi-icon-bg: rgba(126, 87, 194, 0.12);
        --kpi-glow: rgba(126, 87, 194, 0.15);
    }

    .kpi-webinars {
        --kpi-accent: #831843;
        --kpi-icon-bg: rgba(131, 24, 67, 0.12);
        --kpi-glow: rgba(131, 24, 67, 0.15);
    }

    .kpi-internships {
        --kpi-accent: #1E88C7;
        --kpi-icon-bg: rgba(30, 136, 199, 0.12);
        --kpi-glow: rgba(30, 136, 199, 0.15);
    }

    .kpi-reports {
        --kpi-accent: #0e7490;
        --kpi-icon-bg: rgba(14, 116, 144, 0.12);
        --kpi-glow: rgba(14, 116, 144, 0.15);
    }

    .kpi-gallery {
        --kpi-accent: #F0932B;
        --kpi-icon-bg: rgba(240, 147, 43, 0.12);
        --kpi-glow: rgba(240, 147, 43, 0.15);
    }

    .kpi-calendar {
        --kpi-accent: #7E57C2;
        --kpi-icon-bg: rgba(126, 87, 194, 0.12);
        --kpi-glow: rgba(126, 87, 194, 0.15);
    }

    /* CMS Section Color Themes */
    .kpi-banners {
        --kpi-accent: #7E57C2;
        --kpi-icon-bg: rgba(126, 87, 194, 0.12);
        --kpi-glow: rgba(126, 87, 194, 0.15);
    }

    .kpi-tickers {
        --kpi-accent: #00897B;
        --kpi-icon-bg: rgba(0, 137, 123, 0.12);
        --kpi-glow: rgba(0, 137, 123, 0.15);
    }

    .kpi-team {
        --kpi-accent: #F0932B;
        --kpi-icon-bg: rgba(240, 147, 43, 0.12);
        --kpi-glow: rgba(240, 147, 43, 0.15);
    }

    .kpi-collabs {
        --kpi-accent: #1E88C7;
        --kpi-icon-bg: rgba(30, 136, 199, 0.12);
        --kpi-glow: rgba(30, 136, 199, 0.15);
    }

    .kpi-research {
        --kpi-accent: #00897B;
        --kpi-icon-bg: rgba(0, 137, 123, 0.12);
        --kpi-glow: rgba(0, 137, 123, 0.15);
    }

    .kpi-facilities {
        --kpi-accent: #00897B;
        --kpi-icon-bg: rgba(0, 137, 123, 0.12);
        --kpi-glow: rgba(0, 137, 123, 0.15);
    }
</style>

<div id="main-wrapper">
    <div class="content-body default-height unified-dash">
        <div class="container-fluid">

            <?php include 'institute_banner.php'; ?>

            <?php
            $adminDisplayName = '';
            if (!empty($_SESSION['full_name'])) {
                $adminDisplayName = $_SESSION['full_name'];
            } elseif (!empty($_SESSION['name'])) {
                $adminDisplayName = $_SESSION['name'];
            } else {
                $userId = $_SESSION['user_id'] ?? null;
                if ($userId && isset($pdo)) {
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($userRow) {
                            $adminDisplayName = !empty($userRow['full_name']) ? $userRow['full_name'] : ($userRow['name'] ?? '');
                        }
                    } catch (PDOException $e) {}
                }
            }
            if (empty($adminDisplayName)) {
                $email    = $_SESSION['username'] ?? '';
                $namePart = explode('@', $email)[0];
                $namePart = str_replace(['.', '_', '-'], ' ', $namePart);
                $adminDisplayName = ucwords(strtolower($namePart));
            }
            $instituteName = htmlspecialchars(getInstituteFullName($prefix));
            $totalRecords  = $countPublications + $countPatents + $countConferences + $countWebinars + $countInternships + $countReports;
            ?>

            <!-- ── WELCOME HEADER ── -->
            <div class="dash-header-minimal">
                <h2>Welcome, <?= htmlspecialchars($adminDisplayName) ?></h2>
                <p>PAIR Portal Database Registry Directory for <strong><?= $instituteName ?></strong></p>
            </div>

            <?php if ($is_super && $pendingApprovalsCount > 0): ?>
                <div class="alert alert-danger" style="background-color: #fef2f2; border: 1px solid #fee2e2; border-left: 5px solid #ef4444; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 18px; line-height: 1;">🔴</span>
                        <div>
                            <strong style="color: #991b1b; font-size: 14px;">Pending Approvals (<?= $pendingApprovalsCount ?>)</strong>
                            <p style="color: #7f1d1d; font-size: 12.5px; margin: 2px 0 0 0;">There are pending Spoke Admin modification requests that require your review.</p>
                        </div>
                    </div>
                    <a href="approvals.php" class="btn btn-sm btn-danger" style="background-color: #ef4444; border-color: #ef4444; color: #fff; font-weight: 600; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px;">Review Requests</a>
                </div>
            <?php endif; ?>

            <?php if (!$is_super): ?>
                <?php
                $adminPendingCount = getTableCount($pdo, "approval_requests", "status = 'Pending' AND requested_by = '" . $_SESSION['username'] . "'");
                $adminRejectedCount = getTableCount($pdo, "approval_requests", "status = 'Rejected' AND requested_by = '" . $_SESSION['username'] . "'");
                ?>
                <?php if ($adminPendingCount > 0 || $adminRejectedCount > 0): ?>
                    <div class="alert alert-info" style="background-color: #f0f9ff; border: 1px solid #e0f2fe; border-left: 5px solid #0284c7; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.08);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 18px; line-height: 1;">ℹ️</span>
                            <div>
                                <strong style="color: #075985; font-size: 14px;">Request Status Tracker</strong>
                                <p style="color: #0c4a6e; font-size: 12.5px; margin: 2px 0 0 0;">
                                    You have <strong><?= $adminPendingCount ?></strong> pending and <strong><?= $adminRejectedCount ?></strong> rejected requests.
                                </p>
                            </div>
                        </div>
                        <a href="approvals.php" class="btn btn-sm btn-info" style="background-color: #0284c7; border-color: #0284c7; color: #fff; font-weight: 600; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px;">View Statuses</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- ── SECTION 1: Registry Data ── -->
            <div class="sec-header">
                <div class="sec-line-left"></div>
                <h5 class="sec-title">Institutional Registry Data</h5>
                <div class="sec-line-right"></div>
            </div>
            
            <div class="kpi-grid">
                <!-- 1. Publications (#1E88C7) -->
                <a href="<?= $navUrl('publications.php') ?>" class="kpi-card kpi-publications">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1E88C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countPublications ?></div>
                        <div class="kpi-title">Publications</div>
                    </div>
                </a>

                <!-- 2. Patents (#00897B) -->
                <a href="<?= $navUrl('patents.php') ?>" class="kpi-card kpi-patents">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00897B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="6"/>
                                <path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countPatents ?></div>
                        <div class="kpi-title">Patents</div>
                    </div>
                </a>

                <!-- 3. Conferences (#7E57C2) -->
                <a href="<?= $navUrl('conferences.php') ?>" class="kpi-card kpi-conferences">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7E57C2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countConferences ?></div>
                        <div class="kpi-title">Conferences</div>
                    </div>
                </a>

                <!-- 4. Webinars (#F0932B) -->
                <a href="<?= $navUrl('webinars.php') ?>" class="kpi-card kpi-webinars">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F0932B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="23 7 16 12 23 17 23 7"/>
                                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countWebinars ?></div>
                        <div class="kpi-title">Webinars</div>
                    </div>
                </a>

                <!-- 5. Internships (#1E88C7) -->
                <a href="<?= $navUrl('internships.php') ?>" class="kpi-card kpi-internships">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1E88C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countInternships ?></div>
                        <div class="kpi-title">Internships</div>
                    </div>
                </a>

                <!-- 6. Progress Reports (#FF8FAB) -->
                <a href="<?= $navUrl('progress_reports.php') ?>" class="kpi-card kpi-reports">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF8FAB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                                <polyline points="17 6 23 6 23 12"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countReports ?></div>
                        <div class="kpi-title">Progress Reports</div>
                    </div>
                </a>

                <!-- 7. Gallery & Albums (#F0932B) -->
                <a href="<?= $navUrl('gallery.php') ?>" class="kpi-card kpi-gallery">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F0932B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countGallery ?></div>
                        <div class="kpi-title">Gallery &amp; Albums</div>
                    </div>
                </a>

                <!-- 8. Calendar Events (#7E57C2) -->
                <a href="<?= $navUrl('event_calendar.php') ?>" class="kpi-card kpi-calendar">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7E57C2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countEvents ?></div>
                        <div class="kpi-title">Calendar Events</div>
                    </div>
                </a>
            </div>

            <!-- ── SECTION 2: Homepage & Global CMS ── -->
            <div class="sec-header" style="margin-top: 10px;">
                <div class="sec-line-left"></div>
                <h5 class="sec-title">Homepage &amp; Global CMS Modules</h5>
                <div class="sec-line-right"></div>
            </div>
            
            <div class="kpi-grid">
                <?php if ($is_super): ?>
                <a href="<?= $navUrl('banner_management.php') ?>" class="kpi-card kpi-banners">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7E57C2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countBanners ?></div>
                        <div class="kpi-title">Active Banners</div>
                    </div>
                </a>

                <a href="<?= $navUrl('announcements_management.php') ?>" class="kpi-card kpi-tickers">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00897B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 12A10 10 0 0 0 12 2v10z"/>
                                <path d="M6 12a6 6 0 1 1 12 0"/>
                                <path d="M12 2v20"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countAnnouncements ?></div>
                        <div class="kpi-title">Scrolling Tickers</div>
                    </div>
                </a>

                <a href="<?= $navUrl('team_management.php') ?>" class="kpi-card kpi-team">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F0932B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countTeam ?></div>
                        <div class="kpi-title">Team Members</div>
                    </div>
                </a>
                <?php endif; ?>

                <a href="<?= $navUrl('collaborations_management.php') ?>" class="kpi-card kpi-collabs">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1E88C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 15h2a2 2 0 1 0 0-4h-3c-.6 0-1.1.2-1.4.6L3 17"/>
                                <path d="m7 21 1.6-1.6c.4-.4.6-.9.6-1.4v-3"/>
                                <path d="m14 11 4.4-4.4c.4-.4.9-.6 1.4-.6h2"/>
                                <path d="M18 3v4"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countCollaborations ?></div>
                        <div class="kpi-title">Partners &amp; Collabs</div>
                    </div>
                </a>

                <?php if ($is_super): ?>
                <a href="<?= $navUrl('research_infrastructure.php') ?>" class="kpi-card kpi-research">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00897B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55A2 2 0 0 0 6.5 23.5h11a2 2 0 0 0 1.78-2.95l-5.069-10.127A2 2 0 0 1 14 9.527V2"/>
                                <line x1="8.5" y1="2" x2="15.5" y2="2"/>
                                <line x1="7" y1="16" x2="17" y2="16"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countResearch ?></div>
                        <div class="kpi-title">Research Areas</div>
                    </div>
                </a>

                <a href="<?= $navUrl('research_infrastructure.php?tab=infrastructure') ?>" class="kpi-card kpi-facilities">
                    <div class="kpi-card-header">
                        <div class="kpi-icon-container">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00897B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 18h8"/>
                                <path d="M3 22h18"/>
                                <path d="M14 22a7 7 0 1 0-14 0"/>
                                <path d="M9 14h2"/>
                                <path d="M9 12a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v2"/>
                                <path d="M12 6V3"/>
                            </svg>
                        </div>
                        <span class="kpi-link">
                            View Details
                            <svg class="kpi-link-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </span>
                    </div>
                    <div class="kpi-body">
                        <div class="kpi-number"><?= $countInfrastructure ?></div>
                        <div class="kpi-title">Facilities</div>
                    </div>
                </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

        </div>
    </div>
</div>

<!-- Template Scripts for Sidebar & MetisMenu Dropdowns -->
<script src="vendor/global/global.min.js"></script>
<script src="vendor/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="js/custom.min.js"></script>
<script src="js/dlabnav-init.js"></script>

<?php include 'footer.php'; ?>
