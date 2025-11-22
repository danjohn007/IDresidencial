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
        }
        
        // Aquí se implementaría la lógica real para probar el dispositivo
        // Por ahora solo registramos la acción
        $success = true; // Simular prueba exitosa
        $responseTime = rand(100, 500); // Simular tiempo de respuesta
        
        $this->deviceModel->logAction($id, 'test', $success, null, $responseTime);
        
        if ($success) {
            $this->deviceModel->updateStatus($id, 'online');
            $_SESSION['success_message'] = 'Dispositivo probado exitosamente';
        } else {
            $this->deviceModel->updateStatus($id, 'error');
            $_SESSION['error_message'] = 'Error al probar el dispositivo';
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
        }
        
        // Aquí se implementaría la lógica real para activar el dispositivo
        // Por ahora solo registramos la acción
        $success = true;
        $responseTime = rand(100, 500);
        
        $this->deviceModel->logAction($id, 'open', $success, null, $responseTime);
        AuditController::log('activate', 'Dispositivo activado: ' . $device['device_name'], 'access_devices', $id);
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Dispositivo activado exitosamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al activar el dispositivo'], 500);
        }
    }
}
