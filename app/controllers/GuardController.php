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
                echo json_encode([
                    'success' => true, 
                    'message' => 'Acceso registrado exitosamente',
                    'access_type' => $accessType
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
}
