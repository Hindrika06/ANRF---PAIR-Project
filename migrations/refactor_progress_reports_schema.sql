-- ============================================================================
-- ANRF-PAIR CMS: PROGRESS REPORTS SCHEMA AUDIT & INDEX MIGRATION SCRIPT
-- Status: 100% Data-Safe & Idempotent (No UNIQUE constraint, No Data Mutation)
-- ============================================================================
-- Existing schema tables already contain:
--   - Primary Key: `id` (INT AUTO_INCREMENT)
--   - Business Identifier: `task_no` (VARCHAR(50))
--   - Status: `approval_status` (ENUM('Pending','Approved','Rejected'))
--
-- No UNIQUE(task_no) constraint is added because historical data contains duplicate Task IDs.
-- The query logic uses progress_report.id as the technical foreign key context.
-- ============================================================================

-- Optional non-unique performance indexes for task_no lookups:
ALTER TABLE `cuk_progress_reports` ADD INDEX `idx_cuk_task_no` (`task_no`);
ALTER TABLE `kannur_progress_reports` ADD INDEX `idx_kannur_task_no` (`task_no`);
ALTER TABLE `mgu_progress_reports` ADD INDEX `idx_mgu_task_no` (`task_no`);
ALTER TABLE `ou_progress_reports` ADD INDEX `idx_ou_task_no` (`task_no`);
ALTER TABLE `svu_progress_reports` ADD INDEX `idx_svu_task_no` (`task_no`);
ALTER TABLE `uoh_progress_reports` ADD INDEX `idx_uoh_task_no` (`task_no`);
ALTER TABLE `yvu_progress_reports` ADD INDEX `idx_yvu_task_no` (`task_no`);
