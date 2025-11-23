-- ============================================
-- Migración: Nuevas Funcionalidades
-- Fecha: 2025-11-23
-- Descripción: Agregar tablas para módulo financiero, 
--              membresías, auditoría y reportes
-- ============================================

USE erp_residencial;

-- ============================================
-- TABLA: Auditoría del Sistema
-- ============================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    table_name VARCHAR(100),
    record_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_table_name (table_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Catálogo de Tipos de Movimiento Financiero
-- ============================================
CREATE TABLE IF NOT EXISTS financial_movement_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('ingreso', 'egreso', 'ambos') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Movimientos Financieros
-- ============================================
CREATE TABLE IF NOT EXISTS financial_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movement_type_id INT NOT NULL,
    transaction_type ENUM('ingreso', 'egreso') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    reference_type VARCHAR(50), -- 'maintenance_fee', 'reservation', 'penalty', 'membership', 'other'
    reference_id INT, -- ID del registro relacionado
    property_id INT,
    resident_id INT,
    payment_method ENUM('efectivo', 'tarjeta', 'transferencia', 'paypal', 'otro'),
    payment_reference VARCHAR(100),
    transaction_date DATE NOT NULL,
    created_by INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (movement_type_id) REFERENCES financial_movement_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_property_id (property_id),
    INDEX idx_resident_id (resident_id),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Planes de Membresía
-- ============================================
CREATE TABLE IF NOT EXISTS membership_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    monthly_cost DECIMAL(10,2) NOT NULL,
    benefits TEXT, -- JSON con lista de beneficios
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Membresías de Residentes
-- ============================================
CREATE TABLE IF NOT EXISTS memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    membership_plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'suspended', 'cancelled', 'expired') DEFAULT 'active',
    payment_day INT DEFAULT 1, -- Día del mes para el pago
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_plan_id) REFERENCES membership_plans(id) ON DELETE RESTRICT,
    INDEX idx_resident_id (resident_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Pagos de Membresías
-- ============================================
CREATE TABLE IF NOT EXISTS membership_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membership_id INT NOT NULL,
    period VARCHAR(7) NOT NULL, -- YYYY-MM
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE,
    payment_method ENUM('efectivo', 'tarjeta', 'transferencia', 'paypal', 'otro'),
    payment_reference VARCHAR(100),
    status ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    financial_movement_id INT, -- Relación con movimiento financiero
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE CASCADE,
    FOREIGN KEY (financial_movement_id) REFERENCES financial_movements(id) ON DELETE SET NULL,
    INDEX idx_membership_id (membership_id),
    INDEX idx_period (period),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERTAR TIPOS DE MOVIMIENTO PREDETERMINADOS
-- ============================================
INSERT INTO financial_movement_types (name, description, category) VALUES
('Cuota de Mantenimiento', 'Pago mensual de cuota de mantenimiento', 'ingreso'),
('Reservación de Amenidades', 'Pago por reservación de amenidades', 'ingreso'),
('Penalización', 'Multas y penalizaciones', 'ingreso'),
('Membresía Mensual', 'Pago de membresía mensual', 'ingreso'),
('Otros Ingresos', 'Ingresos diversos', 'ingreso'),
('Mantenimiento General', 'Gastos de mantenimiento del residencial', 'egreso'),
('Servicios Públicos', 'Pago de luz, agua, etc.', 'egreso'),
('Personal', 'Pago de nómina y personal', 'egreso'),
('Proveedores', 'Pago a proveedores externos', 'egreso'),
('Reparaciones', 'Gastos de reparaciones y mejoras', 'egreso'),
('Seguridad', 'Gastos relacionados con seguridad', 'egreso'),
('Otros Egresos', 'Egresos diversos', 'egreso');

-- ============================================
-- INSERTAR PLANES DE MEMBRESÍA PREDETERMINADOS
-- ============================================
INSERT INTO membership_plans (name, description, monthly_cost, benefits, is_active) VALUES
('Básico', 'Plan básico con acceso a amenidades estándar', 500.00, '["Acceso a alberca", "Acceso a gimnasio", "2 reservaciones mensuales"]', TRUE),
('Premium', 'Plan premium con beneficios adicionales', 1000.00, '["Acceso a alberca", "Acceso a gimnasio", "Reservaciones ilimitadas", "Descuento 10% en eventos", "Invitados sin costo"]', TRUE),
('VIP', 'Plan VIP con todos los beneficios', 1500.00, '["Acceso a alberca", "Acceso a gimnasio", "Reservaciones prioritarias", "Descuento 20% en eventos", "Invitados sin costo", "Acceso a áreas exclusivas"]', TRUE);

-- ============================================
-- ACTUALIZAR TABLA DE USUARIOS
-- Agregar campo house_number para residentes
-- ============================================
ALTER TABLE users 
ADD COLUMN house_number VARCHAR(20) AFTER phone,
ADD INDEX idx_house_number (house_number);

-- ============================================
-- MIGRAR PAGOS EXISTENTES A MOVIMIENTOS FINANCIEROS
-- ============================================
-- Migrar cuotas de mantenimiento pagadas a movimientos financieros
INSERT INTO financial_movements 
    (movement_type_id, transaction_type, amount, description, reference_type, reference_id, property_id, payment_method, payment_reference, transaction_date, created_by, created_at)
SELECT 
    1, -- ID del tipo 'Cuota de Mantenimiento'
    'ingreso',
    mf.amount,
    CONCAT('Cuota de mantenimiento - ', mf.period),
    'maintenance_fee',
    mf.id,
    mf.property_id,
    mf.payment_method,
    mf.payment_reference,
    COALESCE(mf.paid_date, mf.due_date),
    1, -- Usuario admin por defecto
    mf.updated_at
FROM maintenance_fees mf
WHERE mf.status = 'paid' 
  AND NOT EXISTS (
    SELECT 1 FROM financial_movements fm 
    WHERE fm.reference_type = 'maintenance_fee' 
    AND fm.reference_id = mf.id
  );

-- Migrar reservaciones pagadas a movimientos financieros
INSERT INTO financial_movements 
    (movement_type_id, transaction_type, amount, description, reference_type, reference_id, resident_id, payment_method, transaction_date, created_by, created_at)
SELECT 
    2, -- ID del tipo 'Reservación de Amenidades'
    'ingreso',
    r.amount,
    CONCAT('Reservación de amenidad - ', a.name, ' - ', r.reservation_date),
    'reservation',
    r.id,
    r.resident_id,
    CASE 
        WHEN r.payment_status = 'paid' THEN 'transferencia'
        ELSE NULL
    END,
    r.reservation_date,
    1, -- Usuario admin por defecto
    r.updated_at
FROM reservations r
INNER JOIN amenities a ON r.amenity_id = a.id
WHERE r.payment_status = 'paid' 
  AND r.amount > 0
  AND NOT EXISTS (
    SELECT 1 FROM financial_movements fm 
    WHERE fm.reference_type = 'reservation' 
    AND fm.reference_id = r.id
  );

-- Migrar penalizaciones pagadas a movimientos financieros
INSERT INTO financial_movements 
    (movement_type_id, transaction_type, amount, description, reference_type, reference_id, resident_id, transaction_date, created_by, created_at)
SELECT 
    3, -- ID del tipo 'Penalización'
    'ingreso',
    p.amount,
    p.description,
    'penalty',
    p.id,
    p.resident_id,
    p.penalty_date,
    1, -- Usuario admin por defecto
    p.updated_at
FROM penalties p
WHERE p.status = 'paid'
  AND NOT EXISTS (
    SELECT 1 FROM financial_movements fm 
    WHERE fm.reference_type = 'penalty' 
    AND fm.reference_id = p.id
  );

-- ============================================
-- COMENTARIOS FINALES
-- ============================================
-- Este script crea las tablas necesarias para:
-- 1. Módulo Financiero con catálogo de movimientos
-- 2. Sistema de Auditoría
-- 3. Módulo de Membresías
-- 4. Integración de pagos existentes con el módulo financiero
--
-- Los datos existentes son preservados y migrados automáticamente
-- al nuevo sistema financiero.
-- ============================================
