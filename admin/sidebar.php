<?php
if (!isset($GLOBALS['__role_access_loaded'])) { require_once 'role_access.php'; $GLOBALS['__role_access_loaded'] = true; }
$__isSuper = isSuperAdmin();
$__brandPrefix = $__isSuper ? 'uoh' : resolveAdminPrefix();
$__brandName = $__isSuper ? 'ANRF-PAIR Portal' : getInstituteFullName($__brandPrefix);
$__brandLogo = $__isSuper ? 'logo/logo.png' : getInstituteLogo($__brandPrefix);

$currentPage = basename($_SERVER['PHP_SELF']);
$kpiActive = in_array($currentPage, [
    'publications.php',
    'patents.php',
    'conferences.php',
    'webinars.php',
    'internships.php',
    'progress_reports.php'
]);
$pagesActive = isSuperAdmin() && in_array($currentPage, [
    'collaborations_management.php',
    'research_infrastructure.php',
    'gallery_albums_management.php',
    'gallery.php',
    'event_calendar.php',
    'banner_management.php',
    'announcements_management.php',
    'team_management.php',
    'manage_admins.php'
]);
?>

<!--**********************************
    Nav header start
***********************************-->
<!-- Logo + name reflect global brand for Super Admin / specific institute for regular Admin -->
<div class="nav-header" style="background-color: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); z-index: 999;">
    <a href="publications.php" class="brand-logo">
        <img src="<?= htmlspecialchars($__brandLogo) ?>" alt="<?= htmlspecialchars($__brandName) ?> Logo" class="logo-img" style="border-radius: 4px; object-fit: contain; background: #fff; padding: 2px;">
        <span class="brand-institute-name"><?= htmlspecialchars($__brandName) ?></span>
    </a>
    <div class="nav-control">
        <div class="hamburger">
            <span class="line"></span><span class="line"></span><span class="line"></span>
        </div>
    </div>
</div>
<!--**********************************
    Nav header end
***********************************-->

<style>
/* --- Global & Header Updates --- */
.brand-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 100%;
    overflow: hidden;
}
.logo-img {
    max-height: 45px;
    width: auto;
    flex-shrink: 0;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.logo-img:hover {
    transform: scale(1.03);
}
.brand-institute-name {
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    color: #bc2121;
    white-space: normal;
    overflow: visible;
    word-break: break-word;
}

/* --- Sidebar Base --- */
.custom-sidebar {
    background: #7a0e0e;
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
}
.custom-sidebar ul.metismenu {
    padding: 15px 10px;
    list-style: none;
}
.custom-sidebar ul.metismenu li {
    color: #ffffff;
    margin-bottom: 4px;
}

/* --- Top-Level Parent Links --- */
.custom-sidebar ul.metismenu > li > a {
    display: flex;
    align-items: center;
    padding: 11px 16px;
    color: #cbd5e1 !important;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.25s ease;
    font-weight: 600;
    font-size: 13.5px;
    letter-spacing: 0.01em;
}
.custom-sidebar ul.metismenu > li > a:hover {
    background: rgba(255, 255, 255, 0.09);
    color: #ffffff !important;
    transform: translateX(3px);
}
.custom-sidebar ul.metismenu > li > a i {
    font-size: 1.05rem;
    margin-right: 11px;
    width: 22px;
    text-align: center;
    flex-shrink: 0;
    transition: color 0.25s ease;
}
.custom-sidebar ul.metismenu > li > a:hover i {
    color: #886cc0 !important;
}

/* --- Parent Active State --- */
.custom-sidebar ul.metismenu li.mm-active > a {
    background: linear-gradient(135deg, rgba(136,108,192,0.28) 0%, rgba(170,108,192,0.18) 100%) !important;
    color: #ffffff !important;
    border-left: 3px solid #886cc0;
    padding-left: 13px !important;
    position: relative;
    overflow: hidden;
}
.custom-sidebar ul.metismenu li.mm-active > a::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -60%;
    width: 30%;
    height: 200%;
    background: linear-gradient(
        to right,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,0.12) 50%,
        rgba(255,255,255,0) 100%
    );
    transform: rotate(25deg);
    animation: menuShine 5s infinite ease-in-out;
}
@keyframes menuShine {
    0%   { left: -60%; }
    15%  { left: 140%; }
    100% { left: 140%; }
}

