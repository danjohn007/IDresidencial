-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 27-11-2025 a las 06:30:44
-- Versión del servidor: 5.7.23-23
-- Versión de PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `janetzy_residencial`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE PROCEDURE `generate_payment_reminders` ()   BEGIN
    -- Generar recordatorios para pagos que vencen mañana y no tienen recordatorio
    INSERT INTO payment_reminders (maintenance_fee_id, reminder_date, email_to)
    SELECT 
        mf.id,
        DATE_SUB(mf.due_date, INTERVAL 1 DAY) as reminder_date,
        u.email
    FROM maintenance_fees mf
    JOIN properties p ON mf.property_id = p.id
    JOIN residents r ON r.property_id = p.id AND r.is_primary = 1
    JOIN users u ON r.user_id = u.id
    WHERE mf.status IN ('pending', 'overdue')
        AND mf.due_date > CURDATE()
        AND DATE_SUB(mf.due_date, INTERVAL 1 DAY) = CURDATE()
        AND NOT EXISTS (
            SELECT 1 FROM payment_reminders pr 
            WHERE pr.maintenance_fee_id = mf.id 
            AND pr.reminder_date = DATE_SUB(mf.due_date, INTERVAL 1 DAY)
        );
END$$

CREATE PROCEDURE `SendPaymentReminders` ()   BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_resident_id INT;
    DECLARE v_fee_id INT;
    DECLARE v_due_date DATE;
    DECLARE reminder_days INT;
    DECLARE fee_cursor CURSOR FOR
        SELECT DISTINCT r.id as resident_id, mf.id as fee_id, mf.due_date
        FROM maintenance_fees mf
        INNER JOIN properties p ON mf.property_id = p.id
        INNER JOIN residents r ON r.property_id = p.id AND r.is_primary = 1
        WHERE mf.status IN ('pending', 'overdue')
          AND DATEDIFF(mf.due_date, CURDATE()) = reminder_days
          AND NOT EXISTS (
              SELECT 1 FROM payment_reminders pr 
              WHERE pr.fee_id = mf.id AND pr.status = 'sent'
          );
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    SELECT CAST(setting_value AS UNSIGNED) INTO reminder_days 
    FROM system_settings 
    WHERE setting_key = 'payment_reminder_days' 
    LIMIT 1;
    
    OPEN fee_cursor;
    read_loop: LOOP
        FETCH fee_cursor INTO v_resident_id, v_fee_id, v_due_date;
        IF done THEN
            LEAVE read_loop;
        END IF;
        INSERT INTO payment_reminders (resident_id, fee_id, reminder_type, status)
        VALUES (v_resident_id, v_fee_id, 'email', 'pending');
    END LOOP;
    CLOSE fee_cursor;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `access_devices`
--

