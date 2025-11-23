-- ============================================
-- Migration: System Improvements
-- Date: 2024-11-23
-- Description: Mejoras al sistema residencial
-- NOTA: Este script usa 'erp_residencial' como nombre de BD
--       Si tu base de datos tiene otro nombre (ej: janetzy_residencial),
--       reemplaza 'erp_residencial' con tu nombre de BD antes de ejecutar
-- ============================================

USE erp_residencial;

-- ============================================
-- 1. Crear tabla de fraccionamientos/subdivisiones
-- ============================================
CREATE TABLE IF NOT EXISTS subdivisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    address VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar fraccionamiento por defecto
INSERT INTO subdivisions (name, description, status) 
VALUES ('Fraccionamiento Principal', 'Fraccionamiento principal del sistema', 'active')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================
-- 2. Agregar campo subdivision_id a propiedades
-- ============================================
ALTER TABLE properties ADD COLUMN subdivision_id INT DEFAULT NULL AFTER id;
ALTER TABLE properties 
    ADD CONSTRAINT fk_properties_subdivision 
    FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) 
    ON DELETE SET NULL;

-- Asignar subdivision por defecto a propiedades existentes
UPDATE properties SET subdivision_id = 1 WHERE subdivision_id IS NULL;

-- ============================================
-- 3. Agregar campo subdivision_id a residentes
-- ============================================
ALTER TABLE residents ADD COLUMN subdivision_id INT DEFAULT NULL AFTER id;
ALTER TABLE residents 
    ADD CONSTRAINT fk_residents_subdivision 
    FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) 
    ON DELETE SET NULL;

-- Asignar subdivision por defecto a residentes existentes
UPDATE residents SET subdivision_id = 1 WHERE subdivision_id IS NULL;

-- ============================================
-- 4. Agregar campo subdivision_id a usuarios
-- ============================================
ALTER TABLE users ADD COLUMN subdivision_id INT DEFAULT NULL AFTER id;
ALTER TABLE users 
    ADD CONSTRAINT fk_users_subdivision 
    FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) 
    ON DELETE SET NULL;

-- Asignar subdivision por defecto a usuarios existentes
UPDATE users SET subdivision_id = 1 WHERE subdivision_id IS NULL;

-- ============================================
-- 5. Agregar campo subdivision_id a vehículos
-- ============================================
ALTER TABLE vehicles ADD COLUMN subdivision_id INT DEFAULT NULL AFTER id;
ALTER TABLE vehicles 
    ADD CONSTRAINT fk_vehicles_subdivision 
    FOREIGN KEY (subdivision_id) REFERENCES subdivisions(id) 
    ON DELETE SET NULL;

-- Asignar subdivision por defecto a vehículos existentes
UPDATE vehicles SET subdivision_id = 1 WHERE subdivision_id IS NULL;

-- ============================================
-- 6. Crear tabla de validaciones pendientes
-- ============================================
CREATE TABLE IF NOT EXISTS pending_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT DEFAULT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    validation_type ENUM('resident', 'user') DEFAULT 'resident',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    notes TEXT,
    reviewed_by INT DEFAULT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_email_verified (email_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. Agregar campos de términos y condiciones al sistema
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description)
VALUES 
    ('terms_and_conditions', 'Al registrarse, acepta cumplir con las políticas del fraccionamiento.', 'text', 'Términos y condiciones del registro público'),
    ('whatsapp_number', '', 'text', 'Número de WhatsApp para soporte técnico'),
    ('enable_email_verification', '1', 'boolean', 'Habilitar verificación de correo electrónico'),
    ('enable_admin_approval', '1', 'boolean', 'Requiere aprobación de admin para nuevos residentes'),
    ('paypal_enabled', '0', 'boolean', 'Habilitar pagos con PayPal')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- ============================================
