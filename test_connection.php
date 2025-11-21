<?php
/**
 * Archivo de prueba de conexión y confirmación de URL Base
 */

require_once __DIR__ . '/config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - ERP Residencial</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">🏘️ Test de Conexión - ERP Residencial</h1>
            
            <!-- Test de URL Base -->
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                <h2 class="text-xl font-semibold text-blue-800 mb-2">✅ URL Base Detectada</h2>
                <p class="text-gray-700"><strong>Base URL:</strong> <?php echo BASE_URL; ?></p>
                <p class="text-gray-700"><strong>Public URL:</strong> <?php echo PUBLIC_URL; ?></p>
                <p class="text-gray-700"><strong>Root Path:</strong> <?php echo ROOT_PATH; ?></p>
            </div>

            <!-- Test de Configuración -->
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                <h2 class="text-xl font-semibold text-green-800 mb-2">✅ Configuración del Sistema</h2>
                <p class="text-gray-700"><strong>Nombre del Sitio:</strong> <?php echo SITE_NAME; ?></p>
                <p class="text-gray-700"><strong>Email:</strong> <?php echo SITE_EMAIL; ?></p>
                <p class="text-gray-700"><strong>Teléfono:</strong> <?php echo SITE_PHONE; ?></p>
                <p class="text-gray-700"><strong>Zona Horaria:</strong> <?php echo date_default_timezone_get(); ?></p>
            </div>

            <!-- Test de Base de Datos -->
            <div class="mb-6 p-4 <?php 
                try {
                    require_once __DIR__ . '/config/database.php';
                    $db = Database::getInstance();
                    $conn = $db->getConnection();
                    echo 'bg-green-50 border-l-4 border-green-500';
                    $dbStatus = 'success';
                } catch (Exception $e) {
                    echo 'bg-red-50 border-l-4 border-red-500';
                    $dbStatus = 'error';
                    $dbError = $e->getMessage();
                }
            ?> rounded">
                <h2 class="text-xl font-semibold <?php echo $dbStatus === 'success' ? 'text-green-800' : 'text-red-800'; ?> mb-2">
                    <?php echo $dbStatus === 'success' ? '✅' : '❌'; ?> Conexión a Base de Datos
                </h2>
                <?php if ($dbStatus === 'success'): ?>
                    <p class="text-gray-700"><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                    <p class="text-gray-700"><strong>Base de Datos:</strong> <?php echo DB_NAME; ?></p>
                    <p class="text-gray-700"><strong>Usuario:</strong> <?php echo DB_USER; ?></p>
                    <p class="text-green-600 font-semibold mt-2">¡Conexión exitosa!</p>
                <?php else: ?>
                    <p class="text-red-600"><strong>Error:</strong> <?php echo $dbError; ?></p>
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Nota:</strong> Asegúrate de crear la base de datos ejecutando el archivo SQL en database/schema.sql
                    </p>
                <?php endif; ?>
            </div>

            <!-- Test de Directorios -->
            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                <h2 class="text-xl font-semibold text-yellow-800 mb-2">📁 Directorios del Sistema</h2>
                <?php
                $directories = [
                    'App Path' => APP_PATH,
                    'Public Path' => PUBLIC_PATH,
                    'Upload Path' => UPLOAD_PATH,
                    'Models' => APP_PATH . '/models',
                    'Controllers' => APP_PATH . '/controllers',
                    'Views' => APP_PATH . '/views',
                ];
                
                foreach ($directories as $name => $path):
                    $exists = is_dir($path);
                    $writable = is_writable($path);
                ?>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-gray-700"><?php echo $name; ?>:</span>
                        <span class="<?php echo $exists ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $exists ? '✓ Existe' : '✗ No existe'; ?>
                            <?php if ($exists && !$writable) echo ' (No escribible)'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Test de PHP -->
            <div class="mb-6 p-4 bg-purple-50 border-l-4 border-purple-500 rounded">
                <h2 class="text-xl font-semibold text-purple-800 mb-2">🐘 Información de PHP</h2>
                <p class="text-gray-700"><strong>Versión de PHP:</strong> <?php echo phpversion(); ?></p>
                <p class="text-gray-700"><strong>PDO MySQL:</strong> <?php echo extension_loaded('pdo_mysql') ? '✓ Disponible' : '✗ No disponible'; ?></p>
                <p class="text-gray-700"><strong>GD Library:</strong> <?php echo extension_loaded('gd') ? '✓ Disponible' : '✗ No disponible'; ?></p>
                <p class="text-gray-700"><strong>Session:</strong> <?php echo extension_loaded('session') ? '✓ Disponible' : '✗ No disponible'; ?></p>
            </div>

            <div class="mt-6 text-center">
                <a href="<?php echo BASE_URL; ?>/public/index.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Ir al Sistema →
                </a>
            </div>
        </div>
    </div>
</body>
</html>
