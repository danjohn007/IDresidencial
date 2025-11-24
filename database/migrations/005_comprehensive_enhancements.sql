-- ============================================
-- Comprehensive System Enhancements Migration
-- Date: 2024-11-24
-- Description: Adds all necessary tables and updates for new features
-- ============================================

USE janetzy_residencial;

-- ============================================
-- 1. EXISTING TABLES - Verify they exist
-- ============================================

-- Financial Movement Types (should already exist from migration 001)
CREATE TABLE IF NOT EXISTS financial_movement_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('ingreso', 'egreso', 'ambos') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Financial Movements (should already exist from migration 001)
CREATE TABLE IF NOT EXISTS financial_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movement_type_id INT NOT NULL,
    transaction_type ENUM('ingreso', 'egreso') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    property_id INT,
    resident_id INT,
    payment_method ENUM('efectivo', 'tarjeta', 'transferencia', 'paypal', 'otro'),
    payment_reference VARCHAR(100),
    transaction_date DATE NOT NULL,
    created_by INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (movement_type_id) REFERENCES financial_movement_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_property_id (property_id),
    INDEX idx_resident_id (resident_id),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Resets (should already exist from migration 003)
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at),
    INDEX idx_user_used (user_id, used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. NEW TABLES for Resident Portal Features
-- ============================================

-- Payment Reminders (may already exist from migration 004)
CREATE TABLE IF NOT EXISTS payment_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    fee_id INT NOT NULL,
    reminder_type ENUM('email','sms','notification') DEFAULT 'email',
    sent_at TIMESTAMP NULL DEFAULT NULL,
    status ENUM('pending','sent','failed') DEFAULT 'pending',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_id) REFERENCES maintenance_fees(id) ON DELETE CASCADE,
    INDEX idx_resident_id (resident_id),
    INDEX idx_fee_id (fee_id),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resident Access Passes (may already exist from migration 004)
