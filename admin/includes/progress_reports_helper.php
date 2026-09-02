<?php
/**
 * ANRF-PAIR Progress Reports Helper Functions
 * 
 * Provides database schema auto-migration, independent KPI record discovery/search,
 * JSON reference retrieval (single source of truth), and report management.
 */

if (!function_exists('ensureProgressReportsSchema')) {
    /**
     * Ensures all 7 institute progress_reports tables have the required columns.
     */
    function ensureProgressReportsSchema($pdo) {
        static $executed = false;
        if ($executed) return;
        $executed = true;

        $prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
        $colsToAdd = [
            'work_package_no'          => "VARCHAR(100) DEFAULT NULL",
            'interns_trained_count'    => "INT UNSIGNED NOT NULL DEFAULT 0",
            'include_publications'     => "TINYINT(1) NOT NULL DEFAULT 0",
            'include_conferences'      => "TINYINT(1) NOT NULL DEFAULT 0",
            'include_webinars'         => "TINYINT(1) NOT NULL DEFAULT 0",
            'include_internships'      => "TINYINT(1) NOT NULL DEFAULT 0",
            'include_patents'          => "TINYINT(1) NOT NULL DEFAULT 0",
            'include_capacity_building'=> "TINYINT(1) NOT NULL DEFAULT 0",
            'include_workshops'        => "TINYINT(1) NOT NULL DEFAULT 0",
            'include_training'         => "TINYINT(1) NOT NULL DEFAULT 0",
            'include_interns'          => "TINYINT(1) NOT NULL DEFAULT 0",
            'selected_publication_ids' => "TEXT DEFAULT NULL",
            'selected_conference_ids'  => "TEXT DEFAULT NULL",
            'selected_webinar_ids'     => "TEXT DEFAULT NULL",
            'selected_internship_ids'  => "TEXT DEFAULT NULL",
            'selected_patent_ids'      => "TEXT DEFAULT NULL",
            'selected_workshop_ids'    => "TEXT DEFAULT NULL",
            'selected_training_ids'    => "TEXT DEFAULT NULL",
            'workshops_narrative'      => "TEXT DEFAULT NULL",
            'training_narrative'       => "TEXT DEFAULT NULL",
        ];

        foreach ($prefixes as $p) {
            $tbl = "{$p}_progress_reports";
            try {
                $chk = $pdo->query("SHOW TABLES LIKE '$tbl'");
                if (!$chk || count($chk->fetchAll()) === 0) continue;

                $existingCols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);

                foreach ($colsToAdd as $col => $definition) {
                    if (!in_array($col, $existingCols, true)) {
                        $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `$col` $definition");
                    }
                }
            } catch (Exception $e) {
                error_log("Schema update notice for $tbl: " . $e->getMessage());
            }
        }
    }
}

