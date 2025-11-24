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
                SELECT r.id, u.first_name, u.last_name, u.email, u.phone, p.property_number
                FROM residents r
                INNER JOIN users u ON r.user_id = u.id
                LEFT JOIN properties p ON r.property_id = p.id
                WHERE u.role = 'residente' 
                  AND r.status = 'active'
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
    
    /**
     * Obtener comparación de placas para un residente
     */
    public function getPlateComparison($residentId = null) {
        header('Content-Type: application/json');
        
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'No autorizado - Debe iniciar sesión']);
            exit;
        }
        
        if (!$residentId) {
            echo json_encode(['success' => false, 'error' => 'ID de residente requerido']);
            exit;
        }
        
        $response = [
            'success' => true,
            'saved_plate' => null,
            'detected_plate' => null,
            'is_match' => false,
            'debug' => [
                'resident_id' => $residentId,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
        
        try {
            // Obtener placa guardada del residente (del vehículo registrado)
            $stmt = $this->db->prepare("
                SELECT v.plate, v.brand, v.model, v.color
                FROM vehicles v
                WHERE v.resident_id = ? AND v.status = 'active'
                ORDER BY v.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$residentId]);
            $savedVehicle = $stmt->fetch();
            
            $response['debug']['vehicles_found'] = $savedVehicle ? 'yes' : 'no';
            
            if ($savedVehicle) {
                $response['saved_plate'] = $savedVehicle['plate'];
                $response['vehicle_info'] = [
                    'brand' => $savedVehicle['brand'],
                    'model' => $savedVehicle['model'],
                    'color' => $savedVehicle['color']
                ];
            }
            
            // Obtener la última placa detectada (últimas 24 horas)
            $stmt = $this->db->prepare("
                SELECT plate_text, captured_at, is_match, status
                FROM detected_plates
                WHERE captured_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY captured_at DESC
                LIMIT 1
            ");
            $stmt->execute();
            $detectedPlate = $stmt->fetch();
            
            $response['debug']['detected_plates_found'] = $detectedPlate ? 'yes' : 'no';
            
            if ($detectedPlate) {
                $response['detected_plate'] = [
                    'plate_text' => $detectedPlate['plate_text'],
                    'captured_at' => date('d/m/Y H:i:s', strtotime($detectedPlate['captured_at'])),
                    'status' => $detectedPlate['status']
                ];
                
                // Comparar placas
                if ($response['saved_plate'] && 
                    strtoupper(trim($response['saved_plate'])) === strtoupper(trim($detectedPlate['plate_text']))) {
                    $response['is_match'] = true;
                }
            }
            
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error' => 'Error al obtener datos: ' . $e->getMessage(),
                'debug' => [
                    'resident_id' => $residentId,
                    'exception' => $e->getMessage()
                ]
            ];
        }
        
        echo json_encode($response);
        exit;
    }
}
