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
        
        // Obtener rango de fechas (por defecto: mes actual)
        $dateFrom = $this->get('date_from', date('Y-m-01'));
        $dateTo = $this->get('date_to', date('Y-m-t'));
        
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
        
        // Charts data for SuperAdmin
        $chartsData = [];
        if ($role === 'superadmin') {
            // Chart 1: Ingresos vs Egresos por mes
            $stmt = $db->prepare("
                SELECT 
                    DATE_FORMAT(transaction_date, '%Y-%m') as month,
                    SUM(CASE WHEN transaction_type = 'ingreso' THEN amount ELSE 0 END) as ingresos,
                    SUM(CASE WHEN transaction_type = 'egreso' THEN amount ELSE 0 END) as egresos
                FROM financial_movements
                WHERE transaction_date BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
                ORDER BY month
            ");
            $stmt->execute([$dateFrom, $dateTo]);
            $chartsData['financialMovements'] = $stmt->fetchAll();
            
            // Chart 2: Visitas por día
            $stmt = $db->prepare("
                SELECT DATE(created_at) as date, COUNT(*) as total
                FROM visits
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$dateFrom, $dateTo]);
            $chartsData['dailyVisits'] = $stmt->fetchAll();
            
            // Chart 3: Mantenimientos por categoría
            $stmt = $db->prepare("
                SELECT category, COUNT(*) as total
                FROM maintenance_reports
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY category
            ");
            $stmt->execute([$dateFrom, $dateTo]);
            $chartsData['maintenanceByCategory'] = $stmt->fetchAll();
            
            // Chart 4: Pagos por estado
            $stmt = $db->prepare("
                SELECT status, COUNT(*) as total, SUM(amount) as total_amount
                FROM maintenance_fees
                WHERE due_date BETWEEN ? AND ?
                GROUP BY status
            ");
            $stmt->execute([$dateFrom, $dateTo]);
            $chartsData['paymentsByStatus'] = $stmt->fetchAll();
            
            // Report 1: Movimientos financieros recientes
            $stmt = $db->prepare("
                SELECT fm.*, fmt.name as movement_type_name, p.property_number,
                       CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM financial_movements fm
                LEFT JOIN financial_movement_types fmt ON fm.movement_type_id = fmt.id
                LEFT JOIN properties p ON fm.property_id = p.id
                LEFT JOIN users u ON fm.created_by = u.id
                WHERE fm.transaction_date BETWEEN ? AND ?
                ORDER BY fm.transaction_date DESC, fm.created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$dateFrom, $dateTo]);
            $chartsData['recentMovements'] = $stmt->fetchAll();
            
            // Report 2: Resumen de pagos pendientes
            $stmt = $db->prepare("
                SELECT mf.*, p.property_number, CONCAT(u.first_name, ' ', u.last_name) as resident_name
                FROM maintenance_fees mf
                LEFT JOIN properties p ON mf.property_id = p.id
                LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1
                LEFT JOIN users u ON r.user_id = u.id
                WHERE mf.status IN ('pending', 'overdue')
                  AND mf.due_date BETWEEN ? AND ?
                ORDER BY mf.due_date ASC
                LIMIT 10
            ");
            $stmt->execute([$dateFrom, $dateTo]);
            $chartsData['pendingPayments'] = $stmt->fetchAll();
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
            'chartsData' => $chartsData,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'recentActivity' => $recentActivity,
            'upcomingReservations' => $upcomingReservations,
            'pendingMaintenance' => $pendingMaintenance,
            'user' => $this->getCurrentUser()
        ];
        
        $this->view('dashboard/index', $data);
    }
}
