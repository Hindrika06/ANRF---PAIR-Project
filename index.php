<body class="page-homepage-courses">

    <!-- Wrapper -->
    <div class="wrapper">

        <?php include 'header.php'; ?>

        <!-- MAIN HOME SLIDER -->
        <section id="homepage-slider">
            <div class="main-slider">
                <div class="slide active">
                    <img src="assets/img/1.jpg" alt="ANRF PAIR" loading="eager" fetchpriority="high" decoding="async">
                    <div class="slide-overlay">
                        <!-- HERO LOGO ANIMATION -->
                        <div class="hero-logo-anim-wrap" id="hero-logo-anim-wrap">
                            <!-- Layer 1: Innermost arc (clip: show only inner arc region) -->
                            <img src="2.png" alt="" class="hero-logo-layer" id="hero-layer-arc1">
                            <!-- Layer 2: Middle arc -->
                            <img src="2.png" alt="" class="hero-logo-layer" id="hero-layer-arc2">
                            <!-- Layer 3: Outermost arc -->
                            <img src="2.png" alt="" class="hero-logo-layer" id="hero-layer-arc3">
                            <!-- Layer 4: Red dot -->
                            <img src="2.png" alt="" class="hero-logo-layer" id="hero-layer-dot">
                            <!-- Layer 5: ANRF-PAIR text (right half) -->
                            <img src="2.png" alt="" class="hero-logo-layer" id="hero-layer-text">
                        </div>
                        <h5 style="text-transform:none; font-size:2.5rem; margin-top:-50px;"></h5>
                    </div>
                </div>
                <button class="slider-btn prev">&#10094;</button>
                <button class="slider-btn next">&#10095;</button>
            </div>
        </section>

        <?php include 'whatsnew.php'; ?>

        <?php include 'logoscroll.php'; ?>

        <?php include 'about-1.php'; ?>

        <?php include 'stats.php'; ?>


        <!-- ======= END CALENDAR ======= -->

        <?php include 'gallery-1.php'; ?>

        <?php include 'footer.php'; ?>

        <!-- SLIDER SCRIPT -->
        <script>
            const slides = document.querySelectorAll(".slide");
            const next = document.querySelector(".next");
            const prev = document.querySelector(".prev");
            let index = 0;

            function showSlide(i) {
                slides.forEach(slide => slide.classList.remove("active"));
                slides[i].classList.add("active");
            }
            function nextSlide() {
                index = (index + 1) % slides.length;
                showSlide(index);
            }
            function prevSlide() {
                index = (index - 1 + slides.length) % slides.length;
                showSlide(index);
            }
            next.addEventListener("click", nextSlide);
            prev.addEventListener("click", prevSlide);
            setInterval(nextSlide, 5000);
        </script>

    </div><!-- end .wrapper -->
</body>