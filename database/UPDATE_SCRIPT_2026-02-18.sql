-- ============================================
-- 2. OPTIMIZAR ÍNDICES PARA REPORTES
-- ============================================

-- Procedimiento auxiliar para crear índices si no existen
DELIMITER $$

DROP PROCEDURE IF EXISTS `create_index_if_not_exists`$$

CREATE PROCEDURE `create_index_if_not_exists`(
    IN p_table_name VARCHAR(128),
    IN p_index_name VARCHAR(128),
    IN p_columns VARCHAR(255)
)
BEGIN
    DECLARE index_exists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO index_exists
    FROM information_schema.statistics
    WHERE table_schema = DATABASE() COLLATE utf8_general_ci
      AND table_name = p_table_name COLLATE utf8_general_ci
      AND index_name = p_index_name COLLATE utf8_general_ci;
    
    IF index_exists = 0 THEN
        SET @sql = CONCAT('CREATE INDEX `', p_index_name, '` ON `', p_table_name, '` (', p_columns, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Crear índices usando el procedimiento
CALL create_index_if_not_exists('visits', 'idx_visits_entry_time', '`entry_time`');
CALL create_index_if_not_exists('visits', 'idx_visits_created_at', '`created_at`');
CALL create_index_if_not_exists('maintenance_fees', 'idx_mf_due_date_status', '`due_date`, `status`');
CALL create_index_if_not_exists('financial_movements', 'idx_fm_transaction_date', '`transaction_date`');
CALL create_index_if_not_exists('financial_movements', 'idx_fm_movement_type', '`movement_type_id`');

-- Limpiar procedimiento auxiliar
DROP PROCEDURE IF EXISTS `create_index_if_not_exists`;
