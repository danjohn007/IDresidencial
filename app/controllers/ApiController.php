<?php
/**
 * API Controller - Handles API endpoints
 */

class ApiController extends Controller {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Global search endpoint
     */
    public function search() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $query = $this->get('q', '');
        
        if (strlen($query) < 2) {
            header('Content-Type: application/json');
            echo json_encode(['residents' => [], 'users' => []]);
            exit;
        }
        
        $searchTerm = '%' . $query . '%';
        $role = $_SESSION['role'];
        
        $results = [
            'residents' => [],
            'users' => []
        ];
        
        // Search residents (for admin and superadmin)
        if (in_array($role, ['superadmin', 'administrador'])) {
            $stmt = $this->db->prepare("
                SELECT u.id, u.first_name, u.last_name, u.email, u.phone, p.property_number,
                       r.id as resident_id
                FROM users u
                INNER JOIN residents r ON u.id = r.user_id
                LEFT JOIN properties p ON r.property_id = p.id
                WHERE u.role = 'residente' 
                  AND u.status = 'active'
                  AND (
                      u.first_name LIKE ? OR 
                      u.last_name LIKE ? OR 
                      u.email LIKE ? OR 
                      u.phone LIKE ? OR
                      p.property_number LIKE ?
                  )
                LIMIT 10
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $results['residents'] = $stmt->fetchAll();
            
            // Search other users
            $stmt = $this->db->prepare("
                SELECT id, first_name, last_name, email, phone, role
                FROM users
                WHERE role != 'residente'
                  AND status = 'active'
                  AND (
                      first_name LIKE ? OR 
                      last_name LIKE ? OR 
                      email LIKE ? OR 
                      phone LIKE ?
                  )
                LIMIT 5
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $results['users'] = $stmt->fetchAll();
        }
        
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}
