
<?php 
// 1. INTEGRATE DATABASE CONNECTION VIA YOUR EXISTING CONFIG FILE
require_once 'config.php'; 

include 'header.php';
?>

<body class="page-homepage-courses" style="font-size: 16px; line-height: 1.6;">


<div id="page-content">
    <div class="container">
        <ol class="breadcrumb" style="font-size: 14px;">
            <li><a href="index.html">Home</a></li>
            <li class="active">About ANRF–PAIR</li>
        </ol>
    </div>

   <section id="course-detail" style="margin-top: 15px;">
    <div class="block" style="background-color: #fff;">
        <div class="container">
            <div class="row">
                <div class="col-md-11 col-sm-11">
                    <div class="course-info">
                        <div class="overview-content">

                            <div>
                                <h2 class="no-theme-underline" style="font-size: 24px !important; font-weight: bold !important; text-transform: uppercase !important; text-decoration: none !important; border: none !important; border-left: 5px solid #002b5c !important; padding-left: 12px !important; margin-top: 25px !important; margin-bottom: 20px !important; color: #002b5c !important; background: none !important; background-image: none !important; box-shadow: none !important; outline: none !important;">PUBLICATIONS & RESEARCH OUTPUT</h2>
                            </div>
                            
                            <div class="publications-container">
                                <div class="author-entry">
                                  
                                    
                                    <ul class="pub-list">
                                        <?php
                                        try {
                                            // Selects everything from your correct table 'publications', sorted by ID descending
                                            $stmt = $pdo->query("SELECT * FROM uoh_publications ORDER BY id DESC");
                                            $hasData = false;
                                            
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $hasData = true;
                                                
                                                echo "<li>";
                                                // 1. Authors / Author Name
                                                echo htmlspecialchars($row['author_name'] ?? '') . " ";
                                                
                                                // 2. Publication Title (Plain text ending with a period)
                                                $title = trim($row['publication_title'] ?? '');
                                                if (!empty($title)) {
                                                    echo htmlspecialchars($title) . (substr($title, -1) === '.' ? ' ' : '. ');
                                                }
                                                
                                                // 3. Journal Name (Italicized)
                                                if (!empty($row['publication_journal'])) {
                                                    echo "<em>" . htmlspecialchars($row['publication_journal']) . "</em>";
                                                }
                                                
                                                // 4. Publication Date / Year formatting
                                                if (!empty($row['publication_date'])) {
                                                    $pubYear = date('Y', strtotime($row['publication_date']));
                                                    echo " (" . htmlspecialchars($pubYear) . ")";
                                                }
                                                
                                                // 5. Impact Factor (Optional metadata)
                                                if (!empty($row['impact_factor']) && $row['impact_factor'] > 0) {
                                                    echo " [Impact Factor: " . htmlspecialchars($row['impact_factor']) . "]";
                                                }
                                                
                                                // 6. DOI Hyperlink integration
                                                if (!empty($row['doi_number'])) {
                                                    // Standard prefixing for raw doi strings if needed, or direct output
                                                    $doiUrl = strpos($row['doi_number'], 'http') === 0 ? $row['doi_number'] : 'https://doi.org/' . $row['doi_number'];
                                                    echo ". <a href='" . htmlspecialchars($doiUrl) . "' target='_blank' class='pub-link'>doi: " . htmlspecialchars($row['doi_number']) . "</a>";
                                                } else {
                                                    echo ".";
                                                }
                                                
                                                // 7. Task Number Tag display on side
                                                if (!empty($row['task_no'])) {
                                                    echo " <span class='badge-category'>[" . htmlspecialchars($row['task_no']) . "]</span>";
                                                }
                                                
                                                echo "</li>";
                                            }
                                            
                                            if (!$hasData) {
                                                echo "<li class='text-muted' style='text-decoration: none !important;'>No publications found in the database.</li>";
                                            }
                                        } catch (PDOException $e) {
                                            echo "<li class='text-danger' style='text-decoration: none !important;'>Error loading entries: " . htmlspecialchars($e->getMessage()) . "</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</div>

</div>

<style>
    /* Strict overrides to completely crush template-generated heading lines/borders */
    h2.no-theme-underline::after,
    h2.no-theme-underline::before,
    .overview-content h2::after,
    .overview-content h2::before,
    #course-detail h2::after,
    #course-detail h2::before {
        display: none !important;
        content: none !important;
        border: none !important;
        background: none !important;
        height: 0 !important;
        width: 0 !important;
    }

    .author-entry {
        margin-bottom: 25px;
        padding-bottom: 10px;
    }
    .pub-list {
        list-style-type: decimal; 
        margin-left: 20px;
        font-size: 15px;
        text-decoration: none !important;
    }
    .pub-list li {
        margin-bottom: 15px;
        line-height: 1.6;
        text-decoration: none !important;
    }
    .pub-list a.pub-link {
        color: #0066cc;
        text-decoration: none !important;
    }
    .pub-list a.pub-link:hover {
        text-decoration: underline !important; 
    }
    .badge-category {
        font-size: 11px;
        color: #888;
        text-transform: uppercase;
        margin-left: 5px;
        text-decoration: none !important;
    }
</style>
</body>
<?php include 'footer.php';?>
