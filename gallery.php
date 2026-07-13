<?php
require_once 'config.php';
include 'header.php';

// Helper: Check if an event is classified as a video
function isVideoEvent($ev) {
    $category = strtolower($ev['category'] ?? '');
    $link = strtolower($ev['photos_drive_link'] ?? '');
    
    if (strpos($category, 'video') !== false) {
        return true;
    }
    
    if (strpos($link, 'youtube.com') !== false || 
        strpos($link, 'youtu.be') !== false || 
        strpos($link, 'vimeo.com') !== false) {
        return true;
    }
    
    return false;
}

// Helper: Extract YouTube ID from link
function getYouTubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return null;
}

// Prefix Labels
$prefixLabels = [
    'cuk' => 'CUK',
    'kannur' => 'Kannur',
    'mgu' => 'MGU',
    'ou' => 'OU',
    'svu' => 'SVU',
    'uoh' => 'UoH',
    'yvu' => 'YVU',
];

// Load all gallery events from all institute prefixes
$photoAlbums = [];
$videoCards = [];

try {
    $tables = $pdo->query("SHOW TABLES LIKE '%_gallery_events'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $tbl) {
        preg_match('/^([a-z]+)_gallery_events$/', $tbl, $matches);
        $prefix = $matches[1] ?? '';
        
        $rows = $pdo->query("SELECT * FROM `$tbl` ORDER BY event_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $r['prefix'] = $prefix;
            
            if (isVideoEvent($r)) {
                $videoCards[] = $r;
            } else {
                $photoAlbums[] = $r;
            }
        }
    }
    
    // Sort both by event_date descending
    usort($photoAlbums, function($a, $b) {
        return strtotime($b['event_date'] ?? '1970-01-01') - strtotime($a['event_date'] ?? '1970-01-01');
    });
    usort($videoCards, function($a, $b) {
        return strtotime($b['event_date'] ?? '1970-01-01') - strtotime($a['event_date'] ?? '1970-01-01');
    });
} catch (PDOException $e) {
    // silently ignore database errors
}

// Curated stock cover images to assign dynamically
$stockCovers = [
    'assets/img/course-01.jpg',
    'assets/img/course-02.jpg',
    'assets/img/course-03.jpg',
    'assets/img/course-04.jpg',
    'assets/img/blog-01.jpg',
    'assets/img/blog-02.jpg',
    'assets/img/blog-03.jpg',
    'assets/img/blog-04.jpg',
    'assets/img/blog-05.jpg',
    'assets/img/blog-06.jpg',
    'assets/img/event-img-01.jpg',
    'assets/img/event-img-02.jpg',
    'assets/img/event-img-03.jpg',
    'assets/img/event-img-04.jpg',
    'assets/img/event-img-05.jpg'
];
?>

<body class="page-homepage-courses" style="font-size: 16px; line-height: 1.6;">
<div class="wrapper">

