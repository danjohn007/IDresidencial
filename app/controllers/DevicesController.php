<?php
/**
 * Controlador de Dispositivos de Control de Acceso
 */

class DevicesController extends Controller {
    
    private $deviceModel;
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador']);
        $this->deviceModel = $this->model('Device');
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de dispositivos
     */
    public function index() {
        $filters = [
            'device_type' => $this->get('device_type'),
            'status' => $this->get('status')
        ];
        
        $devices = $this->deviceModel->getAll($filters);
        $stats = $this->deviceModel->getStats();
        
        $data = [
            'title' => 'Dispositivos',
            'devices' => $devices,
            'filters' => $filters,
            'stats' => $stats
        ];
        
        $this->view('devices/index', $data);
    }
    
    /**
     * Actualizar estados de dispositivos
     */
    public function updateStatus() {
        // Esta función se puede llamar vía AJAX para actualizar estados
        $devices = $this->deviceModel->getAll();
        
        foreach ($devices as $device) {
            // Aquí se implementaría la lógica para verificar el estado real
            // Por ahora solo retornamos el estado actual
            $this->deviceModel->updateStatus($device['id'], $device['status']);
        }
        
        $_SESSION['success_message'] = 'Estados actualizados';
        $this->redirect('devices');
    }
    
    /**
     * Crear dispositivo HikVision
     */
    public function createHikvision() {
        $data = [
            'title' => 'Nuevo Dispositivo HikVision',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deviceData = [
                'device_name' => $this->post('device_name'),
                'device_type' => 'hikvision',
                'device_id' => $this->post('device_id'),
                'ip_address' => $this->post('ip_address'),
                'port' => $this->post('port', 80),
                'username' => $this->post('username'),
                'password' => $this->post('password'),
                'door_number' => $this->post('door_number', 1),
                'branch_id' => $this->post('branch_id'),
                'location' => $this->post('location'),
                'enabled' => $this->post('enabled', 1)
            ];
            
            if ($this->deviceModel->create($deviceData)) {
                AuditController::log('create', 'Dispositivo HikVision creado: ' . $deviceData['device_name'], 'access_devices', null);
                $_SESSION['success_message'] = 'Dispositivo HikVision registrado exitosamente';
                $this->redirect('devices');
            } else {
                $data['error'] = 'Error al registrar el dispositivo. Verifique que el Device ID no esté duplicado.';
            }
        }
        
        // Obtener sucursales para el dropdown
        $stmt = $this->db->query("SELECT DISTINCT section FROM properties WHERE section IS NOT NULL AND section != '' ORDER BY section");
        $data['branches'] = $stmt->fetchAll();
        
        $this->view('devices/createHikvision', $data);
    }
    
    /**
     * Crear dispositivo Shelly
     */
    public function createShelly() {
        $data = [
            'title' => 'Nuevo Dispositivo Shelly',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deviceData = [
                'device_name' => $this->post('device_name'),
                'device_type' => 'shelly',
                'device_id' => $this->post('device_id'),
                'auth_token' => $this->post('auth_token'),
                'cloud_server' => $this->post('cloud_server', 'shelly-208-eu.shelly.cloud'),
                'branch_id' => $this->post('branch_id'),
                'location' => $this->post('location'),
                'area' => $this->post('area'),
                'input_channel' => $this->post('input_channel', 1),
                'output_channel' => $this->post('output_channel', 0),
                'pulse_duration' => $this->post('pulse_duration', 4000),
                'open_time' => $this->post('open_time', 5),
                'inverted' => $this->post('inverted', 0),
                'simultaneous' => $this->post('simultaneous', 0),
                'enabled' => $this->post('enabled', 1)
            ];
            
            if ($this->deviceModel->create($deviceData)) {
                AuditController::log('create', 'Dispositivo Shelly creado: ' . $deviceData['device_name'], 'access_devices', null);
                $_SESSION['success_message'] = 'Dispositivo Shelly registrado exitosamente';
                $this->redirect('devices');
            } else {
                $data['error'] = 'Error al registrar el dispositivo. Verifique que el Device ID no esté duplicado.';
            }
        }
        
        // Obtener sucursales para el dropdown
        $stmt = $this->db->query("SELECT DISTINCT section FROM properties WHERE section IS NOT NULL AND section != '' ORDER BY section");
        $data['branches'] = $stmt->fetchAll();
        
        $this->view('devices/createShelly', $data);
    }
    
