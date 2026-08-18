-- =============================================================
-- Migration Script: hris_hub -> hr_selfservice (with hrhub_ prefix)
-- Date: 2026-08-18
-- Description: Memindahkan semua tabel dari database hris_hub
--              ke database hr_selfservice dengan prefix hrhub_
-- =============================================================

-- 1. Buat database baru
CREATE DATABASE IF NOT EXISTS `hr_selfservice`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2. Rename semua tabel dari hris_hub ke hr_selfservice dengan prefix hrhub_
-- NOTE: Pastikan tidak ada koneksi aktif ke database hris_hub saat menjalankan script ini

RENAME TABLE
  `hris_hub`.`users`                        TO `hr_selfservice`.`hrhub_users`,
  `hris_hub`.`roles`                        TO `hr_selfservice`.`hrhub_roles`,
  `hris_hub`.`role_permissions`             TO `hr_selfservice`.`hrhub_role_permissions`,
  `hris_hub`.`access_modules`               TO `hr_selfservice`.`hrhub_access_modules`,
  `hris_hub`.`api_keys`                     TO `hr_selfservice`.`hrhub_api_keys`,
  `hris_hub`.`master_user_types`            TO `hr_selfservice`.`hrhub_master_user_types`,
  `hris_hub`.`tickets`                      TO `hr_selfservice`.`hrhub_tickets`,
  `hris_hub`.`ticket_comments`              TO `hr_selfservice`.`hrhub_ticket_comments`,
  `hris_hub`.`ticket_attachments`           TO `hr_selfservice`.`hrhub_ticket_attachments`,
  `hris_hub`.`projects`                     TO `hr_selfservice`.`hrhub_projects`,
  `hris_hub`.`approval_requests`            TO `hr_selfservice`.`hrhub_approval_requests`,
  `hris_hub`.`project_comments`             TO `hr_selfservice`.`hrhub_project_comments`,
  `hris_hub`.`project_attachments`          TO `hr_selfservice`.`hrhub_project_attachments`,
  `hris_hub`.`project_user_types`           TO `hr_selfservice`.`hrhub_project_user_types`,
  `hris_hub`.`master_projects`              TO `hr_selfservice`.`hrhub_master_projects`,
  `hris_hub`.`modules`                      TO `hr_selfservice`.`hrhub_modules`,
  `hris_hub`.`pages`                        TO `hr_selfservice`.`hrhub_pages`,
  `hris_hub`.`module_blueprint_modules`     TO `hr_selfservice`.`hrhub_module_blueprint_modules`,
  `hris_hub`.`blueprints`                   TO `hr_selfservice`.`hrhub_blueprints`,
  `hris_hub`.`blueprint_modules`            TO `hr_selfservice`.`hrhub_blueprint_modules`,
  `hris_hub`.`blueprint_business_scenarios` TO `hr_selfservice`.`hrhub_blueprint_business_scenarios`,
  `hris_hub`.`blueprint_design_pages`       TO `hr_selfservice`.`hrhub_blueprint_design_pages`,
  `hris_hub`.`blueprint_page_specifications` TO `hr_selfservice`.`hrhub_blueprint_page_specifications`,
  `hris_hub`.`blueprint_approval_requests`  TO `hr_selfservice`.`hrhub_blueprint_approval_requests`,
  `hris_hub`.`blueprint_comments`           TO `hr_selfservice`.`hrhub_blueprint_comments`,
  `hris_hub`.`blueprint_attachments`        TO `hr_selfservice`.`hrhub_blueprint_attachments`,
  `hris_hub`.`audit_logs`                   TO `hr_selfservice`.`hrhub_audit_logs`;

-- 3. Pindahkan tabel migrations (tetap tanpa prefix, internal CI4)
RENAME TABLE
  `hris_hub`.`migrations`                   TO `hr_selfservice`.`migrations`;

-- =============================================================
-- SELESAI. Setelah script ini dijalankan:
-- 1. Update api/.env: database.default.database = hr_selfservice
-- 2. Jalankan aplikasi dan verifikasi
-- =============================================================
