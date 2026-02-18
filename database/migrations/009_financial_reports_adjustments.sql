-- ============================================
-- Add indexes for better performance on reports
-- ============================================

-- Index for daily visits query performance (on raw columns, not functions)
-- Check and add indexes only if they don't exist

-- For visits table
DROP PROCEDURE IF EXISTS add_visits_indexes;

DELIMITER $$
CREATE PROCEDURE add_visits_indexes()
BEGIN
    -- Check and add idx_visits_entry_time
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'visits' 
        AND index_name = 'idx_visits_entry_time'
    ) THEN
        ALTER TABLE visits ADD INDEX idx_visits_entry_time (entry_time);
    END IF;
    
    -- Check and add idx_visits_created_at
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'visits' 
        AND index_name = 'idx_visits_created_at'
    ) THEN
        ALTER TABLE visits ADD INDEX idx_visits_created_at (created_at);
    END IF;
END$$
DELIMITER ;

CALL add_visits_indexes();
DROP PROCEDURE add_visits_indexes;

-- Index for maintenance fees query performance
DROP PROCEDURE IF EXISTS add_maintenance_fees_indexes;

DELIMITER $$
CREATE PROCEDURE add_maintenance_fees_indexes()
BEGIN
    -- Check and add idx_mf_due_date_status
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'maintenance_fees' 
        AND index_name = 'idx_mf_due_date_status'
    ) THEN
        ALTER TABLE maintenance_fees ADD INDEX idx_mf_due_date_status (due_date, status);
    END IF;
END$$
DELIMITER ;

CALL add_maintenance_fees_indexes();
DROP PROCEDURE add_maintenance_fees_indexes;

-- Index for financial movements query performance
DROP PROCEDURE IF EXISTS add_financial_movements_indexes;

DELIMITER $$
CREATE PROCEDURE add_financial_movements_indexes()
BEGIN
    -- Check and add idx_fm_transaction_date
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'financial_movements' 
        AND index_name = 'idx_fm_transaction_date'
    ) THEN
        ALTER TABLE financial_movements ADD INDEX idx_fm_transaction_date (transaction_date);
    END IF;
    
    -- Check and add idx_fm_movement_type
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'financial_movements' 
        AND index_name = 'idx_fm_movement_type'
    ) THEN
        ALTER TABLE financial_movements ADD INDEX idx_fm_movement_type (movement_type_id);
    END IF;
END$$
DELIMITER ;

CALL add_financial_movements_indexes();
DROP PROCEDURE add_financial_movements_indexes;
