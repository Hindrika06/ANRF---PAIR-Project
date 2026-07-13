<!DOCTYPE html>
<html lang="en">
<head>
 <!-- PAGE TITLE HERE -->
	<title>Management And Administration Website Templates | Fillow : Fillow Saas Admin Bootstrap 5 Template - Empowering Your Administration Work  | Dexignlabs</title>


	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="Dexignlabs">
	<meta name="robots" content="index, follow">

	<meta name="keywords" content="	admin, admin dashboard, admin template, analytics, bootstrap, bootstrap5, bootstrap 5 admin template, modern, responsive admin dashboard, sales dashboard, sass, ui kit, web app, Fillow SaaS, User Interface (UI), User Experience (UX), Dashboard Design, SaaS Application, Web Application, Data Visualization, Analytics, Customization, Responsive Design, Bootstrap Framework, Charts and Graphs, Data Management, Reporting, Dark Mode, Mobile-Friendly, Dashboard Components, Integrations, Analytics Dashboard, API Integration, User Authentication">


	<meta name="description" content="Elevate your administrative efficiency and enhance productivity with the Fillow SaaS Admin Dashboard Template. Designed to streamline your tasks, this powerful tool provides a user-friendly interface, robust features, and customizable options, making it the ideal choice for managing your data and operations with ease.">

	<meta property="og:title" content="Fillow : Fillow Saas Admin Bootstrap 5 Template | Dexignlabs">
	<meta property="og:description" content="Elevate your administrative efficiency and enhance productivity with the Fillow SaaS Admin Dashboard Template. Designed to streamline your tasks, this powerful tool provides a user-friendly interface, robust features, and customizable options, making it the ideal choice for managing your data and operations with ease.">
	<meta property="og:image" content="https://fillow.dexignlab.com/xhtml/social-image.png">
	<meta name="format-detection" content="telephone=no">

	<meta name="twitter:title" content="Fillow : Fillow Saas Admin Bootstrap 5 Template | Dexignlabs">
	<meta name="twitter:description" content="Elevate your administrative efficiency and enhance productivity with the Fillow SaaS Admin Dashboard Template. Designed to streamline your tasks, this powerful tool provides a user-friendly interface, robust features, and customizable options, making it the ideal choice for managing your data and operations with ease.">
	<meta name="twitter:image" content="https://fillow.dexignlab.com/xhtml/social-image.png">
	<meta name="twitter:card" content="summary_large_image">

	<!-- MOBILE SPECIFIC -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- FAVICONS ICON -->
	<link rel="shortcut icon" type="image/png" href="images/favicon.png">
	<link href="vendor/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
	
    <?php if (!isSuperAdmin()): ?>
    <style>
        /* ══════════════════════════════════════════════════════
           ADMIN PORTAL — PROFESSIONAL TEAL/EMERALD THEME
           Designed as a distinct, polished theme vs. Super Admin
        ══════════════════════════════════════════════════════ */

        /* Force visibility of the main wrapper in case the preloader JS is delayed or fails */
        #main-wrapper {
            opacity: 1 !important;
        }

        /* ── 1. Sidebar: Solid Dark Teal ── */
        .custom-sidebar {
            background: #0f4c3a !important;
            box-shadow: 4px 0 20px rgba(15, 76, 58, 0.4) !important;
        }
        .custom-sidebar ul.metismenu li a {
            color: #a7d9c8 !important;
        }
        body.theme-admin .custom-sidebar ul.metismenu li a i {
            color: #ffffff !important;
        }
        .custom-sidebar ul.metismenu li a:hover {
            background: rgba(255,255,255,0.10) !important;
            color: #ffffff !important;
            transform: translateX(5px);
        }
        body.theme-admin .custom-sidebar ul.metismenu li a:hover i {
            color: #ffffff !important;
        }
        body.theme-admin .custom-sidebar ul.metismenu li.mm-active > a,
        body.theme-admin .custom-sidebar ul.metismenu li.active > a,
        body.theme-admin .custom-sidebar ul.metismenu li a.mm-active,
        body.theme-admin .custom-sidebar ul.metismenu li a.active {
            background: rgba(255,255,255,0.15) !important;
            color: #ffffff !important;
            font-weight: 700;
            border-left: none !important;
        }
        body.theme-admin .custom-sidebar ul.metismenu > li > a:before {
            background: #ffffff !important;
        }

        /* ── Portal Badge: minimal navigation title ── */
        .custom-sidebar .portal-badge {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 10px !important;
            margin-top: 5px !important;
        }
        .custom-sidebar .portal-badge i {
            color: #ffffff !important;
            font-size: 14px !important;
        }
        .custom-sidebar .portal-badge span {
            color: #ffffff !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.1em !important;
            text-transform: uppercase !important;
        }

        /* ── Sidebar Footer ── */
        .custom-sidebar .sidebar-footer {
            background: rgba(0,0,0,0.25) !important;
            border-top: 1px solid rgba(255,255,255,0.08) !important;
        }
        .custom-sidebar ul.metismenu li a.logout-btn {
            color: #fca5a5 !important;
        }
        .custom-sidebar ul.metismenu li a.logout-btn i {
            color: #fca5a5 !important;
        }
        .custom-sidebar ul.metismenu li a.logout-btn:hover {
            background: rgba(239,68,68,0.2) !important;
            color: #fff !important;
        }

        /* ── Sidebar brand name color override ── */
        .brand-institute-name {
            color: #047857 !important;
        }

        /* ── 2. Top Header bar: teal accent ── */
        .header {
            background: #ffffff !important;
            border-bottom: 3px solid #059669 !important;
        }
        .header .dashboard_bar {
            color: #065f46 !important;
            font-weight: 700 !important;
        }
        .input-group.search-area .form-control {
            border-color: #a7f3d0 !important;
            background: #f0fdf4 !important;
        }
        .input-group.search-area .form-control:focus {
            border-color: #059669 !important;
            box-shadow: 0 0 0 3px rgba(5,150,105,0.15) !important;
        }
        .input-group.search-area .input-group-text {
            border-color: #a7f3d0 !important;
            background: #ecfdf5 !important;
            color: #059669 !important;
        }

        /* ── 3. Table Headers: rich teal ── */
        .table-theme-sapphire thead th {
            background: #059669 !important;
            color: #ecfdf5 !important;
            border-bottom: none !important;
            letter-spacing: 0.6px !important;
        }
        .table-theme-sapphire tbody tr:nth-child(even) {
            background-color: #f0fdf4 !important;
        }
        .table-theme-sapphire tbody tr:hover {
            background-color: #d1fae5 !important;
        }

        /* ── 4. Titles & Links ── */
        .card-title, .registry-task-link {
            color: #065f46 !important;
        }
        .registry-task-link:hover {
            color: #059669 !important;
        }
        .index-badge-circle {
            background-color: #059669 !important;
        }
        .registry-tag-pill {
            color: #047857 !important;
            background-color: #d1fae5 !important;
        }

        /* ── 5. Pagination ── */
        .pagination-theme-sapphire .page-item.active .page-link {
            background-color: #059669 !important;
            border-color: #059669 !important;
            color: #fff !important;
        }
        .pagination-theme-sapphire .page-link {
            color: #059669 !important;
        }

        /* ── 6. KPI Cards: colourful gradient tiles per page ── */
        .kpi-widget-card {
            border: none !important;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important;
            border-radius: 14px !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        }
        .kpi-widget-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 10px 28px rgba(0,0,0,0.18) !important;
        }
        .kpi-icon-circle {
            background-color: rgba(255,255,255,0.22) !important;
        }
        .kpi-icon-circle i { color: #ffffff !important; }
        .kpi-title-text, .kpi-metric-value, .kpi-subtext { color: #ffffff !important; }

        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        if ($currentPage === 'publications.php') {
            echo '.kpi-widget-card { background: #0e7490 !important; }';
        } elseif ($currentPage === 'progress_reports.php') {
            echo '.kpi-widget-card { background: #5b21b6 !important; }';
        } elseif ($currentPage === 'patents.php') {
            echo '.kpi-widget-card { background: #d97706 !important; }';
        } elseif ($currentPage === 'conferences.php') {
            echo '.kpi-widget-card { background: #047857 !important; }';
        } elseif ($currentPage === 'webinars.php') {
            echo '.kpi-widget-card { background: #be185d !important; }';
        } elseif ($currentPage === 'internships.php') {
            echo '.kpi-widget-card { background: #3730a3 !important; }';
        } elseif ($currentPage === 'event_calendar.php' || $currentPage === 'team_management.php') {
            echo '.kpi-widget-card { background: #886cc0 !important; }';
        } else {
            echo '.kpi-widget-card { background: #047857 !important; }';
        }
        ?>
    </style>
    <?php else: ?>
    <style>
        /* ══════════════════════════════════════════════════════
           SUPER ADMIN PORTAL — PREMIUM SAPPHIRE BLUE THEME
           Designed with rich gradients, shine, and modern glow.
           Keeps original color identity (Sapphire Blue #024283).
        ══════════════════════════════════════════════════════ */

        /* Force visibility of the main wrapper in case the preloader JS is delayed or fails */
        #main-wrapper {
            opacity: 1 !important;
        }

        /* ── Portal Badge: minimal navigation title ── */
        body.theme-super-admin .custom-sidebar .portal-badge {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 10px !important;
            margin-top: 5px !important;
        }
        body.theme-super-admin .custom-sidebar .portal-badge i {
            color: #ffffff !important;
            font-size: 14px !important;
        }
        body.theme-super-admin .custom-sidebar .portal-badge span {
            color: #ffffff !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.1em !important;
            text-transform: uppercase !important;
        }

        /* ── 1. Sidebar: Solid Premium Dark Sapphire ── */
        .custom-sidebar {
            background: #01244a !important;
            box-shadow: 4px 0 20px rgba(2, 66, 131, 0.35) !important;
        }

        /* Ambient shine overlay for the sidebar */
        .custom-sidebar::before {
            display: none !important;
        }

        .custom-sidebar ul.metismenu li a {
            color: #b4c6fc !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        body.theme-super-admin .custom-sidebar ul.metismenu li a i {
            color: #ffffff !important;
            transition: color 0.3s ease !important;
        }

        .custom-sidebar ul.metismenu li a:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
            transform: translateX(5px) !important;
        }

        body.theme-super-admin .custom-sidebar ul.metismenu li a:hover i {
            color: #ffffff !important;
        }

        body.theme-super-admin .custom-sidebar ul.metismenu li.mm-active > a,
        body.theme-super-admin .custom-sidebar ul.metismenu li.active > a,
        body.theme-super-admin .custom-sidebar ul.metismenu li a.mm-active,
        body.theme-super-admin .custom-sidebar ul.metismenu li a.active {
            background: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
            font-weight: 700;
            border-left: none !important;
            box-shadow: inset 4px 0 10px rgba(0,0,0,0.1);
        }
        body.theme-super-admin .custom-sidebar ul.metismenu > li > a:before {
            background: #ffffff !important;
        }

        /* ── Portal Badge: Glossy sapphire pill with neon glow border ── */
        .custom-sidebar .portal-badge {
            background: #0b2e5c !important;
            color: #ffffff !important;
            border: 1px solid rgba(56, 189, 248, 0.35) !important;
            box-shadow: 0 4px 12px rgba(2, 66, 131, 0.4) !important;
        }
        .custom-sidebar .portal-badge i,
        .custom-sidebar .portal-badge span {
            color: #ffffff !important;
            text-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
        }

        /* ── Sidebar Footer ── */
        .custom-sidebar .sidebar-footer {
            background: rgba(0, 0, 0, 0.22) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .custom-sidebar ul.metismenu li a.logout-btn {
            color: #fca5a5 !important;
        }
        .custom-sidebar ul.metismenu li a.logout-btn i {
            color: #fca5a5 !important;
        }
        .custom-sidebar ul.metismenu li a.logout-btn:hover {
            background: rgba(239, 68, 68, 0.2) !important;
            color: #ffffff !important;
        }

        /* ── Sidebar brand name color override ── */
        .brand-institute-name {
            color: #024283 !important;
        }

        /* ── 2. Top Header bar: sapphire accent ── */
        .header {
            background: #ffffff !important;
            border-bottom: 3px solid #024283 !important;
            box-shadow: 0 4px 20px rgba(2, 66, 131, 0.04) !important;
        }
        .header .dashboard_bar {
            color: #024283 !important;
            font-weight: 700 !important;
        }
        .input-group.search-area .form-control {
            border-color: #bfdbfe !important;
            background: #f8fafc !important;
        }
        .input-group.search-area .form-control:focus {
            border-color: #024283 !important;
            box-shadow: 0 0 0 3px rgba(2, 66, 131, 0.15) !important;
            background: #ffffff !important;
        }
        .input-group.search-area .input-group-text {
            border-color: #bfdbfe !important;
            background: #eff6ff !important;
            color: #024283 !important;
        }

        /* ── 3. Table Headers & Rows: sapphire gradient & soft blue hover ── */
        body.theme-super-admin .table-theme-sapphire thead th {
            background: #024283 !important;
            color: #ffffff !important;
            border-bottom: none !important;
            letter-spacing: 0.6px !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.15);
        }
        body.theme-super-admin .table-theme-sapphire tbody tr:nth-child(even) {
            background-color: #f8fafc !important;
        }
        body.theme-super-admin .table-theme-sapphire tbody tr:hover {
            background-color: #eff6ff !important;
        }

        /* ── 4. Titles, Badges & Links ── */
        .card-title, .registry-task-link {
            color: #024283 !important;
        }
        .registry-task-link:hover {
            color: #1e40af !important;
        }
        .index-badge-circle {
            background-color: #024283 !important;
            box-shadow: 0 2px 5px rgba(2, 66, 131, 0.25) !important;
        }
        .registry-tag-pill {
            color: #024283 !important;
            background-color: #eff6ff !important;
            border: 1px solid rgba(2, 66, 131, 0.15) !important;
        }

        /* ── 5. Pagination ── */
        .pagination-theme-sapphire .page-item.active .page-link {
            background-color: #024283 !important;
            border-color: #024283 !important;
            color: #fff !important;
            box-shadow: 0 2px 6px rgba(2, 66, 131, 0.3) !important;
        }
        .pagination-theme-sapphire .page-link {
            color: #024283 !important;
        }

        /* ── 6. KPI Cards: Beautiful Gradient & Glassy Shine ── */
        body.theme-super-admin .kpi-widget-card {
            border: none !important;
            box-shadow: 0 6px 20px rgba(2, 66, 131, 0.12) !important;
            border-radius: 14px !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
            position: relative;
            overflow: hidden;
        }

        /* Premium Gloss/Shine Highlight Overlay for KPI Cards */
        body.theme-super-admin .kpi-widget-card::before {
            display: none !important;
        }

        body.theme-super-admin .kpi-widget-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 10px 28px rgba(2, 66, 131, 0.2) !important;
        }

        body.theme-super-admin .kpi-icon-circle {
            background-color: rgba(255, 255, 255, 0.22) !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        body.theme-super-admin .kpi-icon-circle i {
            color: #ffffff !important;
        }
        body.theme-super-admin .kpi-title-text,
        body.theme-super-admin .kpi-metric-value,
        body.theme-super-admin .kpi-subtext {
            color: #ffffff !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        if ($currentPage === 'publications.php') {
            // Solid RED color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: #b71c1c !important; box-shadow: 0 6px 20px rgba(183, 28, 28, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(183, 28, 28, 0.35) !important; }';
        } elseif ($currentPage === 'progress_reports.php') {
            // Solid PURPLE color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: #5b21b6 !important; box-shadow: 0 6px 20px rgba(124, 58, 237, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(124, 58, 237, 0.35) !important; }';
        } elseif ($currentPage === 'patents.php') {
            // Solid VIOLET/PURPLE color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: #6d28d9 !important; box-shadow: 0 6px 20px rgba(109, 40, 217, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(109, 40, 217, 0.35) !important; }';
        } elseif ($currentPage === 'conferences.php') {
            // Solid GREEN color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: #15803d !important; box-shadow: 0 6px 20px rgba(21, 128, 61, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(21, 128, 61, 0.35) !important; }';
        } elseif ($currentPage === 'webinars.php') {
            // Solid ROSE/PINK color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: #be185d !important; box-shadow: 0 6px 20px rgba(190, 24, 93, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(190, 24, 93, 0.35) !important; }';
        } elseif ($currentPage === 'internships.php') {
            // Solid INDIGO color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: #3730a3 !important; box-shadow: 0 6px 20px rgba(55, 48, 163, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(55, 48, 163, 0.35) !important; }';
        } elseif ($currentPage === 'gallery.php') {
            // Solid TEAL color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: #0f766e !important; box-shadow: 0 6px 20px rgba(15, 118, 110, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(15, 118, 110, 0.35) !important; }';
        } elseif ($currentPage === 'event_calendar.php' || $currentPage === 'team_management.php') {
            // Solid PURPLE color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: #886cc0 !important; box-shadow: 0 6px 20px rgba(136, 108, 192, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(136, 108, 192, 0.35) !important; }';
        } else {
            // Default to sapphire
            echo 'body.theme-super-admin .kpi-widget-card { background: #024283 !important; box-shadow: 0 6px 20px rgba(2, 66, 131, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(2, 66, 131, 0.35) !important; }';
        }
        ?>
    </style>
    <?php endif; ?>
    <!-- User Profile Dropdown Styles & Scripts -->
    <style>
        /* --- PROFILE DROPDOWN CUSTOM STYLES --- */
        .profile-trigger-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .profile-trigger-btn:hover .profile-avatar-img,
        .profile-trigger-btn:focus .profile-avatar-img {
            border-color: #024283 !important;
            transform: scale(1.05);
        }

        .theme-admin .profile-trigger-btn:hover .profile-avatar-img,
        .theme-admin .profile-trigger-btn:focus .profile-avatar-img {
            border-color: #059669 !important;
        }

        .dropdown-menu-custom {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            min-width: 270px;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 1rem; /* Rounded border design */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); /* Soft drop shadow */
            z-index: 1050; /* Z-index priority */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease-in-out, visibility 0.2s ease-in-out;
            padding: 0;
            overflow: hidden;
        }

        .dropdown-menu-custom.show {
            opacity: 1;
            visibility: visible;
        }

        /* Top Section: User Info Card */
        .dropdown-header-custom {
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
        }

        .dropdown-header-custom .user-avatar-large {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            background: #ffffff;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dropdown-header-custom .user-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 2px;
        }

        .dropdown-header-custom .user-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .dropdown-header-custom .user-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .dropdown-header-custom .user-role {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.2;
        }

        .dropdown-header-custom .user-badge {
            margin-top: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            background: #eff6ff;
            color: #024283;
            padding: 2px 8px;
            border-radius: 12px;
            width: fit-content;
            white-space: nowrap;
        }

        .theme-admin .dropdown-header-custom .user-badge {
            background: #f0fdf4;
            color: #059669;
        }

        .dropdown-divider-custom {
            height: 1px;
            background-color: #f1f5f9;
            margin: 0;
        }

        /* Bottom Section: Logout Action */
        .dropdown-item-custom {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #dc2626; /* Crimson Red text */
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
            cursor: pointer;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
        }

        .dropdown-item-custom i {
            font-size: 0.95rem;
            color: #dc2626; /* Left-aligned red sign-out icon */
            width: 18px;
            text-align: center;
        }

        .dropdown-item-custom:hover,
        .dropdown-item-custom:focus {
            background-color: rgba(220, 38, 38, 0.08); /* Soft light-red tint background */
            color: #b91c1c; /* Crimson Red hover */
            text-decoration: none;
            outline: none;
        }

        .dropdown-item-custom:hover i,
        .dropdown-item-custom:focus i {
            color: #b91c1c;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trigger = document.getElementById('profileDropdownTrigger');
            const menu = document.getElementById('profileDropdownMenu');
            
            if (!trigger || !menu) return;
            
            const menuItems = Array.from(menu.querySelectorAll('[role="menuitem"], a'));

            function openDropdown() {
                menu.classList.add('show');
                trigger.setAttribute('aria-expanded', 'true');
            }
            
            function closeDropdown() {
                menu.classList.remove('show');
                trigger.setAttribute('aria-expanded', 'false');
            }
            
            // Toggle dropdown on click
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const isOpen = menu.classList.contains('show');
                if (isOpen) {
                    closeDropdown();
                    trigger.focus();
                } else {
                    openDropdown();
                    if (menuItems.length > 0) {
                        menuItems[0].focus();
                    }
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!menu.contains(e.target) && !trigger.contains(e.target)) {
                    closeDropdown();
                }
            });
            
            // Keyboard navigation within the dropdown
            menu.addEventListener('keydown', (e) => {
                const currentIndex = menuItems.indexOf(document.activeElement);
                
                switch (e.key) {
                    case 'Escape':
                        e.preventDefault();
                        closeDropdown();
                        trigger.focus();
                        break;
                    case 'ArrowDown':
                        e.preventDefault();
                        const nextIndex = (currentIndex + 1) % menuItems.length;
                        menuItems[nextIndex].focus();
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        const prevIndex = (currentIndex - 1 + menuItems.length) % menuItems.length;
                        menuItems[prevIndex].focus();
                        break;
                    case 'Home':
                        e.preventDefault();
                        if (menuItems.length > 0) {
                            menuItems[0].focus();
                        }
                        break;
                    case 'End':
                        e.preventDefault();
                        if (menuItems.length > 0) {
                            menuItems[menuItems.length - 1].focus();
                        }
                        break;
                    case 'Tab':
                        setTimeout(() => {
                            if (!menu.contains(document.activeElement) && document.activeElement !== trigger) {
                                closeDropdown();
                            }
                        }, 10);
                        break;
                }
            });

            // Close when Esc is pressed on trigger
            trigger.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeDropdown();
                    trigger.focus();
                }
            });
        });
    </script>
