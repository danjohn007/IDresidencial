-- ============================================
-- ERP Residencial - Base de Datos
-- Sistema de gestión para fraccionamientos
-- ============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS erp_residencial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE erp_residencial;

-- ============================================
-- TABLA: Usuarios (con roles)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'administrador', 'guardia', 'residente') DEFAULT 'residente',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    photo VARCHAR(255),
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Propiedades (Lotes/Viviendas)
-- ============================================
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_number VARCHAR(20) UNIQUE NOT NULL,
    street VARCHAR(100),
    section VARCHAR(50),
    tower VARCHAR(50),
    property_type ENUM('casa', 'departamento', 'lote') DEFAULT 'casa',
    bedrooms INT DEFAULT 0,
    bathrooms INT DEFAULT 0,
    area_m2 DECIMAL(10,2),
    status ENUM('ocupada', 'desocupada', 'en_construccion') DEFAULT 'ocupada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_property_number (property_number),
    INDEX idx_section (section),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Residentes (vinculación con propiedades)
-- ============================================
CREATE TABLE IF NOT EXISTS residents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    relationship ENUM('propietario', 'inquilino', 'familiar') DEFAULT 'propietario',
    contract_start DATE,
    contract_end DATE,
    is_primary BOOLEAN DEFAULT FALSE,
    documents_path VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_property_id (property_id),
    INDEX idx_relationship (relationship)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Vehículos