    /**
     * Editar dispositivo
     */
    public function edit($id) {
        $device = $this->deviceModel->findById($id);
        
        if (!$device) {
            $_SESSION['error_message'] = 'Dispositivo no encontrado';
            $this->redirect('devices');
        }
        
        $data = [
            'title' => 'Editar Dispositivo',
            'device' => $device,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updateData = [
                'device_id' => $this->post('device_id'),
                'device_name' => $this->post('device_name'),
                'location' => $this->post('location'),
                'enabled' => $this->post('enabled', 0)
            ];
            
            // Agregar campos específicos según el tipo
            if ($device['device_type'] === 'hikvision') {
                $updateData['ip_address'] = $this->post('ip_address');
                $updateData['port'] = $this->post('port');
                $updateData['username'] = $this->post('username');
                if ($this->post('password')) {
                    $updateData['password'] = $this->post('password');
                }
                $updateData['door_number'] = $this->post('door_number');
            } else {
                $updateData['auth_token'] = $this->post('auth_token');
                $updateData['cloud_server'] = $this->post('cloud_server');
                $updateData['area'] = $this->post('area');
                $updateData['input_channel'] = $this->post('input_channel');
                $updateData['output_channel'] = $this->post('output_channel');
                $updateData['pulse_duration'] = $this->post('pulse_duration');
                $updateData['open_time'] = $this->post('open_time');
                $updateData['inverted'] = $this->post('inverted', 0);
                $updateData['simultaneous'] = $this->post('simultaneous', 0);
            }
            
            if ($this->deviceModel->update($id, $updateData)) {
                AuditController::log('update', 'Dispositivo actualizado: ' . $updateData['device_name'], 'access_devices', $id);
                $_SESSION['success_message'] = 'Dispositivo actualizado exitosamente';
                $this->redirect('devices');
            } else {
                $data['error'] = 'Error al actualizar el dispositivo';
            }
        }
        
        // Obtener sucursales para el dropdown
        $stmt = $this->db->query("SELECT DISTINCT section FROM properties WHERE section IS NOT NULL AND section != '' ORDER BY section");
        $data['branches'] = $stmt->fetchAll();
        
        $this->view('devices/edit', $data);
    }
    
    /**
     * Ver detalles del dispositivo
     */
    public function viewDetails($id) {
        $device = $this->deviceModel->findById($id);
        
        if (!$device) {
            $_SESSION['error_message'] = 'Dispositivo no encontrado';
            $this->redirect('devices');
        }
        
        $logs = $this->deviceModel->getActionLogs($id, 50);
        
        $data = [
            'title' => 'Detalles del Dispositivo',
            'device' => $device,
            'logs' => $logs
        ];
        
        $this->view('devices/viewDetails', $data);
    }
    
    /**
     * Probar dispositivo
     */
    public function test($id) {
        $device = $this->deviceModel->findById($id);
        
        if (!$device) {
            $_SESSION['error_message'] = 'Dispositivo no encontrado';
            $this->redirect('devices');
            return;
        }
        
        $startTime = microtime(true);
        $success = false;
        $errorMessage = '';
        
        try {
            if ($device['device_type'] === 'shelly') {
                // Probar dispositivo Shelly con activación real
                $result = $this->activateShelly($device);
                $success = $result['success'];
                $errorMessage = $result['message'] ?? '';
            } elseif ($device['device_type'] === 'hikvision') {
                // Probar dispositivo HikVision con activación real
                $result = $this->activateHikvision($device);
                $success = $result['success'];
                $errorMessage = $result['message'] ?? '';
            } else {
                $errorMessage = 'Tipo de dispositivo no soportado';
            }
        } catch (Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
        }
        
        $responseTime = round((microtime(true) - $startTime) * 1000); // en ms
        
        $this->deviceModel->logAction($id, 'test', $success, $errorMessage, $responseTime);
        
        if ($success) {
            $this->deviceModel->updateStatus($id, 'online');
            $_SESSION['success_message'] = "✅ Dispositivo activado exitosamente en {$responseTime}ms";
            AuditController::log('test', 'Dispositivo probado: ' . $device['device_name'], 'access_devices', $id);
        } else {
            $this->deviceModel->updateStatus($id, 'error');
            $_SESSION['error_message'] = "❌ Error: {$errorMessage}";
            AuditController::log('test', 'Error al probar dispositivo: ' . $device['device_name'] . ' - ' . $errorMessage, 'access_devices', $id);
        }
        
        $this->redirect('devices');
    }
    
    /**
     * Eliminar dispositivo
     */
    public function delete($id) {
        $device = $this->deviceModel->findById($id);
        
        if (!$device) {
            $_SESSION['error_message'] = 'Dispositivo no encontrado';
            $this->redirect('devices');
        }
        
        if ($this->deviceModel->delete($id)) {
            AuditController::log('delete', 'Dispositivo eliminado: ' . $device['device_name'], 'access_devices', $id);
            $_SESSION['success_message'] = 'Dispositivo eliminado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el dispositivo';
        }
        
        $this->redirect('devices');
    }
    
    /**
     * Ver dispositivos inhabilitados
     */
    public function disabled() {
        $devices = $this->deviceModel->getAll(['enabled' => 0]);
        
        $data = [
            'title' => 'Dispositivos Inhabilitados',
            'devices' => $devices
        ];
        
        $this->view('devices/disabled', $data);
    }
    
