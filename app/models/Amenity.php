<?php
/**
 * Modelo de Amenidades
 */

class Amenity {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO amenities (name, description, capacity, amenity_type, hourly_rate, 
                hours_open, hours_close, days_available, requires_payment, status, photo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'], $data['description'], $data['capacity'], $data['amenity_type'],
            $data['hourly_rate'], $data['hours_open'], $data['hours_close'], 
            $data['days_available'], $data['requires_payment'], $data['status'], $data['photo']
        ]);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM amenities WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll($status = 'active') {
        if ($status) {
            $stmt = $this->db->prepare("SELECT * FROM amenities WHERE status = ? ORDER BY name");
            $stmt->execute([$status]);
        } else {
            $stmt = $this->db->query("SELECT * FROM amenities ORDER BY name");
        }
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
        $sql = "UPDATE amenities SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM amenities WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
