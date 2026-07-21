<?php
require_once 'events_helper.php';
$eventsList = getAllPublishedEvents();
include 'header.php';
?>

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li class="active">Events & Activities</li>
    </ol>
</div>
<!-- end Breadcrumb -->

<!-- Page Content -->
<div id="page-content">
    <div class="container">
        <div class="row">
            <!--MAIN Content-->
            <div class="col-md-12" style="margin-top: 30px;">
                <div id="page-main">
                    <section class="events" id="events">
                        <div class="section-content">
                            <?php if (!empty($eventsList)): ?>
                                <?php foreach ($eventsList as $event): ?>
                                    <?php
                                        $eventTs = strtotime($event['event_date']);
                                        $monthStr = date('M', $eventTs);
                                        $dayStr = date('d', $eventTs);

                                        $startDateFormatted = date("d F, Y", $eventTs);
                                        $endDateFormatted = !empty($event['end_date']) && $event['end_date'] !== $event['event_date']
                                            ? ' – ' . date("d F, Y", strtotime($event['end_date']))
                                            : '';
                                        $startTime = date("g:i A", strtotime($event['start_time']));
                                        $endTime = date("g:i A", strtotime($event['end_time']));
                                        $timeStr = "{$startTime} - {$endTime}";
                                        $detailMeta = "{$startDateFormatted}{$endDateFormatted} | {$timeStr} | {$event['venue']} | " . ($event['organizer'] ?: 'ANRF-PAIR Project');
                                    ?>
                                    <article class="event">
                                        <figure class="date">
                                            <div class="month"><?= $monthStr ?></div>
                                            <div class="day"><?= $dayStr ?></div>
                                        </figure>
                                        <aside>
                                            <header style="margin-bottom: 4px;">
                                                <a href="event-detail.php?id=<?= $event['id'] ?>"><b><?= htmlspecialchars($event['title']) ?></b></a>
                                            </header>
                                            <div class="event-details-line" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 13px; color: #767676; margin-top: 6px;" title="<?= htmlspecialchars($detailMeta) ?>">
                                                <?= htmlspecialchars($detailMeta) ?>
                                            </div>
                                        </aside>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted" style="padding: 20px 0;">No published events found at this time.</p>
                            <?php endif; ?>
                        </div><!-- /.section-content -->
                    </section><!-- /.events -->
                </div><!-- /#page-main -->
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->
    </div><!-- /.container -->
</div>
<!-- end Page Content -->

<?php include 'footer.php';?>

</div>

</body>
</html>