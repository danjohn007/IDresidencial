<?php
/**
 * Controlador de Residentes
 */

require_once APP_PATH . '/controllers/AuditController.php';

class ResidentsController extends Controller {
    
    private $residentModel;
    private $userModel;
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        
        // Get current action
        $url = isset($_GET['url']) ? explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL)) : [];
        $action = isset($url[1]) ? $url[1] : 'index';
        
        // Methods that residents can access
        $residentMethods = ['myPayments', 'generateAccess', 'myAccesses', 'cancelPass', 'makePayment', 'processPayment'];
        
        // If not a resident method, require admin roles
        if (!in_array($action, $residentMethods)) {
            $this->requireRole(['superadmin', 'administrador']);
        }
        
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
     * Editar residente
     */
    public function edit($id) {
        $resident = $this->residentModel->findById($id);
        
        if (!$resident) {
            $_SESSION['error_message'] = 'Residente no encontrado';
            $this->redirect('residents');
        }
        
        $data = [
            'title' => 'Editar Residente',
            'resident' => $resident,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Actualizar datos del usuario
            $userData = [
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'phone' => $this->post('phone'),
                'email' => filter_var($this->post('email'), FILTER_SANITIZE_EMAIL)
            ];
            
            // Validate email format
            if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'El formato del correo electrónico no es válido';
                $this->view('residents/edit', $data);
                return;
            }
            
            // Check if email is unique (excluding current user)
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$userData['email'], $resident['user_id']]);
            if ($stmt->fetch()) {
                $data['error'] = 'El correo electrónico ya está en uso';
                $this->view('residents/edit', $data);
                return;
            }
            
            // Update user data
            $stmt = $this->db->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, phone = ?, email = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $userData['first_name'],
                $userData['last_name'],
                $userData['phone'],
                $userData['email'],
                $resident['user_id']
            ]);
            
            // Update resident data
            $residentData = [
                'property_id' => $this->post('property_id'),
                'relationship' => $this->post('relationship')
            ];
            
            $stmt = $this->db->prepare("
                UPDATE residents 
                SET property_id = ?, relationship = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $residentData['property_id'],
                $residentData['relationship'],
                $id
            ]);
            
            // Update password if provided
            $newPassword = $this->post('password');
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $resident['user_id']]);
            }
            
            AuditController::log('update', 'Residente actualizado: ' . $userData['first_name'] . ' ' . $userData['last_name'], 'residents', $id);
            $_SESSION['success_message'] = 'Residente actualizado exitosamente';
            $this->redirect('residents');
        }
        
        // Obtener propiedades disponibles
        $stmt = $this->db->query("SELECT * FROM properties ORDER BY property_number");
        $data['properties'] = $stmt->fetchAll();
        
        $this->view('residents/edit', $data);
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
            // Auto-generate username from email
            $email = $this->post('email');
            $baseUsername = strstr($email, '@', true);
            
            // Ensure username is unique by adding suffix if needed
            $username = $baseUsername;
            $counter = 1;
            while ($this->userModel->findByUsername($username)) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            // Crear usuario primero
            $userData = [
                'username' => $username,
                'email' => $email,
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
                    AuditController::log('create', 'Residente creado: ' . $userData['first_name'] . ' ' . $userData['last_name'], 'residents', null);
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
        
        // Calculate statistics
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
        // Default to current month date range
        $defaultDateFrom = date('Y-m-01'); // First day of current month
        $defaultDateTo = date('Y-m-t');     // Last day of current month
        
        $filters = [
            'status' => $this->get('status'),
            'date_from' => $this->get('date_from', $defaultDateFrom),
            'date_to' => $this->get('date_to', $defaultDateTo),
            'search' => $this->get('search', '')
        ];
        
        // Pagination
        $page = max(1, intval($this->get('page', 1)));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $where = [];
        $params = [];
        
        if ($filters['status']) {
            $where[] = "mf.status = ?";
            $params[] = $filters['status'];
        }
        
        // Use date range instead of month
        if ($filters['date_from']) {
            $where[] = "mf.due_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if ($filters['date_to']) {
            $where[] = "mf.due_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Search by resident name or phone
        if (!empty($filters['search'])) {
            $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.phone LIKE ? OR p.property_number LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Update overdue fees in the database FIRST
        $updateStmt = $this->db->prepare("
            UPDATE maintenance_fees 
            SET status = 'overdue' 
            WHERE status = 'pending' AND due_date < CURDATE()
        ");
        $updateStmt->execute();
        
        // Get total count for pagination
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            $whereClause
        ");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        $total_pages = ceil($total / $per_page);
        
        // Get paginated results - showing all maintenance fees with resident info
        $params[] = $per_page;
        $params[] = $offset;
        
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number, p.section,
                   COALESCE(u.first_name, 'Sin asignar') as first_name, 
                   COALESCE(u.last_name, '') as last_name, 
                   u.phone,
                   r.id as resident_id
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            $whereClause
            ORDER BY mf.due_date DESC, p.property_number
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $fees = $stmt->fetchAll();
        
        // Calculate statistics (for all results, not just current page)
        // Use the same where clause but without LIMIT/OFFSET
        $statsParams = array_slice($params, 0, -2); // Remove LIMIT and OFFSET params
        
        $statsStmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN mf.status = 'pending' AND mf.due_date >= CURDATE() THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN mf.status = 'pending' AND mf.due_date < CURDATE() THEN 1 
                         WHEN mf.status = 'overdue' THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN mf.status = 'paid' THEN 1 ELSE 0 END) as paid,
                SUM(mf.amount) as total_amount,
                SUM(CASE WHEN mf.status = 'paid' THEN mf.amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN mf.status = 'pending' OR mf.status = 'overdue' THEN mf.amount ELSE 0 END) as pending_amount
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            $whereClause
        ");
        $statsStmt->execute($statsParams);
        $stats = $statsStmt->fetch();
        
        // Check for properties that need fees generated (active residents without current period fee)
        $currentPeriod = date('Y-m');
        $checkStmt = $this->db->prepare("
            SELECT p.id, p.property_number
            FROM properties p
            INNER JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            WHERE p.status = 'ocupada'
              AND NOT EXISTS (
                  SELECT 1 FROM maintenance_fees mf 
                  WHERE mf.property_id = p.id AND mf.period = ?
              )
            LIMIT 10
        ");
        $checkStmt->execute([$currentPeriod]);
        $propertiesNeedingFees = $checkStmt->fetchAll();
        
        // Optionally auto-generate fees for these properties
        if (!empty($propertiesNeedingFees)) {
            $defaultAmount = 1500.00; // Default maintenance fee
            $dueDate = date('Y-m-10'); // 10th of current month
            
            foreach ($propertiesNeedingFees as $property) {
                // Get membership amount if exists
                $amountStmt = $this->db->prepare("
                    SELECT mp.monthly_cost
                    FROM memberships m
                    INNER JOIN membership_plans mp ON m.membership_plan_id = mp.id
                    INNER JOIN residents r ON r.id = m.resident_id
                    WHERE r.property_id = ? AND m.status = 'active' AND r.is_primary = 1
                    LIMIT 1
                ");
                $amountStmt->execute([$property['id']]);
                $membershipAmount = $amountStmt->fetch();
                $amount = $membershipAmount ? $membershipAmount['monthly_cost'] : $defaultAmount;
                
                // Insert fee
                $insertStmt = $this->db->prepare("
                    INSERT INTO maintenance_fees (property_id, period, amount, due_date, status)
                    VALUES (?, ?, ?, ?, 'pending')
                ");
                $insertStmt->execute([$property['id'], $currentPeriod, $amount, $dueDate]);
            }
            
            // If fees were generated, redirect to refresh the page
            if (!empty($propertiesNeedingFees)) {
                $_SESSION['success_message'] = 'Se generaron ' . count($propertiesNeedingFees) . ' nuevas cuotas de mantenimiento.';
                $this->redirect('residents/payments?' . http_build_query($filters));
                return;
            }
        }
        
        $data = [
            'title' => 'Pagos y Cuotas',
            'fees' => $fees,
            'filters' => $filters,
            'stats' => $stats,
            'page' => $page,
            'total_pages' => $total_pages,
            'total' => $total
        ];
        
        $this->view('residents/payments', $data);
    }
    
    /**
     * Create new property
     */
    public function createProperty() {
        $data = [
            'title' => 'Nueva Propiedad',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propertyData = [
                'property_number' => $this->post('property_number'),
                'street' => $this->post('street'),
                'section' => $this->post('section'),
                'tower' => $this->post('tower'),
                'property_type' => $this->post('property_type'),
                'bedrooms' => $this->post('bedrooms', 0),
                'bathrooms' => $this->post('bathrooms', 0),
                'area_m2' => $this->post('area_m2'),
                'status' => $this->post('status', 'desocupada')
            ];
            
            // Check if property number exists
            $stmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ?");
            $stmt->execute([$propertyData['property_number']]);
            if ($stmt->fetch()) {
                $data['error'] = 'Ya existe una propiedad con este número.';
                $this->view('residents/create_property', $data);
                return;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO properties (property_number, street, section, tower, property_type, bedrooms, bathrooms, area_m2, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute(array_values($propertyData))) {
                $_SESSION['success_message'] = 'Propiedad creada exitosamente';
                $this->redirect('residents/properties');
            } else {
                $data['error'] = 'Error al crear la propiedad';
            }
        }
        
        $this->view('residents/create_property', $data);
    }
    
    /**
     * Edit property
     */
    public function editProperty($id) {
        $stmt = $this->db->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$id]);
        $property = $stmt->fetch();
        
        if (!$property) {
            $_SESSION['error_message'] = 'Propiedad no encontrada';
            $this->redirect('residents/properties');
            return;
        }
        
        $data = [
            'title' => 'Editar Propiedad',
            'property' => $property,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propertyData = [
                'property_number' => $this->post('property_number'),
                'street' => $this->post('street'),
                'section' => $this->post('section'),
                'tower' => $this->post('tower'),
                'property_type' => $this->post('property_type'),
                'bedrooms' => $this->post('bedrooms', 0),
                'bathrooms' => $this->post('bathrooms', 0),
                'area_m2' => $this->post('area_m2'),
                'status' => $this->post('status')
            ];
            
            // Check if property number exists (excluding current property)
            $stmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ? AND id != ?");
            $stmt->execute([$propertyData['property_number'], $id]);
            if ($stmt->fetch()) {
                $data['error'] = 'Ya existe una propiedad con este número.';
                $this->view('residents/edit_property', $data);
                return;
            }
            
            $stmt = $this->db->prepare("
                UPDATE properties 
                SET property_number = ?, street = ?, section = ?, tower = ?, property_type = ?, 
                    bedrooms = ?, bathrooms = ?, area_m2 = ?, status = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([...array_values($propertyData), $id])) {
                $_SESSION['success_message'] = 'Propiedad actualizada exitosamente';
                $this->redirect('residents/properties');
            } else {
                $data['error'] = 'Error al actualizar la propiedad';
            }
        }
        
        $this->view('residents/edit_property', $data);
    }
    
    /**
     * Delete property
     */
    public function deleteProperty($id) {
        // Check if property has residents
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM residents WHERE property_id = ? AND status = 'active'");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $_SESSION['error_message'] = 'No se puede eliminar una propiedad con residentes activos';
            $this->redirect('residents/properties');
            return;
        }
        
        $stmt = $this->db->prepare("DELETE FROM properties WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success_message'] = 'Propiedad eliminada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar la propiedad';
        }
        
        $this->redirect('residents/properties');
    }
    
    /**
     * View pending registrations from public registration form
     */
    public function pendingRegistrations() {
        // Get all pending users with their property info
        $stmt = $this->db->query("
            SELECT u.*, 
                   p.property_number,
                   r.id as resident_id,
                   r.relationship,
                   u.created_at as registration_date
            FROM users u
            LEFT JOIN residents r ON u.id = r.user_id
            LEFT JOIN properties p ON r.property_id = p.id
            WHERE u.status = 'pending' AND u.role = 'residente'
            ORDER BY u.created_at DESC
        ");
        $pendingUsers = $stmt->fetchAll();
        
        $data = [
            'title' => 'Registros Pendientes',
            'pendingUsers' => $pendingUsers
        ];
        
        $this->view('residents/pending_registrations', $data);
    }
    
    /**
     * Approve a pending registration
     */
    public function approveRegistration($userId) {
        try {
            $this->db->beginTransaction();
            
            // Update user status to active
            $stmt = $this->db->prepare("UPDATE users SET status = 'active' WHERE id = ? AND status = 'pending'");
            $stmt->execute([$userId]);
            
            // Update resident status to active
            $stmt = $this->db->prepare("UPDATE residents SET status = 'active' WHERE user_id = ? AND status = 'pending'");
            $stmt->execute([$userId]);
            
            $this->db->commit();
            
            AuditController::log('approve', 'Registro de residente aprobado', 'users', $userId);
            $_SESSION['success_message'] = 'Registro aprobado exitosamente';
            
            // In production, send welcome email here
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Error al aprobar el registro: ' . $e->getMessage();
        }
        
        $this->redirect('residents/pendingRegistrations');
    }
    
    /**
     * Reject a pending registration
     */
    public function rejectRegistration($userId) {
        try {
            $this->db->beginTransaction();
            
            // Delete resident record first
            $stmt = $this->db->prepare("DELETE FROM residents WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Delete user record
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND status = 'pending'");
            $stmt->execute([$userId]);
            
            $this->db->commit();
            
            AuditController::log('reject', 'Registro de residente rechazado', 'users', $userId);
            $_SESSION['success_message'] = 'Registro rechazado y eliminado';
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Error al rechazar el registro: ' . $e->getMessage();
        }
        
        $this->redirect('residents/pendingRegistrations');
    }
    
    /**
     * Portal del residente - Ver pagos propios
     */
    public function myPayments() {
        // Solo para residentes
        if ($_SESSION['role'] !== 'residente') {
            $_SESSION['error_message'] = 'Acceso denegado';
            $this->redirect('dashboard');
        }
        
        // Obtener datos del residente actual
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("
            SELECT r.*, p.property_number 
            FROM residents r 
            JOIN properties p ON r.property_id = p.id 
            WHERE r.user_id = ? AND r.status = 'active' 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $resident = $stmt->fetch();
        
        if (!$resident) {
            $_SESSION['error_message'] = 'No se encontró información del residente';
            $this->redirect('dashboard');
        }
        
        // Obtener historial de pagos
        $stmt = $this->db->prepare("
            SELECT * FROM maintenance_fees 
            WHERE property_id = ? 
            ORDER BY due_date DESC, created_at DESC
        ");
        $stmt->execute([$resident['property_id']]);
        $payments = $stmt->fetchAll();
        
        // Calcular adeudos
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as overdue_amount,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount,
                COUNT(CASE WHEN status IN ('pending', 'overdue') THEN 1 END) as pending_count
            FROM maintenance_fees 
            WHERE property_id = ?
        ");
        $stmt->execute([$resident['property_id']]);
        $summary = $stmt->fetch();
        
        $data = [
            'title' => 'Mis Pagos',
            'resident' => $resident,
            'payments' => $payments,
            'summary' => $summary
        ];
        
        $this->view('residents/my_payments', $data);
    }
    
    /**
     * Generar pase de acceso para residente
     */
    public function generateAccess() {
        // Solo para residentes
        if ($_SESSION['role'] !== 'residente') {
            $_SESSION['error_message'] = 'Acceso denegado';
            $this->redirect('dashboard');
        }
        
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("
            SELECT r.*, p.property_number 
            FROM residents r 
            JOIN properties p ON r.property_id = p.id 
            WHERE r.user_id = ? AND r.status = 'active' 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $resident = $stmt->fetch();
        
        if (!$resident) {
            $_SESSION['error_message'] = 'No se encontró información del residente';
            $this->redirect('dashboard');
        }
        
        $data = [
            'title' => 'Generar Accesos',
            'resident' => $resident,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $passType = $this->post('pass_type', 'single_use');
            $validFrom = $this->post('valid_from', date('Y-m-d H:i:s'));
            $validUntil = $this->post('valid_until');
            $maxUses = intval($this->post('max_uses', 1));
            $notes = $this->post('notes', '');
            
            // Generar código QR único
            $qrCode = bin2hex(random_bytes(16));
            
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO resident_access_passes 
                    (resident_id, pass_type, qr_code, valid_from, valid_until, max_uses, notes, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                $stmt->execute([
                    $resident['id'], 
                    $passType, 
                    $qrCode, 
                    $validFrom, 
                    $validUntil, 
                    $maxUses, 
                    $notes
                ]);
                
                $passId = $this->db->lastInsertId();
                
                AuditController::log('create', 'Pase de acceso generado por residente', 'resident_access_passes', $passId);
                $_SESSION['success_message'] = 'Pase de acceso generado exitosamente';
                $this->redirect('residents/myAccesses');
                
            } catch (PDOException $e) {
                $data['error'] = 'Error al generar el pase: ' . $e->getMessage();
            }
        }
        
        $this->view('residents/generate_access', $data);
    }
    
    /**
     * Cancelar pase de acceso
     */
    public function cancelPass($passId) {
        // Solo para residentes
        if ($_SESSION['role'] !== 'residente') {
            $_SESSION['error_message'] = 'Acceso denegado';
            $this->redirect('dashboard');
        }
        
        $userId = $_SESSION['user_id'];
        
        // Verify pass belongs to resident
        $stmt = $this->db->prepare("
            SELECT rap.* 
            FROM resident_access_passes rap
            JOIN residents r ON rap.resident_id = r.id
            WHERE rap.id = ? AND r.user_id = ?
        ");
        $stmt->execute([$passId, $userId]);
        $pass = $stmt->fetch();
        
        if (!$pass) {
            $_SESSION['error_message'] = 'Pase no encontrado';
            $this->redirect('residents/myAccesses');
            return;
        }
        
        // Cancel pass
        $stmt = $this->db->prepare("UPDATE resident_access_passes SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$passId]);
        
        AuditController::log('cancel', 'Pase de acceso cancelado', 'resident_access_passes', $passId);
        $_SESSION['success_message'] = 'Pase de acceso cancelado exitosamente';
        
        $this->redirect('residents/myAccesses');
    }
    
    /**
     * Ver pases de acceso del residente
     */
    public function myAccesses() {
        // Solo para residentes
        if ($_SESSION['role'] !== 'residente') {
            $_SESSION['error_message'] = 'Acceso denegado';
            $this->redirect('dashboard');
        }
        
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("
            SELECT r.id as resident_id 
            FROM residents r 
            WHERE r.user_id = ? AND r.status = 'active' 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $resident = $stmt->fetch();
        
        if (!$resident) {
            $_SESSION['error_message'] = 'No se encontró información del residente';
            $this->redirect('dashboard');
        }
        
        // Obtener pases de acceso
        $stmt = $this->db->prepare("
            SELECT * FROM resident_access_passes 
            WHERE resident_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$resident['resident_id']]);
        $passes = $stmt->fetchAll();
        
        $data = [
            'title' => 'Mis Pases de Acceso',
            'passes' => $passes
        ];
        
        $this->view('residents/my_accesses', $data);
    }
    
    /**
     * Realizar pago con PayPal
     */
    public function makePayment($feeId = null) {
        // Solo para residentes
        if ($_SESSION['role'] !== 'residente') {
            $_SESSION['error_message'] = 'Acceso denegado';
            $this->redirect('dashboard');
        }
        
        if (!$feeId) {
            $_SESSION['error_message'] = 'ID de pago no especificado';
            $this->redirect('residents/myPayments');
        }
        
        $userId = $_SESSION['user_id'];
        
        // Verificar que el pago pertenece al residente
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number, r.id as resident_id
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            JOIN residents r ON r.property_id = p.id
            WHERE mf.id = ? AND r.user_id = ? AND r.status = 'active'
        ");
        $stmt->execute([$feeId, $userId]);
        $fee = $stmt->fetch();
        
        if (!$fee) {
            $_SESSION['error_message'] = 'Pago no encontrado o no autorizado';
            $this->redirect('residents/myPayments');
        }
        
        if ($fee['status'] === 'paid') {
            $_SESSION['info_message'] = 'Este pago ya ha sido realizado';
            $this->redirect('residents/myPayments');
        }
        
        // Obtener configuración de PayPal
        $stmt = $this->db->query("
            SELECT setting_key, setting_value 
            FROM system_settings 
            WHERE setting_key LIKE 'paypal_%'
        ");
        $paypalSettings = [];
        while ($row = $stmt->fetch()) {
            $paypalSettings[$row['setting_key']] = $row['setting_value'];
        }
        
        $data = [
            'title' => 'Realizar Pago',
            'fee' => $fee,
            'paypalSettings' => $paypalSettings
        ];
        
        $this->view('residents/make_payment', $data);
    }
    
    /**
     * Suspender residente
     */
    public function suspend($id) {
        try {
            $this->db->beginTransaction();
            
            // Update resident status
            $stmt = $this->db->prepare("UPDATE residents SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            
            // Update user status
            $stmt = $this->db->prepare("
                UPDATE users u
                JOIN residents r ON u.id = r.user_id
                SET u.status = 'inactive'
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            
            $this->db->commit();
            
            AuditController::log('suspend', 'Residente suspendido', 'residents', $id);
            $_SESSION['success_message'] = 'Residente suspendido exitosamente';
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Error al suspender el residente: ' . $e->getMessage();
        }
        
        $this->redirect('residents');
    }
    
    /**
     * Activar residente
     */
    public function activate($id) {
        try {
            $this->db->beginTransaction();
            
            // Update resident status
            $stmt = $this->db->prepare("UPDATE residents SET status = 'active' WHERE id = ?");
            $stmt->execute([$id]);
            
            // Update user status
            $stmt = $this->db->prepare("
                UPDATE users u
                JOIN residents r ON u.id = r.user_id
                SET u.status = 'active'
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            
            $this->db->commit();
            
            AuditController::log('activate', 'Residente activado', 'residents', $id);
            $_SESSION['success_message'] = 'Residente activado exitosamente';
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['error_message'] = 'Error al activar el residente: ' . $e->getMessage();
        }
        
        $this->redirect('residents');
    }
    
    /**
     * Eliminar residente (soft delete para mantener integridad de datos)
     */
    public function delete($id) {
        try {
            // Check if resident exists
            $stmt = $this->db->prepare("SELECT r.*, u.id as user_id, u.email FROM residents r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
            $stmt->execute([$id]);
            $resident = $stmt->fetch();
            
            if (!$resident) {
                $_SESSION['error_message'] = 'Residente no encontrado';
                $this->redirect('residents');
                return;
            }
            
            // Check if resident has unpaid fees
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as unpaid_count, SUM(amount) as unpaid_amount
                FROM maintenance_fees
                WHERE property_id = ? AND status IN ('pending', 'overdue')
            ");
            $stmt->execute([$resident['property_id']]);
            $feeCheck = $stmt->fetch();
            
            if ($feeCheck['unpaid_count'] > 0) {
                $_SESSION['warning_message'] = 'Este residente tiene ' . $feeCheck['unpaid_count'] . 
                    ' pagos pendientes por $' . number_format($feeCheck['unpaid_amount'], 2) . 
                    '. Se recomienda resolver estos pagos antes de eliminar.';
            }
            
            $this->db->beginTransaction();
            
            // Soft delete: Update status to 'deleted' instead of removing records
            // This preserves audit trail and referential integrity
            $stmt = $this->db->prepare("UPDATE residents SET status = 'deleted', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            
            // Also mark user as deleted
            // Append .deleted with timestamp to allow future re-registration with same email/username
            // Format: original@email.com.deleted.1234567890
            $stmt = $this->db->prepare("
                UPDATE users 
                SET status = 'deleted', 
                    email = CONCAT(email, '.deleted.', UNIX_TIMESTAMP()),
                    username = CONCAT(username, '.deleted.', UNIX_TIMESTAMP())
                WHERE id = ?
            ");
            $stmt->execute([$resident['user_id']]);
            
            // Cancel active access passes
            $stmt = $this->db->prepare("
                UPDATE resident_access_passes 
                SET status = 'cancelled' 
                WHERE resident_id = ? AND status = 'active'
            ");
            $stmt->execute([$id]);
            
            // Mark vehicles as inactive
            $stmt = $this->db->prepare("UPDATE vehicles SET status = 'inactive' WHERE resident_id = ?");
            $stmt->execute([$id]);
            
            $this->db->commit();
            
            AuditController::log('delete', 'Residente marcado como eliminado (soft delete): ' . $resident['email'], 'residents', $id);
            $_SESSION['success_message'] = 'Residente eliminado exitosamente';
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error deleting resident: ' . $e->getMessage());
            $_SESSION['error_message'] = 'Error al eliminar el residente. Por favor, contacte al administrador.';
        }
        
        $this->redirect('residents');
    }
    
    /**
     * Procesar pago de PayPal
     */
    public function processPayment() {
        // Solo para residentes
        if ($_SESSION['role'] !== 'residente') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }
        
        $feeId = $this->post('fee_id');
        $paymentId = $this->post('payment_id');
        $payerId = $this->post('payer_id');
        $paymentMethod = $this->post('payment_method', 'paypal');
        
        $userId = $_SESSION['user_id'];
        
        // Verificar que el pago pertenece al residente
        $stmt = $this->db->prepare("
            SELECT mf.*, r.id as resident_id
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            JOIN residents r ON r.property_id = p.id
            WHERE mf.id = ? AND r.user_id = ? AND r.status = 'active'
        ");
        $stmt->execute([$feeId, $userId]);
        $fee = $stmt->fetch();
        
        if (!$fee) {
            echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
            exit;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Actualizar estado del pago
            $stmt = $this->db->prepare("
                UPDATE maintenance_fees 
                SET status = 'paid', 
                    paid_date = NOW(), 
                    payment_method = ?,
                    payment_reference = ?
                WHERE id = ?
            ");
            $stmt->execute([$paymentMethod, $paymentId, $feeId]);
            
            // Registrar movimiento financiero
            $stmt = $this->db->prepare("
                INSERT INTO financial_movements 
                (movement_type_id, transaction_type, amount, description, 
                 property_id, resident_id, payment_method, payment_reference, 
                 transaction_date, created_by, reference_type, reference_id)
                SELECT 
                    (SELECT id FROM financial_movement_types WHERE category = 'ingreso' LIMIT 1),
                    'ingreso',
                    ?,
                    CONCAT('Pago de cuota de mantenimiento - ', ?),
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW(),
                    ?,
                    'maintenance_fee',
                    ?
            ");
            $stmt->execute([
                $fee['amount'],
                $fee['period'],
                $fee['property_id'],
                $fee['resident_id'],
                $paymentMethod,
                $paymentId,
                $userId,
                $feeId
            ]);
            
            $this->db->commit();
            
            AuditController::log('payment', 'Pago realizado via ' . $paymentMethod, 'maintenance_fees', $feeId);
            
            echo json_encode(['success' => true, 'message' => 'Pago procesado exitosamente']);
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Payment processing error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al procesar el pago']);
        }
        
        exit;
    }
}
