-- ============================================================================
-- ANRF-PAIR WEBSITE CMS: SAFE IDEMPOTENT PRODUCTION MIGRATION SCRIPT (FIXED)
-- Purpose: Synchronize production database schema with local application code
-- Target Environment: GoDaddy MySQL / MariaDB (https://www.anrfpairuoh.com/)
-- Data Safety: 100% Data-Safe (No DROP TABLE, No TRUNCATE, No DELETE, No Data Overwrite)
-- Idempotency: Safe to run multiple times without causing syntax or duplicate column errors.
-- ============================================================================

DELIMITER $$

-- 1. Helper Procedure to Add Columns Idempotently
DROP PROCEDURE IF EXISTS AddColumnIfNotExists$$
CREATE PROCEDURE AddColumnIfNotExists(
    IN p_tableName VARCHAR(64),
    IN p_columnName VARCHAR(64),
    IN p_columnDef TEXT
)
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.TABLES
        WHERE table_schema = DATABASE()
          AND table_name = p_tableName
    ) THEN
        IF NOT EXISTS (
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE table_schema = DATABASE()
              AND table_name = p_tableName
              AND column_name = p_columnName
        ) THEN
            SET @sql = CONCAT('ALTER TABLE `', p_tableName, '` ADD COLUMN `', p_columnName, '` ', p_columnDef);
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- STEP 1: ENSURE MAIN PROGRESS REPORTS TABLES (7 PREFIXES)
-- Prefixes: cuk, kannur, mgu, ou, svu, uoh, yvu
-- ============================================================================

CREATE TABLE IF NOT EXISTS `cuk_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) NOT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) NOT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kannur_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) NOT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) NOT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mgu_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) NOT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) NOT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ou_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) NOT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) NOT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `svu_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) NOT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) NOT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `uoh_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) NOT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) NOT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `yvu_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) NOT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) NOT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `interns_trained_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Safely ensure columns exist if tables were created from an older baseline
CALL AddColumnIfNotExists('cuk_progress_reports', 'work_package_no', 'VARCHAR(100) DEFAULT NULL AFTER `task_no`');
CALL AddColumnIfNotExists('cuk_progress_reports', 'interns_trained_count', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`');

