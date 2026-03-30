-- Migration 024: Catálogo de Incidencias Fijas (Recurring Maintenance Catalog)
-- Adds a catalog table for scheduled/recurring maintenance events that auto-generate reports.

CREATE TABLE IF NOT EXISTS `maintenance_catalog` (
    `id`             INT          NOT NULL AUTO_INCREMENT,
    `title`          VARCHAR(200) NOT NULL,
    `description`    TEXT,
    `category`       ENUM('alumbrado','jardineria','plomeria','seguridad','limpieza','electricidad','pintura','otro') NOT NULL,
    `location`       VARCHAR(200)          DEFAULT NULL,
    `priority`       ENUM('baja','media','alta','urgente')                 NOT NULL DEFAULT 'media',
    `interval_value` INT          NOT NULL COMMENT 'Number of interval units (e.g. 6)',
    `interval_unit`  ENUM('dias','meses','anios')                          NOT NULL DEFAULT 'meses' COMMENT 'Unit for interval_value',
    `last_generated` DATE                  DEFAULT NULL COMMENT 'Date the last automatic report was generated',
    `next_due`       DATE                  DEFAULT NULL COMMENT 'Date the next report should be generated',
    `active`         TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_next_due` (`next_due`),
    KEY `idx_active`   (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de incidencias de mantenimiento recurrentes';
