<style>
        /* --- Centered Main Gallery Heading --- */
        .gallery-main-heading {
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            color: #1B3A6B;
            margin: 20px auto 40px auto;
            letter-spacing: 2px;
            text-decoration: none;
            display: block;
            width: 100%;
        }
        .gallery-main-heading::after {
            display: none !important;
        }

        /* --- Event Section Separation Gap --- */
        .event-block {
            margin-bottom: 60px;
        }
        .event-block:last-child {
            margin-bottom: 0px;
        }
        #gallery {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* --- Tighter, Stylized Event Header --- */
        .event-header-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-bottom: 14px;
            padding-bottom: 6px;
            text-align: center;
            position: relative;
        }

        .event-title-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            width: 100%;
        }

        .event-title {
            font-size: 20px;
            font-weight: 500;
            color: #1e293b;
            margin: 0;
            letter-spacing: -0.3px;
            text-decoration: none;
        }

        .event-title::after {
            content: '';
            display: block;
            width: 24px;
            height: 2px;
            background: #e2e8f0;
            margin: 6px auto 0 auto;
            transition: background 0.3s ease;
        }
        .event-block:hover .event-title::after {
            background: #bc2121;
        }

        .event-meta {
            font-size: 13px;
            color: #64748b;
        }

        .image-count-badge {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 12px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }

        /* --- Custom Horizontal Slider Controls --- */
        .gallery-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .gallery-list-horizontal {
            display: flex;
            overflow-x: auto;
            scroll-behavior: smooth;
            list-style: none;
            padding: 4px 0 16px 0;
            margin: 0;
            gap: 24px;
            width: 100%;
            scrollbar-width: none;
        }
        .gallery-list-horizontal::-webkit-scrollbar { display: none; }

        /* --- Image Card Presentation --- */
        .gallery-list-horizontal li {
            flex: 0 0 calc(25% - 18px);
            min-width: 260px;
        }

        .image-card {
            display: block;
            position: relative;
            overflow: hidden;
            background: #f8fafc;
            aspect-ratio: 4 / 3;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
        }

        .image-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .image-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 10px 10px -5px rgba(15, 23, 42, 0.04);
        }
        .image-card:hover img {
            transform: scale(1.04);
        }

        .image-card::after {
            content: 'View Photo';
            font-size: 13px;
            font-weight: 500;
            color: #ffffff;
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.2));
            display: flex;
            align-items: flex-end;
            padding: 16px;
            box-sizing: border-box;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .image-card:hover::after { opacity: 1; }

        /* --- Sleek Integrated Navigation Buttons --- */
        .nav-btn {
            position: absolute;
            background-color: #bc2121;
            color: #ffffff;
            border: 1px solid #bc2121;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            z-index: 5;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 10px rgba(188, 33, 33, 0.25);
        }

        .nav-btn:hover {
            background-color: #9e1b1b;
            border-color: #9e1b1b;
            transform: scale(1.08);
            box-shadow: 0 6px 14px rgba(188, 33, 33, 0.35);
        }

        .btn-left { left: -22px; }
        .btn-right { right: -22px; }

        /* --- Responsive Viewports --- */
        @media (max-width: 1024px) {
            .gallery-list-horizontal li { flex: 0 0 calc(33.333% - 16px); }
            .nav-btn { display: none; }
            .gallery-list-horizontal { padding-bottom: 8px; }
        }
        @media (max-width: 768px) {
            .event-block { padding: 0; margin-bottom: 40px; }
            .gallery-list-horizontal li { flex: 0 0 calc(50% - 12px); gap: 16px; }
            .event-header-box { margin-bottom: 12px; }
        }
        @media (max-width: 480px) {
            .gallery-list-horizontal li { flex: 0 0 85%; }
            .gallery-main-heading { font-size: 20px; margin: 30px auto 35px auto; }
            .event-title { font-size: 18px; }
            .image-count-badge { display: none; }
        }
