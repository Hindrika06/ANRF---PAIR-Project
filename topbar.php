<?php
/**
 * topbar.php — ANRF–PAIR Project
 * Red contact bar shown above the logo/menu header.
 *
 * Usage: <?php include 'topbar.php'; ?>
 * Normally you don't need to include this directly — header.php
 * already includes it. Use this file on its own only if you need
 * the contact bar without the rest of the header.
 */
?>
<style>
    :root {
        --logo-h: 60px;      /* set this to your actual logo height */
        --logo-h-tablet: 48px;
        --logo-h-mobile: 38px;
    }

    /* ============================================================
       TOP BAR (RED) — contact strip, sized relative to logo height
       ============================================================ */
    .topbar {
        background-color: #BC2121 !important;
        /* padding derived from logo height instead of a fixed px value */
        padding: calc(var(--logo-h) * 0.15) 0 !important;
    }
    .topbar-inner {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: calc(var(--logo-h) * 0.4) !important;
        flex-wrap: wrap !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .topbar-inner li {
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .topbar-inner li a {
        display: inline-flex !important;
        align-items: center !important;
        gap: calc(var(--logo-h) * 0.12) !important;
        color: #fff !important;
        opacity: 0.95 !important;
        font-size: calc(var(--logo-h) * 0.22) !important;
        line-height: 1.3 !important;
        text-decoration: none !important;
        white-space: nowrap !important;
    }
    .topbar-inner li a:hover {
        opacity: 1 !important;
        text-decoration: underline !important;
    }
    .topbar-inner li a i {
        font-size: calc(var(--logo-h) * 0.2) !important;
        flex-shrink: 0 !important;
    }

    /* Tablet & mobile — scale with the smaller logo size used there */
    @media (max-width: 767px) {
        .topbar {
            padding: calc(var(--logo-h-tablet) * 0.15) 0 !important;
        }
        .topbar-inner {
            gap: calc(var(--logo-h-tablet) * 0.3) !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .topbar-inner li a {
            font-size: calc(var(--logo-h-tablet) * 0.24) !important;
        }
        .topbar-inner li a i {
            font-size: calc(var(--logo-h-tablet) * 0.22) !important;
        }
    }

    @media (max-width: 480px) {
        .topbar {
            padding: calc(var(--logo-h-mobile) * 0.16) 0 !important;
        }
        .topbar-inner {
            gap: calc(var(--logo-h-mobile) * 0.26) !important;
        }
        .topbar-inner li a {
            font-size: calc(var(--logo-h-mobile) * 0.26) !important;
            gap: calc(var(--logo-h-mobile) * 0.12) !important;
        }
        .topbar-inner li a i {
            font-size: calc(var(--logo-h-mobile) * 0.24) !important;
        }
    }

    @media (max-width: 360px) {
        .topbar-inner li a {
            font-size: calc(var(--logo-h-mobile) * 0.22) !important;
        }
    }
</style>

<!-- Top bar (red) -->
<div class="topbar">
    <div class="container">
        <ul class="topbar-inner">
            <li>
                <a href="mailto:pairdirecorate@uohyd.ac.in">
                    <i class="fa fa-envelope" aria-hidden="true"></i>
                    <span>pairdirecorate@uohyd.ac.in</span>
                </a>
            </li>
            <li>
                <a href="tel:914023134546">
                    <i class="fa fa-phone" aria-hidden="true"></i>
                    <span>040 2313 2309</span>
                </a>
            </li>
        </ul>
    </div>
</div>