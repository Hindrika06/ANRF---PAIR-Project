<?php
/**
 * ANRF-PAIR Progress Reports Helper Functions
 * 
 * Provides database schema auto-migration, Task ID discovery across KPI tables,
 * KPI reference retrieval (single source of truth), and report management.
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
            'include_publications'     => "TINYINT(1) NOT NULL DEFAULT 1",
            'include_capacity_building'=> "TINYINT(1) NOT NULL DEFAULT 1",
            'include_workshops'        => "TINYINT(1) NOT NULL DEFAULT 1",
            'include_training'         => "TINYINT(1) NOT NULL DEFAULT 1",
            'include_interns'          => "TINYINT(1) NOT NULL DEFAULT 1",
            'workshops_narrative'      => "TEXT DEFAULT NULL",
            'training_narrative'       => "TEXT DEFAULT NULL",
        ];

        foreach ($prefixes as $p) {
            $tbl = "{$p}_progress_reports";
            try {
                // Check if table exists
                $chk = $pdo->query("SHOW TABLES LIKE '$tbl'");
                if (!$chk || count($chk->fetchAll()) === 0) continue;

                $existingCols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);

                foreach ($colsToAdd as $col => $definition) {
                    if (!in_array($col, $existingCols, true)) {
                        $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `$col` $definition");
                    }
                }
            } catch (Exception $e) {
                // Log silently or ignore if already present
                error_log("Schema update notice for $tbl: " . $e->getMessage());
            }
        }
    }
}

if (!function_exists('getInstituteTaskIds')) {
    /**
     * Discovers all distinct Task IDs for an institute across all existing KPI tables.
     */
    function getInstituteTaskIds($pdo, $prefix) {
        $taskMap = [];

        // 1. Publications (task_no)
        try {
            $tbl = "{$prefix}_publications";
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk > 0) {
                $rows = $pdo->query("SELECT DISTINCT task_no FROM `$tbl` WHERE task_no IS NOT NULL AND TRIM(task_no) != ''")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($rows as $r) {
                    $t = trim($r);
                    if ($t !== '') $taskMap[$t] = true;
                }
            }
        } catch (Exception $e) {}

        // 2. Conferences (taskno or task_no)
        try {
            $tbl = "{$prefix}_conferences";
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk > 0) {
                $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
                $col = in_array('taskno', $cols, true) ? 'taskno' : (in_array('task_no', $cols, true) ? 'task_no' : null);
                if ($col) {
                    $rows = $pdo->query("SELECT DISTINCT `$col` FROM `$tbl` WHERE `$col` IS NOT NULL AND TRIM(`$col`) != ''")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($rows as $r) {
                        $t = trim($r);
                        if ($t !== '') $taskMap[$t] = true;
                    }
                }
            }
        } catch (Exception $e) {}

        // 3. Webinars (taskno or task_no)
        try {
            $tbl = "{$prefix}_webinars";
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk > 0) {
                $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
                $col = in_array('taskno', $cols, true) ? 'taskno' : (in_array('task_no', $cols, true) ? 'task_no' : null);
                if ($col) {
                    $rows = $pdo->query("SELECT DISTINCT `$col` FROM `$tbl` WHERE `$col` IS NOT NULL AND TRIM(`$col`) != ''")->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($rows as $r) {
                        $t = trim($r);
                        if ($t !== '') $taskMap[$t] = true;
                    }
                }
            }
        } catch (Exception $e) {}

        // 4. Internships (task_no)
        try {
            $tbl = "{$prefix}_internships";
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk > 0) {
                $rows = $pdo->query("SELECT DISTINCT task_no FROM `$tbl` WHERE task_no IS NOT NULL AND TRIM(task_no) != ''")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($rows as $r) {
                    $t = trim($r);
                    if ($t !== '') $taskMap[$t] = true;
                }
            }
        } catch (Exception $e) {}

        // 5. Patents (task_no)
        try {
            $tbl = "{$prefix}_patents";
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk > 0) {
                $rows = $pdo->query("SELECT DISTINCT task_no FROM `$tbl` WHERE task_no IS NOT NULL AND TRIM(task_no) != ''")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($rows as $r) {
                    $t = trim($r);
                    if ($t !== '') $taskMap[$t] = true;
                }
            }
        } catch (Exception $e) {}

        // 6. Progress Reports (task_no)
        try {
            $tbl = "{$prefix}_progress_reports";
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk > 0) {
                $rows = $pdo->query("SELECT DISTINCT task_no FROM `$tbl` WHERE task_no IS NOT NULL AND TRIM(task_no) != ''")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($rows as $r) {
                    $t = trim($r);
                    if ($t !== '') $taskMap[$t] = true;
                }
            }
        } catch (Exception $e) {}

        $tasks = array_keys($taskMap);
        $tasks = array_unique(array_map('trim', $tasks));
        natsort($tasks);
        return array_values($tasks);
    }
}

if (!function_exists('getReportsForTask')) {
    /**
     * Fetches all existing progress reports for a specific Institute + Task ID.
     */
    function getReportsForTask($pdo, $prefix, $taskId) {
        $tbl = "{$prefix}_progress_reports";
        try {
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk === 0) return [];

            $stmt = $pdo->prepare("SELECT * FROM `$tbl` WHERE task_no = ? ORDER BY id DESC");
            $stmt->execute([$taskId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getKpiPublicationsForTask')) {
    /**
     * Retrieves existing publication KPI records for a specific Task ID (READ-ONLY).
     */
    function getKpiPublicationsForTask($pdo, $prefix, $taskId) {
        $tbl = "{$prefix}_publications";
        try {
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk === 0) return [];

            $stmt = $pdo->prepare("SELECT * FROM `$tbl` WHERE task_no = ? ORDER BY id DESC");
            $stmt->execute([$taskId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getKpiConferencesForTask')) {
    /**
     * Retrieves existing conference KPI records for a specific Task ID (READ-ONLY).
     */
    function getKpiConferencesForTask($pdo, $prefix, $taskId) {
        $tbl = "{$prefix}_conferences";
        try {
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk === 0) return [];

            $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
            $col = in_array('taskno', $cols, true) ? 'taskno' : (in_array('task_no', $cols, true) ? 'task_no' : null);

            if (!$col) return [];

            $stmt = $pdo->prepare("SELECT * FROM `$tbl` WHERE `$col` = ? ORDER BY id DESC");
            $stmt->execute([$taskId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getKpiWebinarsForTask')) {
    /**
     * Retrieves existing webinar / training KPI records for a specific Task ID (READ-ONLY).
     */
    function getKpiWebinarsForTask($pdo, $prefix, $taskId) {
        $tbl = "{$prefix}_webinars";
        try {
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk === 0) return [];

            $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
            $col = in_array('taskno', $cols, true) ? 'taskno' : (in_array('task_no', $cols, true) ? 'task_no' : null);

            if (!$col) return [];

            $stmt = $pdo->prepare("SELECT * FROM `$tbl` WHERE `$col` = ? ORDER BY id DESC");
            $stmt->execute([$taskId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('getKpiInternCountForTask')) {
    /**
     * Calculates total interns trained by summing `no_students_trained` from internship KPI records.
     */
    function getKpiInternCountForTask($pdo, $prefix, $taskId) {
        $tbl = "{$prefix}_internships";
        try {
            $chk = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
            if ($chk === 0) return 0;

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(no_students_trained), 0) AS total_trained FROM `$tbl` WHERE task_no = ?");
            $stmt->execute([$taskId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($res['total_trained'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
}
