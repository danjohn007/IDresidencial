<?php
/**
 * Modelo de Usuario
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener usuario por ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener usuario por username
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener usuario por email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Crear nuevo usuario
     */
    public function create($data) {
        $sql = "INSERT INTO users (username, email, password, role, first_name, last_name, phone, house_number, status, is_vigilance_committee) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'residente',
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $data['house_number'] ?? null,
            $data['status'] ?? 'active',
            $data['is_vigilance_committee'] ?? 0
        ]);
    }
    
    /**
     * Actualizar usuario
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key === 'password' && !empty($value)) {
                $fields[] = "$key = ?";
                $values[] = password_hash($value, PASSWORD_DEFAULT);
            } elseif ($key !== 'password' && $key !== 'id') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Verificar password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Actualizar último login
     */
    public function updateLastLogin($id) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Obtener todos los usuarios
     */
    public function getAll($role = null) {
        if ($role) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
            $stmt->execute([$role]);
        } else {
            $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        }
        return $stmt->fetchAll();
    }
    
    /**
     * Cambiar estado del usuario
     */
    public function changeStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * Contar usuarios por rol
     */
    public function countByRole($role) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE role = ?");
        $stmt->execute([$role]);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Contar usuarios con filtros opcionales
     */
    public function count($filters = []) {
        $where = [];
        $params = [];
        
        foreach ($filters as $key => $value) {
            $where[] = "$key = ?";
            $params[] = $value;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users $whereClause");
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }
}
