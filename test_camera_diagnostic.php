<?php
/**
 * Script de diagnóstico completo para cámara HikVision
 * Acceso: https://janetzy.shop/residencial/test_camera_diagnostic.php?key=diag2025
 */

if (!isset($_GET['key']) || $_GET['key'] !== 'diag2025') {
    die('Acceso denegado');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Cámara HikVision</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #00ff00;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #0a0a0a;
            border: 2px solid #00ff00;
            border-radius: 10px;
            padding: 20px;
        }
        h1 { color: #00ff00; margin-bottom: 20px; text-align: center; }
        h2 { 
            color: #00ff00; 
            margin: 20px 0 10px 0; 
            padding: 10px;
            background: #1a4d1a;
            border-left: 4px solid #00ff00;
        }
        .section {
            background: #0f0f0f;
            border: 1px solid #333;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        .info { color: #00aaff; }
        pre {
            background: #000;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 3px solid #00ff00;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        th { background: #1a4d1a; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #00ff00;
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 5px;
        }
        .btn:hover { background: #00cc00; }
        .code-box {
            background: #000;
            color: #00ff00;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 12px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DIAGNÓSTICO COMPLETO - CÁMARA HIKVISION</h1>
        
        <?php
        // Configuración
        $ftpDir = '/home2/janetzy/placas/IP CAMERA/01';
        $publicDir = '/home2/janetzy/public_html/placas';
        $receiverScript = '/home2/janetzy/public_html/residencial/receive_plate.php';
        $receiverLog = '/home2/janetzy/public_html/logs/camera_receiver.log';
        $cronLog = '/home2/janetzy/public_html/logs/plate_processing.log';
        
        echo "<h2>📂 1. VERIFICACIÓN DE DIRECTORIOS</h2>";
        echo "<div class='section'>";
        
        $dirs = [
            'FTP Origen' => $ftpDir,
            'Público Destino' => $publicDir,
            'Logs' => dirname($receiverLog)
        ];
        
        echo "<table>";
        echo "<tr><th>Directorio</th><th>Ruta</th><th>Existe</th><th>Escribible</th><th>Archivos</th></tr>";
        
        foreach ($dirs as $name => $path) {
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);
            $files = $exists ? count(scandir($path)) - 2 : 0;
            
            echo "<tr>";
            echo "<td><strong>$name</strong></td>";
            echo "<td style='font-size:10px;'>$path</td>";
            echo "<td class='" . ($exists ? 'success' : 'error') . "'>" . ($exists ? '✓ SÍ' : '✗ NO') . "</td>";
            echo "<td class='" . ($writable ? 'success' : 'error') . "'>" . ($writable ? '✓ SÍ' : '✗ NO') . "</td>";
            echo "<td>$files archivos</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
        // Imágenes en FTP
        echo "<h2>📸 2. IMÁGENES EN FTP (últimas detecciones)</h2>";
        echo "<div class='section'>";
        
        if (is_dir($ftpDir)) {
            $images = glob($ftpDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
            if (count($images) > 0) {
                echo "<p class='warning'>⚠️ Hay " . count($images) . " imagen(es) pendiente(s) de procesar</p>";
                echo "<table>";
                echo "<tr><th>Archivo</th><th>Tamaño</th><th>Fecha</th></tr>";
                foreach (array_slice($images, 0, 5) as $img) {
                    echo "<tr>";
                    echo "<td>" . basename($img) . "</td>";
                    echo "<td>" . round(filesize($img)/1024, 2) . " KB</td>";
                    echo "<td>" . date('Y-m-d H:i:s', filemtime($img)) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='success'>✓ No hay imágenes pendientes en FTP</p>";
            }
        }
        echo "</div>";
        
        // Script receptor
        echo "<h2>📜 3. SCRIPT RECEPTOR</h2>";
        echo "<div class='section'>";
        
        if (file_exists($receiverScript)) {
            echo "<p class='success'>✓ Script existe: $receiverScript</p>";
            echo "<p><strong>URL Pública:</strong> <span class='info'>https://janetzy.shop/residencial/receive_plate.php</span></p>";
            
            // Mostrar permisos
            $perms = substr(sprintf('%o', fileperms($receiverScript)), -4);
            echo "<p><strong>Permisos:</strong> $perms</p>";
            
        } else {
            echo "<p class='error'>✗ Script NO existe en: $receiverScript</p>";
        }
        echo "</div>";
        
        // Logs del receptor
        echo "<h2>📋 4. LOG DEL RECEPTOR (últimas líneas)</h2>";
        echo "<div class='section'>";
        
        if (file_exists($receiverLog)) {
            $logContent = file_get_contents($receiverLog);
            $lines = explode("\n", $logContent);
            $lastLines = array_slice($lines, -30);
            
            $lastRequest = null;
            foreach ($lastLines as $line) {
                if (strpos($line, 'IP Origen:') !== false) {
                    preg_match('/\[(.*?)\].*IP Origen: (.*)/', $line, $matches);
                    if (count($matches) >= 3) {
                        $lastRequest = [
                            'time' => $matches[1],
                            'ip' => $matches[2]
                        ];
                    }
                }
            }
            
            if ($lastRequest) {
                echo "<p class='success'>✓ Última petición recibida:</p>";
                echo "<p><strong>Fecha:</strong> {$lastRequest['time']}</p>";
                echo "<p><strong>IP:</strong> {$lastRequest['ip']}</p>";
                
                $timeDiff = time() - strtotime($lastRequest['time']);
                $minutes = round($timeDiff / 60);
                
                if ($minutes < 5) {
                    echo "<p class='success'>✓ Hace $minutes minuto(s) - RECIENTE</p>";
                } elseif ($minutes < 60) {
                    echo "<p class='warning'>⚠️ Hace $minutes minuto(s)</p>";
                } else {
                    $hours = round($minutes / 60);
                    echo "<p class='error'>⚠️ Hace $hours hora(s) - ANTIGUO</p>";
                }
            } else {
                echo "<p class='warning'>⚠️ No se encontraron peticiones recientes</p>";
            }
            
            echo "<pre>";
            echo htmlspecialchars(implode("\n", $lastLines));
            echo "</pre>";
        } else {
            echo "<p class='error'>✗ No existe archivo de log: $receiverLog</p>";
            echo "<p>La cámara AÚN NO ha enviado ninguna imagen al script</p>";
        }
        echo "</div>";
        
        // Configuración de la cámara
        echo "<h2>⚙️ 5. CONFIGURACIÓN DE LA CÁMARA</h2>";
        echo "<div class='section'>";
        echo "<p><strong>Parámetros que debes configurar en tu cámara HikVision:</strong></p>";
        echo "<div class='code-box'>";
        echo "Menú: Configuration → Event → Smart Event → Vehicle Detection\n\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║  Enable Vehicle Detection:      [✓] Enabled                 ║\n";
        echo "║  License Plate Recognition:     [✓] Enabled                 ║\n";
        echo "║                                                              ║\n";
        echo "║  Upload Method:                  HTTP (POST)                ║\n";
        echo "║  Protocol:                       HTTPS                      ║\n";
        echo "║  Server Address:                 janetzy.shop               ║\n";
        echo "║  Port:                           443                        ║\n";
        echo "║  URL Path:                       /residencial/receive_plate.php ║\n";
        echo "║  Username:                       (dejar vacío)              ║\n";
        echo "║  Password:                       (dejar vacío)              ║\n";
        echo "║                                                              ║\n";
        echo "║  Upload Picture:                 [✓] License Plate          ║\n";
        echo "║                                  [✓] Vehicle Picture        ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "</div>";
        echo "</div>";
        
        // Test manual
        echo "<h2>🧪 6. HACER TEST MANUAL</h2>";
        echo "<div class='section'>";
        echo "<p>Usa este comando desde tu computadora para simular que la cámara envía una imagen:</p>";
        echo "<div class='code-box'>";
        echo "curl -X POST -F \"licensePlatePicture_jpg=@test.jpg\" \\\n";
        echo "     -F \"plate=ABC123\" \\\n";
        echo "     https://janetzy.shop/residencial/receive_plate.php\n";
        echo "</div>";
        echo "<p>O prueba desde el navegador:</p>";
        echo "<form action='/residencial/receive_plate.php' method='POST' enctype='multipart/form-data' style='margin:10px 0;'>";
        echo "<input type='file' name='licensePlatePicture_jpg' required style='padding:5px; background:#000; color:#0f0; border:1px solid #0f0;'><br><br>";
        echo "<input type='text' name='plate' placeholder='ABC123' style='padding:5px; background:#000; color:#0f0; border:1px solid #0f0;'><br><br>";
        echo "<button type='submit' class='btn'>Enviar Test</button>";
        echo "</form>";
        echo "</div>";
        
        // Base de datos
        echo "<h2>💾 7. REGISTROS EN BASE DE DATOS</h2>";
        echo "<div class='section'>";
        
        try {
            $db = new PDO(
                "mysql:host=localhost;dbname=janetzy_residencial;charset=utf8mb4",
                "janetzy_residencial",
                "Danjohn007!",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM detected_plates");
            $total = $stmt->fetch()['total'];
            
            echo "<p class='success'>✓ Conexión a BD exitosa</p>";
            echo "<p><strong>Total placas detectadas:</strong> $total</p>";
            
            if ($total > 0) {
                $stmt = $db->query("
                    SELECT plate_text, captured_at, is_match, status 
                    FROM detected_plates 
                    ORDER BY captured_at DESC 
                    LIMIT 5
                ");
                echo "<table>";
                echo "<tr><th>Placa</th><th>Fecha</th><th>Match</th><th>Status</th></tr>";
                while ($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td><strong>{$row['plate_text']}</strong></td>";
                    echo "<td>{$row['captured_at']}</td>";
                    echo "<td class='" . ($row['is_match'] ? 'success' : 'warning') . "'>" . ($row['is_match'] ? 'SÍ' : 'NO') . "</td>";
                    echo "<td>{$row['status']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
        } catch (PDOException $e) {
            echo "<p class='error'>✗ Error de BD: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
        
        // Resumen y acciones
        echo "<h2>📊 8. RESUMEN Y ACCIONES</h2>";
        echo "<div class='section'>";
        
        $hasLog = file_exists($receiverLog);
        $hasImages = is_dir($ftpDir) && count(glob($ftpDir . '/*.{jpg,jpeg,png}', GLOB_BRACE)) > 0;
        
        if ($hasLog && $lastRequest && $minutes < 10) {
            echo "<p class='success'>✓ TODO FUNCIONANDO: La cámara está enviando imágenes correctamente</p>";
            echo "<a href='/residencial/access/detectedPlates' class='btn'>Ver Placas Detectadas</a>";
        } elseif ($hasLog) {
            echo "<p class='warning'>⚠️ La cámara envió imágenes antes, pero hace tiempo</p>";
            echo "<p><strong>Verifica:</strong></p>";
            echo "<ul>";
            echo "<li>¿La cámara está encendida y conectada?</li>";
            echo "<li>¿Está en el mismo modo de detección?</li>";
            echo "<li>¿Pasaron vehículos frente a ella recientemente?</li>";
            echo "</ul>";
        } else {
            echo "<p class='error'>✗ LA CÁMARA NUNCA HA ENVIADO IMÁGENES</p>";
            echo "<p><strong>Pasos a seguir:</strong></p>";
            echo "<ol>";
            echo "<li>Revisa la configuración de la cámara (ver sección 5)</li>";
            echo "<li>Verifica que la URL sea exactamente: <code>https://janetzy.shop/residencial/receive_plate.php</code></li>";
            echo "<li>Haz un test manual (ver sección 6)</li>";
            echo "<li>Verifica que la cámara tenga acceso a Internet</li>";
            echo "</ol>";
        }
        
        echo "</div>";
        
        // Enlaces útiles
        echo "<h2>🔗 ENLACES ÚTILES</h2>";
        echo "<div class='section'>";
        echo "<a href='/logs/camera_receiver.log' class='btn' target='_blank'>Ver Log Completo</a>";
        echo "<a href='/residencial/check_cron_status.php?key=status2025' class='btn' target='_blank'>Estado del Cron</a>";
        echo "<a href='/residencial/access/detectedPlates' class='btn' target='_blank'>Placas Detectadas</a>";
        echo "<a href='?key=diag2025' class='btn'>🔄 Actualizar</a>";
        echo "</div>";
        ?>
        
        <p style="text-align:center; margin-top:30px; color:#666;">
            Actualizado: <?php echo date('Y-m-d H:i:s'); ?>
        </p>
    </div>
</body>
</html>
