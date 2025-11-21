<?php
/**
 * Configuración principal del sistema
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'erp_residencial');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Detectar URL base automáticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$script = str_replace('/public', '', $script);
define('BASE_URL', $protocol . '://' . $host . $script);
define('PUBLIC_URL', BASE_URL . '/public');

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);

// Configuración de errores (cambiar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración del sistema
define('SITE_NAME', 'ERP Residencial');
define('SITE_EMAIL', 'admin@residencial.com');
define('SITE_PHONE', '+52 442 123 4567');

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Configuración de PayPal (modo sandbox)
define('PAYPAL_MODE', 'sandbox');
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_SECRET', '');

// Configuración de API QR
define('QR_API_ENABLED', true);
define('QR_LIBRARY', 'phpqrcode'); // Usaremos biblioteca PHP local

// Configuración de correo (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', SITE_EMAIL);
define('SMTP_FROM_NAME', SITE_NAME);

// Horarios de atención
define('SERVICE_HOURS', [
    'Lunes a Viernes' => '8:00 AM - 6:00 PM',
    'Sábados' => '9:00 AM - 2:00 PM',
    'Domingos' => 'Cerrado'
]);

// Colores del tema (Tailwind CSS)
define('THEME_COLORS', [
    'primary' => 'blue',
    'secondary' => 'gray',
    'accent' => 'green',
    'danger' => 'red'
]);