/* ═══════════════════════════════════════════════════════
   DROPDOWN PARENT ROW (nav-group-toggle / has-arrow)
═══════════════════════════════════════════════════════ */
.custom-sidebar ul.metismenu li.nav-group > a.nav-group-toggle {
    cursor: pointer;
}

/* Hide the template's built-in right-arrow pseudo-element entirely */
.custom-sidebar .has-arrow::after {
    display: none !important;
}

/* Chevron: only the explicit .nav-arrow i tag is visible */
.custom-sidebar a.nav-group-toggle .nav-arrow {
    font-size: 0.65rem !important;
    opacity: 0.7;
    transition: transform 0.25s ease, opacity 0.25s ease;
    flex-shrink: 0;
    margin-left: auto !important;
}
.custom-sidebar a.nav-group-toggle:hover .nav-arrow {
    opacity: 1;
}

/* ═══════════════════════════════════════════════════════
   CHILD SUBMENU LIST  (.nav-group-sub)
═══════════════════════════════════════════════════════ */
.nav-group-sub {
    /* Vertical guide line on the left */
    border-left: 2px solid rgba(255,255,255,0.12) !important;
    margin-left: 20px !important;
    margin-top: 4px !important;
    margin-bottom: 4px !important;
    padding: 2px 0 2px 0 !important;
    list-style: none !important;
}

/* --- Child Item Links --- */
.nav-group-sub li {
    margin-bottom: 2px !important;
}
.nav-group-sub li a {
    display: flex !important;
    align-items: center !important;
    padding: 8px 12px 8px 16px !important;
    color: #94a3b8 !important;         /* Lighter than parent */
    font-weight: 400 !important;       /* Lighter than parent's 600 */
    font-size: 12.5px !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    transition: all 0.22s ease !important;
    background: transparent !important;
    border-left: 2px solid transparent !important;
    transform: none !important;
}
/* Child icon: slightly smaller */
.nav-group-sub li a i {
    font-size: 0.82rem !important;
    margin-right: 9px !important;
    width: 18px !important;
    flex-shrink: 0 !important;
    opacity: 0.75;
    transition: opacity 0.22s ease, color 0.22s ease !important;
}

/* --- Child Hover State --- */
.nav-group-sub li a:hover {
    background: rgba(255,255,255,0.07) !important;
    color: #e2e8f0 !important;
    border-left-color: rgba(136,108,192,0.5) !important;
    transform: translateX(2px) !important;
}
.nav-group-sub li a:hover i {
    opacity: 1;
    color: #886cc0 !important;
}

/* --- Active Child Item --- */
.nav-group-sub li.mm-active > a,
.nav-group-sub li.mm-active > a:hover {
    background: rgba(136,108,192,0.18) !important;
    color: #ffffff !important;
    border-left-color: #886cc0 !important;
    font-weight: 600 !important;
    transform: none !important;
}
.nav-group-sub li.mm-active > a i {
    opacity: 1;
    color: #886cc0 !important;
}

/* --- Logout Button --- */
.custom-sidebar .sidebar-footer {
    background: rgba(0,0,0,0.25);
}
.custom-sidebar ul.metismenu li a.logout-btn {
    color: #ffffff !important;
}
.custom-sidebar ul.metismenu li a.logout-btn:hover {
    background: rgba(239,68,68,0.15) !important;
    color: #f5f5f5 !important;
}
.custom-sidebar ul.metismenu li a.logout-btn i {
    color: #ffffff !important;
}

/* --- Portal Badge with Gradient Shine --- */
.portal-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0f3a72 0%, #1e40af 100%);
    color: #ffffff;
    font-weight: 800;
    letter-spacing: 0.04em;
    white-space: nowrap;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}
.portal-badge::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -60%;
    width: 30%;
    height: 200%;
    background: linear-gradient(
        to right,
        rgba(255,255,255,0) 0%,
        rgba(255,255,255,0.3) 50%,
        rgba(255,255,255,0) 100%
    );
    transform: rotate(25deg);
    animation: badgeShine 4s infinite ease-in-out;
}
@keyframes badgeShine {
    0%   { left: -60%; }
    20%  { left: 140%; }
    100% { left: 140%; }
}

