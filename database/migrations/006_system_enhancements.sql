-- =====================================================
-- Migration: Sistema de Mejoras Integrales
-- Fecha: 2024-11-24
-- Descripción: Actualizaciones del sistema para residentes,
--              pagos, accesos y optimizaciones
-- =====================================================

-- 1. Crear tabla de pases de acceso para residentes (si no existe)
CREATE TABLE IF NOT EXISTS resident_access_passes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    pass_type ENUM('single_use', 'temporary', 'permanent') DEFAULT 'single_use',
    qr_code VARCHAR(255) NOT NULL UNIQUE,
    valid_from DATETIME NOT NULL,
    valid_until DATETIME,
    max_uses INT DEFAULT 1,
    uses_count INT DEFAULT 0,
    notes TEXT,
    status ENUM('active', 'expired', 'used', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_qr_code (qr_code),
    INDEX idx_resident (resident_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Agregar configuraciones de PayPal si no existen
INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES
('paypal_enabled', '0'),
('paypal_mode', 'sandbox'),
('paypal_client_id', ''),
('paypal_secret', '');

-- 3. Agregar configuraciones de soporte técnico si no existen
INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES
('support_email', 'soporte@janetzy.shop'),
('support_phone', ''),
('support_hours', 'Lunes a Viernes 9:00 - 18:00'),
('support_url', CONCAT((SELECT setting_value FROM (SELECT * FROM system_settings) AS temp WHERE setting_key = 'site_url'), '/support'));

-- 4. Agregar configuraciones de optimización si no existen
INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES
('cache_enabled', '1'),
('cache_ttl', '3600'),
('query_cache_enabled', '1'),
('max_records_per_page', '50'),
('image_optimization', '1'),
('lazy_loading', '1'),
('minify_assets', '0'),
('session_timeout', '3600');

-- 5. Actualizar configuración de email SMTP
UPDATE system_settings SET setting_value = 'janetzy.shop' WHERE setting_key = 'email_host';
UPDATE system_settings SET setting_value = '465' WHERE setting_key = 'email_port';
UPDATE system_settings SET setting_value = 'hola@janetzy.shop' WHERE setting_key = 'email_user';
UPDATE system_settings SET setting_value = 'hola@janetzy.shop' WHERE setting_key = 'email_from';

-- IMPORTANTE: La contraseña de email debe establecerse desde la interfaz de configuración
-- en Configuración > Email por razones de seguridad.
-- NO incluir contraseñas en scripts de migración bajo ninguna circunstancia.

-- 6. Agregar soporte para soft delete (si no existe)
ALTER TABLE residents 
MODIFY COLUMN status ENUM('active', 'inactive', 'pending', 'deleted') DEFAULT 'active';

ALTER TABLE users 
MODIFY COLUMN status ENUM('active', 'inactive', 'pending', 'deleted') DEFAULT 'active';

-- 7. Agregar índices para optimización de consultas (idempotente recomendado en MySQL)

-- residents(user_id)
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'residents'
    AND index_name = 'idx_residents_user_id'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_residents_user_id ON residents(user_id);',
  'SELECT "idx_residents_user_id ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- residents(property_id)
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'residents'
    AND index_name = 'idx_residents_property_id'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_residents_property_id ON residents(property_id);',
  'SELECT "idx_residents_property_id ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- residents(status)
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'residents'
    AND index_name = 'idx_residents_status'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_residents_status ON residents(status);',
  'SELECT "idx_residents_status ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- maintenance_fees(property_id)
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'maintenance_fees'
    AND index_name = 'idx_maintenance_fees_property'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_maintenance_fees_property ON maintenance_fees(property_id);',
  'SELECT "idx_maintenance_fees_property ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- maintenance_fees(status)
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'maintenance_fees'
    AND index_name = 'idx_maintenance_fees_status'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_maintenance_fees_status ON maintenance_fees(status);',
  'SELECT "idx_maintenance_fees_status ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- maintenance_fees(due_date)
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'maintenance_fees'
    AND index_name = 'idx_maintenance_fees_due_date'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_maintenance_fees_due_date ON maintenance_fees(due_date);',
  'SELECT "idx_maintenance_fees_due_date ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- users(email)
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'users'
    AND index_name = 'idx_users_email'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_users_email ON users(email);',
  'SELECT "idx_users_email ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- users(role)
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'users'
    AND index_name = 'idx_users_role'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_users_role ON users(role);',
  'SELECT "idx_users_role ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- users(status)
SET @idx_exists := (
  SELECT COUNT(1)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'users'
    AND index_name = 'idx_users_status'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_users_status ON users(status);',
  'SELECT "idx_users_status ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 8. Crear tabla de programación de recordatorios de pago (si no existe)
CREATE TABLE IF NOT EXISTS payment_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    maintenance_fee_id INT NOT NULL,
    reminder_date DATE NOT NULL,
    sent BOOLEAN DEFAULT FALSE,
    sent_at DATETIME,
    email_to VARCHAR(255),
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (maintenance_fee_id) REFERENCES maintenance_fees(id) ON DELETE CASCADE,
    INDEX idx_reminder_date (reminder_date),
    INDEX idx_status (status, sent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Procedimiento para generar recordatorios automáticos de pago
DELIMITER //

CREATE PROCEDURE generate_payment_reminders()
BEGIN
    -- Generar recordatorios para pagos que vencen mañana y no tienen recordatorio
    INSERT INTO payment_reminders (maintenance_fee_id, reminder_date, email_to)
    SELECT 
        mf.id,
        DATE_SUB(mf.due_date, INTERVAL 1 DAY) as reminder_date,
        u.email
    FROM maintenance_fees mf
    JOIN properties p ON mf.property_id = p.id
    JOIN residents r ON r.property_id = p.id AND r.is_primary = 1
    JOIN users u ON r.user_id = u.id
    WHERE mf.status IN ('pending', 'overdue')
        AND mf.due_date > CURDATE()
        AND DATE_SUB(mf.due_date, INTERVAL 1 DAY) = CURDATE()
        AND NOT EXISTS (
            SELECT 1 FROM payment_reminders pr 
            WHERE pr.maintenance_fee_id = mf.id 
            AND pr.reminder_date = DATE_SUB(mf.due_date, INTERVAL 1 DAY)
        );
END//

DELIMITER ;

-- 10. Actualizar estructura de maintenance_fees para soportar acumulación de adeudos
-- MySQL no permite IF NOT EXISTS en ADD COLUMN; necesitas comprobar manualmente antes de agregar nuevas columnas si ejecutas varias veces este script en la misma base.
-- Si no existen, deberás agregarlas manualmente (o vía script/procedimiento); aquí lo dejamos plano para simple migraciones.
ALTER TABLE maintenance_fees 
ADD COLUMN accumulated_debt DECIMAL(10,2) DEFAULT 0.00 AFTER amount;

ALTER TABLE maintenance_fees 
ADD COLUMN late_fee DECIMAL(10,2) DEFAULT 0.00 AFTER accumulated_debt;

-- 11. Crear vista para resumen de adeudos por propiedad
CREATE OR REPLACE VIEW property_debt_summary AS
SELECT 
    p.id as property_id,
    p.property_number,
    p.section,
    r.id as resident_id,
    CONCAT(u.first_name, ' ', u.last_name) as resident_name,
    u.email,
    u.phone,
    COUNT(CASE WHEN mf.status IN ('pending', 'overdue') THEN 1 END) as pending_payments_count,
    SUM(CASE WHEN mf.status IN ('pending', 'overdue') THEN mf.amount ELSE 0 END) as total_debt,
    SUM(CASE WHEN mf.status = 'paid' THEN mf.amount ELSE 0 END) as total_paid,
    MAX(CASE WHEN mf.status IN ('pending', 'overdue') THEN mf.due_date END) as oldest_due_date
FROM properties p
LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
LEFT JOIN users u ON r.user_id = u.id
LEFT JOIN maintenance_fees mf ON mf.property_id = p.id
GROUP BY p.id, p.property_number, p.section, r.id, u.first_name, u.last_name, u.email, u.phone;

-- 12. Optimizar tablas existentes
OPTIMIZE TABLE users;
OPTIMIZE TABLE residents;
OPTIMIZE TABLE properties;
OPTIMIZE TABLE maintenance_fees;
OPTIMIZE TABLE access_logs;
OPTIMIZE TABLE audit_logs;

-- 13. Limpiar datos antiguos (opcional - comentado por seguridad)
-- DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY);
-- DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1;

-- =====================================================
-- Notas de Implementación:
-- =====================================================
-- 1. Este script es idempotente y puede ejecutarse múltiples veces
-- 2. Se recomienda hacer un backup antes de ejecutar
-- 3. La contraseña de email debe configurarse desde la interfaz
-- 4. El procedimiento generate_payment_reminders debe ejecutarse diariamente
--    mediante un cron job o tarea programada
-- 5. Las optimizaciones pueden tardar en tablas grandes
-- =====================================================

-- Registro de migración
INSERT INTO system_settings (setting_key, setting_value) 
VALUES ('migration_006_applied', NOW())
ON DUPLICATE KEY UPDATE setting_value = NOW();
