<?php
$pageTitle  = $pageTitle  ?? 'ANRF–PAIR Project | Innovations in Health and Medical Technologies';
$activePage = $activePage ?? '';
$isHomePage = (basename($_SERVER['PHP_SELF']) === 'index.php' && strpos($_SERVER['PHP_SELF'], '/admin/') === false);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="ANRF–PAIR Project, University of Hyderabad">

    <link rel="shortcut icon" href="logo.ico" type="image/x-icon">
    <link rel="icon" href="2.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="2.png">

    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="assets/css/font-awesome.css"              rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.css"  type="text/css">
    <link rel="stylesheet" href="assets/css/selectize.css"            type="text/css">
    <link rel="stylesheet" href="assets/css/owl.carousel.css"         type="text/css">
    <link rel="stylesheet" href="assets/css/vanillabox/vanillabox.css" type="text/css">
    <link rel="stylesheet" href="assets/css/layerslider.css"          type="text/css">
    <link rel="stylesheet" href="assets/css/flexslider.css"           type="text/css">
    <link rel="stylesheet" href="assets/css/style.css"                type="text/css">
    <link rel="stylesheet" href="assets/css/custom.css"                type="text/css">

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <style>

        /* ============================================================
           BOOTSTRAP OVERRIDES
           ============================================================ */
        .navbar {
            padding: 0 !important;
            min-height: auto !important;
        }
        .navbar-nav > li > a {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        /* ============================================================
           LOGO SECTION — BASE (desktop first)
           ============================================================ */
        .logo-section {
            background-color: #fff;
            padding: 12px 0;
        }

        /* Three-column flex row */
        .logo-container-split {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        /* Grey separator line — left section only */
        .logo-left::after {
            content: '';
            width: 1px;
            height: 60px;
            background-color: #d0d0d0;
            margin: 0 15px;
        }

        /* LEFT: ANRF logo */
        .logo-left {
            flex: 1;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
        .logo-left a { display: inline-block; line-height: 0; }
        .logo-left-img {
            max-height: 90px;
            width: auto;
            display: block;
        }

        /* CENTER: PAIR badge + title */
        .logo-center {
            flex: 2;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .logo-center-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }
        .logo-pair-img {
            max-height: 80px;
            width: auto;
            flex-shrink: 0;
        }
        .logo-center-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .project-title-main {
            font-size: 18px;
            font-weight: 800;
            color: #BC2121;
            margin: 0;
            line-height: 1.2;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: -0.3px;
        }
        .project-title-tagline {
            font-size: 12px;
            font-weight: 500;
            color: #444;
            margin: 5px 0 0 0;
            line-height: 1.45;
            font-family: 'Montserrat', sans-serif;
            font-style: italic;
        }

        /* RIGHT: UoH logo */
        .logo-right {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .logo-right a { display: inline-block; line-height: 0; }
        .logo-right img {
            max-height: 118px;
            width: auto;
            display: block;
        }

        /* ============================================================
           MENU BAR (BLUE)
           ============================================================ */
        .menu-bar-wrapper {
            background-color: #024283;
            padding: 0 !important;
        }
        .menu-bar-wrapper .navbar {
            background-color: #024283 !important;
            margin: 0;
            border: none;
            border-radius: 0;
            min-height: auto !important;
            padding: 0 !important;

        }
        .menu-bar-wrapper .navbar-collapse {
            background-color: #024283;
            border-top: none;
            padding: 0 !important;

        }
        /* Slightly widen the nav container (Bootstrap normally caps this at
           1170px) so the extra Login item has room to sit next to Contact
           without wrapping onto its own line. */
        .menu-bar-wrapper .container {
            width: 100% !important;
            max-width: 1300px !important;
            padding: 0 20px !important;
        }
        .menu-bar-wrapper .nav.navbar-nav {
            width: 100%;
            display: flex;
            flex-wrap: nowrap;
            justify-content: center;
            float: none;
            padding: 0 !important;
            margin: 0 !important;

        }
        .menu-bar-wrapper .nav.navbar-nav > li {
            position: relative;
            padding: 0 !important;
            margin: 0 !important;
        }
        .menu-bar-wrapper .nav.navbar-nav > li > a {
            color: #fff !important;
            font-weight: 600;
            padding: 3px 20px !important;
            transition: background 0.2s;
            font-size: 14px !important;
            line-height: 1 !important;
            height: auto !important;
            display: inline-block !important;
            white-space: nowrap;
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        .menu-bar-wrapper .nav.navbar-nav > li:last-child > a { border-right: none; }
        .menu-bar-wrapper .nav.navbar-nav > li > a i { margin-right: 4px; font-size: 18px; }
        .menu-bar-wrapper .nav.navbar-nav > li > a:hover,
        .menu-bar-wrapper .nav.navbar-nav > li.active > a { background-color: rgba(255,255,255,0.1); }

        /* Login's dropdown opens right-aligned since it's the rightmost item
           and would otherwise overflow past the edge of the bar. */
        .menu-bar-wrapper .nav.navbar-nav > li.nav-login .child-navigation {
            left: auto;
            right: 0;
        }

        /* Desktop dropdowns */
        .menu-bar-wrapper .child-navigation {
            background-color: #BC2121;
            border: 1px solid #a01818;
            position: absolute;
            top: 100% !important;
            left: 0;
            z-index: 9999;
            min-width: 220px;
            display: none;
            list-style: none;
            padding: 0px 0 !important;
            margin: 0 !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .menu-bar-wrapper .nav.navbar-nav > li:hover > .child-navigation,
        .menu-bar-wrapper .nav.navbar-nav > li.open > .child-navigation { display: block; }
        .menu-bar-wrapper .child-navigation li a {
            color: #fff;
            padding: 8px 15px !important;
            font-size: 14px;
            display: block !important;
        }
        .menu-bar-wrapper .child-navigation li a i { margin-right: 6px; font-size: 16px; }
        .menu-bar-wrapper .child-navigation li a:hover { background-color: rgba(0,0,0,0.15); }

        /* Hamburger button */
        .navbar-header button.navbar-toggle {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 10px 12px !important;
            outline: none !important;
        }
        .navbar-header button.navbar-toggle:hover,
        .navbar-header button.navbar-toggle:focus,
        .navbar-header button.navbar-toggle:active {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
        }
        .navbar-toggle .icon-bar {
            background-color: #fff !important;
            height: 2px !important;
            width: 22px !important;
            border-radius: 2px !important;
            display: block;
            margin: 5px 0 !important;
        }

        /* ============================================================
           RESPONSIVE — TABLET 768px–991px
           ============================================================ */
        @media (min-width: 768px) and (max-width: 991px) {
            .logo-section { padding: 10px 0; }
            .logo-left-img  { max-height: 72px; }
            .logo-pair-img  { max-height: 72px; }
            .logo-right img { max-height: 66px; }
            .project-title-main    { font-size: 16px; }
            .project-title-tagline { font-size: 11px; }
            .logo-center-link { gap: 10px; }
            .menu-bar-wrapper .nav.navbar-nav > li > a {
                padding: 5px 13px !important;
                font-size: 13px !important;
            }
            .menu-bar-wrapper .nav.navbar-nav > li > a i { font-size: 17px; }
            .menu-bar-wrapper .child-navigation li a i { font-size: 15px; }
            .logo-left::after {
                height: 52px;
            }
        }

        /* ============================================================
           RESPONSIVE — MOBILE <=767px
           ============================================================ */
        @media (max-width: 767px) {

            .logo-section { padding: 8px 0; }

            .logo-container-split {
                flex-wrap: nowrap;
                align-items: center;
                justify-content: space-between;
                gap: 6px;
                padding: 0 8px;
                box-sizing: border-box;
                width: 100%;
            }

            .logo-left,
            .logo-right {
                flex: 0 0 auto;
            }

            .logo-center {
                flex: 1 1 auto;
                min-width: 0;
                overflow: hidden;
            }
            .logo-center-link {
                gap: 8px;
                justify-content: center;
                align-items: center;
                flex-wrap: nowrap;
            }

            .logo-left-img  { max-height: 78px !important; width: auto; }
            .logo-pair-img  { max-height: 78px !important; flex-shrink: 0; width: 70px; margin-left: 0; }
            .logo-right img { max-height: 78px !important; width: auto; max-width: 200px; }

            .logo-center-text { min-width: 0; overflow: hidden; }
            .project-title-main {
                font-size: 11px !important;
                line-height: 1.25;
                white-space: normal;
                word-break: break-word;
                margin: 0;
            }
            .project-title-tagline {
                font-size: 9px !important;
                margin-top: 3px;
                line-height: 1.3;
                white-space: normal;
                word-break: break-word;
                display: block;
            }

            .logo-left::after { display: none; }

            .menu-bar-wrapper .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .menu-bar-wrapper .primary-navigation-wrapper { padding: 0 !important; margin: 0 !important; }
            .menu-bar-wrapper .navbar-header {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                width: 100%;
                padding: 4px 15px;
                min-height: 30px;
                box-sizing: border-box;
            }
            .navbar-header button.navbar-toggle { padding: 6px 10px !important; }
            .navbar-toggle .icon-bar { margin: 4px 0 !important; }
            .menu-bar-wrapper .navbar-collapse.in,
            .menu-bar-wrapper .navbar-collapse.collapsing {
                max-height: 70vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                background-color: #024283;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                display: block !important;
                box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            }
            .menu-bar-wrapper .nav.navbar-nav {
                width: 100% !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .menu-bar-wrapper .nav.navbar-nav > li {
                width: 100%;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                float: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .menu-bar-wrapper .nav.navbar-nav > li:last-child { border-bottom: none; }
            .menu-bar-wrapper .nav.navbar-nav > li.nav-login .child-navigation {
                right: auto;
            }
            .menu-bar-wrapper .nav.navbar-nav > li > a {
                display: flex !important;
                align-items: center;
                width: 100% !important;
                box-sizing: border-box;
                text-align: left;
                padding: 13px 20px !important;
                font-size: 14px !important;
                border: none !important;
                margin: 0 !important;
                line-height: 1.4;
                height: auto !important;
            }
            .menu-bar-wrapper .nav.navbar-nav > li > a i {
                margin-right: 10px;
                min-width: 16px;
                text-align: center;
                font-size: 18px;
            }
            .menu-bar-wrapper .nav.navbar-nav > li > a.has-child::after {
                content: '▾';
                font-size: 11px;
                opacity: 0.8;
                margin-left: auto;
                transition: transform 0.2s;
            }
            .menu-bar-wrapper .nav.navbar-nav > li.open > a.has-child::after { transform: rotate(180deg); }

            .menu-bar-wrapper .child-navigation {
                position: static !important;
                display: none !important;
                width: 100% !important;
                box-shadow: none;
                border: none !important;
                background-color: rgba(0,0,0,0.2) !important;
                padding: 0 !important;
                margin: 0 !important;
                border-left: 3px solid rgba(255,255,255,0.25) !important;
                box-sizing: border-box;
            }
            .menu-bar-wrapper .nav.navbar-nav > li:hover > .child-navigation { display: none !important; }
            .menu-bar-wrapper .nav.navbar-nav > li.open > .child-navigation  { display: block !important; }
            .menu-bar-wrapper .child-navigation li {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100%;
            }
            .menu-bar-wrapper .child-navigation li a {
                padding: 11px 20px 11px 44px !important;
                font-size: 13px !important;
                display: flex !important;
                align-items: center;
                border: none !important;
                color: rgba(255,255,255,0.9) !important;
                width: 100%;
                box-sizing: border-box;
                border-bottom: 1px solid rgba(255,255,255,0.06) !important;
            }
            .menu-bar-wrapper .child-navigation li:last-child a { border-bottom: none !important; }
            .menu-bar-wrapper .child-navigation li a i {
                margin-right: 8px;
                min-width: 14px;
                text-align: center;
                opacity: 0.8;
                font-size: 16px;
            }
        }

        /* ============================================================
           RESPONSIVE — SMALL MOBILE <=480px
           ============================================================ */
        @media (max-width: 480px) {
            .logo-section { padding: 6px 0; }
            .logo-container-split { gap: 5px !important; padding: 0 6px; justify-content: space-between; }
            .logo-left-img  { max-height: 58px !important; width: auto; }
            .logo-pair-img  { max-height: 58px !important; width: 52px; }
            .logo-right img { max-height: 58px !important; width: auto; max-width: 150px; }
            .logo-center-link { gap: 6px; }
            .project-title-main    { font-size: 6px !important; }
            .project-title-tagline { font-size: 5px  !important; margin-top: 2px; }
            .menu-bar-wrapper .navbar-header { padding: 3px 12px; min-height: 26px; }
            .menu-bar-wrapper .navbar-collapse.in { max-height: 65vh; }
            .menu-bar-wrapper .nav.navbar-nav > li > a { padding: 11px 15px !important; font-size: 13px !important; }
            .menu-bar-wrapper .nav.navbar-nav > li > a i { font-size: 17px; }
            .menu-bar-wrapper .child-navigation li a { padding: 10px 15px 10px 38px !important; font-size: 12px !important; }
            .menu-bar-wrapper .child-navigation li a i { font-size: 15px; }
        }

        /* ============================================================
           RESPONSIVE — VERY SMALL <=360px
           ============================================================ */
        @media (max-width: 360px) {
            .logo-container-split { gap: 4px !important; padding: 0 5px; justify-content: space-between; }
            .logo-left-img  { max-height: 50px !important; width: auto; }
            .logo-pair-img  { max-height: 50px !important; width: 46px; }
            .logo-right img { max-height: 50px !important; width: auto; max-width: 124px; }
            .logo-center-link { gap: 5px; }
            .project-title-main    { font-size: 9px !important; }
            .project-title-tagline { display: none !important; }
        }
        <?php if ($isHomePage): ?>
        /* ============================================================
           FULL SCREEN PRELOADER (CINEMATIC DARK OVERLAY & BLUR)
           ============================================================ */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 1;
            box-sizing: border-box;
            
            /* Preloader Dissolution Transitions */
            transition: backdrop-filter 500ms ease-in-out, -webkit-backdrop-filter 500ms ease-in-out, opacity 500ms ease-in-out;
        }

        body:not(.preloader-active) #preloader {
            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);
            opacity: 0;
        }

        #preloader .preloader-logo-container {
            width: 240px;
            height: 180px;
            display: block;
            mix-blend-mode: multiply;
        }

        #preloader svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Preloader CSS Animations */
        .layer-arc-5 {
            opacity: 0;
            animation: preloader-fade-in 250ms ease-in-out forwards;
            animation-delay: 0ms;
        }
        .layer-arc-4 {
            opacity: 0;
            animation: preloader-fade-in 250ms ease-in-out forwards;
            animation-delay: 200ms;
        }
        .layer-arc-3 {
            opacity: 0;
            animation: preloader-fade-in 250ms ease-in-out forwards;
            animation-delay: 400ms;
        }
        .layer-arc-2 {
            opacity: 0;
            animation: preloader-fade-in 250ms ease-in-out forwards;
            animation-delay: 600ms;
        }
        .layer-arc-1 {
            opacity: 0;
            animation: preloader-fade-in 250ms ease-in-out forwards;
            animation-delay: 800ms;
        }

        .layer-dot {
            opacity: 0;
            transform-origin: 60px 31px;
            animation: preloader-dot-animation 250ms ease-in-out forwards;
            animation-delay: 1000ms;
        }

        .layer-text {
            opacity: 0;
            animation: preloader-text-animation 250ms ease-in-out forwards;
            animation-delay: 1150ms;
        }

        .layer-final {
            opacity: 0;
            animation: preloader-fade-in 100ms ease-in-out forwards;
            animation-delay: 1400ms;
        }

        @keyframes preloader-fade-in {
            to {
                opacity: 1;
            }
        }

        @keyframes preloader-dot-animation {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1.0);
            }
        }

        @keyframes preloader-text-animation {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        body.preloader-active {
            overflow: hidden !important;
            height: 100vh !important;
        }

        #preloader .preloader-loading-block {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            opacity: 0;
            animation: preloader-fade-in 400ms ease-in-out 1600ms forwards;
        }

        #preloader .preloader-text {
            color: #024283;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            display: flex;
        }

        #preloader .preloader-text span {
            display: inline-block;
            animation: preloader-wave 1.4s ease-in-out infinite;
        }

        #preloader .preloader-text span:nth-child(1)  { animation-delay: 0.00s; }
        #preloader .preloader-text span:nth-child(2)  { animation-delay: 0.08s; }
        #preloader .preloader-text span:nth-child(3)  { animation-delay: 0.16s; }
        #preloader .preloader-text span:nth-child(4)  { animation-delay: 0.24s; }
        #preloader .preloader-text span:nth-child(5)  { animation-delay: 0.32s; }
        #preloader .preloader-text span:nth-child(6)  { animation-delay: 0.40s; }
        #preloader .preloader-text span:nth-child(7)  { animation-delay: 0.48s; }
        #preloader .preloader-text span:nth-child(8)  { animation-delay: 0.56s; }
        #preloader .preloader-text span:nth-child(9)  { animation-delay: 0.64s; }
        #preloader .preloader-text span:nth-child(10) { animation-delay: 0.72s; }

        @keyframes preloader-wave {
            0%, 40%, 100% { transform: translateY(0); }
            20% { transform: translateY(-4px); }
        }

        #preloader .preloader-bar-track {
            width: 140px;
            height: 3px;
            background: rgba(2, 66, 131, 0.12);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
        }

        #preloader .preloader-bar-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 45%;
            background: linear-gradient(90deg, #024283 0%, #0369a1 50%, #024283 100%);
            border-radius: 20px;
            background-size: 200% 100%;
            animation: preloader-shimmer 1.3s ease-in-out infinite;
        }

        @keyframes preloader-shimmer {
            0%   { left: -60%; }
            100% { left: 110%; }
        }
        <?php endif; ?>
    </style>
    <script>
        (function() {
            var isHomePage = <?php echo json_encode($isHomePage); ?>;
            if (isHomePage) {
                var hasLoadedHome = sessionStorage.getItem('homePageLoaded');
                var isRefresh = false;
                try {
                    if (window.performance && window.performance.getEntriesByType) {
                        var navs = window.performance.getEntriesByType('navigation');
                        if (navs.length > 0 && navs[0].type === 'reload') {
                            isRefresh = true;
                        }
                    } else if (window.performance && window.performance.navigation) {
                        if (window.performance.navigation.type === 1) {
                            isRefresh = true;
                        }
                    }
                } catch(e) {}
                
                if (!hasLoadedHome || isRefresh) {
                    console.log("Preloader: Home page first load or refresh detected. Displaying preloader.");
                    return;
                }
            }
            // Otherwise, inject style to hide preloader immediately and run normal flow
            var style = document.createElement('style');
            style.innerHTML = '#preloader { display: none !important; opacity: 0 !important; pointer-events: none !important; }';
            document.head.appendChild(style);
        })();

        document.addEventListener("DOMContentLoaded", function() {
            var isHomePage = <?php echo json_encode($isHomePage); ?>;
            var preloader = document.getElementById("preloader");
            if (!preloader) return;

            // Check if we should show the preloader
            var hasLoadedHome = sessionStorage.getItem('homePageLoaded');
            var isRefresh = false;
            try {
                if (window.performance && window.performance.getEntriesByType) {
                    var navs = window.performance.getEntriesByType('navigation');
                    if (navs.length > 0 && navs[0].type === 'reload') {
                        isRefresh = true;
                    }
                } else if (window.performance && window.performance.navigation) {
                    if (window.performance.navigation.type === 1) {
                        isRefresh = true;
                    }
                }
            } catch(e) {}

            var showPreloader = isHomePage && (!hasLoadedHome || isRefresh);

            if (showPreloader) {
                console.log("Preloader: Activating preloader.");
                document.body.classList.add("preloader-active");

                var animationDone = false;
                var assetsLoaded = false;

                function attemptDismiss() {
                    if (animationDone && assetsLoaded) {
                        console.log("Preloader: Holding time complete and assets loaded. Dissolving preloader.");
                        document.body.classList.remove("preloader-active");
                        
                        // Mark as loaded so next visits in the same session bypass the preloader
                        sessionStorage.setItem('homePageLoaded', 'true');

                        setTimeout(function() {
                            console.log("Preloader: Dissolve complete, removing preloader and starting autoplay.");
                            if (preloader) {
                                preloader.remove();
                            }
                            if (typeof window.startSliderAutoplay === "function") {
                                window.startSliderAutoplay();
                            }
                        }, 500);
                    }
                }

                // 1. Minimum animation duration (1.9s)
                setTimeout(function() {
                    animationDone = true;
                    attemptDismiss();
                }, 1900);

                // 2. Wait for slider images
                var sliderImg = document.querySelector("#homepage-slider img");
                if (sliderImg) {
                    if (sliderImg.complete) {
                        assetsLoaded = true;
                        attemptDismiss();
                    } else {
                        sliderImg.addEventListener("load", function() {
                            console.log("Preloader: Slider image loaded.");
                            assetsLoaded = true;
                            attemptDismiss();
                        });
                        sliderImg.addEventListener("error", function() {
                            console.log("Preloader: Slider image load error.");
                            assetsLoaded = true;
                            attemptDismiss();
                        });
                    }
                } else {
                    assetsLoaded = true;
                    attemptDismiss();
                }
            } else {
                // Not showing preloader - clean up preloader element immediately
                console.log("Preloader: Bypassing preloader.");
                if (preloader) {
                    preloader.remove();
                }
                // Start autoplay immediately if available
                if (typeof window.startSliderAutoplay === "function") {
                    window.startSliderAutoplay();
                } else {
                    // Check again after a tiny timeout or on window load just in case
                    window.addEventListener("load", function() {
                        if (typeof window.startSliderAutoplay === "function") {
                            window.startSliderAutoplay();
                        }
                    });
                }
            }
        });
    </script>
