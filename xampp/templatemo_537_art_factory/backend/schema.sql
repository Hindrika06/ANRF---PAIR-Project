-- MySQL schema for ACTIVAURA backend
-- Create database (run once)
CREATE DATABASE IF NOT EXISTS `activaura_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `activaura_db`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nutrition profiles (each save creates a record snapshot)
CREATE TABLE IF NOT EXISTS `nutrition_profiles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `age` INT DEFAULT NULL,
  `gender` VARCHAR(20) DEFAULT NULL,
  `region` VARCHAR(30) DEFAULT NULL,
  `goal` VARCHAR(30) DEFAULT NULL,
  `diet` VARCHAR(20) DEFAULT NULL,
  `concerns` VARCHAR(255) DEFAULT NULL,
  `height_cm` DECIMAL(6,2) DEFAULT NULL,
  `weight_kg` DECIMAL(6,2) DEFAULT NULL,
  `bmi` DECIMAL(5,2) DEFAULT NULL,
  `calories` INT DEFAULT NULL,
  `carbs_g` INT DEFAULT NULL,
  `protein_g` INT DEFAULT NULL,
  `fat_g` INT DEFAULT NULL,
  `plan_json` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nutrition_user` (`user_id`),
  CONSTRAINT `fk_nutrition_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Workout plans
CREATE TABLE IF NOT EXISTS `workout_plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `goal` VARCHAR(40) DEFAULT NULL,
  `level` VARCHAR(20) DEFAULT NULL,
  `minutes` INT DEFAULT NULL,
  `equipment` VARCHAR(40) DEFAULT NULL,
  `bodypart` VARCHAR(40) DEFAULT NULL,
  `plan_json` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_workout_user` (`user_id`),
  CONSTRAINT `fk_workout_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Task logs
CREATE TABLE IF NOT EXISTS `task_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `tasks_json` JSON NOT NULL,
  `progress_percent` INT DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_task_user` (`user_id`),
  CONSTRAINT `fk_task_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



