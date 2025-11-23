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
    
    /**
     * Actualizar foto de perfil
     */
    public function updatePhoto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('profile');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Verificar que se subió un archivo
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = 'Error al subir la foto. Por favor, intenta de nuevo.';
            $this->redirect('profile');
            return;
        }
        
        $file = $_FILES['photo'];
        
        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error_message'] = 'Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG o GIF.';
            $this->redirect('profile');
            return;
        }
        
        // Validar tamaño (máximo 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error_message'] = 'La imagen es demasiado grande. El tamaño máximo es 5MB.';
            $this->redirect('profile');
            return;
        }
        
        // Crear directorio si no existe
        $uploadDir = PUBLIC_PATH . '/uploads/profiles';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $userId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        // Obtener foto actual para eliminarla
        $user = $this->userModel->findById($userId);
        $oldPhoto = $user['photo'];
        
        // Mover archivo subido
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Actualizar base de datos
            $stmt = $this->db->prepare("UPDATE users SET photo = ? WHERE id = ?");
            
            if ($stmt->execute([$filename, $userId])) {
                // Eliminar foto antigua si existe
                if ($oldPhoto && file_exists($uploadDir . '/' . $oldPhoto)) {
                    unlink($uploadDir . '/' . $oldPhoto);
                }
                
                $_SESSION['success_message'] = 'Foto de perfil actualizada exitosamente';
            } else {
                $_SESSION['error_message'] = 'Error al actualizar la foto en la base de datos';
                // Eliminar archivo subido si falla la actualización
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
        } else {
            $_SESSION['error_message'] = 'Error al guardar la foto. Por favor, intenta de nuevo.';
        }
        
        $this->redirect('profile');
    }
}
