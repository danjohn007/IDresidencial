<?php
/**
 * Controlador de Perfil de Usuario
 */

class ProfileController extends Controller {
    
    private $userModel;
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->userModel = $this->model('User');
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal del perfil
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        $data = [
            'title' => 'Mi Perfil',
            'user' => $user,
            'error' => '',
            'success' => ''
        ];
        
        $this->view('profile/index', $data);
    }
    
    /**
     * Actualizar información de contacto
     */
    public function updateContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('profile');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $data = [
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'phone' => $this->post('phone'),
            'email' => $this->post('email')
        ];
        
        // Validate email is unique (excluding current user)
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $userId]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = 'El correo electrónico ya está en uso por otro usuario';
            $this->redirect('profile');
            return;
        }
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, phone = ?, email = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([...array_values($data), $userId])) {
            // Update session variables
            $_SESSION['first_name'] = $data['first_name'];
            $_SESSION['last_name'] = $data['last_name'];
            
            $_SESSION['success_message'] = 'Información actualizada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al actualizar la información';
        }
        
        $this->redirect('profile');
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('profile');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $currentPassword = $this->post('current_password');
        $newPassword = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');
        
        // Validate new password
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = 'Las contraseñas nuevas no coinciden';
            $this->redirect('profile');
            return;
        }
        
        if (strlen($newPassword) < 6) {
            $_SESSION['error_message'] = 'La contraseña debe tener al menos 6 caracteres';
            $this->redirect('profile');
            return;
        }
        
        // Verify current password
        $user = $this->userModel->findById($userId);
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['error_message'] = 'La contraseña actual es incorrecta';
            $this->redirect('profile');
            return;
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($stmt->execute([$hashedPassword, $userId])) {
            $_SESSION['success_message'] = 'Contraseña actualizada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al actualizar la contraseña';
        }
        
        $this->redirect('profile');
    }
}
