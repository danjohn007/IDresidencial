<?php
/**
 * Modelo de Reservaciones
 */

class Reservation {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO reservations (amenity_id, resident_id, reservation_date, start_time, 
                end_time, guests_count, amount, payment_status, status, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['amenity_id'], $data['resident_id'], $data['reservation_date'],
            $data['start_time'], $data['end_time'], $data['guests_count'] ?? 0,
            $data['amount'] ?? 0, $data['payment_status'] ?? 'pending',
            $data['status'] ?? 'pending', $data['notes'] ?? null
        ]);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT r.*, a.name as amenity_name, u.first_name, u.last_name, p.property_number
            FROM reservations r
            JOIN amenities a ON r.amenity_id = a.id
            JOIN residents res ON r.resident_id = res.id
            JOIN users u ON res.user_id = u.id
            JOIN properties p ON res.property_id = p.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['amenity_id'])) {
            $where[] = "r.amenity_id = ?";
            $params[] = $filters['amenity_id'];
        }
        
        if (!empty($filters['date'])) {
            $where[] = "r.reservation_date = ?";
            $params[] = $filters['date'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT r.*, a.name as amenity_name, u.first_name, u.last_name, p.property_number
                FROM reservations r
                JOIN amenities a ON r.amenity_id = a.id
                JOIN residents res ON r.resident_id = res.id
                JOIN users u ON res.user_id = u.id
                JOIN properties p ON res.property_id = p.id
                $whereClause
                ORDER BY r.reservation_date DESC, r.start_time DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function checkAvailability($amenityId, $date, $startTime, $endTime, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM reservations 
                WHERE amenity_id = ? AND reservation_date = ? 
                AND status IN ('pending', 'confirmed')
                AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
        
        if ($excludeId) {
            $sql .= " AND id != ?";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = [$amenityId, $date, $endTime, $startTime, $endTime, $startTime];
        if ($excludeId) $params[] = $excludeId;
        
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] == 0;
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
