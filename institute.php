<?php
require_once 'config.php';

$logo_map = [
    'Central University of Karnataka' => 'logos/cuk1.jpg',
    'Kannur University'               => 'logos/ku1.jpg',
    'Mahatma Gandhi University'       => 'logos/mg1.jpg',
    'Osmania University'              => 'logos/ou1.jpg',
    'Sri Venkateswara University'     => 'logos/gan1.jpg',
    'Yogi Vemana University'          => 'logos/yu.jpg',
];
$all_institutes  = array_keys($logo_map);

// 1. Sanitize and fall back to the first university if none is specified or valid
$institute_name = isset($_GET['name']) && in_array(trim($_GET['name']), $all_institutes) ? trim($_GET['name']) : $all_institutes[0];
$active_tab     = isset($_GET['tab'])  ? $_GET['tab']  : 'progress';
$institute_logo  = $logo_map[$institute_name] ?? 'logos/default.jpg';

// 2. Map the university name to its database table prefix
$prefix_map = [
    'Central University of Karnataka' => 'cuk_',
    'Kannur University'               => 'kannur_',
    'Mahatma Gandhi University'       => 'mgu_',
    'Osmania University'              => 'ou_',
    'Sri Venkateswara University'     => 'svu_',
    'Yogi Vemana University'          => 'yvu_',
];
$prefix = $prefix_map[$institute_name] ?? 'cuk_';

