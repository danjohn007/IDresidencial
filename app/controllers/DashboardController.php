<?php
/**
 * Controlador del Dashboard
 */

class DashboardController extends Controller {
    
    public function __construct() {
        $this->requireAuth();
    }
    
    /**
     * Dashboard principal
     */
    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Estadísticas generales
        $stats = [
            'total_residents' => 0,
            'total_visits_today' => 0,
            'total_maintenance' => 0,
            'total_reservations' => 0,
            'pending_payments' => 0,
            'active_alerts' => 0
        ];
        
        // Obtener estadísticas según el rol
        $role = $_SESSION['role'];
        
        // Total de residentes
        if (in_array($role, ['superadmin', 'administrador'])) {
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'residente'");
            $stats['total_residents'] = $stmt->fetch()['total'];
            
            // Pagos pendientes
            $stmt = $db->query("SELECT COUNT(*) as total FROM maintenance_fees WHERE status = 'pending'");
            $stats['pending_payments'] = $stmt->fetch()['total'];
        }
        
        // Visitas del día
        if (in_array($role, ['superadmin', 'administrador', 'guardia'])) {
            $stmt = $db->query("SELECT COUNT(*) as total FROM visits WHERE DATE(created_at) = CURDATE()");
            $stats['total_visits_today'] = $stmt->fetch()['total'];
        }
        
        // Mantenimientos activos
        $stmt = $db->query("SELECT COUNT(*) as total FROM maintenance_reports WHERE status IN ('pendiente', 'en_proceso')");
        $stats['total_maintenance'] = $stmt->fetch()['total'];
        
        // Reservaciones activas
        $stmt = $db->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'confirmed' AND reservation_date >= CURDATE()");
        $stats['total_reservations'] = $stmt->fetch()['total'];
        
        // Alertas activas
        if (in_array($role, ['superadmin', 'administrador', 'guardia'])) {
            $stmt = $db->query("SELECT COUNT(*) as total FROM security_alerts WHERE status IN ('open', 'in_progress')");
            $stats['active_alerts'] = $stmt->fetch()['total'];
        }
        
        // Actividad reciente (últimos accesos)
        $recentActivity = [];
        if (in_array($role, ['superadmin', 'administrador', 'guardia'])) {
            $stmt = $db->query("
                SELECT log_type, name, access_type, timestamp 
                FROM access_logs 
                ORDER BY timestamp DESC 
                LIMIT 10
            ");
            $recentActivity = $stmt->fetchAll();
        }
        
        // Próximas reservaciones
        $upcomingReservations = [];
        $stmt = $db->query("
            SELECT r.*, a.name as amenity_name, u.first_name, u.last_name
            FROM reservations r
            JOIN amenities a ON r.amenity_id = a.id
            JOIN residents res ON r.resident_id = res.id
            JOIN users u ON res.user_id = u.id
            WHERE r.status = 'confirmed' AND r.reservation_date >= CURDATE()
            ORDER BY r.reservation_date, r.start_time
            LIMIT 5
        ");
        $upcomingReservations = $stmt->fetchAll();
        
        // Mantenimientos pendientes
        $pendingMaintenance = [];
        $stmt = $db->query("
            SELECT m.*, u.first_name, u.last_name
            FROM maintenance_reports m
            JOIN residents res ON m.resident_id = res.id
            JOIN users u ON res.user_id = u.id
            WHERE m.status IN ('pendiente', 'en_proceso')
            ORDER BY 
                CASE m.priority 
                    WHEN 'urgente' THEN 1
                    WHEN 'alta' THEN 2
                    WHEN 'media' THEN 3
                    WHEN 'baja' THEN 4
                END,
                m.created_at DESC
            LIMIT 5
        ");
        $pendingMaintenance = $stmt->fetchAll();
        
        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'upcomingReservations' => $upcomingReservations,
            'pendingMaintenance' => $pendingMaintenance,
            'user' => $this->getCurrentUser()
        ];
        
        $this->view('dashboard/index', $data);
    }
}
