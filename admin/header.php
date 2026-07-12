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

        /* ── 1. Sidebar: Rich Teal-Green gradient ── */
        .custom-sidebar {
            background: linear-gradient(180deg, #0f4c3a 0%, #1a6b52 55%, #0d3d2f 100%) !important;
            box-shadow: 4px 0 20px rgba(15, 76, 58, 0.4) !important;
        }
        .custom-sidebar ul.metismenu li a {
            color: #a7d9c8 !important;
        }
        .custom-sidebar ul.metismenu li a i {
            color: #6ec6aa !important;
        }
        .custom-sidebar ul.metismenu li a:hover {
            background: rgba(255,255,255,0.10) !important;
            color: #ffffff !important;
            transform: translateX(5px);
        }
        .custom-sidebar ul.metismenu li a:hover i {
            color: #4ade80 !important;
        }
        .custom-sidebar ul.metismenu li.active > a,
        .custom-sidebar ul.metismenu li a.active {
            background: rgba(255,255,255,0.15) !important;
            color: #ffffff !important;
            font-weight: 700;
            border-left: 3px solid #4ade80;
        }

        /* ── Portal Badge: vibrant emerald pill ── */
        .custom-sidebar .portal-badge {
            background: linear-gradient(135deg, #065f46, #047857) !important;
            color: #d1fae5 !important;
            border: 1px solid rgba(74,222,128,0.3) !important;
            box-shadow: 0 2px 8px rgba(4,120,87,0.4) !important;
        }
        .custom-sidebar .portal-badge i,
        .custom-sidebar .portal-badge span {
            color: #d1fae5 !important;
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
            background: linear-gradient(90deg,#065f46,#059669) !important;
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
            echo '.kpi-widget-card { background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%) !important; }';
        } elseif ($currentPage === 'progress_reports.php') {
            echo '.kpi-widget-card { background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%) !important; }';
        } elseif ($currentPage === 'patents.php') {
            echo '.kpi-widget-card { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; }';
        } elseif ($currentPage === 'conferences.php') {
            echo '.kpi-widget-card { background: linear-gradient(135deg, #059669 0%, #047857 100%) !important; }';
        } elseif ($currentPage === 'webinars.php') {
            echo '.kpi-widget-card { background: linear-gradient(135deg, #e11d48 0%, #be185d 100%) !important; }';
        } elseif ($currentPage === 'internships.php') {
            echo '.kpi-widget-card { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%) !important; }';
        } else {
            echo '.kpi-widget-card { background: linear-gradient(135deg, #059669 0%, #047857 100%) !important; }';
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

        /* ── 1. Sidebar: Rich Sapphire gradient with subtle shine ── */
        .custom-sidebar {
            background: linear-gradient(180deg, #01244a 0%, #024283 50%, #00152b 100%) !important;
            box-shadow: 4px 0 20px rgba(2, 66, 131, 0.35) !important;
        }

        /* Ambient shine overlay for the sidebar */
        .custom-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% -20%, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 60%);
            pointer-events: none;
            z-index: 1;
        }

        .custom-sidebar ul.metismenu li a {
            color: #b4c6fc !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .custom-sidebar ul.metismenu li a i {
            color: #60a5fa !important;
            transition: color 0.3s ease !important;
        }

        .custom-sidebar ul.metismenu li a:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
            transform: translateX(5px) !important;
        }

        .custom-sidebar ul.metismenu li a:hover i {
            color: #38bdf8 !important;
        }

        .custom-sidebar ul.metismenu li.active > a,
        .custom-sidebar ul.metismenu li a.active {
            background: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
            font-weight: 700;
            border-left: 3px solid #38bdf8 !important;
            box-shadow: inset 4px 0 10px rgba(0,0,0,0.1);
        }

        /* ── Portal Badge: Glossy sapphire pill with neon glow border ── */
        .custom-sidebar .portal-badge {
            background: linear-gradient(135deg, #0b2e5c 0%, #0f3a72 100%) !important;
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
            background: linear-gradient(90deg, #024283 0%, #1e40af 100%) !important;
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
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0.05) 50%, rgba(0, 0, 0, 0) 50.1%, rgba(0, 0, 0, 0.05) 100%) !important;
            pointer-events: none;
            z-index: 2;
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
            // Keep the RED color identity, but style as a gorgeous gradient
            echo 'body.theme-super-admin .kpi-widget-card { background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 50%, #7f0000 100%) !important; box-shadow: 0 6px 20px rgba(183, 28, 28, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(183, 28, 28, 0.35) !important; }';
        } elseif ($currentPage === 'progress_reports.php') {
            // Keep the PURPLE color identity, but style as a gorgeous gradient
            echo 'body.theme-super-admin .kpi-widget-card { background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 50%, #1e1b4b 100%) !important; box-shadow: 0 6px 20px rgba(124, 58, 237, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(124, 58, 237, 0.35) !important; }';
        } elseif ($currentPage === 'patents.php') {
            // Keep the VIOLET/PURPLE color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 50%, #4c1d95 100%) !important; box-shadow: 0 6px 20px rgba(109, 40, 217, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(109, 40, 217, 0.35) !important; }';
        } elseif ($currentPage === 'conferences.php') {
            // Keep the GREEN color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: linear-gradient(135deg, #22c55e 0%, #15803d 50%, #14532d 100%) !important; box-shadow: 0 6px 20px rgba(21, 128, 61, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(21, 128, 61, 0.35) !important; }';
        } elseif ($currentPage === 'webinars.php') {
            // Keep the ROSE/PINK color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: linear-gradient(135deg, #e11d48 0%, #be185d 50%, #881337 100%) !important; box-shadow: 0 6px 20px rgba(190, 24, 93, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(190, 24, 93, 0.35) !important; }';
        } elseif ($currentPage === 'internships.php') {
            // Keep the INDIGO color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 50%, #1e1b4b 100%) !important; box-shadow: 0 6px 20px rgba(55, 48, 163, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(55, 48, 163, 0.35) !important; }';
        } elseif ($currentPage === 'gallery.php') {
            // Keep the TEAL color identity
            echo 'body.theme-super-admin .kpi-widget-card { background: linear-gradient(135deg, #0d9488 0%, #0f766e 50%, #115e59 100%) !important; box-shadow: 0 6px 20px rgba(15, 118, 110, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(15, 118, 110, 0.35) !important; }';
        } else {
            // Default to sapphire gradient
            echo 'body.theme-super-admin .kpi-widget-card { background: linear-gradient(135deg, #024283 0%, #1e40af 50%, #172554 100%) !important; box-shadow: 0 6px 20px rgba(2, 66, 131, 0.25) !important; }';
            echo 'body.theme-super-admin .kpi-widget-card:hover { box-shadow: 0 10px 28px rgba(2, 66, 131, 0.35) !important; }';
        }
        ?>
    </style>
    <?php endif; ?>
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
						
							
							
							<li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link " href="javascript:void(0);" data-bs-toggle="dropdown">
									<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M22.1666 5.83331H20.9999V3.49998C20.9999 3.19056 20.877 2.89381 20.6582 2.67502C20.4394 2.45623 20.1427 2.33331 19.8333 2.33331C19.5238 2.33331 19.2271 2.45623 19.0083 2.67502C18.7895 2.89381 18.6666 3.19056 18.6666 3.49998V5.83331H9.33325V3.49998C9.33325 3.19056 9.21034 2.89381 8.99154 2.67502C8.77275 2.45623 8.47601 2.33331 8.16659 2.33331C7.85717 2.33331 7.56042 2.45623 7.34163 2.67502C7.12284 2.89381 6.99992 3.19056 6.99992 3.49998V5.83331H5.83325C4.90499 5.83331 4.01476 6.20206 3.35838 6.85844C2.702 7.51482 2.33325 8.40506 2.33325 9.33331V10.5H25.6666V9.33331C25.6666 8.40506 25.2978 7.51482 24.6415 6.85844C23.9851 6.20206 23.0948 5.83331 22.1666 5.83331Z" fill="#717579"/>
										<path d="M2.33325 22.1666C2.33325 23.0949 2.702 23.9851 3.35838 24.6415C4.01476 25.2979 4.90499 25.6666 5.83325 25.6666H22.1666C23.0948 25.6666 23.9851 25.2979 24.6415 24.6415C25.2978 23.9851 25.6666 23.0949 25.6666 22.1666V12.8333H2.33325V22.1666Z" fill="#717579"/>
									</svg>
									<span class="badge light text-white bg-success rounded-circle">!</span>
                                </a>
								<div class="dropdown-menu dropdown-menu-end">
									<div id="DZ_W_TimeLine02" class="widget-timeline dlab-scroll style-1 ps ps--active-y p-3 height370">
										<ul class="timeline">
											<li>
												<div class="timeline-badge primary"></div>
												<a class="timeline-panel text-muted" href="javascript:void(0);">
													<span>10 minutes ago</span>
													<h6 class="mb-0">Youtube, a video-sharing website, goes live <strong class="text-primary">$500</strong>.</h6>
												</a>
											</li>
											<li>
												<div class="timeline-badge info">
												</div>
												<a class="timeline-panel text-muted" href="javascript:void(0);">
													<span>20 minutes ago</span>
													<h6 class="mb-0">New order placed <strong class="text-info">#XF-2356.</strong></h6>
													<p class="mb-0">Quisque a consequat ante Sit amet magna at volutapt...</p>
												</a>
											</li>
											<li>
												<div class="timeline-badge danger">
												</div>
												<a class="timeline-panel text-muted" href="javascript:void(0);">
													<span>30 minutes ago</span>
													<h6 class="mb-0">john just buy your product <strong class="text-warning">Sell $250</strong></h6>
												</a>
											</li>
											<li>
												<div class="timeline-badge success">
												</div>
												<a class="timeline-panel text-muted" href="javascript:void(0);">
													<span>15 minutes ago</span>
													<h6 class="mb-0">StumbleUpon is acquired by eBay. </h6>
												</a>
											</li>
											<li>
												<div class="timeline-badge warning">
												</div>
												<a class="timeline-panel text-muted" href="javascript:void(0);">
													<span>20 minutes ago</span>
													<h6 class="mb-0">Mashable, a news website and blog, goes live.</h6>
												</a>
											</li>
											<li>
												<div class="timeline-badge dark">
												</div>
												<a class="timeline-panel text-muted" href="javascript:void(0);">
													<span>20 minutes ago</span>
													<h6 class="mb-0">Mashable, a news website and blog, goes live.</h6>
												</a>
											</li>
										</ul>
									</div>
								</div>
							</li>
							
							<li class="nav-item header-profile d-flex align-items-center" style="margin-left: 15px;">
								<a class="nav-link p-0" href="logout.php" title="Logout" style="display: flex; align-items: center;">
									<img src="logo/logo.png" alt="ANRF Logo" style="width: 40px; height: 40px; border-radius: 50%; object-fit: contain; border: 2px solid #e2e8f0; background: #fff; padding: 2px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
								</a>
							</li>
                        </ul>
                    </div>
				</nav>
			</div>
		</div>
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->