CREATE TABLE `access_devices` (
  `id` int(11) NOT NULL,
  `device_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_type` enum('hikvision','shelly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Device ID único (MAC, Serial, etc)',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `port` int(11) DEFAULT '80',
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token de autenticación para Shelly Cloud',
  `cloud_server` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Servidor de Shelly Cloud',
  `input_channel` int(11) DEFAULT '1' COMMENT 'Canal de entrada (para apertura)',
  `output_channel` int(11) DEFAULT '0' COMMENT 'Canal de salida (para cierre)',
  `pulse_duration` int(11) DEFAULT '4000' COMMENT 'Duración del pulso en ms (4000ms = 4s)',
  `open_time` int(11) DEFAULT '5' COMMENT 'Tiempo que la puerta permanece abierta en segundos',
  `inverted` tinyint(1) DEFAULT '0' COMMENT 'Invertido (off → on)',
  `simultaneous` tinyint(1) DEFAULT '0' COMMENT 'Dispositivo simultáneo',
  `door_number` int(11) DEFAULT NULL COMMENT 'Número de puerta del dispositivo HikVision (1-8)',
  `branch_id` int(11) DEFAULT NULL COMMENT 'Sucursal asociada',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ubicación física del dispositivo',
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Área específica',
  `status` enum('online','offline','error','disabled') COLLATE utf8mb4_unicode_ci DEFAULT 'offline',
  `enabled` tinyint(1) DEFAULT '1',
  `last_online` timestamp NULL DEFAULT NULL,
  `last_test` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `access_devices`
--

INSERT INTO `access_devices` (`id`, `device_name`, `device_type`, `device_id`, `ip_address`, `port`, `username`, `password`, `auth_token`, `cloud_server`, `input_channel`, `output_channel`, `pulse_duration`, `open_time`, `inverted`, `simultaneous`, `door_number`, `branch_id`, `location`, `area`, `status`, `enabled`, `last_online`, `last_test`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 'Portón Principal', 'hikvision', 'HIK-ABC123', '192.168.1.10', 80, 'admin', '1234', NULL, NULL, 1, 0, 4000, 5, 0, 0, NULL, NULL, NULL, NULL, 'online', 1, NULL, NULL, '2025-11-23 18:18:07', '2025-11-23 18:18:07', NULL),
(2, 'Portón Secundario', 'shelly', '34987A67DA6C', '192.168.1.21', 80, 'user', '5678', 'MzgwNjRhdWlk0574CFA7E6D9F34D8F306EB51648C8DA5D79A03333414C2FBF51CFA88A780F9867246CE317003A74', 'shelly-208-eu.shelly.cloud ', 1, 0, 4000, 5, 1, 0, NULL, NULL, '', '', 'online', 1, '2025-11-27 09:37:34', NULL, '2025-11-23 18:18:07', '2025-11-27 09:37:34', NULL),
(3, 'Puerta Peatonal', 'hikvision', 'HIK-GHI789', '192.168.1.33', 80, 'admin', 'abcd', NULL, NULL, 1, 0, 4000, 5, 0, 0, NULL, NULL, NULL, NULL, 'error', 1, NULL, NULL, '2025-11-23 18:18:07', '2025-11-23 18:18:07', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `log_type` enum('resident','visit','vehicle','provider') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `access_type` enum('entry','exit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_method` enum('qr','rfid','manual','plate_recognition') COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_plate` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_id` int(11) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `access_logs`
--

INSERT INTO `access_logs` (`id`, `log_type`, `reference_id`, `access_type`, `access_method`, `property_id`, `name`, `vehicle_plate`, `guard_id`, `notes`, `timestamp`) VALUES
(1, 'resident', 6, 'entry', 'qr', 7, 'Carla Mendez Torres', 'QRO-124-D', 2, 'Ingreso con QR', '2025-11-23 18:18:07'),
(2, 'resident', 7, 'exit', 'manual', 8, 'Arturo Vera Luna', 'QRO-125-E', 2, 'Salida manual', '2025-11-23 18:18:07'),
(3, 'visit', 8, 'entry', 'rfid', 9, 'Juan Torres', 'QRO-126-F', 2, 'Ingreso visitante', '2025-11-23 18:18:07'),
(4, 'vehicle', 10, 'entry', 'plate_recognition', 1, 'Juan Pérez García', 'ABC123X', NULL, 'Acceso automático por reconocimiento de placa (ID: 7)', '2025-11-24 18:33:02'),
(5, 'visit', 3, 'entry', 'qr', NULL, 'Rodrigo Sanchez', 'ABC123', 1, NULL, '2025-11-24 19:34:29'),
(6, 'visit', 4, 'entry', 'qr', NULL, 'Danonino', 'ABC123X', 1, NULL, '2025-11-25 17:13:01'),
(7, 'visit', 5, 'entry', 'qr', NULL, 'dszzxfgcvhjbkn', 'QRO-456-B', 1, NULL, '2025-11-25 17:35:34'),
(8, 'visit', 6, 'entry', 'qr', NULL, 'Danonino', 'QRO-789-C', 1, NULL, '2025-11-26 17:09:19'),
(9, 'visit', 7, 'entry', 'qr', NULL, 'Dan Raso', 'RSV-205-BV', 1, NULL, '2025-11-26 17:42:15'),
(10, 'visit', 7, 'exit', 'qr', NULL, 'Dan Raso', 'RSV-205-BV', 1, NULL, '2025-11-26 17:44:48'),
(16, 'resident', 1, 'entry', 'manual', 1, NULL, NULL, 15, '', '2025-11-26 20:29:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `capacity` int(11) DEFAULT '0',
  `amenity_type` enum('salon','alberca','asadores','cancha','gimnasio','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `hourly_rate` decimal(10,2) DEFAULT '0.00',
  `hours_open` time DEFAULT NULL,
  `hours_close` time DEFAULT NULL,
  `days_available` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requires_payment` tinyint(1) DEFAULT '0',
  `status` enum('active','maintenance','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `amenities`
--

INSERT INTO `amenities` (`id`, `name`, `description`, `capacity`, `amenity_type`, `hourly_rate`, `hours_open`, `hours_close`, `days_available`, `requires_payment`, `status`, `photo`, `created_at`, `updated_at`) VALUES
(1, 'Salón de Eventos Principal', 'Salón de usos múltiples con capacidad para 100 personas', 100, 'salon', 500.00, '08:00:00', '22:00:00', NULL, 1, 'active', NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(2, 'Alberca Principal', 'Alberca climatizada para uso familiar', 50, 'alberca', 0.00, '07:00:00', '21:00:00', NULL, 0, 'active', NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(3, 'Área de Asadores', 'Zona de asadores con 5 parrillas disponibles', 30, 'asadores', 100.00, '10:00:00', '20:00:00', NULL, 1, 'active', NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(4, 'Cancha de Tenis', 'Cancha de tenis con iluminación nocturna', 4, 'cancha', 50.00, '06:00:00', '22:00:00', NULL, 1, 'active', NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(5, 'Gimnasio', 'Gimnasio equipado con máquinas de ejercicio', 20, 'gimnasio', 0.00, '06:00:00', '22:00:00', NULL, 0, 'active', NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `target_audience` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'all',
  `target_filter` text COLLATE utf8mb4_unicode_ci,
  `sent_via` text COLLATE utf8mb4_unicode_ci,
  `scheduled_for` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `status` enum('draft','scheduled','sent') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `description`, `table_name`, `record_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'system', 'Sistema inicializado y migraciones aplicadas', NULL, NULL, '127.0.0.1', NULL, '2025-11-23 11:49:23'),
(2, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:39:32'),
(3, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:39:35'),
(4, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-23 17:50:40'),
(5, 1, 'create', 'Residente creado: Valeria Rodriguez Sanchez', 'residents', NULL, '187.145.46.170', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-23 17:53:32'),
(6, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:57:09'),
(7, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:57:14'),
(8, 1, 'update', 'Usuario actualizó su información de contacto', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:57:36'),
(9, 1, 'update', 'Usuario actualizó su información de contacto', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:58:01'),
(10, 1, 'update', 'Usuario actualizó su foto de perfil', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:58:18'),
(11, 1, 'update', 'Usuario actualizó su foto de perfil', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:58:37'),
(12, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:59:09'),
(13, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:59:13'),
(14, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 17:59:56'),
(15, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-23 18:08:19'),
(16, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-23 18:10:10'),
(17, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:12:04'),
(18, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:23:59'),
(19, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:28:09'),
(20, 1, 'create', 'Movimiento financiero creado', 'financial_movements', 4, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:30:38'),
(21, 1, 'create', 'Movimiento financiero creado: HOla', 'financial_movements', NULL, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:30:38'),
(22, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:38:02'),
(23, 7, 'login', 'Usuario inició sesión: janerosas', 'users', 7, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:38:06'),
(24, 7, 'create', 'Reservación de amenidad creada: Alberca Principal', 'reservations', NULL, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:38:39'),
(25, 7, 'logout', 'Usuario cerró sesión: janerosas', 'users', 7, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:41:29'),
(26, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 18:41:32'),
(27, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.9.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:09:05'),
(28, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.128.9.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 21:09:18'),
(29, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 22:09:25'),
(30, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 22:11:00'),
(31, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 22:11:05'),
(32, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 22:11:52'),
(33, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 22:11:56'),
(34, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 22:16:01'),
(35, NULL, 'register', 'Nuevo registro pendiente de aprobación: administracion@impactosdigitales.com', 'users', 14, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 22:17:11'),
(36, NULL, 'login_failed', 'Intento de login con cuenta pendiente: administracion@impactosdigitales.com', 'users', 14, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:39:32'),
(37, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:39:47'),
(38, 1, 'approve', 'Registro de residente aprobado', 'users', 14, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:40:08'),
(39, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:40:18'),
(40, 14, 'login', 'Usuario inició sesión: administracion_20c07f04', 'users', 14, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:40:26'),
(41, 14, 'update', 'Usuario actualizó su foto de perfil', 'users', 14, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:44:58'),
(42, 14, 'logout', 'Usuario cerró sesión: administracion_20c07f04', 'users', 14, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:47:44'),
(43, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:47:48'),
(44, 1, 'create', 'Movimiento financiero creado', 'financial_movements', 32, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:48:50'),
(45, 1, 'create', 'Movimiento financiero creado: probando', 'financial_movements', NULL, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:48:50'),
(46, 1, 'create', 'Movimiento financiero creado', 'financial_movements', 33, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:54:45'),
(47, 1, 'create', 'Movimiento financiero creado: a ver\r\n', 'financial_movements', NULL, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 23:54:45'),
(48, 1, 'create', 'Tipo de movimiento creado: Nómina', 'financial_movement_types', 13, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 00:02:05'),
(49, 1, 'delete', 'Movimiento financiero eliminado', 'financial_movements', 33, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 00:07:13'),
(50, 1, 'delete', 'Movimiento financiero eliminado ID: 33', 'financial_movements', 33, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 00:07:13'),
(51, 1, 'delete', 'Movimiento financiero eliminado', 'financial_movements', 4, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 00:07:29'),
(52, 1, 'delete', 'Movimiento financiero eliminado ID: 4', 'financial_movements', 4, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 00:07:29'),
(53, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 00:08:04'),
(54, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 00:10:16'),
(55, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:16:29'),
(56, 1, 'create', 'Movimiento financiero creado', 'financial_movements', 34, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:18:04'),
(57, 1, 'create', 'Movimiento financiero creado: hj', 'financial_movements', NULL, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:18:04'),
(58, 1, 'update', 'Movimiento financiero actualizado', 'financial_movements', 34, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:19:55'),
(59, 1, 'update', 'Movimiento financiero actualizado: hj', 'financial_movements', 34, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:19:55'),
(60, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:22:24'),
(61, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:27:56'),
(62, 1, 'create', 'Movimiento financiero creado', 'financial_movements', 35, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:31:49'),
(63, 1, 'create', 'Movimiento financiero creado: marketing', 'financial_movements', NULL, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:31:49'),
(64, 1, 'update', 'Tipo de movimiento actualizado: Cuota de Mantenimiento', 'financial_movement_types', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:35:15'),
(65, 1, 'update', 'Estado de tipo de movimiento actualizado', 'financial_movement_types', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:35:21'),
(66, 1, 'update', 'Estado de tipo de movimiento actualizado', 'financial_movement_types', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 05:35:27'),
(67, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 06:58:42'),
(68, 1, 'delete', 'Movimiento financiero eliminado', 'financial_movements', 35, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 06:59:18'),
(69, 1, 'delete', 'Movimiento financiero eliminado ID: 35', 'financial_movements', 35, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 06:59:18'),
(70, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 07:05:55'),
(71, 6, 'login', 'Usuario inició sesión: danjohn007', 'users', 6, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 07:06:26'),
(72, 6, 'logout', 'Usuario cerró sesión: danjohn007', 'users', 6, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 07:07:05'),
(73, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 07:07:10'),
(74, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 07:08:45'),
(75, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.128.227.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 07:12:01'),
(76, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 07:40:53'),
(77, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 08:06:10'),
(78, 6, 'login', 'Usuario inició sesión: danjohn007', 'users', 6, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 08:06:18'),
(79, 6, 'logout', 'Usuario cerró sesión: danjohn007', 'users', 6, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 08:06:24'),
(80, 7, 'login', 'Usuario inició sesión: janerosas', 'users', 7, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 08:06:29'),
(81, 7, 'update', 'Reservación cancelada ID: 4', 'reservations', 4, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 08:07:53'),
(82, 7, 'logout', 'Usuario cerró sesión: janerosas', 'users', 7, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 08:08:02'),
(83, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 08:08:06'),
(84, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 08:08:49'),
(85, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 08:12:18'),
(86, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 12:58:16'),
(87, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 12:59:00'),
(88, NULL, 'password_reset_failed', 'Error al enviar correo de recuperación: jane@impactosdigitales.com', 'users', 7, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:00:11'),
(89, NULL, 'password_reset_failed', 'Error al enviar correo de recuperación: jane@impactosdigitales.com', 'users', 7, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:01:11'),
(90, 1, 'logout', 'Usuario cerró sesión: hola@janetzy.shop', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:05:11'),
(91, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:05:18'),
(92, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:05:32'),
(93, NULL, 'password_reset_failed', 'Error al enviar correo de recuperación: jane@impactosdigitales.com', 'users', 7, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:06:43'),
(94, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:07:05'),
(95, 1, 'suspend', 'Residente suspendido', 'residents', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:09:16'),
(96, 1, 'delete', 'Residente marcado como eliminado (soft delete): administracion@impactosdigitales.com', 'residents', 11, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:09:31'),
(97, 1, 'activate', 'Residente activado', 'residents', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:09:40'),
(98, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:15:39'),
(99, NULL, 'password_reset_request', 'Solicitud de recuperación de contraseña enviada por email: jane@impactosdigitales.com', 'users', 7, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:15:48'),
(100, NULL, 'login_failed', 'Intento de login fallido con usuario: jane@impactosdigitales.com', 'users', NULL, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:16:18'),
(101, 7, 'login', 'Usuario inició sesión: janerosas', 'users', 7, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:16:27'),
(102, 7, 'logout', 'Usuario cerró sesión: janerosas', 'users', 7, '200.68.165.246', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:41:30'),
(103, NULL, 'login_failed', 'Intento de login fallido con usuario: admin@residencial.com', 'users', NULL, '200.68.165.246', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:42:07'),
(104, NULL, 'login_failed', 'Intento de login fallido con usuario: admin@residencial.com', 'users', NULL, '200.68.165.246', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:42:18'),
(105, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '200.68.165.246', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:42:32'),
(106, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '200.68.165.246', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:45:39'),
(107, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '200.68.165.246', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:45:46'),
(108, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.141.189.149', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 14:21:10'),
(109, 7, 'login', 'Usuario inició sesión: janerosas', 'users', 7, '189.141.189.149', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 14:21:15'),
(110, 7, 'create', 'Reservación de amenidad creada: Cancha de Tenis', 'reservations', NULL, '189.141.189.149', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 14:22:03'),
(111, 7, 'logout', 'Usuario cerró sesión: janerosas', 'users', 7, '189.141.189.149', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 14:27:19'),
(112, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.141.189.149', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 14:27:23'),
(113, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '189.141.189.149', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 15:08:10'),
(114, NULL, 'login_failed', 'Intento de login fallido con usuario: admin@recidencial.com', 'users', NULL, '187.145.46.170', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 15:11:10'),
(115, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 15:11:45'),
(116, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '189.141.189.149', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 15:42:21'),
(117, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 16:11:29'),
(118, 1, 'update', 'Residente actualizado: Juan Pérez García', 'residents', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 16:12:16'),
(119, 1, 'update', 'Residente actualizado: María López Sánchez', 'residents', 2, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 16:12:28'),
(120, 1, 'suspend', 'Residente suspendido', 'residents', 2, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 16:22:01'),
(121, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 18:33:47'),
(122, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:18:13'),
(123, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:21:28'),
(124, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:22:35'),
(125, NULL, 'login_failed', 'Intento de login fallido con usuario: guardia@residencial.com', 'users', NULL, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:22:41'),
(126, NULL, 'login_failed', 'Intento de login fallido con usuario: guardia@residencial.com', 'users', NULL, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:22:50'),
(127, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:22:53'),
(128, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:23:24'),
(129, NULL, 'login_failed', 'Intento de login fallido con usuario: guardia@residencial.com', 'users', NULL, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:23:29'),
(130, NULL, 'login_failed', 'Intento de login fallido con usuario: guardia@residencial.com', 'users', NULL, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:23:45'),
(131, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:23:49'),
(132, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:24:29'),
(133, NULL, 'login_failed', 'Intento de login fallido con usuario: guardia2@residencial.com', 'users', NULL, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:24:38'),
(134, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:24:48'),
(135, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:25:25'),
(136, 15, 'login', 'Usuario inició sesión: tecnologia', 'users', 15, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 19:25:30'),
(137, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', '2025-11-24 19:29:36'),
(138, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', '2025-11-24 19:33:54'),
(139, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:37:50'),
(140, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-25 17:08:42'),
(141, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-25 17:33:02'),
(142, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:49:46'),
(143, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:15:29'),
(144, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:10:43'),
(145, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:46:34'),
(146, 15, 'login', 'Usuario inició sesión: tecnologia', 'users', 15, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 15:46:40'),
(147, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 16:43:56'),
(148, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 16:49:59'),
(149, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 16:53:40'),
(150, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 16:57:58'),
(151, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 16:59:35'),
(152, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:00:17'),
(153, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:01:13'),
(154, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:05:32'),
(155, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-26 17:08:43'),
(156, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:11:18'),
(157, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:12:50'),
(158, 1, 'test', 'Error al probar dispositivo: Portón Secundario - Error de conexión: Failed to connect to shelly-208-eu.shelly.cloud port 6022 after 1 ms: Could not connect to server', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:12:54'),
(159, 1, 'test', 'Error al probar dispositivo: Portón Secundario - Error de conexión: Failed to connect to shelly-208-eu.shelly.cloud port 6022 after 1 ms: Could not connect to server', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:13:07'),
(160, 1, 'test', 'Error al probar dispositivo: Portón Secundario - HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:14:07'),
(161, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:15:00'),
(162, 1, 'test', 'Error al probar dispositivo: Portón Secundario - HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:15:05'),
(163, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:16:48'),
(164, 1, 'test', 'Error al probar dispositivo: Portón Secundario - HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:16:53'),
(165, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:17:34'),
(166, 1, 'test', 'Error al probar dispositivo: Portón Secundario - HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:17:39'),
(167, 1, 'test', 'Error al probar dispositivo: Portón Secundario - HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:17:49'),
(168, 1, 'test', 'Error al probar dispositivo: Portón Secundario - HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:19:12'),
(169, 1, 'test', 'Error al probar dispositivo: Portón Secundario - HTTP Error 404: {\"isok\":false,\"errors\":{\"404\":\"Requested method was not found\"}}', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:20:44'),
(170, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:21:12'),
(171, 1, 'test', 'Error al probar dispositivo: Portón Secundario - Error de conexión: URL rejected: Malformed input to a URL function', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:21:22'),
(172, 1, 'test', 'Error al probar dispositivo: Portón Secundario - Error de conexión: URL rejected: Malformed input to a URL function', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:29:42'),
(173, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:29:55'),
(174, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:30:02'),
(175, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:30:16'),
(176, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:30:59'),
(177, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:32:06'),
(178, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:32:16'),
(179, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:35:31'),
(180, 1, 'test', 'Error al probar dispositivo: Portón Secundario - Error de conexión: URL rejected: Malformed input to a URL function', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:35:36'),
(181, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:37:32'),
(182, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:37:48'),
(183, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:38:47'),
(184, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:38:57'),
(185, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:39:46'),
(186, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:41:07'),
(187, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:42:16'),
(188, 1, 'update', 'Dispositivo actualizado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:42:33'),
(189, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:42:43'),
(190, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:43:22'),
(191, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 17:44:42'),
(192, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 18:44:02'),
(193, 1, 'logout', 'Usuario cerró sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 18:44:54'),
(194, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 18:45:13'),
(195, 15, 'login', 'Usuario inició sesión: tecnologia', 'users', 15, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 18:49:17'),
(196, 15, 'logout', 'Usuario cerró sesión: tecnologia', 'users', 15, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 18:52:32'),
(197, NULL, 'login_failed', 'Intento de login fallido con usuario: tecnologia@industrial.com.mx', 'users', NULL, '187.243.195.15', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-26 18:52:58'),
(198, 15, 'login', 'Usuario inició sesión: tecnologia', 'users', 15, '187.243.195.15', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-11-26 18:53:18'),
(199, 15, 'login', 'Usuario inició sesión: tecnologia', 'users', 15, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 18:55:01'),
(200, 15, 'logout', 'Usuario cerró sesión: tecnologia', 'users', 15, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:49:12'),
(201, 15, 'login', 'Usuario inició sesión: tecnologia', 'users', 15, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:49:20'),
(202, 15, 'logout', 'Usuario cerró sesión: tecnologia', 'users', 15, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:51:17'),
(203, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.243.195.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:51:28'),
(204, 1, 'login', 'Usuario inició sesión: admin', 'users', 1, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:12:30'),
(205, 1, 'test', 'Dispositivo probado: Portón Secundario', 'access_devices', 2, '187.145.46.170', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 09:37:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detected_plates`
--

CREATE TABLE `detected_plates` (
  `id` int(11) NOT NULL,
  `plate_text` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Texto de la placa detectada por la cámara',
  `captured_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha y hora de captura',
  `unit_id` int(11) DEFAULT NULL COMMENT 'ID de la unidad/cámara específica',
  `is_match` tinyint(1) DEFAULT '0' COMMENT '1 si coincide con placa registrada, 0 si no',
  `matched_vehicle_id` int(11) DEFAULT NULL COMMENT 'ID del vehículo que coincidió (si aplica)',
  `payload_json` json DEFAULT NULL COMMENT 'Datos completos del payload enviado por HikVision',
  `status` enum('new','processed','authorized','rejected','error') COLLATE utf8mb4_unicode_ci DEFAULT 'new' COMMENT 'Estado del procesamiento',
  `processed_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha y hora de procesamiento',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notas adicionales o motivo de rechazo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detected_plates`
--

INSERT INTO `detected_plates` (`id`, `plate_text`, `captured_at`, `unit_id`, `is_match`, `matched_vehicle_id`, `payload_json`, `status`, `processed_at`, `notes`, `created_at`) VALUES
(9, '5555', '2025-11-25 19:55:01', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/5555_20251125135501.jpg\", \"processed_at\": \"2025-11-25 13:55:01\", \"original_filename\": \"5555.jpg\"}', '', '2025-11-25 19:55:01', 'Placa extraída del nombre de archivo: 5555.jpg', '2025-11-25 19:55:01'),
(10, '20251125152025112515', '2025-11-25 21:38:01', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/2025112515_20251125153746_20251125153801.jpg\", \"processed_at\": \"2025-11-25 15:38:01\", \"original_filename\": \"2025112515_20251125153746.jpg\"}', '', '2025-11-25 21:38:01', 'Placa extraída del nombre de archivo: 2025112515_20251125153746.jpg', '2025-11-25 21:38:01'),
(11, 'LPUNKNOWN20251125155', '2025-11-25 21:59:01', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/LP_UNKNOWN_20251125155851595_20251125155901.jpg\", \"processed_at\": \"2025-11-25 15:59:01\", \"original_filename\": \"LP_UNKNOWN_20251125155851595.jpg\"}', '', '2025-11-25 21:59:01', 'Placa extraída del nombre de archivo: LP_UNKNOWN_20251125155851595.jpg', '2025-11-25 21:59:01'),
(12, 'LPVBC1S3X20251125155', '2025-11-25 21:59:01', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/LP_VBC1S3X_20251125155846382_20251125155901.jpg\", \"processed_at\": \"2025-11-25 15:59:01\", \"original_filename\": \"LP_VBC1S3X_20251125155846382.jpg\"}', '', '2025-11-25 21:59:01', 'Placa extraída del nombre de archivo: LP_VBC1S3X_20251125155846382.jpg', '2025-11-25 21:59:01'),
(13, 'VHVBC1S3X20251125155', '2025-11-25 21:59:01', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/VH_VBC1S3X_20251125155846383_20251125155901.jpg\", \"processed_at\": \"2025-11-25 15:59:01\", \"original_filename\": \"VH_VBC1S3X_20251125155846383.jpg\"}', '', '2025-11-25 21:59:01', 'Placa extraída del nombre de archivo: VH_VBC1S3X_20251125155846383.jpg', '2025-11-25 21:59:01'),
(14, 'LPXCST38A20251125155', '2025-11-25 22:00:02', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/LP_XCST38A_20251125155921481_20251125160002.jpg\", \"processed_at\": \"2025-11-25 16:00:02\", \"original_filename\": \"LP_XCST38A_20251125155921481.jpg\"}', '', '2025-11-25 22:00:02', 'Placa extraída del nombre de archivo: LP_XCST38A_20251125155921481.jpg', '2025-11-25 22:00:02'),
(15, 'VHXCST38A20251125155', '2025-11-25 22:00:02', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/VH_XCST38A_20251125155921482_20251125160002.jpg\", \"processed_at\": \"2025-11-25 16:00:02\", \"original_filename\": \"VH_XCST38A_20251125155921482.jpg\"}', '', '2025-11-25 22:00:02', 'Placa extraída del nombre de archivo: VH_XCST38A_20251125155921482.jpg', '2025-11-25 22:00:02'),
(16, 'VBC1S3X', '2025-11-26 15:10:15', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/VBC1S3X_20251126091801.jpg\", \"processed_at\": \"2025-11-26 09:18:02\", \"original_filename\": \"LP_VBC1S3X_20251126091015432.jpg\"}', 'new', '2025-11-26 15:18:02', 'Vehículo no registrado en el sistema', '2025-11-26 15:18:02'),
(17, 'VBC1S3X', '2025-11-26 15:10:15', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/VBC1S3X_20251126091802.jpg\", \"processed_at\": \"2025-11-26 09:18:02\", \"original_filename\": \"VH_VBC1S3X_20251126091015432.jpg\"}', 'new', '2025-11-26 15:18:02', 'Vehículo no registrado en el sistema', '2025-11-26 15:18:02'),
(18, 'DBC1S3X', '2025-11-26 15:31:33', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/DBC1S3X_20251126093201.jpg\", \"processed_at\": \"2025-11-26 09:32:01\", \"original_filename\": \"LP_DBC1S3X_20251126093133393.jpg\"}', 'new', '2025-11-26 15:32:01', 'Vehículo no registrado en el sistema', '2025-11-26 15:32:01'),
(19, 'VBC1S3X', '2025-11-26 15:31:47', 1, 0, NULL, '{\"source\": \"ftp_hikvision\", \"image_path\": \"/placas/VBC1S3X_20251126093201.jpg\", \"processed_at\": \"2025-11-26 09:32:01\", \"original_filename\": \"LP_VBC1S3X_20251126093147345.jpg\"}', 'new', '2025-11-26 15:32:01', 'Vehículo no registrado en el sistema', '2025-11-26 15:32:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `device_action_logs`
--

CREATE TABLE `device_action_logs` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `action` enum('open','close','test','status_check','config_update') COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_by` int(11) DEFAULT NULL COMMENT 'Usuario que realizó la acción',
  `success` tinyint(1) DEFAULT '1',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `response_time` int(11) DEFAULT NULL COMMENT 'Tiempo de respuesta en ms',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `device_action_logs`
--

INSERT INTO `device_action_logs` (`id`, `device_id`, `action`, `action_by`, `success`, `error_message`, `response_time`, `ip_address`, `created_at`) VALUES
(1, 2, 'test', 1, 1, NULL, 481, '187.243.195.15', '2025-11-26 16:44:13'),
(2, 2, 'test', 1, 1, NULL, 131, '187.243.195.15', '2025-11-26 16:50:05'),
(3, 2, 'test', 1, 1, NULL, 271, '187.243.195.15', '2025-11-26 16:54:55'),
(4, 2, 'test', 1, 1, NULL, 114, '187.243.195.15', '2025-11-26 16:55:50'),
(5, 2, 'test', 1, 1, NULL, 311, '187.243.195.15', '2025-11-26 16:56:47'),
(6, 2, 'test', 1, 1, NULL, 252, '187.243.195.15', '2025-11-26 16:58:04'),
(7, 2, 'test', 1, 1, NULL, 403, '187.243.195.15', '2025-11-26 16:59:40'),
(8, 2, 'test', 1, 1, NULL, 304, '187.243.195.15', '2025-11-26 17:00:01'),
(9, 2, 'test', 1, 1, NULL, 137, '187.243.195.15', '2025-11-26 17:00:21'),
(10, 2, 'test', 1, 1, NULL, 437, '187.243.195.15', '2025-11-26 17:05:42'),
(11, 2, 'test', 1, 1, NULL, 453, '187.243.195.15', '2025-11-26 17:06:00'),
(12, 2, 'test', 1, 0, 'Error de conexión: Failed to connect to shelly-208-eu.shelly.cloud port 6022 after 1 ms: Could not connect to server', 2, '187.243.195.15', '2025-11-26 17:12:54'),
(13, 2, 'test', 1, 0, 'Error de conexión: Failed to connect to shelly-208-eu.shelly.cloud port 6022 after 1 ms: Could not connect to server', 1, '187.243.195.15', '2025-11-26 17:13:07'),
(14, 2, 'test', 1, 0, 'HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 655, '187.243.195.15', '2025-11-26 17:14:07'),
(15, 2, 'test', 1, 0, 'HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 592, '187.243.195.15', '2025-11-26 17:15:05'),
(16, 2, 'test', 1, 0, 'HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 554, '187.243.195.15', '2025-11-26 17:16:53'),
(17, 2, 'test', 1, 0, 'HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 568, '187.243.195.15', '2025-11-26 17:17:39'),
(18, 2, 'test', 1, 0, 'HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 604, '187.243.195.15', '2025-11-26 17:17:49'),
(19, 2, 'test', 1, 0, 'HTTP Error 400: {\"isok\":false,\"errors\":{\"wrong_channel\":\"Could not control this relay channel!\"}}', 643, '187.243.195.15', '2025-11-26 17:19:12'),
(20, 2, 'test', 1, 0, 'HTTP Error 404: {\"isok\":false,\"errors\":{\"404\":\"Requested method was not found\"}}', 540, '187.243.195.15', '2025-11-26 17:20:44'),
(21, 2, 'test', 1, 0, 'Error de conexión: URL rejected: Malformed input to a URL function', 0, '187.243.195.15', '2025-11-26 17:21:22'),
(22, 2, 'test', 1, 0, 'Error de conexión: URL rejected: Malformed input to a URL function', 0, '187.243.195.15', '2025-11-26 17:29:42'),
(23, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 2736, '187.243.195.15', '2025-11-26 17:30:02'),
(24, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 2329, '187.243.195.15', '2025-11-26 17:30:16'),
(25, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 2475, '187.243.195.15', '2025-11-26 17:30:59'),
(26, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 5510, '187.243.195.15', '2025-11-26 17:32:16'),
(27, 2, 'test', 1, 0, 'Error de conexión: URL rejected: Malformed input to a URL function', 0, '187.243.195.15', '2025-11-26 17:35:36'),
(28, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 5500, '187.243.195.15', '2025-11-26 17:37:48'),
(29, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 5508, '187.243.195.15', '2025-11-26 17:38:57'),
(30, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 5504, '187.243.195.15', '2025-11-26 17:39:46'),
(31, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 5509, '187.243.195.15', '2025-11-26 17:41:07'),
(32, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 5502, '187.243.195.15', '2025-11-26 17:42:43'),
(33, 2, 'test', 1, 1, 'Dispositivo Shelly activado correctamente', 5959, '187.145.46.170', '2025-11-27 09:37:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `verified` tinyint(1) DEFAULT '0',
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `financial_movements`
--

CREATE TABLE `financial_movements` (
  `id` int(11) NOT NULL,
  `movement_type_id` int(11) NOT NULL,
  `transaction_type` enum('ingreso','egreso') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `payment_method` enum('efectivo','tarjeta','transferencia','paypal','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `financial_movements`
--

INSERT INTO `financial_movements` (`id`, `movement_type_id`, `transaction_type`, `amount`, `description`, `reference_type`, `reference_id`, `property_id`, `resident_id`, `payment_method`, `payment_reference`, `transaction_date`, `created_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'ingreso', 1500.00, 'Cuota de mantenimiento - 2024-11', 'maintenance_fee', 1, 1, NULL, NULL, NULL, '2024-11-10', 1, NULL, '2025-11-22 19:44:11', '2025-11-23 06:27:14'),
(2, 1, 'ingreso', 1200.00, 'Cuota de mantenimiento - 2024-11', 'maintenance_fee', 3, 2, NULL, NULL, NULL, '2024-11-10', 1, NULL, '2025-11-22 19:44:11', '2025-11-23 06:27:14'),
(5, 1, 'ingreso', 1500.00, 'Cuota mantenimiento noviembre', 'maintenance_fee', 1, 1, 1, 'tarjeta', 'MNT-202311', '2024-11-10', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(6, 1, 'ingreso', 1500.00, 'Cuota mantenimiento diciembre', 'maintenance_fee', 2, 1, 1, 'transferencia', 'MNT-202312', '2024-12-10', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(7, 1, 'ingreso', 1500.00, 'Cuota mantenimiento enero', 'maintenance_fee', 3, 1, 1, 'tarjeta', 'MNT-202401', '2025-01-10', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(8, 2, 'ingreso', 500.00, 'Reservación salón eventos', 'reservation', 1, 1, 1, 'efectivo', 'RES-20231201', '2024-12-01', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(9, 3, 'ingreso', 250.00, 'Penalización por ruido', 'penalty', 1, 1, 1, 'tarjeta', 'PEN-20240115', '2025-01-15', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(10, 6, 'egreso', 300.00, 'Pago jardinero externo', 'provider', 1, 1, NULL, NULL, 'PROV-202312', '2024-12-12', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(11, 7, 'egreso', 110.00, 'Factura luz CFE', 'public_service', NULL, 1, NULL, NULL, 'LZ-202412', '2024-12-16', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(12, 8, 'egreso', 420.00, 'Pago mensual guardia', 'payroll', NULL, 1, NULL, NULL, 'PGD-202412', '2024-12-01', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(13, 1, 'ingreso', 1200.00, 'Cuota mantenimiento noviembre', 'maintenance_fee', 2, 2, 2, 'tarjeta', 'MNT-202311', '2024-11-12', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(14, 1, 'ingreso', 1200.00, 'Cuota mantenimiento diciembre', 'maintenance_fee', 2, 2, 2, 'transferencia', 'MNT-202312', '2024-12-12', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(15, 1, 'ingreso', 1200.00, 'Cuota mantenimiento enero', 'maintenance_fee', 3, 2, 2, 'tarjeta', 'MNT-202401', '2025-01-12', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(16, 4, 'ingreso', 1000.00, 'Membresía mensual Premium', 'membership', 1, 2, 2, 'tarjeta', 'MBR-202401', '2025-01-05', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(17, 6, 'egreso', 400.00, 'Gasto mantenimiento plomería', 'maintenance', NULL, 2, NULL, NULL, 'SVC-20240113', '2025-01-13', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(18, 1, 'ingreso', 1500.00, 'Cuota mantenimiento noviembre', 'maintenance_fee', 3, 3, 3, 'tarjeta', 'MNT-202311', '2024-11-15', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(19, 1, 'ingreso', 1500.00, 'Cuota mantenimiento diciembre', 'maintenance_fee', 3, 3, 3, 'transferencia', 'MNT-202312', '2024-12-15', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(20, 2, 'ingreso', 50.00, 'Reservación cancha tenis', 'reservation', 2, 3, 3, 'efectivo', 'RES-20240122', '2025-01-22', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(21, 3, 'ingreso', 300.00, 'Penalización por daño amenidad', 'penalty', 2, 3, 3, 'paypal', 'PEN-20250210', '2025-02-10', 1, 'Pago por daños en alberca', '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(22, 10, 'egreso', 600.00, 'Reparación red eléctrica', 'repair', NULL, 3, NULL, NULL, 'REP-20250218', '2025-02-18', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(23, 1, 'ingreso', 1500.00, 'Cuota mantenimiento febrero', 'maintenance_fee', 4, 4, 4, 'tarjeta', 'MNT-202402', '2025-02-10', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(24, 1, 'ingreso', 1500.00, 'Cuota mantenimiento marzo', 'maintenance_fee', 5, 4, 4, 'efectivo', 'MNT-202403', '2025-03-10', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(25, 1, 'ingreso', 1500.00, 'Cuota mantenimiento abril', 'maintenance_fee', 6, 5, 5, 'tarjeta', 'MNT-202404', '2025-04-10', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(26, 2, 'ingreso', 100.00, 'Reservación asador', 'reservation', 3, 5, 5, 'efectivo', 'RES-20240502', '2025-05-02', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(27, 6, 'egreso', 200.00, 'Pago mantenimiento limpieza', 'maintenance', NULL, 4, NULL, NULL, 'LIM-202503', '2025-03-15', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(28, 7, 'egreso', 95.00, 'Pago agua', 'public_service', NULL, 3, NULL, NULL, 'AGUA-202502', '2025-02-15', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(29, 5, 'ingreso', 300.00, 'Otros ingresos varios', 'other', NULL, 4, 3, 'efectivo', 'OTR-202508', '2025-08-03', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(30, 9, 'egreso', 250.00, 'Pago proveedor externo', 'provider', NULL, 4, NULL, NULL, 'PROV-202505', '2025-05-05', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(31, 12, 'egreso', 175.00, 'Egreso extraordinario', 'other', NULL, 5, NULL, NULL, 'EGST-202510', '2025-10-11', 1, NULL, '2025-11-23 18:46:38', '2025-11-23 18:46:38'),
(32, 1, 'ingreso', 100000.00, 'probando', NULL, NULL, 2, NULL, 'efectivo', NULL, '2025-11-23', 1, NULL, '2025-11-23 23:48:50', '2025-11-23 23:48:50'),
(34, 1, 'ingreso', 1500.00, 'hj', NULL, NULL, 3, 4, 'efectivo', NULL, '2025-11-23', 1, NULL, '2025-11-24 05:18:04', '2025-11-24 05:19:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `financial_movement_types`
--

CREATE TABLE `financial_movement_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` enum('ingreso','egreso','ambos') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `financial_movement_types`
--

INSERT INTO `financial_movement_types` (`id`, `name`, `description`, `category`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Cuota de Mantenimiento', 'Pago mensual de cuota de mantenimiento', 'ingreso', 1, '2025-11-23 06:27:13', '2025-11-24 05:35:27'),
(2, 'Reservación de Amenidades', 'Pago por reservación de amenidades', 'ingreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(3, 'Penalización', 'Multas y penalizaciones', 'ingreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(4, 'Membresía Mensual', 'Pago de membresía mensual', 'ingreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(5, 'Otros Ingresos', 'Ingresos diversos', 'ingreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(6, 'Mantenimiento General', 'Gastos de mantenimiento del residencial', 'egreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(7, 'Servicios Públicos', 'Pago de luz, agua, etc.', 'egreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(8, 'Personal', 'Pago de nómina y personal', 'egreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(9, 'Proveedores', 'Pago a proveedores externos', 'egreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(10, 'Reparaciones', 'Gastos de reparaciones y mejoras', 'egreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(11, 'Seguridad', 'Gastos relacionados con seguridad', 'egreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(12, 'Otros Egresos', 'Egresos diversos', 'egreso', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(13, 'Nómina', 'Trabajadores', 'egreso', 1, '2025-11-24 00:02:05', '2025-11-24 00:02:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maintenance_comments`
--

CREATE TABLE `maintenance_comments` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maintenance_fees`
--

CREATE TABLE `maintenance_fees` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `period` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `accumulated_debt` decimal(10,2) DEFAULT '0.00',
  `late_fee` decimal(10,2) DEFAULT '0.00',
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `payment_method` enum('efectivo','tarjeta','transferencia','paypal','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_confirmation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reminder_sent` tinyint(1) DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `maintenance_fees`
--

INSERT INTO `maintenance_fees` (`id`, `property_id`, `period`, `amount`, `accumulated_debt`, `late_fee`, `due_date`, `paid_date`, `payment_method`, `payment_reference`, `payment_confirmation`, `status`, `reminder_sent`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, '2024-11', 1500.00, 0.00, 0.00, '2024-11-10', NULL, NULL, NULL, NULL, 'paid', 0, NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(2, 1, '2024-12', 1500.00, 0.00, 0.00, '2024-12-10', NULL, NULL, NULL, NULL, 'pending', 0, NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(3, 2, '2024-11', 1200.00, 0.00, 0.00, '2024-11-10', NULL, NULL, NULL, NULL, 'paid', 0, NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(4, 2, '2024-12', 1200.00, 0.00, 0.00, '2024-12-10', NULL, NULL, NULL, NULL, 'pending', 0, NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(5, 3, '2024-11', 1500.00, 0.00, 0.00, '2024-11-10', NULL, NULL, NULL, NULL, 'overdue', 0, NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(6, 3, '2024-12', 1500.00, 0.00, 0.00, '2024-12-10', NULL, NULL, NULL, NULL, 'pending', 0, NULL, '2025-11-22 19:44:11', '2025-11-22 19:44:11'),
(7, 7, '2025-11', 1500.00, 0.00, 0.00, '2025-11-10', '2025-11-10', NULL, NULL, NULL, 'paid', 0, NULL, '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(8, 8, '2025-11', 1400.00, 0.00, 0.00, '2025-11-10', NULL, NULL, NULL, NULL, 'pending', 0, NULL, '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(9, 9, '2025-11', 1350.00, 0.00, 0.00, '2025-11-10', '2025-11-12', NULL, NULL, NULL, 'paid', 0, NULL, '2025-11-23 18:18:07', '2025-11-23 18:18:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maintenance_reports`
--

CREATE TABLE `maintenance_reports` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `category` enum('alumbrado','jardineria','plomeria','seguridad','limpieza','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('baja','media','alta','urgente') COLLATE utf8mb4_unicode_ci DEFAULT 'media',
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` enum('pendiente','en_proceso','completado','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `photos` text COLLATE utf8mb4_unicode_ci,
  `estimated_completion` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `maintenance_reports`
--

INSERT INTO `maintenance_reports` (`id`, `resident_id`, `property_id`, `category`, `title`, `description`, `priority`, `location`, `assigned_to`, `status`, `photos`, `estimated_completion`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 4, 3, 'jardineria', 'Pasto ', 'Crecido', 'media', '45', NULL, 'en_proceso', NULL, NULL, NULL, '2025-11-23 18:39:09', '2025-11-24 05:32:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `memberships`
--

CREATE TABLE `memberships` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `membership_plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','suspended','cancelled','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `payment_day` int(11) DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membership_payments`
--

CREATE TABLE `membership_payments` (
  `id` int(11) NOT NULL,
  `membership_id` int(11) NOT NULL,
  `period` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `payment_method` enum('efectivo','tarjeta','transferencia','paypal','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `financial_movement_id` int(11) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membership_plans`
--

CREATE TABLE `membership_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `monthly_cost` decimal(10,2) NOT NULL,
  `benefits` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `membership_plans`
--

INSERT INTO `membership_plans` (`id`, `name`, `description`, `monthly_cost`, `benefits`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Básico', 'Plan básico con acceso a amenidades estándar', 500.00, '[\"Acceso a alberca\", \"Acceso a gimnasio\", \"2 reservaciones mensuales\"]', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(2, 'Premium', 'Plan premium con beneficios adicionales', 1000.00, '[\"Acceso a alberca\", \"Acceso a gimnasio\", \"Reservaciones ilimitadas\", \"Descuento 10% en eventos\", \"Invitados sin costo\"]', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13'),
(3, 'VIP', 'Plan VIP con todos los beneficios', 1500.00, '[\"Acceso a alberca\", \"Acceso a gimnasio\", \"Reservaciones prioritarias\", \"Descuento 20% en eventos\", \"Invitados sin costo\", \"Acceso a áreas exclusivas\"]', 1, '2025-11-23 06:27:13', '2025-11-23 06:27:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 6, '9692d4176d46ac3e8e18c4152e27941b1e567408e8db75e38de96ff781ca6770', '2025-11-24 00:22:28', 0, '2025-11-24 05:22:28'),
(2, 7, '9b600485cbd74fb5fc7b52b5b34cc4ef42990a9c3e629c85afb2836bc2706618', '2025-11-24 00:23:01', 0, '2025-11-24 05:23:01'),
(3, 6, '83168d7b4cd0994f016f083348c6bfa37b45a4877a735984088bc4f3725d2972', '2025-11-24 02:08:59', 0, '2025-11-24 07:08:59'),
(4, 7, 'f66ee5660c1089e36bc11e39e3d0b735af79f4b655a7a995544b867be2fa1c9e', '2025-11-24 03:09:03', 0, '2025-11-24 08:09:03'),
(5, 6, '2a332dbbf3ab3dff47f9813fcb7ef9a9bd472b22cac4d5c6c88e524d88f11379', '2025-11-24 07:53:46', 0, '2025-11-24 12:53:46'),
(6, 7, '2c305c8227bdba4f3205a93dfc6f684de3697f7c69417463ce36c7cefe7e8152', '2025-11-24 07:59:11', 0, '2025-11-24 12:59:11'),
(7, 7, '5d87f5b96c56ce4fccf369283ede58b9d74c72f646587f2625ff3eee0139714e', '2025-11-24 08:00:11', 0, '2025-11-24 13:00:11'),
(8, 7, 'f6c0613c9b5bbc2005a783ba1caece3a7a1e7a4446658289439c663d33d81f83', '2025-11-24 08:05:43', 0, '2025-11-24 13:05:43'),
(9, 7, 'f2b05bea728b26eacb795de22923507be089434659f72bfbf95a769cfbc05e9a', '2025-11-24 08:15:48', 1, '2025-11-24 13:15:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_reminders`
--

CREATE TABLE `payment_reminders` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `reminder_type` enum('email','sms','notification') COLLATE utf8mb4_unicode_ci DEFAULT 'email',
  `sent_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `penalties`
--

CREATE TABLE `penalties` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `penalty_type` enum('no_show','damage','overtime','rule_violation','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) DEFAULT '0.00',
  `penalty_date` date NOT NULL,
  `status` enum('pending','paid','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `block_until` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pending_validations`
--

CREATE TABLE `pending_validations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validation_type` enum('resident','user') COLLATE utf8mb4_unicode_ci DEFAULT 'resident',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `subdivision_id` int(11) DEFAULT NULL,
  `property_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `street` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tower` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_type` enum('casa','departamento','lote') COLLATE utf8mb4_unicode_ci DEFAULT 'casa',
  `bedrooms` int(11) DEFAULT '0',
  `bathrooms` int(11) DEFAULT '0',
  `area_m2` decimal(10,2) DEFAULT NULL,
  `status` enum('ocupada','desocupada','en_construccion') COLLATE utf8mb4_unicode_ci DEFAULT 'ocupada',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `properties`
--

INSERT INTO `properties` (`id`, `subdivision_id`, `property_number`, `street`, `section`, `tower`, `property_type`, `bedrooms`, `bathrooms`, `area_m2`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'A-101', 'Av. de las Flores', 'Sección A', 'Torre 1', 'departamento', 3, 2, 120.50, 'ocupada', '2025-11-22 19:44:11', '2025-11-23 15:59:25'),
(2, 1, 'A-102', 'Av. de las Flores', 'Sección A', 'Torre 1', 'departamento', 2, 1, 85.00, 'ocupada', '2025-11-22 19:44:11', '2025-11-23 15:59:25'),
(3, 1, 'B-201', 'Calle Querétaro', 'Sección B', 'Torre 2', 'departamento', 3, 2, 110.00, 'ocupada', '2025-11-22 19:44:11', '2025-11-23 15:59:25'),
(4, 1, 'C-001', 'Calle del Parque', 'Sección C', NULL, 'casa', 4, 3, 180.00, 'ocupada', '2025-11-22 19:44:11', '2025-11-23 15:59:25'),
(5, 1, 'C-002', 'Calle del Parque', 'Sección C', NULL, 'casa', 3, 2, 150.00, 'desocupada', '2025-11-22 19:44:11', '2025-11-23 15:59:25'),
(6, NULL, 'B-202', 'Calle Querétaro ', 'Sección B', 'Torre 2 ', 'casa', 4, 2, 150.00, 'desocupada', '2025-11-23 17:58:11', '2025-11-23 17:58:11'),
(7, 1, 'D-201', 'Calle Cedros', 'Sección D', 'Torre 3', 'departamento', 3, 2, 115.00, 'ocupada', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(8, 1, 'D-202', 'Calle Cedros', 'Sección D', 'Torre 3', 'departamento', 2, 1, 90.00, 'ocupada', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(9, 1, 'E-001', 'Calle Sauces', 'Sección E', NULL, 'casa', 4, 3, 200.00, 'ocupada', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(10, 1, 'E-002', 'Calle Sauces', 'Sección E', NULL, 'casa', 3, 2, 160.00, 'desocupada', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(11, 1, 'F-101', 'Calle Palmas', 'Sección F', 'Torre 4', 'departamento', 2, 1, 80.50, 'en_construccion', '2025-11-23 18:18:07', '2025-11-23 18:18:07');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `property_debt_summary`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `property_debt_summary` (
`property_id` int(11)
,`property_number` varchar(20)
,`section` varchar(50)
,`resident_id` int(11)
,`resident_name` varchar(201)
,`email` varchar(100)
,`phone` varchar(20)
,`pending_payments_count` bigint(21)
,`total_debt` decimal(32,2)
,`total_paid` decimal(32,2)
,`oldest_due_date` date
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `guests_count` int(11) DEFAULT '0',
  `amount` decimal(10,2) DEFAULT '0.00',
  `payment_status` enum('pending','paid','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `status` enum('pending','confirmed','completed','cancelled','no_show') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservations`
--

INSERT INTO `reservations` (`id`, `amenity_id`, `resident_id`, `reservation_date`, `start_time`, `end_time`, `guests_count`, `amount`, `payment_status`, `status`, `cancellation_reason`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 6, '2025-12-15', '18:00:00', '21:00:00', 25, 500.00, 'paid', 'confirmed', NULL, NULL, '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(2, 2, 7, '2025-12-16', '09:00:00', '12:00:00', 5, 0.00, 'paid', 'completed', NULL, NULL, '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(3, 3, 8, '2025-12-20', '14:00:00', '15:30:00', 8, 100.00, 'pending', 'pending', NULL, NULL, '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(4, 2, 4, '2025-11-30', '12:00:00', '15:00:00', 10, 0.00, 'pending', 'cancelled', NULL, '', '2025-11-23 18:38:39', '2025-11-24 08:07:53'),
(5, 4, 4, '2025-11-24', '12:00:00', '13:00:00', 3, 50.00, 'pending', 'pending', NULL, '', '2025-11-24 14:22:03', '2025-11-24 14:22:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `residents`
--

CREATE TABLE `residents` (
  `id` int(11) NOT NULL,
  `subdivision_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `relationship` enum('propietario','inquilino','familiar') COLLATE utf8mb4_unicode_ci DEFAULT 'propietario',
  `contract_start` date DEFAULT NULL,
  `contract_end` date DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `documents_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','pending','deleted') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `residents`
--

INSERT INTO `residents` (`id`, `subdivision_id`, `user_id`, `property_id`, `relationship`, `contract_start`, `contract_end`, `is_primary`, `documents_path`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 1, 'propietario', NULL, NULL, 1, NULL, 'active', '2025-11-22 19:44:11', '2025-11-24 13:09:40'),
(2, 1, 4, 2, 'propietario', NULL, NULL, 1, NULL, 'inactive', '2025-11-22 19:44:11', '2025-11-24 16:22:01'),
(3, 1, 5, 3, 'propietario', NULL, NULL, 1, NULL, 'active', '2025-11-22 19:44:11', '2025-11-23 15:59:25'),
(4, 1, 7, 3, 'propietario', NULL, NULL, 1, NULL, 'active', '2025-11-23 08:24:35', '2025-11-23 15:59:25'),
(5, NULL, 8, 3, 'propietario', NULL, NULL, 1, NULL, 'active', '2025-11-23 17:53:32', '2025-11-23 17:53:32'),
(6, 1, 9, 7, 'propietario', '2025-01-01', NULL, 1, NULL, 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(7, 1, 10, 8, 'propietario', '2025-02-01', NULL, 1, NULL, 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(8, 1, 11, 9, 'propietario', '2025-03-01', NULL, 1, NULL, 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(9, 1, 12, 10, 'propietario', '2025-04-01', NULL, 1, NULL, 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(10, 1, 13, 11, 'propietario', '2025-05-01', NULL, 1, NULL, 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(11, NULL, 14, 4, 'propietario', NULL, NULL, 1, NULL, 'deleted', '2025-11-23 22:17:11', '2025-11-24 13:09:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resident_access_passes`
--

CREATE TABLE `resident_access_passes` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `pass_type` enum('single_use','temporary','permanent') COLLATE utf8mb4_unicode_ci DEFAULT 'single_use',
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valid_from` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `valid_until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uses_count` int(11) DEFAULT '0',
  `max_uses` int(11) DEFAULT '1',
  `status` enum('active','used','expired','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resident_balances`
--

CREATE TABLE `resident_balances` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `balance` decimal(10,2) DEFAULT '0.00',
  `last_payment_date` date DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `resident_dashboard_stats`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `resident_dashboard_stats` (
`resident_id` int(11)
,`user_id` int(11)
,`total_visits` bigint(21)
,`total_reservations` bigint(21)
,`total_maintenance_reports` bigint(21)
,`current_balance` decimal(10,2)
,`pending_payments` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `resident_debt_summary`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `resident_debt_summary` (
`resident_id` int(11)
,`user_id` int(11)
,`resident_name` varchar(201)
,`email` varchar(100)
,`phone` varchar(20)
,`property_number` varchar(20)
,`total_fees` bigint(21)
,`pending_amount` decimal(32,2)
,`overdue_amount` decimal(32,2)
,`paid_amount` decimal(32,2)
,`oldest_due_date` date
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `resident_payment_history`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `resident_payment_history` (
`resident_id` int(11)
,`user_id` int(11)
,`resident_name` varchar(201)
,`property_number` varchar(20)
,`fee_id` int(11)
,`period` varchar(7)
,`amount` decimal(10,2)
,`due_date` date
,`paid_date` date
,`payment_status` enum('pending','paid','overdue','cancelled')
,`payment_method` enum('efectivo','tarjeta','transferencia','paypal','otro')
,`financial_movement_id` int(11)
,`transaction_date` date
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `security_alerts`
--

CREATE TABLE `security_alerts` (
  `id` int(11) NOT NULL,
  `alert_type` enum('intrusion','fire','medical','vandalism','noise','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `status` enum('open','in_progress','resolved','false_alarm') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `security_patrols`
--

CREATE TABLE `security_patrols` (
  `id` int(11) NOT NULL,
  `guard_id` int(11) NOT NULL,
  `patrol_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `patrol_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `route` text COLLATE utf8mb4_unicode_ci,
  `incidents_found` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('in_progress','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `security_patrols`
--

INSERT INTO `security_patrols` (`id`, `guard_id`, `patrol_start`, `patrol_end`, `route`, `incidents_found`, `notes`, `status`, `created_at`) VALUES
(1, 1, '2025-11-22 21:00:29', '0000-00-00 00:00:00', 'Hola', NULL, NULL, 'in_progress', '2025-11-22 21:00:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subdivisions`
--

CREATE TABLE `subdivisions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subdivisions`
--

INSERT INTO `subdivisions` (`id`, `name`, `description`, `address`, `city`, `state`, `postal_code`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Fraccionamiento Principal', 'Fraccionamiento principal del sistema', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-11-23 15:57:03', '2025-11-23 15:57:03'),
(2, 'Fraccionamiento Principal', 'Fraccionamiento principal del sistema', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-11-23 15:59:24', '2025-11-23 15:59:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_optimization`
--

CREATE TABLE `system_optimization` (
  `id` int(11) NOT NULL,
  `optimization_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `optimization_value` text COLLATE utf8mb4_unicode_ci,
  `is_enabled` tinyint(1) DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `system_optimization`
--

INSERT INTO `system_optimization` (`id`, `optimization_key`, `optimization_value`, `is_enabled`, `description`, `created_at`, `updated_at`) VALUES
(1, 'cache_enabled', '1', 1, 'Habilitar caché del sistema', '2025-11-23 15:59:25', '2025-11-23 15:59:25'),
(2, 'compress_images', '1', 1, 'Comprimir imágenes automáticamente', '2025-11-23 15:59:25', '2025-11-23 15:59:25'),
(3, 'lazy_loading', '1', 1, 'Carga diferida de imágenes', '2025-11-23 15:59:25', '2025-11-23 15:59:25'),
(4, 'minify_css', '0', 0, 'Minificar archivos CSS', '2025-11-23 15:59:25', '2025-11-23 15:59:25'),
(5, 'minify_js', '0', 0, 'Minificar archivos JavaScript', '2025-11-23 15:59:25', '2025-11-23 15:59:25'),
(6, 'database_optimization', '0', 0, 'Optimización automática de base de datos', '2025-11-23 15:59:25', '2025-11-23 15:59:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('text','number','boolean','json','file') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Residencial Juriquilla', 'text', 'Nombre del sitio', '2025-11-23 12:04:54'),
(2, 'site_logo', 'uploads/logos/logo_1764182658.png', 'file', 'Logo del sitio', '2025-11-26 18:44:18'),
(3, 'site_email', 'hola@residencialqro.com', 'text', 'Email principal del sistema', '2025-11-23 12:06:37'),
(4, 'site_phone', '44212345447', 'text', 'Teléfono de contacto', '2025-11-24 05:20:58'),
(5, 'maintenance_fee_default', '1500', 'number', 'Cuota de mantenimiento por defecto', '2025-11-22 19:44:11'),
(6, 'qr_enabled', '1', 'boolean', 'Habilitar generación de códigos QR', '2025-11-22 19:44:11'),
(7, 'theme_color', 'blue', 'text', 'Color principal del tema', '2025-11-26 18:44:44'),
(8, 'paypal_enabled', '1', 'boolean', 'Habilitar pagos con PayPal', '2025-11-24 07:39:22'),
(9, 'whatsapp_enabled', '0', 'boolean', 'Habilitar notificaciones por WhatsApp', '2025-11-22 19:44:11'),
(11, 'devices_enabled', '1', 'boolean', 'Habilitar gestión de dispositivos de acceso', '2025-11-23 00:02:47'),
(12, 'devices_auto_status_check', '1', 'boolean', 'Verificar estado de dispositivos automáticamente', '2025-11-23 00:02:47'),
(13, 'devices_status_check_interval', '300', 'number', 'Intervalo de verificación de estado en segundos', '2025-11-23 00:02:47'),
(15, 'qr_expiration_hours', '24', 'text', NULL, '2025-11-23 00:17:03'),
(16, 'qr_api_key', '', 'text', NULL, '2025-11-23 00:17:50'),
(17, 'qr_logo_enabled', '1', 'text', NULL, '2025-11-23 00:17:03'),
(24, 'password_reset_expiry', '3600', 'text', 'Tiempo de expiración del token de reset (segundos)', '2025-11-23 11:52:53'),
(25, 'audit_retention_days', '180', 'text', 'Días de retención de logs de auditoría', '2025-11-23 11:52:53'),
(45, 'site_copyright', '© 2025 Sistema Residencial. Todos los derechos reservados by ID', 'text', 'Texto de copyright en el pie de página', '2025-11-23 13:27:53'),
(51, 'whatsapp_number', '', 'text', 'Número de WhatsApp para soporte', '2025-11-23 13:23:57'),
(66, 'terms_and_conditions', 'Al registrarse, acepta cumplir con las políticas del fraccionamiento.', 'text', 'Términos y condiciones del registro público', '2025-11-23 15:59:25'),
(67, 'enable_email_verification', '1', 'boolean', 'Habilitar verificación de correo electrónico', '2025-11-23 15:59:25'),
(68, 'enable_admin_approval', '1', 'boolean', 'Requiere aprobación de admin para nuevos residentes', '2025-11-23 15:59:25'),
(71, 'support_email', 'soporte@residencial.com', 'text', 'Technical support email', '2025-11-24 15:34:01'),
(72, 'support_phone', '+52 442 123 4567', 'text', 'Technical support phone', '2025-11-24 15:34:01'),
(73, 'support_url', 'https://janetzy.shop/residencial/soporte-tecnico', 'text', 'Public support portal URL', '2025-11-24 16:24:16'),
(76, 'email_verification_required', '1', 'boolean', 'Require email verification for new registrations', '2025-11-23 21:16:21'),
(77, 'admin_approval_required', '1', 'boolean', 'Require admin approval for new registrations', '2025-11-23 21:16:21'),
(78, 'payment_reminder_days', '1', 'number', 'Días antes del vencimiento para enviar recordatorio', '2025-11-24 07:39:22'),
(79, 'paypal_client_id', '', 'text', 'PayPal Client ID', '2025-11-23 21:16:21'),
(80, 'paypal_secret', '', 'text', 'PayPal Secret Key', '2025-11-23 21:16:21'),
(150, 'email_host', 'janetzy.shop', 'text', 'Servidor SMTP para envío de correos', '2025-11-24 07:39:22'),
(151, 'email_port', '465', 'text', 'Puerto SMTP', '2025-11-24 07:39:22'),
(152, 'email_user', 'hola@janetzy.shop', 'text', 'Usuario SMTP', '2025-11-24 07:39:22'),
(153, 'email_password', 'Danjohn007', 'text', 'Contraseña SMTP (configurar en Settings)', '2025-11-24 12:58:54'),
(154, 'email_from', 'hola@janetzy.shop', 'text', 'Dirección de remitente', '2025-11-24 07:39:22'),
(192, 'paypal_mode', 'sandbox', 'text', 'PayPal Mode (sandbox o live)', '2025-11-24 07:39:22'),
(193, 'system_optimization_enabled', '1', 'boolean', 'Auto-optimización del sistema habilitada', '2025-11-24 07:39:22'),
(194, 'cache_enabled', '1', 'boolean', 'Enable system cache for better performance', '2025-11-24 15:34:01'),
(195, 'max_records_per_page', '50', 'number', 'Maximum records to display per page', '2025-11-24 15:34:01'),
(308, 'support_hours', 'Lunes a Viernes 9:00 AM - 6:00 PM', 'text', 'Support service hours', '2025-11-24 15:34:01'),
(312, 'cache_ttl', '3600', 'text', 'Cache time to live in seconds', '2025-11-24 15:34:01'),
(313, 'query_cache_enabled', '1', 'text', 'Enable query result caching', '2025-11-24 15:34:01'),
(314, 'image_optimization', '1', 'text', 'Enable automatic image optimization', '2025-11-24 15:34:01'),
(315, 'lazy_loading', '1', 'text', 'Enable lazy loading for images', '2025-11-24 15:34:01'),
(316, 'minify_assets', '0', 'text', 'Enable asset minification', '2025-11-24 15:34:01'),
(317, 'session_timeout', '3600', 'text', 'Session timeout in seconds', '2025-11-24 15:34:01'),
(336, 'migration_006_applied', '2025-11-24 06:57:42', 'text', NULL, '2025-11-24 12:57:42'),
(409, 'lpr_enabled', '1', 'boolean', 'Habilitar reconocimiento automático de placas', '2025-11-24 16:23:00'),
(410, 'lpr_auto_open_gate', '1', 'boolean', 'Abrir puerta automáticamente si hay coincidencia', '2025-11-24 16:23:00'),
(411, 'lpr_retention_days', '90', 'number', 'Días para retener registros de placas detectadas', '2025-11-24 16:23:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `subdivision_id` int(11) DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('superadmin','administrador','guardia','residente') COLLATE utf8mb4_unicode_ci DEFAULT 'residente',
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `house_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','blocked','pending','deleted') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `subdivision_id`, `username`, `email`, `email_verification_token`, `email_verified`, `email_verified_at`, `password`, `role`, `first_name`, `last_name`, `phone`, `house_number`, `photo`, `status`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 1, 'admin', 'admin@residencial.com', NULL, 1, '2025-11-23 15:59:25', '$2y$10$Smktg525qYZgcBdG/ap8CuO4L9gLr4pdtI1VohXLWfx/aVn2rHRFm', 'superadmin', 'ID', 'Residencial', '+52 442 123 4443', NULL, 'user_1_1763920717.jpg', 'active', '2025-11-22 19:44:11', '2025-11-27 09:12:30', '2025-11-27 09:12:30'),
(2, 1, 'guardia1', 'guardia@residencial.com', NULL, 1, '2025-11-23 15:59:25', '$2y$10$LZNnYntYqRMc0haLIxUIKudBeqpPajqEseuPpBr5hPck7z.cnXww6', 'guardia', 'José', 'Guardián', '4422345678', NULL, NULL, 'active', '2025-11-22 19:44:11', '2025-11-24 19:23:16', NULL),
(3, 1, 'residente1', 'juan.perez@email.com', NULL, 1, '2025-11-23 15:59:25', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'residente', 'Juan', 'Pérez García', '4423456789', NULL, NULL, 'active', '2025-11-22 19:44:11', '2025-11-24 16:12:16', NULL),
(4, 1, 'residente2', 'maria.lopez@email.com', NULL, 1, '2025-11-23 15:59:25', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'residente', 'María', 'López Sánchez', '4424567890', NULL, NULL, 'inactive', '2025-11-22 19:44:11', '2025-11-24 16:22:01', NULL),
(5, 1, 'residente3', 'pedro.martinez@email.com', NULL, 1, '2025-11-23 15:59:25', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'residente', 'Pedro', 'Martínez Rodríguez', '+52 442 567 8901', NULL, NULL, 'active', '2025-11-22 19:44:11', '2025-11-23 15:59:25', NULL),
(6, 1, 'danjohn007', 'dan@impactosdigitales.com', NULL, 1, '2025-11-23 15:59:25', '$2y$10$sk0TCVq3B1asFiinUSacJOiv5lZwHKHnSwB7tB/SjZkQIHN/EMc9u', 'residente', 'Dan', 'Raso', '4425986318', NULL, NULL, 'active', '2025-11-23 00:27:47', '2025-11-24 08:06:18', '2025-11-24 08:06:18'),
(7, 1, 'janerosas', 'jane@impactosdigitales.com', NULL, 1, '2025-11-23 15:59:25', '$2y$10$Nb2X9.xwZdQd1GQ8fgtKfu.FD8912Kur2X6BBiQKl7egBYGhyAZ5a', 'residente', 'Jane', 'Rosas', '4424865389', NULL, NULL, 'active', '2025-11-23 08:24:35', '2025-11-24 14:21:15', '2025-11-24 14:21:15'),
(8, NULL, 'lleya.rodriguez.s', 'lleya.rodriguez.s@gmail.com', NULL, 0, NULL, '$2y$10$WIq8tkzo0aZigh1q/0uU.uZY8Q3Ra6ox4VD5XA2lo8Q3Typz0iZcq', 'residente', 'Valeria', 'Rodriguez Sanchez', '4461176401', NULL, NULL, 'active', '2025-11-23 17:53:32', '2025-11-23 17:53:32', NULL),
(9, 1, 'superadmin', 'superadmin@residencial.com', NULL, 1, NULL, '$2y$10$ABCDEF123456', 'superadmin', 'Ana', 'Ortega', '4421001000', NULL, NULL, 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07', NULL),
(10, 1, 'guardia2', 'guardia2@residencial.com', NULL, 1, NULL, '$2y$10$m3BoNskjkot.PsKeJsLYYO2rYH96GjHTlnCqpPbNxgxdn1Yh.mJum', 'guardia', 'Luis', 'Ramirez', '4421001001', NULL, NULL, 'active', '2025-11-23 18:18:07', '2025-11-24 19:24:25', NULL),
(11, 1, 'residente4', 'carla.mendez@email.com', NULL, 1, NULL, '$2y$10$ABCDEF123458', 'residente', 'Carla', 'Mendez Torres', '4421001002', NULL, NULL, 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07', NULL),
(12, 1, 'residente5', 'arturo.vera@email.com', NULL, 1, NULL, '$2y$10$ABCDEF123459', 'residente', 'Arturo', 'Vera Luna', '4421001003', NULL, NULL, 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07', NULL),
(13, 1, 'residente6', 'monica.diaz@email.com', NULL, 1, NULL, '$2y$10$ABCDEF123460', 'residente', 'Monica', 'Diaz Juarez', '4421001004', NULL, NULL, 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07', NULL),
(14, NULL, 'administracion_20c07f04.deleted.1763989771', 'administracion@impactosdigitales.com.deleted.1763989771', '521ee1708836f10d433895c4fccb88431465895e3251934c67738c968fab5bb7', 0, NULL, '$2y$10$r9vX6UXexKclSP5/uJfK5e9a71O9cJ.hOSrahth5J4RvLXG0jk7bu', 'residente', 'Paola', 'Cadena', '8912368997', NULL, 'user_14_1763941498.jpg', 'deleted', '2025-11-23 22:17:11', '2025-11-24 13:09:31', '2025-11-23 23:40:26'),
(15, NULL, 'tecnologia', 'tecnologia@idindustrial.com.mx', NULL, 0, NULL, '$2y$10$I8ZAqfDLkb3h1dnpsVHf3u924THCWxl/sVR06tI5Eo6FOjNvtOgTW', 'guardia', 'Luis', 'Jimenez', '8326846896', NULL, NULL, 'active', '2025-11-24 19:25:21', '2025-11-26 20:49:20', '2025-11-26 20:49:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `subdivision_id` int(11) DEFAULT NULL,
  `resident_id` int(11) NOT NULL,
  `plate` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `vehicle_type` enum('auto','motocicleta','camioneta','otro') COLLATE utf8mb4_unicode_ci DEFAULT 'auto',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vehicles`
--

INSERT INTO `vehicles` (`id`, `subdivision_id`, `resident_id`, `plate`, `brand`, `model`, `color`, `year`, `vehicle_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'QRO-123-A', 'Toyota', 'Camry', 'Blanco', 2022, 'auto', 'active', '2025-11-22 19:44:11', '2025-11-23 15:59:25'),
(2, 1, 2, 'QRO-456-B', 'Honda', 'CR-V', 'Negro', 2021, 'camioneta', 'active', '2025-11-22 19:44:11', '2025-11-23 15:59:25'),
(3, 1, 3, 'QRO-789-C', 'Nissan', 'Sentra', 'Gris', 2020, 'auto', 'active', '2025-11-22 19:44:11', '2025-11-23 15:59:25'),
(4, NULL, 5, 'RSV-205-BV', 'Nissan ', 'AB-54-FC', 'Negro', 2022, 'camioneta', 'active', '2025-11-23 17:59:50', '2025-11-23 17:59:50'),
(5, 1, 6, 'QRO-124-D', 'Mazda', 'CX-5', 'Rojo', 2023, 'auto', 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(6, 1, 7, 'QRO-125-E', 'Ford', 'Fiesta', 'Azul', 2019, 'auto', 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(7, 1, 8, 'QRO-126-F', 'BMW', 'X1', 'Blanco', 2021, 'camioneta', 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(8, 1, 9, 'QRO-127-G', 'Kia', 'Rio', 'Gris', 2018, 'auto', 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(9, 1, 10, 'QRO-128-H', 'Chevrolet', 'Aveo', 'Negro', 2020, 'auto', 'active', '2025-11-23 18:18:07', '2025-11-23 18:18:07'),
(10, NULL, 1, 'ABC123X', 'Toyota', 'Corolla', 'Blanco', 2020, 'auto', 'active', '2025-11-24 18:31:25', '2025-11-24 18:31:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visits`
--

CREATE TABLE `visits` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `visitor_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `visitor_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visitor_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_plate` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identification_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path to visitor identification photo',
  `visit_type` enum('personal','proveedor','delivery','otro') COLLATE utf8mb4_unicode_ci DEFAULT 'personal',
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_from` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `valid_until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `entry_time` timestamp NULL DEFAULT NULL,
  `exit_time` timestamp NULL DEFAULT NULL,
  `guard_entry_id` int(11) DEFAULT NULL,
  `guard_exit_id` int(11) DEFAULT NULL,
  `status` enum('pending','active','completed','cancelled','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `visits`
--

INSERT INTO `visits` (`id`, `resident_id`, `visitor_name`, `visitor_id`, `visitor_phone`, `vehicle_plate`, `identification_photo`, `visit_type`, `qr_code`, `valid_from`, `valid_until`, `entry_time`, `exit_time`, `guard_entry_id`, `guard_exit_id`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'Dan Raso', '', '', '', NULL, 'personal', 'VIS-20251122-C5A776D1', '2025-11-23 00:17:00', '2025-11-23 04:17:00', NULL, NULL, NULL, NULL, 'pending', '', '2025-11-23 00:17:21', '2025-11-23 00:17:21'),
(2, 2, 'Jonathan Rios', '', '', '', NULL, 'personal', 'VIS-20251122-F85DECD8', '2025-11-23 00:17:00', '2025-11-23 04:17:00', NULL, NULL, NULL, NULL, 'pending', '', '2025-11-23 00:18:05', '2025-11-23 00:18:05'),
(3, 5, 'Rodrigo Sanchez', '2186318912', '213217132712', 'ABC123', NULL, 'proveedor', 'VIS-20251124-9B5BDA17', '2025-11-24 19:34:29', '2025-11-24 23:26:00', '2025-11-24 19:34:29', NULL, 1, NULL, 'active', '', '2025-11-24 19:28:38', '2025-11-24 19:34:29'),
(4, 1, 'Danonino', '', '4444444444', 'ABC123X', NULL, 'personal', 'VIS-20251125-97586137', '2025-11-25 17:13:01', '2025-11-25 21:05:00', '2025-11-25 17:13:01', NULL, 1, NULL, 'active', '', '2025-11-25 17:06:07', '2025-11-25 17:13:01'),
(5, 2, 'dszzxfgcvhjbkn', '', '4444444444', 'QRO-456-B', 'uploads/id_photos/id_photo_5_1764092129.png', 'personal', 'VIS-20251125-C87E9B8C', '2025-11-25 17:35:34', '2025-11-25 21:32:00', '2025-11-25 17:35:34', NULL, 1, NULL, 'active', '', '2025-11-25 17:32:18', '2025-11-25 17:35:34'),
(6, 3, 'Danonino', '', '2222222222', 'QRO-789-C', 'uploads/id_photos/id_photo_6_1764176957.png', 'personal', 'VIS-20251126-8F0E4564', '2025-11-26 17:09:19', '2025-11-26 21:08:00', '2025-11-26 17:09:19', NULL, 1, NULL, 'active', '', '2025-11-26 17:08:14', '2025-11-26 17:09:19'),
(7, 5, 'Dan Raso', '7821533781', '8721581358', 'RSV-205-BV', 'uploads/id_photos/id_photo_7_1764178931.png', 'otro', 'VIS-20251126-DA1A704A', '2025-11-26 17:44:48', '2025-11-26 21:37:00', '2025-11-26 17:42:15', '2025-11-26 17:44:48', 1, 1, 'completed', '', '2025-11-26 17:38:28', '2025-11-26 17:44:48'),
(8, 2, 'Dan', '1097309127', '2309709179', 'QRO-456-B', NULL, 'personal', 'VIS-20251127-6EF92E16', '2025-11-27 09:41:00', '2025-11-27 13:41:00', NULL, NULL, NULL, NULL, 'pending', '', '2025-11-27 09:41:15', '2025-11-27 09:41:15');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_active_residents`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_active_residents` (
`resident_id` int(11)
,`user_id` int(11)
,`property_id` int(11)
,`relationship` enum('propietario','inquilino','familiar')
,`is_primary` tinyint(1)
,`username` varchar(50)
,`email` varchar(100)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`phone` varchar(20)
,`user_status` enum('active','inactive','blocked','pending','deleted')
,`property_number` varchar(20)
,`section` varchar(50)
,`tower` varchar(50)
,`property_status` enum('ocupada','desocupada','en_construccion')
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_reservation_calendar`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_reservation_calendar` (
`reservation_id` int(11)
,`amenity_id` int(11)
,`resident_id` int(11)
,`reservation_date` date
,`start_time` time
,`end_time` time
,`guests_count` int(11)
,`amount` decimal(10,2)
,`payment_status` enum('pending','paid','cancelled')
,`status` enum('pending','confirmed','completed','cancelled','no_show')
,`amenity_name` varchar(100)
,`amenity_type` enum('salon','alberca','asadores','cancha','gimnasio','otro')
,`property_id` int(11)
,`property_number` varchar(20)
,`section` varchar(50)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`email` varchar(100)
,`phone` varchar(20)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `property_debt_summary`
--
DROP TABLE IF EXISTS `property_debt_summary`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `property_debt_summary`  AS SELECT `p`.`id` AS `property_id`, `p`.`property_number` AS `property_number`, `p`.`section` AS `section`, `r`.`id` AS `resident_id`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `resident_name`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, count((case when (`mf`.`status` in ('pending','overdue')) then 1 end)) AS `pending_payments_count`, sum((case when (`mf`.`status` in ('pending','overdue')) then `mf`.`amount` else 0 end)) AS `total_debt`, sum((case when (`mf`.`status` = 'paid') then `mf`.`amount` else 0 end)) AS `total_paid`, max((case when (`mf`.`status` in ('pending','overdue')) then `mf`.`due_date` end)) AS `oldest_due_date` FROM (((`properties` `p` left join `residents` `r` on(((`r`.`property_id` = `p`.`id`) and (`r`.`is_primary` = 1) and (`r`.`status` = 'active')))) left join `users` `u` on((`r`.`user_id` = `u`.`id`))) left join `maintenance_fees` `mf` on((`mf`.`property_id` = `p`.`id`))) GROUP BY `p`.`id`, `p`.`property_number`, `p`.`section`, `r`.`id`, `u`.`first_name`, `u`.`last_name`, `u`.`email`, `u`.`phone` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `resident_dashboard_stats`
--
DROP TABLE IF EXISTS `resident_dashboard_stats`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `resident_dashboard_stats`  AS SELECT `r`.`id` AS `resident_id`, `u`.`id` AS `user_id`, count(distinct `v`.`id`) AS `total_visits`, count(distinct `res`.`id`) AS `total_reservations`, count(distinct `mr`.`id`) AS `total_maintenance_reports`, coalesce(`rb`.`balance`,0) AS `current_balance`, count(distinct (case when (`mf`.`status` = 'pending') then `mf`.`id` end)) AS `pending_payments` FROM (((((((`residents` `r` join `users` `u` on((`r`.`user_id` = `u`.`id`))) left join `visits` `v` on((`r`.`id` = `v`.`resident_id`))) left join `reservations` `res` on((`r`.`id` = `res`.`resident_id`))) left join `maintenance_reports` `mr` on((`r`.`id` = `mr`.`resident_id`))) left join `resident_balances` `rb` on((`r`.`id` = `rb`.`resident_id`))) left join `properties` `p` on((`r`.`property_id` = `p`.`id`))) left join `maintenance_fees` `mf` on(((`p`.`id` = `mf`.`property_id`) and (`mf`.`status` = 'pending')))) GROUP BY `r`.`id`, `u`.`id`, `rb`.`balance` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `resident_debt_summary`
--
DROP TABLE IF EXISTS `resident_debt_summary`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `resident_debt_summary`  AS SELECT `r`.`id` AS `resident_id`, `u`.`id` AS `user_id`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `resident_name`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, `p`.`property_number` AS `property_number`, count(`mf`.`id`) AS `total_fees`, sum((case when (`mf`.`status` = 'pending') then `mf`.`amount` else 0 end)) AS `pending_amount`, sum((case when (`mf`.`status` = 'overdue') then `mf`.`amount` else 0 end)) AS `overdue_amount`, sum((case when (`mf`.`status` = 'paid') then `mf`.`amount` else 0 end)) AS `paid_amount`, min((case when (`mf`.`status` in ('pending','overdue')) then `mf`.`due_date` end)) AS `oldest_due_date` FROM (((`residents` `r` join `users` `u` on((`r`.`user_id` = `u`.`id`))) join `properties` `p` on((`r`.`property_id` = `p`.`id`))) left join `maintenance_fees` `mf` on((`mf`.`property_id` = `p`.`id`))) WHERE (`r`.`status` = 'active') GROUP BY `r`.`id`, `u`.`id`, `u`.`first_name`, `u`.`last_name`, `u`.`email`, `u`.`phone`, `p`.`property_number` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `resident_payment_history`
--
DROP TABLE IF EXISTS `resident_payment_history`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `resident_payment_history`  AS SELECT `r`.`id` AS `resident_id`, `u`.`id` AS `user_id`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `resident_name`, `p`.`property_number` AS `property_number`, `mf`.`id` AS `fee_id`, `mf`.`period` AS `period`, `mf`.`amount` AS `amount`, `mf`.`due_date` AS `due_date`, `mf`.`paid_date` AS `paid_date`, `mf`.`status` AS `payment_status`, `mf`.`payment_method` AS `payment_method`, `fm`.`id` AS `financial_movement_id`, `fm`.`transaction_date` AS `transaction_date` FROM ((((`residents` `r` join `users` `u` on((`r`.`user_id` = `u`.`id`))) join `properties` `p` on((`r`.`property_id` = `p`.`id`))) left join `maintenance_fees` `mf` on((`mf`.`property_id` = `p`.`id`))) left join `financial_movements` `fm` on(((`fm`.`reference_type` = 'maintenance_fee') and (`fm`.`reference_id` = `mf`.`id`)))) WHERE (`r`.`status` = 'active') ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_active_residents`
--
DROP TABLE IF EXISTS `v_active_residents`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_active_residents`  AS SELECT `r`.`id` AS `resident_id`, `r`.`user_id` AS `user_id`, `r`.`property_id` AS `property_id`, `r`.`relationship` AS `relationship`, `r`.`is_primary` AS `is_primary`, `u`.`username` AS `username`, `u`.`email` AS `email`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `u`.`phone` AS `phone`, `u`.`status` AS `user_status`, `p`.`property_number` AS `property_number`, `p`.`section` AS `section`, `p`.`tower` AS `tower`, `p`.`status` AS `property_status` FROM ((`residents` `r` join `users` `u` on((`r`.`user_id` = `u`.`id`))) join `properties` `p` on((`r`.`property_id` = `p`.`id`))) WHERE ((`r`.`status` = 'active') AND (`u`.`status` = 'active')) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_reservation_calendar`
--
DROP TABLE IF EXISTS `v_reservation_calendar`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `v_reservation_calendar`  AS SELECT `r`.`id` AS `reservation_id`, `r`.`amenity_id` AS `amenity_id`, `r`.`resident_id` AS `resident_id`, `r`.`reservation_date` AS `reservation_date`, `r`.`start_time` AS `start_time`, `r`.`end_time` AS `end_time`, `r`.`guests_count` AS `guests_count`, `r`.`amount` AS `amount`, `r`.`payment_status` AS `payment_status`, `r`.`status` AS `status`, `a`.`name` AS `amenity_name`, `a`.`amenity_type` AS `amenity_type`, `res`.`property_id` AS `property_id`, `p`.`property_number` AS `property_number`, `p`.`section` AS `section`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `u`.`email` AS `email`, `u`.`phone` AS `phone` FROM ((((`reservations` `r` join `amenities` `a` on((`r`.`amenity_id` = `a`.`id`))) join `residents` `res` on((`r`.`resident_id` = `res`.`id`))) join `users` `u` on((`res`.`user_id` = `u`.`id`))) join `properties` `p` on((`res`.`property_id` = `p`.`id`))) WHERE (`r`.`status` <> 'cancelled') ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `access_devices`
--
ALTER TABLE `access_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`),
  ADD KEY `idx_device_type` (`device_type`),
  ADD KEY `idx_device_id` (`device_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guard_id` (`guard_id`),
  ADD KEY `idx_log_type` (`log_type`),
  ADD KEY `idx_access_type` (`access_type`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_property_id` (`property_id`),
  ADD KEY `idx_timestamp_type` (`timestamp`,`log_type`);

--
-- Indices de la tabla `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_amenity_type` (`amenity_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`);

--
-- Indices de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_table_name` (`table_name`);

--
-- Indices de la tabla `detected_plates`
--
ALTER TABLE `detected_plates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_plate_text` (`plate_text`),
  ADD KEY `idx_captured_at` (`captured_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_match` (`is_match`),
  ADD KEY `idx_matched_vehicle_id` (`matched_vehicle_id`);

--
-- Indices de la tabla `device_action_logs`
--
ALTER TABLE `device_action_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_device_id` (`device_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `action_by` (`action_by`);

--
-- Indices de la tabla `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_verified` (`verified`);

--
-- Indices de la tabla `financial_movements`
--
ALTER TABLE `financial_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movement_type_id` (`movement_type_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_property_id` (`property_id`),
  ADD KEY `idx_resident_id` (`resident_id`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_transaction_date_type` (`transaction_date`,`transaction_type`),
  ADD KEY `idx_financial_movements_date_type` (`transaction_date`,`transaction_type`);

--
-- Indices de la tabla `financial_movement_types`
--
ALTER TABLE `financial_movement_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indices de la tabla `maintenance_comments`
--
ALTER TABLE `maintenance_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_report_id` (`report_id`);

--
-- Indices de la tabla `maintenance_fees`
--
ALTER TABLE `maintenance_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_property_id` (`property_id`),
  ADD KEY `idx_period` (`period`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_status_due_date` (`status`,`due_date`),
  ADD KEY `idx_maintenance_fees_status_due` (`status`,`due_date`),
  ADD KEY `idx_maintenance_fees_property` (`property_id`),
  ADD KEY `idx_maintenance_fees_status` (`status`),
  ADD KEY `idx_maintenance_fees_due_date` (`due_date`);

--
-- Indices de la tabla `maintenance_reports`
--
ALTER TABLE `maintenance_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_resident_id` (`resident_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_status_priority` (`status`,`priority`);

--
-- Indices de la tabla `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `membership_plan_id` (`membership_plan_id`),
  ADD KEY `idx_resident_id` (`resident_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indices de la tabla `membership_payments`
--
ALTER TABLE `membership_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financial_movement_id` (`financial_movement_id`),
  ADD KEY `idx_membership_id` (`membership_id`),
  ADD KEY `idx_period` (`period`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indices de la tabla `membership_plans`
--
ALTER TABLE `membership_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_user_used` (`user_id`,`used`);

--
-- Indices de la tabla `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `fee_id` (`fee_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indices de la tabla `penalties`
--
ALTER TABLE `penalties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `idx_resident_id` (`resident_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `pending_validations`
--
ALTER TABLE `pending_validations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email_verified` (`email_verified`);

--
-- Indices de la tabla `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `property_number` (`property_number`),
  ADD KEY `idx_property_number` (`property_number`),
  ADD KEY `idx_section` (`section`),
  ADD KEY `idx_property_status` (`status`),
  ADD KEY `idx_property_type` (`property_type`),
  ADD KEY `idx_subdivision_id` (`subdivision_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_amenity_id` (`amenity_id`),
  ADD KEY `idx_resident_id` (`resident_id`),
  ADD KEY `idx_reservation_date` (`reservation_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_status` (`reservation_date`,`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_reservation_date_status` (`reservation_date`,`status`);

--
-- Indices de la tabla `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_property_id` (`property_id`),
  ADD KEY `idx_relationship` (`relationship`),
  ADD KEY `idx_residents_subdivision_id` (`subdivision_id`),
  ADD KEY `idx_status_relationship` (`status`,`relationship`),
  ADD KEY `idx_residents_status_primary` (`status`,`is_primary`),
  ADD KEY `idx_residents_user_id` (`user_id`),
  ADD KEY `idx_residents_property_id` (`property_id`),
  ADD KEY `idx_residents_status` (`status`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `resident_access_passes`
--
ALTER TABLE `resident_access_passes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`),
  ADD KEY `idx_resident_id` (`resident_id`),
  ADD KEY `idx_qr_code` (`qr_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_valid_dates` (`valid_from`,`valid_until`);

--
-- Indices de la tabla `resident_balances`
--
ALTER TABLE `resident_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_resident` (`resident_id`);

--
-- Indices de la tabla `security_alerts`
--
ALTER TABLE `security_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `idx_alert_type` (`alert_type`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `security_patrols`
--
ALTER TABLE `security_patrols`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_guard_id` (`guard_id`),
  ADD KEY `idx_patrol_start` (`patrol_start`);

--
-- Indices de la tabla `subdivisions`
--
ALTER TABLE `subdivisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indices de la tabla `system_optimization`
--
ALTER TABLE `system_optimization`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `optimization_key` (`optimization_key`);

--
-- Indices de la tabla `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_house_number` (`house_number`),
  ADD KEY `idx_email_verification_token` (`email_verification_token`),
  ADD KEY `idx_users_subdivision_id` (`subdivision_id`),
  ADD KEY `idx_role_status` (`role`,`status`),
  ADD KEY `idx_users_role_status` (`role`,`status`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_first_name` (`first_name`),
  ADD KEY `idx_last_name` (`last_name`),
  ADD KEY `idx_phone` (`phone`);

--
-- Indices de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate` (`plate`),
  ADD KEY `idx_plate` (`plate`),
  ADD KEY `idx_resident_id` (`resident_id`),
  ADD KEY `idx_vehicles_subdivision_id` (`subdivision_id`);

--
-- Indices de la tabla `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`),
  ADD KEY `guard_entry_id` (`guard_entry_id`),
  ADD KEY `guard_exit_id` (`guard_exit_id`),
  ADD KEY `idx_resident_id` (`resident_id`),
  ADD KEY `idx_qr_code` (`qr_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_entry_time` (`entry_time`),
  ADD KEY `idx_valid_dates` (`valid_from`,`valid_until`),
  ADD KEY `idx_visits_created_date` (`created_at`),
  ADD KEY `idx_identification_photo` (`identification_photo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `access_devices`
--
ALTER TABLE `access_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT de la tabla `detected_plates`
--
ALTER TABLE `detected_plates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `device_action_logs`
--
ALTER TABLE `device_action_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `financial_movements`
--
ALTER TABLE `financial_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `financial_movement_types`
--
ALTER TABLE `financial_movement_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `maintenance_comments`
--
ALTER TABLE `maintenance_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `maintenance_fees`
--
ALTER TABLE `maintenance_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `maintenance_reports`
--
ALTER TABLE `maintenance_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `memberships`
--
ALTER TABLE `memberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `membership_payments`
--
ALTER TABLE `membership_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `membership_plans`
--
ALTER TABLE `membership_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `payment_reminders`
--
ALTER TABLE `payment_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `penalties`
--
ALTER TABLE `penalties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pending_validations`
--
ALTER TABLE `pending_validations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `residents`
--
ALTER TABLE `residents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `resident_access_passes`
--
ALTER TABLE `resident_access_passes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resident_balances`
--
ALTER TABLE `resident_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `security_alerts`
--
ALTER TABLE `security_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `security_patrols`
--
ALTER TABLE `security_patrols`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `subdivisions`
--
ALTER TABLE `subdivisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `system_optimization`
--
ALTER TABLE `system_optimization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=428;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `access_devices`
--
ALTER TABLE `access_devices`
  ADD CONSTRAINT `access_devices_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `access_logs`
--
ALTER TABLE `access_logs`
  ADD CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `access_logs_ibfk_2` FOREIGN KEY (`guard_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detected_plates`
--
ALTER TABLE `detected_plates`
  ADD CONSTRAINT `detected_plates_ibfk_1` FOREIGN KEY (`matched_vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `device_action_logs`
--
ALTER TABLE `device_action_logs`
  ADD CONSTRAINT `device_action_logs_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `access_devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `device_action_logs_ibfk_2` FOREIGN KEY (`action_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `financial_movements`
--
ALTER TABLE `financial_movements`
  ADD CONSTRAINT `financial_movements_ibfk_1` FOREIGN KEY (`movement_type_id`) REFERENCES `financial_movement_types` (`id`),
  ADD CONSTRAINT `financial_movements_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_movements_ibfk_3` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_movements_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `maintenance_comments`
--
ALTER TABLE `maintenance_comments`
  ADD CONSTRAINT `maintenance_comments_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `maintenance_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `maintenance_fees`
--
ALTER TABLE `maintenance_fees`
  ADD CONSTRAINT `maintenance_fees_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `maintenance_reports`
--
ALTER TABLE `maintenance_reports`
  ADD CONSTRAINT `maintenance_reports_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_reports_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maintenance_reports_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `memberships`
--
ALTER TABLE `memberships`
  ADD CONSTRAINT `memberships_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `memberships_ibfk_2` FOREIGN KEY (`membership_plan_id`) REFERENCES `membership_plans` (`id`);

--
-- Filtros para la tabla `membership_payments`
--
ALTER TABLE `membership_payments`
  ADD CONSTRAINT `membership_payments_ibfk_1` FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `membership_payments_ibfk_2` FOREIGN KEY (`financial_movement_id`) REFERENCES `financial_movements` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD CONSTRAINT `payment_reminders_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_reminders_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `maintenance_fees` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `penalties`
--
ALTER TABLE `penalties`
  ADD CONSTRAINT `penalties_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penalties_ibfk_2` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pending_validations`
--
ALTER TABLE `pending_validations`
  ADD CONSTRAINT `pending_validations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pending_validations_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pending_validations_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `fk_properties_subdivision` FOREIGN KEY (`subdivision_id`) REFERENCES `subdivisions` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `fk_residents_subdivision` FOREIGN KEY (`subdivision_id`) REFERENCES `subdivisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `residents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `residents_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resident_access_passes`
--
ALTER TABLE `resident_access_passes`
  ADD CONSTRAINT `resident_access_passes_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resident_balances`
--
ALTER TABLE `resident_balances`
  ADD CONSTRAINT `resident_balances_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `security_alerts`
--
ALTER TABLE `security_alerts`
  ADD CONSTRAINT `security_alerts_ibfk_1` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `security_alerts_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `security_patrols`
--
ALTER TABLE `security_patrols`
  ADD CONSTRAINT `security_patrols_ibfk_1` FOREIGN KEY (`guard_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_subdivision` FOREIGN KEY (`subdivision_id`) REFERENCES `subdivisions` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_subdivision` FOREIGN KEY (`subdivision_id`) REFERENCES `subdivisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `visits`
--
ALTER TABLE `visits`
  ADD CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `visits_ibfk_2` FOREIGN KEY (`guard_entry_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visits_ibfk_3` FOREIGN KEY (`guard_exit_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

DELIMITER $$
--
-- Eventos
--
CREATE EVENT `daily_payment_reminders` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-24 09:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL SendPaymentReminders()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
