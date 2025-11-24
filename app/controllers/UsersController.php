<?php
/**
 * Controlador de Usuarios
 */

class UsersController extends Controller {
    
    private $userModel;
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin']);
        $this->userModel = $this->model('User');
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de usuarios
     */
    public function index() {
        // Get filter parameters
        $filters = [
            'role' => $this->get('role'),
            'status' => $this->get('status'),
            'search' => $this->get('search', '')
        ];
        
        // Build query
        $where = [];
        $params = [];
        
        if ($filters['role']) {
            $where[] = "role = ?";
            $params[] = $filters['role'];
        }
        
        if ($filters['status']) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get filtered users
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            $whereClause
            ORDER BY created_at DESC
        ");
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        // Obtener estadísticas (sin filtros para mostrar totales reales)
        $stats = [
            'total' => $this->userModel->count([]),
            'superadmin' => $this->userModel->count(['role' => 'superadmin']),
            'administrador' => $this->userModel->count(['role' => 'administrador']),
            'guardia' => $this->userModel->count(['role' => 'guardia']),
            'residente' => $this->userModel->count(['role' => 'residente']),
            'active' => $this->userModel->count(['status' => 'active']),
            'inactive' => $this->userModel->count(['status' => 'inactive'])
        ];
        
        $data = [
            'title' => 'Gestión de Usuarios',
            'users' => $users,
            'stats' => $stats,
            'filters' => $filters
        ];
        
        $this->view('users/index', $data);
    }
    
    /**
     * Crear nuevo usuario
     */
    public function create() {
        $data = [
            'title' => 'Nuevo Usuario',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role = $this->post('role');
            
            // Prevent creating residents through this interface
            if ($role === 'residente') {
                $data['error'] = 'Los residentes deben crearse desde el módulo de Residentes.';
                $this->view('users/create', $data);
                return;
            }
            
            // Generate username from email
            $email = $this->post('email');
            $baseUsername = strstr($email, '@', true);
            
            // Ensure username is unique by adding suffix if needed
            $username = $baseUsername;
            $counter = 1;
            while ($this->userModel->findByUsername($username)) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $this->post('password'),
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'phone' => $this->post('phone'),
                'role' => $role,
                'status' => $this->post('status', 'active')
            ];
            
            if ($this->userModel->create($userData)) {
                $_SESSION['success_message'] = 'Usuario creado exitosamente';
                $this->redirect('users');
            } else {
                $data['error'] = 'Error al crear el usuario. Verifique que el correo no exista.';
            }
        }
        
        $this->view('users/create', $data);
    }
    
    /**
     * Editar usuario
     */
    public function edit($id) {
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error_message'] = 'Usuario no encontrado';
            $this->redirect('users');
        }
        
        $data = [
            'title' => 'Editar Usuario',
            'user' => $user,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'email' => $this->post('email'),
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'phone' => $this->post('phone'),
                'role' => $this->post('role'),
                'status' => $this->post('status')
            ];
            
            // Actualizar contraseña si se proporcionó
            if ($this->post('password')) {
                $userData['password'] = password_hash($this->post('password'), PASSWORD_DEFAULT);
            }
            
            if ($this->userModel->update($id, $userData)) {
                $_SESSION['success_message'] = 'Usuario actualizado exitosamente';
                $this->redirect('users');
            } else {
                $data['error'] = 'Error al actualizar el usuario';
            }
        }
        
        $this->view('users/edit', $data);
    }
    
    /**
     * Ver detalles de usuario (public method)
     */
    public function viewDetails($id) {
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error_message'] = 'Usuario no encontrado';
            $this->redirect('users');
        }
        
        // Obtener actividad reciente
        $stmt = $this->db->prepare("
            SELECT * FROM audit_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $stmt->execute([$id]);
        $activity = $stmt->fetchAll();
        
        $data = [
            'title' => 'Detalles de Usuario',
            'user' => $user,
            'activity' => $activity
        ];
        
        $this->view('users/viewDetails', $data);
    }
    
    /**
     * Eliminar usuario
     */
    public function delete($id) {
        // No permitir eliminar el propio usuario
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'No puedes eliminar tu propio usuario';
            $this->redirect('users');
        }
        
        if ($this->userModel->delete($id)) {
            $_SESSION['success_message'] = 'Usuario eliminado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el usuario';
        }
        
        $this->redirect('users');
    }
    
    /**
     * Cambiar estado de usuario
     */
    public function toggleStatus($id) {
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $_SESSION['error_message'] = 'Usuario no encontrado';
            $this->redirect('users');
        }
        
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        
        if ($this->userModel->update($id, ['status' => $newStatus])) {
            $_SESSION['success_message'] = 'Estado del usuario actualizado';
        } else {
            $_SESSION['error_message'] = 'Error al actualizar el estado';
        }
        
        $this->redirect('users');
    }
}
