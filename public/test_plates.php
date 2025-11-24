<?php
/**
 * Archivo para ejecutar manualmente el procesamiento de placas
 * Acceder desde: http://tudominio.com/test_plates.php?key=plates2025
 * 
 * IMPORTANTE: Eliminar este archivo después de las pruebas por seguridad
 */

// Seguridad básica con clave
$secret_key = "plates2025";
if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    die('Acceso denegado. Use: test_plates.php?key=plates2025');
}

// Detener el sistema de autenticación del framework
define('NO_AUTH_REQUIRED', true);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Placas - Test</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #252526;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        h1 {
            color: #4ec9b0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        .btn {
            background: #0e639c;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #1177bb;
        }
        .btn-danger {
            background: #c93737;
        }
        .btn-danger:hover {
            background: #d44949;
        }
        .output {
            background: #1e1e1e;
            border: 1px solid #3c3c3c;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
            white-space: pre-wrap;
            font-size: 14px;
            max-height: 600px;
            overflow-y: auto;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #4ec9b0;
        }
        .stat-label {
            color: #858585;
            font-size: 12px;
            text-transform: uppercase;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #4ec9b0;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚗 Procesamiento de Placas - Panel de Control</h1>
        
        <div style="margin: 20px 0;">
            <button class="btn" onclick="runTest()">🔍 Ejecutar Test</button>
            <button class="btn" onclick="processPlates()">▶️ Procesar Placas Ahora</button>
            <button class="btn" onclick="viewRecent()">📋 Ver Últimas Placas</button>
            <button class="btn btn-danger" onclick="clearOutput()">🗑️ Limpiar</button>
        </div>

        <div id="stats" class="stats" style="display: none;"></div>
        <div id="output" class="output">Haz clic en un botón para comenzar...</div>
    </div>

    <script>
        function clearOutput() {
            document.getElementById('output').innerHTML = 'Consola limpiada...';
            document.getElementById('stats').style.display = 'none';
        }

        async function runTest() {
            const output = document.getElementById('output');
            output.innerHTML = '<span class="info">⏳ Ejecutando test de configuración...</span>\n\n';
            
            try {
                const response = await fetch('?action=test');
                const text = await response.text();
                output.innerHTML = text;
            } catch (error) {
                output.innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            }
        }

        async function processPlates() {
            const output = document.getElementById('output');
            output.innerHTML = '<span class="info">⏳ Procesando placas detectadas...</span>\n\n';
            
            try {
                const response = await fetch('?action=process');
                const text = await response.text();
                output.innerHTML = text;
                
                // Actualizar estadísticas después de procesar
                viewRecent();
            } catch (error) {
                output.innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            }
        }

        async function viewRecent() {
            const statsDiv = document.getElementById('stats');
            const output = document.getElementById('output');
            output.innerHTML = '<span class="info">⏳ Consultando base de datos...</span>\n\n';
            
            try {
                const response = await fetch('?action=recent');
                const text = await response.text();
                output.innerHTML = text;
                statsDiv.style.display = 'grid';
            } catch (error) {
                output.innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            }
        }

        // Auto-refrescar cada 30 segundos si está visible
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                viewRecent();
            }
        }, 30000);
    </script>
</body>
</html>

<?php
if (isset($_GET['action'])) {
    header('Content-Type: text/plain; charset=utf-8');
    
    switch ($_GET['action']) {
        case 'test':
            runConfigTest();
            break;
        case 'process':
            processPlatesNow();
            break;
        case 'recent':
            showRecentPlates();
            break;
    }
    exit;
}

function runConfigTest() {
    echo "========== TEST DE CONFIGURACIÓN ==========\n\n";
    
    // Test de BD con credenciales directas
    try {
        $db = new PDO(
            "mysql:host=localhost;dbname=janetzy_residencial;charset=utf8mb4",
            "janetzy_shop",
            "Danjohn007",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "✓ Conexión a base de datos exitosa\n";
        
        // Verificar tablas
        $tables = ['detected_plates', 'vehicles', 'access_logs'];
        foreach ($tables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            echo $stmt->rowCount() > 0 ? "✓" : "✗";
            echo " Tabla: $table\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error de BD: " . $e->getMessage() . "\n";
    }
    
    // Verificar directorios
    echo "\nVerificando directorios:\n";
    $ftpDir = '/home2/janetzy/placas/IP CAMERA/01';
    $publicDir = '/home2/janetzy/public_html/placas';
    
    echo is_dir($ftpDir) ? "✓" : "✗";
    echo " FTP: $ftpDir\n";
    
    echo is_dir($publicDir) ? "✓" : "✗";
    echo " Public: $publicDir\n";
    
    // Contar imágenes
    if (is_dir($ftpDir)) {
        $images = glob($ftpDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
        echo "\n📸 Imágenes en FTP: " . count($images) . "\n";
        
        if (count($images) > 0) {
            echo "\nPrimeras 5 imágenes:\n";
            foreach (array_slice($images, 0, 5) as $img) {
                echo "  - " . basename($img) . "\n";
            }
        }
    }
    
    echo "\n========== FIN DEL TEST ==========\n";
}

function processPlatesNow() {
    echo "========== PROCESANDO PLACAS ==========\n";
    echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Incluir el script principal
    $scriptPath = __DIR__ . '/../cron/process_plate_images.php';
    
    if (!file_exists($scriptPath)) {
        echo "✗ Error: No se encuentra el script en $scriptPath\n";
        return;
    }
    
    // Capturar salida del script
    ob_start();
    include $scriptPath;
    $output = ob_get_clean();
    
    echo $output;
    echo "\n========== PROCESAMIENTO COMPLETADO ==========\n";
}

function showRecentPlates() {
    try {
        $db = new PDO(
            "mysql:host=localhost;dbname=janetzy_residencial;charset=utf8mb4",
            "janetzy_shop",
            "Danjohn007",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Estadísticas
        $stmt = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(is_match) as matched,
                COUNT(*) - SUM(is_match) as unmatched
            FROM detected_plates
            WHERE DATE(captured_at) = CURDATE()
        ");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "========== ESTADÍSTICAS DE HOY ==========\n\n";
        echo "📊 Total detectadas: " . $stats['total'] . "\n";
        echo "✓ Autorizadas: " . $stats['matched'] . "\n";
        echo "✗ No autorizadas: " . $stats['unmatched'] . "\n\n";
        
        // Últimas 10 placas
        echo "========== ÚLTIMAS 10 PLACAS ==========\n\n";
        $stmt = $db->query("
            SELECT 
                dp.plate_text,
                dp.captured_at,
                dp.is_match,
                dp.status,
                v.brand,
                v.model
            FROM detected_plates dp
            LEFT JOIN vehicles v ON dp.matched_vehicle_id = v.id
            ORDER BY dp.captured_at DESC
            LIMIT 10
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $icon = $row['is_match'] ? '✓' : '✗';
            $status = $row['is_match'] ? 'AUTORIZADA' : 'NO AUTORIZADA';
            
            echo "$icon {$row['plate_text']} - $status\n";
            echo "   Detectada: {$row['captured_at']}\n";
            if ($row['brand']) {
                echo "   Vehículo: {$row['brand']} {$row['model']}\n";
            }
            echo "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}
?>