CALL AddColumnIfNotExists('kannur_progress_reports', 'work_package_no', 'VARCHAR(100) DEFAULT NULL AFTER `task_no`');
CALL AddColumnIfNotExists('kannur_progress_reports', 'interns_trained_count', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`');

CALL AddColumnIfNotExists('mgu_progress_reports', 'work_package_no', 'VARCHAR(100) DEFAULT NULL AFTER `task_no`');
CALL AddColumnIfNotExists('mgu_progress_reports', 'interns_trained_count', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`');

CALL AddColumnIfNotExists('ou_progress_reports', 'work_package_no', 'VARCHAR(100) DEFAULT NULL AFTER `task_no`');
CALL AddColumnIfNotExists('ou_progress_reports', 'interns_trained_count', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`');

CALL AddColumnIfNotExists('svu_progress_reports', 'work_package_no', 'VARCHAR(100) DEFAULT NULL AFTER `task_no`');
CALL AddColumnIfNotExists('svu_progress_reports', 'interns_trained_count', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`');

CALL AddColumnIfNotExists('uoh_progress_reports', 'work_package_no', 'VARCHAR(100) DEFAULT NULL AFTER `task_no`');
CALL AddColumnIfNotExists('uoh_progress_reports', 'interns_trained_count', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`');

CALL AddColumnIfNotExists('yvu_progress_reports', 'work_package_no', 'VARCHAR(100) DEFAULT NULL AFTER `task_no`');
CALL AddColumnIfNotExists('yvu_progress_reports', 'interns_trained_count', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `summary_progress`');


-- ============================================================================
-- STEP 2: CREATE PROGRESS REPORT PUBLICATIONS CHILD TABLES (7 PREFIXES)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `cuk_progress_report_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(300) NOT NULL,
  `impact_factor` DECIMAL(6,3) DEFAULT NULL,
  `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_id` (`progress_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kannur_progress_report_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(300) NOT NULL,
  `impact_factor` DECIMAL(6,3) DEFAULT NULL,
  `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_id` (`progress_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mgu_progress_report_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(300) NOT NULL,
  `impact_factor` DECIMAL(6,3) DEFAULT NULL,
  `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_id` (`progress_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ou_progress_report_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(300) NOT NULL,
  `impact_factor` DECIMAL(6,3) DEFAULT NULL,
  `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_id` (`progress_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `svu_progress_report_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(300) NOT NULL,
  `impact_factor` DECIMAL(6,3) DEFAULT NULL,
  `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_id` (`progress_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `uoh_progress_report_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(300) NOT NULL,
  `impact_factor` DECIMAL(6,3) DEFAULT NULL,
  `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_id` (`progress_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `yvu_progress_report_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(300) NOT NULL,
  `impact_factor` DECIMAL(6,3) DEFAULT NULL,
  `approval_status` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_id` (`progress_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ============================================================================
-- STEP 3: CREATE CAPACITY BUILDING EVENTS CHILD TABLES (7 PREFIXES)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `cuk_progress_report_capacity_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `category` ENUM('Workshop_Conference', 'Training_Program') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `event_date` DATE DEFAULT NULL,
  `duration` VARCHAR(100) DEFAULT NULL,
  `venue_mode` VARCHAR(255) DEFAULT NULL,
  `organizing_institution` VARCHAR(255) DEFAULT NULL,
  `participant_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_events_id` (`progress_report_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kannur_progress_report_capacity_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `category` ENUM('Workshop_Conference', 'Training_Program') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `event_date` DATE DEFAULT NULL,
  `duration` VARCHAR(100) DEFAULT NULL,
  `venue_mode` VARCHAR(255) DEFAULT NULL,
  `organizing_institution` VARCHAR(255) DEFAULT NULL,
  `participant_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_events_id` (`progress_report_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mgu_progress_report_capacity_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `category` ENUM('Workshop_Conference', 'Training_Program') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `event_date` DATE DEFAULT NULL,
  `duration` VARCHAR(100) DEFAULT NULL,
  `venue_mode` VARCHAR(255) DEFAULT NULL,
  `organizing_institution` VARCHAR(255) DEFAULT NULL,
  `participant_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_events_id` (`progress_report_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ou_progress_report_capacity_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `category` ENUM('Workshop_Conference', 'Training_Program') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `event_date` DATE DEFAULT NULL,
  `duration` VARCHAR(100) DEFAULT NULL,
  `venue_mode` VARCHAR(255) DEFAULT NULL,
  `organizing_institution` VARCHAR(255) DEFAULT NULL,
  `participant_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_events_id` (`progress_report_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `svu_progress_report_capacity_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `category` ENUM('Workshop_Conference', 'Training_Program') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `event_date` DATE DEFAULT NULL,
  `duration` VARCHAR(100) DEFAULT NULL,
  `venue_mode` VARCHAR(255) DEFAULT NULL,
  `organizing_institution` VARCHAR(255) DEFAULT NULL,
  `participant_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_events_id` (`progress_report_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `uoh_progress_report_capacity_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `category` ENUM('Workshop_Conference', 'Training_Program') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `event_date` DATE DEFAULT NULL,
  `duration` VARCHAR(100) DEFAULT NULL,
  `venue_mode` VARCHAR(255) DEFAULT NULL,
  `organizing_institution` VARCHAR(255) DEFAULT NULL,
  `participant_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_events_id` (`progress_report_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `yvu_progress_report_capacity_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `progress_report_id` INT NOT NULL,
  `category` ENUM('Workshop_Conference', 'Training_Program') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `event_date` DATE DEFAULT NULL,
  `duration` VARCHAR(100) DEFAULT NULL,
  `venue_mode` VARCHAR(255) DEFAULT NULL,
  `organizing_institution` VARCHAR(255) DEFAULT NULL,
  `participant_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pr_events_id` (`progress_report_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ============================================================================
-- STEP 4: ENSURE SYSTEM & HOMEPAGE TABLES
-- ============================================================================

-- Ensure website_visitors table exists
CREATE TABLE IF NOT EXISTS `website_visitors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL UNIQUE,
  `first_visit_at` DATETIME NOT NULL,
  `last_visit_at` DATETIME NOT NULL,
  `visit_count` INT NOT NULL DEFAULT 1,
  INDEX `idx_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ensure homepage_banners table exists
CREATE TABLE IF NOT EXISTS `homepage_banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `short_description` TEXT DEFAULT NULL,
  `target_url` VARCHAR(1000) DEFAULT '',
  `start_datetime` DATETIME DEFAULT NULL,
  `end_datetime` DATETIME DEFAULT NULL,
  `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all',
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(500) DEFAULT '',
  `display_order` INT NOT NULL DEFAULT 10,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_status_dates` (`status`, `start_datetime`, `end_datetime`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Safely ensure columns exist if homepage_banners was created from an older baseline
CALL AddColumnIfNotExists('homepage_banners', 'title', 'VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `id`');
CALL AddColumnIfNotExists('homepage_banners', 'short_description', 'TEXT DEFAULT NULL AFTER `title`');
CALL AddColumnIfNotExists('homepage_banners', 'target_url', 'VARCHAR(1000) DEFAULT \'\' AFTER `short_description`');
CALL AddColumnIfNotExists('homepage_banners', 'start_datetime', 'DATETIME DEFAULT NULL AFTER `target_url`');
CALL AddColumnIfNotExists('homepage_banners', 'end_datetime', 'DATETIME DEFAULT NULL AFTER `start_datetime`');
CALL AddColumnIfNotExists('homepage_banners', 'institute_prefix', 'VARCHAR(50) NOT NULL DEFAULT \'all\' AFTER `end_datetime`');

-- Clean up helper procedure
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;
