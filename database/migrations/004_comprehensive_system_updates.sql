-- ============================================
-- Comprehensive System Updates Migration (Robusto, idempotente, compatible con INFORMATION_SCHEMA utf8 charset)
-- Usa COLLATE utf8_general_ci en procedimientos/metadatos, permite utf8mb4_utf8_unicode_ci en tus datos
-- ============================================
USE janetzy_residencial;

-- Limpieza previa de procedimientos
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;
DROP PROCEDURE IF EXISTS AddIndexIfNotExists;
DROP PROCEDURE IF EXISTS AddFKIfNotExists;

-- ==== Procedimiento genérico para columnas ====
DELIMITER $$
CREATE PROCEDURE AddColumnIfNotExists(
  IN tblName VARCHAR(64),
  IN colName VARCHAR(64),
  IN colDef TEXT
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA COLLATE utf8_general_ci = DATABASE() COLLATE utf8_general_ci
      AND TABLE_NAME COLLATE utf8_general_ci = tblName COLLATE utf8_general_ci
      AND COLUMN_NAME COLLATE utf8_general_ci = colName COLLATE utf8_general_ci
  ) THEN
    SET @sql = CONCAT('ALTER TABLE `', tblName, '` ADD COLUMN ', colDef);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$

-- ==== Procedimiento genérico para índices ====
CREATE PROCEDURE AddIndexIfNotExists(
  IN tblName VARCHAR(64),
  IN idxName VARCHAR(64),
  IN idxDef TEXT
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA COLLATE utf8_general_ci = DATABASE() COLLATE utf8_general_ci
      AND TABLE_NAME COLLATE utf8_general_ci = tblName COLLATE utf8_general_ci
      AND INDEX_NAME COLLATE utf8_general_ci = idxName COLLATE utf8_general_ci
  ) THEN
    SET @sql = CONCAT('ALTER TABLE `', tblName, '` ADD INDEX ', idxDef);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$

-- ==== Procedimiento genérico para Foreign Keys ====
CREATE PROCEDURE AddFKIfNotExists(
  IN tblName VARCHAR(64),
  IN fkName VARCHAR(64),
  IN fkDef TEXT
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA COLLATE utf8_general_ci = DATABASE() COLLATE utf8_general_ci
      AND TABLE_NAME COLLATE utf8_general_ci = tblName COLLATE utf8_general_ci
      AND CONSTRAINT_NAME COLLATE utf8_general_ci = fkName COLLATE utf8_general_ci
  ) THEN
    SET @sql = CONCAT('ALTER TABLE `', tblName, '` ADD CONSTRAINT ', fkDef);
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;

