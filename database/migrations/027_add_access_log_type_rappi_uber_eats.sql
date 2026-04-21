-- ============================================
-- Migration: Add Rappi/Uber Eats access log type
-- ============================================
USE erp_residencial;

ALTER TABLE access_logs
    MODIFY COLUMN log_type ENUM('resident', 'visit', 'vehicle', 'provider', 'emergency', 'resident_pass', 'rappi_uber_eats') NOT NULL;
