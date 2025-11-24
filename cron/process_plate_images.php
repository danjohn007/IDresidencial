<?php
/**
 * Script para procesar imágenes de placas detectadas
 * Mueve imágenes desde FTP a carpeta pública y registra en BD
 * 
 * Ejecutar con cron cada 1-5 minutos en cPanel:
 * Comando: /usr/bin/php /home2/janetzy/public_html/cron/process_plate_images.php
 */

// Configuración de rutas
define('FTP_SOURCE_DIR', '/home2/janetzy/placas/IP CAMERA/01');
define('PUBLIC_DEST_DIR', '/home2/janetzy/public_html/placas');
define('WEB_URL_PATH', '/placas'); // Ruta web relativa

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'janetzy_residencial');
define('DB_USER', 'janetzy_residencial');
define('DB_PASS', 'Danjohn007!');

// Log file
define('LOG_FILE', '/home2/janetzy/public_html/logs/plate_processing.log');

// Iniciar script
writeLog("========== INICIO DE PROCESAMIENTO ==========");
writeLog("Fecha/Hora: " . date('Y-m-d H:i:s'));

try {
    // Conectar a base de datos
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    writeLog("Conexión a BD exitosa");
    
    // Verificar que existen los directorios
    if (!is_dir(FTP_SOURCE_DIR)) {
        writeLog("ERROR: No existe directorio FTP: " . FTP_SOURCE_DIR);
        exit(1);
    }
    
    if (!is_dir(PUBLIC_DEST_DIR)) {
        writeLog("Creando directorio público: " . PUBLIC_DEST_DIR);
        mkdir(PUBLIC_DEST_DIR, 0755, true);
    }
    
    // Buscar imágenes en directorio FTP
    $images = glob(FTP_SOURCE_DIR . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
    $processedCount = 0;
    
    writeLog("Imágenes encontradas: " . count($images));
    
    foreach ($images as $imagePath) {
        try {
            processImage($imagePath, $db);
            $processedCount++;
        } catch (Exception $e) {
            writeLog("ERROR procesando " . basename($imagePath) . ": " . $e->getMessage());
        }
    }
    
    writeLog("Imágenes procesadas exitosamente: $processedCount");
    writeLog("========== FIN DE PROCESAMIENTO ==========\n");
    
} catch (PDOException $e) {
    writeLog("ERROR DE BD: " . $e->getMessage());
    exit(1);
} catch (Exception $e) {
    writeLog("ERROR GENERAL: " . $e->getMessage());
    exit(1);
}

/**
 * Procesar una imagen individual
 */
function processImage($imagePath, $db) {
    $fileName = basename($imagePath);
    
    // Extraer información del nombre del archivo
    // Formato esperado: PLACA_FECHA_HORA.jpg o similar
    $plateInfo = extractPlateInfo($fileName);
    
    if (!$plateInfo) {
        writeLog("SKIP: No se pudo extraer info de placa de: $fileName");
        return;
    }
    
    // Generar nuevo nombre con timestamp para evitar sobrescribir
    $timestamp = date('YmdHis');
    $newFileName = $plateInfo['plate'] . '_' . $timestamp . '.jpg';
    $destPath = PUBLIC_DEST_DIR . '/' . $newFileName;
    $webPath = WEB_URL_PATH . '/' . $newFileName;
    
    // Mover archivo
    if (!copy($imagePath, $destPath)) {
        throw new Exception("No se pudo copiar la imagen");
    }
    
    // Verificar si la placa ya existe en detected_plates (últimas 2 horas)
    $stmt = $db->prepare("
        SELECT id FROM detected_plates 
        WHERE plate_text = ? 
        AND captured_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
        LIMIT 1
    ");
    $stmt->execute([$plateInfo['plate']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        writeLog("SKIP: Placa {$plateInfo['plate']} ya registrada recientemente");
        unlink($imagePath); // Eliminar original
        return;
    }
    
    // Buscar si la placa está registrada en vehicles
    $stmt = $db->prepare("
        SELECT v.id, v.resident_id, v.plate, v.brand, v.model
        FROM vehicles v
        WHERE v.plate = ? AND v.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$plateInfo['plate']]);
    $vehicle = $stmt->fetch();
    
    $isMatch = $vehicle ? 1 : 0;
    
    // SOLO guardar en detected_plates si NO está registrada (placa desconocida)
    if (!$isMatch) {
        $stmt = $db->prepare("
            INSERT INTO detected_plates (
                plate_text,
                captured_at,
                unit_id,
                is_match,
                matched_vehicle_id,
                payload_json,
                status,
                processed_at,
                notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        
        $payload = json_encode([
            'image_path' => $webPath,
            'original_filename' => $fileName,
            'processed_at' => date('Y-m-d H:i:s'),
            'source' => 'ftp_hikvision'
        ]);
        
        $stmt->execute([
            $plateInfo['plate'],
            $plateInfo['captured_at'],
            1, // unit_id (cámara 1)
            0, // is_match = false
            null, // no matched_vehicle_id
            $payload,
            'new', // status
            "Vehículo no registrado en el sistema"
        ]);
        
        $detectedPlateId = $db->lastInsertId();
        writeLog("GUARDADO en detected_plates ID: $detectedPlateId (placa NO registrada)");
    } else {
        writeLog("SKIP: Placa {$plateInfo['plate']} ya registrada como {$vehicle['brand']} {$vehicle['model']}");
    }
    
    // Si hay coincidencia (vehículo autorizado), registrar en access_logs
    if ($isMatch) {
        $stmt = $db->prepare("
            INSERT INTO access_logs (
                log_type,
                reference_id,
                access_type,
                access_method,
                property_id,
                name,
                vehicle_plate,
                notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Obtener info del residente
        $stmt2 = $db->prepare("
            SELECT r.property_id, u.first_name, u.last_name
            FROM residents r
            INNER JOIN users u ON r.user_id = u.id
            WHERE r.id = ? AND r.status = 'active'
            LIMIT 1
        ");
        $stmt2->execute([$vehicle['resident_id']]);
        $resident = $stmt2->fetch();
        
        if ($resident) {
            $stmt->execute([
                'vehicle',
                $vehicle['id'],
                'entry',
                'plate_recognition',
                $resident['property_id'],
                $resident['first_name'] . ' ' . $resident['last_name'],
                $plateInfo['plate'],
                "Acceso automático por reconocimiento de placa (ID: $detectedPlateId)"
            ]);
            
            writeLog("✓ REGISTRADO: Placa {$plateInfo['plate']} - Vehículo autorizado");
        }
    } else {
        writeLog("⚠ ALERTA: Placa {$plateInfo['plate']} - NO autorizada");
    }
    
    // Eliminar archivo original del FTP
    unlink($imagePath);
    writeLog("Imagen movida: $fileName -> $newFileName");
}

/**
 * Extraer información de placa del nombre de archivo
 * Formatos soportados:
 * - ABC123_20251124_143015.jpg
 * - ABC-123-D_20251124143015.jpg
 * - Snapshot_1_20251124143015_ABC123.jpg
 */
function extractPlateInfo($fileName) {
    // Remover extensión
    $name = pathinfo($fileName, PATHINFO_FILENAME);
    
    // Patrón 1: PLACA_FECHA_HORA
    if (preg_match('/^([A-Z0-9\-]+)_(\d{8})_(\d{6})/', $name, $matches)) {
        return [
            'plate' => $matches[1],
            'captured_at' => date('Y-m-d H:i:s', strtotime($matches[2] . $matches[3]))
        ];
    }
    
    // Patrón 2: Snapshot_X_FECHAHORA_PLACA
    if (preg_match('/Snapshot_\d+_(\d{8})(\d{6})_([A-Z0-9\-]+)/', $name, $matches)) {
        return [
            'plate' => $matches[3],
            'captured_at' => date('Y-m-d H:i:s', strtotime($matches[1] . $matches[2]))
        ];
    }
    
    // Patrón 3: Solo fecha en nombre, intentar extraer placa de cualquier parte
    if (preg_match('/([A-Z]{3}\-?\d{3}\-?[A-Z0-9]?)/', $name, $matches)) {
        // Usar fecha de modificación del archivo
        return [
            'plate' => $matches[1],
            'captured_at' => date('Y-m-d H:i:s')
        ];
    }
    
    return null;
}

/**
 * Escribir en log
 */
function writeLog($message) {
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
    echo $logMessage; // También mostrar en consola
}
