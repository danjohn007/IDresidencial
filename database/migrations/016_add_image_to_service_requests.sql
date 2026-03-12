-- Migration: Add image field to provider_service_requests
-- Date: 2026-03-12
-- Description: Adds image_path column to allow residents to attach images to service requests

ALTER TABLE `provider_service_requests`
ADD COLUMN `image_path` VARCHAR(255) DEFAULT NULL COMMENT 'Optional image attachment for service request'
AFTER `notes`;
