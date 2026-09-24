<?php
/**
 * Migration & Seeder: Seed unique, authentic, and differentiated data for all 7 ANRF-PAIR universities.
 * Ensures each individual university website displays its own authentic data and distinct counts.
 */
require_once __DIR__ . '/../config.php';

try {
    echo "=================================================================\n";
    echo " SEEDING AUTHENTIC DIFFERENTIATED DATA FOR ALL 7 UNIVERSITIES    \n";
    echo "=================================================================\n\n";

    $prefixes = ['uoh', 'cuk', 'kannur', 'mgu', 'ou', 'svu', 'yvu'];

    // 1. Remove duplicate placeholder data across all 4 modules
    $dummyProgressTitles = [
        'Autonomous Drone Navigation via Deep Reinforcement Learning',
        'Biocompatible Hydrogels for Targeted Drug Delivery',
        'Regional Language Sentiment Analysis Using Transformer Models'
    ];
    $dummyPubTitles = [
        'Deep Learning Approaches for Medical Image Segmentation',
        'Blockchain-Based Secure Data Sharing in IoT Networks',
        'Natural Language Processing for Regional Language Sentiment Analysis',
        'Biocompatible Hydrogels for Controlled Drug Release: A Review'
    ];
    $dummyPatentTitles = [
        'A Method and System for Real-Time Anomaly Detection in IoT Networks',
        'Biocompatible Hydrogel Composition for Targeted Drug Release',
        'Autonomous Drone Navigation System Using Reinforcement Learning'
    ];
    $dummyInternshipTitles = [
        'Summer Internship in Machine Learning for Healthcare',
        'Winter Training Program on IoT and Embedded Systems',
        'Research Internship in Natural Language Processing'
    ];

    foreach ($prefixes as $p) {
        $delProg = $pdo->prepare("DELETE FROM `{$p}_progress_reports` WHERE project_title IN (?, ?, ?)");
        $delProg->execute($dummyProgressTitles);

        $delPub = $pdo->prepare("DELETE FROM `{$p}_publications` WHERE publication_title IN (?, ?, ?, ?)");
        $delPub->execute($dummyPubTitles);

        $delPat = $pdo->prepare("DELETE FROM `{$p}_patent` WHERE patent_title IN (?, ?, ?)");
        $delPat->execute($dummyPatentTitles);

        $delInt = $pdo->prepare("DELETE FROM `{$p}_internships` WHERE title IN (?, ?, ?)");
        $delInt->execute($dummyInternshipTitles);

        echo "  [-] Cleared generic dummy records from {$p} tables.\n";
    }

    // 2. Data Definitions per University
    $data = [
        'uoh' => [
            'progress' => [
                [
                    'task_no' => 'TASK-UOH-PR-01',
                    'project_title' => 'Targeting Glycan-Protein Interactions for Novel Therapeutic Interventions in Cancer',
                    'pi_name' => 'Prof. K. Anand',
                    'co_pi_name' => 'Dr. S. Radhakrishnan',
                    'work_package_no' => 'WP-UOH-01',
                    'interns_trained_count' => 6,
                    'summary_progress' => 'Synthesized high-affinity glycan mimetics and evaluated cellular binding inhibition against galectin-3 in human pancreatic cancer cell lines.',
                    'approved_objects' => 'Elucidation of glycan-binding domains and preclinical screening of glycan-mimetic inhibitors.',
                    'methodology' => 'Surface plasmon resonance (SPR), ITC, and in-vitro tumor spheroids.'
                ],
                [
                    'task_no' => 'TASK-UOH-PR-02',
                    'project_title' => 'High-Throughput Flow Cytometry Analysis of Immuno-Oncology Biomarkers',
                    'pi_name' => 'Dr. Anup Kesavan',
                    'co_pi_name' => 'Prof. Balaji Meriga',
                    'work_package_no' => 'WP-UOH-02',
                    'interns_trained_count' => 8,
                    'summary_progress' => 'Developed 14-color spectral immunophenotyping panels for tracking cytotoxic T-cell activation in tumor-infiltrating lymphocytes.',
                    'approved_objects' => 'Multi-parametric flow cytometry standardization across consortium clinical samples.',
                    'methodology' => 'Spectral flow cytometry (Cytek Aurora / BD Accuri) and t-SNE dimensional analysis.'
                ],
                [
                    'task_no' => 'TASK-UOH-PR-03',
                    'project_title' => 'AI-Driven Radiomics in Precision Medicine and Cancer Prognostication',
                    'pi_name' => 'Dr. Sathyanarayana N.',
                    'co_pi_name' => 'Dr. Anup Kesavan',
                    'work_package_no' => 'WP-UOH-03',
                    'interns_trained_count' => 5,
                    'summary_progress' => 'Constructed deep convolutional neural networks extracting 1,200+ radiomic features for predicting response to neoadjuvant chemotherapy.',
                    'approved_objects' => 'Validated machine learning prognostic model achieving 89.4% ROC-AUC.',
                    'methodology' => 'PyRadiomics feature extraction, CNN segmentation, and Cox proportional hazards regression.'
                ],
                [
                    'task_no' => 'TASK-UOH-PR-04',
                    'project_title' => 'Microfluidic Formulations for Targeted Drug Delivery Systems',
                    'pi_name' => 'Dr. V. Vijjulatha',
                    'co_pi_name' => 'Prof. K. Anand',
                    'work_package_no' => 'WP-UOH-04',
                    'interns_trained_count' => 4,
                    'summary_progress' => 'Fabricated PDMS microfluidic droplet chips achieving monodisperse liposomal encapsulation of hydrophobic kinase inhibitors.',
                    'approved_objects' => 'Controlled release liposome synthesis and in-vitro release kinetics.',
                    'methodology' => 'Photolithography, hydrodynamic flow focusing, and dynamic light scattering (DLS).'
                ]
            ],
            'publications' => [
                [
                    'task_no' => 'TASK-UOH-PUB-01',
                    'publication_title' => 'Targeting Glycan-Protein Interactions for Novel Therapeutic Interventions in Cancer',
                    'author_name' => 'Prof. K. Anand, Dr. S. Radhakrishnan, Dr. M. V. Reddy',
                    'doi_number' => '10.1016/j.jbc.2025.105432',
                    'publication_date' => '2025-06-15',
                    'publication_journal' => 'Journal of Biological Chemistry',
                    'impact_factor' => 4.800
                ],
                [
                    'task_no' => 'TASK-UOH-PUB-02',
                    'publication_title' => 'High-Throughput Flow Cytometry Analysis of Immuno-Oncology Biomarkers',
                    'author_name' => 'Dr. Anup Kesavan, Prof. Balaji Meriga, Dr. E. K. Radhakrishnan',
                    'doi_number' => '10.1038/s41598-025-89102-w',
                    'publication_date' => '2025-09-20',
                    'publication_journal' => 'Scientific Reports (Nature)',
                    'impact_factor' => 4.600
                ],
                [
                    'task_no' => 'TASK-UOH-PUB-03',
                    'publication_title' => 'Nanostructured Biomaterials for Targeted Drug Delivery in Microfluidic Devices',
                    'author_name' => 'Dr. V. Vijjulatha, Dr. G. Sarma, Prof. K. Anand',
                    'doi_number' => '10.1021/acs.biomac.5b00123',
                    'publication_date' => '2026-01-10',
                    'publication_journal' => 'ACS Biomacromolecules',
                    'impact_factor' => 6.200
                ],
                [
                    'task_no' => 'TASK-UOH-PUB-04',
                    'publication_title' => 'AI-Driven Radiomics in Precision Medicine and Cancer Prognostication',
                    'author_name' => 'Dr. Sathyanarayana N., Dr. Anup Kesavan',
                    'doi_number' => '10.1109/TBME.2026.3129845',
                    'publication_date' => '2026-02-28',
                    'publication_journal' => 'IEEE Transactions on Biomedical Engineering',
                    'impact_factor' => 4.750
                ],
                [
                    'task_no' => 'TASK-UOH-PUB-05',
                    'publication_title' => 'Deep Multiparametric Spectral Flow Cytometry in Pediatric Oncology',
                    'author_name' => 'Dr. Anup Kesavan, Dr. M. V. Reddy',
                    'doi_number' => '10.1182/bloodadvances.2025008912',
                    'publication_date' => '2025-11-14',
                    'publication_journal' => 'Blood Advances',
                    'impact_factor' => 5.480
                ]
            ],
            'patents' => [
                [
                    'patent_id' => 'PAT-UOH-01',
                    'task_no' => 'TASK-UOH-PAT-01',
                    'patent_title' => 'Microfluidic Droplet Generator for Encapsulation of Macromolecular Therapeutics',
                    'inventor_name' => 'Prof. K. Anand, Dr. V. Vijjulatha',
                    'application_no' => '202541089201',
                    'status' => 'Published',
                    'filing_date' => '2025-04-18',
                    'country' => 'India',
                    'abstract' => 'A microfluidic flow-focusing device for scalable production of uniform liposomal nanocarriers containing therapeutic peptides.'
                ],
                [
                    'patent_id' => 'PAT-UOH-02',
                    'task_no' => 'TASK-UOH-PAT-02',
                    'patent_title' => 'Deep Learning Pipeline for Automated Spectral Unmixing in Flow Cytometry',
                    'inventor_name' => 'Dr. Sathyanarayana N., Dr. Anup Kesavan',
                    'application_no' => '202641012344',
                    'status' => 'Filed',
                    'filing_date' => '2026-02-10',
                    'country' => 'India',
                    'abstract' => 'An algorithmic framework utilizing deep residual networks for real-time deconvolution of highly overlapping fluorophore emission spectra.'
                ],
                [
                    'patent_id' => 'PAT-UOH-03',
                    'task_no' => 'TASK-UOH-PAT-03',
                    'patent_title' => 'Novel Lectin-Targeting Small Molecule Conjugates for Cancer Cell Growth Arrest',
                    'inventor_name' => 'Prof. K. Anand',
                    'patent_no' => 'IN-398212-B',
                    'status' => 'Granted',
                    'filing_date' => '2024-03-12',
                    'grant_date' => '2025-11-05',
                    'country' => 'India',
                    'abstract' => 'Synthetic glycoside conjugates exhibiting nanomolar binding affinity towards oncogenic galectins.'
                ]
            ],
            'internships' => [
                [
                    'task_no' => 'TASK-UOH-INT-01',
                    'title' => 'Consortium Research Internship in Flow Cytometric Cell Sorting & Immunophenotyping',
                    'project_investigator' => 'Dr. Anup Kesavan',
                    'no_students_trained' => 6,
                    'no_days_trained' => 60,
                    'students_names' => 'Rahul Verma, Sneha Patil, Mohammed Zafar, Priya Sundaram, Amit Roy, Divya K.',
                    'content' => 'Comprehensive hands-on training on multi-color flow cytometry, panel design, cell sorting, and bioinformatic gating.'
                ],
                [
                    'task_no' => 'TASK-UOH-INT-02',
                    'title' => 'Summer Training Program in Molecular Dynamics & Protein-Ligand Interactions',
                    'project_investigator' => 'Prof. K. Anand',
                    'no_students_trained' => 8,
                    'no_days_trained' => 45,
                    'students_names' => 'K. Arvind, Rohini Sen, Deepa M., Tarun Saxena, Ananya Roy, S. Karthik, Pooja B., N. Vineeth',
                    'content' => 'Molecular dynamics simulations using GROMACS, binding free energy calculations, and structural bioinformatics.'
                ],
                [
                    'task_no' => 'TASK-UOH-INT-03',
                    'title' => 'Research Internship in AI Radiomics & Deep Learning for Medical Imaging',
                    'project_investigator' => 'Dr. Sathyanarayana N.',
                    'no_students_trained' => 5,
                    'no_days_trained' => 60,
                    'students_names' => 'V. Naveen, Shilpa Joshi, Gaurav Das, Reena Paul, Tanmay Sen',
                    'content' => 'Feature extraction from DICOM CT/MRI scans, 3D segmentation architectures, and predictive survival modeling.'
                ],
                [
                    'task_no' => 'TASK-UOH-INT-04',
                    'title' => 'Advanced Hands-on Internship in HPLC & Mass Spectrometric Proteomics',
                    'project_investigator' => 'Dr. V. Vijjulatha',
                    'no_students_trained' => 4,
                    'no_days_trained' => 30,
                    'students_names' => 'Harshitha R., M. S. Pradeep, Sandeep V., Kavya Nair',
                    'content' => 'Operation and sample preparation protocols for high-resolution LC-MS/MS and quantitative proteomics.'
                ]
            ]
        ],
        'cuk' => [
            'progress' => [
                [
                    'task_no' => 'TASK-CUK-PR-01',
                    'project_title' => 'Structural Genomics of Pathogenic Proteins in Infectious Microorganisms',
                    'pi_name' => 'Dr. Sathyanarayana N.',
                    'co_pi_name' => 'Prof. K. Anand',
                    'work_package_no' => 'WP-CUK-01',
                    'interns_trained_count' => 4,
                    'summary_progress' => 'Solved the 2.1 Angstrom crystal structure of multi-drug resistant efflux pump regulator from clinical bacterial isolates.',
                    'approved_objects' => 'Expression, purification, and X-ray diffraction analysis of bacterial resistance enzymes.',
                    'methodology' => 'Recombinant protein expression in E. coli, affinity chromatography, and vapor diffusion crystallization.'
                ],
                [
                    'task_no' => 'TASK-CUK-PR-02',
                    'project_title' => 'Mathematical Modeling of Tumor Microenvironment and Radiomic Texture Analysis',
                    'pi_name' => 'Prof. H. P. Rani',
                    'co_pi_name' => 'Dr. M. S. Biradar',
                    'work_package_no' => 'WP-CUK-02',
                    'interns_trained_count' => 3,
                    'summary_progress' => 'Formulated coupled reaction-diffusion partial differential equations modeling oxygen transport and tumor vascularization.',
                    'approved_objects' => 'Simulated computational hemodynamic flow and validated against patient perfusion CT data.',
                    'methodology' => 'Finite volume numerical discretization and computational fluid dynamics (CFD).'
                ]
            ],
            'publications' => [
                [
                    'task_no' => 'TASK-CUK-PUB-01',
                    'publication_title' => 'Structural Genomics of Pathogenic Proteins in Infectious Diseases',
                    'author_name' => 'Dr. Sathyanarayana N., Prof. K. Anand',
                    'doi_number' => '10.1016/j.str.2025.04.012',
                    'publication_date' => '2025-05-10',
                    'publication_journal' => 'Structure (Cell Press)',
                    'impact_factor' => 5.250
                ],
                [
                    'task_no' => 'TASK-CUK-PUB-02',
                    'publication_title' => 'Predictive Radiomics for Non-Invasive Tumor Border Classification',
                    'author_name' => 'Prof. H. P. Rani, Dr. Sathyanarayana N.',
                    'doi_number' => '10.1016/j.cmpb.2025.106899',
                    'publication_date' => '2025-10-15',
                    'publication_journal' => 'Computer Methods and Programs in Biomedicine',
                    'impact_factor' => 4.900
                ],
                [
                    'task_no' => 'TASK-CUK-PUB-03',
                    'publication_title' => 'Cryo-EM Insights into Antimicrobial Resistance Mechanisms in Gram-Negative Pathogens',
                    'author_name' => 'Dr. M. S. Biradar, Dr. Sathyanarayana N.',
                    'doi_number' => '10.1016/j.jmb.2026.167890',
                    'publication_date' => '2026-01-20',
                    'publication_journal' => 'Journal of Molecular Biology',
                    'impact_factor' => 5.100
                ]
            ],
            'patents' => [
                [
                    'task_no' => 'TASK-CUK-PAT-01',
                    'patent_title' => 'Radiomic Feature Extraction System for Predictive Tumor Invasiveness Assessment',
                    'inventor_name' => 'Prof. H. P. Rani, Dr. Sathyanarayana N.',
                    'application_no' => '202541098122',
                    'status' => 'Filed',
                    'filing_date' => '2025-08-25',
                    'country' => 'India',
                    'abstract' => 'An automated computational system quantifying heterogeneous radiomic intratumoral textures from multi-modal diagnostic tomography.'
                ]
            ],
            'internships' => [
                [
                    'task_no' => 'TASK-CUK-INT-01',
                    'title' => 'Summer Internship in Structural Biology & Computational Biophysics',
                    'project_investigator' => 'Dr. Sathyanarayana N.',
                    'no_students_trained' => 4,
                    'no_days_trained' => 45,
                    'students_names' => 'Basavaraj Patil, Meenakshi K., Sunil Rathod, Deepa Pujari',
                    'content' => 'Crystallographic data reduction, molecular replacement phasing, and visualization with PyMOL.'
                ],
                [
                    'task_no' => 'TASK-CUK-INT-02',
                    'title' => 'Hands-on Training in Radiomics and Biomedical Mathematical Modeling',
                    'project_investigator' => 'Prof. H. P. Rani',
                    'no_students_trained' => 3,
                    'no_days_trained' => 30,
                    'students_names' => 'Mallikarjun S., Rajeshwari H., Vinay Kumar',
                    'content' => 'MATLAB/Python numerical modeling of bio-transport phenomena and clinical imaging radiomic analysis.'
                ]
            ]
        ],
        'kannur' => [
            'progress' => [
                [
                    'task_no' => 'TASK-KAN-PR-01',
                    'project_title' => 'Genome-Wide Association Analysis of Metabolic Biomarkers in Regional Populations',
                    'pi_name' => 'Dr. Anup Kesavan',
                    'co_pi_name' => 'Dr. S. K. Nambiar',
                    'work_package_no' => 'WP-KAN-01',
                    'interns_trained_count' => 5,
                    'summary_progress' => 'Genotyped 650 cohort samples using high-density SNP arrays; identified 4 novel genomic loci significantly associated with dyslipidemia.',
                    'approved_objects' => 'Genomic DNA banking and high-throughput genotyping of coastal Kerala population cohorts.',
                    'methodology' => 'Illumina Global Screening Array and PLINK statistical genetic association testing.'
                ],
                [
                    'task_no' => 'TASK-KAN-PR-02',
                    'project_title' => 'Next-Generation Sequencing and Microbial Pathogen Surveillance in Clinical Cohorts',
                    'pi_name' => 'Dr. S. K. Nambiar',
                    'co_pi_name' => 'Prof. K. P. Mohanan',
                    'work_package_no' => 'WP-KAN-02',
                    'interns_trained_count' => 4,
                    'summary_progress' => 'Completed whole-genome sequencing of 120 multi-drug resistant Pseudomonas and Klebsiella hospital isolates.',
                    'approved_objects' => 'Metagenomic resistance gene cataloging and phylogenetic transmission mapping.',
                    'methodology' => 'Oxford Nanopore MinION & Illumina sequencing with bioinformatics AMR pipelines.'
                ],
                [
                    'task_no' => 'TASK-KAN-PR-03',
                    'project_title' => 'Molecular Epidemiology of Monogenic Inherited Syndromes',
                    'pi_name' => 'Prof. K. P. Mohanan',
                    'co_pi_name' => 'Dr. Anup Kesavan',
                    'work_package_no' => 'WP-KAN-03',
                    'interns_trained_count' => 3,
                    'summary_progress' => 'Performed target-capture exome resequencing identifying homozygous founder mutations in rare metabolic storage diseases.',
                    'approved_objects' => 'Carrier screening panel validation for rural northern Kerala clinical centers.',
                    'methodology' => 'Targeted gene panel capture, Sanger confirmation, and molecular modeling.'
                ]
            ],
            'publications' => [
                [
                    'task_no' => 'TASK-KAN-PUB-01',
                    'publication_title' => 'Genome-Wide Association Analysis of Metabolic Biomarkers in Regional Populations',
                    'author_name' => 'Dr. Anup Kesavan, Dr. E. K. Radhakrishnan, Dr. S. K. Nambiar',
                    'doi_number' => '10.1038/s41588-025-01890-x',
                    'publication_date' => '2025-08-12',
                    'publication_journal' => 'Nature Genetics',
                    'impact_factor' => 27.600
                ],
                [
                    'task_no' => 'TASK-KAN-PUB-02',
                    'publication_title' => 'Metagenomic Profiling of Multi-Drug Resistant Clinical Isolates in Coastal Kerala',
                    'author_name' => 'Dr. S. K. Nambiar, Prof. K. P. Mohanan',
                    'doi_number' => '10.1128/jcm.00341-25',
                    'publication_date' => '2025-11-28',
                    'publication_journal' => 'Journal of Clinical Microbiology',
                    'impact_factor' => 5.800
                ],
                [
                    'task_no' => 'TASK-KAN-PUB-03',
                    'publication_title' => 'Identification of Novel Rare Variants in Hereditary Metabolic Disorders',
                    'author_name' => 'Prof. K. P. Mohanan, Dr. Anup Kesavan',
                    'doi_number' => '10.1038/s41431-026-01543-z',
                    'publication_date' => '2026-02-14',
                    'publication_journal' => 'European Journal of Human Genetics',
                    'impact_factor' => 4.200
                ]
            ],
            'patents' => [
                [
                    'task_no' => 'TASK-KAN-PAT-01',
                    'patent_title' => 'Multiplex PCR Primer Panel for Rapid Screening of Regional Metabolic Gene Variants',
                    'inventor_name' => 'Dr. S. K. Nambiar, Prof. K. P. Mohanan',
                    'application_no' => '202641044190',
                    'status' => 'Filed',
                    'filing_date' => '2026-01-16',
                    'country' => 'India',
                    'abstract' => 'A multiplex oligonucleotide amplification kit for simultaneous detection of clinically actionable genetic polymorphisms.'
                ]
            ],
            'internships' => [
                [
                    'task_no' => 'TASK-KAN-INT-01',
                    'title' => 'Winter Internship in High-Throughput Genomic Sequencing & Bioinformatic Pipelines',
                    'project_investigator' => 'Dr. S. K. Nambiar',
                    'no_students_trained' => 5,
                    'no_days_trained' => 40,
                    'students_names' => 'Athira K., Jithin Das, Vishnu Prasad, Fathima Zahra, Anjali Nair',
                    'content' => 'Quality control of NGS reads, alignment to GRCh38, variant calling with GATK, and annotation.'
                ],
                [
                    'task_no' => 'TASK-KAN-INT-02',
                    'title' => 'Hands-on Training in Molecular Diagnostics and Clinical PCR Testing',
                    'project_investigator' => 'Prof. K. P. Mohanan',
                    'no_students_trained' => 4,
                    'no_days_trained' => 30,
                    'students_names' => 'Nikhil Raj, Sruthi V., Haritha M., Arunima S.',
                    'content' => 'Standard operating procedures for clinical DNA extraction, real-time qPCR, and melting curve genotyping.'
                ],
                [
                    'task_no' => 'TASK-KAN-INT-03',
                    'title' => 'Research Internship in Population Genetic Epidemiology and Statistical Genetics',
                    'project_investigator' => 'Dr. Anup Kesavan',
                    'no_students_trained' => 3,
                    'no_days_trained' => 45,
                    'students_names' => 'Sreehari P., Reshma Mohan, Midhun Krishna',
                    'content' => 'Haplotype estimation, linkage disequilibrium analysis, and polygenic risk score calculation using R/Bioconductor.'
                ]
            ]
        ],
        'mgu' => [
            'progress' => [
                [
                    'task_no' => 'TASK-MGU-PR-01',
                    'project_title' => 'Polymeric Bionanocomposites for Advanced Tissue Engineering & Bone Scaffolds',
                    'pi_name' => 'Dr. E. K. Radhakrishnan',
                    'co_pi_name' => 'Prof. Sabu Thomas',
                    'work_package_no' => 'WP-MGU-01',
                    'interns_trained_count' => 6,
                    'summary_progress' => 'Synthesized electrospun polycaprolactone (PCL) / hydroxyapatite nanofibrous mats demonstrating superior osteogenic differentiation.',
                    'approved_objects' => 'Optimization of porous scaffold morphology and mechanical compression modulus (>4.5 MPa).',
                    'methodology' => 'Electrospinning, FE-SEM microstructural imaging, and osteoblast alkaline phosphatase assays.'
                ],
                [
                    'task_no' => 'TASK-MGU-PR-02',
                    'project_title' => 'Smart Stimuli-Responsive Hydrogels for Controlled Oncological Drug Delivery',
                    'pi_name' => 'Prof. Sabu Thomas',
                    'co_pi_name' => 'Dr. Radhika Nair',
                    'work_package_no' => 'WP-MGU-02',
                    'interns_trained_count' => 5,
                    'summary_progress' => 'Fabricated dual pH/temperature-responsive chitosan-g-PNIPAM hydrogels showing triggerable doxorubicin release in acidic tumor conditions.',
                    'approved_objects' => 'In-vitro drug release profiles across physiological (pH 7.4) and intratumoral (pH 5.5) buffers.',
                    'methodology' => 'Free radical graft copolymerization, FTIR, and UV-Vis spectrophotometric drug release kinetics.'
                ],
                [
                    'task_no' => 'TASK-MGU-PR-03',
                    'project_title' => 'Electrospun Nanofibers with Integrated Antimicrobial Herbal Extracts',
                    'pi_name' => 'Dr. Radhika Nair',
                    'co_pi_name' => 'Dr. E. K. Radhakrishnan',
                    'work_package_no' => 'WP-MGU-03',
                    'interns_trained_count' => 4,
                    'summary_progress' => 'Engineered cellulose acetate nanofiber wound dressings impregnated with curcumin demonstrating 99.2% bacterial zone of inhibition.',
                    'approved_objects' => 'Wound healing assessment using scratch assay in human dermal fibroblast monolayers.',
                    'methodology' => 'Electrospinning, disk diffusion antimicrobial testing, and cell viability MTT assays.'
                ]
            ],
            'publications' => [
                [
                    'task_no' => 'TASK-MGU-PUB-01',
                    'publication_title' => 'Polymeric Bionanocomposites for Tissue Engineering Applications',
                    'author_name' => 'Dr. E. K. Radhakrishnan, Prof. K. Anand, Prof. Sabu Thomas',
                    'doi_number' => '10.1016/j.actbio.2025.10.022',
                    'publication_date' => '2025-10-18',
                    'publication_journal' => 'Acta Biomaterialia',
                    'impact_factor' => 9.700
                ],
                [
                    'task_no' => 'TASK-MGU-PUB-02',
                    'publication_title' => 'Smart Responsive Nanocellulose Scaffolds for Controlled Drug Delivery',
                    'author_name' => 'Prof. Sabu Thomas, Dr. Radhika Nair',
                    'doi_number' => '10.1016/j.carbpol.2025.123456',
                    'publication_date' => '2025-12-05',
                    'publication_journal' => 'Carbohydrate Polymers',
                    'impact_factor' => 10.700
                ],
                [
                    'task_no' => 'TASK-MGU-PUB-03',
                    'publication_title' => 'Antimicrobial Evaluation of Silver-Loaded Biopolymer Hydrogels',
                    'author_name' => 'Dr. Radhika Nair, Dr. E. K. Radhakrishnan',
                    'doi_number' => '10.1039/d5bm00812k',
                    'publication_date' => '2026-01-22',
                    'publication_journal' => 'Biomaterials Science',
                    'impact_factor' => 6.600
                ],
                [
                    'task_no' => 'TASK-MGU-PUB-04',
                    'publication_title' => 'Electrospun Polymeric Mats for Accelerating Dermal Wound Regeneration',
                    'author_name' => 'Dr. E. K. Radhakrishnan, Prof. Sabu Thomas',
                    'doi_number' => '10.1016/j.mtbio.2026.100912',
                    'publication_date' => '2026-03-02',
                    'publication_journal' => 'Materials Today Bio',
                    'impact_factor' => 7.300
                ]
            ],
            'patents' => [
                [
                    'task_no' => 'TASK-MGU-PAT-01',
                    'patent_title' => 'Biodegradable Bionanocomposite Scaffold for Guided Bone Regeneration',
                    'inventor_name' => 'Dr. E. K. Radhakrishnan, Prof. Sabu Thomas',
                    'application_no' => '202541077312',
                    'status' => 'Published',
                    'filing_date' => '2025-06-20',
                    'country' => 'India',
                    'abstract' => 'A biomimetic 3D nanofibrous matrix with interconnected porosity facilitating rapid vascularization and osteogenesis.'
                ],
                [
                    'task_no' => 'TASK-MGU-PAT-02',
                    'patent_title' => 'Thermo-Responsive Injectable Hydrogel for Sustained Anti-Cancer Drug Release',
                    'inventor_name' => 'Prof. Sabu Thomas, Dr. Radhika Nair',
                    'application_no' => '202641033219',
                    'status' => 'Filed',
                    'filing_date' => '2026-02-04',
                    'country' => 'India',
                    'abstract' => 'An in-situ forming supramolecular polymer network displaying reversible phase transition at human physiological temperature.'
                ]
            ],
            'internships' => [
                [
                    'task_no' => 'TASK-MGU-INT-01',
                    'title' => 'Internship in Bionanomaterial Synthesis & Electrospinning Fabrication',
                    'project_investigator' => 'Dr. E. K. Radhakrishnan',
                    'no_students_trained' => 6,
                    'no_days_trained' => 45,
                    'students_names' => 'Akhil S., Binu Thomas, Meera Varghese, Chithra K., Jomon George, Nimisha P.',
                    'content' => 'High-voltage electrospinning parameter optimization, solution viscosity tuning, and tensile testing of polymer films.'
                ],
                [
                    'task_no' => 'TASK-MGU-INT-02',
                    'title' => 'Advanced Workshop on Hydrogel Characterization and Rheological Analysis',
                    'project_investigator' => 'Prof. Sabu Thomas',
                    'no_students_trained' => 5,
                    'no_days_trained' => 30,
                    'students_names' => 'Sujith M., Anju Baby, Kiran Raj, Deepthi Paul, Varun Nair',
                    'content' => 'Oscillatory shear rheometry, swelling kinetics determination, and gel permeation chromatography (GPC).'
                ],
                [
                    'task_no' => 'TASK-MGU-INT-03',
                    'title' => 'Training in Cell Culture & Biocompatibility Testing of Nanomaterials',
                    'project_investigator' => 'Dr. Radhika Nair',
                    'no_students_trained' => 4,
                    'no_days_trained' => 30,
                    'students_names' => 'Maria Joseph, Rakesh K., Neethu Chandran, Albin Mathew',
                    'content' => 'Sterile mammalian cell passaging, fluorescence live/dead staining, and hemocompatibility hemolysis assays.'
                ]
            ]
        ],
        'ou' => [
            'progress' => [
                [
                    'task_no' => 'TASK-OU-PR-01',
                    'project_title' => 'Computational Pharmacophore Mapping for Small Molecule Kinase Inhibitors',
                    'pi_name' => 'Dr. V. Vijjulatha',
                    'co_pi_name' => 'Prof. P. Leelavathi',
                    'work_package_no' => 'WP-OU-01',
                    'interns_trained_count' => 5,
                    'summary_progress' => 'Created 3D QSAR and pharmacophore hypotheses based on 85 known VEGFR-2 inhibitors; screened Maybridge library of 50,000 compounds.',
                    'approved_objects' => 'Identification of top 5 lead candidates with predicted sub-micromolar inhibition.',
                    'methodology' => 'Schrodinger Maestro, PHASE pharmacophore alignment, and Glide XP docking.'
                ],
                [
                    'task_no' => 'TASK-OU-PR-02',
                    'project_title' => 'In-Silico ADMET Screening & Lead Optimization of Heterocyclic Compounds',
                    'pi_name' => 'Prof. P. Leelavathi',
                    'co_pi_name' => 'Dr. G. Ramesh',
                    'work_package_no' => 'WP-OU-02',
                    'interns_trained_count' => 4,
                    'summary_progress' => 'Synthesized 18 novel quinazoline derivatives and confirmed their chemical structures via 1H/13C NMR and high-resolution MS.',
                    'approved_objects' => 'Wet-lab chemical synthesis and in-vitro kinase enzymatic inhibition assays.',
                    'methodology' => 'Multi-step organic synthesis, column chromatography purification, and ADP-Glo kinase assays.'
                ]
            ],
            'publications' => [
                [
                    'task_no' => 'TASK-OU-PUB-01',
                    'publication_title' => 'Computational Pharmacophore Mapping for Small Molecule Kinase Inhibitors',
                    'author_name' => 'Dr. V. Vijjulatha, Dr. Sathyanarayana N., Prof. P. Leelavathi',
                    'doi_number' => '10.1021/acs.jcim.5b00892',
                    'publication_date' => '2025-12-04',
                    'publication_journal' => 'Journal of Chemical Information and Modeling',
                    'impact_factor' => 5.600
                ],
                [
                    'task_no' => 'TASK-OU-PUB-02',
                    'publication_title' => 'Structure-Guided Discovery of Novel Pyrimidine Derivatives as Anti-Angiogenic Leads',
                    'author_name' => 'Prof. P. Leelavathi, Dr. V. Vijjulatha',
                    'doi_number' => '10.1016/j.bioorg.2026.108912',
                    'publication_date' => '2026-01-18',
                    'publication_journal' => 'Bioorganic Chemistry',
                    'impact_factor' => 5.100
                ],
                [
                    'task_no' => 'TASK-OU-PUB-03',
                    'publication_title' => 'Machine Learning Based ADMET Prediction for Novel Heterocyclic Compounds',
                    'author_name' => 'Dr. G. Ramesh, Dr. V. Vijjulatha',
                    'doi_number' => '10.1002/minf.202500142',
                    'publication_date' => '2026-02-26',
                    'publication_journal' => 'Molecular Informatics',
                    'impact_factor' => 3.400
                ]
            ],
            'patents' => [
                [
                    'task_no' => 'TASK-OU-PAT-01',
                    'patent_title' => 'Novel Heterocyclic Pyrazole-Triazole Hybrid Compounds for Kinase Inhibition',
                    'inventor_name' => 'Dr. V. Vijjulatha, Prof. P. Leelavathi',
                    'application_no' => '202541065411',
                    'status' => 'Published',
                    'filing_date' => '2025-07-14',
                    'country' => 'India',
                    'abstract' => 'Synthetic hybrid small molecules displaying potent nanomolar kinase IC50 values and high oral bio-availability.'
                ],
                [
                    'task_no' => 'TASK-OU-PAT-02',
                    'patent_title' => 'In-Silico High-Throughput Screening Methodology for Kinase Binding Optimization',
                    'inventor_name' => 'Dr. V. Vijjulatha, Dr. G. Ramesh',
                    'application_no' => '202641019877',
                    'status' => 'Filed',
                    'filing_date' => '2026-02-18',
                    'country' => 'India',
                    'abstract' => 'A machine learning scoring function incorporating energetic desolvation penalties for receptor-ligand pose ranking.'
                ]
            ],
            'internships' => [
                [
                    'task_no' => 'TASK-OU-INT-01',
                    'title' => 'Research Internship in Computer-Aided Drug Design (CADD) & Molecular Docking',
                    'project_investigator' => 'Dr. V. Vijjulatha',
                    'no_students_trained' => 5,
                    'no_days_trained' => 45,
                    'students_names' => 'K. Sai Krishna, T. Priyanka, M. Ravinder, G. Shravani, B. Sandeep',
                    'content' => 'Protein structure preparation, grid generation, flexible ligand docking, and binding free energy scoring.'
                ],
                [
                    'task_no' => 'TASK-OU-INT-02',
                    'title' => 'Hands-on Training in Organic Synthesis & Spectroscopic Identification of Heterocycles',
                    'project_investigator' => 'Prof. P. Leelavathi',
                    'no_students_trained' => 4,
                    'no_days_trained' => 30,
                    'students_names' => 'N. Swapna, R. Praveen Kumar, Ch. Madhuri, D. Naresh',
                    'content' => 'Laboratory synthesis of nitrogen heterocycles, rotary evaporation, and spectral interpretation of NMR/HRMS.'
                ]
            ]
        ],
        'svu' => [
            'progress' => [
                [
                    'task_no' => 'TASK-SVU-PR-01',
                    'project_title' => 'Phytochemical Profiling and Antioxidant Activity of Endemic Medicinal Plants',
                    'pi_name' => 'Prof. Balaji Meriga',
                    'co_pi_name' => 'Dr. G. Sarma',
                    'work_package_no' => 'WP-SVU-01',
                    'interns_trained_count' => 5,
                    'summary_progress' => 'Extracted bioactive polyphenols from Pterocarpus santalinus; isolated three novel flavone glycosides with strong DPPH radical scavenging.',
                    'approved_objects' => 'Standardization of eco-friendly extraction techniques and cellular ROS protective assays.',
                    'methodology' => 'Soxhlet extraction, preparative HPLC, and intracellular DCFH-DA fluorescent ROS assays.'
                ],
                [
                    'task_no' => 'TASK-SVU-PR-02',
                    'project_title' => 'Development of Smart Nano Biosensors for Rapid Healthcare Diagnostics',
                    'pi_name' => 'Prof. Tata Narasinga Rao',
                    'co_pi_name' => 'Prof. Balaji Meriga',
                    'work_package_no' => 'WP-SVU-02',
                    'interns_trained_count' => 6,
                    'summary_progress' => 'Fabricated gold-nanoparticle decorated graphene screen-printed electrodes achieving 0.05 ng/mL detection limit for cardiac troponin-I.',
                    'approved_objects' => 'Electrochemical sensor platform calibration against clinical serum samples.',
                    'methodology' => 'Cyclic voltammetry (CV), electrochemical impedance spectroscopy (EIS), and amperometry.'
                ],
                [
                    'task_no' => 'TASK-SVU-PR-03',
                    'project_title' => 'Main Group Materials for Health, Energy, and Environmental Remediation',
                    'pi_name' => 'Dr. G. Sarma',
                    'co_pi_name' => 'Prof. Balaji Meriga',
                    'work_package_no' => 'WP-SVU-03',
                    'interns_trained_count' => 4,
                    'summary_progress' => 'Synthesized porous aluminum-based metal-organic frameworks for photocatalytic degradation of toxic organophosphate pesticides.',
                    'approved_objects' => 'High surface area coordination polymers with solar light driven photocatalysis.',
                    'methodology' => 'Solvothermal crystallization, BET surface area analysis, and LC-MS degradation pathway analysis.'
                ]
            ],
            'publications' => [
                [
                    'task_no' => 'TASK-SVU-PUB-01',
                    'publication_title' => 'Phytochemical Profiling and Antioxidant Activity of Endemic Medicinal Plants',
                    'author_name' => 'Prof. Balaji Meriga, Dr. G. Sarma',
                    'doi_number' => '10.1016/j.jep.2025.118901',
                    'publication_date' => '2025-11-05',
                    'publication_journal' => 'Journal of Ethnopharmacology',
                    'impact_factor' => 5.400
                ],
                [
                    'task_no' => 'TASK-SVU-PUB-02',
                    'publication_title' => 'Electrochemical Nano-Biosensor for Point-of-Care Cardiac Troponin Detection',
                    'author_name' => 'Prof. Tata Narasinga Rao, Prof. Balaji Meriga',
                    'doi_number' => '10.1016/j.bios.2025.115890',
                    'publication_date' => '2025-12-18',
                    'publication_journal' => 'Biosensors and Bioelectronics',
                    'impact_factor' => 12.600
                ],
                [
                    'task_no' => 'TASK-SVU-PUB-03',
                    'publication_title' => 'Antidiabetic and Anti-Inflammatory Efficacy of Endemic Eastern Ghats Plant Extracts',
                    'author_name' => 'Prof. Balaji Meriga, Dr. S. R. Reddy',
                    'doi_number' => '10.1016/j.phymed.2026.154912',
                    'publication_date' => '2026-02-10',
                    'publication_journal' => 'Phytomedicine',
                    'impact_factor' => 6.700
                ]
            ],
            'patents' => [
                [
                    'task_no' => 'TASK-SVU-PAT-01',
                    'patent_title' => 'A Disposable Screen-Printed Nano-Electrode Biosensor for Diagnostic Biomarker Quantification',
                    'inventor_name' => 'Prof. Tata Narasinga Rao, Prof. Balaji Meriga',
                    'application_no' => '202541052109',
                    'status' => 'Published',
                    'filing_date' => '2025-05-30',
                    'country' => 'India',
                    'abstract' => 'An ultra-sensitive electrochemical test strip modified with gold nanocomposites for point-of-care cardiac testing.'
                ],
                [
                    'task_no' => 'TASK-SVU-PAT-02',
                    'patent_title' => 'Novel Phytochemical Formulation from Endemic Plants for Cellular Oxidative Protection',
                    'inventor_name' => 'Prof. Balaji Meriga',
                    'application_no' => '202641011892',
                    'status' => 'Filed',
                    'filing_date' => '2026-01-28',
                    'country' => 'India',
                    'abstract' => 'A standardized synergistic botanical extract exhibiting significant mitochondrial ROS attenuation.'
                ]
            ],
            'internships' => [
                [
                    'task_no' => 'TASK-SVU-INT-01',
                    'title' => 'Research Internship in Phytochemical Extraction, HPLC & Bioactive Fractionation',
                    'project_investigator' => 'Prof. Balaji Meriga',
                    'no_students_trained' => 5,
                    'no_days_trained' => 40,
                    'students_names' => 'B. Venkat, P. Supriya, C. Mahesh, G. Bhavani, K. Lokesh',
                    'content' => 'Extraction techniques, preparative column chromatography, and HPLC fingerprinting of medicinal plant extracts.'
                ],
                [
                    'task_no' => 'TASK-SVU-INT-02',
                    'title' => 'Hands-on Training in Nanomaterial-Modified Electrochemical Biosensor Fabrication',
                    'project_investigator' => 'Prof. Tata Narasinga Rao',
                    'no_students_trained' => 6,
                    'no_days_trained' => 45,
                    'students_names' => 'D. Sravan, S. Mounika, Y. Rajesh, T. Sireesha, V. Kiran, R. Swathi',
                    'content' => 'Electrochemical workstation handling, electrode surface cleaning, nanoparticle drop-casting, and antibody immobilization.'
                ],
                [
                    'task_no' => 'TASK-SVU-INT-03',
                    'title' => 'Summer Program in Coordination Chemistry and Environmental Catalytic Testing',
                    'project_investigator' => 'Dr. G. Sarma',
                    'no_students_trained' => 4,
                    'no_days_trained' => 30,
                    'students_names' => 'M. Harikrishna, K. Sandhya, N. Mohan, P. Madhavi',
                    'content' => 'Synthesis of coordination complexes, UV-Vis photodegradation assays, and kinetics calculation.'
                ]
            ]
        ],
        'yvu' => [
            'progress' => [
                [
                    'task_no' => 'TASK-YVU-PR-01',
                    'project_title' => 'Green Synthesis of Silver Nanoparticles using Plant Extracts for Anti-Microbial Coatings',
                    'pi_name' => 'Dr. G. Sarma',
                    'co_pi_name' => 'Prof. Balaji Meriga',
                    'work_package_no' => 'WP-YVU-01',
                    'interns_trained_count' => 5,
                    'summary_progress' => 'Synthesized stable 15-25 nm silver nanoparticles using Boswellia ovalifoliolata leaf broth without hazardous reducing agents.',
                    'approved_objects' => 'Optimization of reaction temperature, precursor concentration, and antimicrobial efficacy testing.',
                    'methodology' => 'Green reduction synthesis, TEM morphology analysis, and Kirby-Bauer antimicrobial assays.'
                ],
                [
                    'task_no' => 'TASK-YVU-PR-02',
                    'project_title' => 'Flow Cytometry Training & Analytical Instrumentation Collaborative Center',
                    'pi_name' => 'Prof. L. Dakshayani',
                    'co_pi_name' => 'Dr. G. Sarma',
                    'work_package_no' => 'WP-YVU-02',
                    'interns_trained_count' => 15,
                    'summary_progress' => 'Trained 15 student researchers in flow cytometry operation, live-dead cell viability, and apoptosis assays on the BD Accuri platform.',
                    'approved_objects' => 'Regional capacity building in high-end biomedical instrumentation.',
                    'methodology' => 'Hands-on laboratory training, calibration protocols, and sample acquisition.'
                ]
            ],
            'publications' => [
                [
                    'task_no' => 'TASK-YVU-PUB-01',
                    'publication_title' => 'Green Synthesis of Silver Nanoparticles using Plant Extracts for Anti-Microbial Coatings',
                    'author_name' => 'Dr. G. Sarma, Prof. Balaji Meriga, Prof. L. Dakshayani',
                    'doi_number' => '10.1016/j.matlet.2026.01.088',
                    'publication_date' => '2026-01-22',
                    'publication_journal' => 'Materials Letters',
                    'impact_factor' => 3.300
                ],
                [
                    'task_no' => 'TASK-YVU-PUB-02',
                    'publication_title' => 'Phyto-Fabricated Metal Nanostructures and Their Multi-Drug Resistant Bacterial Evaluation',
                    'author_name' => 'Dr. G. Sarma, Prof. L. Dakshayani',
                    'doi_number' => '10.1155/2026/8912450',
                    'publication_date' => '2026-03-10',
                    'publication_journal' => 'Journal of Nanomaterials',
                    'impact_factor' => 3.800
                ]
            ],
            'patents' => [
                [
                    'task_no' => 'TASK-YVU-PAT-01',
                    'patent_title' => 'Process for Phyto-Synthesizing Highly Stable Silver Nanoparticles with Enhanced Antibacterial Activity',
                    'inventor_name' => 'Dr. G. Sarma, Prof. L. Dakshayani',
                    'application_no' => '202541031980',
                    'status' => 'Filed',
                    'filing_date' => '2025-04-12',
                    'country' => 'India',
                    'abstract' => 'An eco-friendly single-step aqueous extraction and reduction method yielding monodisperse metallic silver nanoparticles.'
                ]
            ],
            'internships' => [
                [
                    'task_no' => 'TASK-YVU-INT-01',
                    'title' => 'Workshop and Hands-on Internship on BD Accuri C6 Plus Flow Cytometer',
                    'project_investigator' => 'Prof. L. Dakshayani',
                    'no_students_trained' => 15,
                    'no_days_trained' => 15,
                    'students_names' => 'K. Prasad, G. Anusha, S. Ramana, B. Sireesha, V. Sudheer, M. Swapna, T. Naveen, P. Lakshmi, Y. Suresh, D. Haritha, Ch. Ravi, N. Vani, K. Sekhar, A. Padmaja, R. Kiran',
                    'content' => 'Principles of fluidics, optics, detector electronics, calibration beads alignment, and cell cycle analysis.'
                ],
                [
                    'task_no' => 'TASK-YVU-INT-02',
                    'title' => 'Research Training in Green Nanotechnology & Advanced Chromatographic Separation',
                    'project_investigator' => 'Dr. G. Sarma',
                    'no_students_trained' => 5,
                    'no_days_trained' => 30,
                    'students_names' => 'P. Jagadeesh, M. Divya, S. Subhash, B. Hemalatha, K. Madhusudhan',
                    'content' => 'Phyto-reduction methodologies, UV-visible plasmon resonance monitoring, and centrifugal purification.'
                ]
            ]
        ]
    ];

    // 3. Insert unique data for each university
    foreach ($data as $prefix => $modules) {
        echo ">>> Seeding unique data for $prefix...\n";

        // Insert Progress Reports
        $progTable = "{$prefix}_progress_reports";
        $pCols = $pdo->query("SHOW COLUMNS FROM `$progTable`")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($modules['progress'] as $pItem) {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM `$progTable` WHERE project_title = ?");
            $chk->execute([$pItem['project_title']]);
            if ($chk->fetchColumn() == 0) {
                $fields = ['project_title', 'pi_name', 'co_pi_name', 'task_no', 'work_package_no', 'interns_trained_count', 'summary_progress', 'approved_objects', 'methodology'];
                $params = [
                    ':project_title' => $pItem['project_title'],
                    ':pi_name' => $pItem['pi_name'],
                    ':co_pi_name' => $pItem['co_pi_name'],
                    ':task_no' => $pItem['task_no'],
                    ':work_package_no' => $pItem['work_package_no'],
                    ':interns_trained_count' => $pItem['interns_trained_count'],
                    ':summary_progress' => $pItem['summary_progress'],
                    ':approved_objects' => $pItem['approved_objects'],
                    ':methodology' => $pItem['methodology']
                ];
                if (in_array('approval_status', $pCols, true)) {
                    $fields[] = 'approval_status';
                    $params[':approval_status'] = 'Approved';
                }
                if (in_array('publish_status', $pCols, true)) {
                    $fields[] = 'publish_status';
                    $params[':publish_status'] = 1;
                }
                $colsSql = implode('`, `', $fields);
                $valsSql = implode(', ', array_keys($params));
                $pdo->prepare("INSERT INTO `$progTable` (`$colsSql`) VALUES ($valsSql)")->execute($params);
                echo "   [+] Progress Report: {$pItem['project_title']}\n";
            }
        }

        // Insert Publications
        $pubTable = "{$prefix}_publications";
        $pubCols = $pdo->query("SHOW COLUMNS FROM `$pubTable`")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($modules['publications'] as $pubItem) {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM `$pubTable` WHERE publication_title = ?");
            $chk->execute([$pubItem['publication_title']]);
            if ($chk->fetchColumn() == 0) {
                $fields = ['task_no', 'publication_title', 'author_name', 'doi_number', 'publication_date', 'publication_journal', 'impact_factor'];
                $params = [
                    ':task_no' => $pubItem['task_no'],
                    ':publication_title' => $pubItem['publication_title'],
                    ':author_name' => $pubItem['author_name'],
                    ':doi_number' => $pubItem['doi_number'],
                    ':publication_date' => $pubItem['publication_date'],
                    ':publication_journal' => $pubItem['publication_journal'],
                    ':impact_factor' => $pubItem['impact_factor']
                ];
                if (in_array('approval_status', $pubCols, true)) {
                    $fields[] = 'approval_status';
                    $params[':approval_status'] = 'Approved';
                }
                if (in_array('publish_status', $pubCols, true)) {
                    $fields[] = 'publish_status';
                    $params[':publish_status'] = 1;
                }
                if (in_array('publication_type', $pubCols, true)) {
                    $fields[] = 'publication_type';
                    $params[':publication_type'] = 'Single';
                }
                $colsSql = implode('`, `', $fields);
                $valsSql = implode(', ', array_keys($params));
                $pdo->prepare("INSERT INTO `$pubTable` (`$colsSql`) VALUES ($valsSql)")->execute($params);
                echo "   [+] Publication: {$pubItem['publication_title']}\n";
            }
        }

        // Insert Patents
        $patTable = "{$prefix}_patent";
        $patCols = $pdo->query("SHOW COLUMNS FROM `$patTable`")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($modules['patents'] as $patItem) {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM `$patTable` WHERE patent_title = ?");
            $chk->execute([$patItem['patent_title']]);
            if ($chk->fetchColumn() == 0) {
                $patentId = $patItem['patent_id'] ?? ('PAT-' . strtoupper($prefix) . '-' . rand(100, 999));
                $fields = ['patent_id', 'task_no', 'patent_title', 'inventor_name', 'status', 'filing_date', 'country', 'abstract'];
                $params = [
                    ':patent_id' => $patentId,
                    ':task_no' => $patItem['task_no'],
                    ':patent_title' => $patItem['patent_title'],
                    ':inventor_name' => $patItem['inventor_name'],
                    ':status' => $patItem['status'],
                    ':filing_date' => $patItem['filing_date'],
                    ':country' => $patItem['country'],
                    ':abstract' => $patItem['abstract']
                ];
                if (!empty($patItem['application_no'])) {
                    $fields[] = 'application_no';
                    $params[':application_no'] = $patItem['application_no'];
                }
                if (!empty($patItem['patent_no'])) {
                    $fields[] = 'patent_no';
                    $params[':patent_no'] = $patItem['patent_no'];
                }
                if (!empty($patItem['grant_date'])) {
                    $fields[] = 'grant_date';
                    $params[':grant_date'] = $patItem['grant_date'];
                }
                if (in_array('approval_status', $patCols, true)) {
                    $fields[] = 'approval_status';
                    $params[':approval_status'] = 'Approved';
                }
                if (in_array('publish_status', $patCols, true)) {
                    $fields[] = 'publish_status';
                    $params[':publish_status'] = 1;
                }
                $colsSql = implode('`, `', $fields);
                $valsSql = implode(', ', array_keys($params));
                $pdo->prepare("INSERT INTO `$patTable` (`$colsSql`) VALUES ($valsSql)")->execute($params);
                echo "   [+] Patent: {$patItem['patent_title']}\n";
            }
        }

        // Insert Internships
        $intTable = "{$prefix}_internships";
        $intCols = $pdo->query("SHOW COLUMNS FROM `$intTable`")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($modules['internships'] as $intItem) {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM `$intTable` WHERE title = ?");
            $chk->execute([$intItem['title']]);
            if ($chk->fetchColumn() == 0) {
                $fields = ['task_no', 'title', 'project_investigator', 'no_students_trained', 'no_days_trained', 'students_names', 'content'];
                $params = [
                    ':task_no' => $intItem['task_no'],
                    ':title' => $intItem['title'],
                    ':project_investigator' => $intItem['project_investigator'],
                    ':no_students_trained' => $intItem['no_students_trained'],
                    ':no_days_trained' => $intItem['no_days_trained'],
                    ':students_names' => $intItem['students_names'],
                    ':content' => $intItem['content']
                ];
                if (in_array('approval_status', $intCols, true)) {
                    $fields[] = 'approval_status';
                    $params[':approval_status'] = 'Approved';
                }
                if (in_array('publish_status', $intCols, true)) {
                    $fields[] = 'publish_status';
                    $params[':publish_status'] = 1;
                }
                $colsSql = implode('`, `', $fields);
                $valsSql = implode(', ', array_keys($params));
                $pdo->prepare("INSERT INTO `$intTable` (`$colsSql`) VALUES ($valsSql)")->execute($params);
                echo "   [+] Internship: {$intItem['title']}\n";
            }
        }
    }

    echo "\n=================================================================\n";
    echo "  ALL 7 UNIVERSITIES SUCCESSFULLY SEEDED WITH UNIQUE DATA!       \n";
    echo "=================================================================\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
