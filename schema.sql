-- Complete Database Schema for ANRF-PAIR Website Dynamic CMS
-- Includes core tables, approval workflow, and prefix-based tables for all 7 participating institutes.

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table (Authentication & Access Control)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `institute_prefix` VARCHAR(50) NOT NULL,
  `role` VARCHAR(50) DEFAULT 'admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Team Directory Table
CREATE TABLE IF NOT EXISTS `team` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `designation` VARCHAR(255) NOT NULL,
  `department` VARCHAR(255) DEFAULT NULL,
  `university` VARCHAR(255) DEFAULT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `biography` TEXT DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(255) DEFAULT NULL,
  `linkedin` VARCHAR(500) DEFAULT NULL,
  `google_scholar` VARCHAR(500) DEFAULT NULL,
  `orcid` VARCHAR(100) DEFAULT NULL,
  `research_area` VARCHAR(255) DEFAULT NULL,
  `display_order` INT NOT NULL DEFAULT 10,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Homepage Banners (Carousel) Table
CREATE TABLE IF NOT EXISTS `homepage_banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(500) DEFAULT '',
  `display_order` INT NOT NULL DEFAULT 10,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Announcements ("What's New" news ticker) Table
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(500) NOT NULL,
  `link` VARCHAR(500) DEFAULT '',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Institutional Collaborations & Participating Partners Table
CREATE TABLE IF NOT EXISTS `collaborations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `partner_name` VARCHAR(255) NOT NULL,
  `logo_path` VARCHAR(255) NOT NULL,
  `profile_description` TEXT DEFAULT NULL,
  `collab_type` ENUM('Academic', 'Research', 'Industry') NOT NULL DEFAULT 'Academic',
  `website_url` VARCHAR(255) DEFAULT '',
  `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all',
  `display_order` INT NOT NULL DEFAULT 10,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Research Areas Table
CREATE TABLE IF NOT EXISTS `research_areas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `display_order` INT NOT NULL DEFAULT 10,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Infrastructure Facilities Table
CREATE TABLE IF NOT EXISTS `infrastructure_facilities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `equipment_details` TEXT DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all',
  `display_order` INT NOT NULL DEFAULT 10,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Gallery Albums Table
CREATE TABLE IF NOT EXISTS `gallery_albums` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `album_name` VARCHAR(255) NOT NULL,
  `album_date` DATE DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `institute_prefix` VARCHAR(50) NOT NULL DEFAULT 'all',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. Gallery Photos Table (Relational Child of Albums)
CREATE TABLE IF NOT EXISTS `gallery_photos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `album_id` INT NOT NULL,
  `photo_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_gallery_photos_album` FOREIGN KEY (`album_id`) REFERENCES `gallery_albums` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 10. Approval Requests Table (Central Hub Moderation Log)
CREATE TABLE IF NOT EXISTS `approval_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `module_name` VARCHAR(255) NOT NULL,
  `table_name` VARCHAR(255) NOT NULL,
  `institute_prefix` VARCHAR(50) NOT NULL,
  `record_id` INT DEFAULT NULL,
  `action_type` VARCHAR(50) NOT NULL,
  `old_data` LONGTEXT DEFAULT NULL,
  `new_data` LONGTEXT DEFAULT NULL,
  `sql_query` LONGTEXT DEFAULT NULL,
  `sql_params` LONGTEXT DEFAULT NULL,
  `requested_by` VARCHAR(255) NOT NULL,
  `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
  `approved_by` VARCHAR(255) DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `rejection_reason` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Institute Tables for Prefix: cuk
