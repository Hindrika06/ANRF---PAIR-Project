



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
                            


                            <style>
                                /* Instrument Category Panels */
                                .category-container {
                                    margin-top: 25px;
                                }
                                .category-row {
                                    margin-bottom: 24px;
                                    border: 1px solid #eef2f6;
                                    border-radius: 12px;
                                    overflow: hidden;
                                    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
                                    background: #ffffff;
                                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                                }
                                .category-row:hover {
                                    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
                                }
                                .category-header {
                                    background: #002b5c;
                                    color: #ffffff;
                                    padding: 18px 24px;
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    cursor: pointer;
                                    user-select: none;
                                    transition: background-color 0.2s ease;
                                }
                                .category-header:hover {
                                    background: #0a3d75;
                                }
                                .category-header-static {
                                    background: #002b5c;
                                    color: #ffffff;
                                    padding: 18px 24px;
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                }
                                .category-header h3 {
                                    margin: 0;
                                    font-size: 17px;
                                    font-weight: 700;
                                    color: #ffffff;
                                    display: flex;
                                    align-items: center;
                                    gap: 12px;
                                    line-height: 1.4;
                                }
                                .category-header h3 i {
                                    font-size: 18px;
                                    color: #ffc107;
                                }
                                .header-right-info {
                                    display: flex;
                                    align-items: center;
                                    gap: 15px;
                                }
                                .instrument-count {
                                    background: rgba(255, 255, 255, 0.15);
                                    padding: 4px 12px;
                                    border-radius: 20px;
                                    font-size: 12px;
                                    font-weight: 600;
                                    letter-spacing: 0.5px;
                                }
                                .toggle-icon {
                                    font-size: 14px;
                                    transition: transform 0.2s ease;
                                }
                                .category-content {
                                    display: block;
                                    padding: 24px;
                                    border-top: 1px solid #eef2f6;
                                }
                                .category-content.collapsed {
                                    display: none;
                                }

                                /* Responsive Table */
                                .instrument-table-wrapper {
                                    overflow-x: auto;
                                    border-radius: 8px;
                                    border: 1px solid #eef2f6;
                                }
                                .instrument-table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin: 0;
                                }
                                .instrument-table th {
                                    background: #f8fafc;
                                    color: #002b5c;
                                    font-weight: 700;
                                    text-align: left;
                                    padding: 14px 18px;
                                    font-size: 13.5px;
                                    border-bottom: 2px solid #eef2f6;
                                    text-transform: uppercase;
                                    letter-spacing: 0.5px;
                                }
                                .instrument-table td {
                                    padding: 14px 18px;
                                    font-size: 14px;
                                    border-bottom: 1px solid #eef2f6;
                                    color: #334155;
                                    vertical-align: middle;
                                }
                                .instrument-table tbody tr {
                                    transition: background-color 0.15s ease;
                                }
                                .instrument-table tbody tr:hover {
                                    background: #f8fafc;
                                }
                                .instrument-name-cell {
                                    font-weight: 700;
                                    color: #1e293b;
                                    font-size: 14.5px;
                                }
                                .instrument-make-badge {
                                    display: inline-block;
                                    background: #f1f5f9;
                                    color: #475569;
                                    font-size: 11px;
                                    padding: 2px 8px;
                                    border-radius: 4px;
                                    margin-top: 5px;
                                    font-weight: 600;
                                    border: 1px solid #e2e8f0;
                                }
                                .instrument-desc-cell {
                                    line-height: 1.6;
                                    color: #475569;
                                }

                                /* Action Buttons */
                                .action-btn-group {
                                    display: flex;
                                    gap: 8px;
                                    justify-content: flex-start;
                                }
                                .action-btn {
                                    padding: 8px 14px;
                                    font-size: 12.5px;
                                    font-weight: 700;
                                    border-radius: 6px;
                                    border: none;
                                    cursor: pointer;
                                    transition: all 0.2s ease;
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 6px;
                                    text-decoration: none !important;
                                }
                                .btn-preview {
                                    background: #e0f2fe;
                                    color: #0369a1;
                                }
                                .btn-preview:hover {
                                    background: #bae6fd;
                                    color: #0369a1;
                                }
                                .btn-download {
                                    background: #fee2e2;
                                    color: #bc2121;
                                }
                                .btn-download:hover {
                                    background: #fca5a5;
                                    color: #bc2121;
                                }

                                /* Modal Popup Window */
                                .modal-overlay {
                                    position: fixed;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    background: rgba(15, 23, 42, 0.6);
                                    backdrop-filter: blur(4px);
                                    z-index: 2000;
                                    display: none;
                                    align-items: center;
                                    justify-content: center;
                                    opacity: 0;
                                    transition: opacity 0.3s ease;
                                    padding: 20px;
                                }
                                .modal-overlay.show {
                                    display: flex;
                                    opacity: 1;
                                }
                                .modal-card {
                                    background: #ffffff;
                                    border-radius: 16px;
                                    width: 100%;
                                    max-width: 650px;
                                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                                    overflow: hidden;
                                    transform: translateY(-20px);
                                    transition: transform 0.3s ease;
                                    border: 1px solid #eef2f6;
                                }
                                .modal-overlay.show .modal-card {
                                    transform: translateY(0);
                                }
                                .modal-header {
                                    background: #002b5c;
                                    color: #ffffff;
                                    padding: 20px 24px;
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                }
                                .modal-header h4 {
                                    margin: 0;
                                    color: #ffffff;
                                    font-size: 18px;
                                    font-weight: 700;
                                }
                                .modal-close {
                                    background: transparent;
                                    border: none;
                                    color: #ffffff;
                                    font-size: 24px;
                                    cursor: pointer;
                                    opacity: 0.8;
                                    transition: opacity 0.2s ease;
                                    line-height: 1;
                                    padding: 0;
                                }
                                .modal-close:hover {
                                    opacity: 1;
                                }
                                .modal-body {
                                    padding: 24px;
                                }
                                .modal-meta-row {
                                    display: flex;
                                    gap: 16px;
                                    margin-bottom: 20px;
                                    flex-wrap: wrap;
                                }
                                .modal-meta-item {
                                    flex: 1;
                                    min-width: 200px;
                                    background: #f8fafc;
                                    padding: 12px 16px;
                                    border-radius: 8px;
                                    border: 1px solid #f1f5f9;
                                }
                                .modal-meta-label {
                                    font-size: 10px;
                                    text-transform: uppercase;
                                    color: #64748b;
                                    font-weight: 700;
                                    margin-bottom: 4px;
                                    letter-spacing: 0.5px;
                                }
                                .modal-meta-value {
                                    font-size: 14px;
                                    color: #1e293b;
                                    font-weight: 700;
                                }
                                .modal-desc-section {
                                    background: #f8fafc;
                                    padding: 20px;
                                    border-radius: 8px;
                                    border: 1px solid #f1f5f9;
                                }
                                .modal-desc-label {
                                    font-size: 10px;
                                    text-transform: uppercase;
                                    color: #64748b;
                                    font-weight: 700;
                                    margin-bottom: 8px;
                                    letter-spacing: 0.5px;
                                }
                                .modal-desc-value {
                                    font-size: 14px;
                                    color: #334155;
                                    line-height: 1.7;
                                }
                                .modal-footer {
                                    padding: 16px 24px;
                                    background: #f8fafc;
                                    border-top: 1px solid #eef2f6;
                                    display: flex;
                                    justify-content: flex-end;
                                    gap: 12px;
                                }
                                .btn-modal-close {
                                    background: #e2e8f0;
                                    color: #475569;
                                }
                                .btn-modal-close:hover {
                                    background: #cbd5e1;
                                    color: #334155;
                                }

                                /* Responsive Table layout for mobile */
                                @media (max-width: 768px) {
                                    .category-content {
                                        padding: 15px;
                                    }
                                    .instrument-table-wrapper {
                                        border: none;
                                    }
                                    .instrument-table, .instrument-table thead, .instrument-table tbody, .instrument-table th, .instrument-table td, .instrument-table tr {
                                        display: block;
                                    }
                                    .instrument-table thead {
                                        display: none;
                                    }
                                    .instrument-table tr {
                                        margin-bottom: 20px;
                                        background: #ffffff;
                                        border: 1px solid #eef2f6;
                                        border-radius: 10px;
                                        padding: 16px;
                                        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01);
                                    }
                                    .instrument-table td {
                                        border-bottom: none;
                                        padding: 8px 0;
                                        font-size: 13.5px;
                                    }
                                    .instrument-table td::before {
                                        content: attr(data-label);
                                        font-weight: 700;
                                        color: #64748b;
                                        display: block;
                                        font-size: 10px;
                                        text-transform: uppercase;
                                        margin-bottom: 4px;
                                        letter-spacing: 0.5px;
                                    }
                                    .action-btn-group {
                                        margin-top: 12px;
                                        justify-content: flex-start;
                                    }
                                }
                            </style>



                            <!-- Infrastructure Documentation Categories -->
                            <div class="category-row" style="margin-top: 25px; border-top: 4px solid #002b5c;">
                                <div class="category-header-static">
                                    <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 12px; line-height: 1.4;">
                                        <i class="fa fa-file-text" style="color: #ffc107; font-size: 18px;"></i>
                                        Infrastructure Documentation & Downloads
                                    </h3>
                                </div>
                                <div class="category-content">
                                    <div class="instrument-table-wrapper">
                                        <table class="instrument-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 30%;">Category / Department</th>
                                                    <th style="width: 50%;">Description</th>
                                                    <th style="width: 20%;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="instrument-name-cell" data-label="Category / Department">
                                                        School of Life Science
                                                    </td>
                                                    <td class="instrument-desc-cell" data-label="Description">
                                                        Infrastructure facilities and equipment list for the School of Life Sciences.
                                                    </td>
                                                    <td data-label="Actions">
                                                        <div class="action-btn-group">
                                                            <a href="assets/pdf/school_of_life_science.pdf" target="_blank" class="action-btn btn-preview">
                                                                <i class="fa fa-eye"></i> Preview
                                                            </a>
                                                            <a href="assets/pdf/school_of_life_science.pdf" download class="action-btn btn-download">
                                                                <i class="fa fa-file-pdf-o"></i> PDF
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="instrument-name-cell" data-label="Category / Department">
                                                        Department of Biochemistry
                                                    </td>
                                                    <td class="instrument-desc-cell" data-label="Description">
                                                        Infrastructure facilities and equipment list for the Department of Biochemistry.
                                                    </td>
                                                    <td data-label="Actions">
                                                        <div class="action-btn-group">
                                                            <a href="assets/pdf/department_of_biochemistry.pdf" target="_blank" class="action-btn btn-preview">
                                                                <i class="fa fa-eye"></i> Preview
                                                            </a>
                                                            <a href="assets/pdf/department_of_biochemistry.pdf" download class="action-btn btn-download">
                                                                <i class="fa fa-file-pdf-o"></i> PDF
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="instrument-name-cell" data-label="Category / Department">
                                                        Department of Plant Sciences
                                                    </td>
                                                    <td class="instrument-desc-cell" data-label="Description">
                                                        Infrastructure facilities and equipment list for the Department of Plant Sciences.
                                                    </td>
                                                    <td data-label="Actions">
                                                        <div class="action-btn-group">
                                                            <a href="assets/pdf/department_of_plant_sciences.pdf" target="_blank" class="action-btn btn-preview">
                                                                <i class="fa fa-eye"></i> Preview
                                                            </a>
                                                            <a href="assets/pdf/department_of_plant_sciences.pdf" download class="action-btn btn-download">
                                                                <i class="fa fa-file-pdf-o"></i> PDF
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
