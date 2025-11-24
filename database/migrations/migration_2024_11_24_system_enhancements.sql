-- ============================================
-- Migration Script for System Enhancements
-- Date: 2024-11-24
-- Description: Updates for resident access fixes, optimization settings, and amenities calendar
-- ============================================

USE erp_residencial;

-- ============================================
-- TABLA: Resident Access Passes
-- For resident-generated access passes (QR codes)
-- ============================================
CREATE TABLE IF NOT EXISTS resident_access_passes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    pass_type ENUM('single_use', 'multi_use', 'temporary') DEFAULT 'single_use',
    qr_code VARCHAR(255) UNIQUE NOT NULL,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NULL,
    max_uses INT DEFAULT 1,
    uses_count INT DEFAULT 0,
    notes TEXT,
    status ENUM('active', 'used', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_resident_id (resident_id),
    INDEX idx_qr_code (qr_code),
    INDEX idx_status (status),
    INDEX idx_valid_until (valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Financial Movements
-- For tracking all financial transactions
-- ============================================
CREATE TABLE IF NOT EXISTS financial_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movement_type_id INT,
    transaction_type ENUM('ingreso', 'egreso') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    property_id INT,
    resident_id INT,
    payment_method ENUM('efectivo', 'tarjeta', 'transferencia', 'paypal', 'otro'),
    payment_reference VARCHAR(100),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    reference_type VARCHAR(50),
    reference_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_property_id (property_id),
    INDEX idx_resident_id (resident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Financial Movement Types
-- For categorizing financial movements
-- ============================================
CREATE TABLE IF NOT EXISTS financial_movement_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('ingreso', 'egreso') NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Audit Logs
-- For system audit trail
-- ============================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    table_name VARCHAR(100),
    record_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert default system settings for optimization
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
    ('cache_enabled', '1', 'Enable system cache for better performance'),
    ('cache_ttl', '3600', 'Cache time to live in seconds'),
    ('query_cache_enabled', '1', 'Enable query result caching'),
    ('max_records_per_page', '50', 'Maximum records to display per page'),
    ('image_optimization', '1', 'Enable automatic image optimization'),
    ('lazy_loading', '1', 'Enable lazy loading for images'),
    ('minify_assets', '0', 'Enable asset minification'),
    ('session_timeout', '3600', 'Session timeout in seconds')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    description = VALUES(description);

-- ============================================
-- Insert default system settings for support
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
    ('support_email', 'soporte@residencial.com', 'Technical support email'),
    ('support_phone', '+52 442 123 4567', 'Technical support phone'),
    ('support_hours', 'Lunes a Viernes 9:00 AM - 6:00 PM', 'Support service hours'),
    ('support_url', '', 'Public support portal URL')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    description = VALUES(description);

-- ============================================
-- Update residents table to support soft delete
-- ============================================
ALTER TABLE residents 
    MODIFY COLUMN status ENUM('active', 'inactive', 'pending', 'deleted') DEFAULT 'active';

-- ============================================
-- Update users table to support soft delete
-- ============================================
ALTER TABLE users 
    MODIFY COLUMN status ENUM('active', 'inactive', 'blocked', 'pending', 'deleted') DEFAULT 'active';

-- ============================================
-- Add indexes for better performance
-- ============================================
-- Users table
ALTER TABLE users ADD INDEX idx_first_name (first_name);
ALTER TABLE users ADD INDEX idx_last_name (last_name);
ALTER TABLE users ADD INDEX idx_phone (phone);

-- Residents table  
ALTER TABLE residents ADD INDEX idx_status (status);

-- Properties table
ALTER TABLE properties ADD INDEX idx_status (status);

-- Amenities table
ALTER TABLE amenities ADD INDEX idx_status (status);

-- Reservations table
ALTER TABLE reservations ADD INDEX idx_payment_status (payment_status);
ALTER TABLE reservations ADD INDEX idx_reservation_date_status (reservation_date, status);

-- ============================================
-- Create view for active residents with property info
-- ============================================
CREATE OR REPLACE VIEW v_active_residents AS
SELECT 
    r.id as resident_id,
    r.user_id,
    r.property_id,
    r.relationship,
    r.is_primary,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.phone,
    u.status as user_status,
    p.property_number,
    p.section,
    p.tower,
    p.status as property_status
FROM residents r
JOIN users u ON r.user_id = u.id
JOIN properties p ON r.property_id = p.id
WHERE r.status = 'active' AND u.status = 'active';

-- ============================================
-- Create view for reservation calendar
-- ============================================
CREATE OR REPLACE VIEW v_reservation_calendar AS
SELECT 
    r.id as reservation_id,
    r.amenity_id,
    r.resident_id,
    r.reservation_date,
    r.start_time,
    r.end_time,
    r.guests_count,
    r.amount,
    r.payment_status,
    r.status,
    a.name as amenity_name,
    a.amenity_type,
    res.property_id,
    p.property_number,
    p.section,
    u.first_name,
    u.last_name,
    u.email,
    u.phone
FROM reservations r
JOIN amenities a ON r.amenity_id = a.id
JOIN residents res ON r.resident_id = res.id
JOIN users u ON res.user_id = u.id
JOIN properties p ON res.property_id = p.id
WHERE r.status NOT IN ('cancelled');

-- ============================================
-- Optimize existing tables
-- ============================================
OPTIMIZE TABLE users;
OPTIMIZE TABLE residents;
OPTIMIZE TABLE properties;
OPTIMIZE TABLE amenities;
OPTIMIZE TABLE reservations;
OPTIMIZE TABLE maintenance_fees;
OPTIMIZE TABLE access_logs;
OPTIMIZE TABLE audit_logs;

-- ============================================
-- Clean up old data (optional - run with caution)
-- ============================================
-- Delete audit logs older than 180 days
-- DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 180 DAY);

-- Delete expired visits
-- UPDATE visits SET status = 'expired' WHERE status = 'active' AND valid_until < NOW();

-- ============================================
-- Grant necessary permissions (adjust as needed for your setup)
-- ============================================
-- GRANT SELECT, INSERT, UPDATE, DELETE ON erp_residencial.* TO 'janetzy_residencial'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================
-- Migration complete
-- ============================================
SELECT 'Migration completed successfully!' as message;