CREATE TABLE IF NOT EXISTS resident_access_passes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    pass_type ENUM('single_use','temporary','permanent') DEFAULT 'single_use',
    qr_code VARCHAR(255) NOT NULL UNIQUE,
    valid_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    valid_until TIMESTAMP NOT NULL,
    uses_count INT DEFAULT 0,
    max_uses INT DEFAULT 1,
    status ENUM('active','used','expired','cancelled') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_resident_id (resident_id),
    INDEX idx_qr_code (qr_code),
    INDEX idx_status (status),
    INDEX idx_valid_dates (valid_from, valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support Tickets (may already exist from migration 004)
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
    status ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
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

-- ============================================
-- 3. INSERT DEFAULT DATA
-- ============================================

-- Insert default financial movement types if not exist
INSERT IGNORE INTO financial_movement_types (id, name, description, category) VALUES
(1, 'Cuota de Mantenimiento', 'Pago mensual de cuota de mantenimiento', 'ingreso'),
(2, 'Cuota Extraordinaria', 'Cuota especial para gastos no recurrentes', 'ingreso'),
(3, 'Reservación de Amenidad', 'Pago por uso de amenidades', 'ingreso'),
(4, 'Penalización', 'Multa o penalización', 'ingreso'),
(5, 'Servicios', 'Pago de servicios (luz, agua, etc.)', 'egreso'),
(6, 'Mantenimiento y Reparaciones', 'Gastos de mantenimiento general', 'egreso'),
(7, 'Nómina', 'Pago de personal', 'egreso'),
(8, 'Suministros', 'Compra de materiales y suministros', 'egreso'),
(9, 'Otros Ingresos', 'Ingresos diversos', 'ingreso'),
(10, 'Otros Egresos', 'Gastos diversos', 'egreso');

-- Insert/Update system settings for email and PayPal
-- Note: Email password should be configured through the Settings UI, not hardcoded here
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('email_host', 'janetzy.shop', 'text', 'Servidor SMTP para envío de correos'),
('email_port', '465', 'number', 'Puerto SMTP'),
('email_user', 'hola@janetzy.shop', 'text', 'Usuario SMTP'),
('email_password', '', 'password', 'Contraseña SMTP (configurar en Settings)'),
('email_from', 'hola@janetzy.shop', 'text', 'Dirección de remitente'),
('paypal_enabled', '1', 'boolean', 'Habilitar pagos con PayPal'),
('paypal_client_id', '', 'text', 'PayPal Client ID'),
('paypal_secret', '', 'password', 'PayPal Secret Key'),
('paypal_mode', 'sandbox', 'text', 'PayPal Mode (sandbox o live)'),
('payment_reminder_days', '1', 'number', 'Días antes del vencimiento para enviar recordatorio'),
('support_email', 'soporte@janetzy.shop', 'text', 'Email de soporte técnico'),
('system_optimization_enabled', '1', 'boolean', 'Auto-optimización del sistema habilitada'),
('cache_enabled', '1', 'boolean', 'Cache de consultas habilitado'),
('max_records_per_page', '50', 'number', 'Máximo de registros por página')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    description = VALUES(description);

-- ============================================
-- 4. CREATE VIEWS for Reporting
-- ============================================

-- Drop and recreate views to ensure they're up to date
DROP VIEW IF EXISTS resident_payment_history;
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

DROP VIEW IF EXISTS resident_debt_summary;
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

-- ============================================
-- 5. OPTIMIZE TABLES with Additional Indexes
-- ============================================

-- Add composite indexes for better query performance
CREATE INDEX IF NOT EXISTS idx_users_role_status ON users(role, status);
CREATE INDEX IF NOT EXISTS idx_residents_status_primary ON residents(status, is_primary);
CREATE INDEX IF NOT EXISTS idx_maintenance_fees_status_due ON maintenance_fees(status, due_date);
CREATE INDEX IF NOT EXISTS idx_financial_movements_date_type ON financial_movements(transaction_date, transaction_type);
CREATE INDEX IF NOT EXISTS idx_visits_created_date ON visits(created_at);

-- ============================================
-- 6. CREATE STORED PROCEDURE for Payment Reminders
-- ============================================

DELIMITER $$

DROP PROCEDURE IF EXISTS SendPaymentReminders$$
CREATE PROCEDURE SendPaymentReminders()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_resident_id INT;
    DECLARE v_fee_id INT;
    DECLARE v_due_date DATE;
    DECLARE reminder_days INT;
    
    -- Get reminder days setting
    SELECT CAST(setting_value AS UNSIGNED) INTO reminder_days 
    FROM system_settings 
    WHERE setting_key = 'payment_reminder_days' 
    LIMIT 1;
    
    -- Cursor for fees that need reminders
    DECLARE fee_cursor CURSOR FOR
        SELECT DISTINCT r.id as resident_id, mf.id as fee_id, mf.due_date
        FROM maintenance_fees mf
        INNER JOIN properties p ON mf.property_id = p.id
        INNER JOIN residents r ON r.property_id = p.id AND r.is_primary = 1
        WHERE mf.status IN ('pending', 'overdue')
          AND DATEDIFF(mf.due_date, CURDATE()) = reminder_days
          AND NOT EXISTS (
              SELECT 1 FROM payment_reminders pr 
              WHERE pr.fee_id = mf.id AND pr.status = 'sent'
          );
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN fee_cursor;
    
    read_loop: LOOP
        FETCH fee_cursor INTO v_resident_id, v_fee_id, v_due_date;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insert reminder
        INSERT INTO payment_reminders (resident_id, fee_id, reminder_type, status)
        VALUES (v_resident_id, v_fee_id, 'email', 'pending');
    END LOOP;
    
    CLOSE fee_cursor;
END$$

DELIMITER ;

-- ============================================
-- 7. CREATE EVENT for Automated Payment Reminders
-- ============================================

-- Enable event scheduler if not already enabled
SET GLOBAL event_scheduler = ON;

-- Drop existing event if exists
DROP EVENT IF EXISTS daily_payment_reminders;

-- Create event to run daily at 9:00 AM
CREATE EVENT daily_payment_reminders
ON SCHEDULE EVERY 1 DAY
STARTS (TIMESTAMP(CURRENT_DATE) + INTERVAL 9 HOUR)
DO
CALL SendPaymentReminders();

-- ============================================
-- Migration Complete!
-- ============================================

SELECT 'Migration 005_comprehensive_enhancements.sql completed successfully!' as status;
SELECT CONCAT('Total financial movement types: ', COUNT(*)) as count FROM financial_movement_types;
SELECT CONCAT('Total system settings: ', COUNT(*)) as count FROM system_settings;
