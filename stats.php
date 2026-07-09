<?php
/**
 * UoHyd Stats Section
 * -------------------
 * Pulls counts from the uoh_* tables only (University of Hyderabad data).
 * Include this file from index.php where you want the stats to appear.
 *
 * Uses the $pdo connection created in config.php.
 */

require_once 'config.php';

$uohyd_stats = [
    'publications'      => 0,
    'patents'           => 0,
    'conferences'       => 0,
    'webinars'          => 0,
    'internships'       => 0,
    'progress_reports'  => 0,
];

if (isset($pdo) && $pdo instanceof PDO) {

    $count_queries = [
        'publications'     => "SELECT COUNT(*) AS c FROM uoh_publications",
        'patents'          => "SELECT COUNT(*) AS c FROM uoh_patent",
        'conferences'      => "SELECT COUNT(*) AS c FROM uoh_conferences",
        'webinars'         => "SELECT COUNT(*) AS c FROM uoh_webinars",
        'internships'      => "SELECT COUNT(*) AS c FROM uoh_internships",
        'progress_reports' => "SELECT COUNT(*) AS c FROM uoh_progress_reports",
    ];

    foreach ($count_queries as $key => $sql) {
        try {
            $stmt = $pdo->query($sql);
            $row  = $stmt->fetch();
            if ($row && isset($row['c'])) {
                $uohyd_stats[$key] = (int) $row['c'];
            }
        } catch (PDOException $e) {
            // Table missing, wrong schema, etc. Fails safely, count stays 0.
        }
    }
}

// Card definitions: label, stat key, color, icon
$uohyd_cards = [
    [
        'label' => 'Publications',
        'key'   => 'publications',
        'color' => '#C2185B',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H18a1 1 0 0 1 1 1v17a1 1 0 0 1-1 1H6.5A2.5 2.5 0 0 1 4 18.5v-14Z" stroke="white" stroke-width="1.6" stroke-linejoin="round"/><path d="M8 7h8M8 11h8M8 15h5" stroke="white" stroke-width="1.6" stroke-linecap="round"/></svg>',
    ],
    [
        'label' => 'Patents',
        'key'   => 'patents',
        'color' => '#00897B',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2 3 6v6c0 5 4 8.5 9 10 5-1.5 9-5 9-10V6l-9-4Z" stroke="white" stroke-width="1.6" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ],
    [
        'label' => 'Conferences',
        'key'   => 'conferences',
        'color' => '#1E88C7',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="7" r="3" stroke="white" stroke-width="1.6"/><path d="M4 21c0-3.9 3.6-7 8-7s8 3.1 8 7" stroke="white" stroke-width="1.6" stroke-linecap="round"/><circle cx="5" cy="9" r="2" stroke="white" stroke-width="1.4"/><circle cx="19" cy="9" r="2" stroke="white" stroke-width="1.4"/></svg>',
    ],
    [
        'label' => 'Webinars',
        'key'   => 'webinars',
        'color' => '#8BC34A',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="12" rx="1.5" stroke="white" stroke-width="1.6"/><path d="M8 21h8M12 17v4" stroke="white" stroke-width="1.6" stroke-linecap="round"/><path d="m10 9 4 2-4 2V9Z" fill="white"/></svg>',
    ],
    [
        'label' => 'Internships',
        'key'   => 'internships',
        'color' => '#F0932B',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="8" width="18" height="12" rx="1.5" stroke="white" stroke-width="1.6"/><path d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke="white" stroke-width="1.6" stroke-linecap="round"/><path d="M3 13h18" stroke="white" stroke-width="1.6"/></svg>',
    ],
    [
        'label' => 'Progress Reports',
        'key'   => 'progress_reports',
        'color' => '#7E57C2',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 19V5a1 1 0 0 1 1-1h9l6 6v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1Z" stroke="white" stroke-width="1.6" stroke-linejoin="round"/><path d="M14 4v5a1 1 0 0 0 1 1h5" stroke="white" stroke-width="1.6" stroke-linejoin="round"/><path d="M8 13h8M8 16h5" stroke="white" stroke-width="1.6" stroke-linecap="round"/></svg>',
    ],
];
?>

<section id="uohyd-stats" class="uohyd-stats-section">

    <div class="container">
            <h2 class="gallery-main-heading">KEY PERFORMANCE INDICATORS</h2>
        <div class="uohyd-stats-grid">
            <?php foreach ($uohyd_cards as $card): ?>
                <div class="uohyd-stat-card" style="background:<?php echo $card['color']; ?>;">
                    <div class="uohyd-stat-icon"><?php echo $card['icon']; ?></div>
                    <div class="uohyd-stat-body">
                        <div class="uohyd-stat-label"><?php echo htmlspecialchars($card['label']); ?></div>
                        <div class="uohyd-stat-number"><?php echo $uohyd_stats[$card['key']]; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.uohyd-stats-section {
    padding: 32px 20px;
}
.uohyd-stats-section .container {
    max-width: 1200px;
    margin: 0 auto;
}
.uohyd-stats-eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 1.2px;
    color: #9aa1a8;
    margin-bottom: 14px;
    text-transform: uppercase;
}
/* Flexbox instead of grid: incomplete last row (cards 4 & 5) centers
   automatically instead of sitting flush left like a grid would. */
.uohyd-stats-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 14px;
}
.uohyd-stat-card {
    display: flex;
    align-items: stretch;
    border-radius: 4px;
    overflow: hidden;
    min-height: 92px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    flex: 1 1 260px;
    max-width: 340px;
}
.uohyd-stat-icon {
    flex: 0 0 76px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.12);
}
.uohyd-stat-icon svg {
    width: 32px;
    height: 32px;
}
.uohyd-stat-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 10px 18px;
    color: #fff;
}
.uohyd-stat-label {
    font-size: 1.5rem;
    font-weight: 600;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    opacity: 0.92;
    margin-bottom: 4px;
}
.uohyd-stat-number {
    font-size: 3rem;
    font-weight: 700;
    line-height: 1;
}

@media (max-width: 640px) {
    .uohyd-stat-card {
        flex: 1 1 100%;
        max-width: 100%;
    }
}
</style>