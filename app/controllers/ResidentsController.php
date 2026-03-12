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
        $residentMethods = ['myPayments', 'generateAccess', 'myAccesses', 'cancelPass', 'makePayment', 'processPayment', 'financialReport', 'serviceRequests'];
        
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
        $filters = [
            'search'                => $this->get('search', ''),
            'status'                => $this->get('status', ''),
            'relationship'          => $this->get('relationship', ''),
            'section'               => $this->get('section', ''),
            'is_vigilance_committee'=> $this->get('is_vigilance_committee', ''),
            'sort_by'               => $this->get('sort_by', 'fecha'),
            'sort_order'            => $this->get('sort_order', 'desc'),
        ];

        // Remove empty filters so getAll() doesn't apply them
        $activeFilters = array_filter($filters, fn($v) => $v !== '');
        $residents = $this->residentModel->getAll($activeFilters);
        
        // Obtener estadísticas
        $stats = [
            'total' => count($residents),
            'propietarios' => $this->residentModel->count(['relationship' => 'propietario']),
            'inquilinos' => $this->residentModel->count(['relationship' => 'inquilino']),
            'familiares' => $this->residentModel->count(['relationship' => 'familiar'])
        ];

        // Obtener secciones disponibles para el filtro
        $sectionsStmt = $this->db->query("SELECT DISTINCT section FROM properties WHERE section IS NOT NULL AND section != '' ORDER BY section");
        $sections = $sectionsStmt->fetchAll(PDO::FETCH_COLUMN);
        
        $data = [
            'title' => 'Residentes',
            'residents' => $residents,
            'stats' => $stats,
            'filters' => $filters,
            'sections' => $sections
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
                SET first_name = ?, last_name = ?, phone = ?, email = ?, is_vigilance_committee = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $userData['first_name'],
                $userData['last_name'],
                $userData['phone'],
                $userData['email'],
                $this->post('is_vigilance_committee') ? 1 : 0,
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
            $relationship = $this->post('relationship', 'propietario');
            $propertyId = intval($this->post('property_id'));

            // Validate owner rules before proceeding
            if ($relationship === 'propietario') {
                $ownerStmt = $this->db->prepare("
                    SELECT id FROM residents
                    WHERE property_id = ? AND relationship = 'propietario' AND status = 'active'
                    LIMIT 1
                ");
                $ownerStmt->execute([$propertyId]);
                if ($ownerStmt->fetch()) {
                    $data['error'] = 'Esta propiedad ya tiene un residente propietario asignado. Solo puede haber un propietario por propiedad.';
                }
            } elseif ($relationship === 'inquilino' || $relationship === 'familiar') {
                $ownerStmt = $this->db->prepare("
                    SELECT id FROM residents
                    WHERE property_id = ? AND relationship = 'propietario' AND status = 'active'
                    LIMIT 1
                ");
                $ownerStmt->execute([$propertyId]);
                if (!$ownerStmt->fetch()) {
                    $data['error'] = 'Debe registrar primero un propietario para esta propiedad antes de agregar un inquilino o familiar.';
                }
            }

            // Check for duplicate email before attempting to create
            if (empty($data['error']) && $this->userModel->findByEmail($email)) {
                $data['error'] = 'El correo electrónico ya está registrado en el sistema. Por favor utilice otro correo.';
            }

            if (empty($data['error'])) {
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
                'status' => 'active',
                'is_vigilance_committee' => $this->post('is_vigilance_committee') ? 1 : 0
            ];
            
            if ($this->userModel->create($userData)) {
                $user = $this->userModel->findByUsername($userData['username']);
                
                // Crear residente (is_primary only for propietario)
                $residentData = [
                    'user_id' => $user['id'],
                    'property_id' => $propertyId,
                    'relationship' => $relationship,
                    'is_primary' => $relationship === 'propietario' ? true : false,
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
            } // end if no error
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
        // Default to last 12 months to show full history
        $defaultDateFrom = date('Y-m-01', strtotime('-11 months')); // First day of 12 months ago
        $defaultDateTo = date('Y-m-t'); // Last day of current month
        
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
        $generatedCount = 0;
        
        foreach ([$currentPeriod] as $period) {
            $checkStmt = $this->db->prepare("
                SELECT p.id, p.property_number
                FROM properties p
                INNER JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
                WHERE NOT EXISTS (
                      SELECT 1 FROM maintenance_fees mf 
                      WHERE mf.property_id = p.id AND mf.period = ?
                  )
            ");
            $checkStmt->execute([$period]);
            $propertiesNeedingFees = $checkStmt->fetchAll();
            
            if (!empty($propertiesNeedingFees)) {
                $defaultAmount = 1500.00; // Default maintenance fee
                $dueDate = date('Y-m-10', strtotime($period . '-01')); // 10th of the period month
                
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
                    
                    // Insert fee (IGNORE prevents duplicate if unique constraint exists)
                    $insertStmt = $this->db->prepare("
                        INSERT IGNORE INTO maintenance_fees (property_id, period, amount, due_date, status)
                        VALUES (?, ?, ?, ?, 'pending')
                    ");
                    $insertStmt->execute([$property['id'], $period, $amount, $dueDate]);
                    if ($insertStmt->rowCount() > 0) {
                        $generatedCount++;
                    }
                }
            }
        }
        
        // If fees were generated, redirect to refresh the page
        if ($generatedCount > 0) {
            $_SESSION['success_message'] = 'Se generaron ' . $generatedCount . ' nuevas cuotas de mantenimiento.';
            $this->redirect('residents/payments?' . http_build_query($filters));
            return;
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
        
        // Fecha de ingreso del residente (filtrar solo sus pagos)
        $residentStartDate = $resident['contract_start'] ?? date('Y-m-d');
        
        // Obtener solo pagos desde la fecha de ingreso del residente
        $stmt = $this->db->prepare("
            SELECT * FROM maintenance_fees 
            WHERE property_id = ? AND due_date >= ?
            ORDER BY due_date DESC, created_at DESC
        ");
        $stmt->execute([$resident['property_id'], $residentStartDate]);
        $payments = $stmt->fetchAll();
        
        // Calcular adeudos solo desde la fecha de ingreso
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as overdue_amount,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount,
                COUNT(CASE WHEN status IN ('pending', 'overdue') THEN 1 END) as pending_count
            FROM maintenance_fees 
            WHERE property_id = ? AND due_date >= ?
        ");
        $stmt->execute([$resident['property_id'], $residentStartDate]);
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
            
            // Generar código QR único con formato VIS-YYYYMMDD-XXXXXXXX
            do {
                $qrCode = 'VIS-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                $stmtCheck = $this->db->prepare("SELECT id FROM resident_access_passes WHERE qr_code = ?");
                $stmtCheck->execute([$qrCode]);
            } while ($stmtCheck->fetch());
            
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
    
    /**
     * Registrar pago de cuota de mantenimiento (modal admin)
     */
    public function registerFeePayment() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        $feeId = intval($this->post('fee_id'));
        $transactionDate = $this->post('transaction_date');
        $description = trim($this->post('description', ''));
        $paymentMethod = $this->post('payment_method');
        $paymentReference = trim($this->post('payment_reference', ''));
        
        // Validate required fields
        if (!$feeId || !$transactionDate || !$paymentMethod) {
            echo json_encode(['success' => false, 'message' => 'Fecha y método de pago son obligatorios']);
            exit;
        }
        
        // Get the fee
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number, r.id as resident_id,
                   COALESCE(u.first_name, '') as first_name,
                   COALESCE(u.last_name, '') as last_name,
                   u.email as resident_email
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            WHERE mf.id = ?
        ");
        $stmt->execute([$feeId]);
        $fee = $stmt->fetch();
        
        if (!$fee) {
            echo json_encode(['success' => false, 'message' => 'Cuota no encontrada']);
            exit;
        }
        
        if ($fee['status'] === 'paid') {
            echo json_encode(['success' => false, 'message' => 'Esta cuota ya fue pagada']);
            exit;
        }
        
        // Handle evidence file upload
        $evidencePath = null;
        if (!empty($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileType = $finfo->file($_FILES['evidence']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Use JPG, PNG, GIF o PDF']);
                exit;
            }
            $ext = strtolower(pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Extensión de archivo no permitida. Use JPG, PNG, GIF o PDF']);
                exit;
            }
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES['evidence']['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'El archivo no puede superar 5MB']);
                exit;
            }
            $uploadDir = PUBLIC_PATH . '/uploads/evidence/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = 'fee_' . $feeId . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['evidence']['tmp_name'], $destPath)) {
                $evidencePath = 'uploads/evidence/' . $fileName;
            }
        }
        
        $descriptionFinal = $description ?: ('Pago de cuota de mantenimiento - ' . $fee['period']);
        
        try {
            $this->db->beginTransaction();
            
            // Get 'Cuota de Mantenimiento' movement type id
            $typeStmt = $this->db->prepare("
                SELECT id FROM financial_movement_types 
                WHERE category = 'ingreso' AND (name LIKE '%Cuota%' OR name LIKE '%Mantenimiento%')
                ORDER BY id ASC LIMIT 1
            ");
            $typeStmt->execute();
            $movementType = $typeStmt->fetch();
            $movementTypeId = $movementType ? $movementType['id'] : 1;
            
            // Create financial movement
            $movStmt = $this->db->prepare("
                INSERT INTO financial_movements 
                (movement_type_id, transaction_type, amount, description,
                 property_id, resident_id, payment_method, payment_reference,
                 transaction_date, created_by, reference_type, reference_id, notes)
                VALUES (?, 'ingreso', ?, ?, ?, ?, ?, ?, ?, ?, 'maintenance_fee', ?, ?)
            ");
            $movStmt->execute([
                $movementTypeId,
                $fee['amount'],
                $descriptionFinal,
                $fee['property_id'],
                $fee['resident_id'],
                $paymentMethod,
                $paymentReference ?: null,
                $transactionDate,
                $_SESSION['user_id'],
                $feeId,
                $evidencePath
            ]);
            
            // Update maintenance fee status
            $noteValue = $description ?: $fee['notes'];
            $updateStmt = $this->db->prepare("
                UPDATE maintenance_fees 
                SET status = 'paid',
                    paid_date = ?,
                    payment_method = ?,
                    payment_reference = ?,
                    payment_confirmation = ?,
                    notes = ?
                WHERE id = ?
            ");
            $updateStmt->execute([
                $transactionDate,
                $paymentMethod,
                $paymentReference ?: null,
                $evidencePath,
                $noteValue ?: null,
                $feeId
            ]);
            
            $this->db->commit();
            
            AuditController::log('payment', 'Cuota de mantenimiento pagada (admin): ' . $fee['period'], 'maintenance_fees', $feeId);
            
            // Send email notifications
            $this->sendFeePaymentNotification($fee, 'paid', $transactionDate, $paymentMethod);
            
            echo json_encode(['success' => true, 'message' => 'Pago registrado exitosamente']);
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('registerFeePayment error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al registrar el pago']);
        }
        
        exit;
    }
    
    /**
     * Get or create a maintenance fee for a specific property and period (for advance payments)
     */
    public function getOrCreateFeeForPeriod() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $propertyId = intval($this->post('property_id'));
        $period = trim($this->post('period'));

        if (!$propertyId || !$period || !preg_match('/^\d{4}-\d{2}$/', $period)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        // Validate the period is not in the past (allow current month and future)
        $currentPeriod = date('Y-m');
        if ($period < $currentPeriod) {
            echo json_encode(['success' => false, 'message' => 'No se pueden registrar pagos para períodos anteriores desde esta función']);
            exit;
        }

        // Check if fee already exists
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number,
                   COALESCE(u.first_name, 'Sin asignar') as first_name,
                   COALESCE(u.last_name, '') as last_name
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            WHERE mf.property_id = ? AND mf.period = ?
        ");
        $stmt->execute([$propertyId, $period]);
        $fee = $stmt->fetch();

        if (!$fee) {
            // Create the fee for the requested period
            $defaultAmount = 1500.00;
            $amountStmt = $this->db->prepare("
                SELECT mp.monthly_cost
                FROM memberships m
                INNER JOIN membership_plans mp ON m.membership_plan_id = mp.id
                INNER JOIN residents r ON r.id = m.resident_id
                WHERE r.property_id = ? AND m.status = 'active' AND r.is_primary = 1
                LIMIT 1
            ");
            $amountStmt->execute([$propertyId]);
            $membershipAmount = $amountStmt->fetch();
            $amount = $membershipAmount ? $membershipAmount['monthly_cost'] : $defaultAmount;
            $dueDate = date('Y-m-10', strtotime($period . '-01'));

            $insertStmt = $this->db->prepare("
                INSERT IGNORE INTO maintenance_fees (property_id, period, amount, due_date, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $insertStmt->execute([$propertyId, $period, $amount, $dueDate]);

            // Re-fetch the fee with joined data
            $stmt->execute([$propertyId, $period]);
            $fee = $stmt->fetch();

            if (!$fee) {
                echo json_encode(['success' => false, 'message' => 'Error al crear la cuota']);
                exit;
            }
        }

        echo json_encode([
            'success' => true,
            'fee' => [
                'id' => (int)$fee['id'],
                'property_id' => (int)$fee['property_id'],
                'property_number' => $fee['property_number'],
                'resident_name' => trim($fee['first_name'] . ' ' . $fee['last_name']),
                'period' => $fee['period'],
                'amount' => (float)$fee['amount'],
                'status' => $fee['status']
            ]
        ]);
        exit;
    }

    /**
     * Get status of next 12 months fees for a property
     */
    public function getUpcomingMonthsStatus() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $propertyId = intval($this->post('property_id'));

        if (!$propertyId) {
            echo json_encode(['success' => false, 'message' => 'ID de propiedad inválido']);
            exit;
        }

        $months = [];
        
        for ($i = 0; $i < 12; $i++) {
            $date = new DateTime();
            $date->modify("+$i months");
            $period = $date->format('Y-m');
            
            // Check if fee exists for this period
            $stmt = $this->db->prepare("
                SELECT id, status, amount, due_date, paid_date
                FROM maintenance_fees 
                WHERE property_id = ? AND period = ?
            ");
            $stmt->execute([$propertyId, $period]);
            $fee = $stmt->fetch();
            
            $months[] = [
                'period' => $period,
                'isCurrent' => $i === 0,
                'status' => $fee ? $fee['status'] : 'none',
                'feeId' => $fee ? (int)$fee['id'] : null,
                'amount' => $fee ? (float)$fee['amount'] : null,
                'dueDate' => $fee ? $fee['due_date'] : null,
                'paidDate' => $fee ? $fee['paid_date'] : null
            ];
        }

        echo json_encode(['success' => true, 'months' => $months]);
        exit;
    }

    /**
     * Register payment for multiple fees at once  
     */
    public function registerMultipleFeePayments() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        $feeIds = $this->post('fee_ids'); // Expecting JSON array
        $transactionDate = $this->post('transaction_date');
        $description = trim($this->post('description', ''));
        $paymentMethod = $this->post('payment_method');
        $paymentReference = trim($this->post('payment_reference', ''));
        
        // Decode fee_ids if it's a JSON string
        if (is_string($feeIds)) {
            $feeIds = json_decode($feeIds, true);
        }
        
        // Validate required fields
        if (empty($feeIds) || !is_array($feeIds) || !$transactionDate || !$paymentMethod) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // Validate all fees exist and are unpaid
        $placeholders = str_repeat('?,', count($feeIds) - 1) . '?';
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            WHERE mf.id IN ($placeholders)
        ");
        $stmt->execute($feeIds);
        $fees = $stmt->fetchAll();
        
        if (count($fees) !== count($feeIds)) {
            echo json_encode(['success' => false, 'message' => 'Una o más cuotas no existen']);
            exit;
        }
        
        // Check if any are already paid
        foreach ($fees as $fee) {
            if ($fee['status'] === 'paid') {
                echo json_encode(['success' => false, 'message' => 'Una o más cuotas ya están pagadas']);
                exit;
            }
        }
        
        // Handle evidence file upload
        $evidencePath = null;
        if (!empty($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileType = $finfo->file($_FILES['evidence']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido']);
                exit;
            }
            $ext = strtolower(pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Extensión de archivo no permitida']);
                exit;
            }
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($_FILES['evidence']['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'El archivo no puede superar 5MB']);
                exit;
            }
            $uploadDir = PUBLIC_PATH . '/uploads/evidence/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'evidence_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($_FILES['evidence']['tmp_name'], $uploadDir . $filename)) {
                $evidencePath = 'uploads/evidence/' . $filename;
            }
        }
        
        try {
            $this->db->beginTransaction();
            
            $totalAmount = 0;
            $periods = [];
            
            // Update all fees
            foreach ($fees as $fee) {
                $stmt = $this->db->prepare("
                    UPDATE maintenance_fees 
                    SET status = 'paid',
                        paid_date = ?,
                        payment_method = ?,
                        payment_reference = ?,
                        notes = CONCAT(IFNULL(notes, ''), ?, ?)
                    WHERE id = ?
                ");
                
                $note = "\nPago múltiple registrado el " . date('Y-m-d H:i:s');
                if ($evidencePath) {
                    $note .= " | Evidencia: " . $evidencePath;
                }
                if ($description) {
                    $note .= " | " . $description;
                }
                
                $stmt->execute([
                    $transactionDate,
                    $paymentMethod,
                    $paymentReference,
                    "\n",
                    $note,
                    $fee['id']
                ]);
                
                $totalAmount += $fee['amount'];
                $periods[] = $fee['period'];
                
                // Log audit
                AuditController::log(
                    'update',
                    'Pago registrado para período ' . $fee['period'] . ' (Pago múltiple)',
                    'maintenance_fees',
                    $fee['id']
                );
            }
            
            $this->db->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Se registraron ' . count($fees) . ' pagos exitosamente',
                'total_amount' => $totalAmount,
                'periods' => $periods
            ]);
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('registerMultipleFeePayments error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al registrar los pagos']);
        }
        
        exit;
    }

    /**
     * Ver detalles de pago de una cuota
     */
    public function viewFeePayment($feeId = null) {
        if (!$feeId) {
            $_SESSION['error_message'] = 'ID de cuota no especificado';
            $this->redirect('residents/payments');
        }
        
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number, p.section, p.id as property_id,
                   COALESCE(u.first_name, 'Sin asignar') as first_name,
                   COALESCE(u.last_name, '') as last_name,
                   u.phone,
                   fm.id as movement_id, fm.transaction_date, fm.notes as movement_notes
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN financial_movements fm ON fm.reference_type = 'maintenance_fee' AND fm.reference_id = mf.id
            WHERE mf.id = ?
        ");
        $stmt->execute([$feeId]);
        $fee = $stmt->fetch();
        
        if (!$fee) {
            $_SESSION['error_message'] = 'Cuota no encontrada';
            $this->redirect('residents/payments');
        }
        
        // Obtener todos los pagos de esta propiedad (pagados y pendientes)
        $allFeesStmt = $this->db->prepare("
            SELECT mf.*, fm.transaction_date as paid_date
            FROM maintenance_fees mf
            LEFT JOIN financial_movements fm ON fm.reference_type = 'maintenance_fee' AND fm.reference_id = mf.id
            WHERE mf.property_id = ?
            ORDER BY mf.period DESC
            LIMIT 24
        ");
        $allFeesStmt->execute([$fee['property_id']]);
        $allFees = $allFeesStmt->fetchAll();
        
        $data = [
            'title' => 'Detalle de Pago',
            'fee' => $fee,
            'allFees' => $allFees
        ];
        
        $this->view('residents/view_fee_payment', $data);
    }
    
    /**
     * Editar pago de una cuota
     */
    public function editFeePayment($feeId = null) {
        if (!$feeId) {
            $_SESSION['error_message'] = 'ID de cuota no especificado';
            $this->redirect('residents/payments');
        }
        
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number,
                   COALESCE(u.first_name, '') as first_name,
                   COALESCE(u.last_name, '') as last_name,
                   fm.id as movement_id
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN financial_movements fm ON fm.reference_type = 'maintenance_fee' AND fm.reference_id = mf.id
            WHERE mf.id = ?
        ");
        $stmt->execute([$feeId]);
        $fee = $stmt->fetch();
        
        if (!$fee) {
            $_SESSION['error_message'] = 'Cuota no encontrada';
            $this->redirect('residents/payments');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transactionDate = $this->post('transaction_date');
            $paymentMethod = $this->post('payment_method');
            $paymentReference = trim($this->post('payment_reference', ''));
            $notes = trim($this->post('notes', ''));
            
            if (!$transactionDate || !$paymentMethod) {
                $_SESSION['error_message'] = 'Fecha y método de pago son obligatorios';
                $this->redirect('residents/editFeePayment/' . $feeId);
            }
            
            try {
                $this->db->beginTransaction();
                
                $updateFee = $this->db->prepare("
                    UPDATE maintenance_fees 
                    SET paid_date = ?, payment_method = ?, payment_reference = ?, notes = ?
                    WHERE id = ?
                ");
                $updateFee->execute([$transactionDate, $paymentMethod, $paymentReference ?: null, $notes ?: null, $feeId]);
                
                if ($fee['movement_id']) {
                    $updateMov = $this->db->prepare("
                        UPDATE financial_movements 
                        SET transaction_date = ?, payment_method = ?, payment_reference = ?, notes = ?
                        WHERE id = ?
                    ");
                    $updateMov->execute([$transactionDate, $paymentMethod, $paymentReference ?: null, $notes ?: null, $fee['movement_id']]);
                }
                
                $this->db->commit();
                
                AuditController::log('update', 'Pago de cuota editado: ' . $feeId, 'maintenance_fees', $feeId);
                $_SESSION['success_message'] = 'Pago actualizado exitosamente';
                $this->redirect('residents/payments');
                
            } catch (PDOException $e) {
                $this->db->rollBack();
                error_log('editFeePayment error: ' . $e->getMessage());
                $_SESSION['error_message'] = 'Error al actualizar el pago';
                $this->redirect('residents/editFeePayment/' . $feeId);
            }
        }
        
        $data = [
            'title' => 'Editar Pago',
            'fee' => $fee
        ];
        
        $this->view('residents/edit_fee_payment', $data);
    }
    
    /**
     * Eliminar pago de una cuota (revertir a pendiente/vencido)
     */
    public function deleteFeePayment($feeId = null) {
        if (!$feeId) {
            $_SESSION['error_message'] = 'ID de cuota no especificado';
            $this->redirect('residents/payments');
        }
        
        $stmt = $this->db->prepare("
            SELECT mf.*, fm.id as movement_id,
                   p.property_number,
                   COALESCE(u.first_name, '') as first_name,
                   COALESCE(u.last_name, '') as last_name,
                   u.email as resident_email,
                   r.id as resident_id
            FROM maintenance_fees mf
            LEFT JOIN financial_movements fm ON fm.reference_type = 'maintenance_fee' AND fm.reference_id = mf.id
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            WHERE mf.id = ?
        ");
        $stmt->execute([$feeId]);
        $fee = $stmt->fetch();
        
        if (!$fee) {
            $_SESSION['error_message'] = 'Cuota no encontrada';
            $this->redirect('residents/payments');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Revert fee to appropriate status (date-only comparison to avoid timezone issues)
            $newStatus = ($fee['due_date'] < date('Y-m-d')) ? 'overdue' : 'pending';
            $revertStmt = $this->db->prepare("
                UPDATE maintenance_fees 
                SET status = ?, paid_date = NULL, payment_method = NULL,
                    payment_reference = NULL, payment_confirmation = NULL
                WHERE id = ?
            ");
            $revertStmt->execute([$newStatus, $feeId]);
            
            // Delete the associated financial movement if exists
            if ($fee['movement_id']) {
                $delMovStmt = $this->db->prepare("DELETE FROM financial_movements WHERE id = ?");
                $delMovStmt->execute([$fee['movement_id']]);
            }
            
            $this->db->commit();
            
            AuditController::log('delete', 'Pago de cuota eliminado: ' . $feeId, 'maintenance_fees', $feeId);
            $_SESSION['success_message'] = 'Pago eliminado y cuota revertida a ' . ($newStatus === 'overdue' ? 'Vencido' : 'Pendiente');
            
            // Send cancellation email notifications
            $this->sendFeePaymentNotification($fee, 'cancelled');
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('deleteFeePayment error: ' . $e->getMessage());
            $_SESSION['error_message'] = 'Error al eliminar el pago';
        }
        
        $this->redirect('residents/payments');
    }
    
    /**
     * Imprimir recibo de pago de cuota
     */
    public function printFeePayment($feeId = null) {
        if (!$feeId) {
            $_SESSION['error_message'] = 'ID de cuota no especificado';
            $this->redirect('residents/payments');
        }
        
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number, p.section,
                   COALESCE(u.first_name, 'Sin asignar') as first_name,
                   COALESCE(u.last_name, '') as last_name,
                   u.phone, u.email,
                   fm.id as movement_id, fm.transaction_date, fm.notes as movement_notes,
                   fm.description as movement_description
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1 AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN financial_movements fm ON fm.reference_type = 'maintenance_fee' AND fm.reference_id = mf.id
            WHERE mf.id = ?
        ");
        $stmt->execute([$feeId]);
        $fee = $stmt->fetch();
        
        if (!$fee) {
            $_SESSION['error_message'] = 'Cuota no encontrada';
            $this->redirect('residents/payments');
        }
        
        // Get residencial name from settings
        $settingStmt = $this->db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'residencial_name' LIMIT 1");
        $residencialName = $settingStmt ? ($settingStmt->fetch()['setting_value'] ?? 'Residencial') : 'Residencial';
        
        $data = [
            'title' => 'Recibo de Pago',
            'fee' => $fee,
            'residencialName' => $residencialName
        ];
        
        $this->view('residents/print_fee_payment', $data);
    }
    
    /**
     * Reporte financiero del residente (mis ingresos y egresos)
     */
    public function financialReport() {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        
        // For committee members and admins, allow full range; for regular residents restrict to their registration date
        $isAdmin = in_array($_SESSION['role'], ['superadmin', 'administrador']);
        $isCommittee = false;
        if ($_SESSION['role'] === 'residente') {
            $stmtC = $this->db->prepare("SELECT is_vigilance_committee FROM users WHERE id = ?");
            $stmtC->execute([$userId]);
            $userRow = $stmtC->fetch();
            $isCommittee = !empty($userRow['is_vigilance_committee']);
        }
        
        // Get resident's property and registration date
        $stmt = $this->db->prepare("
            SELECT r.id as resident_id, r.property_id, p.property_number, r.created_at as registration_date
            FROM residents r
            INNER JOIN properties p ON r.property_id = p.id
            WHERE r.user_id = ? AND r.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $residentInfo = $stmt->fetch();
        
        // Determine minimum allowed date based on role
        $registrationDate = $residentInfo ? date('Y-m-d', strtotime($residentInfo['registration_date'])) : date('Y-01-01');
        
        if ($isAdmin) {
            $defaultFrom = date('Y-01-01');
            $minAllowed = null; // No restriction for admins
        } elseif ($isCommittee) {
            $defaultFrom = date('Y-01-01');
            $minAllowed = null; // No restriction for committee
        } else {
            $defaultFrom = $registrationDate;
            $minAllowed = $registrationDate;
        }
        
        $defaultTo = date('Y-m-d');
        
        $date_from = $this->get('date_from', $defaultFrom);
        $date_to = $this->get('date_to', $defaultTo);
        
        // Enforce minimum date restriction for regular residents (from their registration date)
        if ($minAllowed !== null && $date_from < $minAllowed) {
            $date_from = $minAllowed;
        }
        
        $payments = [];
        $totalPaid = 0;
        $totalPending = 0;
        
        if ($residentInfo) {
            // Cuotas pagadas
            $stmt = $this->db->prepare("
                SELECT mf.*, fm.transaction_date as payment_date, fm.payment_method, fm.amount as paid_amount
                FROM maintenance_fees mf
                LEFT JOIN financial_movements fm ON fm.reference_type = 'maintenance_fee' AND fm.reference_id = mf.id
                WHERE mf.property_id = ?
                AND mf.status = 'paid'
                AND (mf.paid_date BETWEEN ? AND ? OR fm.transaction_date BETWEEN ? AND ?)
                ORDER BY mf.period DESC
            ");
            $stmt->execute([$residentInfo['property_id'], $date_from, $date_to, $date_from, $date_to]);
            $payments = $stmt->fetchAll();
            $totalPaid = array_sum(array_column($payments, 'amount'));
            
            // Cuotas pendientes/vencidas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
                FROM maintenance_fees
                WHERE property_id = ? AND status IN ('pending', 'overdue')
            ");
            $stmt->execute([$residentInfo['property_id']]);
            $pendingInfo = $stmt->fetch();
            $totalPending = $pendingInfo['total'];
        }
        
        $data = [
            'title' => 'Informe Financiero',
            'residentInfo' => $residentInfo,
            'payments' => $payments,
            'totalPaid' => $totalPaid,
            'totalPending' => $totalPending,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'isCommittee' => $isCommittee,
            'isAdmin' => $isAdmin,
            'registrationDate' => $registrationDate,
            'minAllowed' => $minAllowed
        ];
        
        $this->view('residents/financial_report', $data);
    }

    /**
     * Estado de cuenta de residente (admin view)
     */
    public function accountStatement($id = null) {
        if (!$id) {
            $this->redirect('residents');
        }

        $resident = $this->residentModel->findById($id);
        if (!$resident) {
            $_SESSION['error_message'] = 'Residente no encontrado';
            $this->redirect('residents');
        }

        $year = $this->get('year', date('Y'));
        $status_filter = $this->get('status_filter', '');

        $whereExtra = '';
        if ($year === 'all') {
            $params = [$resident['property_id']];
        } else {
            $params = [$resident['property_id'], $year . '%'];
            $whereExtra = 'AND mf.period LIKE ?';
        }
        if ($status_filter !== '') {
            $whereExtra .= ' AND mf.status = ?';
            $params[] = $status_filter;
        }

        // Cuotas del residente en el año seleccionado
        $stmt = $this->db->prepare("
            SELECT mf.*,
                   fm.transaction_date as payment_date,
                   fm.payment_method,
                   fm.amount as paid_amount
            FROM maintenance_fees mf
            LEFT JOIN financial_movements fm ON fm.reference_type = 'maintenance_fee' AND fm.reference_id = mf.id
            WHERE mf.property_id = ?
              $whereExtra
            ORDER BY mf.period DESC
        ");
        $stmt->execute($params);
        $fees = $stmt->fetchAll();

        $totalPaid = array_sum(array_column(array_filter($fees, fn($f) => $f['status'] === 'paid'), 'amount'));
        $totalPending = array_sum(array_column(array_filter($fees, fn($f) => in_array($f['status'], ['pending', 'overdue'])), 'amount'));
        $totalOverdue = array_sum(array_column(array_filter($fees, fn($f) => $f['status'] === 'overdue'), 'amount'));

        $data = [
            'title' => 'Estado de Cuenta - ' . $resident['first_name'] . ' ' . $resident['last_name'],
            'resident' => $resident,
            'fees' => $fees,
            'totalPaid' => $totalPaid,
            'totalPending' => $totalPending,
            'totalOverdue' => $totalOverdue,
            'year' => $year,
            'status_filter' => $status_filter
        ];

        $this->view('residents/account_statement', $data);
    }

    /**
     * Reporte de morosidad
     */
    public function delinquencyReport() {
        $month = $this->get('month', date('Y-m'));
        $date_from = $this->get('date_from', date('Y-01-01'));
        $date_to = $this->get('date_to', date('Y-m-d'));

        // Propiedades con cuotas vencidas
        $stmt = $this->db->prepare("
            SELECT
                p.id as property_id,
                p.property_number,
                p.section,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                COUNT(mf.id) as months_overdue,
                COALESCE(SUM(mf.amount), 0) as total_overdue,
                MIN(mf.period) as oldest_overdue,
                MAX(mf.period) as latest_overdue
            FROM properties p
            INNER JOIN residents r ON r.property_id = p.id AND r.status = 'active' AND r.is_primary = 1
            INNER JOIN users u ON u.id = r.user_id
            INNER JOIN maintenance_fees mf ON mf.property_id = p.id AND mf.status IN ('overdue', 'pending')
            WHERE mf.due_date <= CURDATE()
            GROUP BY p.id, p.property_number, p.section, u.first_name, u.last_name, u.email, u.phone
            ORDER BY months_overdue DESC, total_overdue DESC
        ");
        $stmt->execute();
        $delinquents = $stmt->fetchAll();

        $totalProperties = count($delinquents);
        $totalAmount = array_sum(array_column($delinquents, 'total_overdue'));

        $data = [
            'title' => 'Reporte de Morosidad',
            'delinquents' => $delinquents,
            'totalProperties' => $totalProperties,
            'totalAmount' => $totalAmount,
            'date_from' => $date_from,
            'date_to' => $date_to
        ];

        $this->view('residents/delinquency_report', $data);
    }

    /**
     * Send email notification for fee payment registration or cancellation
     * Sends to superadmin(s) and the resident
     *
     * @param array $fee  Fee data (must include property_number, period, amount, first_name, last_name, resident_email)
     * @param string $type 'paid' or 'cancelled'
     * @param string|null $transactionDate Date of the payment (for paid notifications)
     * @param string|null $paymentMethod   Method used (for paid notifications)
     */
    private function sendFeePaymentNotification(array $fee, string $type, ?string $transactionDate = null, ?string $paymentMethod = null): void {
        try {
            require_once APP_PATH . '/core/Mailer.php';
            $mailer = new Mailer();
            if (!$mailer->isConfigured()) {
                return;
            }

            $siteName = 'ERP Residencial';
            $propertyNumber = $fee['property_number'] ?? '';
            $period = $fee['period'] ?? '';
            $amount = number_format($fee['amount'] ?? 0, 2);
            $residentName = trim(($fee['first_name'] ?? '') . ' ' . ($fee['last_name'] ?? ''));
            $residentEmail = $fee['resident_email'] ?? null;
            $paidDateDisplay = $transactionDate ? date('d/m/Y', strtotime($transactionDate)) : date('d/m/Y');

            if ($type === 'paid') {
                $subject = "✅ Pago de Cuota Registrado - {$propertyNumber} Período {$period}";
                $actionText = 'registrado';
                $headerColor = '#10B981';
                $headerIcon = '✅';
                $headerTitle = 'Pago de Cuota Registrado';
                $bodyExtra = "<p><strong>Fecha de pago:</strong> {$paidDateDisplay}</p>"
                           . ($paymentMethod ? "<p><strong>Método de pago:</strong> " . htmlspecialchars($paymentMethod) . "</p>" : '');
            } else {
                $subject = "❌ Pago de Cuota Cancelado - {$propertyNumber} Período {$period}";
                $actionText = 'cancelado';
                $headerColor = '#EF4444';
                $headerIcon = '❌';
                $headerTitle = 'Pago de Cuota Cancelado';
                $bodyExtra = "<p>La cuota ha sido revertida a estado pendiente/vencido.</p>";
            }

            $body = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: {$headerColor}; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                .info-box { background-color: #fff; padding: 15px; border-left: 4px solid {$headerColor}; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #6b7280; }
            </style>
            </head>
            <body>
            <div class='container'>
                <div class='header'><h1>{$headerIcon} {$headerTitle}</h1></div>
                <div class='content'>
                    <p>Se ha <strong>{$actionText}</strong> un pago de cuota de mantenimiento.</p>
                    <div class='info-box'>
                        <p><strong>Residente:</strong> " . htmlspecialchars($residentName) . "</p>
                        <p><strong>Propiedad:</strong> " . htmlspecialchars($propertyNumber) . "</p>
                        <p><strong>Período:</strong> " . htmlspecialchars($period) . "</p>
                        <p><strong>Monto:</strong> \${$amount} MXN</p>
                        {$bodyExtra}
                    </div>
                    <p style='font-size:12px;color:#6b7280;'>Este es un mensaje automático del sistema.</p>
                </div>
                <div class='footer'><p>&copy; " . date('Y') . " {$siteName}. Todos los derechos reservados.</p></div>
            </div>
            </body></html>";

            // Get superadmin emails
            $adminStmt = $this->db->query("SELECT email FROM users WHERE role IN ('superadmin', 'administrador') AND status = 'active' AND email IS NOT NULL AND email != ''");
            $adminEmails = $adminStmt->fetchAll(PDO::FETCH_COLUMN);

            // Build recipient list: admin emails + resident email (deduplicated)
            $residentEmails = ($residentEmail && filter_var($residentEmail, FILTER_VALIDATE_EMAIL)) ? [$residentEmail] : [];
            $allEmails = array_merge($adminEmails, $residentEmails);
            $recipients = array_unique(array_filter($allEmails));

            foreach ($recipients as $recipientEmail) {
                $mailer->send($recipientEmail, $subject, $body);
            }
        } catch (Exception $e) {
            error_log('sendFeePaymentNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Importar residentes desde Excel
     */
    public function importExcel() {
        $data = [
            'title' => 'Importar Residentes desde Excel',
            'errors' => [],
            'imported' => 0,
            'skipped' => 0,
            'results' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['excel_file'])) {
            $file = $_FILES['excel_file'];

            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $data['errors'][] = 'Error al subir el archivo. Código: ' . $file['error'];
                $this->view('residents/import_excel', $data);
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['csv', 'txt'])) {
                $data['errors'][] = 'Solo se aceptan archivos CSV. Use la plantilla provista.';
                $this->view('residents/import_excel', $data);
                return;
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $allowedMimes = ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'];
            if (!in_array($mime, $allowedMimes)) {
                $data['errors'][] = 'Tipo de archivo no válido.';
                $this->view('residents/import_excel', $data);
                return;
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                $data['errors'][] = 'El archivo no puede superar 5MB.';
                $this->view('residents/import_excel', $data);
                return;
            }

            // Parse CSV
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                $data['errors'][] = 'No se pudo leer el archivo.';
                $this->view('residents/import_excel', $data);
                return;
            }

            $lineNum = 0;
            $headers = [];
            $rows = [];
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $lineNum++;
                if ($lineNum === 1) {
                    $headers = array_map('strtolower', array_map('trim', $row));
                    continue;
                }
                if (empty(array_filter($row))) continue;
                // Normalize row length to match headers (truncate extra or pad missing columns)
                $normalizedRow = array_slice(array_pad($row, count($headers), ''), 0, count($headers));
                $rows[] = array_combine($headers, $normalizedRow);
            }
            fclose($handle);

            // Required columns
            $required = ['nombre', 'apellido', 'email', 'telefono', 'propiedad', 'relacion'];
            foreach ($required as $col) {
                if (!in_array($col, $headers)) {
                    $data['errors'][] = "Columna requerida faltante: '{$col}'. Verifique la plantilla.";
                }
            }

            if (!empty($data['errors'])) {
                $this->view('residents/import_excel', $data);
                return;
            }

            foreach ($rows as $rowNum => $row) {
                $lineNum = $rowNum + 2;
                $email = trim($row['email'] ?? '');
                $nombre = trim($row['nombre'] ?? '');
                $apellido = trim($row['apellido'] ?? '');
                $telefono = trim($row['telefono'] ?? '');
                $propNum = trim($row['propiedad'] ?? '');
                $relacion = strtolower(trim($row['relacion'] ?? 'propietario'));
                $seccion = trim($row['seccion'] ?? '');
                $password = trim($row['password'] ?? '');

                if (empty($email) || empty($nombre) || empty($propNum)) {
                    $data['results'][] = ['line' => $lineNum, 'status' => 'error', 'message' => "Línea {$lineNum}: nombre, email y propiedad son requeridos"];
                    $data['skipped']++;
                    continue;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data['results'][] = ['line' => $lineNum, 'status' => 'error', 'message' => "Línea {$lineNum}: email inválido ({$email})"];
                    $data['skipped']++;
                    continue;
                }

                // Check if email already exists
                $checkStmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
                $checkStmt->execute([$email]);
                if ($checkStmt->fetch()) {
                    $data['results'][] = ['line' => $lineNum, 'status' => 'skip', 'message' => "Línea {$lineNum}: email ya registrado ({$email})"];
                    $data['skipped']++;
                    continue;
                }

                // Find or create property
                $propStmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ?");
                $propStmt->execute([$propNum]);
                $property = $propStmt->fetch();

                if (!$property) {
                    // Create the property
                    $insertPropStmt = $this->db->prepare("INSERT INTO properties (property_number, section, status) VALUES (?, ?, 'ocupada')");
                    $insertPropStmt->execute([$propNum, $seccion ?: null]);
                    $propertyId = $this->db->lastInsertId();
                } else {
                    $propertyId = $property['id'];
                }

                try {
                    $this->db->beginTransaction();

                    // Generate username from email
                    $baseUsername = strtolower(explode('@', $email)[0]);
                    $username = $baseUsername;
                    $suffix = 1;
                    while (true) {
                        $uCheck = $this->db->prepare("SELECT id FROM users WHERE username = ?");
                        $uCheck->execute([$username]);
                        if (!$uCheck->fetch()) break;
                        $username = $baseUsername . $suffix;
                        $suffix++;
                    }

                    $passwordHash = password_hash($password ?: bin2hex(random_bytes(6)), PASSWORD_BCRYPT);

                    $userStmt = $this->db->prepare("
                        INSERT INTO users (username, email, password, first_name, last_name, phone, role, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'residente', 'active')
                    ");
                    $userStmt->execute([$username, $email, $passwordHash, $nombre, $apellido, $telefono]);
                    $userId = $this->db->lastInsertId();

                    $resStmt = $this->db->prepare("
                        INSERT INTO residents (user_id, property_id, relationship, is_primary, status)
                        VALUES (?, ?, ?, 1, 'active')
                    ");
                    $resStmt->execute([$userId, $propertyId, $relacion]);

                    $this->db->commit();

                    AuditController::log('create', "Residente importado desde Excel: {$email}", 'residents', $this->db->lastInsertId());
                    $data['results'][] = ['line' => $lineNum, 'status' => 'ok', 'message' => "Línea {$lineNum}: {$nombre} {$apellido} importado correctamente"];
                    $data['imported']++;
                } catch (PDOException $e) {
                    $this->db->rollBack();
                    error_log('importExcel row error: ' . $e->getMessage());
                    $data['results'][] = ['line' => $lineNum, 'status' => 'error', 'message' => "Línea {$lineNum}: error al importar ({$e->getMessage()})"];
                    $data['skipped']++;
                }
            }
        }

        $this->view('residents/import_excel', $data);
    }

    /**
     * Solicitudes de Servicio del residente (filtradas por su propiedad)
     */
    public function serviceRequests() {
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

        $status   = $this->get('status', '');
        $priority = $this->get('priority', '');
        $search   = $this->get('search', '');

        $where  = ["sr.property_id = ?"];
        $params = [$resident['property_id']];

        if ($status !== '') {
            $where[] = "sr.status = ?";
            $params[] = $status;
        }
        if ($priority !== '') {
            $where[] = "sr.priority = ?";
            $params[] = $priority;
        }
        if ($search !== '') {
            $where[] = "(sr.title LIKE ? OR sr.description LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $stmt = $this->db->prepare("
            SELECT sr.*, prov.company_name as provider_name
            FROM provider_service_requests sr
            LEFT JOIN providers prov ON sr.provider_id = prov.id
            $whereClause
            ORDER BY
                CASE sr.priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END,
                sr.created_at DESC
        ");
        $stmt->execute($params);
        $requests = $stmt->fetchAll();

        // Fetch active providers for the dropdown
        $provStmt = $this->db->query("SELECT id, company_name, category FROM providers WHERE status = 'active' ORDER BY company_name");
        $providers = $provStmt->fetchAll();

        $data = [
            'title'     => 'Solicitudes de Servicio',
            'resident'  => $resident,
            'requests'  => $requests,
            'providers' => $providers,
            'filters'   => compact('status', 'priority', 'search'),
        ];

        $this->view('residents/service_requests', $data);
    }

    /**
     * Create a new service request from resident
     */
    public function createServiceRequest() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        if ($_SESSION['role'] !== 'residente') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }

        try {
            $userId = $_SESSION['user_id'];

            // Get resident's property
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
                echo json_encode(['success' => false, 'message' => 'Residente no encontrado']);
                exit;
            }

            // Validate required fields
            $title = trim($this->post('title'));
            $description = trim($this->post('description'));
            $priority = $this->post('priority', 'medium');

            if (empty($title)) {
                echo json_encode(['success' => false, 'message' => 'El título es requerido']);
                exit;
            }

            if (empty($description)) {
                echo json_encode(['success' => false, 'message' => 'La descripción es requerida']);
                exit;
            }

            // Validate priority
            $validPriorities = ['low', 'medium', 'high', 'urgent'];
            if (!in_array($priority, $validPriorities)) {
                $priority = 'medium';
            }

            // Get optional fields
            $category   = trim($this->post('category', ''));
            $area       = trim($this->post('area', ''));
            $requestedDate = $this->post('requested_date', null);
            $notes      = trim($this->post('notes', ''));
            $providerId = $this->post('provider_id', null);

            // Validate provider_id if provided
            if ($providerId !== null && $providerId !== '') {
                $providerId = intval($providerId);
                $provCheck = $this->db->prepare("SELECT id FROM providers WHERE id = ? AND status = 'active'");
                $provCheck->execute([$providerId]);
                if (!$provCheck->fetch()) {
                    $providerId = null;
                }
            } else {
                $providerId = null;
            }

            // Validate requested_date if provided
            if ($requestedDate && !empty($requestedDate)) {
                $dateObj = \DateTime::createFromFormat('Y-m-d', $requestedDate);
                if (!$dateObj || $dateObj->format('Y-m-d') !== $requestedDate) {
                    $requestedDate = null;
                }
            } else {
                $requestedDate = null;
            }

            // Insert service request
            $stmt = $this->db->prepare("
                INSERT INTO provider_service_requests 
                (provider_id, title, description, category, area, property_id, priority, status, 
                 requested_date, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $providerId,
                $title,
                $description,
                $category ?: null,
                $area ?: null,
                $resident['property_id'],
                $priority,
                $requestedDate,
                $notes ?: null,
                $userId
            ]);

            $requestId = $this->db->lastInsertId();

            // Log audit
            if (class_exists('AuditController')) {
                AuditController::log(
                    'create',
                    'Solicitud de servicio creada por residente: ' . $title,
                    'provider_service_requests',
                    $requestId
                );
            }

            echo json_encode([
                'success' => true,
                'message' => 'Solicitud enviada exitosamente',
                'request_id' => $requestId
            ]);

        } catch (PDOException $e) {
            error_log('createServiceRequest error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al crear la solicitud. Intente nuevamente.']);
        }

        exit;
    }
}
