<?php 
// 1. INTEGRATE DATABASE CONNECTION VIA YOUR EXISTING CONFIG FILE
require_once 'config.php'; 

include 'header.php';

// Fetch webinars from the database
$webinars = [];
$hasData = false;

try {
    // Self-healing schema update for uoh_webinars table if publish_status is missing
    try {
        $existingColumns = $pdo->query("SHOW COLUMNS FROM `uoh_webinars`")->fetchAll(PDO::FETCH_COLUMN);
        if ($existingColumns && !in_array('publish_status', $existingColumns, true)) {
            $pdo->exec("ALTER TABLE `uoh_webinars` ADD COLUMN `publish_status` TINYINT(1) NOT NULL DEFAULT 1");
        }
    } catch (Exception $ignored) {}

    try {
        $stmt = $pdo->query("SELECT * FROM uoh_webinars WHERE publish_status = 1 ORDER BY webinar_date DESC, id DESC");
    } catch (PDOException $ex) {
        $stmt = $pdo->query("SELECT * FROM uoh_webinars ORDER BY webinar_date DESC, id DESC");
    }

    $webinars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($webinars)) {
        $hasData = true;
    }
} catch (PDOException $e) {
    // Catch database errors gracefully
}
?>



<div class="container">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li class="active">Webinars & Events</li>
    </ol>
</div>

