<?php
/**
 * Script para procesar imágenes de placas detectadas
 * Mueve imágenes desde FTP a carpeta pública y registra en BD
 * 
 * Ejecutar con cron cada 1 minuto en cPanel:
 * Comando: /usr/bin/php /home2/janetzy/public_html/residencial/cron/process_plate_images.php
 * Frecuencia: * * * * * (cada minuto)
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

// Error reporting (para debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Asegurar que la carpeta de logs existe
$logDir = dirname('/home2/janetzy/public_html/logs/plate_processing.log');
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Iniciar script
writeLog("========== INICIO DE PROCESAMIENTO ==========");
writeLog("Fecha/Hora: " . date('Y-m-d H:i:s'));
writeLog("Script ejecutado desde: " . __FILE__);
writeLog("Usuario PHP: " . get_current_user());
writeLog("Directorio actual: " . getcwd());

try {
    // Conectar a base de datos
    writeLog("Intentando conectar a BD...");
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    writeLog("✓ Conexión a BD exitosa");
    
    // Verificar que existen los directorios
    writeLog("Verificando directorio FTP: " . FTP_SOURCE_DIR);
    if (!is_dir(FTP_SOURCE_DIR)) {
        writeLog("❌ ERROR: No existe directorio FTP: " . FTP_SOURCE_DIR);
        exit(1);
    }
    writeLog("✓ Directorio FTP existe");
    
    writeLog("Verificando directorio público: " . PUBLIC_DEST_DIR);
    if (!is_dir(PUBLIC_DEST_DIR)) {
        writeLog("Creando directorio público: " . PUBLIC_DEST_DIR);
        if (mkdir(PUBLIC_DEST_DIR, 0777, true)) {
            writeLog("✓ Directorio creado");
        } else {
            writeLog("❌ ERROR: No se pudo crear directorio");
        }
    } else {
        writeLog("✓ Directorio público existe");
        writeLog("Permisos: " . substr(sprintf('%o', fileperms(PUBLIC_DEST_DIR)), -4));
        writeLog("Escribible: " . (is_writable(PUBLIC_DEST_DIR) ? 'SÍ' : 'NO'));
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
    
    // Generar nuevo nombre con timestamp para evitar sobrescribir
    $timestamp = date('YmdHis');
    
    if (!$plateInfo) {
        // Si no se puede extraer la placa, usar el nombre original con timestamp
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = $baseName . '_' . $timestamp . '.' . $extension;
        $destPath = PUBLIC_DEST_DIR . '/' . $newFileName;
        $webPath = WEB_URL_PATH . '/' . $newFileName;
        
        writeLog("⚠ No se pudo extraer placa de: $fileName - Intentando extraer del nombre");
        
        // Intentar extraer placa del nombre de archivo (solo números y letras)
        $extractedPlate = preg_replace('/[^A-Z0-9]/', '', strtoupper($baseName));
        if (empty($extractedPlate)) {
            $extractedPlate = 'UNKNOWN_' . $timestamp;
        }
        
        // Copiar archivo
        if (!copy($imagePath, $destPath)) {
            $error = error_get_last();
            writeLog("ERROR al copiar: " . print_r($error, true));
            throw new Exception("No se pudo copiar la imagen");
        }
        
        writeLog("✓ Archivo copiado: $destPath");
        
        // GUARDAR EN BASE DE DATOS aunque no se reconozca el formato
        try {
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
                ) VALUES (?, NOW(), ?, ?, ?, ?, ?, NOW(), ?)
            ");
            
            $payload = json_encode([
                'image_path' => $webPath,
                'original_filename' => $fileName,
                'processed_at' => date('Y-m-d H:i:s'),
                'source' => 'ftp_hikvision'
            ]);
            
            $stmt->execute([
                $extractedPlate,
                1, // unit_id
                0, // is_match = false (no se puede verificar)
                null, // no matched_vehicle_id
                $payload,
                'unknown', // status
                "Placa extraída del nombre de archivo: $fileName"
            ]);
            
            $detectedPlateId = $db->lastInsertId();
            writeLog("✓ GUARDADO en detected_plates ID: $detectedPlateId - Placa: $extractedPlate");
        } catch (Exception $e) {
            writeLog("❌ ERROR al guardar en BD: " . $e->getMessage());
        }
        
        // Eliminar archivo original del FTP
        if (!unlink($imagePath)) {
            writeLog("⚠ No se pudo eliminar archivo original: $imagePath");
        } else {
            writeLog("✓ Archivo original eliminado del FTP");
        }
        
        writeLog("✅ Imagen procesada completamente: $fileName -> $newFileName");
        return;
    }
    
    $newFileName = $plateInfo['plate'] . '_' . $timestamp . '.jpg';
    $destPath = PUBLIC_DEST_DIR . '/' . $newFileName;
    $webPath = WEB_URL_PATH . '/' . $newFileName;
    
    // Verificar permisos del directorio destino
    if (!is_writable(PUBLIC_DEST_DIR)) {
        writeLog("ERROR: No se puede escribir en " . PUBLIC_DEST_DIR);
        throw new Exception("Directorio destino no tiene permisos de escritura");
    }
    
    // Copiar archivo
    if (!copy($imagePath, $destPath)) {
        $error = error_get_last();
        writeLog("ERROR al copiar: " . print_r($error, true));
        throw new Exception("No se pudo copiar la imagen: " . ($error['message'] ?? 'error desconocido'));
    }
    
    writeLog("✓ Archivo copiado: $destPath");
    
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
    
    // GUARDAR TODAS las placas en detected_plates (registradas y no registradas)
    try {
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
        
        $notes = $isMatch 
            ? "Vehículo registrado: {$vehicle['brand']} {$vehicle['model']}" 
            : "Vehículo no registrado en el sistema";
        
        $stmt->execute([
            $plateInfo['plate'],
            $plateInfo['captured_at'],
            1, // unit_id (cámara 1)
            $isMatch,
            $isMatch ? $vehicle['id'] : null,
            $payload,
            $isMatch ? 'known' : 'new',
            $notes
        ]);
        
        $detectedPlateId = $db->lastInsertId();
        writeLog("✓ GUARDADO en detected_plates ID: $detectedPlateId - Placa: {$plateInfo['plate']} - " . ($isMatch ? "REGISTRADA" : "NO REGISTRADA"));
    } catch (PDOException $e) {
        writeLog("❌ ERROR al insertar en detected_plates: " . $e->getMessage());
        writeLog("SQL Error Code: " . $e->getCode());
        writeLog("Plate: {$plateInfo['plate']}, Captured: {$plateInfo['captured_at']}");
        // No lanzar excepción para que continúe procesando otras imágenes
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
    if (!unlink($imagePath)) {
        writeLog("⚠ ADVERTENCIA: No se pudo eliminar archivo original: $imagePath");
    } else {
        writeLog("✓ Archivo original eliminado del FTP");
    }
    
    writeLog("✅ Imagen procesada completamente: $fileName -> $newFileName");
}

/**
 * Extraer información de placa del nombre de archivo
 * Formatos soportados:
 * - LP_XCST38A_20251125155921481_20251125160002.jpg (HikVision con doble timestamp)
 * - LP_ABC123_20251125155921481.jpg (HikVision con prefijo LP)
 * - VH_ABC123_20251125155921481.jpg (HikVision con prefijo VH)
 * - ABC123_20251124_143015.jpg
 * - ABC-123-D_20251124143015.jpg
 * - Snapshot_1_20251124143015_ABC123.jpg
 */
