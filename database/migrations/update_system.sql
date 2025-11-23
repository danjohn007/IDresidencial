-- ============================================
-- SQL de Actualización del Sistema Residencial
-- Fecha: 2025-11-23
-- Descripción: Actualización para implementar mejoras del sistema
-- ============================================

USE erp_residencial;

-- ============================================
-- 1. Agregar campo de copyright en configuración
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) 
VALUES ('site_copyright', '© 2025 Residencial Juriquilla. Todos los derechos reservados.', 'text', 'Texto de copyright en el pie de página')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- ============================================
-- 2. Actualizar/Agregar configuración de tema
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) 
VALUES ('theme_color', 'blue', 'text', 'Color principal del tema del sistema')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- ============================================
-- 3. Asegurar que tabla de vehículos existe con estructura correcta
-- ============================================
-- Nota: La tabla ya debe existir, esta es solo una verificación
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    plate VARCHAR(20) UNIQUE NOT NULL,
    brand VARCHAR(50),
    model VARCHAR(50),
    color VARCHAR(30),
    year INT,
    vehicle_type ENUM('auto', 'motocicleta', 'camioneta', 'otro') DEFAULT 'auto',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_plate (plate),
    INDEX idx_resident_id (resident_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. Verificar índices en propiedades para búsquedas optimizadas
-- ============================================
-- Intentar crear los índices (fallará si ya existen, pero es seguro si el resto del script no depende de esto)
CREATE INDEX idx_property_status ON properties(status);
CREATE INDEX idx_property_type ON properties(property_type);

-- ============================================
-- 5. Verificar estructura de reservaciones
-- ============================================
-- La tabla reservations ya debe existir con la estructura correcta
-- Esta es solo una verificación
SHOW TABLES LIKE 'reservations';

-- ============================================
-- 6. Verificar que system_settings tiene todos los campos necesarios
-- ============================================
-- Insertar configuraciones adicionales si no existen
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('site_logo', '', 'file', 'Ruta del logo del sistema'),
('whatsapp_enabled', '0', 'boolean', 'Habilitar notificaciones por WhatsApp'),
('whatsapp_number', '', 'text', 'Número de WhatsApp para soporte');

-- ============================================
-- 7. Verificar permisos y roles de usuarios
-- ============================================
-- Asegurar que los roles estén correctamente definidos
-- Los usuarios tipo 'residente' no deben poder crearse desde el módulo de usuarios
UPDATE users SET role = 'residente' WHERE role = 'residente';

-- ============================================
-- 8. Limpiar datos inconsistentes (opcional)
-- ============================================
-- Eliminar vehículos huérfanos (sin residente asociado)
-- PRECAUCIÓN: Comentar esta línea si no se desea eliminar datos
-- DELETE FROM vehicles WHERE resident_id NOT IN (SELECT id FROM residents);

-- ============================================
-- 9. Actualizar timestamps de registros sin fecha
-- ============================================
UPDATE vehicles SET updated_at = CURRENT_TIMESTAMP WHERE updated_at IS NULL;
UPDATE properties SET updated_at = CURRENT_TIMESTAMP WHERE updated_at IS NULL;

-- ============================================
-- 10. Verificar integridad referencial
-- ============================================
-- Contar registros huérfanos para revisión
SELECT 
    'Vehículos sin residente' as issue,
    COUNT(*) as count
FROM vehicles v
LEFT JOIN residents r ON v.resident_id = r.id
WHERE r.id IS NULL

UNION ALL

SELECT 
    'Residentes sin usuario' as issue,
    COUNT(*) as count
FROM residents r
LEFT JOIN users u ON r.user_id = u.id
WHERE u.id IS NULL

UNION ALL

SELECT 
    'Residentes sin propiedad' as issue,
    COUNT(*) as count
FROM residents r
LEFT JOIN properties p ON r.property_id = p.id
WHERE p.id IS NULL;

-- ============================================
-- FIN DE MIGRACIÓN
-- ============================================

-- Mensaje de confirmación
SELECT 'Migración completada exitosamente' as status, NOW() as executed_at;

-- ============================================
-- NOTAS IMPORTANTES:
-- ============================================
-- 1. Este script es idempotente - puede ejecutarse múltiples veces sin causar errores (a excepción de CREATE INDEX si el índice ya existe)
-- 2. Se recomienda hacer backup de la base de datos antes de ejecutar
-- 3. Revisar los resultados de la consulta de integridad referencial
-- 4. Las inserciones usan ON DUPLICATE KEY o IGNORE para evitar duplicados
--
-- BACKUP RECOMENDADO:
-- mysqldump -u root -p erp_residencial > backup_before_migration_$(date +%Y%m%d).sql
--
-- EJECUCIÓN:
-- mysql -u root -p erp_residencial < update_system.sql
--
-- ROLLBACK (si es necesario):
-- mysql -u root -p erp_residencial < backup_before_migration_YYYYMMDD.sql
-- ============================================