</head>
<body class="<?= isSuperAdmin() ? 'theme-super-admin' : 'theme-admin' ?>">
	<!--**********************************
            Header start
        ***********************************-->
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
							<div class="dashboard_bar">
                                Manage Dashboard
                            </div>
                        </div>
                         <ul class="navbar-nav header-right">
							
							<li class="nav-item d-flex align-items-center">
								<div class="input-group search-area">
									<input type="text" class="form-control" placeholder="Search here...">
									<span class="input-group-text"><a href="javascript:void(0)"><i class="flaticon-381-search-2"></i></a></span>
								</div>
							</li>
						
							
							

							
							<li class="nav-item header-profile d-flex align-items-center" style="margin-left: 15px; position: relative;">
								<?php
								$headerIsSuper = isSuperAdmin();
								$headerBrandPrefix = $headerIsSuper ? 'uoh' : resolveAdminPrefix();
								$headerProfileLogo = $headerIsSuper ? 'logo/logo.png' : getInstituteLogo($headerBrandPrefix);
								
								// Dynamic display that strictly falls back to requested defaults for Super Admin
								$headerEmail = $_SESSION['username'] ?? 'admin@uoh.ac.in';
								$headerRole = $headerIsSuper ? 'Super Admin' : 'Admin';
								$headerBadge = $headerIsSuper ? 'ANRF Super Admin' : 'ANRF Admin - ' . getInstituteLabel($headerBrandPrefix);
								?>
								<button class="nav-link p-0 profile-trigger-btn" id="profileDropdownTrigger" aria-haspopup="true" aria-expanded="false">
									<img src="<?= htmlspecialchars($headerProfileLogo) ?>" alt="ANRF-PAIR" style="width: 40px; height: 40px; border-radius: 50%; object-fit: contain; border: 2px solid #e2e8f0; background: #fff; padding: 2px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: transform 0.2s ease, border-color 0.2s ease;" class="profile-avatar-img">
								</button>
								
								<!-- User Profile Dropdown Menu -->
								<div class="dropdown-menu-custom" id="profileDropdownMenu" aria-labelledby="profileDropdownTrigger">
									<!-- Top Section (User Info Card) -->
									<div class="dropdown-header-custom">
										<div class="user-avatar-large">
											<img src="<?= htmlspecialchars($headerProfileLogo) ?>" alt="ANRF-PAIR">
										</div>
										<div class="user-details">
											<div class="user-name"><?= htmlspecialchars($headerEmail) ?></div>
											<div class="user-role"><?= htmlspecialchars($headerRole) ?></div>
											<div class="user-badge"><?= htmlspecialchars($headerBadge) ?></div>
										</div>
									</div>
									<div class="dropdown-divider-custom"></div>
									<!-- Bottom Section (Logout Action) -->
									<a href="logout.php" class="dropdown-item-custom logout-item" role="menuitem">
										<i class="fas fa-sign-out-alt"></i>
										<span>Logout</span>
									</a>
								</div>
							</li>
                        </ul>
                    </div>
				</nav>
			</div>
		</div>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->