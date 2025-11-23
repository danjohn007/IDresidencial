<?php
/**
 * Controlador de Vehículos Registrados
 */

class VehiclesController extends Controller {
    
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador']);
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de vehículos
     */
    public function index() {
        $stmt = $this->db->query("
            SELECT v.*, 
                   r.id as resident_id,
                   u.first_name, u.last_name,
                   p.property_number
            FROM vehicles v
            JOIN residents r ON v.resident_id = r.id
            JOIN users u ON r.user_id = u.id
            LEFT JOIN properties p ON r.property_id = p.id
            ORDER BY v.created_at DESC
        ");
        $vehicles = $stmt->fetchAll();
        
        // Calculate statistics
        $stats = [
            'total' => count($vehicles),
            'active' => count(array_filter($vehicles, fn($v) => $v['status'] === 'active')),
            'inactive' => count(array_filter($vehicles, fn($v) => $v['status'] === 'inactive'))
        ];
        
        $data = [
            'title' => 'Vehículos Registrados',
            'vehicles' => $vehicles,
            'stats' => $stats
        ];
        
        $this->view('vehicles/index', $data);
    }
    
    /**
     * Crear nuevo vehículo
     */
    public function create() {
        // Get all residents for dropdown
        $stmt = $this->db->query("
            SELECT r.id, u.first_name, u.last_name, p.property_number
            FROM residents r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN properties p ON r.property_id = p.id
            WHERE r.status = 'active'
            ORDER BY u.first_name, u.last_name
        ");
        $residents = $stmt->fetchAll();
        
        $data = [
            'title' => 'Registrar Vehículo',
            'residents' => $residents,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vehicleData = [
                'resident_id' => $this->post('resident_id'),
                'plate' => strtoupper($this->post('plate')),
                'brand' => $this->post('brand'),
                'model' => $this->post('model'),
                'color' => $this->post('color'),
                'year' => $this->post('year'),
                'vehicle_type' => $this->post('vehicle_type'),
                'status' => $this->post('status', 'active')
            ];
            
            // Check if plate exists
            $stmt = $this->db->prepare("SELECT id FROM vehicles WHERE plate = ?");
            $stmt->execute([$vehicleData['plate']]);
            if ($stmt->fetch()) {
                $data['error'] = 'Ya existe un vehículo con esta placa registrada.';
                $this->view('vehicles/create', $data);
                return;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO vehicles (resident_id, plate, brand, model, color, year, vehicle_type, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute(array_values($vehicleData))) {
                $_SESSION['success_message'] = 'Vehículo registrado exitosamente';
                $this->redirect('vehicles');
            } else {
                $data['error'] = 'Error al registrar el vehículo';
            }
        }
        
        $this->view('vehicles/create', $data);
    }
    
    /**
     * Editar vehículo
     */
    public function edit($id) {
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        $vehicle = $stmt->fetch();
        
        if (!$vehicle) {
            $_SESSION['error_message'] = 'Vehículo no encontrado';
            $this->redirect('vehicles');
            return;
        }
        
        // Get all residents for dropdown
        $stmt = $this->db->query("
            SELECT r.id, u.first_name, u.last_name, p.property_number
            FROM residents r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN properties p ON r.property_id = p.id
            WHERE r.status = 'active'
            ORDER BY u.first_name, u.last_name
        ");
        $residents = $stmt->fetchAll();
        
        $data = [
            'title' => 'Editar Vehículo',
            'vehicle' => $vehicle,
            'residents' => $residents,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vehicleData = [
                'resident_id' => $this->post('resident_id'),
                'plate' => strtoupper($this->post('plate')),
                'brand' => $this->post('brand'),
                'model' => $this->post('model'),
                'color' => $this->post('color'),
                'year' => $this->post('year'),
                'vehicle_type' => $this->post('vehicle_type'),
                'status' => $this->post('status')
            ];
            
            // Check if plate exists (excluding current vehicle)
            $stmt = $this->db->prepare("SELECT id FROM vehicles WHERE plate = ? AND id != ?");
            $stmt->execute([$vehicleData['plate'], $id]);
            if ($stmt->fetch()) {
                $data['error'] = 'Ya existe un vehículo con esta placa registrada.';
                $this->view('vehicles/edit', $data);
                return;
            }
            
            $stmt = $this->db->prepare("
                UPDATE vehicles 
                SET resident_id = ?, plate = ?, brand = ?, model = ?, color = ?, 
                    year = ?, vehicle_type = ?, status = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([...array_values($vehicleData), $id])) {
                $_SESSION['success_message'] = 'Vehículo actualizado exitosamente';
                $this->redirect('vehicles');
            } else {
                $data['error'] = 'Error al actualizar el vehículo';
            }
        }
        
        $this->view('vehicles/edit', $data);
    }
    
    /**
     * Eliminar vehículo
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success_message'] = 'Vehículo eliminado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el vehículo';
        }
        
        $this->redirect('vehicles');
    }
}
