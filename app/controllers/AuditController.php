<?php
/**
 * Controlador de Auditoría
 */

class AuditController extends Controller {
    
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin']);
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de auditoría
     */
    public function index() {
        $filters = [
            'user_id' => $this->get('user_id'),
            'action' => $this->get('action'),
            'date_from' => $this->get('date_from'),
            'date_to' => $this->get('date_to')
        ];
        
        // Pagination
        $page = max(1, (int)$this->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $where = [];
        $params = [];
        
        if ($filters['user_id']) {
            $where[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if ($filters['action']) {
            $where[] = "al.action = ?";
            $params[] = $filters['action'];
        }
        
        if ($filters['date_from']) {
            $where[] = "DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if ($filters['date_to']) {
            $where[] = "DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get total count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            $whereClause
        ");
        $stmt->execute($params);
        $totalRecords = $stmt->fetch()['total'];
        $totalPages = ceil($totalRecords / $perPage);
        
        // Obtener logs con paginación
        $stmt = $this->db->prepare("
            SELECT al.*, u.username, u.first_name, u.last_name, u.role
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            $whereClause
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
        
        // Obtener todos los usuarios para el filtro
        $stmt = $this->db->query("SELECT id, username, first_name, last_name FROM users ORDER BY username");
        $users = $stmt->fetchAll();
        
        // Estadísticas
        $stats = [
            'total_today' => 0,
            'total_week' => 0,
            'unique_users' => 0
        ];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM audit_logs WHERE DATE(created_at) = CURDATE()");
        $stats['total_today'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['total_week'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->query("SELECT COUNT(DISTINCT user_id) as total FROM audit_logs WHERE DATE(created_at) = CURDATE()");
        $stats['unique_users'] = $stmt->fetch()['total'];
        
        $data = [
            'title' => 'Auditoría del Sistema',
            'logs' => $logs,
            'users' => $users,
            'filters' => $filters,
            'stats' => $stats,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords
            ]
        ];
        
        $this->view('audit/index', $data);
    }
    
    /**
     * Ver detalles de un log
     */
    public function viewDetails($id) {
        $stmt = $this->db->prepare("
            SELECT al.*, u.username, u.first_name, u.last_name, u.role, u.email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.id = ?
        ");
        $stmt->execute([$id]);
        $log = $stmt->fetch();
        
        if (!$log) {
            $_SESSION['error_message'] = 'Log no encontrado';
            $this->redirect('audit');
        }
        
        $data = [
            'title' => 'Detalle de Auditoría',
            'log' => $log
        ];
        
        $this->view('audit/viewDetails', $data);
    }
    
    /**
     * Registrar una acción en el log de auditoría
     */
    public static function log($action, $description, $tableName = null, $recordId = null) {
        try {
            $db = Database::getInstance()->getConnection();
            $userId = $_SESSION['user_id'] ?? null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, description, table_name, record_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $action,
                $description,
                $tableName,
                $recordId,
                $ipAddress,
                $userAgent
            ]);
        } catch (Exception $e) {
            // Silent fail - we don't want logging errors to break the application
            error_log("Error logging audit: " . $e->getMessage());
        }
    }
    
    /**
     * Limpiar logs antiguos
     */
    public function cleanup() {
        // Eliminar logs más antiguos de 6 meses
        $stmt = $this->db->prepare("DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)");
        $stmt->execute();
        $deleted = $stmt->rowCount();
        
        $_SESSION['success_message'] = "Se eliminaron {$deleted} registros antiguos";
        $this->redirect('audit');
    }
    
    /**
     * Auto-Optimización del Sistema
     */
    public function optimization() {
        $data = [
            'title' => 'Auto-Optimización del Sistema'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $optimizationSettings = [
                'cache_enabled' => $this->post('cache_enabled', '1'),
                'cache_ttl' => $this->post('cache_ttl', '3600'),
                'query_cache_enabled' => $this->post('query_cache_enabled', '1'),
                'max_records_per_page' => $this->post('max_records_per_page', '50'),
                'image_optimization' => $this->post('image_optimization', '1'),
                'lazy_loading' => $this->post('lazy_loading', '1'),
                'minify_assets' => $this->post('minify_assets', '0'),
                'session_timeout' => $this->post('session_timeout', '3600')
            ];
            
            foreach ($optimizationSettings as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            // Ejecutar optimizaciones inmediatas si se solicita
            if ($this->post('run_optimization')) {
                $this->runOptimizations();
            }
            
            self::log('update', 'Configuración de optimización actualizada', 'system_settings', null);
            $_SESSION['success_message'] = 'Configuración de optimización actualizada';
            $this->redirect('audit/optimization');
        }
        
        // Obtener configuración actual
        $stmt = $this->db->query("SELECT * FROM system_settings WHERE setting_key LIKE 'cache_%' OR setting_key LIKE '%_optimization' OR setting_key LIKE 'lazy_%' OR setting_key LIKE 'max_%' OR setting_key = 'session_timeout' OR setting_key LIKE 'query_%' OR setting_key LIKE 'minify_%'");
        $currentSettings = [];
        while ($row = $stmt->fetch()) {
            $currentSettings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Estadísticas del sistema
        $stats = $this->getSystemStats();
        
        $data['current'] = $currentSettings;
        $data['stats'] = $stats;
        $this->view('audit/optimization', $data);
    }
    
    /**
     * Ejecutar optimizaciones del sistema
     */
    private function runOptimizations() {
        try {
            // Optimizar tablas
            $tables = ['users', 'residents', 'properties', 'visits', 'access_logs', 
                      'maintenance_fees', 'financial_movements', 'reservations', 'audit_logs'];
            
            foreach ($tables as $table) {
                $this->db->exec("OPTIMIZE TABLE {$table}");
            }
            
            // Limpiar logs antiguos
            $this->db->exec("DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 180 DAY)");
            
            // Limpiar sesiones expiradas
            $stmt = $this->db->query("SHOW TABLES LIKE 'password_resets'");
            if ($stmt->rowCount() > 0) {
                $this->db->exec("DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Optimization error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas del sistema
     */
    private function getSystemStats() {
        $stats = [];
        
        try {
            // Tamaño de la base de datos
            $stmt = $this->db->query("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS db_size_mb
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ");
            $result = $stmt->fetch();
            $stats['db_size'] = $result['db_size_mb'] . ' MB';
            
            // Número de registros en tablas principales
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM users");
            $stats['total_users'] = $stmt->fetch()['total'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM residents");
            $stats['total_residents'] = $stmt->fetch()['total'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM visits");
            $stats['total_visits'] = $stmt->fetch()['total'];
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM audit_logs");
            $stats['total_logs'] = $stmt->fetch()['total'];
            
        } catch (PDOException $e) {
            error_log("Stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
}
