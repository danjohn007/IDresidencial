<?php
/**
 * Punto de entrada principal del sistema
 */

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
