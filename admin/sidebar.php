<?php
if (!isset($GLOBALS['__role_access_loaded'])) { require_once 'role_access.php'; $GLOBALS['__role_access_loaded'] = true; }
$__isSuper = isSuperAdmin();
$__activePrefix = resolveAdminPrefix();
$__brandPrefix = $__activePrefix;
$__brandName = getInstituteFullName($__brandPrefix);
$__brandLogo = getInstituteLogo($__brandPrefix);

$currentPage = basename($_SERVER['PHP_SELF']);

// Helper to preserve active institute prefix and tab_token for navigation links
$navUrl = 'buildNavUrl';


$__pendingCount = 0;
try {
    require_once 'config/db.php';
    if ($__isSuper) {
        $__stmt = $pdo->query("SELECT COUNT(*) FROM `approval_requests` WHERE `status` = 'Pending'");
    } else {
        $__stmt = $pdo->prepare("SELECT COUNT(*) FROM `approval_requests` WHERE `status` = 'Pending' AND `requested_by` = ?");
        $__stmt->execute([$_SESSION['username']]);
    }
    $__pendingCount = (int)($__stmt->fetchColumn() ?: 0);
} catch (Exception $e) {
    // count defaults to 0
}
$kpiActive = in_array($currentPage, [
    'publications.php',
    'patents.php',
    'conferences.php',
    'webinars.php',
    'internships.php',
    'progress_reports.php',
    'collaborations_management.php',
    'research_infrastructure.php',
    'sheets.php'
]);
$pagesActive = isSuperAdmin() && in_array($currentPage, [
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
<!-- Logo reflects specific logged-in institute with full clarity and full name -->
<div class="nav-header" style="background-color: #ffffff; box-shadow: none; z-index: 999;">
    <a href="<?= $navUrl('publications.php') ?>" class="brand-logo" title="<?= htmlspecialchars($__brandName) ?>">
        <img src="<?= htmlspecialchars($__brandLogo) ?>" alt="<?= htmlspecialchars($__brandName) ?> Logo" class="logo-img <?= ($__brandPrefix === 'uoh') ? 'logo-img-banner' : 'logo-img-emblem' ?>">
        <?php if ($__brandPrefix !== 'uoh'): ?>
        <span class="brand-institute-name"><?= htmlspecialchars($__brandName) ?></span>
        <?php endif; ?>
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
/* --- Active Pagination Background --- */
.page-item.active .page-link,
.pagination-theme-sapphire .page-item.active .page-link,
.pagination .page-item.active .page-link {
    background-color: #bc2121 !important;
    border-color: #bc2121 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(188, 33, 33, 0.3) !important;
}
.pagination-theme-sapphire .page-link,
.pagination .page-link {
    color: #bc2121 !important;
}

/* --- Strict Vector & Icon Quality Optimization (Admin) --- */
.custom-sidebar .metismenu i,
.custom-sidebar .metismenu .nav-arrow,
.btn i,
.table i,
.card i,
.badge i,
.modal i,
i.fas, i.far, i.fab, i.fa {
    -webkit-font-smoothing: antialiased !important;
    -moz-osx-font-smoothing: grayscale !important;
    text-rendering: optimizeLegibility !important;
    font-style: normal !important;
    transform: translateZ(0);
    backface-visibility: hidden;
}

/* Crisp rendering for admin logos & images */
.brand-logo img,
.header-profile img,
.logo-img {
    image-rendering: -webkit-optimize-contrast !important;
    image-rendering: crisp-edges !important;
    transform: translateZ(0);
    backface-visibility: hidden;
}

/* Alignment for table action buttons & icons */
.btn i, .btn span {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    vertical-align: middle !important;
}

/* --- SELECTION MODULE BLUE THEME (#0856A4) --- */
:root {
    --select-module-blue: #0856A4;
    --select-module-blue-hover: #064482;
    --select-module-blue-light: #eff6ff;
    --select-module-blue-border: #bfdbfe;
}

/* Modal Blue Buttons (Search & Add Selected inside opened modal) */
.btn-select-blue {
    background-color: #0856A4 !important;
    border-color: #0856A4 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    transition: all 0.2s ease-in-out !important;
}

.btn-select-blue:hover,
.btn-select-blue:focus,
.btn-select-blue:active {
    background-color: #064482 !important;
    border-color: #064482 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 10px rgba(8, 86, 164, 0.3) !important;
}

/* Modal Header Background */
.kpi-modal-header-blue {
    background: #0856A4 !important;
}

/* Selection counter badge in modal footer (0 selected, 1 selected, etc.) */
#kpiSelectedCountBadge,
.badge-select-count-blue {
    background-color: #0856A4 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    font-size: 0.8rem !important;
    padding: 6px 14px !important;
    border-radius: 6px !important;
    border: none !important;
    box-shadow: 0 2px 6px rgba(8, 86, 164, 0.25) !important;
}

/* Modal selection checkboxes & highlights */
.modal-kpi-checkbox:checked {
    background-color: #0856A4 !important;
    border-color: #0856A4 !important;
    box-shadow: 0 0 0 3px rgba(8, 86, 164, 0.18) !important;
}

.modal-kpi-checkbox:focus {
    border-color: #0856A4 !important;
    box-shadow: 0 0 0 3px rgba(8, 86, 164, 0.18) !important;
}

/* Task badges in candidate lists & selected display boxes */
.modal-task-tag,
.badge-task-blue {
    background-color: #eff6ff !important;
    color: #0856A4 !important;
    border: 1px solid #bfdbfe !important;
    font-weight: 600 !important;
    font-size: 0.72rem !important;
    padding: 2px 8px !important;
    border-radius: 6px !important;
}

/* --- Global & Header Updates --- */
.nav-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 0 12px 0 16px !important;
    height: 70px !important;
    box-sizing: border-box !important;
    background-color: #ffffff !important;
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    filter: none !important;
    z-index: 999 !important;
}

/* --- Remove All Sidebar & Nav-Header Shadows --- */
.dlabnav,
.custom-sidebar,
.deznav,
.nav-header,
[data-sidebar-style],
.dlabnav::before, .dlabnav::after,
.custom-sidebar::before, .custom-sidebar::after,
.deznav::before, .deznav::after,
.nav-header::before, .nav-header::after {
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    filter: none !important;
    -webkit-filter: none !important;
}

.brand-logo {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 10px !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
    overflow: hidden !important;
    padding: 2px 0 !important;
    margin: 0 !important;
    height: 100% !important;
    text-decoration: none !important;
}

.logo-img-banner {
    height: 48px !important;
    max-height: 52px !important;
    width: auto !important;
    max-width: 100% !important;
    flex-shrink: 0 !important;
    object-fit: contain !important;
    border-radius: 0 !important;
    background: transparent !important;
    padding: 0 !important;
    transition: transform 0.3s ease;
}

.logo-img-emblem {
    height: 38px !important;
    width: 38px !important;
    min-width: 38px !important;
    max-height: 42px !important;
    flex-shrink: 0 !important;
    object-fit: contain !important;
    border-radius: 50% !important;
    background: transparent !important;
    padding: 0 !important;
    transition: transform 0.3s ease;
}

.brand-institute-name {
    font-family: 'Poppins', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    line-height: 1.25 !important;
    color: #0f172a !important;
    white-space: normal !important;
    word-break: normal !important;
    overflow-wrap: break-word !important;
    display: inline-block !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
    letter-spacing: -0.2px !important;
}
.nav-header .nav-control {
    flex-shrink: 0 !important;
    margin-left: 6px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 100% !important;
}
.nav-header .hamburger {
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: center !important;
    margin-top: -3px !important; /* Optically levels the hamburger icon with Manage Dashboard title */
}
.menu-toggle .brand-institute-name,
[data-sidebar-style="mini"] .brand-institute-name {
    display: none !important;
}

/* --- Sidebar Base --- */
.custom-sidebar {
    background: #7a0e0e;
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    filter: none !important;
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
    border-left: none !important;
    border: none !important;
    padding-left: 16px !important;
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
    border-left: none !important;
    margin-left: 10px !important;
    margin-top: 4px !important;
    margin-bottom: 4px !important;
    padding: 2px 0 2px 0 !important;
    list-style: none !important;
}

/* Remove vertical guide line & hyphen (-) symbol pseudo-elements completely */
.nav-group-sub::before,
.nav-group-sub::after,
.nav-group-sub li::before,
.nav-group-sub li::after,
.nav-group-sub li a::before,
.nav-group-sub li a::after,
.metismenu ul::before,
.metismenu ul::after,
.metismenu ul a::before,
.metismenu ul a::after,
.metismenu ul li::before,
.metismenu ul li::after,
.deznav .metismenu ul::before,
.deznav .metismenu ul::after,
.deznav .metismenu ul a::before,
.deznav .metismenu ul a::after,
.deznav .metismenu ul li::before,
.deznav .metismenu ul li::after {
    display: none !important;
    content: none !important;
    border: none !important;
    background: none !important;
    width: 0 !important;
    height: 0 !important;
}

/* --- Child Item Links --- */
.nav-group-sub li {
    margin-bottom: 2px !important;
}

/* ──── KPI Sidebar Submenu Single-Color Identities ──── */
.kpi-sub-item-publications {
    --kpi-sub-color: #1E88C7;
    --kpi-sub-hover-bg: rgba(30, 136, 199, 0.15);
    --kpi-sub-active-bg: rgba(30, 136, 199, 0.22);
}

.kpi-sub-item-patents {
    --kpi-sub-color: #00897B;
    --kpi-sub-hover-bg: rgba(0, 137, 123, 0.15);
    --kpi-sub-active-bg: rgba(0, 137, 123, 0.22);
}

.kpi-sub-item-conferences {
    --kpi-sub-color: #7E57C2;
    --kpi-sub-hover-bg: rgba(126, 87, 194, 0.15);
    --kpi-sub-active-bg: rgba(126, 87, 194, 0.22);
}

.kpi-sub-item-webinars {
    --kpi-sub-color: #831843;
    --kpi-sub-hover-bg: rgba(131, 24, 67, 0.15);
    --kpi-sub-active-bg: rgba(131, 24, 67, 0.22);
}

.kpi-sub-item-internships {
    --kpi-sub-color: #1E88C7;
    --kpi-sub-hover-bg: rgba(30, 136, 199, 0.15);
    --kpi-sub-active-bg: rgba(30, 136, 199, 0.22);
}

.kpi-sub-item-reports {
    --kpi-sub-color: #0e7490;
    --kpi-sub-hover-bg: rgba(14, 116, 144, 0.15);
    --kpi-sub-active-bg: rgba(14, 116, 144, 0.22);
}

.kpi-sub-item-research {
    --kpi-sub-color: #00897B;
    --kpi-sub-hover-bg: rgba(0, 137, 123, 0.15);
    --kpi-sub-active-bg: rgba(0, 137, 123, 0.22);
}

.kpi-sub-item-gallery {
    --kpi-sub-color: #F0932B;
    --kpi-sub-hover-bg: rgba(240, 147, 43, 0.15);
    --kpi-sub-active-bg: rgba(240, 147, 43, 0.22);
}

.kpi-sub-item-calendar {
    --kpi-sub-color: #7E57C2;
    --kpi-sub-hover-bg: rgba(126, 87, 194, 0.15);
    --kpi-sub-active-bg: rgba(126, 87, 194, 0.22);
}

.kpi-sub-item-collabs {
    --kpi-sub-color: #1E88C7;
    --kpi-sub-hover-bg: rgba(30, 136, 199, 0.15);
    --kpi-sub-active-bg: rgba(30, 136, 199, 0.22);
}

.kpi-sub-item-banners {
    --kpi-sub-color: #7E57C2;
    --kpi-sub-hover-bg: rgba(126, 87, 194, 0.15);
    --kpi-sub-active-bg: rgba(126, 87, 194, 0.22);
}

.kpi-sub-item-tickers {
    --kpi-sub-color: #00897B;
    --kpi-sub-hover-bg: rgba(0, 137, 123, 0.15);
    --kpi-sub-active-bg: rgba(0, 137, 123, 0.22);
}

.kpi-sub-item-team {
    --kpi-sub-color: #F0932B;
    --kpi-sub-hover-bg: rgba(240, 147, 43, 0.15);
    --kpi-sub-active-bg: rgba(240, 147, 43, 0.22);
}

/* Submenu item link base styling */
.nav-group-sub li.kpi-sub-link a {
    display: flex !important;
    align-items: center !important;
    padding: 9px 14px !important;
    color: var(--kpi-sub-color) !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    transition: all 0.22s ease !important;
    background: transparent !important;
    border-left: none !important;
    border: none !important;
    transform: none !important;
}

.nav-group-sub li.kpi-sub-link a i {
    font-size: 0.95rem !important;
    margin-right: 9px !important;
    width: 20px !important;
    flex-shrink: 0 !important;
    color: var(--kpi-sub-color) !important;
    opacity: 1 !important;
    transition: transform 0.22s ease !important;
}

/* Submenu hover state with 10-15% tint background (NO side line border) */
.nav-group-sub li.kpi-sub-link a:hover {
    background: var(--kpi-sub-hover-bg) !important;
    color: var(--kpi-sub-color) !important;
    border-left: none !important;
    border: none !important;
    transform: translateX(3px) !important;
}

.nav-group-sub li.kpi-sub-link a:hover i {
    transform: scale(1.1) !important;
    color: var(--kpi-sub-color) !important;
}

/* Submenu active state with 15-20% tint background (NO side line border) */
.nav-group-sub li.kpi-sub-link.mm-active > a,
.nav-group-sub li.kpi-sub-link.mm-active > a:hover {
    background: var(--kpi-sub-active-bg) !important;
    color: var(--kpi-sub-color) !important;
    border-left: none !important;
    border: none !important;
    font-weight: 700 !important;
    transform: none !important;
}

.nav-group-sub li.kpi-sub-link.mm-active > a i {
    color: var(--kpi-sub-color) !important;
    opacity: 1 !important;
}

/* Standard fallbacks for non-KPI child links */
.nav-group-sub li:not(.kpi-sub-link) a {
    display: flex !important;
    align-items: center !important;
    padding: 8px 12px 8px 16px !important;
    color: #94a3b8 !important;
    font-weight: 400 !important;
    font-size: 12.5px !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    transition: all 0.22s ease !important;
    background: transparent !important;
    border-left: none !important;
    border: none !important;
}
.nav-group-sub li:not(.kpi-sub-link) a i {
    font-size: 0.82rem !important;
    margin-right: 9px !important;
    width: 18px !important;
    flex-shrink: 0 !important;
    opacity: 0.75;
}
.nav-group-sub li:not(.kpi-sub-link) a:hover {
    background: rgba(255,255,255,0.07) !important;
    color: #e2e8f0 !important;
    border-left: none !important;
    border: none !important;
}
.nav-group-sub li:not(.kpi-sub-link).mm-active > a {
    background: rgba(136,108,192,0.18) !important;
    color: #ffffff !important;
    border-left: none !important;
    border: none !important;
    font-weight: 600 !important;
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

/* Rotate chevron when parent menu item is open/active */
.custom-sidebar li.mm-active > a.nav-group-toggle .nav-arrow {
    transform: rotate(180deg);
}
</style>

<!--**********************************
    Sidebar start
**********************************-->
<!-- Added 'custom-sidebar' class for custom stylings -->
<div class="dlabnav custom-sidebar">
    <div class="dlabnav-scroll" style="display: flex; flex-direction: column; height: 100%;">
        <div style="padding: 12px 18px 10px; color: #fff; font-size: 12px; opacity: 0.95;">
            
        </div>
        <!-- Main Navigation Links -->
        <ul class="metismenu" id="menu" style="flex: 1;">
            <li class="<?= ($currentPage === 'dashboard.php') ? 'mm-active' : '' ?>">
                <a href="<?= $navUrl('dashboard.php') ?>">
                    <i class="fas fa-th-large"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <?php if (isSuperAdmin()): ?>
            <li class="<?= ($currentPage === 'approvals.php') ? 'mm-active' : '' ?>">
                <a href="<?= $navUrl('approvals.php') ?>" style="display: flex; align-items: center;">
                    <i class="fas fa-check-circle"></i>
                    <span class="nav-text">Approvals</span>
                    <?php if ($__pendingCount > 0): ?>
                        <span class="badge badge-danger" style="margin-left: auto; background-color: #ef4444; color: #ffffff; border-radius: 12px; padding: 2px 8px; font-size: 11px; font-weight: 700; line-height: 1;">
                            <?= $__pendingCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>

            <!-- KPI Dropdown Group -->
            <li class="nav-group <?= $kpiActive ? 'mm-active' : '' ?>" id="nav-kpi-group">
                <a href="javascript:void(0)" class="nav-group-toggle has-arrow">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">KPI</span>
                    <i class="fas fa-chevron-down nav-arrow" id="kpi-arrow" style="margin-left:auto; font-size:0.7rem;"></i>
                </a>
                <ul class="nav-group-sub mm-collapse <?= $kpiActive ? 'mm-show' : '' ?>" id="kpi-sub" style="list-style:none; padding: 4px 0 4px 20px;">
                    <li class="kpi-sub-link kpi-sub-item-publications <?= ($currentPage === 'publications.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('publications.php') ?>">
                            <i class="fas fa-book-open"></i>
                            <span class="nav-text">Publications</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-patents <?= ($currentPage === 'patents.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('patents.php') ?>">
                            <i class="fas fa-certificate"></i>
                            <span class="nav-text">Patents</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-conferences <?= ($currentPage === 'conferences.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('conferences.php') ?>">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Conferences</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-webinars <?= ($currentPage === 'webinars.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('webinars.php') ?>">
                            <i class="fas fa-video"></i>
                            <span class="nav-text">Webinars</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-internships <?= ($currentPage === 'internships.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('internships.php') ?>">
                            <i class="fas fa-user-graduate"></i>
                            <span class="nav-text">Internships</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-reports <?= ($currentPage === 'progress_reports.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('progress_reports.php') ?>">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-text">Progress Reports</span>
                        </a>
                    </li>
                    <?php if (isSuperAdmin()): ?>
                    <li class="kpi-sub-link kpi-sub-item-collabs <?= ($currentPage === 'collaborations_management.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('collaborations_management.php') ?>">
                            <i class="fas fa-handshake"></i>
                            <span class="nav-text">Collaborations</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isSuperAdmin()): ?>
                    <li class="kpi-sub-link kpi-sub-item-research <?= ($currentPage === 'research_infrastructure.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('research_infrastructure.php') ?>">
                            <i class="fas fa-flask"></i>
                            <span class="nav-text">Research &amp; Infrastructure</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="kpi-sub-link kpi-sub-item-patents <?= ($currentPage === 'sheets.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('sheets.php') ?>">
                            <i class="fas fa-file-excel"></i>
                            <span class="nav-text">Sheets</span>
                        </a>
                    </li>
                </ul>
            </li>

            <?php /* ── Pages: Super Admin ONLY — rendered server-side, not CSS-hidden ── */ ?>
            <?php if (isSuperAdmin()): ?>
            <!-- Pages Dropdown Group -->
            <li class="nav-group <?= $pagesActive ? 'mm-active' : '' ?>" id="nav-pages-group">
                <a href="javascript:void(0)" class="nav-group-toggle has-arrow">
                    <i class="fas fa-copy"></i>
                    <span class="nav-text">Pages</span>
                    <i class="fas fa-chevron-down nav-arrow" id="pages-arrow" style="margin-left:auto; font-size:0.7rem;"></i>
                </a>
                <ul class="nav-group-sub mm-collapse <?= $pagesActive ? 'mm-show' : '' ?>" id="pages-sub" style="list-style:none; padding: 4px 0 4px 20px;">
                    <li class="kpi-sub-link kpi-sub-item-gallery <?= ($currentPage === 'gallery_albums_management.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('gallery_albums_management.php') ?>">
                            <i class="fas fa-images"></i>
                            <span class="nav-text">Gallery Albums</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-gallery <?= ($currentPage === 'gallery.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('gallery.php') ?>">
                            <i class="fas fa-link"></i>
                            <span class="nav-text">Drive Event Links</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-calendar <?= ($currentPage === 'event_calendar.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('event_calendar.php') ?>">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="nav-text">Event Calendar</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-banners <?= ($currentPage === 'banner_management.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('banner_management.php') ?>">
                            <i class="fas fa-image"></i>
                            <span class="nav-text">Homepage Banners</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-tickers <?= ($currentPage === 'announcements_management.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('announcements_management.php') ?>">
                            <i class="fas fa-bullhorn"></i>
                            <span class="nav-text">Scrolling Ticker</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-team <?= ($currentPage === 'team_management.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('team_management.php') ?>">
                            <i class="fas fa-user-cog"></i>
                            <span class="nav-text">Team Management</span>
                        </a>
                    </li>
                    <li class="kpi-sub-link kpi-sub-item-patents <?= ($currentPage === 'manage_admins.php') ? 'mm-active' : '' ?>">
                        <a href="<?= $navUrl('manage_admins.php') ?>">
                            <i class="fas fa-users-cog"></i>
                            <span class="nav-text">Manage Spoke Admins</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; /* isSuperAdmin() — Pages group */ ?>
        </ul>

        <!-- Logout Button Section -->



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