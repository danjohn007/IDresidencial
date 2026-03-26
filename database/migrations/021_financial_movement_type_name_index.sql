-- Migration 021: Índice en financial_movement_types.name para búsquedas por nombre
-- Permite buscar tipos de movimiento financiero por nombre (sin distinción de mayúsculas)
-- Compatible con MySQL 5.7
-- La columna ya usa utf8mb4_unicode_ci (case-insensitive), el índice mejora el rendimiento.

ALTER TABLE `financial_movement_types`
    ADD INDEX `idx_fmt_name` (`name`);
