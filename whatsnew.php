<!-- WHATS NEW SCROLLING -->
<?php
if (!isset($pdo)) {
    require_once 'config.php';
}

$activeAnnouncements = [];
try {
    $stmt = $pdo->query("SELECT * FROM `announcements` WHERE is_active = 1 ORDER BY id DESC");
    $activeAnnouncements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback silently if table or query fails
}
?>
        <div class="whatsnew-bar">
            <div class="whatsnew-title">What's New</div>
            <div class="whatsnew-scroll">
                <marquee behavior="scroll" direction="left" scrollamount="6">
                    <?php if (!empty($activeAnnouncements)): ?>
                        <?php foreach ($activeAnnouncements as $ann): ?>
                            <?php if (!empty($ann['link'])): ?>
                                <a href="<?= htmlspecialchars($ann['link']) ?>">
                                    <?= htmlspecialchars($ann['title']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color: #ffffff; font-size: 14px; font-weight: 600;">
                                    <?= htmlspecialchars($ann['title']) ?>
                                </span>
                            <?php endif; ?>
                            &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback: Static original list -->
                        <a href="event-detail.html">📢 Webinar on SMART NANO BIOSENSORS – May 20, 2026</a>
                        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                        <a href="events_activities.php">🎓 Osmania University Education Week: May 11–17, 2026</a>
                        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                        <a href="gallery.php">📸 New Event Photos Uploaded in Gallery</a>
                        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                </marquee>
            </div>
        </div>


        <style>
/* WHATS NEW BAR */
.whatsnew-bar{
    display:flex;
    align-items:center;
    background:#ffffff;
    border-bottom:1px solid #ddd;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

.whatsnew-title{
    background:#bc2121;
    color:#fff;
    width:190px;              /* Fixed width */
    height:38px;              /* Slimmer height */
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:14px;
    font-weight:700;
}

.whatsnew-scroll{
    flex:1;
    background:#0b4c8c;
    height:38px;              /* Slimmer height */
    display:flex;
    align-items:center;
    padding:0 15px;
}

.whatsnew-scroll marquee{
    line-height:38px;
    height:38px;
}

.whatsnew-scroll a{
    color:#ffffff;
    font-size:14px;
    font-weight:600;
    text-decoration:none;
}

.whatsnew-scroll a:hover{
    color:#ffffff;
}

/* MOBILE */
@media(max-width:768px){

    .whatsnew-title{
        padding:12px 14px;
        font-size:13px;
    }

    .whatsnew-scroll a{
        font-size:12px;
    }

}
</style>