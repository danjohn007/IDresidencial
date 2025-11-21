<?php
/**
 * Controller Base - Clase base para todos los controladores
 */

class Controller {
    
    /**
     * Cargar un modelo
     */
    protected function model($model) {
        $modelPath = APP_PATH . '/models/' . $model . '.php';
        
        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        }
        
        die("Model $model not found");
    }
    
    /**
     * Cargar una vista
     */
    protected function view($view, $data = []) {
        $viewPath = APP_PATH . '/views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            extract($data);
            require_once $viewPath;
        } else {
            die("View $view not found");
        }
    }
    
    /**
     * Redireccionar
     */
    protected function redirect($url) {
        header('Location: ' . BASE_URL . '/' . $url);
        exit();
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Obtener usuario actual
     */
    protected function getCurrentUser() {
        if ($this->isAuthenticated()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name']
            ];
        }
        return null;
    }
    
    /**
     * Verificar rol del usuario
     */
    protected function hasRole($roles) {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array($_SESSION['role'], $roles);
    }
    
    /**
     * Requerir autenticación
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('auth/login');
        }
    }
    
    /**
     * Requerir rol específico
     */
    protected function requireRole($roles) {
        $this->requireAuth();
        
        if (!$this->hasRole($roles)) {
            $this->redirect('dashboard');
        }
    }
    
    /**
     * Responder con JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Obtener datos POST
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Obtener datos GET
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Validar CSRF token
     */
    protected function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $this->post('csrf_token');
            if (!$token || $token !== $_SESSION['csrf_token']) {
                die('CSRF token validation failed');
            }
        }
    }
    
    /**
     * Generar CSRF token
     */
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
