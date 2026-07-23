<?php
require_once 'auth_check.php';
require_once 'role_access.php';

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
        return 0;
    }
}

// Fetch counts
$is_super = isSuperAdmin();

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

$pageTitle = "Dashboard | ANRF-PAIR Portal";
?>
<?php include 'nav_header.php'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'loader.php'; ?>

<style>
    /* ══════════════════════════════════════════════════════
       UNIFIED SINGLE-COLOR (CRIMSON) CARD THEME
       Clean, professional, and matching the portal branding.
    ══════════════════════════════════════════════════════ */
    
    .unified-dash {
        padding-bottom: 40px;
        background-color: #f8fafc;
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* ── Header Welcome Style ── */
    .dash-header-minimal {
        padding: 12px 0 20px 0;
        border-bottom: 2px solid #bc2121;
        margin-bottom: 28px;
    }
    .dash-header-minimal h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 6px 0;
    }
    .dash-header-minimal p {
        font-size: 13.5px;
        color: #64748b;
        margin: 0;
    }
    .dash-header-minimal strong {
        color: #bc2121;
    }

    /* ── Section Dividers ── */
    .sec-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        margin-top: 10px;
    }
    .sec-line-left {
        width: 4px;
        height: 16px;
        background: #bc2121;
        border-radius: 2px;
    }
    .sec-title {
        font-size: 13px;
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

    /* ── Unified Crimson Cards ── */
    .unified-card {
        background: #bc2121; /* Single color theme matching the portal */
        border: 1px solid #bc2121;
        border-radius: 6px;
        padding: 22px 20px;
        margin-bottom: 20px;
        display: block;
        text-decoration: none;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 12px rgba(188, 33, 33, 0.15);
    }
    .unified-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(188, 33, 33, 0.25);
        color: #ffffff !important;
        text-decoration: none;
    }
    .unified-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .unified-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .unified-card-arrow {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
        transition: transform 0.2s;
    }
    .unified-card:hover .unified-card-arrow {
        color: #ffffff;
        transform: translateX(4px);
    }
    .unified-card-num {
        font-size: 32px;
        font-weight: 800;
        color: #ffffff;
        line-height: 1;
        letter-spacing: -0.5px;
    }
    .unified-card-label {
        font-size: 11.5px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.85);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 6px;
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

            <!-- ── SECTION 1: Registry Data ── -->
            <div class="sec-header">
                <div class="sec-line-left"></div>
                <h5 class="sec-title">Institutional Registry Data</h5>
                <div class="sec-line-right"></div>
            </div>
            
            <div class="row">
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="publications.php?prefix=<?= $prefix ?>" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-book-open"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countPublications ?></div>
                        <div class="unified-card-label">Publications</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="patents.php?prefix=<?= $prefix ?>" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-certificate"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countPatents ?></div>
                        <div class="unified-card-label">Patents</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="conferences.php?prefix=<?= $prefix ?>" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-users"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countConferences ?></div>
                        <div class="unified-card-label">Conferences</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="webinars.php?prefix=<?= $prefix ?>" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-video"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countWebinars ?></div>
                        <div class="unified-card-label">Webinars</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="internships.php?prefix=<?= $prefix ?>" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-user-graduate"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countInternships ?></div>
                        <div class="unified-card-label">Internships</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="progress_reports.php?prefix=<?= $prefix ?>" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-chart-line"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countReports ?></div>
                        <div class="unified-card-label">Progress Reports</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="gallery.php?prefix=<?= $prefix ?>" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-images"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countGallery ?></div>
                        <div class="unified-card-label">Gallery &amp; Albums</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="event_calendar.php" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-calendar-alt"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countEvents ?></div>
                        <div class="unified-card-label">Calendar Events</div>
                    </a>
                </div>
            </div>

            <!-- ── SECTION 2: Homepage & Global CMS ── -->
            <div class="sec-header" style="margin-top: 14px;">
                <div class="sec-line-left"></div>
                <h5 class="sec-title">Homepage &amp; Global CMS Modules</h5>
                <div class="sec-line-right"></div>
            </div>
            
            <div class="row">
                <?php if ($is_super): ?>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="banner_management.php" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-image"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countBanners ?></div>
                        <div class="unified-card-label">Active Banners</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="announcements_management.php" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-bullhorn"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countAnnouncements ?></div>
                        <div class="unified-card-label">Scrolling Tickers</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="team_management.php" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-user-friends"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countTeam ?></div>
                        <div class="unified-card-label">Team Members</div>
                    </a>
                </div>
                <?php endif; ?>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="collaborations_management.php" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-handshake"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countCollaborations ?></div>
                        <div class="unified-card-label">Partners &amp; Collabs</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="research_infrastructure.php" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-flask"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countResearch ?></div>
                        <div class="unified-card-label">Research Areas</div>
                    </a>
                </div>
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <a href="research_infrastructure.php?tab=infrastructure" class="unified-card">
                        <div class="unified-card-header">
                            <div class="unified-card-icon"><i class="fas fa-microscope"></i></div>
                            <i class="fas fa-chevron-right unified-card-arrow"></i>
                        </div>
                        <div class="unified-card-num"><?= $countInfrastructure ?></div>
                        <div class="unified-card-label">Facilities</div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
