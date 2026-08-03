<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ApprovalPDOStatement extends PDOStatement {
    protected $pdo;
    protected $boundParams = [];
    
    protected function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function bindValue($param, $value, $type = PDO::PARAM_STR) {
        $this->boundParams[$param] = $value;
        return parent::bindValue($param, $value, $type);
    }
    
    public function bindParam($param, &$var, $type = PDO::PARAM_STR, $maxLength = null, $driverOptions = null) {
        $this->boundParams[$param] =& $var;
        return parent::bindParam($param, $var, $type, $maxLength, $driverOptions);
    }
    
    public function execute($params = null) {
        // Bypass if SQL is not write or user is super admin or bypass is active
        if ($this->pdo->bypass_interceptor || 
            !isset($_SESSION['role']) || 
            $_SESSION['role'] === 'super_admin' ||
            !preg_match('/^\s*(INSERT|UPDATE|DELETE)/i', $this->queryString)) {
            return parent::execute($params);
        }
        
        $merged_params = $this->boundParams;
        if ($params) {
            foreach ($params as $k => $v) {
                $merged_params[$k] = $v;
            }
        }
        
        return $this->pdo->interceptQuery($this->queryString, $merged_params);
    }
}

class ApprovalPDO extends PDO {
    public $bypass_interceptor = false;
    
    public function __construct($dsn, $username = null, $password = null, $options = null) {
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['ApprovalPDOStatement', [$this]]);
        $this->installApprovalSchema();
    }
    
    private function installApprovalSchema() {
        try {
            $this->exec("
                CREATE TABLE IF NOT EXISTS `approval_requests` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `module_name` VARCHAR(255) NOT NULL,
                  `table_name` VARCHAR(255) NOT NULL,
                  `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all',
                  `record_id` INT DEFAULT NULL,
                  `action_type` VARCHAR(50) NOT NULL,
                  `old_data` LONGTEXT DEFAULT NULL,
                  `new_data` LONGTEXT DEFAULT NULL,
                  `requested_by` VARCHAR(255) NOT NULL,
                  `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  `status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
                  `approved_by` VARCHAR(255) DEFAULT NULL,
                  `approved_at` DATETIME DEFAULT NULL,
                  `rejection_reason` TEXT DEFAULT NULL,
                  `sql_query` LONGTEXT DEFAULT NULL,
                  `sql_params` LONGTEXT DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");
        } catch (Exception $e) {
            // Ignore schema creation errors
        }
    }
    
    public function interceptQuery($sql, $params) {
        $action_type = 'CREATE';
        if (preg_match('/^\s*INSERT/i', $sql)) {
            $action_type = 'CREATE';
        } elseif (preg_match('/^\s*UPDATE/i', $sql)) {
            $action_type = 'UPDATE';
        } elseif (preg_match('/^\s*DELETE/i', $sql)) {
            $action_type = 'DELETE';
        }
        
        $table_name = '';
        if (preg_match('/(?:FROM|INTO|UPDATE)\s+[`"]?(\w+)[`"]?/i', $sql, $matches)) {
            $table_name = $matches[1];
        }
        
        if ($table_name === 'approval_requests') {
            $this->bypass_interceptor = true;
            $stmt = $this->prepare($sql);
            $res = $stmt->execute($params);
            $this->bypass_interceptor = false;
            return $res;
        }
        
        $module_name = $this->getModuleName($table_name);
        $old_data = null;
        $new_data = null;
        $record_id = null;
        
        $clean_params = [];
        if ($params) {
            foreach ($params as $k => $v) {
                $clean_k = ltrim($k, ':');
                $clean_params[$clean_k] = $v;
            }
        }
        
        $is_positional = false;
        if ($params) {
            $keys = array_keys($params);
            if (is_int($keys[0])) {
                $is_positional = true;
            }
        }
        
        if ($is_positional) {
            if (preg_match('/INSERT\s+INTO\s+[`"]?\w+[`"]?\s*\(([^)]+)\)/i', $sql, $col_matches)) {
                $cols = preg_split('/\s*,\s*/', $col_matches[1]);
                $cols = array_map(function($c) { return trim($c, '`"\''); }, $cols);
                foreach ($cols as $idx => $col) {
                    if (isset($params[$idx])) {
                        $clean_params[$col] = $params[$idx];
                    }
                }
            }
        }
        
        if ($action_type === 'UPDATE' || $action_type === 'DELETE') {
            foreach ($clean_params as $k => $v) {
                if (strtolower($k) === 'id') {
                    $record_id = $v;
                    break;
                }
            }
            if ($record_id === null && !empty($params)) {
                $keys = array_keys($params);
                if (is_int($keys[0])) {
                    if (preg_match('/WHERE\s+id\s*=\s*\?/i', $sql)) {
                        $record_id = end($params);
                    }
                }
            }
            
            if ($record_id) {
                $this->bypass_interceptor = true;
                $stmt = $this->prepare("SELECT * FROM `$table_name` WHERE id = ?");
                $stmt->execute([$record_id]);
                $old_data = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->bypass_interceptor = false;
            }
        }
        
        if ($action_type === 'CREATE') {
            $new_data = $clean_params;
        } elseif ($action_type === 'UPDATE') {
            $new_data = $old_data ? $old_data : [];
            foreach ($clean_params as $k => $v) {
                if ($k !== 'id') {
                    $new_data[$k] = $v;
                }
            }
        }
        
        $this->bypass_interceptor = true;
        $ins = $this->prepare("
            INSERT INTO `approval_requests` 
                (module_name, table_name, institute_prefix, record_id, action_type, old_data, new_data, requested_by, requested_at, status, sql_query, sql_params)
            VALUES
                (:module_name, :table_name, :institute_prefix, :record_id, :action_type, :old_data, :new_data, :requested_by, NOW(), 'Pending', :sql_query, :sql_params)
        ");
        
        $ins->execute([
            ':module_name' => $module_name,
            ':table_name' => $table_name,
            ':institute_prefix' => $_SESSION['institute_prefix'] ?? 'all',
            ':record_id' => $record_id,
            ':action_type' => $action_type,
            ':old_data' => $old_data ? json_encode($old_data, JSON_UNESCAPED_UNICODE) : null,
            ':new_data' => $new_data ? json_encode($new_data, JSON_UNESCAPED_UNICODE) : null,
            ':requested_by' => $_SESSION['username'] ?? 'unknown_admin',
            ':sql_query' => $sql,
            ':sql_params' => $params ? json_encode($params, JSON_UNESCAPED_UNICODE) : null
        ]);
        $this->bypass_interceptor = false;
        
        $_SESSION['approval_message'] = "Your request has been submitted for Super Admin approval.";
        return true;
    }
    
    private function getModuleName($table_name) {
        $clean_table = preg_replace('/^[a-z]+_/', '', $table_name);
        $mappings = [
            'publications' => 'Publications',
            'patent' => 'Patents',
            'patents' => 'Patents',
            'conferences' => 'Conferences',
            'webinars' => 'Webinars',
            'internships' => 'Internships',
            'progress_reports' => 'Progress Reports',
            'infrastructure_facilities' => 'Research Infrastructure',
            'research_areas' => 'Research Areas',
            'collaborations' => 'Collaborations',
            'gallery_events' => 'Gallery Events',
            'gallery_albums' => 'Gallery Albums',
            'gallery_photos' => 'Gallery Photos',
            'events' => 'Event Calendar',
            'team' => 'Team',
            'homepage_banners' => 'Homepage Banners',
            'announcements' => 'Announcements'
        ];
        return $mappings[$clean_table] ?? ucfirst($clean_table);
    }
}
