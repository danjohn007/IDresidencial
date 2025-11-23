-- ============================================
-- Comprehensive System Updates Migration
-- Date: 2025-11-23
-- Description: Updates for public registration, subdivisions,
--              email verification, and system enhancements
-- ============================================

-- Use the database
USE erp_residencial;

-- ============================================
-- 1. Update users table for email verification and pending status
-- ============================================

-- Add email verification columns if they don't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS email_verification_token VARCHAR(255) AFTER email,
ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL AFTER email_verification_token,
ADD INDEX IF NOT EXISTS idx_email_verification_token (email_verification_token);

-- Update status enum to include 'pending'
ALTER TABLE users 
MODIFY COLUMN status ENUM('active', 'inactive', 'blocked', 'pending') DEFAULT 'active';

-- Update residents table to support pending status
ALTER TABLE residents 
MODIFY COLUMN status ENUM('active', 'inactive', 'pending') DEFAULT 'active';

-- ============================================
-- 2. Create subdivisions (Fraccionamientos) table
-- ============================================
CREATE TABLE IF NOT EXISTS subdivisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    total_properties INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. Add subdivision_id to related tables
-- ============================================

-- Add to properties table
ALTER TABLE properties 
ADD COLUMN IF NOT EXISTS subdivision_id INT AFTER id,
ADD INDEX IF NOT EXISTS idx_subdivision_id (subdivision_id),
ADD CONSTRAINT IF NOT EXISTS fk_properties_subdivision 
    FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE SET NULL;

-- Add to users table  
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS subdivision_id INT AFTER house_number,
ADD INDEX IF NOT EXISTS idx_users_subdivision_id (subdivision_id),
ADD CONSTRAINT IF NOT EXISTS fk_users_subdivision 
    FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE SET NULL;

-- Add to residents table
ALTER TABLE residents 
ADD COLUMN IF NOT EXISTS subdivision_id INT AFTER property_id,
ADD INDEX IF NOT EXISTS idx_residents_subdivision_id (subdivision_id),
ADD CONSTRAINT IF NOT EXISTS fk_residents_subdivision 
    FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE SET NULL;

-- Add to vehicles table
ALTER TABLE vehicles 
ADD COLUMN IF NOT EXISTS subdivision_id INT AFTER resident_id,
ADD INDEX IF NOT EXISTS idx_vehicles_subdivision_id (subdivision_id),
ADD CONSTRAINT IF NOT EXISTS fk_vehicles_subdivision 
    FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE SET NULL;

-- ============================================
-- 4. Create support tickets table for technical support
-- ============================================
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    assigned_to INT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. Create payment reminders log table
-- ============================================
CREATE TABLE IF NOT EXISTS payment_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    maintenance_fee_id INT,
    reminder_type ENUM('email', 'sms', 'whatsapp') NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sent', 'failed', 'bounced') DEFAULT 'sent',
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (maintenance_fee_id) REFERENCES maintenance_fees(id) ON DELETE CASCADE,
    INDEX idx_resident_id (resident_id),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. Create resident access passes table (for QR codes)
