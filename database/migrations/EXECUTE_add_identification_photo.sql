-- ============================================
-- SCRIPT: Agregar columna identification_photo a tabla visits
-- Fecha: 25 de Noviembre 2024
-- Propósito: Permitir captura y almacenamiento de foto de identificación del visitante
-- ============================================

USE janetzy_residencial;

-- Agregar columna para almacenar la ruta de la foto de identificación
ALTER TABLE `visits` 
ADD COLUMN `identification_photo` VARCHAR(255) NULL 
COMMENT 'Ruta de la foto de identificación del visitante' 
AFTER `vehicle_plate`;

-- Crear índice para mejorar consultas de fotos
CREATE INDEX `idx_identification_photo` ON `visits` (`identification_photo`);

-- Verificar que la columna se agregó correctamente
DESCRIBE visits;

-- ============================================
-- INSTRUCCIONES DE USO:
-- 1. Ejecutar este script en phpMyAdmin o MySQL CLI
-- 2. Crear carpeta en servidor: /public_html/uploads/id_photos/
-- 3. Dar permisos 0777 a la carpeta: chmod -R 0777 /home2/janetzy/public_html/uploads/id_photos/
-- 4. Probar la funcionalidad desde /access/validate
-- ============================================