<div id="page-content" style="padding-top: 15px;">
    <div class="container">
        
        <!-- Clean Editorial Hero Banner -->
        <div class="webinar-hero-banner">
            <div class="hero-badge-container">
                <span class="hero-badge">Events & Knowledge Sharing</span>
            </div>
            <h1>Webinar Series</h1>
            <p>Join our academic discussions and expert-led webinars hosted under the ANRF–PAIR initiative.</p>
        </div>

        <!-- Search & Filter Controls -->
        <div class="webinar-filter-bar">
            <div class="search-input-wrapper">
                <i class="fa fa-search search-icon"></i>
                <input type="text" id="webinar-search" placeholder="Search by title, speaker, description...">
            </div>
            <div class="filter-tabs">
                <button class="filter-btn active" data-filter="all">All Webinars</button>
                <button class="filter-btn" data-filter="upcoming">Upcoming</button>
                <button class="filter-btn" data-filter="past">Past / Recording</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div id="page-main">
                    <section class="events" id="events">
                        <div class="section-content">

                            <?php if ($hasData): ?>
                                <div class="webinar-list">
                                    <?php foreach ($webinars as $row): 
                                        // Parse the 'webinar_date' column
                                        $timestamp = !empty($row['webinar_date']) ? strtotime($row['webinar_date']) : time();
                                        $month = date('M', $timestamp);
                                        $day   = date('d', $timestamp);
                                        $year  = date('Y', $timestamp);
                                        $fullDisplayDate = date('F d, Y \a\t h:i A', $timestamp);
                                        
                                        // Determine if upcoming or past
                                        $isUpcoming = $timestamp >= time();
                                        $typeAttr = $isUpcoming ? 'upcoming' : 'past';

                                        // Defensive fallback mapping
                                        $speakerVal     = !empty($row['speaker_name']) ? $row['speaker_name'] : ($row['investigator'] ?? '');
                                        $affiliationVal = !empty($row['affiliation']) ? $row['affiliation'] : ($row['institute'] ?? '');
                                        $descriptionVal = !empty($row['description']) ? $row['description'] : ($row['content'] ?? '');
                                        $linkVal        = !empty($row['link']) ? $row['link'] : '';
                                        $organisersVal  = !empty($row['organisers']) ? $row['organisers'] : '';
                                    ?>
                                        <article class="webinar-card" 
                                                 data-title="<?= htmlspecialchars(strtolower($row['title'] ?? '')) ?>" 
                                                 data-speaker="<?= htmlspecialchars(strtolower($speakerVal)) ?>" 
                                                 data-description="<?= htmlspecialchars(strtolower($descriptionVal)) ?>" 
                                                 data-organizer="<?= htmlspecialchars(strtolower($organisersVal)) ?>" 
                                                 data-type="<?= $typeAttr ?>">
                                            
                                            <!-- Date Badge (Calendar Style) -->
                                            <div class="webinar-date-card">
                                                <div class="webinar-date-header"><?= htmlspecialchars($month) ?></div>
                                                <div class="webinar-date-body">
                                                    <span class="webinar-date-day"><?= htmlspecialchars($day) ?></span>
                                                    <span class="webinar-date-year"><?= htmlspecialchars($year) ?></span>
                                                </div>
                                            </div>
                                            
                                            <!-- Main content -->
                                            <div class="webinar-card-content">
                                                <div class="webinar-status-badge <?= $isUpcoming ? 'badge-upcoming' : 'badge-past' ?>">
                                                    <?= $isUpcoming ? 'Upcoming Session' : 'Completed Session' ?>
                                                </div>
                                                
                                                <h3 class="webinar-title"><?= htmlspecialchars($row['title'] ?? 'Untitled Webinar') ?></h3>

                                                <div class="webinar-meta-grid">
                                                    <div class="meta-item">
                                                        <i class="fa fa-clock-o meta-icon"></i>
                                                        <span><?= htmlspecialchars(date('h:i A', $timestamp)) ?> (IST)</span>
                                                    </div>
                                                    
                                                    <?php if (!empty($speakerVal)): ?>
                                                        <div class="meta-item">
                                                            <i class="fa fa-user-md meta-icon"></i>
                                                            <span><strong>Speaker:</strong> <?= htmlspecialchars($speakerVal) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($affiliationVal)): ?>
                                                        <div class="meta-item">
                                                            <i class="fa fa-university meta-icon"></i>
                                                            <span><?= htmlspecialchars($affiliationVal) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <p class="webinar-description"><?= nl2br(htmlspecialchars($descriptionVal)) ?></p>

                                                <div class="webinar-footer-details">
                                                    <?php if (!empty($organisersVal)): ?>
                                                        <div class="webinar-tag">
                                                            <span class="tag-label">Organized by</span>
                                                            <span class="tag-value"><?= htmlspecialchars($organisersVal) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($linkVal)): ?>
                                                        <a href="<?= htmlspecialchars($linkVal) ?>" target="_blank" class="webinar-btn <?= $isUpcoming ? 'btn-join' : 'btn-recording' ?>">
                                                            <i class="fa <?= $isUpcoming ? 'fa-video-camera' : 'fa-play-circle' ?>"></i>
                                                            <?= $isUpcoming ? 'Register / Join' : 'View Recording' ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Card Image Thumbnail -->
                                            <div class="webinar-image-wrapper">
                                                <?php if (!empty($row['image'])): ?>
                                                    <img src="admin/<?= htmlspecialchars($row['image']) ?>" alt="Webinar Presentation" class="webinar-thumbnail">
                                                <?php else: ?>
                                                    <div class="webinar-fallback-thumbnail">
                                                        <i class="fa fa-graduation-cap"></i>
                                                        <span>Seminar</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                        </article>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Dynamic Client-side No Results State -->
                                <div id="no-results-message" style="display: none; text-align: center; padding: 50px 30px; border-radius: 16px; background: #ffffff; border: 1.5px dashed #cbd5e1; margin-top: 20px;">
                                    <i class="fa fa-search" style="font-size: 32px; color: #94a3b8; margin-bottom: 12px; display: block;"></i>
                                    <h4 style="margin: 0 0 6px; font-weight: 700; color: #334155;">No webinars match your filters</h4>
                                    <p style="margin: 0; color: #64748b; font-size: 14px;">Try modifying your keywords or choosing a different status filter.</p>
                                </div>

                            <?php else: ?>
                                <div class="alert alert-info" style="text-align: center; padding: 40px; border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569;">
                                    <i class="fa fa-calendar-o" style="font-size: 32px; margin-bottom: 12px; color: #94a3b8; display: block;"></i>
                                    <h4 style="margin: 0 0 6px; font-weight: 700; color: #334155;">No webinars in records</h4>
                                    <p style="margin: 0; color: #64748b; font-size: 14px;">There are currently no webinars loaded in the database.</p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php';?>

</div>

