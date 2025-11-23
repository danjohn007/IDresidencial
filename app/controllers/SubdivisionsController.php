<?php
/**
 * Controlador de Fraccionamientos/Subdivisiones
 */

require_once APP_PATH . '/controllers/AuditController.php';

class SubdivisionsController extends Controller {
    
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin']);
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de fraccionamientos
     */
    public function index() {
        $stmt = $this->db->query("
            SELECT s.*,
                   COUNT(DISTINCT p.id) as property_count,
                   COUNT(DISTINCT r.id) as resident_count
            FROM subdivisions s
            LEFT JOIN properties p ON s.id = p.subdivision_id
            LEFT JOIN residents r ON s.id = r.subdivision_id
            GROUP BY s.id
            ORDER BY s.name
        ");
        $subdivisions = $stmt->fetchAll();
        
        $data = [
            'title' => 'Fraccionamientos',
            'subdivisions' => $subdivisions
        ];
        
        $this->view('subdivisions/index', $data);
    }
    
    /**
     * Crear nuevo fraccionamiento
     */
    public function create() {
        $data = [
            'title' => 'Nuevo Fraccionamiento',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subdivisionData = [
                'name' => $this->post('name'),
                'description' => $this->post('description'),
                'address' => $this->post('address'),
                'city' => $this->post('city'),
                'state' => $this->post('state'),
                'postal_code' => $this->post('postal_code'),
                'phone' => $this->post('phone'),
                'email' => $this->post('email'),
                'status' => 'active'
            ];
            
            // Validación básica
            if (empty($subdivisionData['name'])) {
                $data['error'] = 'El nombre del fraccionamiento es requerido';
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO subdivisions (name, description, address, city, state, postal_code, phone, email, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute(array_values($subdivisionData))) {
                    $subdivisionId = $this->db->lastInsertId();
                    AuditController::log('create', 'Fraccionamiento creado: ' . $subdivisionData['name'], 'subdivisions', $subdivisionId);
                    $_SESSION['success_message'] = 'Fraccionamiento creado exitosamente';
                    $this->redirect('subdivisions');
                } else {
                    $data['error'] = 'Error al crear el fraccionamiento';
                }
            }
        }
        
        $this->view('subdivisions/create', $data);
    }
    
    /**
     * Ver detalles de un fraccionamiento
     */
    public function viewDetails($id) {
        $stmt = $this->db->prepare("SELECT * FROM subdivisions WHERE id = ?");
        $stmt->execute([$id]);
        $subdivision = $stmt->fetch();
        
        if (!$subdivision) {
            $_SESSION['error_message'] = 'Fraccionamiento no encontrado';
            $this->redirect('subdivisions');
        }
        
        // Obtener estadísticas
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM properties WHERE subdivision_id = ?");
        $stmt->execute([$id]);
        $propertyCount = $stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM residents WHERE subdivision_id = ?");
        $stmt->execute([$id]);
        $residentCount = $stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM vehicles WHERE subdivision_id = ?");
        $stmt->execute([$id]);
        $vehicleCount = $stmt->fetch()['count'];
        
        // Obtener propiedades
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   COUNT(DISTINCT r.id) as resident_count
            FROM properties p
            LEFT JOIN residents r ON p.id = r.property_id AND r.status = 'active'
            WHERE p.subdivision_id = ?
            GROUP BY p.id
            ORDER BY p.property_number
        ");
        $stmt->execute([$id]);
        $properties = $stmt->fetchAll();
        
        $data = [
            'title' => 'Detalles de Fraccionamiento',
            'subdivision' => $subdivision,
            'stats' => [
                'properties' => $propertyCount,
                'residents' => $residentCount,
                'vehicles' => $vehicleCount
            ],
            'properties' => $properties
        ];
        
        $this->view('subdivisions/view', $data);
    }
    
    /**
     * Editar fraccionamiento
     */
    public function edit($id) {
        $stmt = $this->db->prepare("SELECT * FROM subdivisions WHERE id = ?");
        $stmt->execute([$id]);
        $subdivision = $stmt->fetch();
        
        if (!$subdivision) {
            $_SESSION['error_message'] = 'Fraccionamiento no encontrado';
            $this->redirect('subdivisions');
        }
        
        $data = [
            'title' => 'Editar Fraccionamiento',
            'subdivision' => $subdivision,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subdivisionData = [
                'name' => $this->post('name'),
                'description' => $this->post('description'),
                'address' => $this->post('address'),
                'city' => $this->post('city'),
                'state' => $this->post('state'),
                'postal_code' => $this->post('postal_code'),
                'phone' => $this->post('phone'),
                'email' => $this->post('email')
            ];
            
            // Validación básica
            if (empty($subdivisionData['name'])) {
                $data['error'] = 'El nombre del fraccionamiento es requerido';
            } else {
                $stmt = $this->db->prepare("
                    UPDATE subdivisions 
                    SET name = ?, description = ?, address = ?, city = ?, state = ?, 
                        postal_code = ?, phone = ?, email = ?
                    WHERE id = ?
                ");
                
                $params = array_values($subdivisionData);
                $params[] = $id;
                
                if ($stmt->execute($params)) {
                    AuditController::log('update', 'Fraccionamiento actualizado: ' . $subdivisionData['name'], 'subdivisions', $id);
                    $_SESSION['success_message'] = 'Fraccionamiento actualizado exitosamente';
                    $this->redirect('subdivisions');
                } else {
                    $data['error'] = 'Error al actualizar el fraccionamiento';
                }
            }
        }
        
        $this->view('subdivisions/edit', $data);
    }
    
    /**
     * Cambiar estado del fraccionamiento
     */
    public function toggleStatus($id) {
        $stmt = $this->db->prepare("SELECT * FROM subdivisions WHERE id = ?");
        $stmt->execute([$id]);
        $subdivision = $stmt->fetch();
        
        if (!$subdivision) {
            $_SESSION['error_message'] = 'Fraccionamiento no encontrado';
            $this->redirect('subdivisions');
        }
        
        $newStatus = $subdivision['status'] === 'active' ? 'inactive' : 'active';
        
        $stmt = $this->db->prepare("UPDATE subdivisions SET status = ? WHERE id = ?");
        
        if ($stmt->execute([$newStatus, $id])) {
            AuditController::log('update', 'Estado del fraccionamiento cambiado a: ' . $newStatus, 'subdivisions', $id);
            $_SESSION['success_message'] = 'Estado del fraccionamiento actualizado';
        } else {
            $_SESSION['error_message'] = 'Error al actualizar el estado';
        }
        
        $this->redirect('subdivisions');
    }
    
    /**
     * Eliminar fraccionamiento
     */
    public function delete($id) {
        $stmt = $this->db->prepare("SELECT * FROM subdivisions WHERE id = ?");
        $stmt->execute([$id]);
        $subdivision = $stmt->fetch();
        
        if (!$subdivision) {
            $_SESSION['error_message'] = 'Fraccionamiento no encontrado';
            $this->redirect('subdivisions');
        }
        
        // Verificar si tiene propiedades asignadas
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM properties WHERE subdivision_id = ?");
        $stmt->execute([$id]);
        $propertyCount = $stmt->fetch()['count'];
        
        if ($propertyCount > 0) {
            $_SESSION['error_message'] = 'No se puede eliminar el fraccionamiento porque tiene propiedades asignadas';
            $this->redirect('subdivisions');
        }
        
        $stmt = $this->db->prepare("DELETE FROM subdivisions WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            AuditController::log('delete', 'Fraccionamiento eliminado: ' . $subdivision['name'], 'subdivisions', $id);
            $_SESSION['success_message'] = 'Fraccionamiento eliminado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el fraccionamiento';
        }
        
        $this->redirect('subdivisions');
    }
}
