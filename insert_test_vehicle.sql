-- ============================================
-- Script para insertar vehículo de prueba
-- ============================================

-- PASO 1: Ver usuarios disponibles con sus residentes
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    u.email,
    r.id as resident_id,
    p.property_number
FROM users u
INNER JOIN residents r ON u.id = r.user_id
INNER JOIN properties p ON r.property_id = p.id
WHERE u.status = 'active' AND r.status = 'active'
ORDER BY u.id
LIMIT 10;

-- ============================================
-- PASO 2: Después de ver el resultado anterior,
-- usa el resident_id que quieras y ejecuta:
-- ============================================

-- Opción A: Si ya tienes un residente (cambia el número 1 por el resident_id real)
INSERT INTO vehicles (resident_id, plate, brand, model, color, year, vehicle_type, status)
VALUES (1, 'ABC123X', 'Toyota', 'Corolla', 'Blanco', 2020, 'auto', 'active');

-- ============================================
-- ALTERNATIVA: Crear todo desde cero
-- ============================================

-- Si NO tienes usuarios/residentes, ejecuta esto:

-- 1. Crear propiedad
INSERT INTO properties (property_number, street, section, property_type, status)
VALUES ('101', 'Calle Principal', 'Sección A', 'casa', 'ocupada');

-- 2. Crear usuario
INSERT INTO users (username, email, password, role, first_name, last_name, phone, status)
VALUES (
    'juan.perez',
    'juan.perez@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'residente',
    'Juan',
    'Pérez',
    '4421234567',
    'active'
);

-- 3. Vincular residente con propiedad (usa el property_id y user_id generados arriba)
INSERT INTO residents (user_id, property_id, relationship, is_primary, status)
VALUES (
    LAST_INSERT_ID(), -- ID del usuario recién creado
    (SELECT id FROM properties WHERE property_number = '101'), -- ID de la propiedad
    'propietario',
    TRUE,
    'active'
);

-- 4. Insertar vehículo (usa el resident_id recién creado)
INSERT INTO vehicles (resident_id, plate, brand, model, color, year, vehicle_type, status)
VALUES (
    LAST_INSERT_ID(), -- ID del residente recién creado
    'ABC123X',
    'Toyota',
    'Corolla',
    'Blanco',
    2020,
    'auto',
    'active'
);

-- ============================================
-- VERIFICACIÓN: Ver el vehículo insertado
-- ============================================
SELECT 
    v.id,
    v.plate,
    v.brand,
    v.model,
    v.color,
    v.year,
    u.first_name,
    u.last_name,
    p.property_number,
    v.status
FROM vehicles v
INNER JOIN residents r ON v.resident_id = r.id
INNER JOIN users u ON r.user_id = u.id
INNER JOIN properties p ON r.property_id = p.id
WHERE v.plate = 'ABC123X';

-- ============================================
-- RESULTADO ESPERADO:
-- ============================================
-- Después de ejecutar, tendrás:
-- - Usuario: Juan Pérez
-- - Propiedad: 101
-- - Vehículo: ABC123X (Toyota Corolla Blanco 2020)
--
-- Entonces puedes subir una imagen con nombre:
-- ABC123X_20251124_120000.jpg
-- 
-- Y el sistema la reconocerá como AUTORIZADA ✓
-- ============================================
