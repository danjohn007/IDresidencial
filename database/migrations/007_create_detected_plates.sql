-- ============================================
-- TABLA: Placas Detectadas por Cámara HikVision
-- Sistema de reconocimiento automático de placas (LPR)
-- ============================================

CREATE TABLE IF NOT EXISTS detected_plates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Información de la placa detectada
    plate_text VARCHAR(20) NOT NULL COMMENT 'Texto de la placa detectada por la cámara',
    captured_at TIMESTAMP NOT NULL COMMENT 'Fecha y hora de captura',
    
    -- Información del dispositivo
    unit_id INT COMMENT 'ID de la unidad/cámara específica',
    
    -- Validación y coincidencia
    is_match BOOLEAN DEFAULT 0 COMMENT '1 si coincide con placa registrada, 0 si no',
    matched_vehicle_id INT COMMENT 'ID del vehículo que coincidió (si aplica)',
    
    -- Información adicional
    payload_json JSON COMMENT 'Datos completos del payload enviado por HikVision',
    
    -- Estado de procesamiento
    status ENUM('new', 'processed', 'authorized', 'rejected', 'error') DEFAULT 'new' COMMENT 'Estado del procesamiento',
    processed_at TIMESTAMP NULL COMMENT 'Fecha y hora de procesamiento',
    
    -- Notas y observaciones
    notes TEXT COMMENT 'Notas adicionales o motivo de rechazo',
    
    -- Metadatos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para optimizar búsquedas
    INDEX idx_plate_text (plate_text),
    INDEX idx_captured_at (captured_at),
    INDEX idx_status (status),
    INDEX idx_is_match (is_match),
    INDEX idx_matched_vehicle_id (matched_vehicle_id),
    
    -- Claves foráneas
    FOREIGN KEY (matched_vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insertar configuraciones del sistema
-- ============================================

INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('lpr_enabled', '1', 'boolean', 'Habilitar reconocimiento automático de placas'),
('lpr_auto_open_gate', '1', 'boolean', 'Abrir puerta automáticamente si hay coincidencia'),
('lpr_retention_days', '90', 'number', 'Días para retener registros de placas detectadas')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- ============================================
-- Datos de ejemplo para pruebas
-- ============================================

-- Ejemplo 1: Placa procesada
INSERT INTO detected_plates (
    plate_text, 
    captured_at, 
    unit_id, 
    is_match, 
    status
) VALUES (
    'ABC123X', 
    '2025-11-10 14:24:10', 
    1, 
    NULL, 
    'processed'
);

-- Ejemplo 2: Placa procesada
INSERT INTO detected_plates (
    plate_text, 
    captured_at, 
    unit_id, 
    is_match, 
    status
) VALUES (
    'DEF456Y', 
    '2025-11-12 10:31:24', 
    1, 
    NULL, 
    'processed'
);

-- Ejemplo 3: Placa NO registrada
INSERT INTO detected_plates (
    plate_text, 
    captured_at, 
    is_match, 
    status
) VALUES (
    'GHI789P', 
    '2025-11-12 10:28:24', 
    0, 
    'new'
);

-- Ejemplo 4: Placa nueva sin procesar
INSERT INTO detected_plates (
    plate_text, 
    captured_at, 
    is_match, 
    status
) VALUES (
    'WA000A', 
    '2025-11-12 11:58:46', 
    0, 
    'new'
);

-- Ejemplo 5: Otra placa nueva
INSERT INTO detected_plates (
    plate_text, 
    captured_at, 
    is_match, 
    status
) VALUES (
    'YWA000A', 
    '2025-11-12 11:59:02', 
    0, 
    'new'
);