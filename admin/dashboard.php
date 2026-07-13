<?php
session_start();

require_once 'role_access.php';

// Auth Guard
if (!isset($_SESSION['username']) || !isset($_SESSION['institute_prefix'])) {
    header("Location: ../login.php");
    exit();
}

$prefix = resolveAdminPrefix($_GET['prefix'] ?? null);

if (!isValidPrefix($prefix)) {
    die('Invalid institute configuration. Please contact admin.');
}

require_once 'config/db.php';

// Function to safely query counts
function getTableCount($pdo, $tableName, $whereClause = "") {
    try {
        $sql = "SELECT COUNT(*) FROM `$tableName`" . ($whereClause ? " WHERE $whereClause" : "");
        return (int)$pdo->query($sql)->fetchColumn();
    } catch (PDOException $e) {
        return 0; // Return 0 if table does not exist yet
    }
}

// Fetch counts
$is_super = isSuperAdmin();

$countPublications = getTableCount($pdo, "{$prefix}_publications");
$countPatents = getTableCount($pdo, "{$prefix}_patent");
$countConferences = getTableCount($pdo, "{$prefix}_conferences");
$countWebinars = getTableCount($pdo, "{$prefix}_webinars");
$countInternships = getTableCount($pdo, "{$prefix}_internships");
$countReports = getTableCount($pdo, "{$prefix}_progress_reports");
$countGallery = getTableCount($pdo, "{$prefix}_gallery_events") + getTableCount($pdo, "gallery_albums", "institute_prefix = '$prefix' OR institute_prefix = 'all'");

// Global counts (for super admin mostly, but shown to everyone)
$countTeam = getTableCount($pdo, "team", "status = 'Active'");
$countEvents = getTableCount($pdo, "events", $is_super ? "" : "university_id = '$prefix' OR university_id = 'all'");
$countBanners = getTableCount($pdo, "homepage_banners", "status = 'Active'");
$countAnnouncements = getTableCount($pdo, "announcements", "is_active = 1");
$countCollaborations = getTableCount($pdo, "collaborations", "status = 'Active'");
$countResearch = getTableCount($pdo, "research_areas", "status = 'Active'");
$countInfrastructure = getTableCount($pdo, "infrastructure_facilities", "status = 'Active'");

$pageTitle = "Dashboard | ANRF-PAIR Portal";
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
    .dashboard-title-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }
    .metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-bottom: 24px;
        display: block;
        text-decoration: none;
        color: inherit;
    }
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.06);
        text-decoration: none;
        color: inherit;
        border-color: #bc2121;
    }
    .metric-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: rgba(188, 33, 33, 0.08);
        color: #bc2121;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 15px;
    }
    /* Theme overrides for non-super admins (red style) */
    body.theme-admin .metric-icon-box {
        background: rgba(188, 33, 33, 0.08);
        color: #bc2121;
    }
    body.theme-admin .metric-card:hover {
        border-color: #bc2121;
    }
    .metric-num {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .metric-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }
</style>

<div id="main-wrapper">
    <div class="content-body default-height">
        <div class="container-fluid">
            
            <?php include 'institute_banner.php'; ?>

            <div class="dashboard-title-box">
                <h2 style="font-weight: 700; color: #1e3a8a; margin: 0;">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
                <p style="color: #64748b; margin: 5px 0 0 0; font-size: 14px;">Here is a summary of the dynamic records managed under the ANRF-PAIR Project for **<?= htmlspecialchars(getInstituteFullName($prefix)) ?>**.</p>
            </div>

            <!-- SECTION 1: Academic & Research Records (Scoped by Institute) -->
            <h4 style="font-weight: 700; color: #334155; margin-bottom: 16px; border-left: 4px solid #bc2121; padding-left: 10px;">
                Institutional Registry Data
            </h4>
            <div class="row">
                <div class="col-xl-3 col-sm-6">
                    <a href="publications.php?prefix=<?= $prefix ?>" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-book-open"></i></div>
                        <div class="metric-num"><?= $countPublications ?></div>
                        <div class="metric-label">Publications</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="patents.php?prefix=<?= $prefix ?>" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-certificate"></i></div>
                        <div class="metric-num"><?= $countPatents ?></div>
                        <div class="metric-label">Patents</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="conferences.php?prefix=<?= $prefix ?>" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-users"></i></div>
                        <div class="metric-num"><?= $countConferences ?></div>
                        <div class="metric-label">Conferences</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="webinars.php?prefix=<?= $prefix ?>" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-video"></i></div>
                        <div class="metric-num"><?= $countWebinars ?></div>
                        <div class="metric-label">Webinars</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="internships.php?prefix=<?= $prefix ?>" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-user-graduate"></i></div>
                        <div class="metric-num"><?= $countInternships ?></div>
                        <div class="metric-label">Internships</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="progress_reports.php?prefix=<?= $prefix ?>" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-chart-line"></i></div>
                        <div class="metric-num"><?= $countReports ?></div>
                        <div class="metric-label">Progress Reports</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="gallery.php?prefix=<?= $prefix ?>" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-images"></i></div>
                        <div class="metric-num"><?= $countGallery ?></div>
                        <div class="metric-label">Gallery & Albums</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="event_calendar.php" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-calendar-alt"></i></div>
                        <div class="metric-num"><?= $countEvents ?></div>
                        <div class="metric-label">Calendar Events</div>
                    </a>
                </div>
            </div>

            <!-- SECTION 2: Global Frontend/CMS Modules (Managed by Super Admin) -->
            <h4 style="font-weight: 700; color: #334155; margin-top: 15px; margin-bottom: 16px; border-left: 4px solid #bc2121; padding-left: 10px;">
                Homepage & Global CMS Modules
            </h4>
            <div class="row">
                <?php if ($is_super): ?>
                <div class="col-xl-3 col-sm-6">
                    <a href="banner_management.php" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-image"></i></div>
                        <div class="metric-num"><?= $countBanners ?></div>
                        <div class="metric-label">Active Slider Banners</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="announcements_management.php" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-bullhorn"></i></div>
                        <div class="metric-num"><?= $countAnnouncements ?></div>
                        <div class="metric-label">Scrolling Tickers</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="team_management.php" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-user-friends"></i></div>
                        <div class="metric-num"><?= $countTeam ?></div>
                        <div class="metric-label">Team Members</div>
                    </a>
                </div>
                <?php endif; ?>
                <div class="col-xl-3 col-sm-6">
                    <a href="collaborations_management.php" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-handshake"></i></div>
                        <div class="metric-num"><?= $countCollaborations ?></div>
                        <div class="metric-label">Partners & Collabs</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="research_infrastructure.php" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-flask"></i></div>
                        <div class="metric-num"><?= $countResearch ?></div>
                        <div class="metric-label">Research Areas</div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="research_infrastructure.php?tab=infrastructure" class="metric-card">
                        <div class="metric-icon-box"><i class="fas fa-microscope"></i></div>
                        <div class="metric-num"><?= $countInfrastructure ?></div>
                        <div class="metric-label">Facilities</div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
