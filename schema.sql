-- Database Schema for ANRF-PAIR Website Dynamic CMS
-- Includes tables for users, team directory, homepage banners, announcements, institutional collaborations, research areas, infrastructure, and gallery albums.

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table (Authentication)
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

SET FOREIGN_KEY_CHECKS = 1;

-- Seed Sample Data for Announcements
INSERT INTO `announcements` (`title`, `link`, `is_active`) VALUES
('📢 Webinar on SMART NANO BIOSENSORS – May 20, 2026', 'event-detail.html', 1),
('🎓 Osmania University Education Week: May 11–17, 2026', 'events_activities.php', 1),
('📸 New Event Photos Uploaded in Gallery', 'gallery.php', 1);

-- Seed Sample Data for Research Areas
INSERT INTO `research_areas` (`title`, `description`, `display_order`) VALUES
('Biomedical Devices & Sensors', 'Focuses on building next-generation diagnostic sensors and medical edge devices.', 1),
('Healthcare IoT Systems', 'Deploys localized IoT mesh networks to record patient data securely.', 2),
('AI-Driven Clinical Diagnostics', 'Researches CNN and transformer model structures to analyze medical imagery.', 3);

-- 10. Template for prefix-based Webinars Table (e.g., `cuk_webinars`)
-- Replace {prefix} with cuk, kannur, mgu, ou, svu, uoh, or yvu
CREATE TABLE IF NOT EXISTS `cuk_webinars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `taskno` VARCHAR(50) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `speaker_name` VARCHAR(255) NOT NULL,
  `affiliation` VARCHAR(255) DEFAULT NULL,
  `webinar_date` DATETIME NOT NULL,
  `link` VARCHAR(1000) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_webinar_date` (`webinar_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 11. Template for prefix-based Conferences Table (e.g., `cuk_conferences`)
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
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_conf_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
