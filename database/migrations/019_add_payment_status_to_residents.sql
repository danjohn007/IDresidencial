-- ============================================
-- Migración 019: Agregar payment_status a tabla residents
-- Fecha: 2026-03-14
-- Descripción: Agrega columna para rastrear el estado de pago de residentes (current/moroso/suspended)
-- ============================================

-- Verificar si la columna ya existe antes de agregarla
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'residents' 
    AND COLUMN_NAME = 'payment_status'
);

-- Agregar columna payment_status si no existe
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE residents 
     ADD COLUMN payment_status ENUM(''current'', ''moroso'', ''suspended'') 
     DEFAULT ''current'' 
     COMMENT ''Estado de pago del residente'' 
     AFTER status',
    'SELECT "La columna payment_status ya existe en residents" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar índice para optimizar búsquedas por estado de pago
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'residents' 
    AND INDEX_NAME = 'idx_payment_status'
);

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE residents ADD INDEX idx_payment_status (payment_status)',
    'SELECT "El índice idx_payment_status ya existe en residents" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Inicializar todos los residentes activos como 'current'
UPDATE residents 
SET payment_status = 'current' 
WHERE status = 'active' 
AND (payment_status IS NULL OR payment_status = '');

-- ============================================
-- Notas de implementación:
-- ============================================
-- Estados disponibles:
--   - 'current': Residente al corriente en sus pagos
--   - 'moroso':  Residente con más de 60 días de atraso (Tier 3)
--   - 'suspended': Residente suspendido manualmente por administración
--
-- Uso:
--   - El cron apply_late_penalties.php marca como 'moroso' automáticamente
--   - Se puede usar para filtros y reportes
--   - Permite restringir acceso a amenidades para morosos
-- ============================================
