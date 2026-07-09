<style>
.view-institutes-btn {
    position: absolute;
    right: 15px;
    background-color: #bc2121;
    color: #ffffff;
    padding: 9px 20px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.4px;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.view-institutes-btn:hover {
    background-color: #a01c1c;
    color: #fff;
}

/* Hide the below-logos button on desktop */
.view-institutes-below {
    display: none;
}

.view-institutes-wrap {
    display: none;
    text-align: center;
    margin-top: 18px;
}

/* Mobile Media Query Updates */
@media (max-width: 767px) {
    /* 1. HIDES THE TOP BUTTON ON MOBILE */
    .view-institutes-desktop {
        display: none !important;
    }

    /* 2. SHOWS THE LOWER BUTTON WRAPPER ON MOBILE */
    .view-institutes-wrap {
        display: block;
    }

    .view-institutes-below {
        display: inline-flex;
        position: static;
        margin: 0 auto;
    }

    #partners .gallery-main-heading {
        text-align: center;
        width: 100%;
        font-size: 1.6rem;
    }

    #partners > div:first-child {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<!-- Partners & Collaboration Info -->
<div class="block">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <section id="partners">
                    <div style="
                        position: relative;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 10px;
                        padding: 0 15px;
                    ">
                        <h2 class="gallery-main-heading">SPOKE INSTITUTIONS</h2>

                        <a href="institute.php" class="view-institutes-btn view-institutes-desktop">View Institutes
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    </div>
                    <div class="section-content">
                        <div class="logos-ticker-wrapper">
                            <div class="logos-ticker" id="logosTicker">
                                <!-- Central University of Karnataka -->
                                <div class="logo-item">
                                    <a href="institute.php?name=Central University of Karnataka">
                                        <img src="logos/cuk1.jpg" alt="Central University of Karnataka">
                                    </a>
                                    <p class="institution-name">Central University of Karnataka</p>
                                    <p class="institution-address">Kadaganchi, Aland Road</p>
                                </div>

                                <!-- Kannur University -->
                                <div class="logo-item">
                                    <a href="institute.php?name=Kannur University">
                                        <img src="logos/ku1.jpg" alt="Kannur University">
                                    </a>
                                    <p class="institution-name">Kannur University</p>
                                    <p class="institution-address">Thavakkara Civil Station</p>
                                </div>

                                <!-- Mahatma Gandhi University -->
                                <div class="logo-item">
                                    <a href="institute.php?name=Mahatma Gandhi University">
                                        <img src="logos/mg1.jpg" alt="Mahatma Gandhi University">
                                    </a>
                                    <p class="institution-name">Mahatma Gandhi University</p>
                                    <p class="institution-address">Priyadarshi Hills Post</p>
                                </div>

                                <!-- Osmania University -->
                                <div class="logo-item">
                                    <a href="institute.php?name=Osmania University">
                                        <img src="logos/ou1.jpg" alt="Osmania University">
                                    </a>
                                    <p class="institution-name">Osmania University</p>
                                    <p class="institution-address">Amberpet, Hyderabad</p>
                                </div>

                                <!-- Sri Venkateswara University -->
                                <div class="logo-item">
                                    <a href="institute.php?name=Sri Venkateswara University">
                                        <img src="logos/gan1.jpg" alt="Sri Venkateswara University">
                                    </a>
                                    <p class="institution-name">Sri Venkateswara University</p>
                                    <p class="institution-address">University Campus</p>
                                </div>

                                <!-- Yogi Vemana University -->
                                <div class="logo-item">
                                    <a href="institute.php?name=Yogi Vemana University">
                                        <img src="logos/yu.jpg" alt="Yogi Vemana University">
                                    </a>
                                    <p class="institution-name">Yogi Vemana University</p>
                                    <p class="institution-address">Vemana Puram, Gaganapalle</p>
                                </div>
                            </div>
                        </div>
                        <div class="view-institutes-wrap">
                            <a href="institute.php" class="view-institutes-btn view-institutes-below">View Institutes
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4"/>
                                    <polyline points="15 3 21 3 21 9"/>
                                    <line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ticker = document.getElementById('logosTicker');
    if (ticker && !ticker.dataset.cloned) {
        const clone = ticker.innerHTML;
        ticker.insertAdjacentHTML('beforeend', clone);
        ticker.dataset.cloned = 'true';
    }
});
</script>