<?php
/**
 * Controlador de Consola de Guardia
 */

class GuardController extends Controller {
    
    private $visitModel;
    private $accessLogModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['guardia', 'superadmin', 'administrador']);
        $this->visitModel = $this->model('Visit');
        $this->accessLogModel = $this->model('AccessLog');
    }
    
    /**
     * Vista principal de la consola de guardia
     */
    public function index() {
        // Obtener visitas programadas para hoy
        $todayVisits = $this->visitModel->getTodayVisits();
        
        // Obtener accesos recientes
        $recentAccess = $this->accessLogModel->getToday();
        
        // Estadísticas del día
        $stats = [
            'total_visits' => count($todayVisits),
            'active_visits' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'active')),
            'completed_visits' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'completed')),
            'pending_visits' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'pending')),
            'total_entries' => count(array_filter($recentAccess, fn($a) => $a['access_type'] === 'entry')),
            'total_exits' => count(array_filter($recentAccess, fn($a) => $a['access_type'] === 'exit'))
        ];
        
        $data = [
            'title' => 'Consola de Guardia',
            'visits' => $todayVisits,
            'recentAccess' => array_slice($recentAccess, 0, 20),
            'stats' => $stats
        ];
        
        $this->view('guard/index', $data);
    }
    
    /**
     * Registrar acceso manual
     */
    public function manualAccess() {
        $data = [
            'title' => 'Registro Manual de Acceso',
            'error' => '',
            'success' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accessData = [
                'log_type' => $this->post('log_type'),
                'access_type' => $this->post('access_type'),
                'access_method' => 'manual',
                'name' => $this->post('name'),
                'vehicle_plate' => $this->post('vehicle_plate'),
                'property_id' => $this->post('property_id') ?: null,
                'guard_id' => $_SESSION['user_id'],
                'notes' => $this->post('notes')
            ];
            
            if (empty($accessData['name'])) {
                $data['error'] = 'El nombre es requerido';
            } else {
                if ($this->accessLogModel->create($accessData)) {
                    $_SESSION['success_message'] = 'Acceso registrado exitosamente';
                    $this->redirect('guard');
                } else {
                    $data['error'] = 'Error al registrar el acceso';
                }
            }
        }
        
        $this->view('guard/manual_access', $data);
    }
    
    /**
     * Escanear QR rápido
     */
    public function quickScan() {
        $data = [
            'title' => 'Escaneo Rápido QR',
            'result' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $qrCode = $this->post('qr_code');
            $visit = $this->visitModel->validateVisit($qrCode);
            
            if ($visit) {
                $visitDetails = $this->visitModel->findByQR($qrCode);
                
                // Registrar entrada automáticamente
                if ($visitDetails['status'] === 'pending') {
                    $this->visitModel->registerEntry($visitDetails['id'], $_SESSION['user_id']);
                    
                    $this->accessLogModel->create([
                        'log_type' => 'visit',
                        'reference_id' => $visitDetails['id'],
                        'access_type' => 'entry',
                        'access_method' => 'qr',
                        'name' => $visitDetails['visitor_name'],
                        'vehicle_plate' => $visitDetails['vehicle_plate'],
                        'guard_id' => $_SESSION['user_id']
                    ]);
                    
                    $data['result'] = [
                        'success' => true,
                        'message' => 'Entrada registrada exitosamente',
                        'visit' => $visitDetails
                    ];
                } else {
                    $data['result'] = [
                        'success' => true,
                        'message' => 'Visita ya registrada',
                        'visit' => $visitDetails
                    ];
                }
            } else {
                $data['result'] = [
                    'success' => false,
                    'message' => 'Código QR inválido o expirado'
                ];
            }
        }
        
        $this->view('guard/quick_scan', $data);
    }
    
    /**
     * Ver alertas activas
     */
    public function alerts() {
        $db = Database::getInstance()->getConnection();
        
        // Obtener filtros
        $filterType = $_GET['type'] ?? 'unauthorized_plate';
        $filterTime = $_GET['time'] ?? 'today';
        
        // Construir WHERE según el filtro de tiempo
        $timeCondition = match($filterTime) {
            'today' => "DATE(dp.captured_at) = CURDATE()",
            'week' => "dp.captured_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            'month' => "dp.captured_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            default => "1=1" // all
        };
        
        // Obtener placas no autorizadas
        $query = "
            SELECT dp.*, 
                   JSON_UNQUOTE(JSON_EXTRACT(dp.payload_json, '$.image_path')) as image_path
            FROM detected_plates dp
            WHERE dp.is_match = 0 
              AND dp.status IN ('new', 'processed')
              AND $timeCondition
            ORDER BY dp.captured_at DESC
            LIMIT 50
        ";
        
        $stmt = $db->query($query);
        $unauthorizedPlates = $stmt->fetchAll();
        
        // Contar alertas de hoy
        $stmt = $db->query("
            SELECT COUNT(*) as count 
            FROM detected_plates 
            WHERE is_match = 0 
              AND DATE(captured_at) = CURDATE()
        ");
        $todayCount = $stmt->fetch()['count'];
        
        // Contar total de no autorizadas
        $stmt = $db->query("
            SELECT COUNT(*) as count 
            FROM detected_plates 
            WHERE is_match = 0 
              AND status IN ('new', 'processed')
        ");
        $totalUnauthorized = $stmt->fetch()['count'];
        
        // Estadísticas
        $stats = [
            'unauthorized_plates' => $totalUnauthorized,
            'today_alerts' => $todayCount,
            'expired_visits' => 0,
            'recent_access' => 0
        ];
        
        $data = [
            'title' => 'Alertas Activas',
            'unauthorizedPlates' => $unauthorizedPlates,
            'otherAlerts' => [],
            'stats' => $stats,
            'filterType' => $filterType,
            'filterTime' => $filterTime
        ];
        
        $this->view('guard/alerts', $data);
    }
    
    /**
     * Registrar acceso manual (JSON response)
     */
    public function registerManualAccess() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        try {
            $logType = $this->post('log_type');
            $accessType = $this->post('access_type');
            $referenceId = $this->post('reference_id');
            $propertyId = $this->post('property_id');
            
            // Validaciones
            if (empty($logType) || empty($accessType)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de acceso requerido']);
                return;
            }
            
            // Datos del acceso
            $accessData = [
                'log_type' => $logType,
                'access_type' => $accessType,
                'access_method' => 'manual',
                'guard_id' => $_SESSION['user_id'],
                'notes' => $this->post('additional_notes')
            ];
            
            // Para emergencias, usar datos manuales
            if ($logType === 'emergency') {
                $accessData['name'] = $this->post('visitor_name');
                $accessData['vehicle_plate'] = $this->post('phone');
                $accessData['property_id'] = $propertyId;
                
                if (empty($accessData['name'])) {
                    echo json_encode(['success' => false, 'message' => 'Nombre del visitante requerido']);
                    return;
                }
            } else {
                // Para otros tipos, necesita reference_id
                if (empty($referenceId)) {
                    echo json_encode(['success' => false, 'message' => 'Debe seleccionar un registro']);
                    return;
                }
                $accessData['reference_id'] = $referenceId;
                $accessData['property_id'] = $propertyId;
            }
            
            // Registrar el acceso
            if ($this->accessLogModel->create($accessData)) {
                // Intentar activar dispositivo automáticamente (solo para entradas)
                $deviceActivated = false;
                $deviceMessage = '';
                
                if ($accessType === 'entry') {
                    $deviceResult = $this->activateAccessDevice($propertyId);
                    $deviceActivated = $deviceResult['activated'];
                    $deviceMessage = $deviceResult['message'];
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Acceso registrado exitosamente',
                    'access_type' => $accessType,
                    'device_activated' => $deviceActivated,
                    'device_message' => $deviceMessage
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar el acceso']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Buscar registros (JSON response)
     */
    public function search() {
        header('Content-Type: application/json');
        
        $type = $_GET['type'] ?? '';
        $query = $_GET['query'] ?? '';
        
        if (empty($type) || empty($query)) {
            echo json_encode(['success' => false, 'results' => [], 'message' => 'Parámetros faltantes']);
            return;
        }
        
        $results = [];
        $db = Database::getInstance()->getConnection();
        
        try {
            switch ($type) {
                case 'visit':
                    // Buscar visitas con resident y property info
                    $stmt = $db->prepare("
                        SELECT v.id, v.visitor_name as name, 
                               CONCAT('Prop: ', p.property_number, ' | ', u.first_name, ' ', u.last_name, ' | Válido: ', DATE_FORMAT(v.valid_from, '%d/%m %H:%i')) as details,
                               p.property_number as property,
                               p.id as property_id
                        FROM visits v
                        INNER JOIN residents r ON v.resident_id = r.id
                        INNER JOIN properties p ON r.property_id = p.id
                        INNER JOIN users u ON r.user_id = u.id
                        WHERE v.visitor_name LIKE ? 
                          AND v.status IN ('pending', 'active')
                          AND v.valid_until >= NOW()
                        ORDER BY v.valid_from DESC
                        LIMIT 15
                    ");
                    $stmt->execute(["%$query%"]);
                    break;
                    
                case 'resident':
                    // Buscar residentes con JOIN correcto a properties
                    $stmt = $db->prepare("
                        SELECT r.id, CONCAT(u.first_name, ' ', u.last_name) as name,
                               CONCAT('Prop: ', p.property_number, ' | Tel: ', COALESCE(u.phone, 'Sin teléfono')) as details,
                               p.property_number as property,
                               p.id as property_id
                        FROM residents r
                        INNER JOIN users u ON r.user_id = u.id
                        INNER JOIN properties p ON r.property_id = p.id
                        WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR p.property_number LIKE ?)
                          AND r.status = 'active'
                        ORDER BY u.first_name ASC
                        LIMIT 15
                    ");
                    $stmt->execute(["%$query%", "%$query%", "%$query%"]);
                    break;
                    
                case 'vehicle':
                    // Buscar vehículos con property info
                    $stmt = $db->prepare("
                        SELECT v.id, v.plate as name,
                               CONCAT(COALESCE(v.brand, ''), ' ', COALESCE(v.model, ''), ' - Prop: ', p.property_number, ' - ', u.first_name, ' ', u.last_name) as details,
                               p.property_number as property,
                               p.id as property_id
                        FROM vehicles v
                        INNER JOIN residents r ON v.resident_id = r.id
                        INNER JOIN properties p ON r.property_id = p.id
                        INNER JOIN users u ON r.user_id = u.id
                        WHERE v.plate LIKE ?
                          AND v.status = 'active'
                        ORDER BY v.plate ASC
                        LIMIT 15
                    ");
                    $stmt->execute(["%$query%"]);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'results' => [], 'message' => 'Tipo inválido']);
                    return;
            }
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log para debug
            error_log("Guard Search - Type: {$type}, Query: {$query}, Results: " . count($results));
            
            echo json_encode([
                'success' => true, 
                'results' => $results,
                'count' => count($results),
                'query' => $query,
                'type' => $type
            ]);
            
        } catch (Exception $e) {
            error_log("Guard Search Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'results' => []]);
        }
    }
    
    /**
     * Resolver alerta (JSON response)
     */
    public function resolveAlert() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $plateId = $this->post('plate_id');
        
        if (empty($plateId)) {
            echo json_encode(['success' => false, 'message' => 'ID de placa requerido']);
            return;
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE detected_plates SET status = 'resolved' WHERE id = ?");
            
            if ($stmt->execute([$plateId])) {
                echo json_encode(['success' => true, 'message' => 'Alerta resuelta']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Agregar nota a alerta (JSON response)
     */
    public function addNote() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $plateId = $this->post('plate_id');
        $note = $this->post('note');
        
        if (empty($plateId) || empty($note)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE detected_plates SET notes = ? WHERE id = ?");
            
            if ($stmt->execute([$note, $plateId])) {
                echo json_encode(['success' => true, 'message' => 'Nota agregada']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al agregar nota']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Activar dispositivo de acceso automáticamente
     */
    private function activateAccessDevice($propertyId = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Buscar dispositivo activo y habilitado
            // Si hay propertyId, buscar por sección de la propiedad, sino usar dispositivo por defecto
            if ($propertyId) {
                $stmt = $db->prepare("
                    SELECT d.* 
                    FROM access_devices d
                    INNER JOIN properties p ON d.branch = p.section
                    WHERE p.id = ? 
                      AND d.enabled = 1 
                      AND d.status = 'online'
                    ORDER BY d.id DESC
                    LIMIT 1
                ");
                $stmt->execute([$propertyId]);
                $device = $stmt->fetch();
                
                // Si no encuentra por sección, buscar dispositivo general
                if (!$device) {
                    $stmt = $db->query("
                        SELECT * FROM access_devices 
                        WHERE enabled = 1 
                          AND status = 'online'
                          AND (branch IS NULL OR branch = '')
                        ORDER BY id DESC
                        LIMIT 1
                    ");
                    $device = $stmt->fetch();
                }
            } else {
                // Sin propertyId, usar dispositivo general
                $stmt = $db->query("
                    SELECT * FROM access_devices 
                    WHERE enabled = 1 
                      AND status = 'online'
                    ORDER BY id DESC
                    LIMIT 1
                ");
                $device = $stmt->fetch();
            }
            
            if (!$device) {
                return [
                    'activated' => false,
                    'message' => 'No hay dispositivos disponibles'
                ];
            }
            
            // Activar el dispositivo según su tipo
            if ($device['device_type'] === 'shelly') {
                $result = $this->activateShellyDevice($device);
            } elseif ($device['device_type'] === 'hikvision') {
                $result = $this->activateHikvisionDevice($device);
            } else {
                return [
                    'activated' => false,
                    'message' => 'Tipo de dispositivo no soportado'
                ];
            }
            
            // Registrar acción en logs
            $deviceModel = $this->model('Device');
            $deviceModel->logAction(
                $device['id'], 
                'open', 
                $result['success'], 
                $result['success'] ? null : $result['message'],
                0
            );
            
            return [
                'activated' => $result['success'],
                'message' => $result['success'] 
                    ? "✅ {$device['device_name']} activado" 
                    : "❌ Error: {$result['message']}"
            ];
            
        } catch (Exception $e) {
            return [
                'activated' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Activar dispositivo Shelly
     */
    private function activateShellyDevice($device) {
        $authToken = $device['auth_token'];
        $deviceId = $device['device_id'];
        $cloudServer = $device['cloud_server'] ?: 'shelly-208-eu.shelly.cloud';
        $outputChannel = $device['output_channel'] ?? 0;
        $pulseDuration = $device['pulse_duration'] ?? 1000;
        $inverted = $device['inverted'] ?? 0;
        
        if (empty($authToken) || empty($deviceId)) {
            return ['success' => false, 'message' => 'Falta auth_token o device_id'];
        }
        
        // Limpiar el servidor
        $cloudServer = trim($cloudServer);
        $cloudServer = preg_replace('/:\d+.*$/', '', $cloudServer);
        $cloudServer = filter_var($cloudServer, FILTER_SANITIZE_URL);
        
        $turnOn = !$inverted;
        $url = "https://{$cloudServer}/device/relay/control";
        
        $postData = [
            'channel' => $outputChannel,
            'turn' => $turnOn ? 'on' : 'off',
            'id' => $deviceId,
            'auth_key' => $authToken
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $curlError];
        }
        
        if ($httpCode !== 200) {
            return ['success' => false, 'message' => "HTTP Error {$httpCode}"];
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['isok'])) {
            return ['success' => false, 'message' => 'Respuesta inválida del servidor'];
        }
        
        if (!$data['isok']) {
            return ['success' => false, 'message' => 'Shelly Cloud error'];
        }
        
        // Si se configuró duración de pulso, esperar y apagar
        if ($pulseDuration > 0 && $device['simultaneous'] == 0) {
            usleep($pulseDuration * 1000);
            
            $offData = [
                'channel' => $outputChannel,
                'turn' => $turnOn ? 'off' : 'on',
                'id' => $deviceId,
                'auth_key' => $authToken
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($offData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_exec($ch);
            curl_close($ch);
        }
        
        return ['success' => true, 'message' => 'Dispositivo activado'];
    }
    
    /**
     * Activar dispositivo HikVision
     */
    private function activateHikvisionDevice($device) {
        $ip = $device['ip_address'];
        $port = $device['port'] ?? 80;
        $username = $device['username'];
        $password = $device['password'];
        $doorNumber = $device['door_number'] ?? 1;
        
        if (empty($ip) || empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Falta configuración de HikVision'];
        }
        
        $url = "http://{$ip}:{$port}/ISAPI/AccessControl/RemoteControl/door/{$doorNumber}";
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' .
               '<RemoteControlDoor>' .
               '<cmd>open</cmd>' .
               '</RemoteControlDoor>';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/xml',
            'Content-Length: ' . strlen($xml)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $curlError];
        }
        
        if ($httpCode !== 200) {
            return ['success' => false, 'message' => "HTTP Error {$httpCode}"];
        }
        
        return ['success' => true, 'message' => 'Puerta abierta'];
    }
}
