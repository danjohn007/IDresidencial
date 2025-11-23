<?php
/**
 * Modelo de Dispositivos de Control de Acceso
 */

class Device {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los dispositivos
     */
    public function getAll($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['device_type'])) {
            $where[] = "device_type = ?";
            $params[] = $filters['device_type'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['enabled'])) {
            $where[] = "enabled = ?";
            $params[] = $filters['enabled'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("
            SELECT d.*, u.username as created_by_name
            FROM access_devices d
            LEFT JOIN users u ON d.created_by = u.id
            $whereClause
            ORDER BY d.created_at DESC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener dispositivo por ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT d.*, u.username as created_by_name
            FROM access_devices d
            LEFT JOIN users u ON d.created_by = u.id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Crear nuevo dispositivo
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO access_devices (
                    device_name, device_type, device_id, ip_address, port,
                    username, password, auth_token, cloud_server,
                    input_channel, output_channel, pulse_duration, open_time,
                    inverted, simultaneous, door_number, branch_id,
                    location, area, enabled, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $data['device_name'],
                $data['device_type'],
                $data['device_id'],
                $data['ip_address'] ?? null,
                $data['port'] ?? 80,
                $data['username'] ?? null,
                $data['password'] ?? null,
                $data['auth_token'] ?? null,
                $data['cloud_server'] ?? null,
                $data['input_channel'] ?? 1,
                $data['output_channel'] ?? 0,
                $data['pulse_duration'] ?? 4000,
                $data['open_time'] ?? 5,
                $data['inverted'] ?? 0,
                $data['simultaneous'] ?? 0,
                $data['door_number'] ?? null,
                $data['branch_id'] ?? null,
                $data['location'] ?? null,
                $data['area'] ?? null,
                $data['enabled'] ?? 1,
                $_SESSION['user_id'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Error creating device: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar dispositivo
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "$key = ?";
                    $params[] = $value;
                }
            }
            
            $params[] = $id;
            
            $stmt = $this->db->prepare("
                UPDATE access_devices 
                SET " . implode(', ', $fields) . "
                WHERE id = ?
            ");
            
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Error updating device: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar dispositivo
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM access_devices WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error deleting device: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar estado del dispositivo
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("
            UPDATE access_devices 
            SET status = ?, last_online = IF(? = 'online', NOW(), last_online)
            WHERE id = ?
        ");
        return $stmt->execute([$status, $status, $id]);
    }
    
    /**
     * Registrar acción del dispositivo
     */
    public function logAction($deviceId, $action, $success = true, $errorMessage = null, $responseTime = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO device_action_logs (
                    device_id, action, action_by, success, error_message,
                    response_time, ip_address
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $deviceId,
                $action,
                $_SESSION['user_id'] ?? null,
                $success,
                $errorMessage,
                $responseTime,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Error logging device action: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener logs de acciones de un dispositivo
     */
    public function getActionLogs($deviceId, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT dal.*, u.username as action_by_name
            FROM device_action_logs dal
            LEFT JOIN users u ON dal.action_by = u.id
            WHERE dal.device_id = ?
            ORDER BY dal.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$deviceId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas de dispositivos
     */
    public function getStats() {
        $stats = [
            'total' => 0,
            'online' => 0,
            'offline' => 0,
            'hikvision' => 0,
            'shelly' => 0,
            'enabled' => 0,
            'disabled' => 0
        ];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM access_devices");
        $stats['total'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM access_devices WHERE status = 'online'");
        $stats['online'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM access_devices WHERE status = 'offline'");
        $stats['offline'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM access_devices WHERE device_type = 'hikvision'");
        $stats['hikvision'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM access_devices WHERE device_type = 'shelly'");
        $stats['shelly'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM access_devices WHERE enabled = 1");
        $stats['enabled'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM access_devices WHERE enabled = 0");
        $stats['disabled'] = $stmt->fetch()['total'];
        
        return $stats;
    }
}