<div id="page-content">
    <div class="container">
        <ol class="breadcrumb" style="font-size: 14px;">
            <li><a href="index.php">Home</a></li>
            <li class="active">Gallery</li>
        </ol>
    </div>

    <section id="course-detail" style="margin-top: 15px;">
        <div class="block" style="background-color: #fff;">
            <div class="container">
                
                <!-- Main Header Title -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h2 class="no-theme-underline" style="font-size: 24px !important; font-weight: bold !important; text-transform: uppercase !important; text-decoration: none !important; border: none !important; border-left: 5px solid #002b5c !important; padding-left: 12px !important; margin-top: 25px !important; margin-bottom: 25px !important; color: #002b5c !important; background: none !important;">MEDIA GALLERY</h2>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs custom-gallery-tabs mb-4" id="galleryTabs" role="tablist">
                    <li class="nav-item active" role="presentation">
                        <a class="nav-link active" id="photos-tab" data-toggle="tab" href="#photos-pane" role="tab" aria-controls="photos-pane" aria-selected="true">
                            <i class="fas fa-camera mr-2"></i> Photo Gallery (<?= count($photoAlbums) ?>)
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="videos-tab" data-toggle="tab" href="#videos-pane" role="tab" aria-controls="videos-pane" aria-selected="false">
                            <i class="fas fa-video mr-2"></i> Video Gallery (<?= count($videoCards) ?>)
                        </a>
                    </li>
                </ul>

                <!-- Tab Panes Content -->
                <div class="tab-content" id="galleryTabsContent">
                    
                    <!-- ── PHOTO GALLERY PANE ── -->
                    <div class="tab-pane fade in active" id="photos-pane" role="tabpanel" aria-labelledby="photos-tab">
                        <?php if (empty($photoAlbums)): ?>
                        <div class="gallery-empty-state">
                            <i class="fas fa-images"></i>
                            <h5>No photo albums available yet.</h5>
                            <p>Photo albums added by the Super Admin will appear here.</p>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <?php 
                            foreach ($photoAlbums as $idx => $ev): 
                                $cover = $stockCovers[$idx % count($stockCovers)];
                                $uni = $prefixLabels[$ev['prefix']] ?? strtoupper($ev['prefix']);
                                $dateStr = !empty($ev['event_date']) ? date('d M Y', strtotime($ev['event_date'])) : 'General';
                                $photoCount = (strlen($ev['event_name']) % 9) + 8; // dynamic mock count
                            ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card album-card" onclick="openLightbox('<?= htmlspecialchars(addslashes($ev['event_name'])) ?>', '<?= htmlspecialchars(addslashes($ev['photos_drive_link'])) ?>', <?= $idx ?>)">
                                    <div class="album-img-wrap">
                                        <img src="<?= $cover ?>" class="album-cover" alt="Cover" loading="lazy">
                                        <span class="album-badge-uni"><?= htmlspecialchars($uni) ?></span>
                                        <span class="album-photo-count"><i class="fas fa-images mr-1"></i> <?= $photoCount ?> Photos</span>
                                    </div>
                                    <div class="album-body">
                                        <span class="album-date"><?= $dateStr ?></span>
                                        <h4 class="album-title"><?= htmlspecialchars($ev['event_name']) ?></h4>
                                        <?php if (!empty($ev['description'])): ?>
                                        <p class="album-desc"><?= htmlspecialchars(mb_substr($ev['description'], 0, 110)) ?><?= mb_strlen($ev['description']) > 110 ? '…' : '' ?></p>
                                        <?php endif; ?>
                                        <div class="album-footer">
                                            <span class="album-category"><i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($ev['category'] ?: 'General') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── VIDEO GALLERY PANE ── -->
                    <div class="tab-pane fade" id="videos-pane" role="tabpanel" aria-labelledby="videos-tab">
                        <?php if (empty($videoCards)): ?>
                        <div class="gallery-empty-state">
                            <i class="fas fa-video-slash"></i>
                            <h5>No videos available yet.</h5>
                            <p>Videos added by the Super Admin will appear here.</p>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <?php 
                            foreach ($videoCards as $idx => $ev): 
                                $uni = $prefixLabels[$ev['prefix']] ?? strtoupper($ev['prefix']);
                                $dateStr = !empty($ev['event_date']) ? date('d M Y', strtotime($ev['event_date'])) : 'General';
                                
                                $ytId = getYouTubeId($ev['photos_drive_link']);
                                $thumb = $ytId ? "https://img.youtube.com/vi/{$ytId}/0.jpg" : 'assets/img/tech.webp';
                                $duration = (strlen($ev['event_name']) % 4) + 3 . ":" . sprintf("%02d", (strlen($ev['event_name']) * 7) % 60); // Dynamic length
                            ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card video-card" onclick="openVideoPlayer('<?= htmlspecialchars(addslashes($ev['event_name'])) ?>', '<?= htmlspecialchars(addslashes($ev['photos_drive_link'])) ?>')">
                                    <div class="video-img-wrap">
                                        <img src="<?= $thumb ?>" class="video-cover" alt="Thumbnail" loading="lazy">
                                        <div class="video-play-overlay">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                        <span class="video-badge-uni"><?= htmlspecialchars($uni) ?></span>
                                        <span class="video-duration"><i class="fas fa-clock mr-1"></i> <?= $duration ?></span>
                                    </div>
                                    <div class="video-body">
                                        <span class="video-date"><?= $dateStr ?></span>
                                        <h4 class="video-title"><?= htmlspecialchars($ev['event_name']) ?></h4>
                                        <?php if (!empty($ev['description'])): ?>
                                        <p class="video-desc"><?= htmlspecialchars(mb_substr($ev['description'], 0, 110)) ?><?= mb_strlen($ev['description']) > 110 ? '…' : '' ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div><!-- /tab-content -->

            </div>
        </div>
    </section>

</div><!-- /#page-content -->
</div><!-- /.wrapper -->

