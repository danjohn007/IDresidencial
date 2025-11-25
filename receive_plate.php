<?php
/**
 * Script receptor para imágenes de cámara HikVision con LPR
 * URL a configurar en la cámara: https://janetzy.shop/receive_plate.php
 * 
 * La cámara debe enviar las imágenes vía HTTP POST
 */

// Configuración
define('UPLOAD_DIR', '/home2/janetzy/placas/IP CAMERA/01');
define('LOG_FILE', '/home2/janetzy/public_html/logs/camera_receiver.log');

// Habilitar logs de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Crear directorio si no existe
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Crear directorio de logs si no existe
$logDir = dirname(LOG_FILE);
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Función para escribir logs
function writeLog($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents(LOG_FILE, $logMessage, FILE_APPEND);
}

writeLog("========== NUEVA PETICIÓN ==========");
writeLog("Método: " . $_SERVER['REQUEST_METHOD']);
writeLog("IP Origen: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
writeLog("User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'));

// Registrar headers recibidos
writeLog("Headers recibidos:");
foreach (getallheaders() as $name => $value) {
    writeLog("  $name: $value");
}

// Verificar si es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog("ERROR: Método no permitido - solo POST");
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Registrar datos POST
if (!empty($_POST)) {
    writeLog("POST Data: " . json_encode($_POST));
}

// Variables globales para datos de la placa
$plateData = [
    'plate' => null,
    'captureTime' => null,
    'country' => null,
    'confidence' => null
];

// Verificar si hay archivos
if (empty($_FILES)) {
    writeLog("ERROR: No se recibieron archivos");
    
    // Intentar leer datos raw del body
    $rawData = file_get_contents('php://input');
    if (!empty($rawData)) {
        writeLog("Raw data length: " . strlen($rawData) . " bytes");
        
        // Intentar guardar como imagen directamente
        $filename = 'raw_' . date('YmdHis') . '.jpg';
        $filepath = UPLOAD_DIR . '/' . $filename;
        
        if (file_put_contents($filepath, $rawData)) {
            writeLog("✓ Archivo guardado desde raw data: $filename");
            http_response_code(200);
            echo json_encode(['success' => true, 'filename' => $filename]);
            exit;
        }
    }
    
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

writeLog("Archivos recibidos: " . count($_FILES));

// Primero procesar el XML para extraer datos de la placa
foreach ($_FILES as $fieldName => $file) {
    if ($file['type'] === 'text/xml' || strpos($file['name'], '.xml') !== false) {
        writeLog("Procesando XML: " . $file['name']);
        
        $xmlContent = file_get_contents($file['tmp_name']);
        writeLog("XML Content: " . $xmlContent);
        
        try {
            $xml = simplexml_load_string($xmlContent);
            
            // Intentar extraer placa de diferentes formatos XML de HikVision
            if (isset($xml->plateNumber)) {
                $plateData['plate'] = (string)$xml->plateNumber;
            } elseif (isset($xml->ANPR->licensePlate)) {
                $plateData['plate'] = (string)$xml->ANPR->licensePlate;
            } elseif (isset($xml->licensePlate)) {
                $plateData['plate'] = (string)$xml->licensePlate;
            }
            
            if (isset($xml->captureTime)) {
                $plateData['captureTime'] = (string)$xml->captureTime;
            }
            
            if (isset($xml->country)) {
                $plateData['country'] = (string)$xml->country;
            }
            
            if (isset($xml->confidence)) {
                $plateData['confidence'] = (string)$xml->confidence;
            }
            
            writeLog("Datos extraídos del XML:");
            writeLog("  Placa: " . ($plateData['plate'] ?? 'no detectada'));
            writeLog("  Tiempo: " . ($plateData['captureTime'] ?? 'no disponible'));
            writeLog("  País: " . ($plateData['country'] ?? 'no disponible'));
            writeLog("  Confianza: " . ($plateData['confidence'] ?? 'no disponible'));
            
        } catch (Exception $e) {
            writeLog("ERROR al parsear XML: " . $e->getMessage());
        }
        
        break; // Solo procesar un XML
    }
}

// Procesar cada archivo de imagen recibido
$savedFiles = [];
foreach ($_FILES as $fieldName => $file) {
    // Saltar XML, ya lo procesamos
    if ($file['type'] === 'text/xml' || strpos($file['name'], '.xml') !== false) {
        continue;
    }
    
    writeLog("Procesando archivo del campo: $fieldName");
    writeLog("  Nombre original: " . $file['name']);
    writeLog("  Tipo: " . $file['type']);
    writeLog("  Tamaño: " . $file['size'] . " bytes");
    writeLog("  Error: " . $file['error']);
    
    // Verificar errores de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = "Error al subir archivo: ";
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg .= "Archivo demasiado grande";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg .= "Archivo subido parcialmente";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg .= "No se subió ningún archivo";
                break;
            default:
                $errorMsg .= "Error desconocido ($file[error])";
        }
        writeLog("ERROR: $errorMsg");
        continue;
    }
    
    // Validar que sea imagen
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        writeLog("ERROR: Tipo de archivo no permitido: $mimeType");
        continue;
    }
    
    // Generar nombre de archivo usando datos del XML o fallback
    $plateText = null;
    
    // Opción 1: Del XML parseado (mejor fuente)
    if (!empty($plateData['plate'])) {
        $plateText = strtoupper(str_replace([' ', '-'], '', $plateData['plate']));
        writeLog("Usando placa del XML: $plateText");
    }
    // Opción 2: De POST data
    elseif (isset($_POST['plate']) || isset($_POST['plateNumber'])) {
        $plateText = $_POST['plate'] ?? $_POST['plateNumber'];
        writeLog("Usando placa de POST: $plateText");
    }
    // Opción 3: Del nombre de archivo
    elseif (preg_match('/([A-Z0-9]{3,10})/i', $file['name'], $matches)) {
        $plateText = strtoupper($matches[1]);
        writeLog("Usando placa del nombre archivo: $plateText");
    }
    
    // Generar nombre final
    $timestamp = date('YmdHis') . substr(microtime(), 2, 3); // Agregar milisegundos
    
    // Diferenciar entre foto de placa y foto de vehículo completo
    $isLicensePlate = (strpos($fieldName, 'licensePlate') !== false);
    $prefix = $isLicensePlate ? 'LP_' : 'VH_'; // LP = License Plate, VH = Vehicle
    
    if ($plateText) {
        $filename = $prefix . $plateText . '_' . $timestamp . '.jpg';
    } else {
        $filename = $prefix . 'UNKNOWN_' . $timestamp . '.jpg';
    }
    
    $filepath = UPLOAD_DIR . '/' . $filename;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        chmod($filepath, 0644);
        writeLog("✓ Archivo guardado exitosamente: $filename");
        $savedFiles[] = $filename;
    } else {
        writeLog("ERROR: No se pudo mover el archivo a $filepath");
    }
}

// Respuesta
if (count($savedFiles) > 0) {
    writeLog("✓ Proceso completado - " . count($savedFiles) . " archivo(s) guardado(s)");
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Files uploaded successfully',
        'files' => $savedFiles,
        'count' => count($savedFiles)
    ]);
} else {
    writeLog("ERROR: No se guardó ningún archivo");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'No files were saved'
    ]);
}

writeLog("========== FIN DE PETICIÓN ==========\n");
?>