-- ======= USO PARA MIGRACIÓN COMPLETA =======
-- 1. users: email verification columns/indices
CALL AddColumnIfNotExists('users', 'email_verification_token', 'email_verification_token VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER email');
CALL AddColumnIfNotExists('users', 'email_verified_at', 'email_verified_at TIMESTAMP NULL AFTER email_verification_token');
CALL AddIndexIfNotExists('users', 'idx_email_verification_token', 'idx_email_verification_token (email_verification_token)');
ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','blocked','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'active';
-- 2. residents: enum status (no procedure needed for ALTER)
ALTER TABLE residents MODIFY COLUMN status ENUM('active','inactive','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'active';

-- 3. Add subdivision_id safely
CALL AddColumnIfNotExists('properties', 'subdivision_id', 'subdivision_id INT DEFAULT NULL AFTER id');
CALL AddIndexIfNotExists('properties', 'idx_subdivision_id', 'idx_subdivision_id (subdivision_id)');
CALL AddFKIfNotExists('properties', 'fk_properties_subdivision', 'fk_properties_subdivision FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE SET NULL');

CALL AddColumnIfNotExists('users', 'subdivision_id', 'subdivision_id INT DEFAULT NULL AFTER house_number');
CALL AddIndexIfNotExists('users', 'idx_users_subdivision_id', 'idx_users_subdivision_id (subdivision_id)');
CALL AddFKIfNotExists('users', 'fk_users_subdivision', 'fk_users_subdivision FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE SET NULL');

CALL AddColumnIfNotExists('residents', 'subdivision_id', 'subdivision_id INT DEFAULT NULL AFTER property_id');
CALL AddIndexIfNotExists('residents', 'idx_residents_subdivision_id', 'idx_residents_subdivision_id (subdivision_id)');
CALL AddFKIfNotExists('residents', 'fk_residents_subdivision', 'fk_residents_subdivision FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE SET NULL');

CALL AddColumnIfNotExists('vehicles', 'subdivision_id', 'subdivision_id INT DEFAULT NULL AFTER resident_id');
CALL AddIndexIfNotExists('vehicles', 'idx_vehicles_subdivision_id', 'idx_vehicles_subdivision_id (subdivision_id)');
CALL AddFKIfNotExists('vehicles', 'fk_vehicles_subdivision', 'fk_vehicles_subdivision FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) ON DELETE SET NULL');

-- 4. Support tickets
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    email VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    phone VARCHAR(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    subject VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    message TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    priority ENUM('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
    status ENUM('open','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
    assigned_to INT DEFAULT NULL,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Payment reminders
CREATE TABLE IF NOT EXISTS payment_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    fee_id INT NOT NULL,
    reminder_type ENUM('email','sms','notification') COLLATE utf8mb4_unicode_ci DEFAULT 'email',
    sent_at TIMESTAMP NULL DEFAULT NULL,
    status ENUM('pending','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
    error_message TEXT COLLATE utf8mb4_unicode_ci,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_id) REFERENCES maintenance_fees(id) ON DELETE CASCADE,
    INDEX idx_resident_id (resident_id),
    INDEX idx_fee_id (fee_id),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Resident access passes
CREATE TABLE IF NOT EXISTS resident_access_passes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    pass_type ENUM('single_use','temporary','permanent') COLLATE utf8mb4_unicode_ci DEFAULT 'single_use',
    qr_code VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    valid_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    valid_until TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    uses_count INT DEFAULT '0',
    max_uses INT DEFAULT '1',
    status ENUM('active','used','expired','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
    notes TEXT COLLATE utf8mb4_unicode_ci,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_resident_id (resident_id),
    INDEX idx_qr_code (qr_code),
    INDEX idx_status (status),
    INDEX idx_valid_dates (valid_from,valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Views (delete stand-in table if present)
DROP TABLE IF EXISTS resident_payment_history;
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
LEFT JOIN financial_movements fm ON fm.reference_type = 'maintenance_fee' AND fm.reference_id = mf.id
WHERE r.status = 'active';

DROP TABLE IF EXISTS resident_debt_summary;
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
    MIN(CASE WHEN mf.status IN ('pending','overdue') THEN mf.due_date END) as oldest_due_date
FROM residents r
INNER JOIN users u ON r.user_id = u.id
INNER JOIN properties p ON r.property_id = p.id
LEFT JOIN maintenance_fees mf ON mf.property_id = p.id
WHERE r.status = 'active'
GROUP BY r.id, u.id, u.first_name, u.last_name, u.email, u.phone, p.property_number;

-- 8. Insert default subdivision (robusto)
INSERT IGNORE INTO subdivisions (id, name, description, status) VALUES
(1, 'Fraccionamiento Principal', 'Fraccionamiento principal del sistema', 'active');

-- 9. Special settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('email_verification_required', '1', 'boolean', 'Require email verification for new registrations'),
('admin_approval_required', '1', 'boolean', 'Require admin approval for new registrations'),
('payment_reminder_days', '1', 'number', 'Days before due date to send payment reminder'),
('paypal_enabled', '0', 'boolean', 'Enable PayPal payments for residents'),
('paypal_client_id', '', 'text', 'PayPal Client ID'),
('paypal_secret', '', 'text', 'PayPal Secret Key'),
('support_email', 'soporte@residencial.com', 'text', 'Support email address')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- 10. Extra indices
CALL AddIndexIfNotExists('financial_movements', 'idx_transaction_date_type', 'idx_transaction_date_type (transaction_date, transaction_type)');
CALL AddIndexIfNotExists('maintenance_fees', 'idx_status_due_date', 'idx_status_due_date (status, due_date)');
CALL AddIndexIfNotExists('users', 'idx_role_status', 'idx_role_status (role, status)');
CALL AddIndexIfNotExists('residents', 'idx_status_relationship', 'idx_status_relationship (status, relationship)');

-- Limpieza de procedimientos migración
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;
DROP PROCEDURE IF EXISTS AddIndexIfNotExists;
DROP PROCEDURE IF EXISTS AddFKIfNotExists;

-- ============================================
-- Migration complete!
-- ============================================
