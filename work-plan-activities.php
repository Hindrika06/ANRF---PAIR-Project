<?php
require_once 'events_helper.php';
$featuredWorkshop = getFeaturedWorkshop();
$bodyClass = 'page-homepage-courses';
include 'header.php';
?>
<!-- end Header -->

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li class="active">Work Plan & Activities</li>
    </ol>
</div>
<!-- end Breadcrumb -->

<!-- Page Content -->
<div id="page-content">

   <section id="course-detail" style="margin-top: 10px;">
    <div class="block" style="background-color: #fff;">
        <div class="container">
            <div class="row">

                <!-- Content -->
                <div class="col-md-10 col-sm-10">
                    <div class="course-info">

                        <!-- Key Activities -->
                        <div class="overview-content" style="margin-top: 20px;">       

                            <h2 style="margin-top: 30px; color:#000; font-weight: 700;">
                                Key Activities
                            </h2>

                            <ul class="font-color-grey-medium" style="font-size: 15px; line-height: 28px;">
                                <li>Joint research projects</li>
                                <li>Workshops and training programs</li>
                                <li>Webinars and expert lectures</li>
                                <li>Student internships and exchange programs</li>
                                <li>Data analysis and collaborative publications</li>
                            </ul>

                            <?php if ($featuredWorkshop): ?>
                                <?php
                                    $convenerName = !empty($featuredWorkshop['convener']) ? $featuredWorkshop['convener'] : ($featuredWorkshop['coordinator'] ?: 'Convener');
                                    $startDateFmt = date("j", strtotime($featuredWorkshop['event_date']));
                                    $endDateFmt = !empty($featuredWorkshop['end_date']) && $featuredWorkshop['end_date'] !== $featuredWorkshop['event_date']
                                        ? '–' . date("j F, Y", strtotime($featuredWorkshop['end_date']))
                                        : date(" F, Y", strtotime($featuredWorkshop['event_date']));
                                    $datesStr = "{$startDateFmt}{$endDateFmt}";
                                ?>
                                <div style="margin-top: 20px; padding: 20px; background: #f8f8f8; border-left: 4px solid #bc2121;">

                                    <h3 style="margin-top: 0; color:#000;">
                                        <?= htmlspecialchars($convenerName) ?>
                                    </h3>

                                    <p class="font-color-grey-medium" style="font-size: 15px; line-height: 28px;">
                                        ANRF–PAIR Initiative <br>
                                        Department of Genetics & Genomics <br>
                                        Yogi Vemana University (YVU), Kadapa
                                    </p>

                                    <h4 style="margin-top: 25px; color:#000;">
                                        Workshop Organized
                                    </h4>

                                    <p class="font-color-grey-medium" style="font-size: 15px; line-height: 28px;">
                                        Organizing <strong><a href="event-detail.php?id=<?= $featuredWorkshop['id'] ?>" style="color: inherit; text-decoration: underline;"><?= htmlspecialchars($featuredWorkshop['title']) ?></a></strong>, 
                                        <?= htmlspecialchars($featuredWorkshop['description']) ?> 
                                        <?php if (!empty($featuredWorkshop['resource_person'])): ?>
                                            (Resource Person: <?= htmlspecialchars($featuredWorkshop['resource_person']) ?>)
                                        <?php endif; ?>
                                        at <?= htmlspecialchars($featuredWorkshop['venue']) ?> 
                                        on <?= htmlspecialchars($datesStr) ?>.
                                    </p>

                                </div>
                            <?php endif; ?>

                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="background background-color-grey-background"></div>
    </div>
</section>

</div>
<!-- end Page Content -->

<!-- Footer -->
<?php include 'footer.php';?>
<!-- end Footer -->

</div>
<!-- end Wrapper -->

</body>
</html>