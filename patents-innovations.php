<?php
// ── Database & logic ─────────────────────────────────────────────────────────
require_once 'config.php';

$patents = [];
$error   = '';

try {
    $stmt = $pdo->query("SELECT * FROM uoh_patent ORDER BY id DESC");
    $patents = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Could not load patent records: ' . $e->getMessage();
}

// Helper to map status -> a CSS state class
function patentStatusClass($status) {
    switch ($status) {
        case 'Granted':   return 'is-granted';
        case 'Rejected':  return 'is-rejected';
        case 'Pending':   return 'is-pending';
        case 'Published': return 'is-published';
        default:          return 'is-filed';
    }
}
?>
<body class="page-homepage-courses">
<!-- Wrapper -->
<div class="wrapper">
<!-- Header -->
<?php include 'header.php';?>
<!-- end Header -->





<!-- Patent Registry Section -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    .pr-section {
        --navy: #1B3A6B;
        --navy-deep: #142C52;
        --maroon: #B33A3A;
        --maroon-deep: #962F2F;
        --text-dark: #1A1A1A;
        --text-grey: #6B7280;
        --border-grey: #E5E7EB;
        --bg-soft: #F8F9FB;
        --green: #2E7D52;
        --green-bg: #E7F4ED;
        --rust: #B33A3A;
        --rust-bg: #FBEAEA;
        --amber: #B07A1E;
        --amber-bg: #FBF1DD;
        --blue: #2C5C8C;
        --blue-bg: #E8EFF6;

        background: #fff;
        padding: 64px 0 72px;
        font-family: 'Inter', -apple-system, sans-serif;
        color: var(--text-dark);
    }

    .pr-section__inner {
        max-width: 1180px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .pr-section__head {
        text-align: center;
        max-width: 720px;
        margin: 0 auto 10px;
    }

    .pr-section__eyebrow {
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--maroon);
        font-weight: 600;
        margin: 0 0 10px;
    }

    .pr-section__title {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 32px;
        line-height: 1.2;
        margin: 0 0 12px;
        color: var(--navy);
        text-transform: uppercase;
        text-decoration: none !important;
    }

    .pr-section__subtitle {
        font-size: 15.5px;
        color: var(--text-grey);
        line-height: 1.6;
        margin: 0;
    }

    .pr-count-bar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 14px;
    }

    .pr-count-bar__pill {
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        font-weight: 600;
        color: var(--navy);
        background: var(--bg-soft);
        border: 1px solid var(--border-grey);
        border-radius: 100px;
        padding: 6px 16px;
    }

    /* ── Empty / error states ───────────────────────────────────────────── */
    .pr-notice {
        margin-top: 16px;
        padding: 32px 24px;
        border: 1px solid var(--border-grey);
        border-radius: 10px;
        background: var(--bg-soft);
        color: var(--text-grey);
        font-size: 15px;
        text-align: center;
    }
    .pr-notice.is-error {
        border-color: var(--rust);
        background: var(--rust-bg);
        color: var(--maroon-deep);
    }

    /* ── Desktop table ───────────────────────────────────────────────────── */
    .pr-table-wrap {
        display: block;
        border: 1px solid var(--border-grey);
        border-radius: 12px;
        overflow: hidden;
    }

    table.pr-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        font-size: 14.5px;
    }

    table.pr-table thead th {
        text-align: left;
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 600;
        color: #fff;
        background: var(--navy);
        padding: 16px 18px;
        white-space: nowrap;
    }

    table.pr-table thead th:first-child {
        border-radius: 0;
    }

    table.pr-table tbody td {
        padding: 18px 18px;
        border-bottom: 1px solid var(--border-grey);
        vertical-align: top;
        color: var(--text-dark);
    }

    table.pr-table tbody tr:last-child td {
        border-bottom: none;
    }

    table.pr-table tbody tr:hover {
        background: var(--bg-soft);
    }

    .pr-badge-no {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--maroon);
        color: #fff;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 12.5px;
        flex-shrink: 0;
    }

    .pr-id {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 14.5px;
        color: var(--navy);
    }

    .pr-title {
        font-weight: 600;
        color: var(--text-dark);
        line-height: 1.4;
        margin: 6px 0 4px;
    }

    .pr-sub {
        display: block;
        font-size: 12.5px;
        color: var(--text-grey);
        line-height: 1.5;
    }

    .pr-sub--tag {
        display: inline-block;
        margin-top: 4px;
        color: var(--maroon);
        background: var(--rust-bg);
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.02em;
        padding: 3px 10px;
        border-radius: 100px;
    }

    .pr-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 13px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.02em;
        white-space: nowrap;
        font-family: 'Poppins', sans-serif;
    }
    .pr-status::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }
    .is-granted   { background: var(--green-bg); color: var(--green); }
    .is-rejected  { background: var(--rust-bg);  color: var(--maroon-deep); }
    .is-pending   { background: var(--amber-bg); color: var(--amber); }
    .is-published { background: var(--blue-bg);  color: var(--blue); }
    .is-filed     { background: var(--bg-soft);  color: var(--text-grey); }

    /* ── Mobile record cards (hidden on desktop) ────────────────────────── */
    .pr-cards { display: none; }

    .pr-card {
        background: #fff;
        border: 1px solid var(--border-grey);
        border-radius: 12px;
        padding: 18px 18px 16px;
        margin-bottom: 14px;
    }

    .pr-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    .pr-card__id-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .pr-card__title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 16px;
        color: var(--text-dark);
        line-height: 1.4;
        margin: 8px 0 6px;
    }

    .pr-card__meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 16px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--border-grey);
    }

    .pr-card__field { min-width: 0; }

    .pr-card__label {
        display: block;
        font-size: 10.5px;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text-grey);
        margin-bottom: 3px;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
    }

    .pr-card__value {
        font-size: 13.5px;
        color: var(--text-dark);
        word-break: break-word;
    }

    /* ── Responsive breakpoints ──────────────────────────────────────────── */
    @media (max-width: 860px) {
        .pr-table-wrap { display: none; }
        .pr-cards { display: block; }

        .pr-section__title { font-size: 26px; }
        .pr-section { padding: 48px 0 56px; }
    }

    @media (max-width: 480px) {
        .pr-section__inner { padding: 0 16px; }
        .pr-section__title { font-size: 22px; }
        .pr-card__meta { grid-template-columns: 1fr; }
        .pr-count-bar { justify-content: center; }
    }