-- ============================================
CREATE TABLE IF NOT EXISTS resident_access_passes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    pass_type ENUM('single_use', 'temporary', 'permanent') DEFAULT 'single_use',
    qr_code VARCHAR(255) UNIQUE NOT NULL,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    uses_count INT DEFAULT 0,
    max_uses INT DEFAULT 1,
    status ENUM('active', 'used', 'expired', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_resident_id (resident_id),
    INDEX idx_qr_code (qr_code),
    INDEX idx_status (status),
    INDEX idx_valid_dates (valid_from, valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. Create resident payment history view
-- ============================================
CREATE OR REPLACE VIEW resident_payment_history AS
SELECT 
    r.id as resident_id,
    u.id as user_id,
    CONCAT(u.first_name, ' ', u.last_name) as resident_name,
    p.property_number,
    mf.id as fee_id,
    mf.period,
    mf.amount,
    mf.due_date,
    mf.paid_date,
    mf.status as payment_status,
    mf.payment_method,
    fm.id as financial_movement_id,
    fm.transaction_date
FROM residents r
INNER JOIN users u ON r.user_id = u.id
INNER JOIN properties p ON r.property_id = p.id
LEFT JOIN maintenance_fees mf ON mf.property_id = p.id
LEFT JOIN financial_movements fm ON fm.reference_type = 'maintenance_fee' 
    AND fm.reference_id = mf.id
WHERE r.status = 'active';

-- ============================================
-- 8. Create debt accumulation view
-- ============================================
CREATE OR REPLACE VIEW resident_debt_summary AS
SELECT 
    r.id as resident_id,
    u.id as user_id,
    CONCAT(u.first_name, ' ', u.last_name) as resident_name,
    u.email,
    u.phone,
    p.property_number,
    COUNT(mf.id) as total_fees,
    SUM(CASE WHEN mf.status = 'pending' THEN mf.amount ELSE 0 END) as pending_amount,
    SUM(CASE WHEN mf.status = 'overdue' THEN mf.amount ELSE 0 END) as overdue_amount,
    SUM(CASE WHEN mf.status = 'paid' THEN mf.amount ELSE 0 END) as paid_amount,
    MIN(CASE WHEN mf.status IN ('pending', 'overdue') THEN mf.due_date END) as oldest_due_date
FROM residents r
INNER JOIN users u ON r.user_id = u.id
INNER JOIN properties p ON r.property_id = p.id
LEFT JOIN maintenance_fees mf ON mf.property_id = p.id
WHERE r.status = 'active'
GROUP BY r.id, u.id, u.first_name, u.last_name, u.email, u.phone, p.property_number;

-- ============================================
-- 9. Insert default subdivision (if needed)
-- ============================================
INSERT IGNORE INTO subdivisions (id, name, description, status) VALUES
(1, 'Principal', 'Fraccionamiento principal', 'active');

-- ============================================
-- 10. Add system settings for new features
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('email_verification_required', '1', 'boolean', 'Require email verification for new registrations'),
('admin_approval_required', '1', 'boolean', 'Require admin approval for new registrations'),
('payment_reminder_days', '1', 'number', 'Days before due date to send payment reminder'),
('paypal_enabled', '0', 'boolean', 'Enable PayPal payments for residents'),
('paypal_client_id', '', 'text', 'PayPal Client ID'),
('paypal_secret', '', 'text', 'PayPal Secret Key'),
('support_email', 'soporte@residencial.com', 'text', 'Support email address')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- ============================================
-- 11. Create indexes for better performance
-- ============================================
ALTER TABLE financial_movements 
ADD INDEX IF NOT EXISTS idx_transaction_date_type (transaction_date, transaction_type);

ALTER TABLE maintenance_fees 
ADD INDEX IF NOT EXISTS idx_status_due_date (status, due_date);

ALTER TABLE users 
ADD INDEX IF NOT EXISTS idx_role_status (role, status);

ALTER TABLE residents 
ADD INDEX IF NOT EXISTS idx_status_relationship (status, relationship);

-- ============================================
-- 12. Update existing data (safe updates)
-- ============================================

-- Note: These updates will only take effect after properties are assigned to subdivisions
-- Run these manually after assigning subdivisions to properties:

-- UPDATE residents r
-- INNER JOIN properties p ON r.property_id = p.id
-- SET r.subdivision_id = p.subdivision_id
-- WHERE p.subdivision_id IS NOT NULL AND r.subdivision_id IS NULL;

-- UPDATE users u
-- INNER JOIN residents r ON u.id = r.user_id
-- SET u.subdivision_id = r.subdivision_id
-- WHERE r.subdivision_id IS NOT NULL AND u.subdivision_id IS NULL;

-- ============================================
-- VERIFICATION QUERIES
-- ============================================
-- Run these queries after migration to verify success

-- Check new columns exist
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = 'erp_residencial' 
-- AND TABLE_NAME = 'users' 
-- AND COLUMN_NAME IN ('email_verification_token', 'email_verified_at', 'subdivision_id');

-- Check new tables exist
-- SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
-- WHERE TABLE_SCHEMA = 'erp_residencial' 
-- AND TABLE_NAME IN ('subdivisions', 'support_tickets', 'payment_reminders', 'resident_access_passes');

-- Check views exist
-- SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS 
-- WHERE TABLE_SCHEMA = 'erp_residencial' 
-- AND TABLE_NAME IN ('resident_payment_history', 'resident_debt_summary');

-- ============================================
-- ROLLBACK SCRIPT (Use only if needed)
-- ============================================
-- WARNING: This will remove all data added by this migration!
-- 
-- DROP VIEW IF EXISTS resident_payment_history;
-- DROP VIEW IF EXISTS resident_debt_summary;
-- DROP TABLE IF EXISTS resident_access_passes;
-- DROP TABLE IF EXISTS payment_reminders;
-- DROP TABLE IF EXISTS support_tickets;
-- ALTER TABLE vehicles DROP FOREIGN KEY IF EXISTS fk_vehicles_subdivision;
-- ALTER TABLE vehicles DROP COLUMN IF EXISTS subdivision_id;
-- ALTER TABLE residents DROP FOREIGN KEY IF EXISTS fk_residents_subdivision;
-- ALTER TABLE residents DROP COLUMN IF EXISTS subdivision_id;
-- ALTER TABLE users DROP FOREIGN KEY IF EXISTS fk_users_subdivision;
-- ALTER TABLE users DROP COLUMN IF EXISTS subdivision_id;
-- ALTER TABLE properties DROP FOREIGN KEY IF EXISTS fk_properties_subdivision;
-- ALTER TABLE properties DROP COLUMN IF EXISTS subdivision_id;
-- DROP TABLE IF EXISTS subdivisions;
-- ALTER TABLE users DROP COLUMN IF EXISTS email_verification_token;
-- ALTER TABLE users DROP COLUMN IF EXISTS email_verified_at;

-- ============================================
-- Migration complete!
-- ============================================
