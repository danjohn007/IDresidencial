-- ============================================
-- Migración 013: Índices para búsqueda de residentes
-- Fecha: 2026-03-07
-- Descripción:
--   Agrega índices para mejorar el rendimiento de las nuevas
--   búsquedas/filtros en el módulo de Residentes (/residents):
--     - Búsqueda por nombre, teléfono y correo en users
--     - Filtro por sección en properties
--     - Filtro por is_vigilance_committee en users
--
--   También incluye notas sobre los cambios de lógica aplicados:
--     1. FinancialController::edit() – cuando se asigna una propiedad
--        a un movimiento importado (sin referencia) de tipo cuota de
--        mantenimiento, se busca y marca como PAGADA la cuota
--        PENDIENTE más antigua de esa propiedad.
--     2. ResidentsController::payments() – la generación automática
--        de cuotas ya no requiere que la propiedad tenga
--        status = 'ocupada'; ahora se genera para cualquier
--        propiedad con residente principal activo.
-- ============================================

-- Índice compuesto para búsquedas por nombre y apellido
ALTER TABLE `users`
  ADD INDEX IF NOT EXISTS `idx_name_search` (`first_name`, `last_name`);

-- Índice para filtro de comité de vigilancia
ALTER TABLE `users`
  ADD INDEX IF NOT EXISTS `idx_vigilance_committee` (`is_vigilance_committee`);

-- Índice para búsqueda por teléfono
ALTER TABLE `users`
  ADD INDEX IF NOT EXISTS `idx_phone` (`phone`);

-- Índice para sección en propiedades (si no existe ya)
ALTER TABLE `properties`
  ADD INDEX IF NOT EXISTS `idx_section_status` (`section`, `status`);
