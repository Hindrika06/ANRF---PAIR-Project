<!-- WHATS NEW SCROLLING -->
<?php
if (!isset($pdo)) {
    require_once 'config.php';
}

$tickerItems = [];
try {
    // 1. Fetch active announcements
    $stmtAnn = $pdo->query("SELECT title, link FROM `announcements` WHERE is_active = 1 ORDER BY id DESC");
    while ($ann = $stmtAnn->fetch(PDO::FETCH_ASSOC)) {
        $tickerItems[] = [
            'title' => $ann['title'],
            'link'  => $ann['link'] ?: 'events_activities.php'
        ];
    }

    // 2. Fetch published events
    $stmtEvt = $pdo->query("SELECT id, title, event_date, end_date, venue FROM `events` WHERE publish_status = 1 ORDER BY event_date ASC");
    while ($evt = $stmtEvt->fetch(PDO::FETCH_ASSOC)) {
        $dateStr = date("M d, Y", strtotime($evt['event_date']));
        $tickerItems[] = [
            'title' => "📢 " . $evt['title'] . " – " . $dateStr . " at " . $evt['venue'],
            'link'  => "event-detail.php?id=" . $evt['id']
        ];
    }
} catch (PDOException $e) {
    // Silently handle if database error
}
?>
        <div class="whatsnew-bar">
            <div class="whatsnew-title">What's New</div>
            <div class="whatsnew-scroll">
                <marquee behavior="scroll" direction="left" scrollamount="6">
                    <?php if (!empty($tickerItems)): ?>
                        <?php foreach ($tickerItems as $item): ?>
                            <a href="<?= htmlspecialchars($item['link']) ?>">
                                <?= htmlspecialchars($item['title']) ?>
                            </a>
                            &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Welcome to ANRF-PAIR Project Portal</span>
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