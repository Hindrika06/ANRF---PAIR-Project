<?php
$bodyClass = 'page-homepage-courses'; 

// 1. INTEGRATE DATABASE CONNECTION VIA YOUR EXISTING CONFIG FILE
require_once 'config.php'; 

// Fetch progress reports from the database
$reports = [];
$hasData = false;

try {
    $stmt = $pdo->query("SELECT * FROM uoh_progress_reports ORDER BY created_at DESC, id DESC");
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasData = !empty($reports);
} catch (PDOException $e) {
    echo "<div class='container' style='margin-top:20px;'><div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div></div>";
}
?>



<!-- Header -->
<?php include 'header.php';?>
<!-- end Header -->

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li class="active">Progress Reports</li>
    </ol>
</div>
<!-- end Breadcrumb -->

<!-- Page Content -->
<div id="page-content">
    <div class="pr-page">
        <div class="container">

            <div class="pr-header">
                <p class="pr-eyebrow">ANRF&ndash;PAIR Project</p>
                <div class="pr-title" role="heading" aria-level="1">Progress Reports</div>
                <p class="pr-subtitle">Status updates on active research tasks, covering objectives, methodology, and outcomes to date.</p>
            </div>

            <?php if ($hasData): ?>
            <div class="pr-table-wrap" id="prTableWrap">
                <table class="pr-table">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>PI / Co-PI</th>
                            <th>Objective</th>
                            <th>Methodology</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $row): ?>
                            <tr class="pr-card-row">
                                
                                <td class="pr-td-title" data-label="Project Title">
                                    <?= htmlspecialchars($row['project_title'] ?? 'Untitled Project') ?>
                                </td>
                                <td class="pr-td-people" data-label="PI / Co-PI">
                                    <?php if (!empty($row['pi_name'])): ?>
                                        <div class="pr-person"><strong>PI</strong> <?= htmlspecialchars($row['pi_name']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($row['co_pi_name'])): ?>
                                        <div class="pr-person"><strong>Co-PI</strong> <?= htmlspecialchars($row['co_pi_name']) ?></div>
                                    <?php endif; ?>
                                    <?php if (empty($row['pi_name']) && empty($row['co_pi_name'])): ?>
                                        &mdash;
                                    <?php endif; ?>
                                </td>
                                <td class="pr-td-text" data-label="Objective">
                                    <?= !empty($row['approved_objects']) ? nl2br(htmlspecialchars($row['approved_objects'])) : '&mdash;' ?>
                                </td>
                                <td class="pr-td-text" data-label="Methodology">
                                    <?= !empty($row['methodology']) ? nl2br(htmlspecialchars($row['methodology'])) : '&mdash;' ?>
                                </td>
                                <td class="pr-td-text pr-td-progress" data-label="Progress">
                                    <?= !empty($row['summary_progress']) ? nl2br(htmlspecialchars($row['summary_progress'])) : '&mdash;' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="pr-empty">
                    <p>No progress reports on file yet.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<!-- end Page Content -->

<!-- Footer -->
<?php include 'footer.php';?>
<!-- end Footer -->

</div>
<!-- end Wrapper -->

<script type="text/javascript" src="assets/js/jquery-2.1.0.min.js"></script>
<script type="text/javascript" src="assets/js/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="assets/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="assets/js/selectize.min.js"></script>
<script type="text/javascript" src="assets/js/owl.carousel.min.js"></script>
<script type="text/javascript" src="assets/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="assets/js/jquery.placeholder.js"></script>
<script type="text/javascript" src="assets/js/jQuery.equalHeights.js"></script>
<script type="text/javascript" src="assets/js/icheck.min.js"></script>
<script type="text/javascript" src="assets/js/jquery.vanillabox-0.1.5.min.js"></script>
<script type="text/javascript" src="assets/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="assets/js/greensock.js"></script>
<script type="text/javascript" src="assets/js/layerslider.transitions.js"></script>
<script type="text/javascript" src="assets/js/layerslider.kreaturamedia.jquery.js"></script>
<script type="text/javascript" src="assets/js/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="assets/js/retina-1.1.0.min.js"></script>

<script type="text/javascript" src="assets/js/custom.js"></script>

