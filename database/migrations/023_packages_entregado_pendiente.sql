-- ============================================================
-- Migración: Nuevo estado "entregado_pendiente" para paquetes
-- Agrega el estado intermedio para el flujo de confirmación
-- de entrega por parte del residente
-- ============================================================

ALTER TABLE `packages`
    MODIFY COLUMN `status`
        ENUM('pendiente','entregado_pendiente','entregado')
        NOT NULL DEFAULT 'pendiente';
