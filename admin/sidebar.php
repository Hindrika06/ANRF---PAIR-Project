<!--**********************************
    Nav header start
***********************************-->
<!-- Added missing '#' to hex code and a soft shadow for depth -->
<div class="nav-header" style="background-color: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); z-index: 999;">
    <a href="index.html" class="brand-logo">
        <img src="logo/3.png" alt="University of Hyderabad Logo" class="logo-img">
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
.logo-img {
    max-height: 45px; 
    width: auto;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.logo-img:hover {
    transform: scale(1.03); /* Subtle pop when hovering over the logo */
}

/* --- Attractive Sidebar Styling --- */
.custom-sidebar {
    background:#024283;
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
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
    background: #1B3A6B;
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
</style>

<!--**********************************
    Sidebar start
***********************************-->
<!-- Added 'custom-sidebar' class for custom stylings -->
<div class="dlabnav custom-sidebar">
    <div class="dlabnav-scroll" style="display: flex; flex-direction: column; height: 100%;">
        <!-- Main Navigation Links -->
        <ul class="metismenu" id="menu" style="flex: 1;">
            <li>
                <a href="publications.php">
                    <i class="fas fa-book-open"></i>
                    <span class="nav-text">Publications</span>
                </a>
            </li>
            <li>
                <a href="patents.php" aria-expanded="false">
                    <i class="fas fa-certificate"></i>
                    <span class="nav-text">Patents</span>
                </a>
            </li>
            <li>
                <a href="conferences.php" aria-expanded="false">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Conferences</span>
                </a>
            </li>
            <li>
                <a href="webinars.php" aria-expanded="false">
                    <i class="fas fa-video"></i>
                    <span class="nav-text">Webinars</span>
                </a>
            </li>
            <li>
                <a href="internships.php" aria-expanded="false">
                    <i class="fas fa-user-graduate"></i>
                    <span class="nav-text">Internships</span>
                </a>
            </li>
            <li>
                <a href="progress_reports.php" aria-expanded="false">
                    <i class="fas fa-user-graduate"></i>
                    <span class="nav-text">Progress Reports</span>
                </a>
            </li>
        </ul>

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