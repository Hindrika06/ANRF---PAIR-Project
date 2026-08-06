    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    /**
     * KPI Central Approval & Helper Functions
     */

    /**
     * Creates an entry in approval_requests table for Hub Admin review
     */
    function submitKpiApprovalRequest($pdo, $moduleName, $tableName, $institutePrefix, $recordId, $actionType, $newData, $oldData = null) {
        $requestedBy = $_SESSION['username'] ?? 'unknown_admin';

        $stmt = $pdo->prepare("
            INSERT INTO `approval_requests`
                (module_name, table_name, institute_prefix, record_id, action_type, old_data, new_data, requested_by, requested_at, status)
            VALUES
                (:module_name, :table_name, :institute_prefix, :record_id, :action_type, :old_data, :new_data, :requested_by, NOW(), 'Pending')
        ");

        return $stmt->execute([
            ':module_name'     => $moduleName,
            ':table_name'      => $tableName,
            ':institute_prefix'=> $institutePrefix,
            ':record_id'       => $recordId,
            ':action_type'     => $actionType,
            ':old_data'        => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            ':new_data'        => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            ':requested_by'    => $requestedBy
        ]);
    }

    /**
     * Approves a KPI request: updates approval_requests log and target table record
     */
    function approveKpiRequest($pdo, $requestId, $approvedBy) {
        // 1. Fetch request
        $stmt = $pdo->prepare("SELECT * FROM `approval_requests` WHERE id = ?");
        $stmt->execute([$requestId]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$req) return false;

        $tableName = $req['table_name'];
        $recordId  = $req['record_id'];

        // 2. Update target table approval_status to 'Approved'
        if ($tableName && $recordId) {
            $upd = $pdo->prepare("UPDATE `$tableName` SET `approval_status` = 'Approved' WHERE id = ?");
            $upd->execute([$recordId]);
        }

        // 3. Update approval_requests log
        $updLog = $pdo->prepare("
            UPDATE `approval_requests`
            SET status = 'Approved', approved_by = ?, approved_at = NOW()
            WHERE id = ?
        ");
        return $updLog->execute([$approvedBy, $requestId]);
    }

    /**
     * Rejects a KPI request: updates approval_requests log and target table record
     */
    function rejectKpiRequest($pdo, $requestId, $rejectedBy, $reason) {
        // 1. Fetch request
        $stmt = $pdo->prepare("SELECT * FROM `approval_requests` WHERE id = ?");
        $stmt->execute([$requestId]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$req) return false;

        $tableName = $req['table_name'];
        $recordId  = $req['record_id'];

        // 2. Update target table approval_status to 'Rejected'
        if ($tableName && $recordId) {
            $upd = $pdo->prepare("UPDATE `$tableName` SET `approval_status` = 'Rejected' WHERE id = ?");
            $upd->execute([$recordId]);
        }

        // 3. Update approval_requests log
        $updLog = $pdo->prepare("
            UPDATE `approval_requests`
            SET status = 'Rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ?
            WHERE id = ?
        ");
        return $updLog->execute([$rejectedBy, $reason, $requestId]);
    }

    /**
     * Fetches Centralized KPI Data:
     * - Returns ALL approved records across all specified institute tables.
     * - If current user is an Institute Admin, ALSO includes their OWN pending or rejected records.
     * - If current user is Hub Admin ($isSuper = true), fetches all records across all tables.
     */
    function fetchCentralizedKpiDataset($pdo, $moduleSuffix, $userPrefix, $isSuper = false) {
        $prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
        $combined = [];

        foreach ($prefixes as $p) {
            $tbl = "{$p}_{$moduleSuffix}";
            try {
                $tableCheck = $pdo->query("SHOW TABLES LIKE '$tbl'");
                if (!$tableCheck || count($tableCheck->fetchAll()) === 0) continue;

                $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
                $hasApprovalStatus = in_array('approval_status', $cols, true);
                $hasPublishStatus  = in_array('publish_status', $cols, true);

                if ($isSuper) {
                    // Hub Admin: sees all records across all tables
                    $stmt = $pdo->query("SELECT *, '$p' AS institute_prefix FROM `$tbl`");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) $combined = array_merge($combined, $rows);
                } else {
                    // Institute Admin:
                    // 1) All APPROVED records from any institute
                    // 2) OWN records (where prefix matches $userPrefix) regardless of approval status (Pending/Rejected)
                    if ($hasApprovalStatus) {
                        if ($p === $userPrefix) {
                            // Own institute: fetch everything
                            $stmt = $pdo->query("SELECT *, '$p' AS institute_prefix FROM `$tbl`");
                        } else {
                            // Other institutes: fetch only Approved
                            $stmt = $pdo->query("SELECT *, '$p' AS institute_prefix FROM `$tbl` WHERE approval_status = 'Approved'");
                        }
                    } else {
                        // Fallback if column not present yet
                        $where = ($hasPublishStatus && $p !== $userPrefix) ? "WHERE publish_status = 1" : "";
                        $stmt = $pdo->query("SELECT *, '$p' AS institute_prefix FROM `$tbl` $where");
                    }

                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($rows) $combined = array_merge($combined, $rows);
                }
            } catch (Exception $e) {
                // Ignore single table errors
            }
        }

        return $combined;
    }

    /**
     * Fetches Centralized KPI Data for single-table modules (e.g. collaborations, infrastructure_facilities)
     */
    function fetchSingleTableKpiDataset($pdo, $tableName, $userPrefix, $isSuper = false) {
        try {
            $tableCheck = $pdo->query("SHOW TABLES LIKE '$tableName'");
            if (!$tableCheck || count($tableCheck->fetchAll()) === 0) return [];

            $cols = $pdo->query("SHOW COLUMNS FROM `$tableName`")->fetchAll(PDO::FETCH_COLUMN);
            $hasApprovalStatus = in_array('approval_status', $cols, true);

            if ($isSuper) {
                $stmt = $pdo->query("SELECT * FROM `$tableName`");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                if ($hasApprovalStatus) {
                    $stmt = $pdo->prepare("
                        SELECT * FROM `$tableName`
                        WHERE approval_status = 'Approved'
                        OR institute_prefix = ?
                        OR institute_prefix = 'all'
                    ");
                    $stmt->execute([$userPrefix]);
                } else {
                    $stmt = $pdo->prepare("
                        SELECT * FROM `$tableName`
                        WHERE institute_prefix = ? OR institute_prefix = 'all'
                    ");
                    $stmt->execute([$userPrefix]);
                }
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Fetches Public Centralized KPI Data:
     * - Returns all approved & published records across all specified institute tables.
     * - Enforces approval_status = 'Approved' AND (publish_status = 1 OR publish_status IS NULL)
     */
    function fetchPublicCentralizedKpiDataset($pdo, $moduleSuffix) {
        $prefixes = ['cuk', 'kannur', 'mgu', 'ou', 'svu', 'uoh', 'yvu'];
        $combined = [];

        foreach ($prefixes as $p) {
            $tbl = "{$p}_{$moduleSuffix}";
            try {
                $check = $pdo->query("SHOW TABLES LIKE '$tbl'")->rowCount();
                if ($check === 0) continue;

                $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
                $hasApproval = in_array('approval_status', $cols, true);
                $hasPublish  = in_array('publish_status', $cols, true);

                $whereConditions = [];
                if ($hasApproval) {
                    $whereConditions[] = "approval_status = 'Approved'";
                }
                if ($hasPublish) {
                    $whereConditions[] = "(publish_status = 1 OR publish_status IS NULL)";
                }

                $whereClause = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";
                $stmt = $pdo->query("SELECT *, '$p' AS institute_prefix FROM `$tbl` $whereClause");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($rows) {
                    $combined = array_merge($combined, $rows);
                }
            } catch (Exception $e) {
                // Ignore single table errors
            }
        }

        return $combined;
    }