</head>

<body class="page-homepage-courses">
<?php if ($isHomePage): ?>
<div id="preloader">
    <div class="preloader-logo-container">
        <svg viewBox="0 0 120 90" xmlns:xlink="http://www.w3.org/1999/xlink">
            <defs>
                <clipPath id="clip-bottom">
                    <rect x="0" y="20" width="120" height="70" />
                </clipPath>
                <clipPath id="clip-text">
                    <rect x="0" y="0" width="120" height="20" />
                </clipPath>
                <clipPath id="clip-dot">
                    <circle cx="60" cy="31" r="9" />
                </clipPath>
                
                <!-- SVG concentric arc masks -->
                <clipPath id="clip-ring-1">
                    <path d="M 60,11.5 A 19.5,19.5 0 1,0 60,50.5 A 19.5,19.5 0 1,0 60,11.5
                             M 60,21 A 10,10 0 1,1 60,41 A 10,10 0 1,1 60,21" clip-rule="evenodd" />
                </clipPath>
                <clipPath id="clip-ring-2">
                    <path d="M 60,4 A 27,27 0 1,0 60,58 A 27,27 0 1,0 60,4
                             M 60,11.5 A 19.5,19.5 0 1,1 60,50.5 A 19.5,19.5 0 1,1 60,11.5" clip-rule="evenodd" />
                </clipPath>
                <clipPath id="clip-ring-3">
                    <path d="M 60,-4 A 35,35 0 1,0 60,66 A 35,35 0 1,0 60,-4
                             M 60,4 A 27,27 0 1,1 60,58 A 27,27 0 1,1 60,4" clip-rule="evenodd" />
                </clipPath>
                <clipPath id="clip-ring-4">
                    <path d="M 60,-11.5 A 42.5,42.5 0 1,0 60,73.5 A 42.5,42.5 0 1,0 60,-11.5
                             M 60,-4 A 35,35 0 1,1 60,66 A 35,35 0 1,1 60,-4" clip-rule="evenodd" />
                </clipPath>
                <clipPath id="clip-ring-5">
                    <path d="M 60,-27 A 58,58 0 1,0 60,89 A 58,58 0 1,0 60,-27
                             M 60,-11.5 A 42.5,42.5 0 1,1 60,73.5 A 42.5,42.5 0 1,1 60,-11.5" clip-rule="evenodd" />
                </clipPath>
            </defs>
            
            <!-- Animated SVG layers using the original 2.png image -->
            <g class="layer-text" clip-path="url(#clip-text)">
                <image href="2.png" xlink:href="2.png" x="0" y="0" width="120" height="90" />
            </g>
            <g class="layer-dot" clip-path="url(#clip-dot)">
                <image href="2.png" xlink:href="2.png" x="0" y="0" width="120" height="90" />
            </g>
            <g clip-path="url(#clip-bottom)">
                <image class="layer-arc-5" href="2.png" xlink:href="2.png" x="0" y="0" width="120" height="90" clip-path="url(#clip-ring-5)" />
                <image class="layer-arc-4" href="2.png" xlink:href="2.png" x="0" y="0" width="120" height="90" clip-path="url(#clip-ring-4)" />
                <image class="layer-arc-3" href="2.png" xlink:href="2.png" x="0" y="0" width="120" height="90" clip-path="url(#clip-ring-3)" />
                <image class="layer-arc-2" href="2.png" xlink:href="2.png" x="0" y="0" width="120" height="90" clip-path="url(#clip-ring-2)" />
                <image class="layer-arc-1" href="2.png" xlink:href="2.png" x="0" y="0" width="120" height="90" clip-path="url(#clip-ring-1)" />
            </g>
            
            <!-- Final overlay of original unclipped image to guarantee absolute pixel-identity -->
            <image class="layer-final" href="2.png" xlink:href="2.png" x="0" y="0" width="120" height="90" />
        </svg>
    </div>

    <!-- Loading text + progress bar -->
    <div class="preloader-loading-block">
        <div class="preloader-text" aria-label="Loading">
            <span>L</span><span>O</span><span>A</span><span>D</span><span>I</span><span>N</span><span>G</span><span>&nbsp;</span><span>.</span><span>.</span>
        </div>
        <div class="preloader-bar-track">
            <div class="preloader-bar-fill"></div>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="wrapper">

