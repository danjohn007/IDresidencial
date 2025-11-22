<?php
/**
 * Modelo de Residentes
 */

class Resident {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear nuevo residente
     */
    public function create($data) {
        $sql = "INSERT INTO residents (user_id, property_id, relationship, contract_start, 
                contract_end, is_primary, documents_path, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['user_id'],
            $data['property_id'],
            $data['relationship'] ?? 'propietario',
            $data['contract_start'] ?? null,
            $data['contract_end'] ?? null,
            $data['is_primary'] ?? false,
            $data['documents_path'] ?? null,
            $data['status'] ?? 'active'
        ]);
    }
    
    /**
     * Obtener residente por ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   u.username, u.email, u.first_name, u.last_name, u.phone, u.photo,
                   p.property_number, p.street, p.section, p.tower
            FROM residents r
            JOIN users u ON r.user_id = u.id
            JOIN properties p ON r.property_id = p.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener residente por user_id
     */
    public function findByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   p.property_number, p.street, p.section, p.tower
            FROM residents r
            JOIN properties p ON r.property_id = p.id
            WHERE r.user_id = ? AND r.is_primary = 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener todos los residentes
     */
    public function getAll($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['property_id'])) {
            $where[] = "r.property_id = ?";
            $params[] = $filters['property_id'];
        }
        
        if (!empty($filters['relationship'])) {
            $where[] = "r.relationship = ?";
            $params[] = $filters['relationship'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT r.*, 
                       u.username, u.email, u.first_name, u.last_name, u.phone,
                       p.property_number, p.street, p.section, p.tower
                FROM residents r
                JOIN users u ON r.user_id = u.id
                JOIN properties p ON r.property_id = p.id
                $whereClause
                ORDER BY p.property_number, r.is_primary DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener residentes de una propiedad
     */
    public function getByProperty($propertyId) {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   u.first_name, u.last_name, u.email, u.phone
            FROM residents r
            JOIN users u ON r.user_id = u.id
            WHERE r.property_id = ?
            ORDER BY r.is_primary DESC
        ");
        $stmt->execute([$propertyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Actualizar residente
     */
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
        $sql = "UPDATE residents SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Cambiar estado
     */
    public function changeStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE residents SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * Contar residentes
     */
    public function count($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['relationship'])) {
            $where[] = "relationship = ?";
            $params[] = $filters['relationship'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM residents $whereClause");
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
}
