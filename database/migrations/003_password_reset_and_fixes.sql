-- ============================================
-- Migration: Password Reset and System Fixes
-- Adds password reset functionality and other improvements
-- ============================================

USE erp_residencial;

-- ============================================
-- TABLA: Password Resets
-- Para almacenar tokens de recuperación de contraseña
-- ============================================
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
-- Asegurar que las tablas necesarias existan
-- ============================================

-- Verificar/Crear tabla de audit_logs si no existe
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    table_name VARCHAR(100),
    record_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_table_record (table_name, record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insertar registros de auditoría iniciales
-- ============================================
INSERT INTO audit_logs (user_id, action, description, ip_address, created_at)
SELECT 
    1, 
    'system', 
    'Sistema inicializado y migraciones aplicadas',
    '127.0.0.1',
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM audit_logs WHERE action = 'system' AND description LIKE '%migraciones aplicadas%'
);

-- ============================================
-- Agregar índices adicionales para optimización
-- ============================================

-- Índice para búsquedas de residentes por usuario
ALTER TABLE residents 
ADD INDEX IF NOT EXISTS idx_status (status);

-- Índice para búsquedas de membresías
ALTER TABLE IF EXISTS memberships 
ADD INDEX IF NOT EXISTS idx_status (status),
ADD INDEX IF NOT EXISTS idx_dates (start_date, end_date);

-- Índice para movimientos financieros
ALTER TABLE IF EXISTS financial_movements 
ADD INDEX IF NOT EXISTS idx_transaction_date (transaction_date),
ADD INDEX IF NOT EXISTS idx_transaction_type (transaction_type);

-- ============================================
-- Verificar configuraciones del sistema
-- ============================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones por defecto si no existen
INSERT IGNORE INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'ERP Residencial', 'Nombre del sitio'),
('site_email', 'admin@residencial.com', 'Email de contacto del sitio'),
('site_phone', '+52 442 123 4567', 'Teléfono de contacto'),
('maintenance_fee_default', '500.00', 'Cuota de mantenimiento por defecto'),
('password_reset_expiry', '3600', 'Tiempo de expiración del token de reset (segundos)'),
('audit_retention_days', '180', 'Días de retención de logs de auditoría');

-- ============================================
-- Actualizar datos existentes si es necesario
-- ============================================

-- Asegurar que todos los usuarios tengan un rol válido
UPDATE users SET role = 'residente' WHERE role IS NULL OR role = '';

-- Asegurar que todos los usuarios tengan un estado válido
UPDATE users SET status = 'active' WHERE status IS NULL OR status = '';

-- ============================================
-- Verificar integridad de datos
-- ============================================

-- Limpiar registros huérfanos en residents (sin usuario válido)
DELETE FROM residents WHERE user_id NOT IN (SELECT id FROM users);

-- Limpiar registros huérfanos en audit_logs (más de 6 meses)
DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 180 DAY);

-- ============================================
-- Fin de la migración
-- ============================================
SELECT 'Migration 003_password_reset_and_fixes.sql completed successfully' AS status;
