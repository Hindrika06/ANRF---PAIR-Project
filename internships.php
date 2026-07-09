<?php 
require_once 'config.php'; 

$internships = [];
$hasData = false;
$totalStudents = 0;
$totalDays = 0;

try {
    $stmt = $pdo->query("SELECT * FROM uoh_internships ORDER BY created_at DESC, id DESC");
    $internships = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($internships)) {
        $hasData = true;
        foreach ($internships as $r) {
            $totalStudents += (int)($r['no_students_trained'] ?? 0);
            $totalDays     += (int)($r['no_days_trained'] ?? 0);
        }
    }
} catch (PDOException $e) {
    echo "<div class='container' style='margin-top:20px;'><div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div></div>";
}
?>

<body class="page-homepage-courses">
<div class="wrapper">
<?php include 'header.php';?>

<div class="container">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li class="active">Internships & Training</li>
    </ol>
</div>

<div id="page-content">
    <div class="tr-page">
        <div class="container">
            <div class="tr-header">
                <div class="tr-title" role="heading" aria-level="2">Internships Records</div>
                <p class="tr-subtitle">A record of internships and training programs conducted under the initiative, logged by cohort.</p>
            </div>

            <?php if ($hasData): ?>
            <div class="tr-stat-strip">
                <div class="tr-stat">
                    <span class="tr-stat-num"><?= count($internships) ?></span>
                    <span class="tr-stat-label">Programs Logged</span>
                </div>
                <div class="tr-stat-divider"></div>
                <div class="tr-stat">
                    <span class="tr-stat-num"><?= $totalStudents ?></span>
                    <span class="tr-stat-label">Students Trained</span>
                </div>
                <div class="tr-stat-divider"></div>
                <div class="tr-stat">
                    <span class="tr-stat-num"><?= $totalDays ?></span>
                    <span class="tr-stat-label">Cumulative Training Days</span>
                </div>
            </div>
            <?php endif; ?>

            <section class="tr-grid">
                <?php if ($hasData): ?>
                    <?php foreach ($internships as $row):
                        $studentsList = !empty($row['students_names']) ? array_map('trim', explode(',', $row['students_names'])) : [];
                    ?>
                        <article class="tr-card">
                            <div class="tr-card-body">
                                <h3 class="tr-card-title"><?= htmlspecialchars($row['title'] ?? 'Untitled Program') ?></h3>
                                <?php if (!empty($row['project_investigator'])): ?>
                                    <p class="tr-card-meta"><?= htmlspecialchars($row['project_investigator']) ?></p>
                                <?php endif; ?>
                                <p class="tr-card-content"><?= nl2br(htmlspecialchars($row['content'] ?? '')) ?></p>

                                <?php if (!empty($row['no_days_trained']) || !empty($row['no_students_trained'])): ?>
                                <div class="tr-card-stats">
                                    <?php if (!empty($row['no_days_trained'])): ?>
                                    <div class="tr-stat-block">
                                        <span class="tr-stat-block-num"><?= (int)$row['no_days_trained'] ?></span>
                                        <span class="tr-stat-block-label">Training Days</span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($row['no_days_trained']) && !empty($row['no_students_trained'])): ?>
                                    <div class="tr-stat-block-divider"></div>
                                    <?php endif; ?>
                                    <?php if (!empty($row['no_students_trained'])): ?>
                                    <div class="tr-stat-block">
                                        <span class="tr-stat-block-num"><?= (int)$row['no_students_trained'] ?></span>
                                        <span class="tr-stat-block-label">Students Trained</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($studentsList)): ?>
                                    <div class="tr-roster">
                                        <span class="tr-roster-label">Students Trained</span>
                                        <div class="tr-roster-tags">
                                            <?php foreach ($studentsList as $student): ?>
                                                <span class="tr-student-tag"><?= htmlspecialchars($student) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="tr-empty"><p>No training programs on record yet.</p></div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<?php include 'footer.php';?>
</div>

<style>
    :root {
        --tr-primary: #374151;
        --tr-crimson: #B33A3A;
        --tr-paper: #FBFAF7;
        --tr-ink: #2A2D34;
        --tr-slate: #6B7280;
        --tr-hair: #E4E1D8;
    }

    .tr-page { padding: 10px 0 60px; }
    .tr-header { max-width: 640px; margin: 10px auto 30px; text-align: center; }
    .tr-title { font-weight: 700; font-size: 34px; margin: 0 0 12px; color: var(--tr-primary); }
    .tr-subtitle { font-size: 15px; color: var(--tr-slate); line-height: 1.6; }

    /* Border top removed from .tr-stat-strip */
    .tr-stat-strip { display: flex; align-items: center; justify-content: center; gap: 36px; padding: 22px 0; margin: 0 0 44px; border-top: none; border-bottom: 1px solid var(--tr-hair); }
    .tr-stat { text-align: center; }
    .tr-stat-num { display: block; font-weight: 700; font-size: 28px; color: var(--tr-primary); }
    .tr-stat-label { display: block; font-size: 10.5px; text-transform: uppercase; color: var(--tr-slate); margin-top: 6px; font-weight: 600; }
    .tr-stat-divider { width: 1px; height: 32px; background: var(--tr-hair); }

    .tr-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 22px; }
    .tr-card { display: flex; background: var(--tr-paper); border: 1px solid var(--tr-hair); border-top: 3px solid var(--tr-primary); }
    .tr-card-body { padding: 26px; flex-grow: 1; }

    .tr-card-stats { display: flex; align-items: center; gap: 18px; margin: 0 0 16px; padding-bottom: 0; border-bottom: none; }
    .tr-stat-block { display: flex; flex-direction: column; }
    .tr-stat-block-num { font-weight: 700; font-size: 22px; color: var(--tr-primary); }
    .tr-stat-block-label { font-size: 10.5px; text-transform: uppercase; color: var(--tr-crimson); font-weight: 600; margin-top: 5px; }
    .tr-stat-block-divider { width: 1px; height: 30px; background: var(--tr-hair); }

    .tr-card-title { font-weight: 700; font-size: 18.5px; color: var(--tr-primary); margin: 0 0 8px 0; }
    .tr-card-meta { font-size: 13px; color: var(--tr-slate); margin: 0 0 14px; }
    .tr-card-content { font-size: 14px; color: var(--tr-ink); line-height: 1.65; margin: 0 0 16px; }

    .tr-roster { padding-top: 14px; border-top: 1px dashed var(--tr-hair); }
    .tr-roster-label { display: block; text-transform: uppercase; color: var(--tr-crimson); font-weight: 600; font-size: 11px; margin-bottom: 8px; }
    .tr-roster-tags { display: flex; flex-wrap: wrap; gap: 7px; }
    .tr-student-tag { font-size: 12.5px; color: var(--tr-primary); background: #fff; border: 1px solid var(--tr-hair); border-radius: 14px; padding: 4px 12px; }

    @media (max-width: 860px) {
        .tr-grid { grid-template-columns: 1fr; }
    }
</style>