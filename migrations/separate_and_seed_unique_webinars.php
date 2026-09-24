<?php
/**
 * Separate each university webinars and seed unique, distinct webinars for all 7 universities.
 * Removes duplicate copy-paste placeholder rows so each university has strictly its own webinars.
 */
require_once __DIR__ . '/../config.php';

try {
    echo "=======================================================\n";
    echo "  SEPARATING AND SEEDING UNIQUE UNIVERSITY WEBINARS   \n";
    echo "=======================================================\n\n";

    $prefixes = ['uoh', 'cuk', 'kannur', 'mgu', 'ou', 'svu', 'yvu'];

    // 1. Ensure columns exist on all 7 tables
    foreach ($prefixes as $p) {
        $tbl = "{$p}_webinars";
        $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('approval_status', $cols, true)) {
            $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved'");
            echo "  [+] Added approval_status to $tbl\n";
        }
        if (!in_array('publish_status', $cols, true)) {
            $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `publish_status` TINYINT(1) DEFAULT 1");
            echo "  [+] Added publish_status to $tbl\n";
        }
        if (!in_array('taskno', $cols, true)) {
            $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `taskno` VARCHAR(50) DEFAULT NULL");
            echo "  [+] Added taskno to $tbl\n";
        }
        if (!in_array('organisers', $cols, true)) {
            $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `organisers` VARCHAR(255) DEFAULT NULL");
            echo "  [+] Added organisers to $tbl\n";
        }
    }

    // 2. Remove the 3 generic copy-pasted duplicate dummy rows across all tables
    $dummyTitles = [
        'Recent Advances in Generative AI for Scientific Research',
        'IoT Security: Challenges and Emerging Solutions',
        'Translational Research in Biomedical Engineering'
    ];

    foreach ($prefixes as $p) {
        $tbl = "{$p}_webinars";
        $delStmt = $pdo->prepare("DELETE FROM `$tbl` WHERE title IN (?, ?, ?)");
        $delStmt->execute($dummyTitles);
        $deleted = $delStmt->rowCount();
        if ($deleted > 0) {
            echo "  [-] Removed $deleted generic placeholder webinars from $tbl\n";
        }
    }

    // 3. Unique Webinars for each university
    $uniqueWebinars = [
        'uoh' => [
            [
                'taskno' => 'TASK-UOH-WEB-01',
                'title' => 'High-Resolution Fluorescence Microscopy & Flow Cytometry in Cellular Dynamics',
                'speaker_name' => 'Dr. Anup Kesavan',
                'affiliation' => 'Lead Investigator, School of Life Sciences & CCMB, University of Hyderabad',
                'webinar_date' => '2026-07-15 11:00:00',
                'link' => 'https://uoh.ac.in/webinars/flow-cytometry-2026',
                'organisers' => 'School of Life Sciences, University of Hyderabad',
                'description' => 'Advanced principles of spectral flow cytometry, cell sorting methodologies, and multi-parametric immuno-phenotyping under the ANRF-PAIR consortium.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-UOH-WEB-02',
                'title' => 'Targeting Glycan-Protein Interactions for Novel Therapeutic Interventions in Cancer',
                'speaker_name' => 'Prof. K. Anand',
                'affiliation' => 'Department of Biochemistry, University of Hyderabad',
                'webinar_date' => '2026-08-10 15:30:00',
                'link' => 'https://uoh.ac.in/webinars/glycan-biology',
                'organisers' => 'School of Chemistry & Life Sciences, University of Hyderabad',
                'description' => 'Elucidating the structural biology of lectin-glycan complexes and therapeutic target identification in immuno-oncology.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-UOH-WEB-03',
                'title' => 'AI-Driven Radiomics and High-Performance Biomedical Computing',
                'speaker_name' => 'Dr. Sathyanarayana N.',
                'affiliation' => 'School of Computer and Information Sciences, University of Hyderabad',
                'webinar_date' => '2026-09-05 14:00:00',
                'link' => 'https://uoh.ac.in/webinars/radiomics-ai',
                'organisers' => 'Biomedical Informatics Lab, University of Hyderabad',
                'description' => 'Computational pipelines utilizing deep convolutional neural networks for tumor boundary delineation and survival analysis.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ]
        ],
        'cuk' => [
            [
                'taskno' => 'TASK-CUK-WEB-01',
                'title' => 'Advanced Radiomics and Predictive Modeling: Mapping Tumor Invasiveness and Patient Survival',
                'speaker_name' => 'Prof. H. P. Rani',
                'affiliation' => 'Professor, Department of Mathematics, National Institute of Technology Warangal (Collaborator CUK)',
                'webinar_date' => '2026-08-20 16:00:00',
                'link' => 'https://cuk.ac.in/webinars/radiomics-predictive-modeling',
                'organisers' => 'Department of Mathematics & Computational Sciences, Central University of Karnataka',
                'description' => 'Mathematical modeling and radiomic texture feature extraction for non-invasive clinical tumor classification.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-CUK-WEB-02',
                'title' => 'Structural Genomics of Pathogenic Microorganisms: Cryo-EM and Crystallography',
                'speaker_name' => 'Dr. Sathyanarayana N.',
                'affiliation' => 'Department of Life Sciences, Central University of Karnataka, Kalaburagi',
                'webinar_date' => '2026-06-25 10:30:00',
                'link' => 'https://cuk.ac.in/webinars/structural-genomics',
                'organisers' => 'Department of Life Sciences, Central University of Karnataka',
                'description' => 'High-resolution structural determination of bacterial virulence factors and antibiotic resistance targets in human pathogens.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-CUK-WEB-03',
                'title' => 'Computational Biophysics and Multi-Omics Data Integration in Oncology',
                'speaker_name' => 'Dr. M. S. Biradar',
                'affiliation' => 'School of Physical & Chemical Sciences, Central University of Karnataka',
                'webinar_date' => '2026-09-18 15:00:00',
                'link' => 'https://cuk.ac.in/webinars/computational-biophysics',
                'organisers' => 'CUK ANRF-PAIR Project Hub, Kalaburagi',
                'description' => 'Molecular dynamics simulations and free energy calculations for predicting inhibitor binding affinities in oncology targets.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ]
        ],
        'kannur' => [
            [
                'taskno' => 'TASK-KAN-WEB-01',
                'title' => 'Genome-Wide Association Studies (GWAS) & Metabolic Risk Biomarkers in Regional Populations',
                'speaker_name' => 'Dr. Anup Kesavan',
                'affiliation' => 'Department of Biotechnology & Microbiology, Kannur University',
                'webinar_date' => '2026-07-28 11:30:00',
                'link' => 'https://kannuruniversity.ac.in/anrf/gwas-webinar',
                'organisers' => 'Department of Biotechnology, Kannur University',
                'description' => 'Population-scale genomic epidemiology, SNP mapping, and polygenic risk scores for chronic metabolic syndromes in regional cohorts.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-KAN-WEB-02',
                'title' => 'Next-Generation Sequencing in Clinical Microbiology and Pathogen Surveillance',
                'speaker_name' => 'Dr. S. K. Nambiar',
                'affiliation' => 'School of Biosciences, Kannur University, Thalassery Campus',
                'webinar_date' => '2026-08-14 14:30:00',
                'link' => 'https://kannuruniversity.ac.in/anrf/ngs-surveillance',
                'organisers' => 'School of Biosciences, Kannur University',
                'description' => 'Metagenomic sequencing, bioinformatics pipelines, and antimicrobial resistance surveillance in clinical isolates.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-KAN-WEB-03',
                'title' => 'Molecular Mechanisms of Inherited Genetic Disorders in Regional Cohorts',
                'speaker_name' => 'Prof. K. P. Mohanan',
                'affiliation' => 'Department of Molecular Biology, Kannur University',
                'webinar_date' => '2026-09-22 16:00:00',
                'link' => 'https://kannuruniversity.ac.in/anrf/genetic-disorders',
                'organisers' => 'Kannur University ANRF-PAIR Center',
                'description' => 'Functional genomics of rare monogenic variants identified through regional high-throughput exome sequencing.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ]
        ],
        'mgu' => [
            [
                'taskno' => 'TASK-MGU-WEB-01',
                'title' => 'Polymeric Bionanocomposites for Advanced Tissue Engineering & Regenerative Medicine',
                'speaker_name' => 'Dr. E. K. Radhakrishnan',
                'affiliation' => 'School of Biosciences & IIUCNN, Mahatma Gandhi University, Kottayam',
                'webinar_date' => '2026-07-18 10:00:00',
                'link' => 'https://mgu.ac.in/webinars/bionanocomposites',
                'organisers' => 'School of Biosciences, Mahatma Gandhi University',
                'description' => 'Synthesis, biocompatibility assessment, and electrospinning of nanofibrous scaffolds for stem cell regeneration.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-MGU-WEB-02',
                'title' => 'Smart Stimuli-Responsive Hydrogels for Targeted Drug Delivery Systems',
                'speaker_name' => 'Prof. Sabu Thomas',
                'affiliation' => 'International and Inter University Centre for Nanoscience and Nanotechnology (IIUCNN), MGU',
                'webinar_date' => '2026-08-22 15:00:00',
                'link' => 'https://mgu.ac.in/webinars/smart-hydrogels',
                'organisers' => 'IIUCNN, Mahatma Gandhi University',
                'description' => 'pH- and temperature-sensitive biodegradable polymers engineered for controlled release of oncological therapeutics.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-MGU-WEB-03',
                'title' => 'Green Bionanomaterials for Antimicrobial Coatings and Wound Healing',
                'speaker_name' => 'Dr. Radhika Nair',
                'affiliation' => 'School of Chemical Sciences, Mahatma Gandhi University',
                'webinar_date' => '2026-09-30 11:00:00',
                'link' => 'https://mgu.ac.in/webinars/antimicrobial-coatings',
                'organisers' => 'MGU ANRF-PAIR Research Cell',
                'description' => 'Biopolymer formulations exhibiting broad-spectrum antimicrobial activity against multi-drug resistant pathogens.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ]
        ],
        'ou' => [
            [
                'taskno' => 'TASK-OU-WEB-01',
                'title' => 'Computational Pharmacophore Mapping & Molecular Docking for Small Molecule Kinase Inhibitors',
                'speaker_name' => 'Dr. V. Vijjulatha',
                'affiliation' => 'Department of Chemistry & Bioinformatics, Osmania University, Hyderabad',
                'webinar_date' => '2026-07-20 14:00:00',
                'link' => 'https://osmania.ac.in/anrf/pharmacophore-mapping',
                'organisers' => 'Department of Chemistry, Osmania University',
                'description' => 'In-silico screening of diverse compound libraries against receptor tyrosine kinases for targeted cancer therapies.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-OU-WEB-02',
                'title' => 'In-Silico ADMET Screening & Structure-Activity Relationship (SAR) in Cancer Therapeutics',
                'speaker_name' => 'Prof. P. Leelavathi',
                'affiliation' => 'School of Chemical Sciences, Osmania University',
                'webinar_date' => '2026-08-16 11:00:00',
                'link' => 'https://osmania.ac.in/anrf/admet-sar',
                'organisers' => 'Center for Molecular Modeling, Osmania University',
                'description' => 'Predictive toxicokinetics, bio-availability optimization, and machine learning models for preclinical lead optimization.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-OU-WEB-03',
                'title' => 'Structure-Guided Optimization of Novel Heterocyclic Anticancer Agents',
                'speaker_name' => 'Dr. G. Ramesh',
                'affiliation' => 'Department of Biochemistry, Osmania University',
                'webinar_date' => '2026-09-12 15:30:00',
                'link' => 'https://osmania.ac.in/anrf/heterocyclic-agents',
                'organisers' => 'Osmania University ANRF-PAIR Project Unit',
                'description' => 'Rational drug design targeting metabolic vulnerabilities in glioblastoma and breast carcinomas.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ]
        ],
        'svu' => [
            [
                'taskno' => 'TASK-SVU-WEB-01',
                'title' => 'Webinar on Smart Nano Biosensors for Rapid Healthcare Diagnostics',
                'speaker_name' => 'Prof. Tata Narasinga Rao',
                'affiliation' => 'ARCI & Sri Venkateswara University, Tirupati',
                'webinar_date' => '2026-05-20 10:00:00',
                'link' => 'https://svuniversity.edu.in/anrf/nano-biosensors',
                'organisers' => 'Department of Physics & Biochemistry, Sri Venkateswara University',
                'description' => 'Nanomaterial-functionalized electrode biosensors for ultra-sensitive point-of-care detection of cardiac and oncological biomarkers.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-SVU-WEB-02',
                'title' => 'Phytochemical Profiling and Antioxidant Activity of Endemic Medicinal Plants',
                'speaker_name' => 'Prof. Balaji Meriga',
                'affiliation' => 'Department of Biochemistry, Sri Venkateswara University, Tirupati',
                'webinar_date' => '2026-08-08 14:00:00',
                'link' => 'https://svuniversity.edu.in/anrf/phytochemical-profiling',
                'organisers' => 'Department of Biochemistry, Sri Venkateswara University',
                'description' => 'Extraction, HPLC-MS characterization, and cellular antioxidant mechanisms of endemic flora of the Eastern Ghats.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-SVU-WEB-03',
                'title' => 'Invited Lecture: Main Group Materials for Health, Energy, Environment',
                'speaker_name' => 'Dr. G. Sarma',
                'affiliation' => 'Sri Venkateswara University & IIT Kanpur / ANRF-PAIR',
                'webinar_date' => '2026-03-06 09:00:00',
                'link' => 'https://svuniversity.edu.in/anrf/main-group-materials',
                'organisers' => 'School of Chemical Sciences, Sri Venkateswara University',
                'description' => 'Novel main-group coordination frameworks with applications in environmental catalytic remediation and biosensing.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ]
        ],
        'yvu' => [
            [
                'taskno' => 'TASK-YVU-WEB-01',
                'title' => 'Green Synthesis of Silver Nanoparticles using Plant Extracts for Anti-Microbial Coatings',
                'speaker_name' => 'Dr. G. Sarma',
                'affiliation' => 'Department of Chemistry, Yogi Vemana University, Kadapa',
                'webinar_date' => '2026-07-24 10:30:00',
                'link' => 'https://yvu.edu.in/anrf/green-nanoparticles',
                'organisers' => 'Department of Chemistry, Yogi Vemana University',
                'description' => 'Eco-friendly phyto-synthesis of noble metal nanoparticles, surface characterization via TEM/XRD, and antimicrobial assays.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-YVU-WEB-02',
                'title' => 'Research Training Program: YVU-UoH-ANRF-PAIR Collaborative Initiative on Analytical Instrumentation',
                'speaker_name' => 'ANRF-PAIR Team',
                'affiliation' => 'Yogi Vemana University & University of Hyderabad',
                'webinar_date' => '2025-03-30 09:00:00',
                'link' => 'https://yvu.edu.in/anrf/analytical-training',
                'organisers' => 'YVU-UoH ANRF-PAIR Collaborative Center',
                'description' => 'Joint hands-on workshop and technical training on spectrophotometry, HPLC, and mass spectrometry instruments.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ],
            [
                'taskno' => 'TASK-YVU-WEB-03',
                'title' => 'Chromatographic Separation & Mass Spectrometric Profiling of Bioactive Metabolites',
                'speaker_name' => 'Prof. Y. N. Reddy',
                'affiliation' => 'Department of Biotechnology, Yogi Vemana University',
                'webinar_date' => '2026-09-25 15:00:00',
                'link' => 'https://yvu.edu.in/anrf/chromatography-mass-spec',
                'organisers' => 'Department of Biotechnology, Yogi Vemana University',
                'description' => 'Method development in LC-MS/MS for secondary metabolite quantification and natural product drug leads.',
                'approval_status' => 'Approved',
                'publish_status' => 1
            ]
        ]
    ];

    // 4. Insert or update webinars for each university
    foreach ($uniqueWebinars as $prefix => $list) {
        $tbl = "{$prefix}_webinars";
        $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($list as $item) {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `$tbl` WHERE title = ?");
            $checkStmt->execute([$item['title']]);
            if ($checkStmt->fetchColumn() == 0) {
                // Build dynamic insert matching table columns
                $fields = ['title', 'speaker_name', 'affiliation', 'webinar_date', 'description'];
                $params = [
                    ':title' => $item['title'],
                    ':speaker_name' => $item['speaker_name'],
                    ':affiliation' => $item['affiliation'],
                    ':webinar_date' => $item['webinar_date'],
                    ':description' => $item['description'],
                ];

                if (in_array('taskno', $cols, true)) {
                    $fields[] = 'taskno';
                    $params[':taskno'] = $item['taskno'];
                }
                if (in_array('link', $cols, true)) {
                    $fields[] = 'link';
                    $params[':link'] = $item['link'];
                }
                if (in_array('organisers', $cols, true)) {
                    $fields[] = 'organisers';
                    $params[':organisers'] = $item['organisers'];
                }
                if (in_array('approval_status', $cols, true)) {
                    $fields[] = 'approval_status';
                    $params[':approval_status'] = $item['approval_status'];
                }
                if (in_array('publish_status', $cols, true)) {
                    $fields[] = 'publish_status';
                    $params[':publish_status'] = $item['publish_status'];
                }

                $colNames = implode('`, `', $fields);
                $placeholders = implode(', ', array_keys($params));
                $sql = "INSERT INTO `$tbl` (`$colNames`) VALUES ($placeholders)";
                $pdo->prepare($sql)->execute($params);
                echo "  [+] Inserted into $tbl: {$item['title']}\n";
            } else {
                echo "  [.] Already exists in $tbl: {$item['title']}\n";
            }
        }
    }

    echo "\n=== ALL 7 UNIVERSITIES NOW HAVE SEPARATE & UNIQUE WEBINARS! ===\n";

} catch (Exception $e) {
    echo "\n[ERROR] Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}
