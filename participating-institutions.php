




<!-- Header -->
<?php
$bodyClass = 'page-sub-page page-course-detail'; include 'header.php';?>

<!-- end Header -->

<style>
    :root {
        --corporate-blue: #1a2a3a;
        --accent-red: #BC2121;
        --light-gray: #f8f9fa;
        --border-gray: #e9ecef;
        --text-dark: #212529;
        --text-muted: #6c757d;
    }

    .corporate-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: var(--text-dark);
        max-width: 1140px;
        margin: 40px auto;
        padding: 0 15px;
        line-height: 1.6;
    }

    /* --- HEADER STYLING: CENTERED WITH CUSTOM UNDERLINE --- */
    .section-header {
        text-align: center;
        margin-bottom: 50px;
        position: relative;
    }

    /* Kill the old grey line from the screenshot */
    .section-header::after, 
    .section-header::before {
        display: none !important;
    }

    .section-header h2 {
        font-size: 2rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--corporate-blue);
        margin: 0;
        display: inline-block; /* Essential for the underline to match text width */
        position: relative;
        padding-bottom: 15px; /* Space for the underline */
    }

    /* The new centered red underline */
    .section-header h2::after {
        content: "";
        position: absolute;
        left: 50%;
        bottom: 0;
        transform: translateX(-50%); /* Centers the underline relative to the H2 */
        width: 80px; /* Adjust width of the line here */
        height: 3px; /* Thickness of the line */
        background-color: var(--accent-red);
    }

    /* --- GRID STRUCTURE --- */
    .tier-grid {
        display: flex;
        gap: 24px;
        margin-bottom: 60px;
    }

    .tier-card {
        flex: 1;
        background: #fff;
        padding: 30px;
        border-radius: 4px;
        border: 1px solid var(--border-gray);
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
    }

    .tier-card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        border-color: var(--accent-red);
    }

    .tier-card h4 {
        color: var(--accent-red);
        font-size: 1.5rem;
        text-transform: uppercase;
        font-weight: 700;
        margin-top: 0;
        letter-spacing: 1px;
    }

    .tier-card p {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .tier-card span {
        font-size: 1.3rem;
        color: var(--text-muted);
        display: block;
    }

    /* --- HUB DETAIL: RED THEME --- */
    .hub-corporate-box {
        display: flex;
        align-items: stretch;
        background-color: var(--light-gray);
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--border-gray);
    }

    .hub-identity {
        flex: 0 0 40%;
        background-color: var(--accent-red); 
        padding: 40px;
        color: #ffffff; 
    }

    .hub-identity h3 {
        color: #ffffff; 
        margin-top: 0;
        font-size: 1.6rem;
        border-bottom: 2px solid rgba(255, 255, 255, 0.3); 
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    address {
        font-style: normal;
        color: #ffffff; 
        font-size: 1.5rem;
        line-height: 1.8;
    }

    .hub-benefits {
        flex: 1;
        padding: 40px;
        font-size: 15px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .benefit-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        font-weight: 500;
    }

    .benefit-item i {
        width: 10px;
        height: 10px;
        background-color: var(--accent-red);
        border-radius: 50%;
        margin-right: 15px;
        flex-shrink: 0;
    }

    @media (max-width: 992px) {
        .tier-grid { flex-direction: column; }
        .hub-corporate-box { flex-direction: column; }
        .hub-identity { border-right: none; border-bottom: 1px solid var(--border-gray); }
    }
</style>

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb">
        <li><a href="index.html">Home</a></li>
        <li class="active">About the Project</li>
    </ol>
</div>
<!-- end Breadcrumb -->

<div class="corporate-container">
    <header class="section-header">
        <h2>Participating Institutions</h2>
    </header>

    <div class="tier-grid">
        <div class="tier-card">
            <h4>Hub Institution</h4>
            <p>University of Hyderabad</p>
            <span>Central coordinating body and resource provider.</span>
        </div>
        <div class="tier-card">
            <h4>Spoke Institutions</h4>
            <p>Partner Universities</p>
            <span>Research centers and academic institutions.</span>
        </div>
        <div class="tier-card">
            <h4>Collaborators</h4>
            <p>Industry & Government</p>
            <span>Healthcare providers and administrative agencies.</span>
        </div>
    </div>

    <div class="hub-corporate-box">
        <div class="hub-identity">
            <h3>Central Hub</h3>
            <address>
                <strong>University of Hyderabad</strong><br>
                P. O. Central University Campus,<br>
                Professor C. R. Rao Road,<br>
                Gachibowli, Hyderabad - 500046.
            </address>
        </div>
        
        <div class="hub-benefits">
            <p style="font-weight: 700; color: var(--corporate-blue); margin-bottom: 20px;">Institutional Resource Support:</p>
            
            <div class="benefit-item">
                <i></i> Advanced research infrastructure
            </div>
            <div class="benefit-item">
                <i></i> Strategic technical expertise
            </div>
            <div class="benefit-item">
                <i></i> National project management support
            </div>
        </div>
    </div>
</div>


<!-- Partners & Collaboration Info -->
<div class="block">
    <div class="container">
        <div class="row">
            <!-- Left Side: Logo Ticker -->
            <div class="col-md-12 col-sm-12">
                <section id="partners">
                    <header style="text-align:center;">
                        <h2>Spoke Institutions</h2>
                    </header>
                    <div class="section-content">
                        <div class="logos-ticker-wrapper">
                            <div class="logos-ticker">
                                <!-- Original Items -->
                             <!-- Central University of Karnataka -->
            <div class="logo-item">
                <a href="https://www.cuk.ac.in/" target="_blank">
                    <img src="logos/cuk1.jpg" alt="Central University of Karnataka">
                </a>
                <p class="institution-name">Central University of Karnataka</p>
                <p class="institution-address">Kadaganchi, Aland Road</p>
            </div>

            <!-- Kannur University -->
            <div class="logo-item">
                <a href="https://www.kannuruniversity.ac.in/" target="_blank">
                    <img src="logos/ku1.jpg" alt="Kannur University">
                </a>
                <p class="institution-name">Kannur University</p>
                <p class="institution-address">Thavakkara Civil Station</p>
            </div>

            <!-- Mahatma Gandhi University -->
            <div class="logo-item">
                <a href="https://www.mgu.ac.in/" target="_blank">
                    <img src="logos/mg1.jpg" alt="Mahatma Gandhi University">
                </a>
                <p class="institution-name">Mahatma Gandhi University</p>
                <p class="institution-address">Priyadarshi Hills Post</p>
            </div>

            <!-- Osmania University -->
            <div class="logo-item">
                <a href="https://www.osmania.ac.in/" target="_blank">
                    <img src="logos/ou1.jpg" alt="Osmania University">
                </a>
                <p class="institution-name">Osmania University</p>
                <p class="institution-address">Amberpet, Hyderabad</p>
            </div>

            <!-- Sri Venkateswara University -->
             <div class="logo-item">
                <a href="https://svuniversity.edu.in/" target="_blank">
                    <img src="logos/gan1.jpg" alt="Sri Venkateswara University">
                </a>
                <p class="institution-name">Sri Venkateswara University</p>
                <p class="institution-address">University Campus</p>
            </div>

            <!-- Yogi Vemana University -->
            <div class="logo-item">
                <a href="https://yvu.edu.in/" target="_blank">
                    <img src="logos/yu.jpg" alt="Yogi Vemana University">
                </a>
                <p class="institution-name">Yogi Vemana University</p>
                <p class="institution-address">Vemana Puram, Gaganapalle</p>
            </div>
           <!-- Central University of Karnataka -->
            <div class="logo-item">
                <a href="https://www.cuk.ac.in/" target="_blank">
                    <img src="logos/cuk1.jpg" alt="Central University of Karnataka">
                </a>
                <p class="institution-name">Central University of Karnataka</p>
                <p class="institution-address">Kadaganchi, Aland Road</p>
            </div>

            <!-- Kannur University -->
            <div class="logo-item">
                <a href="https://www.kannuruniversity.ac.in/" target="_blank">
                    <img src="logos/ku1.jpg" alt="Kannur University">
                </a>
                <p class="institution-name">Kannur University</p>
                <p class="institution-address">Thavakkara Civil Station</p>
            </div>

            <!-- Mahatma Gandhi University -->
            <div class="logo-item">
                <a href="https://www.mgu.ac.in/" target="_blank">
                    <img src="logos/mg1.jpg" alt="Mahatma Gandhi University">
                </a>
                <p class="institution-name">Mahatma Gandhi University</p>
                <p class="institution-address">Priyadarshi Hills Post</p>
            </div>

            <!-- Osmania University -->
            <div class="logo-item">
                <a href="https://www.osmania.ac.in/" target="_blank">
                    <img src="logos/ou1.jpg" alt="Osmania University">
                </a>
                <p class="institution-name">Osmania University</p>
                <p class="institution-address">Amberpet, Hyderabad</p>
            </div>

            <!-- Sri Venkateswara University -->
            <div class="logo-item">
                <a href="https://svuniversity.edu.in/" target="_blank">
                    <img src="logos/gan1.jpg" alt="Sri Venkateswara University">
                </a>
                <p class="institution-name">Sri Venkateswara University</p>
                <p class="institution-address">University Campus</p>
            </div>

            <!-- Yogi Vemana University -->
            <div class="logo-item">
                <a href="https://yvu.edu.in/" target="_blank">
                    <img src="logos/yu.jpg" alt="Yogi Vemana University">
                </a>
                <p class="institution-name">Yogi Vemana University</p>
                <p class="institution-address">Vemana Puram, Gaganapalle</p>
            </div>
                                    </div>
                        </div>
                    </div>
                </section>
            </div>

         
        </div>
    </div>
</div>


 <style
 >/* Ticker Wrapper — clips overflow and fades edges */
.logos-ticker-wrapper {
    overflow: hidden;
    position: relative;
    width: 100%;
}

/* Fade edges for a polished look */
.logos-ticker-wrapper::before,
.logos-ticker-wrapper::after {
    content: '';
    position: absolute;
    top: 0;
    width: 80px;
    height: 100%;
    z-index: 2;
    pointer-events: none;
}
.logos-ticker-wrapper::before {
    left: 0;
    background: linear-gradient(to right, #fff, transparent);
}
.logos-ticker-wrapper::after {
    right: 0;
    background: linear-gradient(to left, #fff, transparent);
}

/* The scrolling track */
.logos-ticker {
    display: flex;
    align-items: center;
    width: max-content;
    animation: ticker-scroll 15s linear infinite;
}

/* Pause on hover */
.logos-ticker:hover {
    animation-play-state: paused;
}

/* Each institution card */
.logo-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 10px 30px;
    min-width: 180px;
    border-right: 1px solid #e0e0e0;
}

.logo-item img {
    height: 60px;
    width: auto;
    object-fit: contain;
    margin-bottom: 8px;
}

.institution-name {
    font-weight: 700;
    font-size: 13px;
    color: #222;
    margin: 0 0 3px 0;
    line-height: 1.3;
}

.institution-address {
    font-size: 11px;
    color: #777;
    margin: 0;
    line-height: 1.4;
}

/* Scroll keyframe — moves left by exactly 50% (the duplicated half) */
@keyframes ticker-scroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>


<?php include 'footer.php';?>


</div><!-- end Wrapper -->

=