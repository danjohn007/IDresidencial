<?php
/**
 * Panel de Control de Procesamiento de Placas
 * Colocar en: /home2/janetzy/public_html/check_plates.php
 * Acceder: https://janetzy.shop/check_plates.php?key=plates2025
 */

// Suprimir warnings y notices
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');

// Seguridad
$secret_key = "plates2025";
if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    http_response_code(403);
    die('Acceso denegado. Contacte al administrador.');
}

// Configuración
$DB_HOST = 'localhost';
$DB_NAME = 'janetzy_residencial';
$DB_USER = 'janetzy_residencial';
$DB_PASS = 'Danjohn007!';

$FTP_DIR = '/home2/janetzy/placas/IP CAMERA/01';
$PUBLIC_DIR = '/home2/janetzy/public_html/placas';
$CRON_SCRIPT = '/home2/janetzy/public_html/residencial/cron/process_plate_images.php';

// IMPORTANTE: Procesar acciones ANTES de mostrar HTML
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'stats') {
        header('Content-Type: application/json');
        echo json_encode(getStats());
        exit;
    }
    
    header('Content-Type: text/plain; charset=utf-8');
    
    switch ($action) {
        case 'test':
            runTest();
            break;
        case 'process':
            processPlates();
            break;
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Placas</title>
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
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: #48bb78;
            color: white;
        }
        .btn-success:hover {
            background: #38a169;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(72, 187, 120, 0.4);
        }
        .btn-info {
            background: #4299e1;
            color: white;
        }
        .btn-info:hover {
            background: #3182ce;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 153, 225, 0.4);
        }
        .btn-danger {
            background: #f56565;
            color: white;
        }
        .btn-danger:hover {
            background: #e53e3e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 101, 101, 0.4);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-label {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin-top: 10px;
        }
        .output {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            max-height: 500px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .output::-webkit-scrollbar {
            width: 8px;
        }
        .output::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .output::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .success { color: #38a169; font-weight: bold; }
        .error { color: #e53e3e; font-weight: bold; }
        .warning { color: #d69e2e; font-weight: bold; }
        .info { color: #3182ce; font-weight: bold; }
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚗 Control de Placas Detectadas</h1>
        <p class="subtitle">Sistema de procesamiento automático de reconocimiento de placas vehiculares</p>
        
        <div class="stats" id="stats" style="display: none;"></div>
        
        <div class="buttons">
            <button class="btn btn-primary" onclick="runTest()">
                🔍 Test de Configuración
            </button>
            <button class="btn btn-success" onclick="processPlates()">
                ▶️ Procesar Placas Ahora
            </button>
            <button class="btn btn-info" onclick="viewRecent()">
                📊 Ver Estadísticas
            </button>
            <button class="btn btn-danger" onclick="clearOutput()">
                🗑️ Limpiar Consola
            </button>
        </div>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Procesando...</p>
        </div>

        <div class="output" id="output">
            <span class="info">✨ Bienvenido al panel de control</span>
            
Selecciona una opción para comenzar...
        </div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('output').style.display = 'none';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('output').style.display = 'block';
        }

        function clearOutput() {
            document.getElementById('output').innerHTML = '<span class="info">Consola limpiada...</span>';
            document.getElementById('stats').style.display = 'none';
        }

        async function runTest() {
            showLoading();
            try {
                const response = await fetch('?key=plates2025&action=test');
                const text = await response.text();
                document.getElementById('output').innerHTML = text;
            } catch (error) {
                document.getElementById('output').innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            } finally {
                hideLoading();
            }
        }

        async function processPlates() {
            showLoading();
            try {
                const response = await fetch('?key=plates2025&action=process');
                const text = await response.text();
                document.getElementById('output').innerHTML = text;
                setTimeout(viewRecent, 1000);
            } catch (error) {
                document.getElementById('output').innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            } finally {
                hideLoading();
            }
        }

        async function viewRecent() {
            showLoading();
            try {
                const response = await fetch('?key=plates2025&action=stats');
                const data = await response.json();
                
                if (data.success) {
                    // Mostrar estadísticas
                    const statsDiv = document.getElementById('stats');
                    statsDiv.innerHTML = `
                        <div class="stat-card">
                            <div class="stat-label">Total Hoy</div>
                            <div class="stat-value">${data.stats.total}</div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">
                            <div class="stat-label">Autorizadas</div>
                            <div class="stat-value">${data.stats.matched}</div>
                        </div>
                        <div class="stat-card" style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);">
                            <div class="stat-label">No Autorizadas</div>
                            <div class="stat-value">${data.stats.unmatched}</div>
                        </div>
                    `;
                    statsDiv.style.display = 'grid';
                    
                    // Mostrar últimas placas
                    let output = '<span class="info">📋 ÚLTIMAS 10 PLACAS DETECTADAS</span>\n\n';
                    data.plates.forEach(plate => {
                        const icon = plate.is_match ? '<span class="success">✓</span>' : '<span class="error">✗</span>';
                        const status = plate.is_match ? '<span class="success">AUTORIZADA</span>' : '<span class="error">NO AUTORIZADA</span>';
                        output += `${icon} <strong>${plate.plate_text}</strong> - ${status}\n`;
                        output += `   📅 ${plate.captured_at}\n`;
                        if (plate.brand) {
                            output += `   🚗 ${plate.brand} ${plate.model}\n`;
                        }
                        output += '\n';
                    });
                    
                    document.getElementById('output').innerHTML = output;
                } else {
                    document.getElementById('output').innerHTML = '<span class="error">❌ Error al obtener estadísticas</span>';
                }
            } catch (error) {
                document.getElementById('output').innerHTML = '<span class="error">❌ Error: ' + error.message + '</span>';
            } finally {
                hideLoading();
            }
        }

        // Auto-actualizar cada 30 segundos
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                viewRecent();
            }
        }, 30000);
    </script>
</body>
</html>

<?php
// ========================================
// FUNCIONES DE BACKEND
// ========================================

function getStats() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    
    try {
        $db = new PDO(
            "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
            $DB_USER,
            $DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]
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
        
        // Últimas placas
        $stmt = $db->query("
            SELECT 
                dp.plate_text,
                dp.captured_at,
                dp.is_match,
                v.brand,
                v.model
            FROM detected_plates dp
            LEFT JOIN vehicles v ON dp.matched_vehicle_id = v.id
            ORDER BY dp.captured_at DESC
            LIMIT 10
        ");
        $plates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'stats' => $stats,
            'plates' => $plates
        ];
        
    } catch (PDOException $e) {
        // Registrar error detallado
        error_log("Stats Error: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Error de conexión a BD',
            'details' => $e->getMessage(),
            'code' => $e->getCode()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function runTest() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $FTP_DIR, $PUBLIC_DIR;
    
    echo "<span class='info'>========== TEST DE CONFIGURACIÓN ==========</span>\n\n";
    
    // Test BD
    echo "<span class='info'>Intentando conectar...</span>\n";
    echo " Host: $DB_HOST\n";
    echo " Database: $DB_NAME\n";
    echo " User: $DB_USER\n\n";
    
    try {
        $db = new PDO(
            "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
            $DB_USER,
            $DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]
        );
        echo "<span class='success'>✓</span> Conexión a BD exitosa\n";
        
        // Verificar tablas
        $tables = ['detected_plates', 'vehicles', 'access_logs'];
        foreach ($tables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<span class='success'>✓</span> Tabla: $table\n";
            } else {
                echo "<span class='error'>✗</span> Tabla: $table <span class='error'>(NO EXISTE)</span>\n";
            }
        }
        
    } catch (PDOException $e) {
        echo "<span class='error'>✗ Error BD:</span> " . $e->getMessage() . "\n";
        echo "<span class='error'>Code:</span> " . $e->getCode() . "\n";
        echo "<span class='info'>Posible causa: Permisos de usuario en cPanel/phpMyAdmin</span>\n";
    } catch (Exception $e) {
        echo "<span class='error'>✗ Error:</span> " . $e->getMessage() . "\n";
    }
    
    // Verificar directorios
    echo "\n<span class='info'>Directorios:</span>\n";
    echo (is_dir($FTP_DIR) ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>");
    echo " FTP: $FTP_DIR\n";
    
    echo (is_dir($PUBLIC_DIR) ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>");
    echo " Público: $PUBLIC_DIR\n";
    
    // Contar imágenes
    if (is_dir($FTP_DIR)) {
        $images = glob($FTP_DIR . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
        echo "\n<span class='warning'>📸 Imágenes en FTP:</span> " . count($images) . "\n";
        
        if (count($images) > 0) {
            echo "\n<span class='info'>Primeras 5 imágenes:</span>\n";
            foreach (array_slice($images, 0, 5) as $img) {
                echo "  • " . basename($img) . "\n";
            }
        }
    }
    
    echo "\n<span class='info'>========== FIN DEL TEST ==========</span>\n";
}

function processPlates() {
    global $CRON_SCRIPT, $FTP_DIR, $PUBLIC_DIR, $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    
    echo "<span class='info'>========== PROCESANDO PLACAS ==========</span>\n";
    echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Verificar directorio FTP
    if (!is_dir($FTP_DIR)) {
        echo "<span class='error'>✗ Error: No existe directorio FTP</span>\n";
        echo "Buscando en: $FTP_DIR\n";
        return;
    }
    
    // Verificar/crear directorio público
    if (!is_dir($PUBLIC_DIR)) {
        echo "<span class='warning'>⚠ Creando directorio público...</span>\n";
        mkdir($PUBLIC_DIR, 0755, true);
    }
    
    // Buscar imágenes
    $images = glob($FTP_DIR . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
    $processed = 0;
    
    echo "<span class='info'>📸 Imágenes encontradas:</span> " . count($images) . "\n\n";
    
    if (count($images) == 0) {
        echo "<span class='warning'>⚠ No hay imágenes para procesar</span>\n";
        return;
    }
    
    // Conectar a BD
    try {
        $db = new PDO(
            "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
            $DB_USER,
            $DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]
        );
        echo "<span class='success'>✓ Conectado a BD</span>\n\n";
    } catch (PDOException $e) {
        echo "<span class='error'>✗ Error BD: " . $e->getMessage() . "</span>\n";
        echo "<span class='error'>Code: " . $e->getCode() . "</span>\n";
        echo "<span class='info'>Host: $DB_HOST | User: $DB_USER | DB: $DB_NAME</span>\n";
        return;
    }
    
    // Procesar cada imagen
    foreach ($images as $imagePath) {
        $fileName = basename($imagePath);
        echo "Procesando: <strong>$fileName</strong>\n";
        
        try {
            // Extraer placa del nombre
            $plateInfo = extractPlateFromFilename($fileName);
            
            if (!$plateInfo) {
                echo "  <span class='warning'>⚠ No se pudo extraer placa del nombre</span>\n\n";
                continue;
            }
            
            echo "  📋 Placa detectada: <strong>{$plateInfo['plate']}</strong>\n";
            
            // Verificar si ya existe (últimas 2 horas)
            $stmt = $db->prepare("
                SELECT id FROM detected_plates 
                WHERE plate_text = ? 
                AND captured_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
            ");
            $stmt->execute([$plateInfo['plate']]);
            
            if ($stmt->fetch()) {
                echo "  <span class='warning'>⚠ Placa ya registrada recientemente</span>\n";
                unlink($imagePath);
                echo "  <span class='info'>🗑️ Imagen eliminada</span>\n\n";
                continue;
            }
            
            // Buscar en vehicles
            $stmt = $db->prepare("
                SELECT v.id, v.resident_id, v.brand, v.model 
                FROM vehicles v
                WHERE v.plate = ? AND v.status = 'active'
            ");
            $stmt->execute([$plateInfo['plate']]);
            $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $isMatch = $vehicle ? 1 : 0;
            
            if ($isMatch) {
                echo "  <span class='success'>✓ AUTORIZADA</span> - {$vehicle['brand']} {$vehicle['model']}\n";
            } else {
                echo "  <span class='error'>✗ NO AUTORIZADA</span> - Placa no registrada\n";
            }
            
            // Mover imagen
            $newFileName = $plateInfo['plate'] . '_' . date('YmdHis') . '.jpg';
            $destPath = $PUBLIC_DIR . '/' . $newFileName;
            
            if (copy($imagePath, $destPath)) {
                echo "  <span class='success'>✓ Imagen movida a:</span> /placas/$newFileName\n";
                
                // Registrar en BD
                $stmt = $db->prepare("
                    INSERT INTO detected_plates (
                        plate_text, captured_at, unit_id, is_match, 
                        matched_vehicle_id, payload_json, status, 
                        processed_at, notes
                    ) VALUES (?, ?, 1, ?, ?, ?, ?, NOW(), ?)
                ");
                
                $payload = json_encode([
                    'image_path' => "/placas/$newFileName",
                    'original_filename' => $fileName
                ]);
                
                $status = $isMatch ? 'authorized' : 'new';
                $notes = $isMatch ? "Vehículo autorizado" : "Vehículo no registrado";
                
                $stmt->execute([
                    $plateInfo['plate'],
                    $plateInfo['captured_at'],
                    $isMatch,
                    $vehicle ? $vehicle['id'] : null,
                    $payload,
                    $status,
                    $notes
                ]);
                
                echo "  <span class='success'>✓ Registrado en BD</span> (ID: " . $db->lastInsertId() . ")\n";
                
                // Eliminar original
                unlink($imagePath);
                $processed++;
                
            } else {
                echo "  <span class='error'>✗ Error al mover imagen</span>\n";
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "  <span class='error'>✗ Error: " . $e->getMessage() . "</span>\n\n";
        }
    }
    
    echo "<span class='success'>========== COMPLETADO ==========</span>\n";
    echo "<span class='info'>Total procesadas:</span> <strong>$processed</strong>\n";
}

function extractPlateFromFilename($fileName) {
    $name = pathinfo($fileName, PATHINFO_FILENAME);
    
    // Patrón 1: ABC123_20251124_143015
    if (preg_match('/^([A-Z0-9\-]+)_(\d{8})_(\d{6})/', $name, $matches)) {
        return [
            'plate' => $matches[1],
            'captured_at' => date('Y-m-d H:i:s', strtotime($matches[2] . $matches[3]))
        ];
    }
    
    // Patrón 2: ABC123_YYYYMMDD_HHMMSS
    if (preg_match('/([A-Z0-9\-]+)_(\d{8})_(\d{6})/', $name, $matches)) {
        return [
            'plate' => $matches[1],
            'captured_at' => date('Y-m-d H:i:s', strtotime($matches[2] . $matches[3]))
        ];
    }
    
    // Patrón 3: Solo placa (usar fecha actual)
    if (preg_match('/([A-Z0-9]{6,10})/', $name, $matches)) {
        return [
            'plate' => $matches[1],
            'captured_at' => date('Y-m-d H:i:s')
        ];
    }
    
    return null;
}
?>