-- 8. Crear tabla de historial de pagos de residentes
-- ============================================
CREATE TABLE IF NOT EXISTS resident_payment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    payment_type ENUM('maintenance', 'amenity', 'penalty', 'other') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'transfer', 'check', 'paypal', 'card') NOT NULL,
    payment_reference VARCHAR(100),
    payment_date DATE NOT NULL,
    period VARCHAR(20),
    status ENUM('pending', 'completed', 'cancelled', 'refunded') DEFAULT 'completed',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_resident_id (resident_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. Crear tabla de adeudos acumulados
-- ============================================
CREATE TABLE IF NOT EXISTS resident_balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    last_payment_date DATE NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    UNIQUE KEY unique_resident (resident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. Crear tabla de configuración de optimización
-- ============================================
CREATE TABLE IF NOT EXISTS system_optimization (
    id INT AUTO_INCREMENT PRIMARY KEY,
    optimization_key VARCHAR(50) UNIQUE NOT NULL,
    optimization_value TEXT,
    is_enabled BOOLEAN DEFAULT TRUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar opciones de optimización por defecto
INSERT INTO system_optimization (optimization_key, optimization_value, is_enabled, description)
VALUES
    ('cache_enabled', '1', TRUE, 'Habilitar caché del sistema'),
    ('compress_images', '1', TRUE, 'Comprimir imágenes automáticamente'),
    ('lazy_loading', '1', TRUE, 'Carga diferida de imágenes'),
    ('minify_css', '0', FALSE, 'Minificar archivos CSS'),
    ('minify_js', '0', FALSE, 'Minificar archivos JavaScript'),
    ('database_optimization', '0', FALSE, 'Optimización automática de base de datos')
ON DUPLICATE KEY UPDATE optimization_key=optimization_key;

-- ============================================
-- 11. Crear tabla de recordatorios de pago
-- ============================================
CREATE TABLE IF NOT EXISTS payment_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    fee_id INT NOT NULL,
    reminder_type ENUM('email', 'sms', 'notification') DEFAULT 'email',
    sent_at TIMESTAMP NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_id) REFERENCES maintenance_fees(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. Actualizar tabla de mantenimiento de cuotas
-- ============================================
ALTER TABLE maintenance_fees ADD COLUMN reminder_sent BOOLEAN DEFAULT FALSE AFTER status;
ALTER TABLE maintenance_fees ADD COLUMN payment_confirmation VARCHAR(255) AFTER payment_reference;

-- ============================================
-- 13. Crear tabla de tokens de verificación de email
-- ============================================
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. Agregar campo email_verified a users
-- ============================================
ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER email;
ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL AFTER email_verified;

-- Marcar emails existentes como verificados
UPDATE users SET email_verified = TRUE, email_verified_at = NOW() WHERE email_verified IS NULL OR email_verified = FALSE;

-- ============================================
-- 15. Crear vista para dashboard de residentes
-- ============================================
CREATE OR REPLACE VIEW resident_dashboard_stats AS
SELECT 
    r.id as resident_id,
    u.id as user_id,
    COUNT(DISTINCT v.id) as total_visits,
    COUNT(DISTINCT res.id) as total_reservations,
    COUNT(DISTINCT mr.id) as total_maintenance_reports,
    COALESCE(rb.balance, 0) as current_balance,
    COUNT(DISTINCT CASE WHEN mf.status = 'pending' THEN mf.id END) as pending_payments
FROM residents r
JOIN users u ON r.user_id = u.id
LEFT JOIN visits v ON r.id = v.resident_id
LEFT JOIN reservations res ON r.id = res.resident_id
LEFT JOIN maintenance_reports mr ON r.id = mr.resident_id
LEFT JOIN resident_balances rb ON r.id = rb.resident_id
LEFT JOIN properties p ON r.property_id = p.id
LEFT JOIN maintenance_fees mf ON p.id = mf.property_id AND mf.status = 'pending'
GROUP BY r.id, u.id, rb.balance;

-- ============================================
-- 16. Actualizar índices para mejor rendimiento
-- ============================================
ALTER TABLE access_logs ADD INDEX idx_timestamp_type (timestamp, log_type);
ALTER TABLE visits ADD INDEX idx_valid_dates (valid_from, valid_until);
ALTER TABLE reservations ADD INDEX idx_date_status (reservation_date, status);
ALTER TABLE maintenance_reports ADD INDEX idx_status_priority (status, priority);

-- ============================================
-- 17. Agregar configuración para soporte técnico
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description)
VALUES 
    ('support_email', 'soporte@residencial.com', 'text', 'Email de soporte técnico'),
    ('support_phone', '', 'text', 'Teléfono de soporte técnico'),
    ('support_url', '', 'text', 'URL de página de soporte público')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- ============================================
-- VERIFICACIÓN FINAL
-- ============================================
SELECT 'Migration 001 completed successfully' as status;
