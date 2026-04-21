-- ============================================
-- Migration: Add Rappi/Uber Eats visit types
-- ============================================
USE erp_residencial;

ALTER TABLE visits
    MODIFY COLUMN visit_type ENUM('personal', 'proveedor', 'delivery', 'rappi', 'uber_eats', 'otro') DEFAULT 'personal';
