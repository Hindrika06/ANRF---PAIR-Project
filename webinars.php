<?php 
// 1. INTEGRATE DATABASE CONNECTION VIA YOUR EXISTING CONFIG FILE
require_once 'config.php'; 

include 'header.php';

// Fetch webinars from the database
$webinars = [];
$hasData = false;

try {
    $stmt = $pdo->query("SELECT * FROM uoh_webinars ORDER BY webinar_date DESC, id DESC");
    $webinars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($webinars)) {
        $hasData = true;
    }
} catch (PDOException $e) {
    echo "<div class='container' style='margin-top:20px;'><div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div></div>";
}
?>

<div class="wrapper">

<div class="container">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li class="active">Webinars & Events</li>
    </ol>
</div>
<div id="page-content">
    <div class="container">
        
        <div class="webinar-section__head" style="text-align: center; max-width: 720px; margin: 10px auto 35px;">
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; color: #B33A3A; font-weight: 600; margin: 0 0 8px;">Events Showcase</p>
            <h2 class="no-theme-underline" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif !important; font-weight: 700 !important; font-size: 32px !important; line-height: 1.2 !important; margin: 0 0 12px !important; color: #1B3A6B !important; text-transform: uppercase !important; text-decoration: none !important; border: none !important; background: none !important; box-shadow: none !important;">WEBINARS</h2>
            <p style="font-size: 15.5px; color: #6B7280; line-height: 1.6; margin: 0;">Knowledge sharing sessions and academic discussions hosted under the ANRF–PAIR initiative.</p>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div id="page-main">
                    <section class="events" id="events">
                        <div class="section-content">

                            <?php if ($hasData): ?>
                                <?php foreach ($webinars as $row): 
                                    // Parse the 'webinar_date' column
                                    $timestamp = !empty($row['webinar_date']) ? strtotime($row['webinar_date']) : time();
                                    $month = date('M', $timestamp);
                                    $day   = date('d', $timestamp);
                                    $year  = date('Y', $timestamp);
                                    $fullDisplayDate = date('F d, Y \a\t h:i A', $timestamp);
                                ?>
                                    <article class="event-row">
                                        
                                        <div class="event-date-badge">
                                            <div class="badge-month"><?= htmlspecialchars($month) ?></div>
                                            <div class="badge-day"><?= htmlspecialchars($day) ?></div>
                                            <div class="badge-year"><?= htmlspecialchars($year) ?></div>
                                        </div>
                                        
                                        <div class="event-main-details">
                                            <header>
                                                <h3><?= htmlspecialchars($row['title'] ?? 'Untitled Webinar') ?></h3>
                                            </header>

                                            <div class="event-meta-info">
                                                <span><i class="fa fa-calendar icon-red"></i> <?= htmlspecialchars($fullDisplayDate) ?></span>
                                                
                                                <?php if (!empty($row['investigator'])): ?>
                                                    <span><i class="fa fa-user icon-red"></i> <strong>Investigator:</strong> <?= htmlspecialchars($row['investigator']) ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($row['institute'])): ?>
                                                    <span><i class="fa fa-university icon-red"></i> <?= htmlspecialchars($row['institute']) ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="event-body-content">
                                                <p><?= nl2br(htmlspecialchars($row['content'] ?? '')) ?></p>
                                                
                                                <?php if (!empty($row['organisers'])): ?>
                                                    <div class="event-organiser-tag">
                                                        <strong>Organized by:</strong> <?= htmlspecialchars($row['organisers']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="event-image-container">
                                            <?php if (!empty($row['image'])): ?>
                                                <img src="admin/<?= htmlspecialchars($row['image']) ?>" alt="Webinar Presentation">
                                            <?php else: ?>
                                                <div class="image-fallback-placeholder">
                                                    <i class="fa fa-television"></i>
                                                    <span>Webinar</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info" style="text-align: center; padding: 30px; border-radius: 8px;">
                                    <i class="fa fa-calendar-o" style="font-size: 28px; margin-bottom: 10px; color: #999; display: block;"></i>
                                    No webinars found in records.
                                </div>
                            <?php endif; ?>

                        </div></section></div></div></div></div></div>
<?php include 'footer.php';?>

</div>

<style>
    /* Kill any template underlines */
    .webinar-section__head h2::after,
    .webinar-section__head h2::before,
    h2.no-theme-underline::after,
    h2.no-theme-underline::before {
        display: none !important;
        content: none !important;
        border: none !important;
        background: none !important;
        height: 0 !important;
        width: 0 !important;
    }

    /* Plain row layout - card box removed, separated by a thin divider */
    .event-row {
        margin-bottom: 0;
        padding: 24px 0;
        display: flex;
        gap: 25px;
        align-items: flex-start;
        border-bottom: 1px solid #e6e9ed;
    }
    .event-row:first-of-type {
        padding-top: 0;
    }
    .event-row:last-of-type {
        border-bottom: none;
    }

    /* Date badge with month, day, and year */
    .event-date-badge {
        background-color: #0d2c54; 
        color: #ffffff; 
        text-align: center; 
        width: 68px; 
        height: 80px;
        padding: 10px 0;
        display: block;
        flex-shrink: 0; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        box-sizing: border-box;
    }
    .badge-month {
        text-transform: uppercase; 
        font-size: 11px !important; 
        font-weight: 600 !important; 
        letter-spacing: 0.05em !important;
        line-height: 1 !important;
        margin: 0 0 4px 0 !important;
        padding: 0;
        opacity: 0.9;
    }
    .badge-day {
        font-size: 26px !important; 
        font-weight: 700 !important; 
        line-height: 1 !important;
        margin: 0 !important;
        padding: 0;
        letter-spacing: -0.02em;
    }
    .badge-year {
        font-size: 11px !important;
        font-weight: 600 !important;
        letter-spacing: 0.03em !important;
        line-height: 1 !important;
        margin: 4px 0 0 0 !important;
        padding: 0;
        opacity: 0.85;
    }

    /* Text areas column */
    .event-main-details {
        flex-grow: 1; 
        min-width: 0;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    .event-main-details h3 {
        margin: 0 0 8px 0; 
        font-size: 20px; 
        font-weight: 700; 
        color: #1b3a6b;
        line-height: 1.4;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    .event-meta-info {
        font-size: 13px; 
        color: #666; 
        margin-bottom: 12px; 
        display: flex; 
        flex-wrap: wrap; 
        gap: 14px; 
        align-items: center;
    }
    .icon-red {
        color: #b33a3a; 
        margin-right: 3px;
    }
    .event-body-content {
        font-size: 14.5px; 
        color: #4a5568; 
        line-height: 1.6;
    }
    .event-organiser-tag {
        font-size: 13px; 
        color: #4a5568; 
        background: #f8f9fa; 
        padding: 5px 12px; 
        border-left: 3px solid #b33a3a; 
        display: inline-block; 
        margin-top: 10px;
        border-radius: 0 4px 4px 0;
    }

    /* Fixed Image Area Layout mapped to admin folder */
    .event-image-container {
        width: 150px;
        height: 110px;
        border-radius: 6px;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        background: #f7fafc;
    }
    .event-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Fallback placeholder */
    .image-fallback-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #a0aec0;
        background: #f8fafc;
    }
    .image-fallback-placeholder i {
        font-size: 24px;
        margin-bottom: 4px;
    }
    .image-fallback-placeholder span {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Mobile view breakdown rule protection */
    @media (max-width: 767px) {
        .event-row {
            flex-direction: column !important;
            gap: 16px !important;
        }
        .event-date-badge {
            align-self: flex-start;
        }
        .event-image-container {
            width: 100% !important;
            height: 160px !important;
            margin-top: 10px;
        }
    }
</style>

</body>
</html>