CREATE TABLE IF NOT EXISTS `cuk_webinars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `speaker_name` VARCHAR(255) NOT NULL,
  `affiliation` VARCHAR(255) DEFAULT NULL,
  `webinar_date` DATETIME NOT NULL,
  `link` VARCHAR(1000) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_cuk_webinar_date` (`webinar_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cuk_conferences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `organizer` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `submission_deadline` DATE DEFAULT NULL,
  `website_url` VARCHAR(1000) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_cuk_conf_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cuk_internships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `project_investigator` VARCHAR(255) DEFAULT NULL,
  `no_students_trained` INT DEFAULT 0,
  `students_names` TEXT DEFAULT NULL,
  `no_days_trained` INT DEFAULT 0,
  `content` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cuk_patents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `patent_id` VARCHAR(100) DEFAULT NULL,
  `patent_title` VARCHAR(500) NOT NULL,
  `inventor_name` VARCHAR(255) DEFAULT NULL,
  `co_inventors` TEXT DEFAULT NULL,
  `application_no` VARCHAR(100) DEFAULT NULL,
  `patent_no` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `filing_date` DATE DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `grant_date` DATE DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Filed',
  `technology_area` VARCHAR(255) DEFAULT NULL,
  `abstract` TEXT DEFAULT NULL,
  `patent_file` VARCHAR(255) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cuk_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) DEFAULT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `cuk_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(255) DEFAULT NULL,
  `impact_factor` DECIMAL(5,2) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Institute Tables for Prefix: kannur
CREATE TABLE IF NOT EXISTS `kannur_webinars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `speaker_name` VARCHAR(255) NOT NULL,
  `affiliation` VARCHAR(255) DEFAULT NULL,
  `webinar_date` DATETIME NOT NULL,
  `link` VARCHAR(1000) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_kannur_webinar_date` (`webinar_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kannur_conferences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `organizer` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `submission_deadline` DATE DEFAULT NULL,
  `website_url` VARCHAR(1000) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_kannur_conf_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kannur_internships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `project_investigator` VARCHAR(255) DEFAULT NULL,
  `no_students_trained` INT DEFAULT 0,
  `students_names` TEXT DEFAULT NULL,
  `no_days_trained` INT DEFAULT 0,
  `content` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kannur_patents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `patent_id` VARCHAR(100) DEFAULT NULL,
  `patent_title` VARCHAR(500) NOT NULL,
  `inventor_name` VARCHAR(255) DEFAULT NULL,
  `co_inventors` TEXT DEFAULT NULL,
  `application_no` VARCHAR(100) DEFAULT NULL,
  `patent_no` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `filing_date` DATE DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `grant_date` DATE DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Filed',
  `technology_area` VARCHAR(255) DEFAULT NULL,
  `abstract` TEXT DEFAULT NULL,
  `patent_file` VARCHAR(255) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kannur_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) DEFAULT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `kannur_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(255) DEFAULT NULL,
  `impact_factor` DECIMAL(5,2) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Institute Tables for Prefix: mgu
CREATE TABLE IF NOT EXISTS `mgu_webinars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `speaker_name` VARCHAR(255) NOT NULL,
  `affiliation` VARCHAR(255) DEFAULT NULL,
  `webinar_date` DATETIME NOT NULL,
  `link` VARCHAR(1000) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_mgu_webinar_date` (`webinar_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mgu_conferences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `organizer` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `submission_deadline` DATE DEFAULT NULL,
  `website_url` VARCHAR(1000) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_mgu_conf_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mgu_internships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `project_investigator` VARCHAR(255) DEFAULT NULL,
  `no_students_trained` INT DEFAULT 0,
  `students_names` TEXT DEFAULT NULL,
  `no_days_trained` INT DEFAULT 0,
  `content` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mgu_patents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `patent_id` VARCHAR(100) DEFAULT NULL,
  `patent_title` VARCHAR(500) NOT NULL,
  `inventor_name` VARCHAR(255) DEFAULT NULL,
  `co_inventors` TEXT DEFAULT NULL,
  `application_no` VARCHAR(100) DEFAULT NULL,
  `patent_no` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `filing_date` DATE DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `grant_date` DATE DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Filed',
  `technology_area` VARCHAR(255) DEFAULT NULL,
  `abstract` TEXT DEFAULT NULL,
  `patent_file` VARCHAR(255) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mgu_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) DEFAULT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mgu_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(255) DEFAULT NULL,
  `impact_factor` DECIMAL(5,2) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Institute Tables for Prefix: ou
CREATE TABLE IF NOT EXISTS `ou_webinars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `speaker_name` VARCHAR(255) NOT NULL,
  `affiliation` VARCHAR(255) DEFAULT NULL,
  `webinar_date` DATETIME NOT NULL,
  `link` VARCHAR(1000) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_ou_webinar_date` (`webinar_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ou_conferences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `organizer` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `submission_deadline` DATE DEFAULT NULL,
  `website_url` VARCHAR(1000) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_ou_conf_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ou_internships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `project_investigator` VARCHAR(255) DEFAULT NULL,
  `no_students_trained` INT DEFAULT 0,
  `students_names` TEXT DEFAULT NULL,
  `no_days_trained` INT DEFAULT 0,
  `content` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ou_patents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `patent_id` VARCHAR(100) DEFAULT NULL,
  `patent_title` VARCHAR(500) NOT NULL,
  `inventor_name` VARCHAR(255) DEFAULT NULL,
  `co_inventors` TEXT DEFAULT NULL,
  `application_no` VARCHAR(100) DEFAULT NULL,
  `patent_no` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `filing_date` DATE DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `grant_date` DATE DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Filed',
  `technology_area` VARCHAR(255) DEFAULT NULL,
  `abstract` TEXT DEFAULT NULL,
  `patent_file` VARCHAR(255) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ou_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) DEFAULT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ou_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(255) DEFAULT NULL,
  `impact_factor` DECIMAL(5,2) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Institute Tables for Prefix: svu
CREATE TABLE IF NOT EXISTS `svu_webinars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `speaker_name` VARCHAR(255) NOT NULL,
  `affiliation` VARCHAR(255) DEFAULT NULL,
  `webinar_date` DATETIME NOT NULL,
  `link` VARCHAR(1000) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_svu_webinar_date` (`webinar_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `svu_conferences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `organizer` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `submission_deadline` DATE DEFAULT NULL,
  `website_url` VARCHAR(1000) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_svu_conf_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `svu_internships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `project_investigator` VARCHAR(255) DEFAULT NULL,
  `no_students_trained` INT DEFAULT 0,
  `students_names` TEXT DEFAULT NULL,
  `no_days_trained` INT DEFAULT 0,
  `content` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `svu_patents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `patent_id` VARCHAR(100) DEFAULT NULL,
  `patent_title` VARCHAR(500) NOT NULL,
  `inventor_name` VARCHAR(255) DEFAULT NULL,
  `co_inventors` TEXT DEFAULT NULL,
  `application_no` VARCHAR(100) DEFAULT NULL,
  `patent_no` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `filing_date` DATE DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `grant_date` DATE DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Filed',
  `technology_area` VARCHAR(255) DEFAULT NULL,
  `abstract` TEXT DEFAULT NULL,
  `patent_file` VARCHAR(255) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `svu_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) DEFAULT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `svu_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(255) DEFAULT NULL,
  `impact_factor` DECIMAL(5,2) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Institute Tables for Prefix: uoh
CREATE TABLE IF NOT EXISTS `uoh_webinars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `speaker_name` VARCHAR(255) NOT NULL,
  `affiliation` VARCHAR(255) DEFAULT NULL,
  `webinar_date` DATETIME NOT NULL,
  `link` VARCHAR(1000) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_uoh_webinar_date` (`webinar_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `uoh_conferences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `organizer` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `submission_deadline` DATE DEFAULT NULL,
  `website_url` VARCHAR(1000) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_uoh_conf_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `uoh_internships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `project_investigator` VARCHAR(255) DEFAULT NULL,
  `no_students_trained` INT DEFAULT 0,
  `students_names` TEXT DEFAULT NULL,
  `no_days_trained` INT DEFAULT 0,
  `content` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `uoh_patents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `patent_id` VARCHAR(100) DEFAULT NULL,
  `patent_title` VARCHAR(500) NOT NULL,
  `inventor_name` VARCHAR(255) DEFAULT NULL,
  `co_inventors` TEXT DEFAULT NULL,
  `application_no` VARCHAR(100) DEFAULT NULL,
  `patent_no` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `filing_date` DATE DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `grant_date` DATE DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Filed',
  `technology_area` VARCHAR(255) DEFAULT NULL,
  `abstract` TEXT DEFAULT NULL,
  `patent_file` VARCHAR(255) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `uoh_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) DEFAULT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `uoh_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(255) DEFAULT NULL,
  `impact_factor` DECIMAL(5,2) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Institute Tables for Prefix: yvu
CREATE TABLE IF NOT EXISTS `yvu_webinars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `speaker_name` VARCHAR(255) NOT NULL,
  `affiliation` VARCHAR(255) DEFAULT NULL,
  `webinar_date` DATETIME NOT NULL,
  `link` VARCHAR(1000) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_yvu_webinar_date` (`webinar_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `yvu_conferences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `organizer` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `submission_deadline` DATE DEFAULT NULL,
  `website_url` VARCHAR(1000) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_yvu_conf_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `yvu_internships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `project_investigator` VARCHAR(255) DEFAULT NULL,
  `no_students_trained` INT DEFAULT 0,
  `students_names` TEXT DEFAULT NULL,
  `no_days_trained` INT DEFAULT 0,
  `content` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `yvu_patents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `patent_id` VARCHAR(100) DEFAULT NULL,
  `patent_title` VARCHAR(500) NOT NULL,
  `inventor_name` VARCHAR(255) DEFAULT NULL,
  `co_inventors` TEXT DEFAULT NULL,
  `application_no` VARCHAR(100) DEFAULT NULL,
  `patent_no` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `filing_date` DATE DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `grant_date` DATE DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Filed',
  `technology_area` VARCHAR(255) DEFAULT NULL,
  `abstract` TEXT DEFAULT NULL,
  `patent_file` VARCHAR(255) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `yvu_progress_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `project_title` VARCHAR(500) NOT NULL,
  `pi_name` VARCHAR(255) DEFAULT NULL,
  `co_pi_name` VARCHAR(255) DEFAULT NULL,
  `work_package_no` VARCHAR(100) DEFAULT NULL,
  `approved_objects` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `summary_progress` TEXT DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `yvu_publications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_no` VARCHAR(50) DEFAULT NULL,
  `publication_title` VARCHAR(500) NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `doi_number` VARCHAR(255) DEFAULT NULL,
  `publication_date` DATE DEFAULT NULL,
  `publication_journal` VARCHAR(255) DEFAULT NULL,
  `impact_factor` DECIMAL(5,2) DEFAULT NULL,
  `approval_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Approved',
  `publish_status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed Sample Data for Announcements
INSERT INTO `announcements` (`title`, `link`, `is_active`) VALUES
('📢 Webinar on SMART NANO BIOSENSORS – May 20, 2026', 'webinars.php', 1),
('🎓 Osmania University Education Week: May 11–17, 2026', 'conferences.php', 1),
('📸 New Event Photos Uploaded in Gallery', 'gallery.php', 1);

-- Seed Sample Data for Research Areas
INSERT INTO `research_areas` (`title`, `description`, `display_order`) VALUES
('Biomedical Devices & Sensors', 'Focuses on building next-generation diagnostic sensors and medical edge devices.', 1),
('Healthcare IoT Systems', 'Deploys localized IoT mesh networks to record patient data securely.', 2),
('AI-Driven Clinical Diagnostics', 'Researches CNN and transformer model structures to analyze medical imagery.', 3);

-- Seed Initial Users Accounts with bcrypt passwords
INSERT INTO `users` (`username`, `password`, `institute_prefix`, `role`) VALUES 
('admin@uoh.ac.in', '$2y$10$uAVLx4PgW.5RZcmF0JhNFOnb00aBO2dnDtDVg1YPR6uEmkqJroQIa', 'uoh', 'admin'),
('superadmin@uoh.ac.in', '$2y$10$S9JHjM62u1cOsBZPNXicSO3cfBBx8f9srFH/vpddbAByzA6UnUIsu', 'uoh', 'super_admin'),
('Idsathyan@cuk.ac.in', '$2y$10$DNPJpCLLjlLs6HQ8vKZi7eUtMyhhF45pQop2KP7ZBkK7KOV8w6x2m', 'cuk', 'admin'),
('anupkesavan@kannuriuniv.ac.in', '$2y$10$kuI3GAnV1GBFPWx4hUm4vOTsxfLrSn9HR9zMjm4i.mxaD/PbZka4S', 'kannur', 'admin'),
('radhakrishnanek@mgu.ac.in', '$2y$10$hqvpHlGx9o8YLd9OQdurqOjLICwLHeKzz7L.pGyNm7UneH3DsUWSe', 'mgu', 'admin'),
('vijjulatha@osmania.ac.in', '$2y$10$I9/19JJzDD.iPqH8HEPza.p32LQKke13kPrBF5BSNgebNqP5wMUlS', 'ou', 'admin'),
('balaji.meriga@gmail.com', '$2y$10$OJExnJmwt3QeL6Hn5ilCY.R4jEZT7oZSXEVRQPP8.U4X0XdLljYJW', 'svu', 'admin'),
('sarma7@yogivemanauniversity.ac.in', '$2y$10$Qzia0YHgEYXzLzkwjrFwv.H.X0lLKWD9O7l7Wl2Epqt/BgOe3Cdam', 'yvu', 'admin')
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`), `role` = VALUES(`role`);
