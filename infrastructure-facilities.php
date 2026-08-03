



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
                            
                            <!-- jsPDF CDN -->
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

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

                            <?php
                            $instrumentCategories = [
                                'School of Life Science' => [
                                    ['name' => 'Surface Plasmon Resonance (SPR)', 'make' => 'Cytiva', 'desc' => 'Real-time molecular molecular binding kinetics.'],
                                    ['name' => 'Large Scale Protein Purification unit', 'make' => 'Amersham', 'desc' => 'Bulk protein isolation system.'],
                                    ['name' => 'Table top Micro Ultra Centrifuge', 'make' => 'Beckman Coulter', 'desc' => 'High-speed small volume separation.'],
                                    ['name' => 'Floor model Ultra Centrifuge', 'make' => 'Beckman Coulter', 'desc' => 'Large volume density fractionation.'],
                                    ['name' => 'High Speed Centrifuge', 'make' => 'Beckman Coulter', 'desc' => 'General biological sample pelleting.'],
                                    ['name' => 'Preparative LCMS', 'make' => 'Agilent Technologies', 'desc' => 'Compound purification with mass ID.'],
                                    ['name' => 'Confocal Laser Scanning Microscope', 'make' => 'Zeiss', 'desc' => 'High-contrast 3D optical sectioning.'],
                                    ['name' => '3D Laser Scanning Super Resolution Microscopy with Leica SP8', 'make' => 'Leica SP8', 'desc' => 'Nanoscale live cell imaging.'],
                                    ['name' => 'Ultra Microtome', 'make' => 'Leica', 'desc' => 'Ultra-thin slicing for microscopy.'],
                                    ['name' => 'In Vivo Imaging', 'make' => 'Kodak', 'desc' => 'Small animal biological tracking.'],
                                    ['name' => 'Sea Horse Metabolic flux Analyzer', 'make' => 'Agilent Technologies', 'desc' => 'Cellular energy metabolism measurement.'],
                                    ['name' => 'Flow Cytometer LSR Fortessa', 'make' => 'BD LifeSciences', 'desc' => 'Multiparameter cell analysis.'],
                                    ['name' => 'Flow Cytometer BD FACS AriaIII', 'make' => 'BD LifeSciences', 'desc' => 'Cell analysis and physical sorting.'],
                                    ['name' => 'Covaris Focused Ultra Sonicator M22', 'make' => 'Covaris', 'desc' => 'Precision DNA/RNA fragmentation.'],
                                    ['name' => 'MilliQEllix', 'make' => 'Merck', 'desc' => 'Ultrapure water generation.'],
                                    ['name' => 'Real Time PCR Machine with HRM', 'make' => 'Roche', 'desc' => 'Gene quantification and mutation detection.'],
                                    ['name' => 'Fully Motorized Trinocular Fluorescent Microscope', 'make' => 'Leica Microsystems', 'desc' => 'Automated multi-channel fluorescence imaging.']
                                ],
                                'Department of Biochemistry' => [
                                    ['name' => 'Stackable Refrigerated Incubator', 'make' => 'Orbitek', 'desc' => 'Temperature-controlled culture storage.'],
                                    ['name' => 'ND2000C NanoDrop Spectrophotometer', 'make' => 'Thermo', 'desc' => 'Micro-volume DNA/protein quantification.'],
                                    ['name' => 'Shimadzu Prominence High Performance Liquid Chromatography', 'make' => 'Shimadzu', 'desc' => 'High-precision chemical separation.'],
                                    ['name' => 'Sonics -Sonicator -New', 'make' => 'Sonics', 'desc' => 'Ultrasonic cell lysis/homogenization.'],
                                    ['name' => 'SCANVAC Freeze Drier cum Speed Vacuum Concentrator', 'make' => 'Labogene APS-Het Cooling Trap', 'desc' => 'Sample drying and volume reduction.'],
                                    ['name' => 'Syngene Gel Documentation System', 'make' => 'Syngene', 'desc' => 'DNA/Protein gel visualization.'],
                                    ['name' => 'Versa Doc Imaging System (5000MP)', 'make' => 'Versa Doc', 'desc' => 'High-resolution gel and blot imaging.'],
                                    ['name' => 'SpectraMax M2 Multimode Spectrophotometer', 'make' => 'Molecular Devices', 'desc' => 'Absorbance and fluorescence plate reader.'],
                                    ['name' => 'Reverse transcription polymerase chain reaction (RT-PCR)', 'make' => 'Quant Studio', 'desc' => 'Gene expression analysis (RNA detection).'],
                                    ['name' => 'Chemiluminescence Imaging system', 'make' => 'Vilber', 'desc' => 'Sensitive Western blot detection.'],
                                    ['name' => 'Inverted Microscope including plan phase, LWD objectives, centring telescope, Plan Achromatic LED objective and shipping and handling', 'make' => 'Olympus', 'desc' => 'Live cell observation (bottom-up optics).'],
                                    ['name' => 'Hi Speed Centrifuge', 'make' => 'Hitachi', 'desc' => 'Rapid sample separation and pelleting.'],
                                    ['name' => 'Hypoxia Chamber', 'make' => 'Bioxia', 'desc' => 'Low-oxygen cell culture environment.'],
                                    ['name' => 'Gradient Fractionator', 'make' => 'Crystal Bio Equipment', 'desc' => 'Precision separation of density layers.'],
                                    ['name' => 'Gas Chromatography & Mass Spectrometry', 'make' => 'Agilent Technologies', 'desc' => 'Volatile compound identification.'],
                                    ['name' => 'Multimode Plate Reader', 'make' => 'Genesis Bio Solution', 'desc' => 'High-throughput assay measurement.'],
                                    ['name' => 'Fast Protein Liquid Chromatography', 'make' => 'Bio Rad Laboratories', 'desc' => 'Automated protein purification system.'],
                                    ['name' => 'High Performance Liquid Chromatography', 'make' => 'Smart Labtech', 'desc' => 'High-precision chemical separation.'],
                                    ['name' => 'High Speed Centrifuge', 'make' => 'Thermo', 'desc' => 'Rapid sample separation and pelleting.'],
                                    ['name' => 'Incubator Shaker', 'make' => 'Alpha Instruments', 'desc' => 'Agitated temperature-controlled growth.'],
                                    ['name' => 'BOD Incubator', 'make' => 'Med Lab', 'desc' => 'Low-temperature (20°C) incubation.'],
                                    ['name' => 'Respirometer', 'make' => 'OroBoros', 'desc' => 'Oxygen consumption rate measurement.'],
                                    ['name' => 'Ultra Centrifuge', 'make' => 'Beckman Coulter', 'desc' => 'Nanoscale separation (Viruses/DNA).']
                                ],
                                'Department of Plant Sciences' => [
                                    ['name' => 'HRMS- High Resolution Mass Spectrophotometer', 'make' => 'Waters', 'desc' => 'Exact mass measurement & molecular ID.']
                                ]
                            ];
                            ?>

                            <div class="category-container">
                                <?php 
                                $categoryIdx = 0;
                                foreach ($instrumentCategories as $categoryName => $instruments): 
                                    $categoryIdx++;
                                    // Start the first one expanded, collapse the others by default
                                    $collapsedClass = ($categoryIdx === 1) ? '' : 'collapsed';
                                    $iconClass = ($categoryIdx === 1) ? 'fa-minus' : 'fa-plus';
                                ?>
                                    <div class="category-row">
                                        <div class="category-header">
                                            <h3>
                                                <i class="fa fa-university"></i>
                                                <?= htmlspecialchars($categoryName) ?>
                                            </h3>
                                            <div class="header-right-info">
                                                <span class="instrument-count"><?= count($instruments) ?> Instruments</span>
                                                <span class="toggle-icon"><i class="fa <?= $iconClass ?>"></i></span>
                                            </div>
                                        </div>
                                        <div class="category-content <?= $collapsedClass ?>">
                                            <div class="instrument-table-wrapper">
                                                <table class="instrument-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 35%;">Name of the Instrument</th>
                                                            <th style="width: 50%;">Description</th>
                                                            <th style="width: 15%;">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($instruments as $item): ?>
                                                            <tr>
                                                                <td class="instrument-name-cell" data-label="Name of the Instrument">
                                                                    <?= htmlspecialchars($item['name']) ?><br>
                                                                    <span class="instrument-make-badge">Make: <?= htmlspecialchars($item['make']) ?></span>
                                                                </td>
                                                                <td class="instrument-desc-cell" data-label="Description">
                                                                    <?= htmlspecialchars($item['desc']) ?>
                                                                </td>
                                                                <td data-label="Actions">
                                                                    <div class="action-btn-group">
                                                                        <button type="button" class="action-btn btn-preview" onclick="openPreview(<?= htmlspecialchars(json_encode($item['name'])) ?>, <?= htmlspecialchars(json_encode($item['make'])) ?>, <?= htmlspecialchars(json_encode($categoryName)) ?>, <?= htmlspecialchars(json_encode($item['desc'])) ?>)">
                                                                            <i class="fa fa-eye"></i> Preview
                                                                        </button>
                                                                        <button type="button" class="action-btn btn-download" onclick="downloadPDF(<?= htmlspecialchars(json_encode($item['name'])) ?>, <?= htmlspecialchars(json_encode($item['make'])) ?>, <?= htmlspecialchars(json_encode($categoryName)) ?>, <?= htmlspecialchars(json_encode($item['desc'])) ?>)">
                                                                            <i class="fa fa-file-pdf-o"></i> PDF
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Preview Modal Overlay -->
                            <div class="modal-overlay" id="instrumentModal">
                                <div class="modal-card">
                                    <div class="modal-header">
                                        <h4 id="modalTitle">Instrument Details</h4>
                                        <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="modal-meta-row">
                                            <div class="modal-meta-item">
                                                <div class="modal-meta-label">Department / School</div>
                                                <div class="modal-meta-value" id="modalDept">-</div>
                                            </div>
                                            <div class="modal-meta-item">
                                                <div class="modal-meta-label">Manufacturer / Make</div>
                                                <div class="modal-meta-value" id="modalMake">-</div>
                                            </div>
                                        </div>
                                        <div class="modal-desc-section">
                                            <div class="modal-desc-label">Instrument Description</div>
                                            <div class="modal-desc-value" id="modalDesc">-</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="action-btn btn-modal-close" onclick="closeModal()">Close</button>
                                        <button type="button" class="action-btn btn-download" id="modalDownloadBtn">
                                            <i class="fa fa-file-pdf-o"></i> Download PDF
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- JavaScript Controls -->
                            <script>
                                // Accordion Toggle
                                document.querySelectorAll('.category-header').forEach(header => {
                                    header.addEventListener('click', () => {
                                        const content = header.nextElementSibling;
                                        const icon = header.querySelector('.toggle-icon i');
                                        
                                        content.classList.toggle('collapsed');
                                        if (content.classList.contains('collapsed')) {
                                            icon.className = 'fa fa-plus';
                                        } else {
                                            icon.className = 'fa fa-minus';
                                        }
                                    });
                                });

                                // Modal Controls
                                const modal = document.getElementById('instrumentModal');
                                const modalTitle = document.getElementById('modalTitle');
                                const modalDept = document.getElementById('modalDept');
                                const modalMake = document.getElementById('modalMake');
                                const modalDesc = document.getElementById('modalDesc');
                                const modalDownloadBtn = document.getElementById('modalDownloadBtn');

                                function openPreview(name, make, category, desc) {
                                    modalTitle.textContent = name;
                                    modalDept.textContent = category;
                                    modalMake.textContent = make;
                                    modalDesc.textContent = desc;
                                    
                                    modalDownloadBtn.onclick = function() {
                                        downloadPDF(name, make, category, desc);
                                    };
                                    
                                    modal.classList.add('show');
                                }

                                function closeModal() {
                                    modal.classList.remove('show');
                                }

                                modal.addEventListener('click', (e) => {
                                    if (e.target === modal) {
                                        closeModal();
                                    }
                                });

                                // Client-side PDF Generation with jsPDF
                                function downloadPDF(name, make, category, desc) {
                                    if (!window.jspdf) {
                                        alert("PDF library is still loading. Please try again in a moment.");
                                        return;
                                    }
                                    
                                    const { jsPDF } = window.jspdf;
                                    const doc = new jsPDF({
                                        orientation: 'p',
                                        unit: 'mm',
                                        format: 'a4'
                                    });
                                    
                                    // 1. Header Blue Banner
                                    doc.setFillColor(0, 43, 92); // #002b5c
                                    doc.rect(0, 0, 210, 25, 'F');
                                    
                                    // Header Text
                                    doc.setTextColor(255, 255, 255);
                                    doc.setFont('helvetica', 'bold');
                                    doc.setFontSize(14);
                                    doc.text("ANRF-PAIR PROJECT - UNIVERSITY OF HYDERABAD", 15, 16);
                                    
                                    // Title Section
                                    doc.setTextColor(0, 43, 92); // #002b5c
                                    doc.setFontSize(18);
                                    doc.text("Technical Instrument Specification", 15, 40);
                                    
                                    // Drawing a Divider Line
                                    doc.setDrawColor(226, 232, 240); // #e2e8f0
                                    doc.setLineWidth(0.5);
                                    doc.line(15, 45, 195, 45);
                                    
                                    // Metadata fields
                                    doc.setFontSize(11);
                                    doc.setTextColor(100, 116, 139); // Slate Label Color
                                    
                                    // Department
                                    doc.setFont('helvetica', 'bold');
                                    doc.text("DEPARTMENT / SCHOOL:", 15, 55);
                                    doc.setTextColor(30, 41, 59); // Slate Dark Value Color
                                    doc.setFont('helvetica', 'normal');
                                    doc.text(category, 68, 55);
                                    
                                    // Make / Manufacturer
                                    doc.setTextColor(100, 116, 139);
                                    doc.setFont('helvetica', 'bold');
                                    doc.text("MANUFACTURER / MAKE:", 15, 65);
                                    doc.setTextColor(30, 41, 59);
                                    doc.setFont('helvetica', 'normal');
                                    doc.text(make, 68, 65);
                                    
                                    // Instrument Name
                                    doc.setTextColor(100, 116, 139);
                                    doc.setFont('helvetica', 'bold');
                                    doc.text("INSTRUMENT NAME:", 15, 75);
                                    doc.setTextColor(30, 41, 59);
                                    doc.setFont('helvetica', 'bold');
                                    const wrappedName = doc.splitTextToSize(name, 125);
                                    doc.text(wrappedName, 68, 75);
                                    
                                    // Calculate height based on name height
                                    let nextY = 75 + (wrappedName.length * 6);
                                    
                                    // Divider before description
                                    doc.setDrawColor(226, 232, 240);
                                    doc.line(15, nextY, 195, nextY);
                                    nextY += 10;
                                    
                                    // Description Section
                                    doc.setTextColor(100, 116, 139);
                                    doc.setFont('helvetica', 'bold');
                                    doc.text("SPECIFICATION & DESCRIPTION:", 15, nextY);
                                    nextY += 8;
                                    
                                    doc.setTextColor(51, 65, 85);
                                    doc.setFont('helvetica', 'normal');
                                    const wrappedDesc = doc.splitTextToSize(desc, 180);
                                    doc.text(wrappedDesc, 15, nextY);
                                    
                                    nextY += (wrappedDesc.length * 6) + 20;
                                    
                                    // Footer line
                                    doc.setDrawColor(226, 232, 240);
                                    doc.line(15, nextY, 195, nextY);
                                    
                                    // Footer text
                                    doc.setFontSize(8.5);
                                    doc.setTextColor(148, 163, 184);
                                    doc.text("This is an automatically generated specification sheet from the ANRF-PAIR portal.", 15, nextY + 8);
                                    doc.text("University of Hyderabad - Advanced Analytical Infrastructure.", 15, nextY + 13);
                                    
                                    // Save the document
                                    const sanitizedFilename = name.toLowerCase().replace(/[^a-z0-9]+/g, '_') + '_specs.pdf';
                                    doc.save(sanitizedFilename);
                                }
                            </script>
                           
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