/* ═══════════════════════════════════════════════════════
   HAMBURGER  →  Clean ☰ three-bar icon
   Override the template's arrow/chevron default shape.
═══════════════════════════════════════════════════════ */

/* Container: flex-center the button */
.nav-header .nav-control {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* The clickable wrapper */
.nav-header .nav-control .hamburger {
    display: inline-flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: flex-start !important;
    gap: 5px !important;           /* even spacing between bars */
    width: 26px !important;
    height: 26px !important;
    cursor: pointer !important;
    padding: 2px 0 !important;
    border-radius: 4px !important;
    transition: background 0.2s ease !important;
}
.nav-header .nav-control .hamburger:hover {
    background: rgba(0, 0, 0, 0.06) !important;
}

/* All three bars: uniform width, same height */
.nav-header .nav-control .hamburger .line {
    display: block !important;
    width: 22px !important;         /* equal width for all three */
    height: 2px !important;
    border-radius: 2px !important;
    background: #6B7280 !important; /* neutral gray matching header tone */
    margin: 0 !important;
    transition: transform 0.28s ease, opacity 0.28s ease, width 0.28s ease !important;
    transform-origin: center !important;
}

/* Remove the template's short-3rd-bar arrow shape */
.nav-header .nav-control .hamburger .line:nth-child(1),
.nav-header .nav-control .hamburger .line:nth-child(2),
.nav-header .nav-control .hamburger .line:nth-child(3) {
    width: 22px !important;
    transform: none !important;
}

/* ── is-active state: morph into ✕ ── */
.nav-header .nav-control .hamburger.is-active .line:nth-child(1) {
    transform: translateY(7px) rotate(45deg) !important;
    width: 22px !important;
}
.nav-header .nav-control .hamburger.is-active .line:nth-child(2) {
    opacity: 0 !important;
    width: 0 !important;
}
.nav-header .nav-control .hamburger.is-active .line:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg) !important;
    width: 22px !important;
}
</style>

<!--**********************************
    Sidebar start
