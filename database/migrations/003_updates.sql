-- ============================================
-- Sistema de Gestión Residencial - Actualización
-- Fecha: Noviembre 2024
-- Descripción: SQL para actualizar funcionalidad existente
-- ============================================

-- Nota: La tabla audit_logs debe crearse usando el archivo 001_add_audit_logs.sql
-- si aún no existe. El archivo está en database/migrations/001_add_audit_logs.sql

-- ============================================
-- Insertar algunos logs de auditoría de ejemplo (opcional)
-- ============================================
-- INSERT INTO audit_logs (user_id, action, description, table_name, record_id, ip_address)
-- VALUES 
-- (1, 'login', 'Usuario inició sesión', 'users', 1, '127.0.0.1'),
-- (1, 'create', 'Residente creado', 'residents', 1, '127.0.0.1'),
-- (1, 'update', 'Configuración actualizada', 'system_settings', 1, '127.0.0.1');

-- ============================================
-- Verificar estructura de tabla system_settings
-- ============================================
-- La tabla system_settings debe tener estos campos mínimos:
-- - setting_key VARCHAR(100) PRIMARY KEY
-- - setting_value TEXT
-- - setting_type VARCHAR(50)
-- - description VARCHAR(255)

-- ============================================
-- Asegurar que existen configuraciones básicas
-- ============================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) 
VALUES 
('site_logo', '', 'file', 'Logo del sitio'),
('site_name', 'Residencial', 'text', 'Nombre del sitio'),
('site_email', 'contacto@residencial.com', 'text', 'Email principal del sistema'),
('site_phone', '+52 442 123 4567', 'text', 'Teléfono de contacto')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- ============================================
-- Notas sobre la actualización
-- ============================================
-- 1. Los usuarios ya no requieren el campo username obligatorio al crear residentes
--    El username se genera automáticamente desde el email
-- 2. El teléfono en la creación de residentes ahora tiene validación de 10 dígitos
-- 3. El módulo de auditoría ahora incluye paginación de 20 registros por página
-- 4. Los superadmins pueden crear, editar y eliminar amenidades
-- 5. Se agregó el módulo de reportes con 5 tipos de reportes diferentes
-- 6. La funcionalidad de "Olvidaste tu contraseña" ahora está activa

-- ============================================
-- Comando para ejecutar este archivo
-- ============================================
-- mysql -u [usuario] -p [nombre_base_datos] < 003_updates.sql
