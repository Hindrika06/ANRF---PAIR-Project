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
            padding: 10px 0;
            overflow: visible; /* never clip logos */
        }

        /* Three-column flex row — perfect vertical centre */
        .logo-container-split {
            display: flex;
            align-items: center;        /* vertical centre all columns   */
            justify-content: space-between;
            gap: 12px;
            overflow: visible;          /* prevent arc / image clipping  */
        }

        /* Grey separator line — left section only */
        .logo-left::after {
            content: '';
            width: 1px;
            height: 60px;
            background-color: #d0d0d0;
            margin: 0 15px;
        }

        /* LEFT: ANRF (Anusandhan National Research Foundation) logo */
        .logo-left {
            flex: 1;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
        .logo-left a { display: inline-block; line-height: 0; }
        .logo-left-img {
            max-height: 85px;   /* fits comfortably inside the bar */
            width: auto;
            display: block;
        }

        /* CENTER: ANRF-PAIR logo badge
           ────────────────────────────────────────────────────────
           2.png is a landscape image (wider than tall). The logo
           has "ANRF-PAIR" text at the top and 9 semicircular arcs
           fanning downward. A max-height that is too large causes
           the top text to escape the header bar; too small clips
           the bottom arcs. 70px is the sweet-spot: text and arcs
           both fully visible with even padding above and below.
        */
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
            max-height: 70px;           /* shows full logo incl. arcs    */
            width: auto;
            flex-shrink: 0;
            display: block;
            /* ── STATIC: strip every animation / transition ────── */
            animation: none !important;
            transition: none !important;
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

        /* RIGHT: University of Hyderabad logo */
        .logo-right {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .logo-right a { display: inline-block; line-height: 0; }
        .logo-right img {
            max-height: 90px;           /* matches left logo height      */
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
            .logo-section { padding: 8px 0; }
            .logo-left-img  { max-height: 68px; }
            .logo-pair-img  { max-height: 62px; } /* static, no animation */
            .logo-right img { max-height: 68px; }
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

            .logo-left-img  { max-height: 72px !important; width: auto; }
            .logo-pair-img  { max-height: 66px !important; flex-shrink: 0; width: auto; margin-left: 0; animation: none !important; transition: none !important; }
            .logo-right img { max-height: 72px !important; width: auto; max-width: 200px; }

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
            .logo-left-img  { max-height: 52px !important; width: auto; }
            .logo-pair-img  { max-height: 48px !important; width: auto; animation: none !important; transition: none !important; }
            .logo-right img { max-height: 52px !important; width: auto; max-width: 150px; }
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
           PRELOADER — stroke-dashoffset SVG arc animation
           Animation sequence (all times from page paint):
             t=0ms–850ms  : Arc 1 (outermost) draws  [delay 0ms]
             t=150ms–1000ms: Arc 2 draws              [delay 150ms]
             t=300ms–1150ms: Arc 3 draws              [delay 300ms]
             t=450ms–1300ms: Arc 4 draws              [delay 450ms]
             t=600ms–1450ms: Arc 5 (innermost) draws  [delay 600ms]
             t=1500ms     : Red dot elastic-pops
             t=1900ms     : "ANRF-PAIR" text fades in
           ============================================================ */

        body.preloader-active {
            overflow: hidden !important;
            height: 100vh !important;
        }

        /* ── Overlay ──────────────────────────────────────── */
        #preloader {
            position: fixed;
            inset: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 999999;
            opacity: 1;
            transition: backdrop-filter 500ms ease-in-out,
                        -webkit-backdrop-filter 500ms ease-in-out,
                        opacity 500ms ease-in-out;
        }

        body:not(.preloader-active) #preloader {
            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);
            opacity: 0;
            pointer-events: none;
        }

        /* ── SVG canvas ───────────────────────────────────── */
        #preloader .preloader-logo-container {
            width: clamp(220px, 38vmin, 360px);
            height: auto;
            display: block;
        }

        #preloader svg {
            width: 100%;
            height: auto;
            display: block;
            overflow: visible;
        }

        /* ── Arcs — shared ────────────────────────────────── */
        /*
         * All arcs are lower semicircles, center (200,118).
         * Path formula: M (200−r) 118  A r r 0 0 1 (200+r) 118
         * stroke-dasharray = πr (half circumference of full circle)
         *
         *  Arc  Radius   πr (rounded)
         *  1    155      487
         *  2    125      393
         *  3     95      298
         *  4     65      204
         *  5     35      110
         */
        .pl-arc {
            fill: none;
            stroke: #b8aed4;
            stroke-width: 7;
            stroke-linecap: round;
            animation-name: pl-draw-arc;
            animation-duration: 850ms;
            animation-timing-function: cubic-bezier(0.37, 0, 0.63, 1);
            animation-fill-mode: both;
        }

        /* Arc 1 — outermost, r=155, circ≈487 */
        .pl-arc-1 { stroke-dasharray: 487; stroke-dashoffset: 487; animation-delay: 0ms;   }
        /* Arc 2 — r=125, circ≈393 */
        .pl-arc-2 { stroke-dasharray: 393; stroke-dashoffset: 393; animation-delay: 150ms; }
        /* Arc 3 — r=95, circ≈298 */
        .pl-arc-3 { stroke-dasharray: 298; stroke-dashoffset: 298; animation-delay: 300ms; }
        /* Arc 4 — r=65, circ≈204 */
        .pl-arc-4 { stroke-dasharray: 204; stroke-dashoffset: 204; animation-delay: 450ms; }
        /* Arc 5 — innermost, r=35, circ≈110 */
        .pl-arc-5 { stroke-dasharray: 110; stroke-dashoffset: 110; animation-delay: 600ms; }

        @keyframes pl-draw-arc {
            to { stroke-dashoffset: 0; }
        }

        /* ── Red centre dot — elastic pop ─────────────────── */
        .pl-dot {
            transform-box: fill-box;
            transform-origin: center;
            transform: scale(0);
            animation: pl-pop-dot 650ms cubic-bezier(0.34, 1.56, 0.64, 1) 1500ms both;
        }

        @keyframes pl-pop-dot {
            0%   { transform: scale(0); }
            100% { transform: scale(1); }
        }

        /* ── "ANRF-PAIR" text — fade + rise ──────────────── */
        .pl-text {
            transform-box: fill-box;
            transform-origin: center;
            opacity: 0;
            animation: pl-fade-text 550ms ease-out 1900ms both;
        }

        @keyframes pl-fade-text {
            from { opacity: 0; transform: translateY(5px); }
            to   { opacity: 1; transform: translateY(0);   }
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

                // 1. Minimum animation duration — must exceed the full animation sequence:
                //    Last arc finishes at ~1450ms, dot pops at ~2150ms, text fades at ~2450ms.
                //    Adding 250ms buffer → 2700ms total minimum hold.
                setTimeout(function() {
                    animationDone = true;
                    attemptDismiss();
                }, 2700);

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
<!--
  ╔══════════════════════════════════════════════════════════════╗
  ║  ANRF-PAIR PRELOADER — stroke-dashoffset SVG arc animation  ║
  ║  viewBox : 0 0 400 300                                       ║
  ║  Arc center : (200, 118)  — all arcs are lower semicircles   ║
  ║  Arc order  : drawn outside-in, arc-1 first, arc-5 last      ║
  ╚══════════════════════════════════════════════════════════════╝
-->
<div id="preloader" role="status" aria-label="Loading ANRF-PAIR, please wait">
    <div class="preloader-logo-container">
        <svg viewBox="0 0 400 300"
             xmlns="http://www.w3.org/2000/svg"
             aria-hidden="true">

            <defs>
                <!--
                  Rich radial gradient for the red dot:
                  bright highlight top-left, deep crimson core, near-black edge.
                -->
                <radialGradient id="pl-dot-grad" cx="36%" cy="30%" r="68%">
                    <stop offset="0%"   stop-color="#ff5252"/>
                    <stop offset="50%"  stop-color="#c62828"/>
                    <stop offset="100%" stop-color="#7b0000"/>
                </radialGradient>
            </defs>

            <!--
              ┌─────────────────────────────────────────────────────┐
              │  5 CONCENTRIC ARCS — drawn OUTSIDE → IN            │
              │                                                     │
              │  All arcs share centre point (200, 118).           │
              │  Each is a lower semicircle (sweep-flag = 1).       │
              │  Path formula:                                      │
              │    M (200−r) 118  A r r 0 0 1 (200+r) 118          │
              │                                                     │
              │  Arc  Radius  stroke-dasharray (=πr, rounded)      │
              │   1    155         487   ← animates first           │
              │   2    125         393                              │
              │   3     95         298                              │
              │   4     65         204                              │
              │   5     35         110   ← animates last            │
              └─────────────────────────────────────────────────────┘
            -->

            <!-- Arc 1 — outermost — r=155, dasharray=487 -->
            <path class="pl-arc pl-arc-1"
                  d="M 45 118 A 155 155 0 0 0 355 118"/>

            <!-- Arc 2 — r=125, dasharray=393 -->
            <path class="pl-arc pl-arc-2"
                  d="M 75 118 A 125 125 0 0 0 325 118"/>

            <!-- Arc 3 — r=95, dasharray=298 -->
            <path class="pl-arc pl-arc-3"
                  d="M 105 118 A 95 95 0 0 0 295 118"/>

            <!-- Arc 4 — r=65, dasharray=204 -->
            <path class="pl-arc pl-arc-4"
                  d="M 135 118 A 65 65 0 0 0 265 118"/>

            <!-- Arc 5 — innermost — r=35, dasharray=110 -->
            <path class="pl-arc pl-arc-5"
                  d="M 165 118 A 35 35 0 0 0 235 118"/>

            <!--
              RED CENTRE DOT
              Scales from 0→1 with elastic overshoot after arc-5 finishes.
              Positioned at the arc origin (200, 118), r=20.
            -->
            <circle class="pl-dot"
                    cx="200" cy="118" r="20"
                    fill="url(#pl-dot-grad)"/>

            <!--
              "ANRF-PAIR" LOGOTYPE
              Fades in + rises slightly immediately after the dot appears.
              Baseline at y=67, font-size 54, weight 900 — matching the logo.
            -->
            <text class="pl-text"
                  x="200" y="67"
                  text-anchor="middle"
                  font-family="'Montserrat', 'Arial Black', Arial, sans-serif"
                  font-weight="900"
                  font-size="54"
                  fill="#111111"
                  letter-spacing="-1.5">ANRF-PAIR</text>

        </svg>
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

                <!-- LEFT: Anusandhan National Research Foundation (ANRF) logo -->
                <div class="logo-left">
                    <a href="https://anrfonline.in/ANRF/HomePage" target="_blank" rel="noopener">
                        <img src="logos/ANRF Image.png"
                             alt="Anusandhan National Research Foundation"
                             class="logo-left-img">
                    </a>
                </div>

                <!-- CENTER: ANRF-PAIR logo — STATIC, no animations
                     2.png is the project badge (landscape, arcs open downward).
                     max-height:70px keeps text + all arcs within the bar.     -->
                <div class="logo-center">
                    <a href="index.php" class="logo-center-link">
                        <img src="2.png"
                             alt="ANRF-PAIR Project Logo"
                             class="logo-pair-img"
                             style="animation:none!important;transition:none!important;">
                    </a>
                </div>

                <!-- RIGHT: University of Hyderabad logo -->
                <div class="logo-right">
                    <a href="https://uohyd.ac.in/" target="_blank" rel="noopener">
                        <img src="3.png" alt="University of Hyderabad">
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
                                    <li><a href="institute.php"><i class="fa fa-bar-chart-o"></i> Institutions KPIs</a></li>
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