<?php
if (!isset($GLOBALS['__role_access_loaded'])) { require_once 'role_access.php'; $GLOBALS['__role_access_loaded'] = true; }
$__isSuper = isSuperAdmin();
$__brandPrefix = $__isSuper ? 'uoh' : resolveAdminPrefix();
$__brandName = $__isSuper ? 'ANRF-PAIR Portal' : getInstituteFullName($__brandPrefix);
$__brandLogo = $__isSuper ? 'logo/logo.png' : getInstituteLogo($__brandPrefix);
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
    transform: scale(1.03); /* Subtle pop when hovering over the logo */
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

/* --- Attractive Sidebar Styling --- */
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
    margin-bottom: 8px;
}

.custom-sidebar ul.metismenu li a {
    display: flex;
    align-items: center;
    padding: 12px 18px;
    color: #94a3b8 !important; /* Soft, readable gray text */
    text-decoration: none;
    border-radius: 8px; /* Smooth rounded corners */
    transition: all 0.3s ease;
    font-weight: 500;
}

/* Hover & Active states */
.custom-sidebar ul.metismenu li a:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff !important;
    transform: translateX(4px); /* Modern nudge effect */
}

/* Icon customizations */
.custom-sidebar ul.metismenu li a i {
    font-size: 1.1rem;
    margin-right: 12px;
    width: 25px;
    text-align: center;
    transition: color 0.3s ease;
}

.custom-sidebar ul.metismenu li a:hover i {
    color: #886cc0 !important; /* Electric blue accent color on hover */
}

/* --- Logout Button Custom Styling --- */
.custom-sidebar .sidebar-footer {
    background: rgba(0,0,0,0.25);
}

.custom-sidebar ul.metismenu li a.logout-btn {
    color: #ffffff !important; /* Clean pastel red */
}

.custom-sidebar ul.metismenu li a.logout-btn:hover {
    background: rgba(239, 68, 68, 0.15); /* Soft red background tint */
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
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.3) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    transform: rotate(25deg);
    animation: badgeShine 4s infinite ease-in-out;
}

@keyframes badgeShine {
    0% { left: -60%; }
    20% { left: 140%; }
    100% { left: 140%; }
}

/* --- Sidebar Active Link with Gradient Shine --- */
.custom-sidebar ul.metismenu li.mm-active > a {
    background: linear-gradient(135deg, rgba(136, 108, 192, 0.25) 0%, rgba(170, 108, 192, 0.15) 100%) !important;
    color: #ffffff !important;
    border-left: 4px solid var(--primary);
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
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.15) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    transform: rotate(25deg);
    animation: menuShine 5s infinite ease-in-out;
}

@keyframes menuShine {
    0% { left: -60%; }
    15% { left: 140%; }
    100% { left: 140%; }
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
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-th-large"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="publications.php">
                    <i class="fas fa-book-open"></i>
                    <span class="nav-text">Publications</span>
                </a>
            </li>
            <li>
                <a href="patents.php">
                    <i class="fas fa-certificate"></i>
                    <span class="nav-text">Patents</span>
                </a>
            </li>
            <li>
                <a href="conferences.php">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Conferences</span>
                </a>
            </li>
            <li>
                <a href="webinars.php">
                    <i class="fas fa-video"></i>
                    <span class="nav-text">Webinars</span>
                </a>
            </li>
            <li>
                <a href="internships.php">
                    <i class="fas fa-user-graduate"></i>
                    <span class="nav-text">Internships</span>
                </a>
            </li>
            <li>
                <a href="progress_reports.php">
                    <i class="fas fa-chart-line"></i>
                    <span class="nav-text">Progress Reports</span>
                </a>
            </li>

            <!-- Gallery Dropdown Group -->
            <li class="nav-group" id="nav-gallery-group">
                <a href="javascript:void(0)" class="nav-group-toggle has-arrow" onclick="toggleNavGroup('gallery-sub')">
                    <i class="fas fa-photo-video"></i>
                    <span class="nav-text">Gallery</span>
                    <i class="fas fa-chevron-down nav-arrow" id="gallery-arrow" style="margin-left:auto; font-size:0.7rem;"></i>
                </a>
                <ul class="nav-group-sub" id="gallery-sub" style="display:none; list-style:none; padding: 4px 0 4px 20px;">
                    <li>
                        <a href="gallery_albums_management.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-images" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Gallery Albums</span>
                        </a>
                    </li>
                    <li>
                        <a href="gallery.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-link" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Drive Event Links</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="event_calendar.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="nav-text">Event Calendar</span>
                </a>
            </li>
            <li>
                <a href="collaborations_management.php">
                    <i class="fas fa-handshake"></i>
                    <span class="nav-text">Collaborations</span>
                </a>
            </li>
            <li>
                <a href="research_infrastructure.php">
                    <i class="fas fa-flask"></i>
                    <span class="nav-text">Research &amp; Infrastructure</span>
                </a>
            </li>

            <?php if (isSuperAdmin()): ?>
            <!-- Site Management Dropdown Group (Super Admin only) -->
            <li class="nav-group" id="nav-sitemgmt-group">
                <a href="javascript:void(0)" class="nav-group-toggle has-arrow" onclick="toggleNavGroup('sitemgmt-sub')">
                    <i class="fas fa-cogs"></i>
                    <span class="nav-text">Site Management</span>
                    <i class="fas fa-chevron-down nav-arrow" id="sitemgmt-arrow" style="margin-left:auto; font-size:0.7rem;"></i>
                </a>
                <ul class="nav-group-sub" id="sitemgmt-sub" style="display:none; list-style:none; padding: 4px 0 4px 20px;">
                    <li>
                        <a href="banner_management.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-image" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Homepage Banners</span>
                        </a>
                    </li>
                    <li>
                        <a href="announcements_management.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-bullhorn" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Scrolling Ticker</span>
                        </a>
                    </li>
                    <li>
                        <a href="team_management.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-user-cog" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Team Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="manage_admins.php" style="padding: 9px 14px !important; font-size: 13px;">
                            <i class="fas fa-users-cog" style="font-size:0.95rem;"></i>
                            <span class="nav-text">Manage Admins</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>


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
            var galleryPages = ['gallery_albums_management.php', 'gallery.php'];
            var sitemgmtPages = ['banner_management.php', 'announcements_management.php', 'team_management.php', 'manage_admins.php'];
            if (galleryPages.indexOf(currentPage) !== -1) {
                toggleNavGroup('gallery-sub');
            } else if (sitemgmtPages.indexOf(currentPage) !== -1) {
                toggleNavGroup('sitemgmt-sub');
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