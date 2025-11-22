-- ============================================
-- TABLA: Dispositivos de Control de Acceso
-- HikVision y Shelly
-- ============================================

CREATE TABLE IF NOT EXISTS access_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(100) NOT NULL,
    device_type ENUM('hikvision', 'shelly') NOT NULL,
    device_id VARCHAR(100) UNIQUE NOT NULL COMMENT 'Device ID único (MAC, Serial, etc)',
    
    -- Configuración de red
    ip_address VARCHAR(45),
    port INT DEFAULT 80,
    username VARCHAR(100),
    password VARCHAR(255),
    
    -- Configuración específica de Shelly
    auth_token VARCHAR(255) COMMENT 'Token de autenticación para Shelly Cloud',
    cloud_server VARCHAR(255) COMMENT 'Servidor de Shelly Cloud',
    input_channel INT DEFAULT 1 COMMENT 'Canal de entrada (para apertura)',
    output_channel INT DEFAULT 0 COMMENT 'Canal de salida (para cierre)',
    pulse_duration INT DEFAULT 4000 COMMENT 'Duración del pulso en ms (4000ms = 4s)',
    open_time INT DEFAULT 5 COMMENT 'Tiempo que la puerta permanece abierta en segundos',
    inverted BOOLEAN DEFAULT FALSE COMMENT 'Invertido (off → on)',
    simultaneous BOOLEAN DEFAULT FALSE COMMENT 'Dispositivo simultáneo',
    
    -- Configuración específica de HikVision
    door_number INT COMMENT 'Número de puerta del dispositivo HikVision (1-8)',
    
    -- Información de ubicación
    branch_id INT COMMENT 'Sucursal asociada',
    location VARCHAR(255) COMMENT 'Ubicación física del dispositivo',
    area VARCHAR(255) COMMENT 'Área específica',
    
    -- Estado y configuración
    status ENUM('online', 'offline', 'error', 'disabled') DEFAULT 'offline',
    enabled BOOLEAN DEFAULT TRUE,
    last_online TIMESTAMP NULL,
    last_test TIMESTAMP NULL,
    
    -- Metadatos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    
    INDEX idx_device_type (device_type),
    INDEX idx_device_id (device_id),
    INDEX idx_status (status),
    INDEX idx_branch_id (branch_id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Logs de Acciones de Dispositivos
-- ============================================

CREATE TABLE IF NOT EXISTS device_action_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    action ENUM('open', 'close', 'test', 'status_check', 'config_update') NOT NULL,
    action_by INT COMMENT 'Usuario que realizó la acción',
    success BOOLEAN DEFAULT TRUE,
    error_message TEXT,
    response_time INT COMMENT 'Tiempo de respuesta en ms',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_device_id (device_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (device_id) REFERENCES access_devices(id) ON DELETE CASCADE,
    FOREIGN KEY (action_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insertar configuraciones del sistema para dispositivos
-- ============================================

INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('devices_enabled', '1', 'boolean', 'Habilitar gestión de dispositivos de acceso'),
('devices_auto_status_check', '1', 'boolean', 'Verificar estado de dispositivos automáticamente'),
('devices_status_check_interval', '300', 'number', 'Intervalo de verificación de estado en segundos')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