function fetchRows($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// 3. Inject the safe prefix into your queries dynamically
$publications = fetchRows($pdo, "SELECT * FROM {$prefix}publications     ORDER BY created_at DESC");
$patents      = fetchRows($pdo, "SELECT * FROM {$prefix}patent           ORDER BY created_at DESC");
$internships  = fetchRows($pdo, "SELECT * FROM {$prefix}internships      ORDER BY created_at DESC");
$progress     = fetchRows($pdo, "SELECT * FROM {$prefix}progress_reports ORDER BY created_at DESC");
$webinars     = fetchRows($pdo, "SELECT * FROM {$prefix}webinars         ORDER BY created_at DESC");
$conferences  = fetchRows($pdo, "SELECT * FROM {$prefix}conferences      ORDER BY created_at DESC");
$events_count = count($webinars) + count($conferences);

$tabs = [
    'progress'     => ['label' => 'Progress', 'count' => count($progress),      'icon' => '📋'],
    'publications' => ['label' => 'Publications', 'count' => count($publications),   'icon' => '📄'],
    'patents'      => ['label' => 'Patents',     'count' => count($patents),        'icon' => '🔬'],
    'internships'  => ['label' => 'Internships', 'count' => count($internships),   'icon' => '🎓'],
    'events'       => ['label' => 'Events',      'count' => $events_count,          'icon' => '📅'],
];

$tab_labels = [
    'progress'     => ['title' => 'Progress Reports',  'sub' => 'Status updates on active research tasks, covering objectives, methodology, and outcomes to date.'],
    'publications' => ['title' => 'Publications',      'sub' => 'Peer-reviewed journal articles, conference papers, and other scholarly outputs.'],
    'patents'      => ['title' => 'Patents',            'sub' => 'Filed, published, and granted patents arising from the ANRF–PAIR research programme.'],
    'internships'  => ['title' => 'Internships',        'sub' => 'Student training programmes conducted under the supervision of project investigators.'],
    'events'       => ['title' => 'Events',             'sub' => 'Webinars and conferences organised or attended by the research team.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($institute_name) ?> – ANRF-PAIR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/institute.css"                type="text/css">

</head>
<body class="page-institute">
<div class="wrapper">

<?php include 'header.php'; ?>

<div class="inst-wrap">

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Home</a>
        <span class="sep">/</span>
        <a href="index.php#partners">Inst</a>
        <span class="sep">/</span>
        <span class="current"><?= htmlspecialchars($institute_name) ?></span>
    </nav>

    <div class="inst-card" style="margin-top:12px;">
        <div class="inst-logo-wrap">
            <img src="<?= htmlspecialchars($institute_logo) ?>" alt="<?= htmlspecialchars($institute_name) ?> logo">
        </div>
        <div class="inst-meta">
            <h2><?= htmlspecialchars($institute_name) ?></h2>
            <p>Research Portfolio &amp; Database Records — ANRF–PAIR</p>
        </div>
        <div class="inst-switch">
            <label for="instSwitch">Switch Institution</label>
            <select id="instSwitch" onchange="switchInstitute(this.value)">
                <?php foreach ($all_institutes as $inst): ?>
                <option value="<?= htmlspecialchars($inst) ?>" <?= $inst === $institute_name ? 'selected' : '' ?>>
                    <?= htmlspecialchars($inst) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="tab-bar" role="tablist">
        <?php foreach ($tabs as $key => $t): ?>
        <button
            class="tab-btn <?= $active_tab === $key ? 'active' : '' ?>"
            role="tab"
            aria-selected="<?= $active_tab === $key ? 'true' : 'false' ?>"
            aria-controls="panel-<?= $key ?>"
            data-tab="<?= $key ?>"
            onclick="switchTab('<?= $key ?>')"
        ><?= htmlspecialchars($t['label']) ?><span class="tc"><?= $t['count'] ?></span></button>
        <?php endforeach; ?>
    </div>

    <!-- PROGRESS TAB -->
    <div class="tab-panel <?= $active_tab === 'progress' ? 'active' : '' ?>" id="panel-progress" role="tabpanel">
        <div class="table-card">
            <div class="table-wrap">
            <?php if ($progress): ?>
            <table>
                <thead><tr>
                    <th style="width:36px;">S.NO</th>
                    <th>Project Title</th>
                    <th>PI / Co-PI</th>
                    <th>Objective</th>
                    <th>Methodology</th>
                    <th>Progress</th>
                </tr></thead>
                <tbody>
                <?php foreach ($progress as $i => $r): ?>
                <tr>
                    <td><div class="row-num"><?= $i + 1 ?></div></td>
                    <td><div class="col-title"><?= htmlspecialchars($r['project_title']) ?></div></td>
                    <td>
                        <div class="pi-block">
                            <div><span class="lbl">PI</span><span class="name"><?= htmlspecialchars($r['pi_name']) ?></span></div>
                            <?php if ($r['co_pi_name']): ?>
                            <div><span class="lbl">Co‑PI</span><span class="name"><?= htmlspecialchars($r['co_pi_name']) ?></span></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="col-muted"><?= htmlspecialchars($r['approved_objects'] ?? '—') ?></td>
                    <td class="col-muted"><?= htmlspecialchars($r['methodology'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($r['summary_progress'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No progress reports found.</div>
            <?php endif; ?>
            </div>

            <!-- MOBILE CARD LAYOUT -->
            <div class="card-layout" style="display:none;">
            <?php if ($progress): ?>
                <?php foreach ($progress as $i => $r): ?>
                <div class="card-row <?= $i % 2 === 1 ? 'alt' : '' ?>">
                    <div class="card-header">
                        <div class="card-num"><?= $i + 1 ?></div>
                        <div class="card-title-main"><?= htmlspecialchars($r['project_title']) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Principal Investigator</span>
                        <div class="card-value"><?= htmlspecialchars($r['pi_name']) ?></div>
                        <?php if ($r['co_pi_name']): ?>
                        <div class="card-value" style="margin-top:4px;"><strong>Co-PI:</strong> <?= htmlspecialchars($r['co_pi_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Objective</span>
                        <div class="card-value muted"><?= htmlspecialchars($r['approved_objects'] ?? '—') ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Methodology</span>
                        <div class="card-value muted"><?= htmlspecialchars($r['methodology'] ?? '—') ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Progress</span>
                        <div class="card-value"><?= htmlspecialchars($r['summary_progress'] ?? '—') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">No progress reports found.</div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PUBLICATIONS TAB -->
    <div class="tab-panel <?= $active_tab === 'publications' ? 'active' : '' ?>" id="panel-publications" role="tabpanel">
        <div class="table-card">
            <div class="table-wrap">
            <?php if ($publications): ?>
            <table>
                <thead><tr>
                    <th style="width:36px;">#</th>
                    <th>Task</th>
                    <th>Publication Title</th>
                    <th>Author</th>
                    <th>Journal / Venue</th>
                    <th>Date</th>
                    <th>Impact Factor</th>
                    <th>DOI</th>
                </tr></thead>
                <tbody>
                <?php foreach ($publications as $i => $r): ?>
                <tr>
                    <td><div class="row-num"><?= $i + 1 ?></div></td>
                    <td><a class="task-link" href="#"><?= htmlspecialchars($r['task_no'] ?? '—') ?></a></td>
                    <td><div class="col-title"><?= htmlspecialchars($r['publication_title']) ?></div></td>
                    <td><?= htmlspecialchars($r['author_name']) ?></td>
                    <td class="col-muted"><?= htmlspecialchars($r['publication_journal']) ?></td>
                    <td class="col-muted" style="white-space:nowrap;"><?= $r['publication_date'] ? date('d M Y', strtotime($r['publication_date'])) : '—' ?></td>
                    <td>
                        <?= $r['impact_factor']
                            ? '<span class="if-pill">'.number_format($r['impact_factor'], 3).'</span>'
                            : '<span class="na-pill">N/A</span>' ?>
                    </td>
                    <td>
                        <?= $r['doi_number']
                            ? '<a class="doi-link" href="https://doi.org/'.htmlspecialchars($r['doi_number']).'" target="_blank">View ↗</a>'
                            : '<span class="col-muted">—</span>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No publications found.</div>
            <?php endif; ?>
            </div>

            <!-- MOBILE CARD LAYOUT -->
            <div class="card-layout" style="display:none;">
            <?php if ($publications): ?>
                <?php foreach ($publications as $i => $r): ?>
                <div class="card-row <?= $i % 2 === 1 ? 'alt' : '' ?>">
                    <div class="card-header">
                        <div class="card-num"><?= $i + 1 ?></div>
                        <div class="card-title-main"><?= htmlspecialchars($r['publication_title']) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Task</span>
                        <div class="card-inline"><a class="task-link" href="#"><?= htmlspecialchars($r['task_no'] ?? '—') ?></a></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Author</span>
                        <div class="card-value"><?= htmlspecialchars($r['author_name']) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Journal / Venue</span>
                        <div class="card-value muted"><?= htmlspecialchars($r['publication_journal']) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Date</span>
                        <div class="card-value muted"><?= $r['publication_date'] ? date('d M Y', strtotime($r['publication_date'])) : '—' ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Impact Factor</span>
                        <div class="card-inline">
                            <?= $r['impact_factor']
                                ? '<span class="if-pill">'.number_format($r['impact_factor'], 3).'</span>'
                                : '<span class="na-pill">N/A</span>' ?>
                        </div>
                    </div>
                    <?php if ($r['doi_number']): ?>
                    <div class="card-section">
                        <span class="card-label">DOI</span>
                        <div class="card-inline">
                            <a class="doi-link" href="https://doi.org/<?= htmlspecialchars($r['doi_number']) ?>" target="_blank">View DOI ↗</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">No publications found.</div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PATENTS TAB -->
    <div class="tab-panel <?= $active_tab === 'patents' ? 'active' : '' ?>" id="panel-patents" role="tabpanel">
        <div class="table-card">
            <div class="table-wrap">
            <?php if ($patents): ?>
            <table>
                <thead><tr>
                    <th style="width:36px;">#</th>
                    <th>Patent</th>
                    <th>Inventor(s)</th>
                    <th>Identifiers</th>
                    <th>Country</th>
                    <th>Filed</th>
                    <th>Status</th>
                </tr></thead>
                <tbody>
                <?php foreach ($patents as $i => $r):
                    $status_class = 'b-' . strtolower($r['status']);
                ?>
                <tr>
                    <td><div class="row-num"><?= $i + 1 ?></div></td>
                    <td>
                        <a class="pat-id" href="#"><?= htmlspecialchars($r['patent_id']) ?></a>
                        <div class="col-title"><?= htmlspecialchars($r['patent_title']) ?></div>
                        <?php if (!empty($r['technology_area'])): ?>
                        <span class="tech-tag"><?= htmlspecialchars($r['technology_area']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="inv-main"><?= htmlspecialchars($r['inventor_name']) ?></div>
                        <?php if ($r['co_inventors']): ?>
                        <div class="inv-co">with <?= htmlspecialchars($r['co_inventors']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="id-row">
                            <div class="id-label">App</div>
                            <div class="id-val"><?= htmlspecialchars($r['application_no'] ?? '—') ?></div>
                        </div>
                        <div class="id-row">
                            <div class="id-label">Patent</div>
                            <div class="id-val"><?= $r['patent_no'] ? htmlspecialchars($r['patent_no']) : '<span style="color:#b0bcd4;">—</span>' ?></div>
                        </div>
                    </td>
                    <td style="font-weight:600; color:var(--text-mid); white-space:nowrap;"><?= htmlspecialchars($r['country'] ?? '—') ?></td>
                    <td class="col-muted" style="white-space:nowrap;"><?= $r['filing_date'] ? date('d M Y', strtotime($r['filing_date'])) : '—' ?></td>
                    <td><span class="badge <?= $status_class ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No patents found.</div>
            <?php endif; ?>
            </div>

            <!-- MOBILE CARD LAYOUT -->
            <div class="card-layout" style="display:none;">
            <?php if ($patents): ?>
                <?php foreach ($patents as $i => $r): ?>
                <?php $status_class = 'b-' . strtolower($r['status']); ?>
                <div class="card-row <?= $i % 2 === 1 ? 'alt' : '' ?>">
                    <div class="card-header">
                        <div class="card-num"><?= $i + 1 ?></div>
                        <div>
                            <a class="pat-id" href="#"><?= htmlspecialchars($r['patent_id']) ?></a>
                            <div class="card-title-main" style="margin-top:2px;"><?= htmlspecialchars($r['patent_title']) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($r['technology_area'])): ?>
                    <div class="card-section">
                        <span class="tech-tag"><?= htmlspecialchars($r['technology_area']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="card-section">
                        <span class="card-label">Inventor</span>
                        <div class="card-value"><?= htmlspecialchars($r['inventor_name']) ?></div>
                        <?php if ($r['co_inventors']): ?>
                        <div class="card-value muted" style="margin-top:4px; font-size:12px;">Co-inventors: <?= htmlspecialchars($r['co_inventors']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Application No.</span>
                        <div class="card-value"><?= htmlspecialchars($r['application_no'] ?? '—') ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Patent No.</span>
                        <div class="card-value muted"><?= $r['patent_no'] ? htmlspecialchars($r['patent_no']) : '—' ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Country</span>
                        <div class="card-value"><?= htmlspecialchars($r['country'] ?? '—') ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Filed</span>
                        <div class="card-value muted"><?= $r['filing_date'] ? date('d M Y', strtotime($r['filing_date'])) : '—' ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Status</span>
                        <div class="card-inline"><span class="badge <?= $status_class ?>"><?= htmlspecialchars($r['status']) ?></span></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">No patents found.</div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- INTERNSHIPS TAB -->
    <div class="tab-panel <?= $active_tab === 'internships' ? 'active' : '' ?>" id="panel-internships" role="tabpanel">
        <div class="table-card">
            <div class="table-wrap">
            <?php if ($internships): ?>
            <table>
                <thead><tr>
                    <th style="width:36px;">#</th>
                    <th>Task</th>
                    <th>Programme Title</th>
                    <th>Project Investigator</th>
                    <th style="text-align:center;">Students Trained</th>
                    <th style="text-align:center;">Duration (Days)</th>
                    <th>Student Names</th>
                </tr></thead>
                <tbody>
                <?php foreach ($internships as $i => $r): ?>
                <tr>
                    <td><div class="row-num"><?= $i + 1 ?></div></td>
                    <td><a class="task-link" href="#"><?= htmlspecialchars($r['task_no'] ?? '—') ?></a></td>
                    <td><div class="col-title"><?= htmlspecialchars($r['title']) ?></div></td>
                    <td><?= htmlspecialchars($r['project_investigator']) ?></td>
                    <td style="text-align:center;"><span class="big-num"><?= (int)$r['no_students_trained'] ?></span></td>
                    <td style="text-align:center;" class="col-muted"><?= $r['no_days_trained'] ?? '—' ?></td>
                    <td class="col-muted" style="font-size:13px;"><?= htmlspecialchars($r['students_names'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No internships found.</div>
            <?php endif; ?>
            </div>

            <!-- MOBILE CARD LAYOUT -->
            <div class="card-layout" style="display:none;">
            <?php if ($internships): ?>
                <?php foreach ($internships as $i => $r): ?>
                <div class="card-row <?= $i % 2 === 1 ? 'alt' : '' ?>">
                    <div class="card-header">
                        <div class="card-num"><?= $i + 1 ?></div>
                        <div class="card-title-main"><?= htmlspecialchars($r['title']) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Task</span>
                        <div class="card-inline"><a class="task-link" href="#"><?= htmlspecialchars($r['task_no'] ?? '—') ?></a></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Project Investigator</span>
                        <div class="card-value"><?= htmlspecialchars($r['project_investigator']) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Students Trained</span>
                        <div style="text-align:center;"><span class="big-num"><?= (int)$r['no_students_trained'] ?></span></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Duration</span>
                        <div class="card-value muted"><?= $r['no_days_trained'] ?? '—' ?> days</div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Student Names</span>
                        <div class="card-value muted"><?= htmlspecialchars($r['students_names'] ?? '—') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">No internships found.</div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- EVENTS TAB -->
    <div class="tab-panel <?= $active_tab === 'events' ? 'active' : '' ?>" id="panel-events" role="tabpanel">
        <div class="table-card">
            <div class="table-wrap">
            <?php if ($webinars || $conferences): ?>
            <table>
                <thead><tr>
                    <th style="width:36px;">#</th>
                    <th>Task</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Date &amp; Time</th>
                    <th>Organisers</th>
                    <th>Institute</th>
                    <th>Investigator</th>
                </tr></thead>
                <tbody>
                <?php $row_i = 1; ?>
                <?php foreach ($webinars as $r): ?>
                <tr>
                    <td><div class="row-num"><?= $row_i++ ?></div></td>
                    <td><a class="task-link" href="#"><?= htmlspecialchars($r['taskno']) ?></a></td>
                    <td><span class="badge b-webinar">Webinar</span></td>
                    <td><div class="col-title"><?= htmlspecialchars($r['title']) ?></div></td>
                    <td class="col-muted" style="white-space:nowrap;"><?= date('d M Y, h:i A', strtotime($r['webinar_date'])) ?></td>
                    <td><?= htmlspecialchars($r['organisers']) ?></td>
                    <td class="col-muted"><?= htmlspecialchars($r['institute'] ?? '—') ?></td>
                    <td class="col-muted"><?= htmlspecialchars($r['investigator'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php foreach ($conferences as $r): ?>
                <tr>
                    <td><div class="row-num"><?= $row_i++ ?></div></td>
                    <td><a class="task-link" href="#"><?= htmlspecialchars($r['taskno'] ?? '—') ?></a></td>
                    <td><span class="badge b-conf">Conference</span></td>
                    <td><div class="col-title"><?= htmlspecialchars($r['title']) ?></div></td>
                    <td class="col-muted" style="white-space:nowrap;"><?= date('d M Y', strtotime($r['conf_date'])) ?></td>
                    <td><?= htmlspecialchars($r['organisers'] ?? '—') ?></td>
                    <td class="col-muted"><?= htmlspecialchars($r['institute'] ?? '—') ?></td>
                    <td class="col-muted"><?= htmlspecialchars($r['investigator'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No events found.</div>
            <?php endif; ?>
            </div>

            <!-- MOBILE CARD LAYOUT -->
            <div class="card-layout" style="display:none;">
            <?php if ($webinars || $conferences): ?>
                <?php $row_i = 1; ?>
                <?php foreach ($webinars as $r): ?>
                <div class="card-row <?= $row_i % 2 === 1 ? 'alt' : '' ?>">
                    <div class="card-header">
                        <div class="card-num"><?= $row_i++ ?></div>
                        <div class="card-title-main"><?= htmlspecialchars($r['title']) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Type</span>
                        <div class="card-inline"><span class="badge b-webinar">Webinar</span></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Task</span>
                        <div class="card-inline"><a class="task-link" href="#"><?= htmlspecialchars($r['taskno']) ?></a></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Date &amp; Time</span>
                        <div class="card-value muted"><?= date('d M Y, h:i A', strtotime($r['webinar_date'])) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Organisers</span>
                        <div class="card-value"><?= htmlspecialchars($r['organisers']) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Institute</span>
                        <div class="card-value muted"><?= htmlspecialchars($r['institute'] ?? '—') ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Investigator</span>
                        <div class="card-value muted"><?= htmlspecialchars($r['investigator'] ?? '—') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php foreach ($conferences as $r): ?>
                <div class="card-row <?= $row_i % 2 === 1 ? 'alt' : '' ?>">
                    <div class="card-header">
                        <div class="card-num"><?= $row_i++ ?></div>
                        <div class="card-title-main"><?= htmlspecialchars($r['title']) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Type</span>
                        <div class="card-inline"><span class="badge b-conf">Conference</span></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Task</span>
                        <div class="card-inline"><a class="task-link" href="#"><?= htmlspecialchars($r['taskno'] ?? '—') ?></a></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Date</span>
                        <div class="card-value muted"><?= date('d M Y', strtotime($r['conf_date'])) ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Organisers</span>
                        <div class="card-value"><?= htmlspecialchars($r['organisers'] ?? '—') ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Institute</span>
                        <div class="card-value muted"><?= htmlspecialchars($r['institute'] ?? '—') ?></div>
                    </div>
                    <div class="card-section">
                        <span class="card-label">Investigator</span>
                        <div class="card-value muted"><?= htmlspecialchars($r['investigator'] ?? '—') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">No events found.</div>
            <?php endif; ?>
            </div>
        </div>
    </div>

</div><?php include 'footer.php'; ?>
</div>

<script>
function switchTab(key) {
    document.querySelectorAll('.tab-btn').forEach(b => {
        const on = b.dataset.tab === key;
        b.classList.toggle('active', on);
        b.setAttribute('aria-selected', String(on));
    });
    document.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.toggle('active', p.id === 'panel-' + key);
    });
    
    // Toggle table/card layout based on screen size
    const isMobile = window.innerWidth <= 600;
    document.querySelectorAll('.tab-panel.active .table-wrap').forEach(w => {
        w.style.display = isMobile ? 'none' : 'block';
    });
    document.querySelectorAll('.tab-panel.active .card-layout').forEach(c => {
        c.style.display = isMobile ? 'block' : 'none';
    });
    
    const url = new URL(window.location);
    url.searchParams.set('tab', key);
    history.replaceState(null, '', url);
}

function switchInstitute(name) {
    const url = new URL(window.location);
    url.searchParams.set('name', name);
    url.searchParams.set('tab', new URLSearchParams(window.location.search).get('tab') || 'progress');
    window.location.href = url.toString();
}

// Initial layout setup
function initLayout() {
    const isMobile = window.innerWidth <= 600;
    document.querySelectorAll('.table-wrap').forEach(w => {
        w.style.display = isMobile ? 'none' : 'block';
    });
    document.querySelectorAll('.card-layout').forEach(c => {
        c.style.display = isMobile ? 'block' : 'none';
    });
}

// Handle window resize
window.addEventListener('resize', () => {
    initLayout();
});

// Keyboard support
document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') card.click();
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initLayout();
    switchTab(new URLSearchParams(window.location.search).get('tab') || 'progress');
});
</script>
</body>
</html>