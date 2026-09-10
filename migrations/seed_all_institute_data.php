<?php
/**
 * ANRF PAIR Complete Database Seeder for all 7 Institutes
 */

require_once __DIR__ . '/../config.php';

try {
    echo "=======================================================\n";
    echo "       SEEDING ANRF PAIR COMPLETE INSTITUTE DATA       \n";
    echo "=======================================================\n\n";

    $publicationsData = [
        'uoh' => [
            [
                'task_no' => 'TASK-UOH-01',
                'publication_title' => 'Targeting Glycan-Protein Interactions for Novel Therapeutic Interventions in Cancer',
                'author_name' => 'Prof. K. Anand, Dr. S. Radhakrishnan, Dr. M. V. Reddy',
                'doi_number' => '10.1016/j.jbc.2025.105432',
                'publication_date' => '2025-06-15',
                'publication_journal' => 'Journal of Biological Chemistry',
                'impact_factor' => 4.800,
                'approval_status' => 'Approved',
                'publication_type' => 'Single'
            ],
            [
                'task_no' => 'TASK-UOH-02',
                'publication_title' => 'High-Throughput Flow Cytometry Analysis of Immuno-Oncology Biomarkers',
                'author_name' => 'Dr. Anup Kesavan, Prof. Balaji Meriga, Dr. E. K. Radhakrishnan',
                'doi_number' => '10.1038/s41598-025-89102-w',
                'publication_date' => '2025-09-20',
                'publication_journal' => 'Scientific Reports (Nature)',
                'impact_factor' => 4.600,
                'approval_status' => 'Approved',
                'publication_type' => 'Joint'
            ],
            [
                'task_no' => 'TASK-UOH-03',
                'publication_title' => 'Nanostructured Biomaterials for Targeted Drug Delivery in Microfluidic Devices',
                'author_name' => 'Dr. V. Vijjulatha, Dr. G. Sarma, Prof. K. Anand',
                'doi_number' => '10.1021/acs.biomac.5b00123',
                'publication_date' => '2026-01-10',
                'publication_journal' => 'ACS Biomacromolecules',
                'impact_factor' => 6.200,
                'approval_status' => 'Approved',
                'publication_type' => 'Single'
            ],
            [
                'task_no' => 'TASK-UOH-04',
                'publication_title' => 'AI-Driven Radiomics in Precision Medicine and Cancer Prognostication',
                'author_name' => 'Dr. Sathyanarayana N., Dr. Anup Kesavan',
                'doi_number' => '10.1109/TBME.2026.3129845',
                'publication_date' => '2026-02-28',
                'publication_journal' => 'IEEE Transactions on Biomedical Engineering',
                'impact_factor' => 4.750,
                'approval_status' => 'Approved',
                'publication_type' => 'Joint'
            ]
        ],
        'cuk' => [
            [
                'task_no' => 'TASK-CUK-01',
                'publication_title' => 'Structural Genomics of Pathogenic Proteins in Infectious Diseases',
                'author_name' => 'Dr. Sathyanarayana N., Prof. K. Anand',
                'doi_number' => '10.1016/j.str.2025.04.012',
                'publication_date' => '2025-05-10',
                'publication_journal' => 'Structure (Cell Press)',
                'impact_factor' => 5.250,
                'approval_status' => 'Approved',
                'publication_type' => 'Single'
            ]
        ],
        'kannur' => [
            [
                'task_no' => 'TASK-KAN-01',
                'publication_title' => 'Genome-Wide Association Analysis of Metabolic Biomarkers in Regional Populations',
                'author_name' => 'Dr. Anup Kesavan, Dr. E. K. Radhakrishnan',
                'doi_number' => '10.1038/s41588-025-01890-x',
                'publication_date' => '2025-08-12',
                'publication_journal' => 'Nature Genetics',
                'impact_factor' => 27.600,
                'approval_status' => 'Approved',
                'publication_type' => 'Single'
            ]
        ],
        'mgu' => [
            [
                'task_no' => 'TASK-MGU-01',
                'publication_title' => 'Polymeric Bionanocomposites for Tissue Engineering Applications',
                'author_name' => 'Dr. E. K. Radhakrishnan, Prof. K. Anand',
                'doi_number' => '10.1016/j.actbio.2025.10.022',
                'publication_date' => '2025-10-18',
                'publication_journal' => 'Acta Biomaterialia',
                'impact_factor' => 9.700,
                'approval_status' => 'Approved',
                'publication_type' => 'Single'
            ]
        ],
        'ou' => [
            [
                'task_no' => 'TASK-OU-01',
                'publication_title' => 'Computational Pharmacophore Mapping for Small Molecule Kinase Inhibitors',
                'author_name' => 'Dr. V. Vijjulatha, Dr. Sathyanarayana N.',
                'doi_number' => '10.1021/acs.jcim.5b00892',
                'publication_date' => '2025-12-04',
                'publication_journal' => 'Journal of Chemical Information and Modeling',
                'impact_factor' => 5.600,
                'approval_status' => 'Approved',
                'publication_type' => 'Single'
            ]
        ],
        'svu' => [
            [
                'task_no' => 'TASK-SVU-01',
                'publication_title' => 'Phytochemical Profiling and Antioxidant Activity of Endemic Medicinal Plants',
                'author_name' => 'Prof. Balaji Meriga, Dr. G. Sarma',
                'doi_number' => '10.1016/j.jep.2025.118901',
                'publication_date' => '2025-11-05',
                'publication_journal' => 'Journal of Ethnopharmacology',
                'impact_factor' => 5.400,
                'approval_status' => 'Approved',
                'publication_type' => 'Single'
            ]
        ],
        'yvu' => [
            [
                'task_no' => 'TASK-YVU-01',
                'publication_title' => 'Green Synthesis of Silver Nanoparticles using Plant Extracts for Anti-Microbial Coatings',
                'author_name' => 'Dr. G. Sarma, Prof. Balaji Meriga',
                'doi_number' => '10.1016/j.matlet.2026.01.088',
                'publication_date' => '2026-01-22',
                'publication_journal' => 'Materials Letters',
                'impact_factor' => 3.300,
                'approval_status' => 'Approved',
                'publication_type' => 'Single'
            ]
        ]
    ];

    foreach ($publicationsData as $prefix => $pubs) {
        $table = "{$prefix}_publications";
        foreach ($pubs as $pub) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE publication_title = ?");
            $stmt->execute([$pub['publication_title']]);
            if ($stmt->fetchColumn() == 0) {
                $ins = $pdo->prepare("
                    INSERT INTO `$table`
                        (task_no, publication_title, author_name, doi_number, publication_date, publication_journal, impact_factor, approval_status, publication_type)
                    VALUES
                        (:task_no, :title, :author, :doi, :pdate, :journal, :impact, :approval, :type)
                ");
                $ins->execute([
                    ':task_no'  => $pub['task_no'],
                    ':title'    => $pub['publication_title'],
                    ':author'   => $pub['author_name'],
                    ':doi'      => $pub['doi_number'],
                    ':pdate'    => $pub['publication_date'],
                    ':journal'  => $pub['publication_journal'],
                    ':impact'   => $pub['impact_factor'],
                    ':approval' => $pub['approval_status'],
                    ':type'     => $pub['publication_type']
                ]);
                echo "  [+] Inserted Publication: {$pub['publication_title']} into `$table`\n";
            }
        }
    }

    echo "\n=== ALL INSTITUTE PUBLICATIONS SEEDED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "\n[ERROR] Seeding Failed: " . $e->getMessage() . "\n";
    exit(1);
}