<!-- ===== HEADER ===================================================== -->
<div class="navigation-wrapper">

    <?php include 'topbar.php'; ?>

    <!-- Logo Section -->
    <div class="logo-section">
        <div class="container">
            <div class="logo-container-split">

                <!-- LEFT: ANRF logo -->
                <div class="logo-left">
                    <a href="https://anrfonline.in/ANRF/HomePage">
                        <img src="logos/ANRF Image.png" alt="ANRF Logo" class="logo-left-img">
                    </a>
                </div>

                <!-- CENTER: PAIR badge + title text -->
                <div class="logo-center">
                    <a href="index.php" class="logo-center-link">
                        <img src="2.png" alt="ANRF-PAIR Logo" class="logo-pair-img">
                    </a>
                </div>

                <!-- RIGHT: University of Hyderabad logo -->
                <div class="logo-right">
                    <a href="https://uohyd.ac.in/">
                        <img src="3.png" alt="University of Hyderabad Logo">
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Menu Bar (blue) -->
    <div class="menu-bar-wrapper">
        <div class="primary-navigation-wrapper">
            <header class="navbar" id="top" role="banner">
                <div class="container">

                    <!-- Mobile hamburger -->
                    <div class="navbar-header">
                        <button class="navbar-toggle collapsed" type="button"
                                data-toggle="collapse" data-target=".bs-navbar-collapse"
                                aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>

                    <!-- Nav links -->
                    <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
                        <ul class="nav navbar-nav">

                            <!-- 1. Home -->
                            <li class="<?php echo ($activePage === 'home') ? 'active' : ''; ?>">
                                <a href="index.php"><i class="fa fa-home"></i> Home</a>
                            </li>

                            <!-- 2. About -->
                            <li class="<?php echo ($activePage === 'about') ? 'active' : ''; ?>">
                                <a href="#" class="has-child"><i class="fa fa-info-circle"></i> About</a>
                                <ul class="list-unstyled child-navigation">
                                    <li><a href="about-us.php"><i class="fa fa-book"></i> About the Project</a></li>
                                    <li><a href="outcomes_impact.php"><i class="fa fa-star"></i> Outcomes &amp; Impact</a></li>
                                    <li><a href="acknowledgment.php"><i class="fa fa-trophy"></i> Acknowledgment</a></li>
                                </ul>
                            </li>

                            <!-- 3. Institutions -->
                            <li class="<?php echo ($activePage === 'institutions') ? 'active' : ''; ?>">
                                <a href="#" class="has-child"><i class="fa fa-building-o"></i> Institutions</a>
                                <ul class="list-unstyled child-navigation">
                                    <li><a href="participating-institutions.php"><i class="fa fa-sitemap"></i> Participating Institutions</a></li>
                                    <li><a href="collobrations.php"><i class="fa fa-exchange"></i> Collaborations</a></li>
                                    <li><a href="institute.php"><i class="fa fa-exchange"></i> Institutions KPIs</a></li>
                                </ul>
                            </li>

                            <!-- 4. Team -->
                            <li class="<?php echo ($activePage === 'team') ? 'active' : ''; ?>">
                                <a href="team.php"><i class="fa fa-users"></i> Team</a>
                            </li>

                            <!-- 5. Research & Infrastructure -->
                            <li class="<?php echo ($activePage === 'research') ? 'active' : ''; ?>">
                                <a href="#" class="has-child"><i class="fa fa-flask"></i> Research &amp; Infrastructure</a>
                                <ul class="list-unstyled child-navigation">
                                    <li><a href="research_areas.php"><i class="fa fa-lightbulb-o"></i> Research Areas</a></li>
                                    <li><a href="infrastructure-facilities.php"><i class="fa fa-cog"></i> Infrastructure &amp; Facilities</a></li>
                                    <li><a href="work-plan-activities.php"><i class="fa fa-tasks"></i> Work Plan &amp; Activities</a></li>
                                    <li><a href="internships.php"><i class="fa fa-briefcase"></i> Internships</a></li>

                                </ul>
                            </li>

                            <!-- 6. Resources & Gallery -->
                            <li class="<?php echo ($activePage === 'resources') ? 'active' : ''; ?>">
                                <a href="#" class="has-child"><i class="fa fa-folder-open"></i> Resources &amp; Gallery</a>
                                <ul class="list-unstyled child-navigation">
                                    <li><a href="publications-reports.php"><i class="fa fa-book"></i> Publications &amp; Reports</a></li>
                                    <li><a href="patents-innovations.php"><i class="fa fa-lightbulb-o"></i> Patents &amp; Innovations</a></li>
                                    <li><a href="downloads.php"><i class="fa fa-download"></i> Downloads (Forms/SOPs)</a></li>
                                    <li><a href="events_activities.php"><i class="fa fa-calendar"></i> Events &amp; Activities</a></li>
                                    <li><a href="conferences.php"><i class="fa fa-microphone"></i> Conferences</a></li>
                                    <li><a href="webinars.php"><i class="fa fa-video-camera"></i> Webinars</a></li>
                                    <li><a href="progress_reports.php"><i class="fa fa-signal"></i> Progress Reports</a></li>
                                    <li><a href="gallery.php"><i class="fa fa-camera"></i> Photo &amp; Video Gallery</a></li>
                                </ul>
                            </li>

                            <!-- 7. Contact -->
                            <li class="<?php echo ($activePage === 'contact') ? 'active' : ''; ?>">
                                <a href="#" class="has-child"><i class="fa fa-envelope"></i> Contact</a>
                                <ul class="list-unstyled child-navigation">
                                    <li><a href="contact-us.php"><i class="fa fa-phone"></i> Contact Us</a></li>
                                </ul>
                            </li>

                        </ul>
                    </nav>
                </div>
            </header>
        </div>
    </div>

    <!-- Background strip -->
    <div class="background">
        <img src="assets/img/background-city.png" alt="background">
    </div>

