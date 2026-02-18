-- ============================================
-- Migration: Financial Reports Adjustments
-- Date: 2026-02-18
-- Description: Updates to support membership plans CRUD,
--              fix subdivision views, user details views,
--              maintenance reports, daily visits tracking,
--              and payment/fee module improvements
-- ============================================

-- ============================================
-- Update maintenance_reports to allow NULL resident_id
-- This allows administrators and guards to create reports
-- for common areas without being residents
-- ============================================

ALTER TABLE maintenance_reports 
    MODIFY resident_id INT(11) NULL;

-- No structural changes needed for existing tables
-- The current schema already supports:
-- 1. membership_plans table exists with proper structure
-- 2. subdivisions table exists with proper structure  
-- 3. maintenance_fees table exists with proper structure
-- 4. visits table exists with proper structure
-- 5. residents table exists and can be linked to users

-- ============================================
-- Add missing columns if they don't exist
-- ============================================

-- Ensure visits table has proper tracking for entry_time
ALTER TABLE visits 
    MODIFY entry_time TIMESTAMP NULL DEFAULT NULL,
    MODIFY exit_time TIMESTAMP NULL DEFAULT NULL;

-- ============================================
-- Add indexes for better performance on reports
-- ============================================

-- Index for daily visits query performance (on raw columns, not functions)
ALTER TABLE visits 
    ADD INDEX IF NOT EXISTS idx_visits_entry_time (entry_time),
    ADD INDEX IF NOT EXISTS idx_visits_created_at (created_at);

-- Index for maintenance fees query performance  
ALTER TABLE maintenance_fees
    ADD INDEX IF NOT EXISTS idx_mf_due_date_status (due_date, status);

-- Index for financial movements query performance
ALTER TABLE financial_movements
    ADD INDEX IF NOT EXISTS idx_fm_transaction_date (transaction_date),
    ADD INDEX IF NOT EXISTS idx_fm_movement_type (movement_type_id);

-- ============================================
-- Ensure membership plans can be managed (CRUD)
-- ============================================
-- Table already exists, no changes needed

-- ============================================
-- Update system settings if needed
-- ============================================

-- Add setting for automatic fee generation
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description)
VALUES 
    ('auto_generate_fees', '1', 'boolean', 'Generar automáticamente cuotas de mantenimiento'),
    ('fee_generation_day', '1', 'number', 'Día del mes para generar cuotas automáticamente'),
    ('include_visits_in_dashboard', '1', 'boolean', 'Incluir visitas registradas en el dashboard además de pases');

-- ============================================
-- Create view for resident payment summary
-- ============================================

CREATE OR REPLACE VIEW v_resident_payment_summary AS
SELECT 
    r.id as resident_id,
    r.property_id,
    p.property_number,
    u.id as user_id,
    CONCAT(u.first_name, ' ', u.last_name) as resident_name,
    u.email,
    u.phone,
    r.status as resident_status,
    COUNT(mf.id) as total_fees,
    SUM(CASE WHEN mf.status = 'paid' THEN 1 ELSE 0 END) as paid_count,
    SUM(CASE WHEN mf.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN mf.status = 'overdue' THEN 1 ELSE 0 END) as overdue_count,
    SUM(mf.amount) as total_amount,
    SUM(CASE WHEN mf.status = 'paid' THEN mf.amount ELSE 0 END) as paid_amount,
    SUM(CASE WHEN mf.status IN ('pending', 'overdue') THEN mf.amount ELSE 0 END) as pending_amount
FROM residents r
INNER JOIN properties p ON r.property_id = p.id
INNER JOIN users u ON r.user_id = u.id
LEFT JOIN maintenance_fees mf ON mf.property_id = p.id
WHERE r.status = 'active' AND r.is_primary = 1
GROUP BY r.id, r.property_id, p.property_number, u.id, u.first_name, u.last_name, u.email, u.phone, r.status;

-- ============================================
-- Create stored procedure to generate pending fees
-- ============================================

DROP PROCEDURE IF EXISTS generate_monthly_fees;

DELIMITER $$

CREATE PROCEDURE generate_monthly_fees()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_property_id INT;
    DECLARE v_amount DECIMAL(10,2);
    DECLARE v_period VARCHAR(7);
    DECLARE v_due_date DATE;
    DECLARE property_cursor CURSOR FOR
        SELECT p.id, 
               COALESCE(
                   (SELECT monthly_cost FROM membership_plans mp 
                    INNER JOIN memberships m ON m.membership_plan_id = mp.id 
                    INNER JOIN residents r ON r.id = m.resident_id AND r.property_id = p.id
                    WHERE m.status = 'active' AND r.is_primary = 1
                    LIMIT 1),
                   1500.00
               ) as amount
        FROM properties p
        INNER JOIN residents r ON r.property_id = p.id
        WHERE p.status = 'ocupada' AND r.status = 'active' AND r.is_primary = 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Calculate period and due date
    SET v_period = DATE_FORMAT(CURDATE(), '%Y-%m');
    SET v_due_date = DATE_FORMAT(CURDATE(), '%Y-%m-10');
    
    -- If due date is in the past for current month, set it to next month
    IF v_due_date < CURDATE() THEN
        SET v_due_date = DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-10');
        SET v_period = DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m');
    END IF;
    
    OPEN property_cursor;
    read_loop: LOOP
        FETCH property_cursor INTO v_property_id, v_amount;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insert fee if not exists for this period
        INSERT INTO maintenance_fees (property_id, period, amount, due_date, status)
        SELECT v_property_id, v_period, v_amount, v_due_date, 'pending'
        WHERE NOT EXISTS (
            SELECT 1 FROM maintenance_fees 
            WHERE property_id = v_property_id AND period = v_period
        );
    END LOOP;
    CLOSE property_cursor;
END$$

DELIMITER ;

-- ============================================
-- Migration completed successfully
-- ============================================
