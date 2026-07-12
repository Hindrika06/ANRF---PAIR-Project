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
        justify-content: space-between !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .topbar-left {
        display: flex !important;
        align-items: center !important;
        gap: calc(var(--logo-h) * 0.4) !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .topbar-right {
        display: flex !important;
        align-items: center !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .topbar-left li, .topbar-right li {
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .topbar-left li a, .topbar-right li a {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        color: #fff !important;
        opacity: 0.95 !important;
        font-size: 14px !important; /* Set exactly to 14px */
        line-height: 1.3 !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        font-weight: bold !important; /* Bold as requested */
    }
    .topbar-left li a:hover, .topbar-right li a:hover {
        opacity: 1 !important;
        text-decoration: underline !important;
    }
    .topbar-left li a i, .topbar-right li a i {
        font-size: 13px !important;
        flex-shrink: 0 !important;
    }

    /* Dropdown menu positioning and styling */
    .topbar-login {
        position: relative !important;
    }
    .topbar-dropdown-menu {
        display: none !important;
        position: absolute !important;
        top: 100% !important;
        right: 0 !important;
        background-color: #BC2121 !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        list-style: none !important;
        padding: 0 !important;
        margin: calc(var(--logo-h) * 0.1) 0 0 0 !important;
        min-width: 160px !important;
        z-index: 10000 !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
        border-radius: 4px !important;
        overflow: hidden !important;
    }
    .topbar-dropdown-menu li {
        margin: 0 !important;
        padding: 0 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    .topbar-dropdown-menu li:last-child {
        border-bottom: none !important;
    }
    .topbar-dropdown-menu li a {
        display: block !important;
        padding: 8px 15px !important;
        color: #fff !important;
        font-size: 13px !important; /* Dropdown items font size */
        text-decoration: none !important;
        transition: background-color 0.2s !important;
        opacity: 0.9 !important;
        text-align: left !important;
        font-weight: normal !important; /* normal weight inside the dropdown menu is cleaner */
    }
    .topbar-dropdown-menu li a:hover {
        background-color: rgba(0, 0, 0, 0.15) !important;
        opacity: 1 !important;
        text-decoration: none !important;
    }
    /* Show dropdown on hover */
    .topbar-login:hover .topbar-dropdown-menu,
    .topbar-login.open .topbar-dropdown-menu {
        display: block !important;
    }

    /* Tablet & mobile — wrap instead of scroll to prevent dropdown clipping */
    @media (max-width: 767px) {
        .topbar {
            padding: 8px 0 !important;
        }
        .topbar-inner {
            flex-wrap: wrap !important;
            justify-content: center !important;
            gap: 8px !important;
        }
        .topbar-left {
            flex-wrap: wrap !important;
            justify-content: center !important;
            gap: 12px !important;
            width: 100% !important;
        }
        .topbar-right {
            justify-content: center !important;
            width: 100% !important;
        }
        .topbar-left li a, .topbar-right li a {
            font-size: 13px !important; /* Mobile/tablet font size slightly scaled down to 13px */
        }
        .topbar-left li a i, .topbar-right li a i {
            font-size: 12px !important;
        }
        .topbar-dropdown-menu {
            min-width: 140px !important;
            margin-top: calc(var(--logo-h-tablet) * 0.1) !important;
        }
        .topbar-dropdown-menu li a {
            font-size: 12px !important;
            padding: 6px 12px !important;
            font-weight: normal !important;
        }
    }

    @media (max-width: 480px) {
        .topbar {
            padding: calc(var(--logo-h-mobile) * 0.16) 0 !important;
        }
        .topbar-inner {
            gap: 6px !important;
        }
        .topbar-left {
            gap: 8px !important;
        }
        .topbar-left li a, .topbar-right li a {
            font-size: calc(var(--logo-h-mobile) * 0.26) !important;
            gap: calc(var(--logo-h-mobile) * 0.12) !important;
        }
        .topbar-left li a i, .topbar-right li a i {
            font-size: calc(var(--logo-h-mobile) * 0.24) !important;
        }
        .topbar-dropdown-menu {
            margin-top: calc(var(--logo-h-mobile) * 0.1) !important;
        }
        .topbar-dropdown-menu li a {
            font-size: calc(var(--logo-h-mobile) * 0.24) !important;
        }
    }

    @media (max-width: 360px) {
        .topbar-left li a, .topbar-right li a {
            font-size: calc(var(--logo-h-mobile) * 0.22) !important;
        }
        .topbar-dropdown-menu li a {
            font-size: calc(var(--logo-h-mobile) * 0.2) !important;
        }
    }
</style>

<!-- Top bar (red) -->
<div class="topbar">
    <div class="container">
        <div class="topbar-inner">
            <ul class="topbar-left">
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
            <ul class="topbar-right">
                <li class="topbar-login">
                    <a href="#" class="topbar-dropdown-toggle">
                        <i class="fa fa-sign-in" aria-hidden="true"></i>
                        <span>Login <i class="fa fa-caret-down" aria-hidden="true" style="font-size: 10px; margin-left: 2px;"></i></span>
                    </a>
                    <ul class="topbar-dropdown-menu">
                        <li><a href="admin/index.php"><i class="fa fa-user" aria-hidden="true"></i> Hub Admin</a></li>
                        <li><a href="admin/index.php"><i class="fa fa-user" aria-hidden="true"></i> Admin</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var loginToggle = document.querySelector('.topbar-dropdown-toggle');
    if (loginToggle) {
        loginToggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var parentLi = this.closest('.topbar-login');
            if (parentLi) {
                parentLi.classList.toggle('open');
            }
        });
        document.addEventListener('click', function (e) {
            var parentLi = document.querySelector('.topbar-login');
            if (parentLi && !e.target.closest('.topbar-login')) {
                parentLi.classList.remove('open');
            }
        });
    }
});
</script>