if (!function_exists('getAllInstituteProgressReports')) {
    /**
     * Fetches all existing progress reports for an institute.
     */
    function getAllInstituteProgressReports($pdo, $prefix) {
        $tbl = "{$prefix}_progress_reports";
        try {
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk === 0) return [];

            $stmt = $pdo->query("SELECT * FROM `$tbl` ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getKpiCategoryTableName')) {
    function getKpiCategoryTableName($prefix, $category) {
        $category = strtolower(trim($category));
        if ($category === 'workshops') $category = 'conferences';
        if ($category === 'trainings' || $category === 'training') $category = 'webinars';
        
        $validMap = [
            'publications' => "{$prefix}_publications",
            'conferences'  => "{$prefix}_conferences",
            'webinars'     => "{$prefix}_webinars",
            'internships'  => "{$prefix}_internships",
            'patents'      => "{$prefix}_patents",
        ];
        return $validMap[$category] ?? null;
    }
}

if (!function_exists('searchKpiCategoryRecords')) {
    /**
     * Searches existing KPI records for selection modals WITHOUT Task-ID restriction.
     */
    function searchKpiCategoryRecords($pdo, $prefix, $category, $searchQuery = '') {
        $tbl = getKpiCategoryTableName($prefix, $category);
        if (!$tbl) return [];

        try {
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk === 0) return [];

            $existingCols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);

            $sql = "SELECT * FROM `$tbl` WHERE 1=1";
            $params = [];

            $searchQuery = trim($searchQuery);
            if ($searchQuery !== '') {
                $term = "%{$searchQuery}%";
                $cat = strtolower(trim($category));

                $targetFields = [];
                if ($cat === 'publications') {
                    $targetFields = ['publication_title', 'author_name', 'publication_journal', 'doi_number', 'task_no'];
                } elseif ($cat === 'conferences' || $cat === 'workshops') {
                    $targetFields = ['title', 'organisers', 'institute', 'investigator', 'taskno', 'task_no'];
                } elseif ($cat === 'webinars' || $cat === 'trainings' || $cat === 'training') {
                    $targetFields = ['title', 'speaker_name', 'affiliation', 'organisers', 'institute', 'investigator', 'taskno', 'task_no'];
                } elseif ($cat === 'internships') {
                    $targetFields = ['title', 'project_investigator', 'students_names', 'task_no'];
                } elseif ($cat === 'patents') {
                    $targetFields = ['patent_title', 'inventor_name', 'patent_number', 'application_number', 'task_no'];
                }

                $validFields = array_values(array_intersect($targetFields, $existingCols));
                if (!empty($validFields)) {
                    $whereClauses = [];
                    foreach ($validFields as $f) {
                        $whereClauses[] = "`$f` LIKE ?";
                        $params[] = $term;
                    }
                    $sql .= " AND (" . implode(' OR ', $whereClauses) . ")";
                }
            }

            $sql .= " ORDER BY id DESC LIMIT 100";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $formatted = [];
            $catKey = strtolower(trim($category));
            foreach ($rows as $r) {
                $item = [
                    'id' => (int)$r['id'],
                    'task_no' => $r['task_no'] ?? ($r['taskno'] ?? ''),
                ];

                if ($catKey === 'publications') {
                    $item['title'] = $r['publication_title'] ?? 'Untitled Publication';
                    $sub = [];
                    if (!empty($r['author_name'])) $sub[] = "Authors: " . $r['author_name'];
                    if (!empty($r['publication_journal'])) $sub[] = "Journal: " . $r['publication_journal'];
                    if (!empty($r['publication_date'])) $sub[] = "Date: " . date('d M Y', strtotime($r['publication_date']));
                    if (!empty($r['doi_number'])) $sub[] = "DOI: " . $r['doi_number'];
                    $item['subtitle'] = implode(' | ', $sub);
                } elseif ($catKey === 'conferences' || $catKey === 'workshops') {
                    $item['title'] = $r['title'] ?? 'Untitled Event';
                    $sub = [];
                    if (!empty($r['organisers'])) $sub[] = "Organizers: " . $r['organisers'];
                    if (!empty($r['conf_date'])) $sub[] = "Date: " . date('d M Y', strtotime($r['conf_date']));
                    if (!empty($r['institute'])) $sub[] = "Venue: " . $r['institute'];
                    $item['subtitle'] = implode(' | ', $sub);
                } elseif ($catKey === 'webinars' || $catKey === 'trainings' || $catKey === 'training') {
                    $item['title'] = $r['title'] ?? 'Untitled Webinar / Training';
                    $sub = [];
                    if (!empty($r['speaker_name'])) $sub[] = "Speaker: " . $r['speaker_name'];
                    if (!empty($r['webinar_date'])) $sub[] = "Date: " . date('d M Y', strtotime($r['webinar_date']));
                    if (!empty($r['organisers'])) $sub[] = "Organizers: " . $r['organisers'];
                    if (!empty($r['affiliation'])) $sub[] = "Affiliation: " . $r['affiliation'];
                    $item['subtitle'] = implode(' | ', $sub);
                } elseif ($catKey === 'internships') {
                    $item['title'] = $r['title'] ?? 'Untitled Internship Program';
                    $sub = [];
                    if (!empty($r['project_investigator'])) $sub[] = "PI: " . $r['project_investigator'];
                    if (!empty($r['no_students_trained'])) $sub[] = "Interns: " . $r['no_students_trained'];
                    if (!empty($r['students_names'])) $sub[] = "Students: " . $r['students_names'];
                    $item['subtitle'] = implode(' | ', $sub);
                } elseif ($catKey === 'patents') {
                    $item['title'] = $r['patent_title'] ?? 'Untitled Patent';
                    $sub = [];
                    if (!empty($r['inventor_name'])) $sub[] = "Inventor(s): " . $r['inventor_name'];
                    if (!empty($r['patent_number'])) $sub[] = "Patent No: " . $r['patent_number'];
                    if (!empty($r['application_number'])) $sub[] = "App No: " . $r['application_number'];
                    if (!empty($r['patent_status'])) $sub[] = "Status: " . $r['patent_status'];
                    $item['subtitle'] = implode(' | ', $sub);
                }

                $formatted[] = $item;
            }

            return $formatted;
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getKpiCategoryRecordsByIds')) {
    /**
     * Retrieves full KPI record rows for a specific list of integer IDs (READ-ONLY).
     */
    function getKpiCategoryRecordsByIds($pdo, $prefix, $category, $ids) {
        if (empty($ids) || !is_array($ids)) return [];
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) return [];

        $tbl = getKpiCategoryTableName($prefix, $category);
        if (!$tbl) return [];

        try {
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk === 0) return [];

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM `$tbl` WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Sort rows in order of original IDs array
            $idMap = [];
            foreach ($rows as $r) {
                $idMap[(int)$r['id']] = $r;
            }

            $sorted = [];
            foreach ($ids as $id) {
                if (isset($idMap[$id])) {
                    $sorted[] = $idMap[$id];
                }
            }
            return $sorted;
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getInstituteTaskIds')) {
    function getInstituteTaskIds($pdo, $prefix) {
        $taskMap = [];
        $tables = [
            "{$prefix}_publications" => ['task_no'],
            "{$prefix}_conferences"  => ['taskno', 'task_no'],
            "{$prefix}_webinars"     => ['taskno', 'task_no'],
            "{$prefix}_internships"  => ['task_no'],
            "{$prefix}_patents"      => ['task_no'],
            "{$prefix}_progress_reports" => ['task_no'],
        ];

        foreach ($tables as $tbl => $possibleCols) {
            try {
                if ($pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount() > 0) {
                    $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($possibleCols as $c) {
                        if (in_array($c, $cols, true)) {
                            $rows = $pdo->query("SELECT DISTINCT `$c` FROM `$tbl` WHERE `$c` IS NOT NULL AND TRIM(`$c`) != ''")->fetchAll(PDO::FETCH_COLUMN);
                            foreach ($rows as $r) {
                                $t = trim($r);
                                if ($t !== '') $taskMap[$t] = true;
                            }
                            break;
                        }
                    }
                }
            } catch (Exception $e) {}
        }

        $tasks = array_keys($taskMap);
        natsort($tasks);
        return array_values($tasks);
    }
}

if (!function_exists('getReportsForTask')) {
    function getReportsForTask($pdo, $prefix, $taskId) {
        $tbl = "{$prefix}_progress_reports";
        try {
            if ($pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount() === 0) return [];
            $stmt = $pdo->prepare("SELECT * FROM `$tbl` WHERE task_no = ? ORDER BY id DESC");
            $stmt->execute([$taskId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}

