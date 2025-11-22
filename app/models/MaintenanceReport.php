<?php
/**
 * Modelo de Reportes de Mantenimiento
 */

class MaintenanceReport {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO maintenance_reports (resident_id, property_id, category, title, 
                description, priority, location, assigned_to, status, photos) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['resident_id'], $data['property_id'] ?? null, $data['category'],
            $data['title'], $data['description'], $data['priority'] ?? 'media',
            $data['location'] ?? null, $data['assigned_to'] ?? null,
            $data['status'] ?? 'pendiente', $data['photos'] ?? null
        ]);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   u.first_name, u.last_name,
                   p.property_number, p.section,
                   assigned.first_name as assigned_first_name, assigned.last_name as assigned_last_name
            FROM maintenance_reports m
            JOIN residents r ON m.resident_id = r.id
            JOIN users u ON r.user_id = u.id
            LEFT JOIN properties p ON m.property_id = p.id
            LEFT JOIN users assigned ON m.assigned_to = assigned.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = "m.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['category'])) {
            $where[] = "m.category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['priority'])) {
            $where[] = "m.priority = ?";
            $params[] = $filters['priority'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT m.*, u.first_name, u.last_name, p.property_number
                FROM maintenance_reports m
                JOIN residents r ON m.resident_id = r.id
                JOIN users u ON r.user_id = u.id
                LEFT JOIN properties p ON m.property_id = p.id
                $whereClause
                ORDER BY 
                    CASE m.priority 
                        WHEN 'urgente' THEN 1
                        WHEN 'alta' THEN 2
                        WHEN 'media' THEN 3
                        WHEN 'baja' THEN 4
                    END,
                    m.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        $sql = "UPDATE maintenance_reports SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE maintenance_reports SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public function getByResident($residentId) {
        $stmt = $this->db->prepare("
            SELECT m.* 
            FROM maintenance_reports m
            WHERE m.resident_id = ?
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$residentId]);
        return $stmt->fetchAll();
    }
}
