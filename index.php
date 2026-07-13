<body class="page-homepage-courses">

    <!-- Wrapper -->
    <div class="wrapper">

        <?php include 'header.php'; ?>

        <!-- MAIN HOME SLIDER -->
        <section id="homepage-slider">
            <div class="main-slider">
                <div class="slide active">
                    <img src="assets/img/1.jpg" alt="ANRF PAIR Group Photo" loading="eager" fetchpriority="high" decoding="async">
                    <div class="slide-overlay">
                        <h5 style="text-transform:none; font-size:2.5rem; margin-top:-50px;"></h5>
                    </div>
                </div>
                <div class="slide">
                    <img src="assets/img/slide-2.jpg" alt="ANRF PAIR Slide 2" loading="lazy" decoding="async">
                    <div class="slide-overlay">
                        <h5 style="text-transform:none; font-size:2.5rem; margin-top:-50px;"></h5>
                    </div>
                </div>
                <div class="slide">
                    <img src="assets/img/slide-3.jpg" alt="ANRF PAIR Slide 3" loading="lazy" decoding="async">
                    <div class="slide-overlay">
                        <h5 style="text-transform:none; font-size:2.5rem; margin-top:-50px;"></h5>
                    </div>
                </div>
                
                <!-- Slide Indicators/Dots -->
                <div class="slider-indicators">
                    <span class="indicator active" data-slide-to="0"></span>
                    <span class="indicator" data-slide-to="1"></span>
                    <span class="indicator" data-slide-to="2"></span>
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
            const indicators = document.querySelectorAll(".indicator");
            const next = document.querySelector(".next");
            const prev = document.querySelector(".prev");
            const sliderContainer = document.querySelector(".main-slider");
            let index = 0;
            let autoplayTimer = null;
            const intervalTime = 5000; // 5 seconds

            function showSlide(i) {
                index = i;
                slides.forEach(slide => slide.classList.remove("active"));
                indicators.forEach(ind => ind.classList.remove("active"));
                
                slides[index].classList.add("active");
                if (indicators[index]) {
                    indicators[index].classList.add("active");
                }
            }

            function nextSlide() {
                let nextIndex = (index + 1) % slides.length;
                showSlide(nextIndex);
            }

            function prevSlide() {
                let prevIndex = (index - 1 + slides.length) % slides.length;
                showSlide(prevIndex);
            }

            function startAutoplay() {
                if (autoplayTimer) clearInterval(autoplayTimer);
                autoplayTimer = setInterval(nextSlide, intervalTime);
            }

            function stopAutoplay() {
                if (autoplayTimer) {
                    clearInterval(autoplayTimer);
                    autoplayTimer = null;
                }
            }

            if (next) {
                next.addEventListener("click", () => {
                    nextSlide();
                    startAutoplay(); // Reset timer on manual click
                });
            }
            if (prev) {
                prev.addEventListener("click", () => {
                    prevSlide();
                    startAutoplay(); // Reset timer on manual click
                });
            }

            if (indicators.length > 0) {
                indicators.forEach((indicator) => {
                    indicator.addEventListener("click", (e) => {
                        const slideTo = parseInt(e.target.getAttribute("data-slide-to"));
                        showSlide(slideTo);
                        startAutoplay(); // Reset timer on indicator click
                    });
                });
            }

            // Pause autoplay on hover to allow reading content
            if (sliderContainer) {
                sliderContainer.addEventListener("mouseenter", stopAutoplay);
                sliderContainer.addEventListener("mouseleave", startAutoplay);
            }

            // Callback triggered by preloader when loading is finished
            window.startSliderAutoplay = function() {
                console.log("Slider: Autoplay initialized via preloader.");
                startAutoplay();
            };

            // Fail-safe: if preloader is not present, start autoplay immediately
            document.addEventListener("DOMContentLoaded", () => {
                if (!document.getElementById("preloader")) {
                    startAutoplay();
                }
            });
        </script>

    </div><!-- end .wrapper -->
</body>