</style>

<div class="pr-section">
    <div class="pr-section__inner">

        <div class="pr-section__head">
            <p class="pr-section__eyebrow">Intellectual Property</p>
            <h2 class="pr-section__title" style="color: #1A1A1A;">Patent Registry</h2>
            <p class="pr-section__subtitle">Patents filed, published, and granted under the ANRF–PAIR initiative.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="pr-notice is-error"><?= htmlspecialchars($error) ?></div>
        <?php elseif (empty($patents)): ?>
            <div class="pr-notice">No patents have been registered yet. New filings will appear here once added.</div>
        <?php else: ?>

            <div class="pr-count-bar">
                <span class="pr-count-bar__pill"><?= count($patents) ?> record<?= count($patents) === 1 ? '' : 's' ?> on file</span>
            </div>

            <!-- Desktop table -->
            <div class="pr-table-wrap">
                <table class="pr-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Patent</th>
                            <th>Inventor(s)</th>
                            <th>Identifiers</th>
                            <th>Country</th>
                            <th>Filed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sno = 1; foreach ($patents as $patent): ?>
                            <tr>
                                <td><span class="pr-badge-no"><?= $sno++ ?></span></td>
                                <td>
                                    <div class="pr-id"><?= htmlspecialchars($patent['patent_id']) ?></div>
                                    <div class="pr-title"><?= htmlspecialchars($patent['patent_title'] ?: 'Untitled Patent') ?></div>
                                    <?php if (!empty($patent['technology_area'])): ?>
                                        <span class="pr-sub--tag"><?= htmlspecialchars($patent['technology_area']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($patent['inventor_name'] ?: '-') ?>
                                    <?php if (!empty($patent['co_inventors'])): ?>
                                        <span class="pr-sub">with <?= htmlspecialchars($patent['co_inventors']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="pr-sub">App: <?= htmlspecialchars($patent['application_no'] ?: '—') ?></span>
                                    <span class="pr-sub">Patent: <?= htmlspecialchars($patent['patent_no'] ?: '—') ?></span>
                                </td>
                                <td><?= htmlspecialchars($patent['country'] ?: '—') ?></td>
                                <td><?= !empty($patent['filing_date']) ? date('d M Y', strtotime($patent['filing_date'])) : '—' ?></td>
                                <td>
                                    <span class="pr-status <?= patentStatusClass($patent['status']) ?>"><?= htmlspecialchars($patent['status']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile record cards -->
            <div class="pr-cards">
                <?php $sno = 1; foreach ($patents as $patent): ?>
                    <div class="pr-card">
                        <div class="pr-card__top">
                            <div class="pr-card__id-row">
                                <span class="pr-badge-no"><?= $sno++ ?></span>
                                <span class="pr-id"><?= htmlspecialchars($patent['patent_id']) ?></span>
                            </div>
                            <span class="pr-status <?= patentStatusClass($patent['status']) ?>"><?= htmlspecialchars($patent['status']) ?></span>
                        </div>

                        <div class="pr-card__title"><?= htmlspecialchars($patent['patent_title'] ?: 'Untitled Patent') ?></div>
                        <?php if (!empty($patent['technology_area'])): ?>
                            <span class="pr-sub--tag"><?= htmlspecialchars($patent['technology_area']) ?></span>
                        <?php endif; ?>

                        <div class="pr-card__meta">
                            <div class="pr-card__field">
                                <span class="pr-card__label">Inventor</span>
                                <span class="pr-card__value"><?= htmlspecialchars($patent['inventor_name'] ?: '—') ?></span>
                            </div>
                            <?php if (!empty($patent['co_inventors'])): ?>
                            <div class="pr-card__field">
                                <span class="pr-card__label">Co-Inventor(s)</span>
                                <span class="pr-card__value"><?= htmlspecialchars($patent['co_inventors']) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="pr-card__field">
                                <span class="pr-card__label">Application No.</span>
                                <span class="pr-card__value"><?= htmlspecialchars($patent['application_no'] ?: '—') ?></span>
                            </div>
                            <div class="pr-card__field">
                                <span class="pr-card__label">Patent No.</span>
                                <span class="pr-card__value"><?= htmlspecialchars($patent['patent_no'] ?: '—') ?></span>
                            </div>
                            <div class="pr-card__field">
                                <span class="pr-card__label">Country</span>
                                <span class="pr-card__value"><?= htmlspecialchars($patent['country'] ?: '—') ?></span>
                            </div>
                            <div class="pr-card__field">
                                <span class="pr-card__label">Filed</span>
                                <span class="pr-card__value"><?= !empty($patent['filing_date']) ? date('d M Y', strtotime($patent['filing_date'])) : '—' ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</div>
<!-- end Patent Registry Section -->

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

</body>
</html>