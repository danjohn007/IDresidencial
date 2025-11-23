<?php
/**
 * Controlador de Autenticación
 */

require_once APP_PATH . '/controllers/AuditController.php';

class AuthController extends Controller {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = $this->model('User');
    }
    
    /**
     * Mostrar formulario de login
     */
    public function login() {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }
        
        $data = [
            'title' => 'Iniciar Sesión',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->post('username');
            $password = $this->post('password');
            
            if (empty($username) || empty($password)) {
                $data['error'] = 'Por favor, ingresa usuario y contraseña';
            } else {
                $user = $this->userModel->findByUsername($username);
                
                if (!$user) {
                    // También intentar buscar por email
                    $user = $this->userModel->findByEmail($username);
                }
                
                if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                    // Verificar estado del usuario
                    if ($user['status'] === 'pending') {
                        $data['error'] = 'Tu cuenta está pendiente de verificación de correo electrónico y aprobación del administrador.';
                        AuditController::log('login_failed', 'Intento de login con cuenta pendiente: ' . $username, 'users', $user['id']);
                    } elseif ($user['status'] !== 'active') {
                        $data['error'] = 'Tu cuenta está inactiva o bloqueada. Contacta al administrador.';
                        AuditController::log('login_failed', 'Intento de login con cuenta inactiva: ' . $username, 'users', $user['id']);
                    } else {
                        // Iniciar sesión
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['first_name'] = $user['first_name'];
                        $_SESSION['last_name'] = $user['last_name'];
                        
                        // Actualizar último login
                        $this->userModel->updateLastLogin($user['id']);
                        
                        // Log successful login
                        AuditController::log('login', 'Usuario inició sesión: ' . $user['username'], 'users', $user['id']);
                        
                        // Redirigir según rol
                        switch ($user['role']) {
                            case 'guardia':
                                $this->redirect('guard');
                                break;
                            default:
                                $this->redirect('dashboard');
                                break;
                        }
                    }
                } else {
                    $data['error'] = 'Usuario o contraseña incorrectos';
                    AuditController::log('login_failed', 'Intento de login fallido con usuario: ' . $username, 'users', null);
                }
            }
        }
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'unknown';
        
        if ($userId) {
            AuditController::log('logout', 'Usuario cerró sesión: ' . $username, 'users', $userId);
        }
        
        session_destroy();
        $this->redirect('auth/login');
    }
    
    /**
     * Mostrar formulario de recuperación de contraseña
     */
    public function forgotPassword() {
        $data = [
            'title' => 'Recuperar Contraseña',
            'error' => '',
            'success' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->post('email');
            
            if (empty($email)) {
                $data['error'] = 'Por favor, ingresa tu correo electrónico';
            } else {
                $user = $this->userModel->findByEmail($email);
                
                if ($user) {
                    // Generar token de recuperación
                    $token = bin2hex(random_bytes(32));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Guardar token en la base de datos
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("
                        INSERT INTO password_resets (user_id, token, expires_at) 
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE token = ?, expires_at = ?
                    ");
                    $stmt->execute([$user['id'], $token, $expiry, $token, $expiry]);
                    
                    // En producción, aquí se enviaría un email con el link de recuperación
                    // Por ahora, mostraremos el token en pantalla
                    $resetLink = BASE_URL . '/auth/resetPassword?token=' . $token;
                    $data['success'] = "Se ha generado un enlace de recuperación. Por favor, accede a: <a href='$resetLink' class='underline'>$resetLink</a>";
                } else {
                    // No revelar si el email existe o no por seguridad
                    $data['success'] = 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación.';
                }
            }
        }
        
        $this->view('auth/forgot_password', $data);
    }
    
    /**
     * Restablecer contraseña
     */
    public function resetPassword() {
        $token = $this->get('token');
        
        $data = [
            'title' => 'Restablecer Contraseña',
            'error' => '',
            'success' => '',
            'token' => $token
        ];
        
        if (!$token) {
            $data['error'] = 'Token inválido';
            $this->view('auth/reset_password', $data);
            return;
        }
        
        // Verificar token
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT pr.*, u.email, u.username 
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        
        if (!$reset) {
            $data['error'] = 'Token inválido o expirado';
            $this->view('auth/reset_password', $data);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $this->post('password');
            $confirmPassword = $this->post('confirm_password');
            
            if (empty($password) || empty($confirmPassword)) {
                $data['error'] = 'Por favor, completa todos los campos';
            } elseif ($password !== $confirmPassword) {
                $data['error'] = 'Las contraseñas no coinciden';
            } elseif (strlen($password) < 6) {
                $data['error'] = 'La contraseña debe tener al menos 6 caracteres';
            } else {
                // Actualizar contraseña
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $reset['user_id']]);
                
                // Marcar token como usado
                $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->execute([$token]);
                
                $_SESSION['success_message'] = 'Contraseña restablecida exitosamente. Ahora puedes iniciar sesión.';
                $this->redirect('auth/login');
            }
        }
        
        $this->view('auth/reset_password', $data);
    }
    
    /**
     * Registro de nuevo usuario (solo para testing)
     */
    public function register() {
        $data = [
            'title' => 'Registrar Usuario',
            'error' => '',
            'success' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CAPTCHA
            $captchaAnswer = $this->post('captcha');
            $correctAnswer = $_SESSION['captcha_answer'] ?? null;
            
            if (empty($captchaAnswer) || $captchaAnswer != $correctAnswer) {
                $data['error'] = 'La verificación matemática es incorrecta';
                $this->view('auth/register', $data);
                return;
            }
            
            // Validate terms acceptance
            if (!$this->post('accept_terms')) {
                $data['error'] = 'Debes aceptar los términos y condiciones';
                $this->view('auth/register', $data);
                return;
            }
            
            // Validate phone (10 digits)
            $phone = $this->post('phone');
            if (!preg_match('/^[0-9]{10}$/', $phone)) {
                $data['error'] = 'El teléfono debe contener exactamente 10 dígitos';
                $this->view('auth/register', $data);
                return;
            }
            
            $email = $this->post('email');
            $password = $this->post('password');
            $first_name = $this->post('first_name');
            $last_name = $this->post('last_name');
            $property_id = $this->post('property_id');
            
            // Validaciones básicas
            if (empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($property_id)) {
                $data['error'] = 'Por favor, completa todos los campos requeridos';
            } elseif (strlen($password) < 6) {
                $data['error'] = 'La contraseña debe tener al menos 6 caracteres';
            } elseif ($this->userModel->findByEmail($email)) {
                $data['error'] = 'El correo electrónico ya está registrado';
            } else {
                // Generate username from email
                $username = explode('@', $email)[0] . '_' . rand(1000, 9999);
                
                // Generate email verification token
                $email_verification_token = bin2hex(random_bytes(32));
                
                // Create user with pending status
                $db = Database::getInstance()->getConnection();
                try {
                    $db->beginTransaction();
                    
                    // Insert user
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        INSERT INTO users (username, email, password, first_name, last_name, phone, role, status, email_verification_token) 
                        VALUES (?, ?, ?, ?, ?, ?, 'residente', 'pending', ?)
                    ");
                    $stmt->execute([$username, $email, $hashedPassword, $first_name, $last_name, $phone, $email_verification_token]);
                    $userId = $db->lastInsertId();
                    
                    // Create resident record linked to property
                    $stmt = $db->prepare("
                        INSERT INTO residents (user_id, property_id, relationship, is_primary, status) 
                        VALUES (?, ?, 'propietario', 1, 'pending')
                    ");
                    $stmt->execute([$userId, $property_id]);
                    
                    $db->commit();
                    
                    // Log the registration
                    AuditController::log('register', 'Nuevo registro pendiente de aprobación: ' . $email, 'users', $userId);
                    
                    // In production, send verification email here
                    // For now, show success message
                    $verificationLink = BASE_URL . '/auth/verifyEmail?token=' . $email_verification_token;
                    
                    $data['success'] = 'Registro exitoso. Tu cuenta está pendiente de verificación de correo electrónico y aprobación del administrador. 
                                       <br><br>Por favor, verifica tu correo en: <a href="' . $verificationLink . '" class="underline font-medium">Verificar correo</a>';
                    
                } catch (PDOException $e) {
                    $db->rollBack();
                    $data['error'] = 'Error al registrar el usuario: ' . $e->getMessage();
                }
            }
        }
        
        $this->view('auth/register', $data);
    }
    
    /**
     * Verify email address
     */
    public function verifyEmail() {
        $token = $this->get('token');
        
        if (empty($token)) {
            $_SESSION['error_message'] = 'Token de verificación no válido';
            $this->redirect('auth/login');
            return;
        }
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE users 
            SET email_verified_at = NOW(), email_verification_token = NULL 
            WHERE email_verification_token = ? AND email_verified_at IS NULL
        ");
        $stmt->execute([$token]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = 'Correo electrónico verificado exitosamente. Tu cuenta está pendiente de aprobación del administrador.';
        } else {
            $_SESSION['error_message'] = 'Token de verificación inválido o ya utilizado';
        }
        
        $this->redirect('auth/login');
    }
}