<style>
    :root {
        --pr-red: #B33A3A;
        --pr-red-dark: #962F2F;
        --pr-ink: #2A2D34;
        --pr-slate: #6B7280;
        --pr-hair: #E7E5E0;
        --pr-panel: #FAF7F4;
    }

    .pr-page { padding: 10px 0 60px !important; background: #fff !important; }

    .pr-header {
        max-width: 680px !important;
        margin: 10px auto 36px !important;
        text-align: center !important;
    }
    .pr-eyebrow {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        color: var(--pr-red) !important;
        margin: 0 0 10px !important;
        line-height: 1.4 !important;
    }
    .pr-title {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        font-weight: 700 !important;
        font-size: 32px !important;
        line-height: 1.2 !important;
        margin: 0 0 12px !important;
        padding: 0 !important;
        color: var(--pr-ink) !important;
        background: transparent !important;
        text-transform: none !important;
        letter-spacing: normal !important;
        border: none !important;
    }
    .pr-subtitle {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        font-size: 15px !important;
        color: var(--pr-slate) !important;
        line-height: 1.6 !important;
        margin: 0 !important;
    }

    /* ---------- Table wrap ---------- */
    .pr-table-wrap {
        max-width: 1180px !important;
        margin: 0 auto !important;
        border: 1px solid var(--pr-hair) !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04) !important;
        overflow: hidden !important;
    }

    .pr-table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        background: #fff !important;
        margin: 0 !important;
    }

    .pr-table thead th {
        background: var(--pr-red) !important;
        color: #fff !important;
        text-align: left !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        padding: 14px 18px !important;
        white-space: nowrap !important;
        border: none !important;
        vertical-align: middle !important;
    }

    .pr-table tbody td {
        padding: 16px 18px !important;
        font-size: 13.5px !important;
        color: var(--pr-ink) !important;
        line-height: 1.55 !important;
        border-bottom: 1px solid var(--pr-hair) !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        vertical-align: top !important;
        background: transparent !important;
    }

    .pr-table tbody tr:last-child td {
        border-bottom: none !important;
    }

    .pr-table tbody tr:nth-child(even) td {
        background: var(--pr-panel) !important;
    }

    .pr-table tbody tr:hover td {
        background: #F5EAE8 !important;
    }

    .pr-td-wp { white-space: nowrap !important; width: 1% !important; }
    .pr-td-title {
        font-weight: 700 !important;
        color: var(--pr-red-dark) !important;
        min-width: 180px !important;
    }
    .pr-td-people { white-space: nowrap !important; }
    .pr-person { font-size: 13px !important; color: var(--pr-slate) !important; margin: 0 !important; }
    .pr-person strong {
        color: var(--pr-ink) !important;
        font-weight: 700 !important;
        margin-right: 4px !important;
    }
    .pr-td-text { min-width: 220px !important; }
    .pr-td-progress { font-weight: 500 !important; }

    .pr-wp-tag {
        display: inline-block !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: var(--pr-red) !important;
        background: #fff !important;
        border: 1px solid var(--pr-red) !important;
        border-radius: 5px !important;
        padding: 4px 10px !important;
        white-space: nowrap !important;
        line-height: 1.4 !important;
    }

    .pr-empty {
        text-align: center !important;
        padding: 50px 20px !important;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        color: var(--pr-slate) !important;
        border: 1px dashed var(--pr-hair) !important;
        border-radius: 8px !important;
        max-width: 1180px !important;
        margin: 0 auto !important;
    }

    /* ============================================================
       MOBILE: cards instead of table rows (<=767px)
       Each <tr> becomes a card; each <td> becomes a labeled row
       using its data-label attribute. No horizontal scroll.
       ============================================================ */
    @media (max-width: 767px) {
        .pr-table-wrap {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            background: transparent !important;
        }

        .pr-table,
        .pr-table tbody {
            display: block !important;
            width: 100% !important;
        }

        .pr-table thead {
            display: none !important;
        }

        .pr-card-row {
            display: block !important;
            background: #fff !important;
            border: 1px solid var(--pr-hair) !important;
            border-radius: 10px !important;
            margin-bottom: 16px !important;
            padding: 4px 0 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
        }

        .pr-card-row:last-child {
            margin-bottom: 0 !important;
        }

        .pr-table tbody tr:nth-child(even) td,
        .pr-table tbody tr:hover td {
            background: transparent !important;
        }

        .pr-table tbody td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 10px 16px !important;
            border-bottom: 1px solid var(--pr-hair) !important;
        }

        .pr-card-row td:last-child {
            border-bottom: none !important;
        }

        /* Inject the column name as a label above each field */
        .pr-table tbody td[data-label]::before {
            content: attr(data-label) !important;
            display: block !important;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
            font-size: 10.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            color: var(--pr-red) !important;
            margin-bottom: 6px !important;
        }

        /* First field (Work Package) styled as the card header */
        .pr-card-row td.pr-td-wp {
            background: var(--pr-panel) !important;
            border-radius: 10px 10px 0 0 !important;
            border-bottom: 1px solid var(--pr-hair) !important;
            padding: 12px 16px !important;
        }
        .pr-card-row td.pr-td-wp::before {
            display: none !important;
        }

        .pr-td-title { min-width: 0 !important; font-size: 15px !important; }
        .pr-td-text { min-width: 0 !important; }
        .pr-person { white-space: normal !important; }
    }
</style>

<script>
(function () {
    // No-op placeholder retained for future enhancements.
})();
    if (!wrap || !scrollEl) return;

    function checkScrollEnd() {
        var atEnd = scrollEl.scrollLeft + scrollEl.clientWidth >= scrollEl.scrollWidth - 4;
        wrap.classList.toggle('is-scrolled-end', atEnd);
    }

    // Hide the hint entirely if the table doesn't actually overflow
    function checkOverflow() {
        if (scrollEl.scrollWidth <= scrollEl.clientWidth) {
            wrap.classList.add('is-scrolled-end');
        } else {
            checkScrollEnd();
        }
    }

    scrollEl.addEventListener('scroll', checkScrollEnd, { passive: true });
    window.addEventListener('resize', checkOverflow);
    checkOverflow();
})();
</script>