***********************************-->
<!-- Added 'custom-sidebar' class for custom stylings -->
<div class="dlabnav custom-sidebar">
    <div class="dlabnav-scroll" style="display: flex; flex-direction: column; height: 100%;">
        <div style="padding: 12px 18px 10px; color: #fff; font-size: 12px; opacity: 0.95;">
            <div class="portal-badge">
                <i class="fas <?= isSuperAdmin() ? 'fa-shield-alt' : 'fa-user-shield' ?>" style="color:#ffffff;"></i>
                <span><?= isSuperAdmin() ? '🛡️ SUPER ADMIN PORTAL' : '👤 ADMIN PORTAL' ?></span>
            </div>
        </div>
        <!-- Main Navigation Links -->
        <ul class="metismenu" id="menu" style="flex: 1;">
            <li class="<?= ($currentPage === 'dashboard.php') ? 'mm-active' : '' ?>">
                <a href="dashboard.php">
                    <i class="fas fa-th-large"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- KPI Dropdown Group -->
            <li class="nav-group <?= $kpiActive ? 'mm-active' : '' ?>" id="nav-kpi-group">
                <a href="javascript:void(0)" class="nav-group-toggle has-arrow" onclick="toggleNavGroup('kpi-sub')">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">KPI</span>
                    <i class="fas fa-chevron-down nav-arrow" id="kpi-arrow" style="margin-left:auto; font-size:0.7rem;"></i>
                </a>
                <ul class="nav-group-sub" id="kpi-sub" style="display:none; list-style:none; padding: 4px 0 4px 20px;">
                    <li class="<?= ($currentPage === 'publications.php') ? 'mm-active' : '' ?>">
                        <a href="publications.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-book-open" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Publications</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'patents.php') ? 'mm-active' : '' ?>">
                        <a href="patents.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-certificate" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Patents</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'conferences.php') ? 'mm-active' : '' ?>">
                        <a href="conferences.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-users" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Conferences</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'webinars.php') ? 'mm-active' : '' ?>">
                        <a href="webinars.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-video" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Webinars</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'internships.php') ? 'mm-active' : '' ?>">
                        <a href="internships.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-user-graduate" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Internships</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'progress_reports.php') ? 'mm-active' : '' ?>">
                        <a href="progress_reports.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-chart-line" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Progress Reports</span>
                        </a>
                    </li>

                </ul>
            </li>

            <?php /* ── Pages: Super Admin ONLY — rendered server-side, not CSS-hidden ── */ ?>
            <?php if (isSuperAdmin()): ?>
            <!-- Pages Dropdown Group -->
            <li class="nav-group <?= $pagesActive ? 'mm-active' : '' ?>" id="nav-pages-group">
                <a href="javascript:void(0)" class="nav-group-toggle has-arrow" onclick="toggleNavGroup('pages-sub')">
                    <i class="fas fa-copy"></i>
                    <span class="nav-text">Pages</span>
                    <i class="fas fa-chevron-down nav-arrow" id="pages-arrow" style="margin-left:auto; font-size:0.7rem;"></i>
                </a>
                <ul class="nav-group-sub" id="pages-sub" style="display:none; list-style:none; padding: 4px 0 4px 20px;">
                    <li class="<?= ($currentPage === 'collaborations_management.php') ? 'mm-active' : '' ?>">
                        <a href="collaborations_management.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-handshake" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Collaborations</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'research_infrastructure.php') ? 'mm-active' : '' ?>">
                        <a href="research_infrastructure.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-flask" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Research &amp; Infrastructure</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'gallery_albums_management.php') ? 'mm-active' : '' ?>">
                        <a href="gallery_albums_management.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-images" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Gallery Albums</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'gallery.php') ? 'mm-active' : '' ?>">
                        <a href="gallery.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-link" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Drive Event Links</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'event_calendar.php') ? 'mm-active' : '' ?>">
                        <a href="event_calendar.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-calendar-alt" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Event Calendar</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'banner_management.php') ? 'mm-active' : '' ?>">
                        <a href="banner_management.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-image" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Homepage Banners</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'announcements_management.php') ? 'mm-active' : '' ?>">
                        <a href="announcements_management.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-bullhorn" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Scrolling Ticker</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'team_management.php') ? 'mm-active' : '' ?>">
                        <a href="team_management.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-user-cog" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Team Management</span>
                        </a>
                    </li>
                    <li class="<?= ($currentPage === 'manage_admins.php') ? 'mm-active' : '' ?>">
                        <a href="manage_admins.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-users-cog" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Manage Admins</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; /* isSuperAdmin() — Pages group */ ?>
        </ul>

        <script>
        function toggleNavGroup(subId) {
            var sub = document.getElementById(subId);
            var arrowId = subId.replace('-sub', '-arrow');
            var arrow = document.getElementById(arrowId);
            if (!sub) return;
            var isOpen = sub.style.display === 'block';
            // Close all open groups first
            document.querySelectorAll('.nav-group-sub').forEach(function(el) {
                el.style.display = 'none';
            });
            document.querySelectorAll('.nav-arrow').forEach(function(el) {
                el.style.transform = '';
            });
            // Toggle clicked group
            if (!isOpen) {
                sub.style.display = 'block';
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        }
        // Auto-open the group that contains the active page on load
        document.addEventListener('DOMContentLoaded', function() {
            var currentPage = window.location.pathname.split('/').pop();
            
            var kpiPages = [
                'publications.php',
                'patents.php',
                'conferences.php',
                'webinars.php',
                'internships.php',
                'progress_reports.php'
            ];
            
            var pagesPages = [
                'collaborations_management.php',
                'research_infrastructure.php',
                'gallery_albums_management.php',
                'gallery.php',
                'event_calendar.php',
                'banner_management.php',
                'announcements_management.php',
                'team_management.php',
                'manage_admins.php'
            ];
            
            if (kpiPages.indexOf(currentPage) !== -1) {
                toggleNavGroup('kpi-sub');
            } else if (pagesPages.indexOf(currentPage) !== -1) {
                toggleNavGroup('pages-sub');
            }
        });
        </script>

        <!-- Logout Button Section -->
        <div class="sidebar-footer" style="padding: 10px; border-top: 1px solid rgba(255,255,255,0.08);">
            <ul class="metismenu" style="padding: 0;">
                <li>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!--**********************************
    Sidebar end
***********************************-->