-- ============================================
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
    INDEX idx_resident_id (resident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Visitas
-- ============================================
CREATE TABLE IF NOT EXISTS visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    visitor_name VARCHAR(100) NOT NULL,
    visitor_id VARCHAR(50),
    visitor_phone VARCHAR(20),
    vehicle_plate VARCHAR(20),
    visit_type ENUM('personal', 'proveedor', 'delivery', 'otro') DEFAULT 'personal',
    qr_code VARCHAR(255) UNIQUE,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    entry_time TIMESTAMP NULL,
    exit_time TIMESTAMP NULL,
    guard_entry_id INT,
    guard_exit_id INT,
    status ENUM('pending', 'active', 'completed', 'cancelled', 'expired') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (guard_entry_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (guard_exit_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_resident_id (resident_id),
    INDEX idx_qr_code (qr_code),
    INDEX idx_status (status),
    INDEX idx_entry_time (entry_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Bitácora de Accesos
-- ============================================
CREATE TABLE IF NOT EXISTS access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    log_type ENUM('resident', 'visit', 'vehicle', 'provider') NOT NULL,
    reference_id INT,
    access_type ENUM('entry', 'exit') NOT NULL,
    access_method ENUM('qr', 'rfid', 'manual', 'plate_recognition') NOT NULL,
    property_id INT,
    name VARCHAR(150),
    vehicle_plate VARCHAR(20),
    guard_id INT,
    notes TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (guard_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_log_type (log_type),
    INDEX idx_access_type (access_type),
    INDEX idx_timestamp (timestamp),
    INDEX idx_property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Amenidades (Casa Club)
-- ============================================
CREATE TABLE IF NOT EXISTS amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    capacity INT DEFAULT 0,
    amenity_type ENUM('salon', 'alberca', 'asadores', 'cancha', 'gimnasio', 'otro') NOT NULL,
    hourly_rate DECIMAL(10,2) DEFAULT 0.00,
    hours_open TIME,
    hours_close TIME,
    days_available VARCHAR(50),
    requires_payment BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_amenity_type (amenity_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Reservaciones
-- ============================================
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amenity_id INT NOT NULL,
    resident_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    guests_count INT DEFAULT 0,
    amount DECIMAL(10,2) DEFAULT 0.00,
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    cancellation_reason TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_amenity_id (amenity_id),
    INDEX idx_resident_id (resident_id),
    INDEX idx_reservation_date (reservation_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Penalizaciones
-- ============================================
CREATE TABLE IF NOT EXISTS penalties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    reservation_id INT,
    penalty_type ENUM('no_show', 'damage', 'overtime', 'rule_violation', 'other') NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) DEFAULT 0.00,
    penalty_date DATE NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    block_until DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    INDEX idx_resident_id (resident_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Cuotas de Mantenimiento
-- ============================================
CREATE TABLE IF NOT EXISTS maintenance_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    period VARCHAR(7) NOT NULL, -- YYYY-MM
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE,
    payment_method ENUM('efectivo', 'tarjeta', 'transferencia', 'paypal', 'otro'),
    payment_reference VARCHAR(100),
    status ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id),
    INDEX idx_period (period),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Incidentes/Reportes de Mantenimiento
-- ============================================
CREATE TABLE IF NOT EXISTS maintenance_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    property_id INT,
    category ENUM('alumbrado', 'jardineria', 'plomeria', 'seguridad', 'limpieza', 'otro') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    location VARCHAR(200),
    assigned_to INT,
    status ENUM('pendiente', 'en_proceso', 'completado', 'cancelado') DEFAULT 'pendiente',
    photos TEXT, -- JSON array de rutas
    estimated_completion DATE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_resident_id (resident_id),
    INDEX idx_category (category),
    INDEX idx_priority (priority),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Comentarios de Mantenimiento
-- ============================================
CREATE TABLE IF NOT EXISTS maintenance_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES maintenance_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_report_id (report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Rondines/Patrullaje
-- ============================================
CREATE TABLE IF NOT EXISTS security_patrols (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guard_id INT NOT NULL,
    patrol_start TIMESTAMP NOT NULL,
    patrol_end TIMESTAMP,
    route TEXT,
    incidents_found TEXT,
    notes TEXT,
    status ENUM('in_progress', 'completed') DEFAULT 'in_progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guard_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_guard_id (guard_id),
    INDEX idx_patrol_start (patrol_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Alertas de Seguridad
-- ============================================
CREATE TABLE IF NOT EXISTS security_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('intrusion', 'fire', 'medical', 'vandalism', 'noise', 'other') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    location VARCHAR(200),
    description TEXT NOT NULL,
    reported_by INT,
    status ENUM('open', 'in_progress', 'resolved', 'false_alarm') DEFAULT 'open',
    resolved_by INT,
    resolved_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Comunicados
-- ============================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    target_audience VARCHAR(100) DEFAULT 'all', -- all, section, tower, specific_properties
    target_filter TEXT, -- JSON con filtros específicos
    sent_via TEXT, -- JSON: ['app', 'email', 'whatsapp']
    scheduled_for TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('draft', 'scheduled', 'sent') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Notificaciones
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: Configuración del Sistema
-- ============================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json', 'file') DEFAULT 'text',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS DE EJEMPLO - Estado de Querétaro
-- ============================================

-- Insertar usuarios de ejemplo
INSERT INTO users (username, email, password, role, first_name, last_name, phone, status) VALUES
('admin', 'admin@residencial.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'Carlos', 'Administrador', '+52 442 123 4567', 'active'),
('guardia1', 'guardia@residencial.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guardia', 'José', 'Guardián', '+52 442 234 5678', 'active'),
('residente1', 'juan.perez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'residente', 'Juan', 'Pérez García', '+52 442 345 6789', 'active'),
('residente2', 'maria.lopez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'residente', 'María', 'López Sánchez', '+52 442 456 7890', 'active'),
('residente3', 'pedro.martinez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'residente', 'Pedro', 'Martínez Rodríguez', '+52 442 567 8901', 'active');

-- Insertar propiedades en Querétaro (ejemplos)
INSERT INTO properties (property_number, street, section, tower, property_type, bedrooms, bathrooms, area_m2, status) VALUES
('A-101', 'Av. de las Flores', 'Sección A', 'Torre 1', 'departamento', 3, 2, 120.50, 'ocupada'),
('A-102', 'Av. de las Flores', 'Sección A', 'Torre 1', 'departamento', 2, 1, 85.00, 'ocupada'),
('B-201', 'Calle Querétaro', 'Sección B', 'Torre 2', 'departamento', 3, 2, 110.00, 'ocupada'),
('C-001', 'Calle del Parque', 'Sección C', NULL, 'casa', 4, 3, 180.00, 'ocupada'),
('C-002', 'Calle del Parque', 'Sección C', NULL, 'casa', 3, 2, 150.00, 'desocupada');

-- Vincular residentes con propiedades
INSERT INTO residents (user_id, property_id, relationship, is_primary, status) VALUES
(3, 1, 'propietario', TRUE, 'active'),
(4, 2, 'propietario', TRUE, 'active'),
(5, 3, 'propietario', TRUE, 'active');

-- Insertar vehículos
INSERT INTO vehicles (resident_id, plate, brand, model, color, year, vehicle_type) VALUES
(1, 'QRO-123-A', 'Toyota', 'Camry', 'Blanco', 2022, 'auto'),
(2, 'QRO-456-B', 'Honda', 'CR-V', 'Negro', 2021, 'camioneta'),
(3, 'QRO-789-C', 'Nissan', 'Sentra', 'Gris', 2020, 'auto');

-- Insertar amenidades
INSERT INTO amenities (name, description, capacity, amenity_type, hourly_rate, hours_open, hours_close, requires_payment, status) VALUES
('Salón de Eventos Principal', 'Salón de usos múltiples con capacidad para 100 personas', 100, 'salon', 500.00, '08:00:00', '22:00:00', TRUE, 'active'),
('Alberca Principal', 'Alberca climatizada para uso familiar', 50, 'alberca', 0.00, '07:00:00', '21:00:00', FALSE, 'active'),
('Área de Asadores', 'Zona de asadores con 5 parrillas disponibles', 30, 'asadores', 100.00, '10:00:00', '20:00:00', TRUE, 'active'),
('Cancha de Tenis', 'Cancha de tenis con iluminación nocturna', 4, 'cancha', 50.00, '06:00:00', '22:00:00', TRUE, 'active'),
('Gimnasio', 'Gimnasio equipado con máquinas de ejercicio', 20, 'gimnasio', 0.00, '06:00:00', '22:00:00', FALSE, 'active');

-- Insertar cuotas de mantenimiento
INSERT INTO maintenance_fees (property_id, period, amount, due_date, status) VALUES
(1, '2024-11', 1500.00, '2024-11-10', 'paid'),
(1, '2024-12', 1500.00, '2024-12-10', 'pending'),
(2, '2024-11', 1200.00, '2024-11-10', 'paid'),
(2, '2024-12', 1200.00, '2024-12-10', 'pending'),
(3, '2024-11', 1500.00, '2024-11-10', 'overdue'),
(3, '2024-12', 1500.00, '2024-12-10', 'pending');

-- Insertar configuraciones del sistema
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Residencial Querétaro', 'text', 'Nombre del sitio'),
('site_logo', '', 'file', 'Logo del sitio'),
('site_email', 'contacto@residencialqro.com', 'text', 'Email principal del sistema'),
('site_phone', '+52 442 123 4567', 'text', 'Teléfono de contacto'),
('maintenance_fee_default', '1500', 'number', 'Cuota de mantenimiento por defecto'),
('qr_enabled', '1', 'boolean', 'Habilitar generación de códigos QR'),
('theme_color', 'blue', 'text', 'Color principal del tema'),
('paypal_enabled', '0', 'boolean', 'Habilitar pagos con PayPal'),
('whatsapp_enabled', '0', 'boolean', 'Habilitar notificaciones por WhatsApp');

-- ============================================
-- Nota: La contraseña por defecto para todos los usuarios de ejemplo es: "password"
-- Usuario admin: admin / password
-- Usuario guardia: guardia1 / password
-- Usuarios residentes: residente1, residente2, residente3 / password
-- ============================================
