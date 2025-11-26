<?php
/**
 * Modelo de Bitácora de Accesos
 */

class AccessLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear registro de acceso
     */
    public function create($data) {
        $sql = "INSERT INTO access_logs (log_type, reference_id, access_type, access_method, 
                property_id, name, vehicle_plate, guard_id, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['log_type'],
            $data['reference_id'] ?? null,
            $data['access_type'],
            $data['access_method'],
            $data['property_id'] ?? null,
            $data['name'] ?? null,
            $data['vehicle_plate'] ?? null,
            $data['guard_id'] ?? null,
            $data['notes'] ?? null
        ]);
    }
    
    /**
     * Obtener todos los accesos
     */
    public function getAll($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['log_type'])) {
            $where[] = "log_type = ?";
            $params[] = $filters['log_type'];
        }
        
        if (!empty($filters['access_type'])) {
            $where[] = "access_type = ?";
            $params[] = $filters['access_type'];
        }
        
        if (!empty($filters['date'])) {
            $where[] = "DATE(timestamp) = ?";
            $params[] = $filters['date'];
        }
        
        if (!empty($filters['property_id'])) {
            $where[] = "property_id = ?";
            $params[] = $filters['property_id'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(name LIKE ? OR vehicle_plate LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT a.*, 
                       p.property_number, p.section,
                       g.first_name as guard_first_name, g.last_name as guard_last_name
                FROM access_logs a
                LEFT JOIN properties p ON a.property_id = p.id
                LEFT JOIN users g ON a.guard_id = g.id
                $whereClause
                ORDER BY a.timestamp DESC
                LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener accesos de hoy
     */
    public function getToday() {
        $stmt = $this->db->query("
            SELECT a.*, 
                   p.property_number, p.section
            FROM access_logs a
            LEFT JOIN properties p ON a.property_id = p.id
            WHERE DATE(a.timestamp) = CURDATE()
            ORDER BY a.timestamp DESC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas
     */
    public function getStats($date = null) {
        $dateFilter = $date ? "WHERE DATE(timestamp) = ?" : "WHERE DATE(timestamp) = CURDATE()";
        
        $sql = "SELECT 
                    log_type,
                    access_type,
                    COUNT(*) as total
                FROM access_logs
                $dateFilter
                GROUP BY log_type, access_type";
        
        $stmt = $this->db->prepare($sql);
        if ($date) {
            $stmt->execute([$date]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar en bitácora
     */
    public function search($term, $startDate = null, $endDate = null) {
        $where = ["(name LIKE ? OR vehicle_plate LIKE ?)"];
        $params = ["%$term%", "%$term%"];
        
        if ($startDate) {
            $where[] = "DATE(timestamp) >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $where[] = "DATE(timestamp) <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT a.*, 
                       p.property_number, p.section
                FROM access_logs a
                LEFT JOIN properties p ON a.property_id = p.id
                WHERE $whereClause
                ORDER BY a.timestamp DESC
                LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
