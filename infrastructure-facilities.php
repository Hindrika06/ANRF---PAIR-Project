



<!-- Header -->
<?php
$bodyClass = 'page-homepage-courses'; include 'header.php';?>
<!-- end Header -->



<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb">
        <li><a href="index.html">Home</a></li>
        <li class="active">About ANRF–PAIR</li>
    </ol>
</div>
<!-- end Breadcrumb -->


<!-- Page Content -->
<div id="page-content">

   <section id="course-detail" style="margin-top: 10px;">
    <div class="block" style="background-color: #fff;">
        <div class="container" >
            <div class="row" >

                <!-- Right Side: Title and Content -->
                <?php
                require_once 'config.php';
                $facilities = [];
                try {
                    $stmt = $pdo->query("SELECT * FROM `infrastructure_facilities` WHERE status = 'Active' ORDER BY display_order ASC, id DESC");
                    $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Fallback
                }
                ?>
                <div class="col-md-12 col-sm-12">
                    <div class="course-info">
                        <!-- Project Overview -->
                        <div class="overview-content" style="margin-top: 20px;">       
                            <!-- Expected Outcomes -->
                            <h2 style="margin-top: 30px; color:#002b5c; font-weight: 700; ">The project is supported by advanced facilities including:</h2>
                            
                            <?php if (empty($facilities)): ?>
                            <ul class="font-color-grey-medium" style="font-size: 15px;">
                                <li>A Laboratory and analytical instruments</li>
                            </ul>
                            <?php else: ?>
                                <style>
                                    .infra-grid {
                                        display: grid;
                                        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                                        gap: 24px;
                                        margin-top: 25px;
                                    }
                                    .infra-card {
                                        background: #ffffff;
                                        border: 1px solid #eef2f6;
                                        border-radius: 12px;
                                        overflow: hidden;
                                        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
                                        transition: transform 0.25s ease, border-color 0.25s ease;
                                    }
                                    .infra-card:hover {
                                        transform: translateY(-4px);
                                        border-color: #bc2121;
                                        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
                                    }
                                    .infra-img-box {
                                        width: 100%;
                                        height: 180px;
                                        overflow: hidden;
                                        background: #f1f5f9;
                                    }
                                    .infra-img {
                                        width: 100%;
                                        height: 100%;
                                        object-fit: cover;
                                        transition: transform 0.5s ease;
                                    }
                                    .infra-card:hover .infra-img {
                                        transform: scale(1.05);
                                    }
                                    .infra-body {
                                        padding: 20px;
                                    }
                                    .infra-name {
                                        font-size: 17px;
                                        font-weight: 700;
                                        color: #002b5c;
                                        margin: 0 0 10px 0;
                                    }
                                    .infra-desc {
                                        font-size: 13.5px;
                                        color: #475569;
                                        line-height: 1.5;
                                        margin: 0 0 15px 0;
                                    }
                                    .infra-equipment {
                                        background: #f8fafc;
                                        border-left: 3px solid #bc2121;
                                        padding: 10px 12px;
                                        border-radius: 0 6px 6px 0;
                                        font-size: 12.5px;
                                        color: #1e293b;
                                    }
                                    .infra-equipment strong {
                                        display: block;
                                        margin-bottom: 4px;
                                        color: #bc2121;
                                    }
                                    .infra-tag {
                                        font-size: 10px;
                                        font-weight: 700;
                                        background: #e0f2fe;
                                        color: #0369a1;
                                        padding: 2px 8px;
                                        border-radius: 20px;
                                        display: inline-block;
                                        margin-bottom: 8px;
                                        text-transform: uppercase;
                                    }
                                </style>
                                <div class="infra-grid">
                                    <?php foreach ($facilities as $f): ?>
                                    <div class="infra-card">
                                        <?php if ($f['image_path']): ?>
                                        <div class="infra-img-box">
                                            <img src="<?= htmlspecialchars($f['image_path']) ?>" alt="<?= htmlspecialchars($f['name']) ?>" class="infra-img">
                                        </div>
                                        <?php endif; ?>
                                        <div class="infra-body">
                                            <?php if ($f['institute_prefix'] !== 'all'): ?>
                                                <span class="infra-tag"><?= htmlspecialchars(strtoupper($f['institute_prefix'])) ?> Facility</span>
                                            <?php endif; ?>
                                            <h4 class="infra-name"><?= htmlspecialchars($f['name']) ?></h4>
                                            <p class="infra-desc"><?= htmlspecialchars($f['description']) ?></p>
                                            <?php if (!empty($f['equipment_details'])): ?>
                                                <div class="infra-equipment">
                                                    <strong>Featured Equipment:</strong>
                                                    <?= htmlspecialchars($f['equipment_details']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
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