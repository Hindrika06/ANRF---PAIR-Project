<?php
/**
 * Separate each university conferences and seed unique, distinct conferences for all 7 universities.
 */
require_once __DIR__ . '/../config.php';

try {
    echo "=======================================================\n";
    echo " SEPARATING AND SEEDING UNIQUE UNIVERSITY CONFERENCES \n";
    echo "=======================================================\n\n";

    $prefixes = ['uoh', 'cuk', 'kannur', 'mgu', 'ou', 'svu', 'yvu'];

    // 1. Remove the generic duplicate placeholder conference
    foreach ($prefixes as $p) {
        $tbl = "{$p}_conferences";
        try {
            $pdo->exec("DELETE FROM `$tbl` WHERE title = 'International Conference on AI Trends'");
        } catch (Exception $e) {}
    }

    // 2. Ensure schema columns exist
    foreach ($prefixes as $p) {
        $tbl = "{$p}_conferences";
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('approval_status', $cols, true)) {
                $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved'");
            }
            if (!in_array('publish_status', $cols, true)) {
                $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `publish_status` TINYINT(1) DEFAULT 1");
            }
            if (!in_array('conf_date', $cols, true) && in_array('start_date', $cols, true)) {
                $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `conf_date` DATE DEFAULT NULL");
                $pdo->exec("UPDATE `$tbl` SET `conf_date` = `start_date` WHERE `conf_date` IS NULL");
            }
        } catch (Exception $e) {}
    }

    $uniqueConferences = [
        'uoh' => [
            'taskno'      => 'CONF-UOH-2026-01',
            'title'       => 'National Symposium on Glyco-Biology and Immuno-Therapeutics',
            'organizer'   => 'School of Life Sciences & School of Chemistry, University of Hyderabad',
            'start_date'  => '2026-10-14',
            'end_date'    => '2026-10-16',
            'location'    => 'DST Auditorium, University of Hyderabad Campus',
            'website_url' => 'https://uoh.ac.in/events/glyco-biology-symposium-2026',
            'description' => 'A premier 3-day symposium bringing together cellular biologists, structural biochemists, and oncologists discussing glycan-mediated host-pathogen interactions.',
        ],
        'cuk' => [
            'taskno'      => 'CONF-CUK-2026-01',
            'title'       => 'International Conference on Computational Radiomics & Structural Informatics',
            'organizer'   => 'Department of Mathematics & Computational Sciences, Central University of Karnataka',
            'start_date'  => '2026-11-05',
            'end_date'    => '2026-11-07',
            'location'    => 'Administrative Block Auditorium, CUK Campus, Kalaburagi',
            'website_url' => 'https://cuk.ac.in/events/radiomics-conf-2026',
            'description' => 'Focusing on mathematical modeling, deep learning architectures, and high-throughput radiomic profiling for cancer prognostic modeling.',
        ],
        'kannur' => [
            'taskno'      => 'CONF-KAN-2026-01',
            'title'       => 'National Conference on Population Genomics & Molecular Epidemiology',
            'organizer'   => 'Department of Biotechnology & Microbiology, Kannur University',
            'start_date'  => '2026-09-18',
            'end_date'    => '2026-09-20',
            'location'    => 'Thalassery Campus Convention Centre, Kannur University, Kerala',
            'website_url' => 'https://kannuruniversity.ac.in/events/genomics-2026',
            'description' => 'Exploring genomic diversity, SNP genotyping pipelines, and personalized medicine solutions for metabolic conditions.',
        ],
        'mgu' => [
            'taskno'      => 'CONF-MGU-2026-01',
            'title'       => 'International Summit on Advanced Bionanomaterials & Tissue Engineering (ISABTE)',
            'organizer'   => 'School of Biosciences & IIUCNN, Mahatma Gandhi University, Kottayam',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-24',
            'location'    => 'Assembly Hall, Mahatma Gandhi University, Kottayam, Kerala',
            'website_url' => 'https://mgu.ac.in/events/isabte-2026',
            'description' => 'Bringing together polymer chemists, bio-engineers, and nanotechnologists to present breakthroughs in regenerative hydrogels and 3D printed scaffolds.',
        ],
        'ou' => [
            'taskno'      => 'CONF-OU-2026-01',
            'title'       => 'Annual Conference on Computational Pharmacophore Modeling & AI in Drug Discovery',
            'organizer'   => 'Department of Chemistry, Osmania University, Hyderabad',
            'start_date'  => '2026-10-28',
            'end_date'    => '2026-10-30',
            'location'    => 'Arts College Auditorium, Osmania University, Hyderabad',
            'website_url' => 'https://osmania.ac.in/events/pharmacophore-conf-2026',
            'description' => 'Targeting rational drug design, in-silico ADMET screening, and molecular docking methodologies for anti-tumor drug discovery.',
        ],
        'svu' => [
            'taskno'      => 'CONF-SVU-2026-01',
            'title'       => 'National Conference on Nano-Biosensors & Translational Point-of-Care Diagnostics',
            'organizer'   => 'Department of Physics & Biochemistry, Sri Venkateswara University, Tirupati',
            'start_date'  => '2026-12-08',
            'end_date'    => '2026-12-10',
            'location'    => 'Senate Hall, Sri Venkateswara University, Tirupati',
            'website_url' => 'https://svuniversity.edu.in/events/nano-biosensors-2026',
            'description' => 'Frontier conference on electrochemical biosensors, microfluidics, and translational diagnostic devices for rural clinical healthcare.',
        ],
        'yvu' => [
            'taskno'      => 'CONF-YVU-2026-01',
            'title'       => 'WORKSHOP ON Flow Cytometry: Principles, Applications, and Hands-on Training for Biomedical Research',
            'organizer'   => 'ANRF-PAIR Project in association with BD Bioscience, India at Yogi Vemana University',
            'start_date'  => '2026-07-22',
            'end_date'    => '2026-07-23',
            'location'    => 'Tallapaka Annamacharya Senate Hall, YVU, Kadapa',
            'website_url' => 'https://yvu.edu.in/events/flow-cytometry-workshop',
            'description' => 'Intensive two-day hands-on training workshop on the BD Accuri C6 Plus flow cytometer, immunophenotyping, and live-cell assays.',
        ]
    ];

    foreach ($uniqueConferences as $prefix => $conf) {
        $tbl = "{$prefix}_conferences";
        $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);

        $check = $pdo->prepare("SELECT COUNT(*) FROM `$tbl` WHERE title = ?");
        $check->execute([$conf['title']]);
        if ($check->fetchColumn() == 0) {
            $fields = ['title'];
            $params = [':title' => $conf['title']];

            if (in_array('taskno', $cols, true)) {
                $fields[] = 'taskno';
                $params[':taskno'] = $conf['taskno'];
            }
            if (in_array('organizer', $cols, true)) {
                $fields[] = 'organizer';
                $params[':organizer'] = $conf['organizer'];
            }
            if (in_array('organisers', $cols, true)) {
                $fields[] = 'organisers';
                $params[':organisers'] = $conf['organizer'];
            }
            if (in_array('start_date', $cols, true)) {
                $fields[] = 'start_date';
                $params[':start_date'] = $conf['start_date'];
            }
            if (in_array('end_date', $cols, true)) {
                $fields[] = 'end_date';
                $params[':end_date'] = $conf['end_date'];
            }
            if (in_array('conf_date', $cols, true)) {
                $fields[] = 'conf_date';
                $params[':conf_date'] = $conf['start_date'];
            }
            if (in_array('location', $cols, true)) {
                $fields[] = 'location';
                $params[':location'] = $conf['location'];
            }
            if (in_array('website_url', $cols, true)) {
                $fields[] = 'website_url';
                $params[':website_url'] = $conf['website_url'];
            }
            if (in_array('content', $cols, true)) {
                $fields[] = 'content';
                $params[':content'] = $conf['description'];
            }
            if (in_array('approval_status', $cols, true)) {
                $fields[] = 'approval_status';
                $params[':approval_status'] = 'Approved';
            }
            if (in_array('publish_status', $cols, true)) {
                $fields[] = 'publish_status';
                $params[':publish_status'] = 1;
            }

            $colNames = implode('`, `', $fields);
            $placeholders = implode(', ', array_keys($params));
            $sql = "INSERT INTO `$tbl` (`$colNames`) VALUES ($placeholders)";
            $pdo->prepare($sql)->execute($params);
            echo "  [+] Inserted conference for $prefix: {$conf['title']}\n";
        }
    }

    echo "\n=== ALL 7 UNIVERSITIES CONFERENCES SEEDED SUCCESSFULLY! ===\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