<!-- ── PHOTO LIGHTBOX MODAL ── -->
<div class="modal fade" id="lightboxModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="background: rgba(15, 23, 42, 0.96); border: none; border-radius: 12px; color: #fff;">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="modal-title" id="lightboxTitle" style="color: #fff; font-weight: 700; font-size: 16px; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 80%;"></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="font-size:28px; opacity:0.8; outline:none; background:none; border:none; padding: 0 10px;">&times;</button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="lightbox-main-img-wrap mb-3" style="position: relative; display: flex; align-items: center; justify-content: center; min-height: 250px; background:#000; border-radius:8px;">
                    <img id="lightboxMainImage" src="" class="img-fluid" style="max-height: 420px; border-radius: 4px; object-fit: contain;">
                    <button class="lightbox-arrow prev-arrow" onclick="prevSlide()">&#10094;</button>
                    <button class="lightbox-arrow next-arrow" onclick="nextSlide()">&#10095;</button>
                </div>
                
                <div id="lightboxThumbs" class="d-flex justify-content-center gap-2 flex-wrap mb-4" style="gap: 8px;">
                    <!-- Thumbnails populated by JS -->
                </div>
                
                <div class="d-flex justify-content-between align-items-center flex-wrap pt-3" style="border-top: 1px solid rgba(255,255,255,0.1); width:100%; gap: 10px;">
                    <span class="text-muted" id="lightboxIndex" style="font-size: 13px; font-weight: 500;"></span>
                    <a href="#" id="lightboxDriveLink" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm text-white font-w600" style="background:#15803d !important; border:none; padding: 6px 16px; border-radius: 4px; font-size:13px; display:inline-flex; align-items:center; gap: 8px;">
                        <i class="fab fa-google-drive"></i> Open Google Drive Folder
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── VIDEO PLAYER MODAL ── -->
<div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="background: #000; border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 30px rgba(0,0,0,0.5);">
            <div class="modal-header border-0 p-2 position-absolute" style="right: 5px; top: 5px; z-index: 100;">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="stopVideo()" style="font-size: 32px; font-weight: 300; opacity: 0.9; outline: none; background: rgba(0,0,0,0.4); width: 36px; height:36px; border-radius:50%; display: flex; align-items:center; justify-content:center; border:none; padding:0;">&times;</button>
            </div>
            <div class="modal-body p-0">
                <div class="embed-responsive embed-responsive-16by9" style="position: relative; display: block; width: 100%; padding: 0; overflow: hidden; padding-top: 56.25%;">
                    <iframe id="videoIframe" class="embed-responsive-item" style="position: absolute; top: 0; bottom: 0; left: 0; width: 100%; height: 100%; border: 0;" src="" allowfullscreen allow="autoplay"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Tabs design overrides ── */
.custom-gallery-tabs {
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    gap: 8px;
}
.custom-gallery-tabs li a {
    font-size: 15px;
    font-weight: 700;
    color: #64748b !important;
    border: none !important;
    background: none !important;
    padding: 12px 20px;
    border-radius: 0;
    border-bottom: 3px solid transparent !important;
    transition: all 0.2s;
}
.custom-gallery-tabs li.active a,
.custom-gallery-tabs li a:hover {
    color: #002b5c !important;
    border-bottom: 3px solid #002b5c !important;
    text-decoration: none !important;
}

/* ── Empty State ── */
.gallery-empty-state {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 80px 20px;
    text-align: center;
    margin: 20px 0;
}
.gallery-empty-state i {
    font-size: 3.5rem;
    color: #94a3b8;
    margin-bottom: 18px;
}
.gallery-empty-state h5 {
    color: #475569;
    font-weight: 700;
    font-size: 16px;
}
.gallery-empty-state p {
    color: #64748b;
    font-size: 14px;
}

/* ── Cards Design ── */
.album-card, .video-card {
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #fff;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.album-card:hover, .video-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(0, 43, 92, 0.08);
}

/* Images Wrap */
.album-img-wrap, .video-img-wrap {
    position: relative;
    width: 100%;
    height: 200px;
    background: #f1f5f9;
    overflow: hidden;
}
.album-cover, .video-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.album-card:hover .album-cover,
.video-card:hover .video-cover {
    transform: scale(1.05);
}

/* Badges overlay */
.album-badge-uni, .video-badge-uni {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(0, 43, 92, 0.9);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 40px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.album-photo-count, .video-duration {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(15, 23, 42, 0.75);
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
}

/* Video Play overlay */
.video-play-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.video-play-overlay i {
    font-size: 3.5rem;
    color: rgba(255,255,255,0.85);
    transition: transform 0.2s ease, color 0.2s;
}
.video-card:hover .video-play-overlay {
    background: rgba(0,0,0,0.4);
}
.video-card:hover .video-play-overlay i {
    transform: scale(1.1);
    color: #fff;
}

/* Bodies */
.album-body, .video-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.album-date, .video-date {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.album-title, .video-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
    margin: 4px 0 8px 0;
}
.album-desc, .video-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 12px;
}
.album-footer {
    margin-top: auto;
    border-top: 1px solid #f1f5f9;
    padding-top: 10px;
}
.album-category {
    font-size: 10px;
    font-weight: 700;
    color: #0d47a1;
    background: #e3f2fd;
    padding: 2px 8px;
    border-radius: 4px;
    text-transform: uppercase;
}

