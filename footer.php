<?php
/**
 * footer.php — ANRF–PAIR Project
 * Reusable footer include for all pages.
 *
 * Usage: <?php include 'footer.php'; ?>
 */
require_once __DIR__ . '/visitor_tracker.php';
$uniqueVisitorsCount = trackAndGetUniqueVisitors($pdo);
?>

<!-- ===== FOOTER ===================================================== -->
<footer id="page-footer">

<!-- Top bar: social + search -->
    <section id="footer-top">
        <div class="container">
            <div class="footer-inner">

                <div class="footer-social">
                    <figure>Follow us:</figure>
                    <div class="icons">
                        <a href="https://www.youtube.com/@ANRFPAIR-UOH" target="_blank"><i class="fa fa-youtube"></i></a>
                    </div><!-- /.icons -->
                </div><!-- /.footer-social -->

            </div><!-- /.footer-inner -->
        </div><!-- /.container -->
    </section><!-- /#footer-top -->

    <!-- Main footer content -->
    <section id="footer-content">
        <div class="container">
            <div class="row">

                <!-- Logo -->
                <div class="col-md-3 col-sm-12">
                    <aside class="logo">
                        <a href="index.php">
                            <img src="footer_logo2.svg" alt="ANRF–PAIR Logo">
                        </a>
                    </aside>
                </div><!-- /.col-md-3 -->

                <!-- Contact -->
                <div class="col-md-3 col-sm-4">
                    <aside>
                        <header><h4>Contact Us</h4></header>
                        <address>
                            <strong>ANRF–PAIR Project Office</strong><br>
                            IDC Building,<br> University of Hyderabad<br>
                            Gachibowli, Hyderabad <br>Pincode– 500046, India<br>
                            <abbr title="Landline">Landline:</abbr>
                            <a href="tel:04023132309">040-23132309</a><br>
                            <abbr title="Email">Email:</abbr>
                            <a href="mailto:pairdirecorate@uohyd.ac.in">pairdirecorate@uohyd.ac.in</a>
                        </address>
                    </aside>
                </div><!-- /.col-md-3 -->
<!-- Important Links (Synced with Header Nav) -->
<div class="col-md-3 col-sm-4">
    <aside>
        <header><h4>Quick Links</h4></header>
        <ul class="list-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="about-us.php">About the Project</a></li>
            <li><a href="participating-institutions.php">Participating Institutions</a></li>
            <li><a href="research_areas.php">Research Areas</a></li>
            <li><a href="infrastructure-facilities.php">Infrastructure &amp; Facilities</a></li>
            <li><a href="publications-reports.php">Publications &amp; Reports</a></li>
            <li><a href="downloads.php">Downloads (Forms/SOPs)</a></li>
            <li><a href="contact-us.php">Contact Us</a></li>
        </ul>
    </aside>
</div><!-- /.col-md-3 -->

                <!-- About blurb -->
                <div class="col-md-3 col-sm-4">
                    <aside>
                        <header><h4>About ANRF–PAIR</h4></header>
                        <p>The ANRF–PAIR (Partnerships for Accelerated Innovation and Research) program is a
                           national-level initiative supporting multi-institutional collaborations in health
                           and medical technology research.</p>

                        <div class="footer-about-readmore">
                            <a href="about-us.php" class="read-more">Read More</a>
                        </div>

                        <div class="footer-visitor-container" style="margin-top: 14px;">
                            <div class="footer-visitor-box" style="background: #012447; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 10px; color: #ffffff;">
                                <div style="font-size: 18px; color: #ffffff; display: flex; align-items: center;">
                                    <i class="fa fa-users" aria-hidden="true"></i>
                                </div>
                                <div style="text-align: left;">
                                    <div style="font-size: 15px; font-weight: 700; color: #ffffff; line-height: 1.1; letter-spacing: 0.5px;">
                                        <?php echo number_format($uniqueVisitorsCount); ?>
                                    </div>
                                    <div style="font-size: 9px; font-weight: 600; text-transform: uppercase; color: #ffffff; opacity: 0.9; letter-spacing: 0.8px; margin-top: 2px;">
                                        TOTAL VISITORS
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div><!-- /.col-md-3 -->

            </div><!-- /.row -->
        </div><!-- /.container -->

        <div class="background">
            <img src="assets/img/background-city.png" alt="">
        </div>
    </section><!-- /#footer-content -->

    <!-- Bottom bar: copyright + developer credit (Centered) -->
    <section id="footer-bottom" style="position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; width: 100vw; max-width: 100vw; background-color: #012447; padding: 16px 0; border-top: 1px solid rgba(255,255,255,0.08); box-sizing: border-box; clear: both; overflow: hidden; text-align: center;">
        <div class="container" style="max-width: 1170px; margin: 0 auto; padding: 0 15px; text-align: center;">
            <div class="footer-bottom-info" style="text-align: center; margin: 0 auto; color: rgba(255, 255, 255, 0.75); font-size: 12px; line-height: 1.6; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;">
                <div>
                    &copy; <?php echo date('Y'); ?> ANRF&ndash;PAIR Project, University of Hyderabad. <span class="nowrap">All rights reserved.</span>
                </div>
            </div>
        </div><!-- /.container -->
    </section><!-- /#footer-bottom -->

</footer>
<!-- ===== END FOOTER ================================================= -->

</div><!-- /.wrapper  (opened in header.php) -->

<!-- Scripts -->
<script src="assets/js/jquery-2.1.0.min.js"></script>
<script src="assets/js/jquery-migrate-1.2.1.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/selectize.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/jquery.validate.min.js"></script>
<script src="assets/js/jquery.placeholder.js"></script>
<script src="assets/js/jQuery.equalHeights.js"></script>
<script src="assets/js/icheck.min.js"></script>
<script src="assets/js/jquery.vanillabox-0.1.5.min.js"></script>
<script src="assets/js/jquery.tablesorter.min.js"></script>
<script src="assets/js/jquery.flexslider-min.js"></script>
<script src="assets/js/retina-1.1.0.min.js"></script>
<script src="assets/js/custom.js?v=<?= filemtime('assets/js/custom.js') ?>"></script>

</body>
</html>