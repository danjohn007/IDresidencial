-- ============================================================
-- Migración: Módulo de Mensajería (Recepción de Paquetes)
-- Versión MySQL: 5.7
-- ============================================================

CREATE TABLE IF NOT EXISTS `packages` (
    `id`               INT(11)       NOT NULL AUTO_INCREMENT,
    `property_id`      INT(11)       NOT NULL,
    `tracking_number`  VARCHAR(100)  DEFAULT NULL COMMENT 'Número de rastreo del paquete',
    `sender`           VARCHAR(200)  DEFAULT NULL COMMENT 'Remitente (persona o empresa)',
    `description`      VARCHAR(500)  DEFAULT NULL COMMENT 'Descripción breve del paquete',
    `package_type`     ENUM('paquete','sobre','documento','otro') NOT NULL DEFAULT 'paquete',
    `notes`            TEXT          DEFAULT NULL COMMENT 'Observaciones adicionales',
    `status`           ENUM('pendiente','entregado') NOT NULL DEFAULT 'pendiente',
    `received_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora de recepción',
    `received_by`      INT(11)       DEFAULT NULL COMMENT 'Usuario que registró la recepción',
    `delivered_at`     DATETIME      DEFAULT NULL COMMENT 'Fecha/hora de entrega al residente',
    `delivered_by`     INT(11)       DEFAULT NULL COMMENT 'Usuario que registró la entrega',
    `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_packages_property_id` (`property_id`),
    KEY `idx_packages_status`      (`status`),
    KEY `idx_packages_received_at` (`received_at`),
    CONSTRAINT `fk_packages_property`  FOREIGN KEY (`property_id`)  REFERENCES `properties` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_packages_receiver`  FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_packages_deliverer` FOREIGN KEY (`delivered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Módulo de mensajería: recepción de paquetes';
