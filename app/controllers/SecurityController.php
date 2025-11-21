<?php
/**
 * Controlador de Seguridad
 */

class SecurityController extends Controller {
    
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de seguridad
     */
    public function index() {
        // Obtener alertas activas
        $stmt = $this->db->query("
            SELECT * FROM security_alerts 
            WHERE status IN ('open', 'in_progress') 
            ORDER BY severity DESC, created_at DESC
        ");
        $activeAlerts = $stmt->fetchAll();
        
        // Obtener rondines activos
        $stmt = $this->db->query("
            SELECT sp.*, u.first_name, u.last_name 
            FROM security_patrols sp
            JOIN users u ON sp.guard_id = u.id
            WHERE sp.status = 'in_progress'
            ORDER BY sp.patrol_start DESC
        ");
        $activePatrols = $stmt->fetchAll();
        
        // Estadísticas
        $stats = [
            'active_alerts' => count($activeAlerts),
            'active_patrols' => count($activePatrols),
            'alerts_today' => 0,
            'patrols_today' => 0
        ];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM security_alerts WHERE DATE(created_at) = CURDATE()");
        $stats['alerts_today'] = $stmt->fetch()['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM security_patrols WHERE DATE(patrol_start) = CURDATE()");
        $stats['patrols_today'] = $stmt->fetch()['total'];
        
        $data = [
            'title' => 'Seguridad',
            'alerts' => $activeAlerts,
            'patrols' => $activePatrols,
            'stats' => $stats
        ];
        
        $this->view('security/index', $data);
    }
    
    /**
     * Crear nueva alerta
     */
    public function createAlert() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sql = "INSERT INTO security_alerts (alert_type, severity, location, description, reported_by, status) 
                    VALUES (?, ?, ?, ?, ?, 'open')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->post('alert_type'),
                $this->post('severity', 'medium'),
                $this->post('location'),
                $this->post('description'),
                $_SESSION['user_id']
            ]);
            
            $_SESSION['success_message'] = 'Alerta creada exitosamente';
            $this->redirect('security');
        }
        
        $data = ['title' => 'Nueva Alerta'];
        $this->view('security/create_alert', $data);
    }
    
    /**
     * Iniciar rondín
     */
    public function startPatrol() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sql = "INSERT INTO security_patrols (guard_id, patrol_start, route, status) 
                    VALUES (?, NOW(), ?, 'in_progress')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $_SESSION['user_id'],
                $this->post('route')
            ]);
            
            $_SESSION['success_message'] = 'Rondín iniciado';
            $this->redirect('security');
        }
    }
    
    /**
     * Finalizar rondín
     */
    public function endPatrol($patrolId) {
        $sql = "UPDATE security_patrols 
                SET patrol_end = NOW(), incidents_found = ?, notes = ?, status = 'completed' 
                WHERE id = ? AND guard_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $this->post('incidents_found'),
            $this->post('notes'),
            $patrolId,
            $_SESSION['user_id']
        ]);
        
        $_SESSION['success_message'] = 'Rondín finalizado';
        $this->redirect('security');
    }
    
    /**
     * Historial de alertas
     */
    public function alertHistory() {
        $stmt = $this->db->query("
            SELECT a.*, 
                   u.first_name as reporter_first_name, u.last_name as reporter_last_name,
                   r.first_name as resolver_first_name, r.last_name as resolver_last_name
            FROM security_alerts a
            LEFT JOIN users u ON a.reported_by = u.id
            LEFT JOIN users r ON a.resolved_by = r.id
            ORDER BY a.created_at DESC
            LIMIT 50
        ");
        $alerts = $stmt->fetchAll();
        
        $data = [
            'title' => 'Historial de Alertas',
            'alerts' => $alerts
        ];
        
        $this->view('security/alert_history', $data);
    }
    
    /**
     * Resolver alerta
     */
    public function resolveAlert($alertId) {
        $sql = "UPDATE security_alerts 
                SET status = 'resolved', resolved_by = ?, resolved_at = NOW(), notes = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $_SESSION['user_id'],
            $this->post('resolution_notes'),
            $alertId
        ]);
        
        $_SESSION['success_message'] = 'Alerta resuelta';
        $this->redirect('security');
    }
}
