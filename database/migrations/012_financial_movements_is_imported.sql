-- ============================================
-- Migración 012: Agregar campo is_imported a financial_movements
-- ============================================

SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'financial_movements'
      AND COLUMN_NAME = 'is_imported'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `financial_movements` ADD COLUMN `is_imported` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''1 = importado desde archivo CSV/Excel'' AFTER `notes`',
    'SELECT ''Column financial_movements.is_imported already exists'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
