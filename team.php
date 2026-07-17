<?php
$bodyClass = 'page-sub-page page-members';
require_once 'config.php';
include 'header.php';

// Fetch all active team members sorted by display order
$members = [];
try {
    $stmt = $pdo->query("SELECT * FROM `team` WHERE status = 'Active' ORDER BY display_order ASC, full_name ASC");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // silently skip
}

// Categorise members dynamically
$leadership = [];
$investigators = [];
$staff = [];
$students = [];
$others = [];

foreach ($members as $m) {
    $des = strtolower($m['designation'] ?? '');
    
    if (strpos($des, 'director') !== false) {
        $leadership[] = $m;
    } elseif (strpos($des, 'investigator') !== false || strpos($des, 'pi') !== false || strpos($des, 'professor') !== false || strpos($des, 'scientist') !== false) {
        $investigators[] = $m;
    } elseif (strpos($des, 'staff') !== false || strpos($des, 'associate') !== false || strpos($des, 'assistant') !== false || strpos($des, 'fellow') !== false || strpos($des, 'coordinator') !== false || strpos($des, 'technical') !== false) {
        $staff[] = $m;
    } elseif (strpos($des, 'student') !== false || strpos($des, 'scholar') !== false || strpos($des, 'phd') !== false || strpos($des, 'intern') !== false) {
        $students[] = $m;
    } else {
        $others[] = $m;
    }
}

// Merge others into investigators for rendering clean blocks if not matched
if (!empty($others)) {
    $investigators = array_merge($investigators, $others);
    // resort investigators by display_order
    usort($investigators, function($a, $b) {
        if ($a['display_order'] === $b['display_order']) {
            return strcmp($a['full_name'], $b['full_name']);
        }
        return $a['display_order'] - $b['display_order'];
    });
}

// Helper function to render a team card
function renderTeamCard($m) {
    $photo = !empty($m['profile_image']) && file_exists($m['profile_image']) ? $m['profile_image'] : null;
    $deptUni = [];
    if (!empty($m['department'])) $deptUni[] = $m['department'];
    if (!empty($m['university'])) $deptUni[] = $m['university'];
    $deptUniStr = implode(', ', $deptUni);
    ?>
    <div class="team-card mb-4" style="background:#fff; border:1px solid #eee; border-radius:8px; padding:25px; box-shadow:0 4px 15px rgba(0,0,0,0.03); transition: transform 0.25s ease;">
        <div class="row" style="display:flex; align-items:center; flex-wrap:wrap; gap:20px;">
            <?php if ($photo): ?>
            <div class="col-sm-3 col-xs-12 text-center" style="flex:0 0 auto; width:130px; padding:0 15px;">
                <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($m['full_name']) ?>" loading="lazy" style="width:110px; height:110px; object-fit:cover; border-radius:50%; border:3px solid #eee; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
            </div>
            <?php endif; ?>
            
            <div class="col-sm-9 col-xs-12" style="flex:1; min-width:0; padding:0 15px;">
                <div class="team-card-content">
                    <h3 class="member-name" style="margin-top:0; margin-bottom:5px; color:#BC2121; font-weight:700; font-size:18px;"><?= htmlspecialchars($m['full_name']) ?></h3>
                    <div class="member-designation" style="font-size:14px; color:#555; font-weight:500; margin-bottom:4px;">
                        <span><?= htmlspecialchars($m['designation']) ?></span>
                    </div>
                    <?php if (!empty($deptUniStr)): ?>
                    <div class="member-org" style="font-weight:600; color:#333; font-size:14px; margin-bottom:8px;"><?= htmlspecialchars($deptUniStr) ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($m['research_area'])): ?>
                    <div class="member-research-area mb-2" style="font-size:12px; font-weight:700; color:#0d47a1; background:#e3f2fd; padding:3px 10px; border-radius:4px; display:inline-block; text-transform:uppercase;">
                        <i class="fa fa-flask mr-1"></i> Research Area: <?= htmlspecialchars($m['research_area']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($m['biography'])): ?>
                    <p class="member-bio" style="font-size:13px; color:#666; line-height:1.5; margin-top:8px; margin-bottom:12px;"><?= htmlspecialchars($m['biography']) ?></p>
                    <?php endif; ?>
                    
                    <div class="member-contact-bar" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:10px;">
                        <?php if (!empty($m['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="contact-pill">
                            <i class="fa fa-envelope"></i> <?= htmlspecialchars($m['email']) ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($m['phone'])): ?>
                        <a href="tel:<?= htmlspecialchars($m['phone']) ?>" class="contact-pill">
                            <i class="fa fa-phone"></i> <?= htmlspecialchars($m['phone']) ?>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($m['linkedin'])): ?>
                        <a href="<?= htmlspecialchars($m['linkedin']) ?>" target="_blank" rel="noopener noreferrer" class="contact-pill" style="border-color:#0077b5;">
                            <i class="fab fa-linkedin" style="color:#0077b5;"></i> LinkedIn
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($m['google_scholar'])): ?>
                        <a href="<?= htmlspecialchars($m['google_scholar']) ?>" target="_blank" rel="noopener noreferrer" class="contact-pill" style="border-color:#4285f4;">
                            <i class="fa fa-graduation-cap" style="color:#4285f4;"></i> Scholar
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($m['orcid'])): ?>
                        <a href="https://orcid.org/<?= htmlspecialchars($m['orcid']) ?>" target="_blank" rel="noopener noreferrer" class="contact-pill" style="border-color:#a6ce39;">
                            <i class="fa-solid fa-id-card" style="color:#a6ce39;"></i> ORCID: <?= htmlspecialchars($m['orcid']) ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>



