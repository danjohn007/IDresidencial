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
        
        // Obtener logs
        $stmt = $this->db->prepare("
            SELECT al.*, u.username, u.first_name, u.last_name, u.role
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            $whereClause
            ORDER BY al.created_at DESC
            LIMIT 100
        ");
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
            'stats' => $stats
        ];
        
        $this->view('audit/index', $data);
    }
    
    /**
     * Ver detalles de un log
     */
    public function view($id) {
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
        
        $this->view('audit/view', $data);
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
            // Silent fail - no queremos que errores de logging rompan la aplicación
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
}
