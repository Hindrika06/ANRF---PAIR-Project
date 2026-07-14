<?php
require_once 'config.php';

$conferences = [];
$error       = '';

try {
    $stmt        = $pdo->query("SELECT * FROM uoh_conferences ORDER BY conf_date ASC");
    $conferences = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Could not load records: ' . $e->getMessage();
}
?>



<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb">
        <li><a href="#">Home</a></li>
        <li><a href="#">Events</a></li>
        <li class="active">Conferences</li>
    </ol>
</div>
<!-- end Breadcrumb -->

<style>
    /* ── Section layout ── */
    .conf-section {
        padding: 48px 0 72px;
    }

    /* ── Page heading block ── */
    .conf-heading-block {
        text-align: center;
        margin: 0 0 44px;
    }

    .conf-heading-eyebrow {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: #b91c1c;
        margin-bottom: 10px;
    }

    .conf-heading-block h2.conf-heading::after,
    .conf-heading-block h2.conf-heading::before {
        display: none !important;
        content: none !important;
        background: none !important;
        height: 0 !important;
        width: 0 !important;
    }

    .conf-heading-block h2.conf-heading {
        font-size: 32px !important;
        font-weight: 900 !important;
        color: #1a1a1a !important;
        letter-spacing: -0.5px !important;
        line-height: 1.1 !important;
        margin: 0 0 12px !important;
        text-transform: uppercase !important;
        border: none !important;
        padding: 0 !important;
        background: none !important;
        text-align: center !important;
        text-decoration: none !important;
        border-bottom: none !important;
        box-shadow: none !important;
    }

    .conf-section-sub {
        font-size: 15px;
        color: #6b7280;
        margin: 0;
        line-height: 1.6;
    }

    /* ── Col top spacing ── */
    .conf-col-wrap {
        margin-top: 30px;
    }

    /* ── Card ── */
    .conf-card {
        display: flex;
        flex-direction: row;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0px;
        overflow: hidden;
        margin-bottom: 28px;
        transition: box-shadow 0.22s ease, transform 0.22s ease;
    }

    .conf-card:hover {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.09);
        transform: translateY(-2px);
    }

    /* ── Image strip ── */
    .conf-card-image {
        width: 340px;
        min-width: 340px;
        min-height: 240px;
        background: #f3f4f6;
        position: relative;
        overflow: hidden;
    }

    .conf-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .conf-no-image {
        width: 100%;
        height: 100%;
        min-height: 240px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #d1d5db;
        font-size: 13px;
    }

    .conf-no-image svg {
        width: 42px;
        height: 42px;
        stroke: #d1d5db;
        fill: none;
        stroke-width: 1.4;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    /* ── Date badge ── */
    .conf-date-badge {
        position: absolute;
        top: 16px;
        left: 16px;
        background: #ffffff;
        border-radius: 0px;
        padding: 8px 14px;
        text-align: center;
        line-height: 1.2;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
    }

    .conf-date-badge .day {
        display: block;
        font-size: 30px;
        font-weight: 800;
        color: #b91c1c;
    }

    .conf-date-badge .mon {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    /* ── Card body ── */
    .conf-card-body {
        flex: 1;
        padding: 40px 44px;
        display: flex;
        flex-direction: column;
    }

    .conf-card-title {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 20px;
        line-height: 1.45;
    }

    /* ── Meta pills row ── */
    .conf-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }

    .conf-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 0px;
        padding: 6px 16px 6px 12px;
    }

    .conf-meta-item svg {
        width: 15px;
        height: 15px;
        stroke: #6b7280;
        fill: none;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
        flex-shrink: 0;
    }

    /* ── Divider ── */
    .conf-divider {
        border: none;
        border-top: 1px solid #f3f4f6;
        margin: 4px 0 18px;
    }

    /* ── Description ── */
    .conf-description {
        font-size: 15.5px;
        color: #6b7280;
        line-height: 1.9;
        margin: 0;
    }

    /* ── Empty state ── */
    .conf-empty {
        text-align: center;
        padding: 80px 20px;
        color: #9ca3af;
    }

    .conf-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }

    .conf-empty-icon svg {
        width: 28px;
        height: 28px;
        stroke: #d1d5db;
        fill: none;
        stroke-width: 1.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .conf-empty p {
        font-size: 15px;
        margin: 0;
        color: #9ca3af;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .conf-heading {
            font-size: 28px;
        }

        .conf-card {
            flex-direction: column;
        }

        .conf-card-image {
            width: 100%;
            min-width: unset;
            height: 240px;
        }

        .conf-card-body {
            padding: 26px 24px;
        }
    }
</style>

<!-- Page Content -->
<div id="page-content">
    <div class="container">
        <div class="row">
            <div class="col-md-12 conf-col-wrap">
                <div id="page-main">
                    <div class="conf-section">

                        <!-- Heading block -->
                        <div class="conf-heading-block">
                            <span class="conf-heading-eyebrow">Academic Events</span>
                            <h2 class="conf-heading">Conferences</h2>
                            <p class="conf-section-sub">Browse all upcoming and past conferences</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>

                        <?php elseif (empty($conferences)): ?>
                            <div class="conf-empty">
                                <div class="conf-empty-icon">
                                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </div>
                                <p>No conferences have been added yet.</p>
                            </div>

                        <?php else: ?>

                            <?php foreach ($conferences as $conf): ?>
                                <div class="conf-card">

                                    <!-- Image / date strip -->
                                    <div class="conf-card-image">
                                        <?php if (!empty($conf['image']) && file_exists($conf['image'])): ?>
                                            <img src="<?= htmlspecialchars($conf['image']) ?>" alt="<?= htmlspecialchars($conf['title']) ?>">
                                        <?php else: ?>
                                            <div class="conf-no-image">
                                                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                                <span>No image</span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($conf['conf_date'])): ?>
                                            <div class="conf-date-badge">
                                                <span class="day"><?= date('d', strtotime($conf['conf_date'])) ?></span>
                                                <span class="mon"><?= date('M Y', strtotime($conf['conf_date'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Content body -->
                                    <div class="conf-card-body">

                                        <h3 class="conf-card-title"><?= htmlspecialchars($conf['title'] ?: 'Untitled Conference') ?></h3>

                                        <div class="conf-meta-row">
                                            <?php if (!empty($conf['organisers'])): ?>
                                                <span class="conf-meta-item">
                                                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                    <?= htmlspecialchars($conf['organisers']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($conf['institute'])): ?>
                                                <span class="conf-meta-item">
                                                    <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                                    <?= htmlspecialchars($conf['institute']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($conf['investigator'])): ?>
                                                <span class="conf-meta-item">
                                                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                                    <?= htmlspecialchars($conf['investigator']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($conf['content'])): ?>
                                            <hr class="conf-divider">
                                            <p class="conf-description"><?= htmlspecialchars($conf['content']) ?></p>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>
                </div><!-- /#page-main -->
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->
    </div><!-- /.container -->
</div>
<!-- end Page Content -->

<?php include 'footer.php'; ?>

</div>

</body>