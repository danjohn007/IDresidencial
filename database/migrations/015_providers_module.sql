-- Migration 015: Providers (Proveedores) Module
-- Creates tables for maintenance providers and service requests

-- Providers catalog
CREATE TABLE IF NOT EXISTS `providers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_name` VARCHAR(150) NOT NULL,
    `contact_name` VARCHAR(150) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `category` VARCHAR(100) DEFAULT NULL COMMENT 'e.g. Plomería, Electricidad, Jardinería',
    `address` TEXT DEFAULT NULL,
    `rfc` VARCHAR(20) DEFAULT NULL COMMENT 'RFC fiscal',
    `notes` TEXT DEFAULT NULL,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_providers_status` (`status`),
    INDEX `idx_providers_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service requests linked to providers
CREATE TABLE IF NOT EXISTS `provider_service_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `provider_id` INT(11) DEFAULT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `area` VARCHAR(100) DEFAULT NULL COMMENT 'e.g. Área común, Propiedad específica',
    `property_id` INT(11) DEFAULT NULL,
    `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `status` ENUM('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
    `requested_date` DATE DEFAULT NULL,
    `scheduled_date` DATE DEFAULT NULL,
    `completed_date` DATE DEFAULT NULL,
    `estimated_cost` DECIMAL(10,2) DEFAULT NULL,
    `actual_cost` DECIMAL(10,2) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_service_requests_provider` (`provider_id`),
    INDEX `idx_service_requests_status` (`status`),
    INDEX `idx_service_requests_priority` (`priority`),
    CONSTRAINT `fk_service_provider` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
