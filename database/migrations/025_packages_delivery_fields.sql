-- ============================================================
-- Migración: Datos de entrega para módulo de mensajería
-- MySQL 5.7 compatible
-- ============================================================

ALTER TABLE `packages`
    ADD COLUMN `delivery_key` VARCHAR(20) DEFAULT NULL COMMENT 'Clave de entrega para el residente' AFTER `notes`,
    ADD COLUMN `receiver_name` VARCHAR(200) DEFAULT NULL COMMENT 'Nombre de quien recibe el paquete' AFTER `delivered_by`,
    ADD COLUMN `delivery_evidence_path` VARCHAR(255) DEFAULT NULL COMMENT 'Ruta de imagen de evidencia de entrega' AFTER `receiver_name`;

CREATE INDEX `idx_packages_delivery_key` ON `packages` (`delivery_key`);

-- Backfill con subconjunto seguro [A-F2-9], evitando caracteres ambiguos (I, O, 0, 1)
UPDATE `packages`
SET `delivery_key` = UPPER(
    SUBSTRING(
        REPLACE(REPLACE(REPLACE(UUID(), '-', ''), '0', '2'), '1', '3'),
        1,
        8
    )
)
WHERE (`delivery_key` IS NULL OR `delivery_key` = '')
  AND `status` IN ('pendiente', 'entregado_pendiente');
