<?php
$bodyClass = 'page-homepage-courses';
require_once 'config.php';
date_default_timezone_set('Asia/Kolkata');

// Default fallback slide (Original ANRF-PAIR Group Photo)
$defaultSlide = [
    [
        'src' => 'assets/img/1.jpg',
        'alt' => 'ANRF PAIR Group Photo',
        'caption' => ''
    ]
];

$sliderImages = [];
$nowStr = date('Y-m-d H:i:s');

try {
<<<<<<< HEAD
    $cols = $pdo->query("SHOW COLUMNS FROM `homepage_banners`")->fetchAll(PDO::FETCH_COLUMN);
    $hasStart = in_array('start_datetime', $cols, true);
    $hasEnd   = in_array('end_datetime', $cols, true);

    if ($hasStart && $hasEnd) {
=======
    $reqPrefix = isset($_GET['prefix']) ? strtolower(trim($_GET['prefix'])) : null;
    $validPrefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];

    if ($reqPrefix && in_array($reqPrefix, $validPrefixes, true)) {
        $stmt = $pdo->prepare("
            SELECT * FROM `homepage_banners`
            WHERE status = 'Active'
              AND (institute_prefix = :prefix OR institute_prefix = 'all' OR institute_prefix = '' OR institute_prefix IS NULL)
              AND (start_datetime IS NULL OR start_datetime <= :now1)
              AND (end_datetime IS NULL OR end_datetime >= :now2)
            ORDER BY display_order ASC, start_datetime DESC, id DESC
        ");
        $stmt->execute([':prefix' => $reqPrefix, ':now1' => $nowStr, ':now2' => $nowStr]);
    } else {
>>>>>>> 43636af48df1f8536c32d9a9d721dd91293e4a97
        $stmt = $pdo->prepare("
            SELECT * FROM `homepage_banners`
            WHERE status = 'Active'
              AND (start_datetime IS NULL OR start_datetime <= :now1)
              AND (end_datetime IS NULL OR end_datetime >= :now2)
            ORDER BY display_order ASC, start_datetime DESC, id DESC
        ");
        $stmt->execute([':now1' => $nowStr, ':now2' => $nowStr]);
<<<<<<< HEAD
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM `homepage_banners`
            WHERE status = 'Active'
            ORDER BY display_order ASC, id DESC
        ");
        $stmt->execute();
=======
>>>>>>> 43636af48df1f8536c32d9a9d721dd91293e4a97
    }
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($banners)) {
        $activeSlides = [];
        foreach ($banners as $b) {
            $titleText = !empty($b['title']) ? $b['title'] : (!empty($b['caption']) ? $b['caption'] : 'ANRF PAIR Event Poster');
            $captionText = !empty($b['caption']) ? $b['caption'] : (!empty($b['title']) ? $b['title'] : '');
            $activeSlides[] = [
                'src' => $b['image_path'],
<<<<<<< HEAD
                'alt' => htmlspecialchars($titleText),
                'caption' => $captionText
=======
                'alt' => !empty($b['title']) ? $b['title'] : 'ANRF PAIR Event Poster',
                'caption' => '', // Hide auto-generated text overlay for poster slides as posters contain their own text
                'is_poster' => true
>>>>>>> 43636af48df1f8536c32d9a9d721dd91293e4a97
            ];
        }
        // Combine default group photo slide + scheduled active posters
        $sliderImages = array_merge($defaultSlide, $activeSlides);
    } else {
        $sliderImages = $defaultSlide;
    }
} catch (PDOException $e) {
    $sliderImages = $defaultSlide;
}
?>

<?php include 'header.php'; ?>

<!-- MAIN HOME SLIDER (EXACT ORIGINAL DESIGN SYSTEM) -->
<section id="homepage-slider">
    <div class="main-slider">
        <?php foreach ($sliderImages as $idx => $slide): ?>
            <div class="slide <?= $idx === 0 ? 'active' : '' ?>">
                <?php if (!empty($slide['is_poster'])): ?>
                    <div class="poster-slide-wrapper" style="position: absolute; inset: 0; width: 100%; height: 100%; overflow: hidden; background: #0f172a; display: flex; align-items: center; justify-content: center;">
                        <div class="poster-slide-bg" style="position: absolute; top: -10%; left: -10%; width: 120%; height: 120%; background-image: url('<?= htmlspecialchars($slide['src']) ?>'); background-size: cover; background-position: center; filter: blur(22px) brightness(0.4); transform: scale(1.1);"></div>
                        <img src="<?= htmlspecialchars($slide['src']) ?>"
                             alt="<?= htmlspecialchars($slide['alt']) ?>"
                             <?= $idx === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?>
                             decoding="async"
                             style="position: relative; z-index: 2; max-width: 100%; max-height: 100%; width: auto; height: 100%; object-fit: contain; display: block; margin: 0 auto;">
                    </div>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($slide['src']) ?>"
                         alt="<?= htmlspecialchars($slide['alt']) ?>"
                         <?= $idx === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?>
                         decoding="async"
                         style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                <?php endif; ?>

                <?php if (!empty($slide['caption'])): ?>
                    <div class="slide-overlay">
                        <h2><?= htmlspecialchars($slide['caption']) ?></h2>
                    </div>
                <?php endif; ?>
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

<!-- ORIGINAL SLIDER SCRIPT -->
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
        if (!slides || slides.length === 0) return;
        index = (i + slides.length) % slides.length;

        slides.forEach(slide => slide.classList.remove("active"));
        indicators.forEach(ind => ind.classList.remove("active"));

        if (slides[index]) {
            slides[index].classList.add("active");
        }
        if (indicators[index]) {
            indicators[index].classList.add("active");
        }
    }

    function nextSlide() {
        if (slides.length <= 1) return;
        showSlide(index + 1);
    }

    function prevSlide() {
        if (slides.length <= 1) return;
        showSlide(index - 1);
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
</body>
