-- Migration 017: Provider User Role
-- Date: 2026-03-12
-- Description: Adds user_id linkage to providers, adds rate field for provider quotes,
--              and updates SMTP settings for residencial.digital

-- Add user_id column to providers table to link with system users
ALTER TABLE `providers`
ADD COLUMN `user_id` INT(11) DEFAULT NULL COMMENT 'Linked system user with role=proveedor'
AFTER `id`,
ADD INDEX `idx_providers_user_id` (`user_id`);

-- Add rate column to provider_service_requests for provider quotes
ALTER TABLE `provider_service_requests`
ADD COLUMN `rate` DECIMAL(10,2) DEFAULT NULL COMMENT 'Rate set by provider for this service'
AFTER `actual_cost`;

-- Update system_settings with new SMTP configuration for residencial.digital
-- Uses INSERT ... ON DUPLICATE KEY UPDATE to be idempotent
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
  ('email_host',     'residencial.digital'),
  ('email_port',     '465'),
  ('email_user',     'contacto@residencial.digital'),
  ('email_password', 'Danjohn007'),
  ('email_from',     'contacto@residencial.digital'),
  ('email_encryption', 'ssl')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
