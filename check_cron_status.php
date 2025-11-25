<?php
/**
 * Script para verificar el estado del procesamiento de placas
 * Acceso: https://janetzy.shop/check_cron_status.php?key=status2025
 */

// Autenticación simple
if (!isset($_GET['key']) || $_GET['key'] !== 'status2025') {
    die('Acceso denegado');
}

// Configuración
define('FTP_SOURCE_DIR', '/home2/janetzy/placas/IP CAMERA/01');
define('PUBLIC_DEST_DIR', '/home2/janetzy/public_html/placas');
define('LOG_FILE', '/home2/janetzy/public_html/logs/plate_processing.log');
define('CRON_SCRIPT', '/home2/janetzy/public_html/residencial/cron/process_plate_images.php');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Cron Job - Procesamiento de Placas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
        }
        h1 { 
            color: #2d3748; 
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .status-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .status-card.success { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
        .status-card.warning { background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); }
        .status-card.error { background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); }
        .status-card h3 {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .status-card .value {
            font-size: 32px;
            font-weight: bold;
        }
        .section {
            background: #f7fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section h2 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .icon {
            width: 24px;
            height: 24px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #edf2f7;
            font-weight: 600;
            color: #2d3748;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge.success { background: #c6f6d5; color: #22543d; }
        .badge.error { background: #fed7d7; color: #742a2a; }
        .badge.warning { background: #feebc8; color: #7c2d12; }
        .log-content {
            background: #1a202c;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-right: 10px;
            margin-top: 10px;
        }
        .btn:hover { background: #5a67d8; }
        .btn.danger { background: #f56565; }
        .btn.danger:hover { background: #e53e3e; }
        .timestamp { 
            color: #718096; 
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Estado del Cron Job - Procesamiento de Placas</h1>
        <p class="subtitle">Monitoreo en tiempo real del sistema de reconocimiento de placas</p>

        <div class="status-grid">
            <?php
            // Verificar directorio FTP
            $ftpExists = is_dir(FTP_SOURCE_DIR);
            $ftpImages = $ftpExists ? count(glob(FTP_SOURCE_DIR . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE)) : 0;
            ?>
            <div class="status-card <?php echo $ftpImages > 0 ? 'warning' : 'success'; ?>">
                <h3>📁 Imágenes Pendientes</h3>
                <div class="value"><?php echo $ftpImages; ?></div>
            </div>

            <?php
            // Verificar directorio público
            $publicExists = is_dir(PUBLIC_DEST_DIR);
            $publicImages = $publicExists ? count(glob(PUBLIC_DEST_DIR . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE)) : 0;
            ?>
            <div class="status-card success">
                <h3>Imágenes Procesadas</h3>
                <div class="value"><?php echo $publicImages; ?></div>
            </div>

            <?php
            // Verificar log
            $logExists = file_exists(LOG_FILE);
            $lastRun = $logExists ? date('H:i:s', filemtime(LOG_FILE)) : 'Nunca';
            $minutesSinceRun = $logExists ? round((time() - filemtime(LOG_FILE)) / 60) : 999;
            ?>
            <div class="status-card <?php echo $minutesSinceRun <= 2 ? 'success' : 'error'; ?>">
                <h3>⏱️ Última Ejecución</h3>
                <div class="value" style="font-size: 20px;"><?php echo $lastRun; ?></div>
            </div>

            <?php
            // Verificar script
            $scriptExists = file_exists(CRON_SCRIPT);
            ?>
            <div class="status-card <?php echo $scriptExists ? 'success' : 'error'; ?>">
                <h3>📜 Script Cron</h3>
                <div class="value" style="font-size: 20px;"><?php echo $scriptExists ? 'OK' : 'ERROR'; ?></div>
            </div>
        </div>

        <!-- Directorios -->
        <div class="section">
            <h2>📂 Estado de Directorios</h2>
            <table>
                <thead>
                    <tr>
                        <th>Directorio</th>
                        <th>Estado</th>
                        <th>Archivos</th>
                        <th>Ruta</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>FTP Origen</strong></td>
                        <td><?php echo $ftpExists ? '<span class="badge success">Existe</span>' : '<span class="badge error">No existe</span>'; ?></td>
                        <td><?php echo $ftpImages; ?> imágenes</td>
                        <td style="font-size: 11px; color: #718096;"><?php echo FTP_SOURCE_DIR; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Público Destino</strong></td>
                        <td><?php echo $publicExists ? '<span class="badge success">Existe</span>' : '<span class="badge error">No existe</span>'; ?></td>
                        <td><?php echo $publicImages; ?> imágenes</td>
                        <td style="font-size: 11px; color: #718096;"><?php echo PUBLIC_DEST_DIR; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Configuración del Cron -->
        <div class="section">
            <h2>⚙️ Configuración del Cron</h2>
            <p style="margin-bottom: 10px;"><strong>Comando correcto:</strong></p>
            <div class="log-content" style="max-height: auto;">
/usr/bin/php <?php echo CRON_SCRIPT; ?>
            </div>
            <p style="margin-top: 10px; color: #718096; font-size: 13px;">
                ⏰ Frecuencia recomendada: <strong>Cada 1 minuto</strong><br>
                📍 Configurar en: <strong>cPanel → Cron Jobs</strong>
            </p>
        </div>

        <!-- Últimas imágenes en FTP -->
        <?php if ($ftpImages > 0): ?>
        <div class="section">
            <h2>📸 Últimas Imágenes en FTP (Pendientes)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Tamaño</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $images = glob(FTP_SOURCE_DIR . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
                    $images = array_slice($images, 0, 10); // Solo las últimas 10
                    foreach ($images as $img):
                        $size = filesize($img);
                        $date = date('Y-m-d H:i:s', filemtime($img));
                    ?>
                    <tr>
                        <td><?php echo basename($img); ?></td>
                        <td><?php echo round($size / 1024, 2); ?> KB</td>
                        <td><?php echo $date; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Log del cron -->
        <div class="section">
            <h2>📋 Últimas Líneas del Log</h2>
            <?php if ($logExists): ?>
                <div class="log-content">
<?php 
$logContent = file_get_contents(LOG_FILE);
$lines = explode("\n", $logContent);
$lastLines = array_slice($lines, -50); // Últimas 50 líneas
echo htmlspecialchars(implode("\n", $lastLines));
?>
                </div>
            <?php else: ?>
                <p style="color: #e53e3e;">⚠️ No se encontró el archivo de log</p>
            <?php endif; ?>
        </div>

        <!-- Acciones -->
        <div class="section">
            <h2>🔧 Acciones Rápidas</h2>
            <a href="?key=status2025&action=run" class="btn">▶️ Ejecutar Manualmente</a>
            <a href="?key=status2025&action=clear_log" class="btn danger">🗑️ Limpiar Log</a>
            <a href="?key=status2025" class="btn">🔄 Actualizar</a>
        </div>

        <?php
        // Procesar acciones
        if (isset($_GET['action'])) {
            if ($_GET['action'] === 'run') {
                echo '<div class="section" style="background: #bee3f8; color: #2c5282;">';
                echo '<h2>⚡ Ejecutando script manualmente...</h2>';
                echo '<div class="log-content" style="background: white; color: #2d3748;">';
                
                // Verificar que el script existe
                if (!file_exists(CRON_SCRIPT)) {
                    echo "❌ ERROR: El script no existe en: " . CRON_SCRIPT;
                } else {
                    // Ejecutar el script
                    $command = '/usr/bin/php ' . CRON_SCRIPT . ' 2>&1';
                    echo "Ejecutando: $command\n\n";
                    echo "--- INICIO DE SALIDA ---\n";
                    $output = shell_exec($command);
                    echo htmlspecialchars($output);
                    echo "\n--- FIN DE SALIDA ---\n";
                }
                
                echo '</div>';
                echo '<a href="?key=status2025" class="btn" style="margin-top: 10px;">🔄 Ver resultados actualizados</a>';
                echo '</div>';
            }
            
            if ($_GET['action'] === 'clear_log' && $logExists) {
                file_put_contents(LOG_FILE, '');
                echo '<div class="section" style="background: #c6f6d5; color: #22543d;">';
                echo '<p>✅ Log limpiado exitosamente</p>';
                echo '</div>';
            }
        }
        ?>

        <p class="timestamp">
            🕐 Actualizado: <?php echo date('Y-m-d H:i:s'); ?> | 
            <a href="?key=status2025" style="color: #667eea;">Recargar</a>
        </p>
    </div>
</body>
</html>
