<?php
/**
 * Controlador de Residentes
 */

class ResidentsController extends Controller {
    
    private $residentModel;
    private $userModel;
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador']);
        $this->residentModel = $this->model('Resident');
        $this->userModel = $this->model('User');
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de residentes
     */
    public function index() {
        $residents = $this->residentModel->getAll();
        
        // Obtener estadísticas
        $stats = [
            'total' => count($residents),
            'propietarios' => $this->residentModel->count(['relationship' => 'propietario']),
            'inquilinos' => $this->residentModel->count(['relationship' => 'inquilino']),
            'familiares' => $this->residentModel->count(['relationship' => 'familiar'])
        ];
        
        $data = [
            'title' => 'Residentes',
            'residents' => $residents,
            'stats' => $stats
        ];
        
        $this->view('residents/index', $data);
    }
    
    /**
     * Ver detalles de residente
     */
    public function viewDetails($id) {
        $resident = $this->residentModel->findById($id);
        
        if (!$resident) {
            $_SESSION['error_message'] = 'Residente no encontrado';
            $this->redirect('residents');
        }
        
        // Obtener vehículos
        $stmt = $this->db->prepare("
            SELECT * FROM vehicles WHERE resident_id = ? AND status = 'active'
        ");
        $stmt->execute([$id]);
        $vehicles = $stmt->fetchAll();
        
        // Obtener estado de cuenta
        $stmt = $this->db->prepare("
            SELECT * FROM maintenance_fees 
            WHERE property_id = ? 
            ORDER BY period DESC 
            LIMIT 12
        ");
        $stmt->execute([$resident['property_id']]);
        $fees = $stmt->fetchAll();
        
        $data = [
            'title' => 'Detalles de Residente',
            'resident' => $resident,
            'vehicles' => $vehicles,
            'fees' => $fees
        ];
        
        $this->view('residents/view', $data);
    }
    
    /**
     * Crear residente
     */
    public function create() {
        $data = [
            'title' => 'Nuevo Residente',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Crear usuario primero
            $userData = [
                'username' => $this->post('username'),
                'email' => $this->post('email'),
                'password' => $this->post('password'),
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'phone' => $this->post('phone'),
                'role' => 'residente',
                'status' => 'active'
            ];
            
            if ($this->userModel->create($userData)) {
                $user = $this->userModel->findByUsername($userData['username']);
                
                // Crear residente
                $residentData = [
                    'user_id' => $user['id'],
                    'property_id' => $this->post('property_id'),
                    'relationship' => $this->post('relationship', 'propietario'),
                    'is_primary' => true,
                    'status' => 'active'
                ];
                
                if ($this->residentModel->create($residentData)) {
                    $_SESSION['success_message'] = 'Residente creado exitosamente';
                    $this->redirect('residents');
                } else {
                    $data['error'] = 'Error al crear el residente';
                }
            } else {
                $data['error'] = 'Error al crear el usuario';
            }
        }
        
        // Obtener propiedades disponibles
        $stmt = $this->db->query("SELECT * FROM properties ORDER BY property_number");
        $data['properties'] = $stmt->fetchAll();
        
        $this->view('residents/create', $data);
    }
    
    /**
     * Gestión de propiedades
     */
    public function properties() {
        $stmt = $this->db->query("
            SELECT p.*, 
                   COUNT(r.id) as resident_count
            FROM properties p
            LEFT JOIN residents r ON p.id = r.property_id AND r.status = 'active'
            GROUP BY p.id
            ORDER BY p.property_number
        ");
        $properties = $stmt->fetchAll();
        
        // Calcular estadísticas
        $stats = [
            'total' => count($properties),
            'ocupada' => count(array_filter($properties, fn($p) => $p['status'] === 'ocupada')),
            'desocupada' => count(array_filter($properties, fn($p) => $p['status'] === 'desocupada')),
            'en_construccion' => count(array_filter($properties, fn($p) => $p['status'] === 'en_construccion'))
        ];
        
        $data = [
            'title' => 'Propiedades',
            'properties' => $properties,
            'stats' => $stats
        ];
        
        $this->view('residents/properties', $data);
    }
    
    /**
     * Ver estado de cuenta
     */
    public function payments() {
        $filters = [
            'status' => $this->get('status'),
            'month' => $this->get('month', date('Y-m'))
        ];
        
        $where = [];
        $params = [];
        
        if ($filters['status']) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if ($filters['month']) {
            $where[] = "period = ?";
            $params[] = $filters['month'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number, p.section
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            $whereClause
            ORDER BY mf.due_date DESC, p.property_number
        ");
        $stmt->execute($params);
        $fees = $stmt->fetchAll();
        
        // Calcular estadísticas
        $stats = [
            'total' => count($fees),
            'pending' => count(array_filter($fees, fn($f) => $f['status'] === 'pending')),
            'overdue' => count(array_filter($fees, fn($f) => $f['status'] === 'overdue'))
        ];
        
        $data = [
            'title' => 'Pagos y Cuotas',
            'fees' => $fees,
            'filters' => $filters,
            'stats' => $stats
        ];
        
        $this->view('residents/payments', $data);
    }
}
