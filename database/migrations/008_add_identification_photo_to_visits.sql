-- Migration: Add identification_photo column to visits table
-- Date: 2024-11-25
-- Description: Stores visitor's ID photo taken during validation for security purposes

ALTER TABLE `visits` 
ADD COLUMN `identification_photo` VARCHAR(255) NULL COMMENT 'Path to visitor identification photo' AFTER `vehicle_plate`;

-- Add index for photo lookups
CREATE INDEX `idx_identification_photo` ON `visits` (`identification_photo`);
