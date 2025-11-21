<?php
/**
 * Controlador de Autenticación
 */

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
                    if ($user['status'] !== 'active') {
                        $data['error'] = 'Tu cuenta está inactiva. Contacta al administrador.';
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
                }
            }
        }
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        session_destroy();
        $this->redirect('auth/login');
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
            $userData = [
                'username' => $this->post('username'),
                'email' => $this->post('email'),
                'password' => $this->post('password'),
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'phone' => $this->post('phone'),
                'role' => 'residente'
            ];
            
            // Validaciones básicas
            if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
                $data['error'] = 'Por favor, completa todos los campos requeridos';
            } elseif ($this->userModel->findByUsername($userData['username'])) {
                $data['error'] = 'El nombre de usuario ya existe';
            } elseif ($this->userModel->findByEmail($userData['email'])) {
                $data['error'] = 'El correo electrónico ya está registrado';
            } else {
                if ($this->userModel->create($userData)) {
                    $data['success'] = 'Usuario registrado exitosamente. Ahora puedes iniciar sesión.';
                } else {
                    $data['error'] = 'Error al registrar el usuario. Inténtalo de nuevo.';
                }
            }
        }
        
        $this->view('auth/register', $data);
    }
}
