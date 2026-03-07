-- ============================================
-- Migración 011: Reglas de Penalización y Áreas de Dispositivos
-- ============================================

-- ============================================
-- TABLA: Reglas de Penalización
-- ============================================
CREATE TABLE IF NOT EXISTS penalty_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT COMMENT 'Sucursal/fraccionamiento',
    cut_day_type ENUM('first', 'last', 'custom') NOT NULL DEFAULT 'first' COMMENT 'Tipo de día de corte',
    cut_day INT DEFAULT 1 COMMENT 'Día de corte (1-28) cuando cut_day_type=custom',
    grace_days INT DEFAULT 0 COMMENT 'Días de gracia (máximo 15)',

    -- Penalización: Después del día de corte
    after_cutday_type ENUM('amount', 'percentage') DEFAULT 'percentage',
    after_cutday_value DECIMAL(10,2) DEFAULT 0 COMMENT 'Monto o porcentaje de penalización tras el corte',

    -- Penalización: Al mes siguiente
    next_month_type ENUM('amount', 'percentage') DEFAULT 'percentage',
    next_month_value DECIMAL(10,2) DEFAULT 0 COMMENT 'Monto o porcentaje al mes siguiente',

    -- Penalización: Al segundo mes (moroso, retiro de servicios)
    second_month_type ENUM('amount', 'percentage') DEFAULT 'percentage',
    second_month_value DECIMAL(10,2) DEFAULT 0 COMMENT 'Monto o porcentaje al segundo mes',

    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Áreas de Dispositivos
-- ============================================
CREATE TABLE IF NOT EXISTS device_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Nombre del área',
    description VARCHAR(255) COMMENT 'Descripción del área',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_area_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Reporte de Morosidad (caché)
-- ============================================
CREATE TABLE IF NOT EXISTS delinquency_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    resident_id INT,
    month_overdue INT NOT NULL COMMENT '1=primer mes, 2=segundo mes+',
    total_overdue DECIMAL(12,2) DEFAULT 0,
    penalty_applied DECIMAL(12,2) DEFAULT 0,
    services_suspended TINYINT(1) DEFAULT 0,
    report_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Agregar campo is_vigilance_committee si no existe (MySQL 5.7.23 compatible)
-- ============================================
SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'is_vigilance_committee'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `is_vigilance_committee` TINYINT(1) NOT NULL DEFAULT 0 AFTER `last_login`',
    'SELECT ''Column users.is_vigilance_committee already exists'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- Actualizar formato de QR en resident_access_passes
-- ============================================
-- Asegurar que la tabla existe (creada en migraciones previas)
CREATE TABLE IF NOT EXISTS resident_access_passes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    pass_type ENUM('single_use', 'temporary', 'permanent') DEFAULT 'single_use',
    qr_code VARCHAR(50) NOT NULL UNIQUE,
    valid_from DATETIME NOT NULL,
    valid_until DATETIME,
    max_uses INT DEFAULT 1,
    uses_count INT DEFAULT 0,
    notes TEXT,
    status ENUM('active', 'used', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_qr_code (qr_code),
    INDEX idx_resident_id (resident_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insertar datos de ejemplo para áreas
-- ============================================
INSERT IGNORE INTO device_areas (name, description) VALUES
    ('Entrada Principal', 'Acceso principal del fraccionamiento'),
    ('Entrada Secundaria', 'Acceso secundario del fraccionamiento'),
    ('Caseta de Vigilancia', 'Área de caseta de vigilancia'),
    ('Área Común', 'Áreas comunes del fraccionamiento'),
    ('Estacionamiento', 'Área de estacionamiento');
