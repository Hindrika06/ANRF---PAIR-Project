<?php
// Check for user-uploaded images in the slider uploads folder
$sliderImages = [];
$sliderDir = 'uploads/slider/';
if (is_dir($sliderDir)) {
    $files = glob($sliderDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    if (!empty($files)) {
        foreach ($files as $file) {
            $sliderImages[] = [
                'src' => $file,
                'alt' => 'ANRF PAIR Slide',
                'caption' => ''
            ];
        }
    }
}

// Fallback: Use ONLY the original group photo if no slider uploads exist
if (empty($sliderImages)) {
    $sliderImages = [
        [
            'src' => 'assets/img/1.jpg',
            'alt' => 'ANRF PAIR Group Photo',
            'caption' => ''
        ]
    ];
}
?>

<body class="page-homepage-courses">

    <!-- Wrapper -->
    <div class="wrapper">

        <?php include 'header.php'; ?>

        <!-- MAIN HOME SLIDER -->
        <section id="homepage-slider">
            <div class="main-slider">
                <?php foreach ($sliderImages as $idx => $slide): ?>
                    <div class="slide <?= $idx === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($slide['src']) ?>" alt="<?= htmlspecialchars($slide['alt']) ?>" <?= $idx === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
                        <div class="slide-overlay">
                            <h5 style="text-transform:none; font-size:2.5rem; margin-top:-50px;"><?= htmlspecialchars($slide['caption']) ?></h5>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($sliderImages) > 1): ?>
                    <!-- Slide Indicators/Dots -->
                    <div class="slider-indicators">
                        <?php foreach ($sliderImages as $idx => $slide): ?>
                            <span class="indicator <?= $idx === 0 ? 'active' : '' ?>" data-slide-to="<?= $idx ?>"></span>
                        <?php endforeach; ?>
                    </div>

                    <button class="slider-btn prev">&#10094;</button>
                    <button class="slider-btn next">&#10095;</button>
                <?php endif; ?>
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
                if (slides.length <= 1) return;
                let nextIndex = (index + 1) % slides.length;
                showSlide(nextIndex);
            }

            function prevSlide() {
                if (slides.length <= 1) return;
                let prevIndex = (index - 1 + slides.length) % slides.length;
                showSlide(prevIndex);
            }

            function startAutoplay() {
                if (slides.length <= 1) return;
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
            if (sliderContainer && slides.length > 1) {
                sliderContainer.addEventListener("mouseenter", stopAutoplay);
                sliderContainer.addEventListener("mouseleave", startAutoplay);
            }

            // Callback triggered by preloader when loading is finished
            window.startSliderAutoplay = function() {
                if (slides.length > 1) {
                    console.log("Slider: Autoplay initialized via preloader.");
                    startAutoplay();
                }
            };

            // Fail-safe: if preloader is not present, start autoplay immediately
            document.addEventListener("DOMContentLoaded", () => {
                if (!document.getElementById("preloader")) {
                    if (slides.length > 1) {
                        startAutoplay();
                    }
                }
            });
        </script>

    </div><!-- end .wrapper -->
</body>