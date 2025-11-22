<?php
/**
 * Punto de entrada principal del sistema
 */

// Configurar sesión antes de iniciarla (debe hacerse ANTES de session_start)
ini_set('session.cookie_httponly', 1); // Previene acceso a cookies vía JavaScript (protección XSS)
ini_set('session.use_only_cookies', 1); // Usa solo cookies, no URL params (previene session fixation)
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0); // Solo HTTPS si está disponible

// Iniciar sesión
session_start();

// Cargar configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Cargar clases del core
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Router.php';

// Inicializar el router
$router = new Router();
