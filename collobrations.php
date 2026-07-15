



<!-- Header -->
<?php
$bodyClass = 'page-sub-page page-about-us'; include 'header.php';?>
<!-- end Header -->

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb">
        <li><a href="index.html">Home</a></li>
        <li class="active">About the Project</li>
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
                $collabs = [];
                try {
                    $stmt = $pdo->query("SELECT * FROM `collaborations` WHERE status = 'Active' ORDER BY display_order ASC, id DESC");
                    $collabs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Fallback
                }
                ?>
                <div class="col-md-12 col-sm-12">
                    <div class="course-info">
                        <!-- Project Overview -->
                        <div class="overview-content" style="margin-top: 20px;">       
                            <!-- Expected Outcomes -->
                            <h2 style="margin-top: 30px; color:#002b5c; font-weight: 700; ">The ANRF–PAIR project actively collaborates with:</h2>
                            
                            <?php if (empty($collabs)): ?>
                            <ul class="font-color-grey-medium" style="font-size: 15px;">
                                <li>Academic institutions</li>
                                <li>Research organizations</li>
                                <li>Industry partners</li>
                            </ul>
                            <?php else: ?>
                                <style>
                                    .collab-grid {
                                        display: grid;
                                        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                                        gap: 20px;
                                        margin-top: 25px;
                                    }
                                    .collab-card {
                                        background: #ffffff;
                                        border: 1px solid #eee;
                                        border-radius: 8px;
                                        padding: 20px;
                                        display: flex;
                                        align-items: center;
                                        gap: 15px;
                                        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
                                        transition: transform 0.2s ease, border-color 0.2s ease;
                                    }
                                    .collab-card:hover {
                                        transform: translateY(-2px);
                                        border-color: #bc2121;
                                        box-shadow: 0 6px 15px rgba(0,0,0,0.05);
                                    }
                                    .collab-logo {
                                        width: 60px;
                                        height: 60px;
                                        object-fit: contain;
                                        border: 1px solid #f1f5f9;
                                        border-radius: 6px;
                                        background: #fff;
                                        padding: 3px;
                                    }
                                    .collab-details {
                                        flex: 1;
                                        min-width: 0;
                                    }
                                    .collab-title {
                                        font-size: 16px;
                                        font-weight: 700;
                                        color: #1e293b;
                                        margin: 0 0 4px 0;
                                    }
                                    .collab-desc {
                                        font-size: 12.5px;
                                        color: #64748b;
                                        margin: 0;
                                        line-height: 1.4;
                                    }
                                    .collab-badge {
                                        font-size: 10px;
                                        font-weight: 700;
                                        text-transform: uppercase;
                                        background: #f1f5f9;
                                        color: #475569;
                                        padding: 2px 6px;
                                        border-radius: 4px;
                                        display: inline-block;
                                        margin-bottom: 6px;
                                    }
                                </style>
                                <div class="collab-grid">
                                    <?php foreach ($collabs as $c): ?>
                                    <div class="collab-card">
                                        <img src="<?= htmlspecialchars($c['logo_path']) ?>" alt="<?= htmlspecialchars($c['partner_name']) ?>" class="collab-logo">
                                        <div class="collab-details">
                                            <span class="collab-badge"><?= htmlspecialchars($c['collab_type']) ?></span>
                                            <h4 class="collab-title"><?= htmlspecialchars($c['partner_name']) ?></h4>
                                            <?php if ($c['profile_description']): ?>
                                                <p class="collab-desc"><?= htmlspecialchars($c['profile_description']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($c['website_url']): ?>
                                                <a href="<?= htmlspecialchars($c['website_url']) ?>" target="_blank" rel="noopener noreferrer" style="font-size: 12px; color: #bc2121; font-weight:600; text-decoration:none; display:inline-block; margin-top:5px;">
                                                    Visit Website <i class="fa fa-external-link" style="font-size:10px;"></i>
                                                </a>
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
<script type="text/javascript" src="assets/js/retina-1.1.0.min.js"></script>

<script type="text/javascript" src="assets/js/custom.js"></script>

</body>
</html>