    /**
     * Abrir/Activar dispositivo
     */
    public function activate($id) {
        $device = $this->deviceModel->findById($id);
        
        if (!$device) {
            $this->json(['success' => false, 'message' => 'Dispositivo no encontrado'], 404);
            return;
        }
        
        $startTime = microtime(true);
        $success = false;
        $errorMessage = '';
        
        try {
            if ($device['device_type'] === 'shelly') {
                // Activar dispositivo Shelly
                $result = $this->activateShelly($device);
                $success = $result['success'];
                $errorMessage = $result['message'] ?? '';
            } elseif ($device['device_type'] === 'hikvision') {
                // Activar dispositivo HikVision
                $result = $this->activateHikvision($device);
                $success = $result['success'];
                $errorMessage = $result['message'] ?? '';
            }
        } catch (Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
        }
        
        $responseTime = round((microtime(true) - $startTime) * 1000); // en ms
        
        // Registrar acción
        $this->deviceModel->logAction($id, 'open', $success, $errorMessage, $responseTime);
        AuditController::log(
            'activate', 
            ($success ? 'Dispositivo activado: ' : 'Error al activar dispositivo: ') . $device['device_name'], 
            'access_devices', 
            $id
        );
        
        if ($success) {
            $this->deviceModel->updateStatus($id, 'online');
            $this->json(['success' => true, 'message' => 'Dispositivo activado exitosamente', 'responseTime' => $responseTime]);
        } else {
            $this->deviceModel->updateStatus($id, 'error');
            $this->json(['success' => false, 'message' => $errorMessage ?: 'Error al activar el dispositivo'], 500);
        }
    }
    
    /**
     * Activar dispositivo Shelly vía Cloud API
     */
    private function activateShelly($device) {
        $authToken = $device['auth_token'];
        $deviceId = $device['device_id'];
        $cloudServer = $device['cloud_server'] ?: 'shelly-208-eu.shelly.cloud';
        $outputChannel = $device['output_channel'] ?? 0;
        $pulseDuration = $device['pulse_duration'] ?? 1000;
        $inverted = $device['inverted'] ?? 0;
        
        if (empty($authToken) || empty($deviceId)) {
            return ['success' => false, 'message' => 'Falta auth_token o device_id'];
        }
        
        // Limpiar el servidor: remover puerto, path, espacios y caracteres invisibles
        // Ejemplo: "shelly-208-eu.shelly.cloud:6022/jrpc" -> "shelly-208-eu.shelly.cloud"
        $cloudServer = trim($cloudServer); // Remover espacios
        $cloudServer = preg_replace('/:\d+.*$/', '', $cloudServer); // Remover puerto y path
        $cloudServer = filter_var($cloudServer, FILTER_SANITIZE_URL); // Sanitizar URL
        
        // Determinar estado: on para pulso de apertura
        $turnOn = !$inverted; // Si no está invertido: on, si está invertido: off
        
        // URL de la API de Shelly Cloud - método POST para Gen2/Pro
        $url = "https://{$cloudServer}/device/relay/control";
        
        // Datos para POST
        $postData = [
            'channel' => $outputChannel,
            'turn' => $turnOn ? 'on' : 'off',
            'id' => $deviceId,
            'auth_key' => $authToken
        ];
        
        // Hacer petición HTTP POST
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
        
        // Log para debug
        error_log("Shelly API Request (POST): {$url}");
        error_log("Shelly API POST Data: " . json_encode($postData));
        error_log("Shelly API Response ({$httpCode}): {$response}");
        
        if ($curlError) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $curlError];
        }
        
        if ($httpCode !== 200) {
            return ['success' => false, 'message' => "HTTP Error {$httpCode}: {$response}"];
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['isok'])) {
            return ['success' => false, 'message' => 'Respuesta inválida del servidor'];
        }
        
        if (!$data['isok']) {
            $error = $data['errors'] ?? 'Error desconocido';
            return ['success' => false, 'message' => 'Shelly Cloud: ' . json_encode($error)];
        }
        
        // Si se configuró duración de pulso, esperar y apagar
        if ($pulseDuration > 0 && $device['simultaneous'] == 0) {
            usleep($pulseDuration * 1000); // Convertir ms a microsegundos
            
            // Apagar relay
            $offData = [
                'channel' => $outputChannel,
                'turn' => $turnOn ? 'off' : 'on', // Invertir estado
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
        
        return ['success' => true, 'message' => 'Dispositivo Shelly activado correctamente'];
    }
    
    /**
     * Activar dispositivo HikVision
     */
    private function activateHikvision($device) {
        $ip = $device['ip_address'];
        $port = $device['port'] ?? 80;
        $username = $device['username'];
        $password = $device['password'];
        $doorNumber = $device['door_number'] ?? 1;
        
        if (empty($ip) || empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Falta configuración de HikVision'];
        }
        
        // URL de la API de HikVision para abrir puerta
        $url = "http://{$ip}:{$port}/ISAPI/AccessControl/RemoteControl/door/{$doorNumber}";
        
        // XML para comando de apertura
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' .
               '<RemoteControlDoor>' .
               '<cmd>open</cmd>' .
               '</RemoteControlDoor>';
        
        // Hacer petición HTTP con autenticación Digest
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
        
        return ['success' => true, 'message' => 'Puerta HikVision abierta correctamente'];
    }
}