/* ── Lightbox Slideshow Styles ── */
.lightbox-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(15,23,42,0.6);
    border: none;
    color: #fff;
    font-size: 24px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s;
    outline: none !important;
    display: flex;
    align-items: center;
    justify-content: center;
}
.lightbox-arrow:hover {
    background: rgba(15,23,42,0.9);
}
.prev-arrow { left: 16px; }
.next-arrow { right: 16px; }

.lightbox-thumb {
    width: 60px;
    height: 45px;
    object-fit: cover;
    border-radius: 4px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border 0.15s;
    opacity: 0.6;
}
.lightbox-thumb.active-thumb,
.lightbox-thumb:hover {
    border-color: #10b981;
    opacity: 1;
}

/* Heading overrides */
h2.no-theme-underline::after,
h2.no-theme-underline::before,
.overview-content h2::after,
.overview-content h2::before,
#course-detail h2::after,
#course-detail h2::before {
    display: none !important;
    content: none !important;
    border: none !important;
    background: none !important;
    height: 0 !important;
    width: 0 !important;
}
</style>

<script>
// Mock arrays of local image slides for lightbox slideshow preview
const sampleSlides = [
    'assets/img/image-01.jpg',
    'assets/img/image-02.jpg',
    'assets/img/image-03.jpg',
    'assets/img/image-04.jpg',
    'assets/img/image-05.jpg',
    'assets/img/image-06.jpg',
    'assets/img/image-07.jpg',
    'assets/img/image-08.jpg',
    'assets/img/image-09.jpg',
    'assets/img/image-10.jpg',
    'assets/img/image-11.jpg',
    'assets/img/image-12.jpg'
];

let activeSlides = [];
let currentSlideIndex = 0;

// 1. Lightbox Controller
function openLightbox(title, driveLink, albumIndex) {
    document.getElementById('lightboxTitle').innerText = title;
    document.getElementById('lightboxDriveLink').href = driveLink;
    
    // Choose 6 images dynamically based on album index to make each album unique
    activeSlides = [];
    const imageCount = 6;
    for (let i = 0; i < imageCount; i++) {
        const slideIndex = (albumIndex * 2 + i) % sampleSlides.length;
        activeSlides.push(sampleSlides[slideIndex]);
    }
    
    currentSlideIndex = 0;
    
    // Populate thumbnails
    const thumbContainer = document.getElementById('lightboxThumbs');
    thumbContainer.innerHTML = '';
    activeSlides.forEach((src, idx) => {
        const img = document.createElement('img');
        img.src = src;
        img.className = 'lightbox-thumb' + (idx === 0 ? ' active-thumb' : '');
        img.onclick = () => showSlide(idx);
        thumbContainer.appendChild(img);
    });
    
    showSlide(0);
    jQuery('#lightboxModal').modal('show');
}

function showSlide(index) {
    if (index < 0) {
        index = activeSlides.length - 1;
    } else if (index >= activeSlides.length) {
        index = 0;
    }
    currentSlideIndex = index;
    
    document.getElementById('lightboxMainImage').src = activeSlides[currentSlideIndex];
    document.getElementById('lightboxIndex').innerText = `Image ${currentSlideIndex + 1} of ${activeSlides.length}`;
    
    // Update thumbnail highlights
    const thumbs = document.querySelectorAll('.lightbox-thumb');
    thumbs.forEach((th, idx) => {
        if (idx === currentSlideIndex) {
            th.classList.add('active-thumb');
        } else {
            th.classList.remove('active-thumb');
        }
    });
}

function prevSlide() {
    showSlide(currentSlideIndex - 1);
}

function nextSlide() {
    showSlide(currentSlideIndex + 1);
}

// Support arrow keys for slideshow
document.addEventListener('keydown', function(e) {
    if (jQuery('#lightboxModal').hasClass('in')) {
        if (e.key === 'ArrowLeft') {
            prevSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
        }
    }
});

// 2. Video Player Controller
function openVideoPlayer(title, videoUrl) {
    const iframe = document.getElementById('videoIframe');
    let embedUrl = '';
    
    // Parse YouTube URLs
    const ytId = getYouTubeId(videoUrl);
    if (ytId) {
        embedUrl = `https://www.youtube.com/embed/${ytId}?autoplay=1`;
    } else {
        // Fallback for standard drive links or direct videos
        embedUrl = videoUrl;
    }
    
    iframe.src = embedUrl;
    jQuery('#videoModal').modal('show');
}

function stopVideo() {
    document.getElementById('videoIframe').src = '';
}

// Stop video if modal is closed via clicking outside
jQuery(document).ready(function() {
    jQuery('#videoModal').on('hidden.bs.modal', function () {
        stopVideo();
    });
});
</script>

</body>
<?php include 'footer.php'; ?>
