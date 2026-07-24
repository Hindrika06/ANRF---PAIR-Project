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