<!-- Header -->

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li class="active">Members</li>
    </ol>
</div>

<!-- Page Content -->
<div id="page-content">
    <div class="container">
        <div class="row">
            
            <!-- MAIN Content -->
            <div class="col-md-8">
                <div id="page-main">
                    <section id="members">
                        <header><h1 class="page-title" style="color: #002b5c; font-weight:700;">Team Overview</h1></header>
                        
                        <?php if (empty($members)): ?>
                        <div style="background:#f9fafb; border-radius:12px; border: 2px dashed #d1d5db; padding: 60px 20px; text-align:center; margin-top:20px;">
                            <i class="fa fa-users" style="font-size:2.8rem; color:#9ca3af;"></i>
                            <h5 style="color:#6b7280; margin-top: 16px;">No team members added yet.</h5>
                            <p style="color:#9ca3af; font-size:14px;">Members added via the Super Admin portal will display here.</p>
                        </div>
                        <?php endif; ?>

                        <!-- SECTION 1: PROJECT LEADERSHIP -->
                        <?php if (!empty($leadership)): ?>
                        <section id="project-leadership" class="mt-4">
                            <header>
                                <h3 class="section-heading">Project Leadership</h3>
                            </header>
                            <?php foreach ($leadership as $m) renderTeamCard($m); ?>
                        </section>
                        <?php endif; ?>

                        <!-- SECTION 2: PRINCIPAL INVESTIGATORS -->
                        <?php if (!empty($investigators)): ?>
                        <hr class="section-divider">
                        <section id="investigators">
                            <header>
                                <h3 class="section-heading">Principal & Co-Principal Investigators</h3>
                            </header>
                            <?php foreach ($investigators as $m) renderTeamCard($m); ?>
                        </section>
                        <?php endif; ?>

                        <!-- SECTION 3: RESEARCH STAFF -->
                        <?php if (!empty($staff)): ?>
                        <hr class="section-divider">
                        <section id="research-staff" class="mb-5">
                            <header>
                                <h3 class="section-heading">Research Staff</h3>
                            </header>
                            <?php foreach ($staff as $m) renderTeamCard($m); ?>
                        </section>
                        <?php endif; ?>

                        <!-- SECTION 4: RESEARCH STUDENTS / SCHOLARS -->
                        <?php if (!empty($students)): ?>
                        <hr class="section-divider">
                        <section id="students" class="mb-5">
                            <header>
                                <h3 class="section-heading">Research Students & Scholars</h3>
                            </header>
                            <?php foreach ($students as $m) renderTeamCard($m); ?>
                        </section>
                        <?php endif; ?>

                    </section>
                </div>
            </div><!-- /.col-md-8 -->

            <!-- SIDEBAR Content -->
            <div class="col-md-4">
                <div id="page-sidebar" class="sidebar">
                    <aside id="quick-contact">
                        <header><h2>Contact Directory</h2></header>
                        <div class="section-content">
                            <address>
                                <strong>PAIR Directorate</strong><br>
                                University of Hyderabad<br>
                                <abbr title="Phone">P:</abbr> 914023134546<br>
                                <abbr title="Email">E:</abbr> <a href="mailto:pairdirecorate@uohyd.ac.in">pairdirecorate@uohyd.ac.in</a>
                            </address>
                        </div>
                    </aside>
                    <aside id="links">
                        <header><h2>Quick Links</h2></header>
                        <ul class="list-links">
                            <?php if (!empty($leadership)): ?>
                            <li><a href="#project-leadership">Project Leadership</a></li>
                            <?php endif; ?>
                            <?php if (!empty($investigators)): ?>
                            <li><a href="#investigators">Principal Investigators</a></li>
                            <?php endif; ?>
                            <?php if (!empty($staff)): ?>
                            <li><a href="#research-staff">Research Staff</a></li>
                            <?php endif; ?>
                            <?php if (!empty($students)): ?>
                            <li><a href="#students">Students & Scholars</a></li>
                            <?php endif; ?>
                        </ul>
                    </aside>
                </div><!-- /#sidebar -->
            </div><!-- /.col-md-4 -->
        </div><!-- /.row -->
    </div><!-- /.container -->
</div>

<!-- Footer -->
<?php include 'footer.php';?>
<!-- end Footer -->

</div>
<!-- end Wrapper -->

<style>
/* Custom card and pill style overrides */
.team-card:hover {
    transform: translateY(-3px);
}
.contact-pill {
    display: inline-block;
    padding: 6px 14px;
    background: #f8f9fa;
    border: 1px solid #e2e8f0;
    border-radius: 50px;
    color: #475569 !important;
    font-size: 12px;
    text-decoration: none !important;
    font-weight: 500;
    transition: all 0.2s;
}
.contact-pill i {
    color: #BC2121;
    margin-right: 5px;
}
.contact-pill:hover {
    background: #BC2121;
    color: #fff !important;
    border-color: #BC2121 !important;
}
.contact-pill:hover i {
    color: #fff !important;
}
.section-heading {
    font-weight: 700;
    color: #002b5c;
    text-transform: uppercase;
    font-size: 16px;
    margin-top: 15px;
    margin-bottom: 25px;
    border-left: 4px solid #BC2121;
    padding-left: 10px;
}
.section-divider {
    margin: 35px 0;
    border-color: #f1f5f9;
}
</style>
</body>