</div><!-- /.navigation-wrapper -->
<!-- ===== END HEADER ================================================= -->

<script>
document.addEventListener('DOMContentLoaded', function () {

    var isMobile = function () { return window.innerWidth <= 767; };

    /* Mobile dropdown toggles */
    document.querySelectorAll('.nav.navbar-nav > li > a.has-child').forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (!isMobile()) return;
            e.preventDefault();
            e.stopPropagation();
            var parentLi = this.closest('li');
            var isOpen   = parentLi.classList.contains('open');
            document.querySelectorAll('.nav.navbar-nav > li.open').forEach(function (li) {
                if (li !== parentLi) li.classList.remove('open');
            });
            parentLi.classList.toggle('open', !isOpen);
        });
    });

    /* Close menu when clicking outside */
    document.addEventListener('click', function (e) {
        if (!isMobile()) return;
        if (!e.target.closest('.nav.navbar-nav')) {
            document.querySelectorAll('.nav.navbar-nav > li.open').forEach(function (li) {
                li.classList.remove('open');
            });
        }
    });

    /* Clear open states when resizing to desktop */
    window.addEventListener('resize', function () {
        if (!isMobile()) {
            document.querySelectorAll('.nav.navbar-nav > li.open').forEach(function (li) {
                li.classList.remove('open');
            });
        }
    });

});
</script>