-- ============================================
-- Migración 020: Claves de configuración CFDI
-- Agrega las claves de configuración CFDI en system_settings
-- si aún no existen, para que puedan ser importadas.
-- ============================================

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('cfdi_rfc',            '',  'text', 'RFC del emisor de CFDI'),
('cfdi_razon_social',   '',  'text', 'Razón social o nombre del emisor'),
('cfdi_regimen_fiscal', '',  'text', 'Clave de régimen fiscal SAT'),
('cfdi_cp',             '',  'text', 'Código postal del domicilio fiscal'),
('cfdi_uso_cfdi',       'G03','text', 'Clave de uso del CFDI (SAT)'),
('cfdi_metodo_pago',    'PUE','text', 'Método de pago SAT (PUE/PPD)'),
('cfdi_forma_pago',     '01','text', 'Clave de forma de pago SAT'),
('cfdi_serie',          'A', 'text', 'Serie de los comprobantes'),
('cfdi_folio_inicio',   '1', 'number','Folio inicial de los comprobantes');