</style>


    <div class="container">
        <section id="gallery">
            <div class="section-content">
                <h2 class="gallery-main-heading">GALLERY</h2>

                <?php
                if (!isset($pdo)) {
                    require_once 'config.php';
                }

                $dbAlbums = [];
                try {
                    $stmt = $pdo->query("SELECT * FROM `gallery_albums` ORDER BY album_date DESC, id DESC");
                    $dbAlbums = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Fallback
                }

                if (!empty($dbAlbums)):
                    foreach ($dbAlbums as $alb):
                        $alb_id = $alb['id'];
                        $stmt = $pdo->prepare("SELECT * FROM `gallery_photos` WHERE album_id = ? ORDER BY id ASC");
                        $stmt->execute([$alb_id]);
                        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (!empty($photos)):
                            // Reorder for PAIR Office Inauguration Photos: move golden shawl image to index 0
                            if ($alb['album_name'] === "PAIR Office Inauguration Photos") {
                                $foundIdx = -1;
                                foreach ($photos as $idx => $ph) {
                                    if (strpos($ph['photo_path'], 'WhatsApp Image 2026-06-02 at 12.38.18') !== false) {
                                        $foundIdx = $idx;
                                        break;
                                    }
                                }
                                if ($foundIdx !== -1) {
                                    $targetPhoto = $photos[$foundIdx];
                                    unset($photos[$foundIdx]);
                                    array_unshift($photos, $targetPhoto);
                                    $photos = array_values($photos);
                                }
                            }
                            $eventName = $alb['album_name'];
                            $eventMeta = $alb['album_date'] ? date('F d, Y', strtotime($alb['album_date'])) : '';
                            if ($alb['institute_prefix'] !== 'all') {
                                $eventMeta = strtoupper($alb['institute_prefix']) . ' • ' . $eventMeta;
                            }
                            ?>
                            <div class="event-block">
                                <div class="event-header-box">
                                    <div class="event-title-wrapper">
                                        <h3 class="event-title"><?php echo htmlspecialchars($eventName); ?></h3>
                                        <?php if (!empty($eventMeta)): ?>
                                            <span class="event-meta" style="font-size:13px; color:#64748b;"><?php echo htmlspecialchars($eventMeta); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="gallery-wrapper">
                                    <button class="nav-btn btn-left" onclick="scrollGallery(this, -1)">&#10094;</button>
                                    <ul class="gallery-list-horizontal">
                                        <?php foreach ($photos as $ph): ?>
                                            <li>
                                                <a href="<?php echo htmlspecialchars($ph['photo_path']); ?>" class="image-popup image-card">
                                                    <img src="<?php echo htmlspecialchars($ph['photo_path']); ?>"
                                                         alt="<?php echo htmlspecialchars($eventName); ?> photo"
                                                         loading="lazy"
                                                         style="object-fit: cover; width: 100%; height: 100%;">
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button class="nav-btn btn-right" onclick="scrollGallery(this, 1)">&#10095;</button>
                                </div>
                            </div>
                            <?php
                        endif;
                    endforeach;
                else:
                    // Fallback: original glob implementation
                    $events = [
                        "Kick off Meeting and Workshop Photos" => "gallery/kickoff meeting",
                        "PAIR Office Inauguration Photos" => "gallery/pair office Inaguration",
                        "Osmania University Two Day National Workshop" => "gallery/osmania University two day national workshop",
                    ];
                    foreach ($events as $eventName => $folder):
                        if (is_dir($folder)) {
                            $images = glob($folder . "/*.{jpg,JPG,jpeg,JPEG,png,PNG,gif,GIF}", GLOB_BRACE);
                            if ($images):
                                // Reorder for PAIR Office Inauguration Photos: move golden shawl image to index 0
                                if ($eventName === "PAIR Office Inauguration Photos" || $folder === "gallery/pair office Inaguration") {
                                    $foundIdx = -1;
                                    foreach ($images as $idx => $img) {
                                        if (strpos($img, 'WhatsApp Image 2026-06-02 at 12.38.18') !== false) {
                                            $foundIdx = $idx;
                                            break;
                                        }
                                    }
                                    if ($foundIdx !== -1) {
                                        $targetImg = $images[$foundIdx];
                                        unset($images[$foundIdx]);
                                        array_unshift($images, $targetImg);
                                        $images = array_values($images);
                                    }
                                }
                                $totalImages = count($images);
                                ?>
                                <div class="event-block">
                                    <div class="event-header-box">
                                        <div class="event-title-wrapper">
                                            <h3 class="event-title"><?php echo $eventName; ?></h3>
                                        </div>
                                    </div>
                                    <div class="gallery-wrapper">
                                        <button class="nav-btn btn-left" onclick="scrollGallery(this, -1)">&#10094;</button>
                                        <ul class="gallery-list-horizontal">
                                            <?php foreach ($images as $img): ?>
                                                <li>
                                                    <a href="<?php echo htmlspecialchars($img); ?>" class="image-popup image-card">
                                                        <img src="<?php echo htmlspecialchars($img); ?>"
                                                             alt="<?php echo $eventName; ?> photo"
                                                             loading="lazy"
                                                             style="object-fit: cover; width: 100%; height: 100%;">
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <button class="nav-btn btn-right" onclick="scrollGallery(this, 1)">&#10095;</button>
                                    </div>
                                </div>
                                <?php
                            endif;
                        }
                    endforeach;
                endif;
                ?>
            </div>
        </section>
    </div>

<script>
    function scrollGallery(btn, direction) {
        const list = btn.parentElement.querySelector('.gallery-list-horizontal');
        const scrollAmount = list.clientWidth * 0.8;
        list.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
</script>