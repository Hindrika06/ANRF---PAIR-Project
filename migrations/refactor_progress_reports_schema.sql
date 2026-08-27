-- ============================================================================
-- ANRF-PAIR CMS: MIGRATION SCRIPT FOR PROGRESS REPORTS WORKFLOW REFACTORING
-- Purpose: Ensure UNIQUE(task_no) constraint and proper indexes across all 7 institute progress report tables.
-- Safe & Idempotent: Can be executed multiple times safely.
-- ============================================================================

-- 1. Central University of Karnataka (cuk)
ALTER TABLE `cuk_progress_reports` MODIFY COLUMN `task_no` VARCHAR(50) NOT NULL;
ALTER TABLE `cuk_progress_reports` ADD CONSTRAINT `uk_cuk_task_no` UNIQUE (`task_no`);

-- 2. Kannur University (kannur)
ALTER TABLE `kannur_progress_reports` MODIFY COLUMN `task_no` VARCHAR(50) NOT NULL;
ALTER TABLE `kannur_progress_reports` ADD CONSTRAINT `uk_kannur_task_no` UNIQUE (`task_no`);

-- 3. Mahatma Gandhi University (mgu)
ALTER TABLE `mgu_progress_reports` MODIFY COLUMN `task_no` VARCHAR(50) NOT NULL;
ALTER TABLE `mgu_progress_reports` ADD CONSTRAINT `uk_mgu_task_no` UNIQUE (`task_no`);

-- 4. Osmania University (ou)
ALTER TABLE `ou_progress_reports` MODIFY COLUMN `task_no` VARCHAR(50) NOT NULL;
ALTER TABLE `ou_progress_reports` ADD CONSTRAINT `uk_ou_task_no` UNIQUE (`task_no`);

-- 5. Sri Venkateswara University (svu)
ALTER TABLE `svu_progress_reports` MODIFY COLUMN `task_no` VARCHAR(50) NOT NULL;
ALTER TABLE `svu_progress_reports` ADD CONSTRAINT `uk_svu_task_no` UNIQUE (`task_no`);

-- 6. University of Hyderabad (uoh)
ALTER TABLE `uoh_progress_reports` MODIFY COLUMN `task_no` VARCHAR(50) NOT NULL;
ALTER TABLE `uoh_progress_reports` ADD CONSTRAINT `uk_uoh_task_no` UNIQUE (`task_no`);

-- 7. Yogi Vemana University (yvu)
ALTER TABLE `yvu_progress_reports` MODIFY COLUMN `task_no` VARCHAR(50) NOT NULL;
ALTER TABLE `yvu_progress_reports` ADD CONSTRAINT `uk_yvu_task_no` UNIQUE (`task_no`);
