-- =============================================================
-- Migración 014: Prevenir duplicados en maintenance_fees
-- Solo debe existir un registro de cuota por propiedad por mes
-- =============================================================

-- Paso 1: Eliminar duplicados manteniendo el registro con status='paid' o el de menor id
DELETE mf1 FROM maintenance_fees mf1
INNER JOIN maintenance_fees mf2
    ON mf1.property_id = mf2.property_id
    AND mf1.period = mf2.period
    AND mf1.id > mf2.id
WHERE mf1.status != 'paid';

-- Paso 2: Eliminar duplicados remanentes (mismo property_id y period, conservar el menor id)
DELETE mf1 FROM maintenance_fees mf1
INNER JOIN maintenance_fees mf2
    ON mf1.property_id = mf2.property_id
    AND mf1.period = mf2.period
    AND mf1.id > mf2.id;

-- Paso 3: Agregar restricción UNIQUE si no existe
SET @dbname = DATABASE();
SET @tablename = 'maintenance_fees';
SET @indexname = 'unique_property_period';
SET @preparedStatement = (
    SELECT IF(
        (
            SELECT COUNT(*) FROM information_schema.statistics
            WHERE table_schema = @dbname
              AND table_name = @tablename
              AND index_name = @indexname
        ) > 0,
        'SELECT ''Index already exists'' AS info',
        'ALTER TABLE maintenance_fees ADD UNIQUE KEY unique_property_period (property_id, period)'
    )
);
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
