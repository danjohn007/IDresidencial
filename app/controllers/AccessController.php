<?php
/**
 * Controlador de Control de Accesos
 */

class AccessController extends Controller {
    
    private $visitModel;
    private $accessLogModel;
    private $residentModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->visitModel = $this->model('Visit');
        $this->accessLogModel = $this->model('AccessLog');
        $this->residentModel = $this->model('Resident');
    }
    
    /**
     * Vista principal de control de accesos
     */
    public function index() {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        // Obtener visitas de hoy
        $todayVisits = $this->visitModel->getTodayVisits();
        
        // Obtener estadísticas
        $stats = [
            'total_today' => count($todayVisits),
            'active' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'active')),
            'completed' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'completed')),
            'pending' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'pending'))
        ];
        
        $data = [
            'title' => 'Control de Accesos',
            'visits' => $todayVisits,
            'stats' => $stats
        ];
        
        $this->view('access/index', $data);
    }
    
    /**
     * Crear nueva visita con QR
     */
    public function create() {
        $defaultVisitType = $this->get('visit_type', 'personal');
        if (!in_array($defaultVisitType, ['personal', 'proveedor', 'delivery', 'rappi', 'uber_eats', 'otro'], true)) {
            $defaultVisitType = 'personal';
        }

        $data = [
            'title' => 'Generar Pase de Visita',
            'error' => '',
            'success' => '',
            'defaultVisitType' => $defaultVisitType
        ];
        
        // Obtener información del residente
        $resident = null;
        if ($_SESSION['role'] === 'residente') {
            $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $visitData = [
                'resident_id' => $this->post('resident_id'),
                'visitor_name' => $this->post('visitor_name'),
                'visitor_id' => $this->post('visitor_id'),
                'visitor_phone' => $this->post('visitor_phone'),
                'vehicle_plate' => $this->post('vehicle_plate'),
                'visit_type' => $this->post('visit_type', 'personal'),
                'valid_from' => $this->post('valid_from'),
                'valid_until' => $this->post('valid_until'),
                'notes' => $this->post('notes'),
                'status' => 'pending'
            ];
            
            // Validaciones
            if (empty($visitData['visitor_name']) || empty($visitData['valid_from']) || empty($visitData['valid_until'])) {
                $data['error'] = 'Por favor, completa todos los campos requeridos';
            } else {
                // Generar código QR único
                $visitData['qr_code'] = $this->visitModel->generateUniqueQR();
                
                if ($this->visitModel->create($visitData)) {
                    // Generar imagen QR
                    $this->generateQRImage($visitData['qr_code']);
                    
                    $_SESSION['success_message'] = 'Pase de visita generado exitosamente';
                    $this->redirect('access/viewDetails/' . $visitData['qr_code']);
                } else {
                    $data['error'] = 'Error al generar el pase de visita';
                }
            }
        }
        
        $data['resident'] = $resident;
        $this->view('access/create', $data);
    }
    
    /**
     * Ver detalles de una visita
     */
    public function viewDetails($qrCode = null) {
        if (!$qrCode) {
            $this->redirect('access');
        }
        
        $visit = $this->visitModel->findByQR($qrCode);
        
        if (!$visit) {
            $_SESSION['error_message'] = 'Visita no encontrada';
            $this->redirect('access');
        }
        
        $data = [
            'title' => 'Detalles de Visita',
            'visit' => $visit,
            'qr_image' => $this->getQRImagePath($qrCode)
        ];
        
        $this->view('access/view', $data);
    }
    
    /**
     * Validar QR y registrar entrada
     */
    public function validate() {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        $db = Database::getInstance()->getConnection();

        // Load available areas for the selector
        $areasStmt = $db->query("SELECT DISTINCT area FROM access_devices WHERE area IS NOT NULL AND area != '' AND enabled = 1 ORDER BY area");
        $areas = $areasStmt->fetchAll(PDO::FETCH_COLUMN);

        // Also load device_areas catalogue if available
        try {
            $daStmt = $db->query("SELECT name FROM device_areas WHERE is_active = 1 ORDER BY name");
            $deviceAreas = $daStmt->fetchAll(PDO::FETCH_COLUMN);
            $areas = array_unique(array_merge($areas, $deviceAreas));
            sort($areas);
        } catch (Exception $e) {
            // Table may not exist yet; keep $areas from access_devices
        }

        // Restore last selected area from session
        $lastArea = $_SESSION['last_validate_area'] ?? '';

        $data = [
            'title' => 'Validar Acceso',
            'error' => '',
            'visit' => null,
            'areas' => $areas,
            'selected_area' => $lastArea
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $qrCode = $this->post('qr_code');
            $area   = trim($this->post('area', ''));

            // Persist selected area in session
            if ($area !== '') {
                $_SESSION['last_validate_area'] = $area;
                $data['selected_area'] = $area;
            }
            
            if (empty($qrCode)) {
                $data['error'] = 'Por favor, ingresa el código QR';
            } elseif (empty($area)) {
                $data['error'] = 'Por favor, selecciona el área donde te encuentras';
            } else {
                // 1. Check visits table
                $visit = $this->visitModel->validateVisit($qrCode);
                
                if ($visit) {
                    $data['visit'] = $this->visitModel->findByQR($qrCode);
                    $data['valid'] = true;
                    // Activate Shelly device for this area
                    $deviceResult = $this->activateAccessDevice($data['visit']['property_id'] ?? null, $area);
                    $data['device_activated'] = $deviceResult['activated'];
                    $data['device_message'] = $deviceResult['message'];
                } else {
                    // 2. Check resident_access_passes
                    $passStmt = $db->prepare("
                        SELECT rap.*,
                               u.first_name, u.last_name,
                               p.property_number, p.id as property_id
                        FROM resident_access_passes rap
                        INNER JOIN residents r ON rap.resident_id = r.id
                        INNER JOIN users u ON r.user_id = u.id
                        INNER JOIN properties p ON r.property_id = p.id
                        WHERE rap.qr_code = ?
                          AND rap.status = 'active'
                          AND NOW() BETWEEN rap.valid_from AND COALESCE(rap.valid_until, '9999-12-31')
                          AND (rap.max_uses = 0 OR rap.uses_count < rap.max_uses)
                    ");
                    $passStmt->execute([$qrCode]);
                    $pass = $passStmt->fetch();

                    if ($pass) {
                        // Increment usage counter; mark single_use passes as used when limit reached
                        $updStmt = $db->prepare("
                            UPDATE resident_access_passes
                            SET uses_count = uses_count + 1,
                                status = IF(max_uses > 0 AND (uses_count + 1) >= max_uses AND pass_type = 'single_use', 'used', status)
                            WHERE id = ?
                        ");
                        $updStmt->execute([$pass['id']]);

                        // Log the access
                        $this->accessLogModel->create([
                            'log_type' => 'resident_pass',
                            'reference_id' => $pass['id'],
                            'access_type' => 'entry',
                            'access_method' => 'qr',
                            'property_id' => $pass['property_id'] ?? null,
                            'name' => $pass['first_name'] . ' ' . $pass['last_name'],
                            'vehicle_plate' => null,
                            'guard_id' => $_SESSION['user_id']
                        ]);

                        $data['visit'] = [
                            'visitor_name'    => $pass['first_name'] . ' ' . $pass['last_name'],
                            'property_number' => $pass['property_number'],
                            'valid_from'      => $pass['valid_from'],
                            'valid_until'     => $pass['valid_until'] ?? 'Sin vencimiento',
                            'pass_type'       => $pass['pass_type']
                        ];
                        $data['valid'] = true;
                        $data['is_resident_pass'] = true;

                        // Activate Shelly device
                        $deviceResult = $this->activateAccessDevice($pass['property_id'] ?? null, $area);
                        $data['device_activated'] = $deviceResult['activated'];
                        $data['device_message'] = $deviceResult['message'];
                    } else {
                        $data['error'] = 'Código QR inválido o expirado';
                        $data['valid'] = false;
                    }
                }
            }
        }
        
        $this->view('access/validate', $data);
    }
    
    /**
     * Vista de placas detectadas
     */
    public function detectedPlates() {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        require_once APP_PATH . '/views/access/detected_plates.php';
    }
    
    /**
     * Guardar foto de identificación del visitante
     */
    public function saveIdentificationPhoto() {
        header('Content-Type: application/json');
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        try {
            $visitId = $this->post('visit_id');
            $photoData = $this->post('photo_data');
            
            if (empty($visitId) || empty($photoData)) {
                echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
                return;
            }
            
            // Decode base64 image
            $photoData = str_replace('data:image/png;base64,', '', $photoData);
            $photoData = str_replace(' ', '+', $photoData);
            $imageData = base64_decode($photoData);
            
            // Generate unique filename
            $filename = 'id_photo_' . $visitId . '_' . time() . '.png';
            $uploadPath = PUBLIC_PATH . '/uploads/id_photos/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            $filePath = $uploadPath . $filename;
            
            // Save image file
            if (file_put_contents($filePath, $imageData)) {
                $relativePath = 'uploads/id_photos/' . $filename;
                
                // Update visit record with photo path
                if ($this->visitModel->updateIdentificationPhoto($visitId, $relativePath)) {
                    echo json_encode([
                        'success' => true, 
                        'photo_url' => BASE_URL . '/' . $relativePath
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Error al actualizar base de datos']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al guardar archivo']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Registrar entrada
     */
    public function registerEntry($visitId) {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        if ($this->visitModel->registerEntry($visitId, $_SESSION['user_id'])) {
            // Registrar en bitácora
            $visit = $this->visitModel->findById($visitId);
            $this->accessLogModel->create([
                'log_type' => 'visit',
                'reference_id' => $visitId,
                'access_type' => 'entry',
                'access_method' => 'qr',
                'property_id' => null,
                'name' => $visit['visitor_name'],
                'vehicle_plate' => $visit['vehicle_plate'],
                'guard_id' => $_SESSION['user_id']
            ]);
            
            $_SESSION['success_message'] = 'Entrada registrada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al registrar entrada';
        }
        
        $this->redirect('access');
    }
    
    /**
     * Registrar acceso (entrada o salida) - JSON response para quick scana
     */
    public function registerAccess() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        $visitId = $this->post('visit_id');
        $accessType = $this->post('access_type', 'entry');
        
        if (empty($visitId)) {
            echo json_encode(['success' => false, 'message' => 'ID de visita requerido']);
            return;
        }
        
        // Obtener información de la visita
        $visit = $this->visitModel->findById($visitId);
        
        if (!$visit) {
            echo json_encode(['success' => false, 'message' => 'Visita no encontrada']);
            return;
        }
        
        // Registrar entrada o salida
        if ($accessType === 'entry') {
            $success = $this->visitModel->registerEntry($visitId, $_SESSION['user_id']);
        } else {
            $success = $this->visitModel->registerExit($visitId, $_SESSION['user_id']);
        }
        
        if (!$success) {
            echo json_encode(['success' => false, 'message' => 'Error al registrar acceso']);
            return;
        }
        
        // Registrar en bitácora
        $this->accessLogModel->create([
            'log_type' => 'visit',
            'reference_id' => $visitId,
            'access_type' => $accessType,
            'access_method' => 'qr',
            'property_id' => $visit['property_id'] ?? null,
            'name' => $visit['visitor_name'],
            'vehicle_plate' => $visit['vehicle_plate'],
            'guard_id' => $_SESSION['user_id']
        ]);
        
        // Activar dispositivo automáticamente (solo para entradas)
        $deviceActivated = false;
        $deviceMessage = '';
        
        if ($accessType === 'entry') {
            $area = $this->post('area');
            $deviceResult = $this->activateAccessDevice($visit['property_id'] ?? null, $area ?: null);
            $deviceActivated = $deviceResult['activated'];
            $deviceMessage = $deviceResult['message'];
        }
        
        echo json_encode([
            'success' => true,
            'message' => ucfirst($accessType) . ' registrada exitosamente',
            'device_activated' => $deviceActivated,
            'device_message' => $deviceMessage
        ]);
    }
    
    /**
     * Registrar salida
     */
    public function registerExit($visitId) {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        if ($this->visitModel->registerExit($visitId, $_SESSION['user_id'])) {
            // Registrar en bitácora
            $visit = $this->visitModel->findById($visitId);
            $this->accessLogModel->create([
                'log_type' => 'visit',
                'reference_id' => $visitId,
                'access_type' => 'exit',
                'access_method' => 'qr',
                'property_id' => null,
                'name' => $visit['visitor_name'],
                'vehicle_plate' => $visit['vehicle_plate'],
                'guard_id' => $_SESSION['user_id']
            ]);
            
            $_SESSION['success_message'] = 'Salida registrada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al registrar salida';
        }
        
        $this->redirect('access');
    }
    
    /**
     * Bitácora de accesos
     */
    public function logs() {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        $filters = [
            'date' => $this->get('date', date('Y-m-d')),
            'log_type' => $this->get('log_type'),
            'access_type' => $this->get('access_type'),
            'search' => $this->get('search')
        ];
        
        $logs = $this->accessLogModel->getAll($filters);
        
        $data = [
            'title' => 'Bitácora de Accesos',
            'logs' => $logs,
            'filters' => $filters
        ];
        
        $this->view('access/logs', $data);
    }
    
    /**
     * Generar imagen QR
     */
    private function generateQRImage($qrCode) {
        // Por simplicidad, usaremos un servicio externo de API para generar QR
        // En producción, se recomienda usar una biblioteca PHP como endroid/qr-code
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrCode);
        
        $qrImagePath = PUBLIC_PATH . '/uploads/qr/' . $qrCode . '.png';
        
        // Crear directorio si no existe
        if (!is_dir(PUBLIC_PATH . '/uploads/qr')) {
            mkdir(PUBLIC_PATH . '/uploads/qr', 0755, true);
        }
        
        // Descargar y guardar la imagen
        $imageData = @file_get_contents($qrUrl);
        if ($imageData) {
            file_put_contents($qrImagePath, $imageData);
        }
        
        return $qrImagePath;
    }
    
    /**
     * Obtener ruta de imagen QR
     */
    private function getQRImagePath($qrCode) {
        $path = '/uploads/qr/' . $qrCode . '.png';
        $fullPath = PUBLIC_PATH . $path;
        
        if (file_exists($fullPath)) {
            return PUBLIC_URL . $path;
        }
        
        // Si no existe, generarla
        $this->generateQRImage($qrCode);
        return PUBLIC_URL . $path;
    }
    
    /**
     * Mis visitas (para residentes)
     */
    public function myVisits() {
        $this->requireRole('residente');
        
        $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
        if (!$resident) {
            $_SESSION['error_message'] = 'No se encontró información de residente';
            $this->redirect('dashboard');
        }
        
        $visits = $this->visitModel->getByResident($resident['id']);
        
        $data = [
            'title' => 'Mis Visitas',
            'visits' => $visits,
            'resident' => $resident
        ];
        
        $this->view('access/my_visits', $data);
    }
    
    /**
     * Activar dispositivo de acceso automáticamente
     */
    private function activateAccessDevice($propertyId = null, $area = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $device = null;

            // Si se especificó área, buscar por área primero
            if ($area) {
                $stmt = $db->prepare("
                    SELECT * FROM access_devices
                    WHERE area = ?
                      AND enabled = 1
                      AND status = 'online'
                    ORDER BY id DESC
                    LIMIT 1
                ");
                $stmt->execute([$area]);
                $device = $stmt->fetch();
            }

            // Buscar dispositivo activo sin sucursal asignada como fallback por propiedad
            if (!$device) {
                $stmt = $db->query("
                    SELECT * FROM access_devices 
                    WHERE enabled = 1 
                      AND status = 'online'
                      AND branch_id IS NULL
                    ORDER BY id DESC
                    LIMIT 1
                ");
                $device = $stmt->fetch();
            }

            // Fallback: cualquier dispositivo activo
            if (!$device) {
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

    /**
     * Validar código QR via AJAX (para quick scan)
     * Verifica tanto visits como resident_access_passes
     */
    public function validateQR() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $this->requireRole(['superadmin', 'administrador', 'guardia']);

        $qrCode = trim($this->post('qr_code', ''));

        if (empty($qrCode)) {
            echo json_encode(['success' => false, 'message' => 'Código QR requerido']);
            return;
        }

        // Validar formato esperado
        if (!preg_match('/^VIS-\d{8}-[A-F0-9]{8}$/i', $qrCode)) {
            echo json_encode(['success' => false, 'message' => 'Código QR no válido. Formato esperado: VIS-YYYYMMDD-XXXXXXXX']);
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Primero buscar en visits (pases generados por admin/guardia)
        $visit = $this->visitModel->validateVisit($qrCode);
        if ($visit) {
            $details = $this->visitModel->findByQR($qrCode);
            echo json_encode([
                'success' => true,
                'type' => 'visit',
                'visit' => [
                    'id' => $details['id'],
                    'visitor_name' => $details['visitor_name'],
                    'property_number' => $details['property_number'],
                    'resident_name' => $details['first_name'] . ' ' . $details['last_name'],
                    'valid_from' => $details['valid_from'],
                    'valid_until' => $details['valid_until']
                ]
            ]);
            return;
        }

        // Buscar en resident_access_passes (pases generados por residentes)
        $stmt = $db->prepare("
            SELECT rap.*,
                   u.first_name, u.last_name,
                   p.property_number, p.id as property_id
            FROM resident_access_passes rap
            INNER JOIN residents r ON rap.resident_id = r.id
            INNER JOIN users u ON r.user_id = u.id
            INNER JOIN properties p ON r.property_id = p.id
            WHERE rap.qr_code = ?
              AND rap.status = 'active'
              AND NOW() BETWEEN rap.valid_from AND COALESCE(rap.valid_until, '9999-12-31')
              AND (rap.max_uses = 0 OR rap.uses_count < rap.max_uses)
              -- max_uses = 0 means unlimited uses (permanent pass)
        ");
        $stmt->execute([$qrCode]);
        $pass = $stmt->fetch();

        if ($pass) {
            // Incrementar contador de usos; si max_uses > 0 y se alcanzó el límite en single_use, marcar como usado
            $stmtUpdate = $db->prepare("
                UPDATE resident_access_passes
                SET uses_count = uses_count + 1,
                    status = IF(max_uses > 0 AND (uses_count + 1) >= max_uses AND pass_type = 'single_use', 'used', status)
                WHERE id = ?
            ");
            $stmtUpdate->execute([$pass['id']]);

            // Log the access entry in access_log
            $this->accessLogModel->create([
                'log_type' => 'resident_pass',
                'reference_id' => $pass['id'],
                'access_type' => 'entry',
                'access_method' => 'qr',
                'property_id' => $pass['property_id'] ?? null,
                'name' => $pass['first_name'] . ' ' . $pass['last_name'],
                'vehicle_plate' => null,
                'guard_id' => $_SESSION['user_id']
            ]);

            echo json_encode([
                'success' => true,
                'type' => 'resident_pass',
                'visit' => [
                    'id' => null,
                    'visitor_name' => $pass['first_name'] . ' ' . $pass['last_name'],
                    'property_number' => $pass['property_number'],
                    'resident_name' => $pass['first_name'] . ' ' . $pass['last_name'],
                    'valid_from' => $pass['valid_from'],
                    'valid_until' => $pass['valid_until'] ?? 'Sin vencimiento'
                ]
            ]);
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Código QR no válido o expirado']);
    }
}
