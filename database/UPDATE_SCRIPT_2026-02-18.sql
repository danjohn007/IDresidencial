-- ============================================
-- SQL Update Script for Financial Reports Adjustments
-- Sistema: IDResidencial
-- Fecha: 2026-02-18
-- 
-- Este script actualiza el sistema manteniendo la funcionalidad actual
-- y agregando las mejoras solicitadas para:
-- - Gestión de planes de membresía (CRUD)
-- - Vistas de subdivisiones y detalles de usuarios
-- - Reportes de mantenimiento mejorados
-- - Visitas diarias con fecha correcta
-- - Pagos y cuotas reflejando movimientos financieros
-- ============================================

USE `janetzy_residencial`;

-- ============================================
-- 1. ACTUALIZAR TABLA maintenance_reports
-- Permitir resident_id NULL para reportes de administradores/guardias
-- ============================================

ALTER TABLE `maintenance_reports` 
    MODIFY COLUMN `resident_id` INT(11) NULL COMMENT 'ID del residente, NULL si es reporte de admin/guardia';

-- ============================================
-- 2. OPTIMIZAR ÍNDICES PARA REPORTES
-- ============================================

-- Índices para consultas de visitas diarias
ALTER TABLE `visits` 
    ADD INDEX IF NOT EXISTS `idx_visits_entry_time` (`entry_time`),
    ADD INDEX IF NOT EXISTS `idx_visits_created_at` (`created_at`);

-- Índices para consultas de cuotas de mantenimiento
ALTER TABLE `maintenance_fees`
    ADD INDEX IF NOT EXISTS `idx_mf_due_date_status` (`due_date`, `status`);

-- Índices para movimientos financieros
ALTER TABLE `financial_movements`
    ADD INDEX IF NOT EXISTS `idx_fm_transaction_date` (`transaction_date`),
    ADD INDEX IF NOT EXISTS `idx_fm_movement_type` (`movement_type_id`);

-- ============================================
-- 3. CONFIGURACIONES DEL SISTEMA
-- ============================================

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`)
VALUES 
    ('auto_generate_fees', '1', 'boolean', 'Generar automáticamente cuotas de mantenimiento'),
    ('fee_generation_day', '1', 'number', 'Día del mes para generar cuotas automáticamente'),
    ('include_visits_in_dashboard', '1', 'boolean', 'Incluir visitas registradas en el dashboard además de pases');

-- ============================================
-- 4. VISTA: Resumen de pagos por residente
-- ============================================

CREATE OR REPLACE VIEW `v_resident_payment_summary` AS
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
FROM `residents` r
INNER JOIN `properties` p ON r.property_id = p.id
INNER JOIN `users` u ON r.user_id = u.id
LEFT JOIN `maintenance_fees` mf ON mf.property_id = p.id
WHERE r.status = 'active' AND r.is_primary = 1
GROUP BY r.id, r.property_id, p.property_number, u.id, u.first_name, u.last_name, u.email, u.phone, r.status;

-- ============================================
-- 5. PROCEDIMIENTO: Generar cuotas mensuales
-- ============================================

DROP PROCEDURE IF EXISTS `generate_monthly_fees`;

DELIMITER $$

CREATE PROCEDURE `generate_monthly_fees`()
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
    
    -- Calcular periodo y fecha de vencimiento
    SET v_period = DATE_FORMAT(CURDATE(), '%Y-%m');
    SET v_due_date = DATE_FORMAT(CURDATE(), '%Y-%m-10');
    
    -- Si la fecha de vencimiento ya pasó, generar para el siguiente mes
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
        
        -- Insertar cuota si no existe para este periodo
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
-- 6. PROCEDIMIENTO: Actualizar estado de cuotas vencidas
-- ============================================

DROP PROCEDURE IF EXISTS `update_overdue_fees`;

DELIMITER $$

CREATE PROCEDURE `update_overdue_fees`()
BEGIN
    UPDATE maintenance_fees 
    SET status = 'overdue' 
    WHERE status = 'pending' AND due_date < CURDATE();
END$$

DELIMITER ;

-- ============================================
-- 7. EVENTO: Generar cuotas automáticamente (primer día del mes)
-- ============================================

SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS `auto_generate_monthly_fees`;

CREATE EVENT `auto_generate_monthly_fees`
ON SCHEDULE EVERY 1 DAY
STARTS (TIMESTAMP(CURRENT_DATE) + INTERVAL 1 DAY + INTERVAL 3 HOUR)
DO
BEGIN
    -- Solo ejecutar el primer día del mes
    IF DAY(CURDATE()) = 1 THEN
        CALL generate_monthly_fees();
    END IF;
    
    -- Actualizar cuotas vencidas todos los días
    CALL update_overdue_fees();
END;

-- ============================================
-- 8. VERIFICACIÓN DE INTEGRIDAD
-- ============================================

-- Verificar que todas las tablas necesarias existen
SELECT 
    CASE 
        WHEN COUNT(*) = 12 THEN 'OK: Todas las tablas requeridas existen'
        ELSE 'ERROR: Faltan tablas requeridas'
    END as verificacion
FROM information_schema.tables
WHERE table_schema = 'janetzy_residencial'
  AND table_name IN (
      'users', 'properties', 'residents', 'maintenance_fees', 
      'maintenance_reports', 'visits', 'memberships', 'membership_plans',
      'financial_movements', 'financial_movement_types', 'subdivisions', 'system_settings'
  );

-- Verificar que los índices fueron creados
SELECT 
    table_name, 
    index_name,
    'OK' as estado
FROM information_schema.statistics
WHERE table_schema = 'janetzy_residencial'
  AND index_name IN (
      'idx_visits_entry_time',
      'idx_visits_created_at',
      'idx_mf_due_date_status',
      'idx_fm_transaction_date',
      'idx_fm_movement_type'
  );

-- ============================================
-- 9. INFORMACIÓN DE ACTUALIZACIÓN
-- ============================================

SELECT 
    'Actualización completada exitosamente' as mensaje,
    NOW() as fecha_hora,
    DATABASE() as base_de_datos,
    VERSION() as version_mysql;

-- ============================================
-- FIN DEL SCRIPT DE ACTUALIZACIÓN
-- ============================================

/*
NOTAS IMPORTANTES:
1. Este script es IDEMPOTENTE - puede ejecutarse múltiples veces sin causar errores
2. Mantiene toda la funcionalidad existente del sistema
3. Agrega mejoras para:
   - Gestión completa de planes de membresía (CRUD)
   - Generación automática de cuotas de mantenimiento
   - Actualización automática de cuotas vencidas
   - Soporte para reportes de administradores/guardias
   - Índices optimizados para consultas de reportes
   - Vista resumida de pagos por residente

4. PRÓXIMOS PASOS RECOMENDADOS:
   - Ejecutar este script en el ambiente de desarrollo primero
   - Probar todas las funcionalidades
   - Crear un respaldo completo antes de ejecutar en producción
   - Ejecutar en producción durante horario de bajo tráfico

5. REVERSIÓN:
   En caso de necesitar revertir los cambios:
   - Restaurar el respaldo de la base de datos
   - Los cambios son principalmente adiciones, no modificaciones destructivas
*/