function extractPlateInfo($fileName) {
    // Remover extensión
    $name = pathinfo($fileName, PATHINFO_FILENAME);
    
    writeLog("Analizando nombre de archivo: $name");
    
    // Patrón 1: LP_PLACA_timestamp o VH_PLACA_timestamp (HikVision)
    // Ejemplo: LP_XCST38A_20251125155921481_20251125160002 o LP_XCST38A_20251125155921481
    if (preg_match('/^(?:LP|VH)_([A-Z0-9]{4,10})_(\d{14,17})/', $name, $matches)) {
        $plate = $matches[1];
        $timestamp = $matches[2];
        
        // Extraer fecha/hora del timestamp (YYYYMMDDHHMMSS + milisegundos opcionales)
        $year = substr($timestamp, 0, 4);
        $month = substr($timestamp, 4, 2);
        $day = substr($timestamp, 6, 2);
        $hour = substr($timestamp, 8, 2);
        $minute = substr($timestamp, 10, 2);
        $second = substr($timestamp, 12, 2);
        
        $capturedAt = "$year-$month-$day $hour:$minute:$second";
        
        writeLog("✓ Placa extraída (formato HikVision LP/VH): $plate - Fecha: $capturedAt");
        
        return [
            'plate' => $plate,
            'captured_at' => $capturedAt
        ];
    }
    
    // Patrón 2: PLACA_FECHA_HORA
    if (preg_match('/^([A-Z0-9\-]+)_(\d{8})_(\d{6})/', $name, $matches)) {
        writeLog("✓ Placa extraída (formato estándar): {$matches[1]}");
        return [
            'plate' => $matches[1],
            'captured_at' => date('Y-m-d H:i:s', strtotime($matches[2] . $matches[3]))
        ];
    }
    
    // Patrón 3: Snapshot_X_FECHAHORA_PLACA
    if (preg_match('/Snapshot_\d+_(\d{8})(\d{6})_([A-Z0-9\-]+)/', $name, $matches)) {
        writeLog("✓ Placa extraída (formato snapshot): {$matches[3]}");
        return [
            'plate' => $matches[3],
            'captured_at' => date('Y-m-d H:i:s', strtotime($matches[1] . $matches[2]))
        ];
    }
    
    // Patrón 4: Solo fecha en nombre, intentar extraer placa de cualquier parte
    if (preg_match('/([A-Z]{3}\-?\d{3}\-?[A-Z0-9]?)/', $name, $matches)) {
        writeLog("✓ Placa extraída (patrón genérico): {$matches[1]}");
        // Usar fecha de modificación del archivo
        return [
            'plate' => $matches[1],
            'captured_at' => date('Y-m-d H:i:s')
        ];
    }
    
    writeLog("⚠ No se pudo extraer placa del nombre");
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