<!-- Interactive Client-side Filter Logic -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("webinar-search");
    const filterBtns = document.querySelectorAll(".filter-btn");
    const cards = document.querySelectorAll(".webinar-card");
    const noResults = document.getElementById("no-results-message");

    let activeFilter = "all";
    let searchQuery = "";

    function filterCards() {
        let visibleCount = 0;
        
        cards.forEach(card => {
            const title = card.getAttribute("data-title") || "";
            const speaker = card.getAttribute("data-speaker") || "";
            const desc = card.getAttribute("data-description") || "";
            const organizer = card.getAttribute("data-organizer") || "";
            const cardType = card.getAttribute("data-type") || "";

            const matchesSearch = 
                title.includes(searchQuery) || 
                speaker.includes(searchQuery) || 
                desc.includes(searchQuery) || 
                organizer.includes(searchQuery);

            const matchesTab = (activeFilter === "all") || (cardType === activeFilter);

            if (matchesSearch && matchesTab) {
                card.style.display = "flex";
                // Trigger a clean animation frame fade
                card.style.opacity = "1";
                card.style.transform = "scale(1)";
                visibleCount++;
            } else {
                card.style.display = "none";
                card.style.opacity = "0";
                card.style.transform = "scale(0.98)";
            }
        });

        if (noResults) {
            if (visibleCount === 0) {
                noResults.style.display = "block";
            } else {
                noResults.style.display = "none";
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            searchQuery = e.target.value.toLowerCase().trim();
            filterCards();
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            filterBtns.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            activeFilter = btn.getAttribute("data-filter");
            filterCards();
        });
    });
});
</script>

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

    /* Clean Editorial Hero Banner */
    .webinar-hero-banner {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border: 1px solid #e2e8f0;
        border-left: 5px solid #024283;
        padding: 30px 35px;
        border-radius: 16px;
        margin-bottom: 30px;
        text-align: left;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
    }
    .webinar-hero-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 380px;
        height: 380px;
        background: radial-gradient(circle, rgba(2, 66, 131, 0.03) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-badge-container {
        display: block;
        margin-bottom: 10px;
    }
    .hero-badge {
        background: rgba(179, 58, 58, 0.08);
        color: #b33a3a;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        display: inline-block;
        border: 1px solid rgba(179, 58, 58, 0.15);
    }
    .webinar-hero-banner h1 {
        display: block !important;
        font-family: 'Montserrat', sans-serif !important;
        font-size: 28px !important;
        font-weight: 800 !important;
        color: #024283 !important;
        margin: 10px 0 10px 0 !important;
        letter-spacing: -0.5px !important;
        text-transform: uppercase !important;
    }
    .webinar-hero-banner h1:after {
        display: none !important;
    }
    .webinar-hero-banner p {
        font-size: 14.5px;
        color: #475569;
        max-width: 750px;
        margin: 0;
        line-height: 1.6;
    }

    /* Filter Bar style */
    .webinar-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
        background: #ffffff;
        padding: 16px 24px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        border: 1px solid #e2e8f0;
    }
    .search-input-wrapper {
        position: relative;
        flex: 1;
        max-width: 450px;
    }
    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 15px;
    }
    #webinar-search {
        width: 100%;
        padding: 12px 16px 12px 44px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14.5px;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.2s ease;
    }
    #webinar-search:focus {
        outline: none;
        border-color: #024283;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(2, 66, 131, 0.08);
    }
    .filter-tabs {
        display: flex;
        gap: 8px;
    }
    .filter-btn {
        background: #f1f5f9;
        color: #475569;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-btn:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .filter-btn.active {
        background: #024283;
        color: #ffffff;
    }

    /* Webinar Cards */
    .webinar-list {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    .webinar-card {
        display: flex;
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 24px;
        gap: 24px;
        align-items: stretch;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .webinar-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -3px rgba(0, 0, 0, 0.05), 0 8px 8px -4px rgba(0, 0, 0, 0.03);
        border-color: #cbd5e1;
    }
    .webinar-card::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: transparent;
        transition: background 0.3s ease;
    }
    .webinar-card[data-type="upcoming"]:hover::after {
        background: #22c55e;
    }
    .webinar-card[data-type="past"]:hover::after {
        background: #024283;
    }

    /* Date Badges */
    .webinar-date-card {
        display: flex;
        flex-direction: column;
        width: 76px;
        height: 86px;
        border-radius: 12px;
        overflow: hidden;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        flex-shrink: 0;
        text-align: center;
        background: #ffffff;
    }
    .webinar-date-header {
        background: #b33a3a;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding: 4px 0;
        line-height: 1.2;
    }
    .webinar-date-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: #f8fafc;
    }
    .webinar-date-day {
        font-size: 24px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1;
    }
    .webinar-date-year {
        font-size: 10px;
        font-weight: 600;
        color: #64748b;
        margin-top: 2px;
    }

    /* Card Contents */
    .webinar-card-content {
        flex-grow: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }
    .webinar-status-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
        display: inline-flex;
        align-self: flex-start;
        padding: 3px 8px;
        border-radius: 6px;
    }
    .badge-upcoming {
        background: rgba(34, 197, 94, 0.1);
        color: #166534;
    }
    .badge-past {
        background: rgba(2, 66, 131, 0.08);
        color: #024283;
    }
    .webinar-title {
        font-family: 'Montserrat', sans-serif !important;
        font-size: 19px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.4 !important;
        margin: 0 0 10px 0 !important;
        text-transform: none !important;
        transition: color 0.2s ease;
    }
    .webinar-card:hover .webinar-title {
        color: #024283 !important;
    }
    .webinar-meta-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 12px;
        align-items: center;
    }
    .meta-item {
        display: flex;
        align-items: center;
        font-size: 13px;
        color: #475569;
        gap: 6px;
    }
    .meta-icon {
        color: #b33a3a;
        font-size: 13px;
    }
    .webinar-description {
        font-size: 14px;
        color: #475569;
        line-height: 1.6;
        margin: 0 0 18px 0;
    }
    .webinar-footer-details {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    /* Tag styles */
    .webinar-tag {
        display: inline-flex;
        align-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        font-size: 12px;
    }
    .tag-label {
        background: #f1f5f9;
        color: #475569;
        padding: 5px 10px;
        font-weight: 600;
        border-right: 1px solid #e2e8f0;
    }
    .tag-value {
        background: #ffffff;
        color: #1e293b;
        padding: 5px 12px;
        font-weight: 500;
    }

    /* Button styles */
    .webinar-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }
    .btn-join {
        background: #22c55e;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
    }
    .btn-join:hover {
        background: #16a34a;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(34, 197, 94, 0.25);
    }
    .btn-recording {
        background: #024283;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(2, 66, 131, 0.15);
    }
    .btn-recording:hover {
        background: #0d2c54;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(2, 66, 131, 0.25);
    }

    /* Image Wrapper */
    .webinar-image-wrapper {
        width: 200px;
        height: 135px;
        border-radius: 12px;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        background: #f8fafc;
    }
    .webinar-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .webinar-card:hover .webinar-thumbnail {
        transform: scale(1.05);
    }

    /* Fallback Image */
    .webinar-fallback-thumbnail {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        color: #94a3b8;
    }
    .webinar-fallback-thumbnail i {
        font-size: 28px;
        margin-bottom: 6px;
        color: #cbd5e1;
    }
    .webinar-fallback-thumbnail span {
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    /* Responsive Breakdown */
    @media (max-width: 991px) {
        .webinar-card {
            flex-direction: column;
            align-items: stretch;
            padding: 20px;
            gap: 16px;
        }
        .webinar-date-card {
            flex-direction: row;
            width: auto;
            height: auto;
            border: none;
            box-shadow: none;
            background: transparent;
            align-items: center;
            gap: 8px;
        }
        .webinar-date-header {
            padding: 4px 10px;
            border-radius: 6px;
        }
        .webinar-date-body {
            flex-direction: row;
            background: transparent;
            gap: 4px;
        }
        .webinar-date-day {
            font-size: 16px;
        }
        .webinar-date-year {
            font-size: 13px;
            margin-top: 0;
        }
        .webinar-image-wrapper {
            width: 100%;
            height: 170px;
            order: -1; /* image on top */
        }
    }
</style>

</body>
</html>