-- ============================================
-- Migración 013: Índices para búsqueda de residentes
-- Fecha: 2026-03-07
-- MySQL 5.7.x: NO soporta "ADD INDEX IF NOT EXISTS"
-- Se implementa con INFORMATION_SCHEMA + SQL dinámico
-- ============================================

-- users.idx_name_search (first_name, last_name)
SET @sql := (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.statistics
      WHERE table_schema = DATABASE()
        AND table_name = 'users'
        AND index_name = 'idx_name_search'
    ),
    'SELECT ''idx_name_search already exists'';',
    'ALTER TABLE `users` ADD INDEX `idx_name_search` (`first_name`, `last_name`);'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- users.idx_vigilance_committee (is_vigilance_committee)
SET @sql := (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.statistics
      WHERE table_schema = DATABASE()
        AND table_name = 'users'
        AND index_name = 'idx_vigilance_committee'
    ),
    'SELECT ''idx_vigilance_committee already exists'';',
    'ALTER TABLE `users` ADD INDEX `idx_vigilance_committee` (`is_vigilance_committee`);'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- users.idx_phone (phone)
SET @sql := (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.statistics
      WHERE table_schema = DATABASE()
        AND table_name = 'users'
        AND index_name = 'idx_phone'
    ),
    'SELECT ''idx_phone already exists'';',
    'ALTER TABLE `users` ADD INDEX `idx_phone` (`phone`);'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- properties.idx_section_status (section, status)
SET @sql := (
  SELECT IF(
    EXISTS (
      SELECT 1
      FROM information_schema.statistics
      WHERE table_schema = DATABASE()
        AND table_name = 'properties'
        AND index_name = 'idx_section_status'
    ),
    'SELECT ''idx_section_status already exists'';',
    'ALTER TABLE `properties` ADD INDEX `idx_section_status` (`section`, `status`);'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
