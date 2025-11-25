<?php
/**
 * Modelo de Visitas
 */

class Visit {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear nueva visita
     */
    public function create($data) {
        $sql = "INSERT INTO visits (resident_id, visitor_name, visitor_id, visitor_phone, 
                vehicle_plate, visit_type, qr_code, valid_from, valid_until, status, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['resident_id'],
            $data['visitor_name'],
            $data['visitor_id'] ?? null,
            $data['visitor_phone'] ?? null,
            $data['vehicle_plate'] ?? null,
            $data['visit_type'] ?? 'personal',
            $data['qr_code'],
            $data['valid_from'],
            $data['valid_until'],
            $data['status'] ?? 'pending',
            $data['notes'] ?? null
        ]);
    }
    
    /**
     * Obtener visita por ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT v.*, 
                   u.first_name, u.last_name, u.phone as resident_phone,
                   p.property_number, p.street, p.section
            FROM visits v
            JOIN residents r ON v.resident_id = r.id
            JOIN users u ON r.user_id = u.id
            JOIN properties p ON r.property_id = p.id
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener visita por código QR
     */
    public function findByQR($qrCode) {
        $stmt = $this->db->prepare("
            SELECT v.*, 
                   u.first_name, u.last_name, u.phone as resident_phone,
                   p.property_number, p.street, p.section
            FROM visits v
            JOIN residents r ON v.resident_id = r.id
            JOIN users u ON r.user_id = u.id
            JOIN properties p ON r.property_id = p.id
            WHERE v.qr_code = ?
        ");
        $stmt->execute([$qrCode]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener todas las visitas
     */
    public function getAll($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = "v.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date'])) {
            $where[] = "DATE(v.valid_from) = ?";
            $params[] = $filters['date'];
        }
        
        if (!empty($filters['property_id'])) {
            $where[] = "r.property_id = ?";
            $params[] = $filters['property_id'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT v.*, 
                       u.first_name, u.last_name,
                       p.property_number, p.section
                FROM visits v
                JOIN residents r ON v.resident_id = r.id
                JOIN users u ON r.user_id = u.id
                JOIN properties p ON r.property_id = p.id
                $whereClause
                ORDER BY v.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener visitas de hoy
     */
    public function getTodayVisits() {
        $stmt = $this->db->query("
            SELECT v.*, 
                   u.first_name, u.last_name,
                   p.property_number, p.section
            FROM visits v
            JOIN residents r ON v.resident_id = r.id
            JOIN users u ON r.user_id = u.id
            JOIN properties p ON r.property_id = p.id
            WHERE DATE(v.valid_from) = CURDATE()
            ORDER BY v.valid_from DESC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener visitas por residente
     */
    public function getByResident($residentId) {
        $stmt = $this->db->prepare("
            SELECT v.* 
            FROM visits v
            WHERE v.resident_id = ?
            ORDER BY v.created_at DESC
        ");
        $stmt->execute([$residentId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Registrar entrada
     */
    public function registerEntry($id, $guardId) {
        $stmt = $this->db->prepare("
            UPDATE visits 
            SET entry_time = NOW(), 
                guard_entry_id = ?, 
                status = 'active' 
            WHERE id = ?
        ");
        return $stmt->execute([$guardId, $id]);
    }
    
    /**
     * Registrar salida
     */
    public function registerExit($id, $guardId) {
        $stmt = $this->db->prepare("
            UPDATE visits 
            SET exit_time = NOW(), 
                guard_exit_id = ?, 
                status = 'completed' 
            WHERE id = ?
        ");
        return $stmt->execute([$guardId, $id]);
    }
    
    /**
     * Actualizar estado
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE visits SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * Generar código QR único
     */
    public function generateUniqueQR() {
        do {
            $qrCode = 'VIS-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $stmt = $this->db->prepare("SELECT id FROM visits WHERE qr_code = ?");
            $stmt->execute([$qrCode]);
        } while ($stmt->fetch());
        
        return $qrCode;
    }
    
    /**
     * Validar visita (verificar si está vigente)
     */
    public function validateVisit($qrCode) {
        $stmt = $this->db->prepare("
            SELECT * FROM visits 
            WHERE qr_code = ? 
            AND NOW() BETWEEN valid_from AND valid_until
            AND status IN ('pending', 'active')
        ");
        $stmt->execute([$qrCode]);
        return $stmt->fetch();
    }
    
    /**
     * Marcar visitas expiradas
     */
    public function markExpired() {
        $stmt = $this->db->query("
            UPDATE visits 
            SET status = 'expired' 
            WHERE status IN ('pending', 'active') 
            AND NOW() > valid_until
        ");
        return $stmt->rowCount();
    }
    
    /**
     * Actualizar foto de identificación
     */
    public function updateIdentificationPhoto($id, $photoPath) {
        $stmt = $this->db->prepare("
            UPDATE visits 
            SET identification_photo = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$photoPath, $id]);
    }
}
