<?php
/**
 * Controlador de Reportes
 */

class ReportsController extends Controller {
    
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador']);
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de reportes
     */
    public function index() {
        $data = [
            'title' => 'Reportes del Sistema'
        ];
        
        $this->view('reports/index', $data);
    }
    
    /**
     * Reporte financiero
     */
    public function financial() {
        // Obtener resumen financiero
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending,
                SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as total_overdue,
                COUNT(*) as total_records
            FROM maintenance_fees
            WHERE period >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 12 MONTH), '%Y-%m')
        ");
        $summary = $stmt->fetch();
        
        // Obtener detalles mensuales
        $stmt = $this->db->query("
            SELECT 
                period,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as overdue,
                COUNT(*) as total
            FROM maintenance_fees
            WHERE period >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 12 MONTH), '%Y-%m')
            GROUP BY period
            ORDER BY period DESC
        ");
        $monthly_data = $stmt->fetchAll();
        
        $data = [
            'title' => 'Reporte Financiero',
            'summary' => $summary,
            'monthly_data' => $monthly_data
        ];
        
        $this->view('reports/financial', $data);
    }
    
    /**
     * Reporte de accesos
     */
    public function access() {
        $date_from = $this->get('date_from', date('Y-m-d', strtotime('-30 days')));
        $date_to = $this->get('date_to', date('Y-m-d'));
        
        $stmt = $this->db->prepare("
            SELECT 
                DATE(al.entry_time) as date,
                COUNT(*) as total_entries,
                COUNT(DISTINCT al.resident_id) as unique_residents,
                COUNT(DISTINCT al.visitor_id) as unique_visitors
            FROM access_logs al
            WHERE DATE(al.entry_time) BETWEEN ? AND ?
            GROUP BY DATE(al.entry_time)
            ORDER BY DATE(al.entry_time) DESC
        ");
        $stmt->execute([$date_from, $date_to]);
        $access_data = $stmt->fetchAll();
        
        // Resumen
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_entries,
                COUNT(DISTINCT resident_id) as total_residents,
                COUNT(DISTINCT visitor_id) as total_visitors
            FROM access_logs
            WHERE DATE(entry_time) BETWEEN ? AND ?
        ");
        $stmt->execute([$date_from, $date_to]);
        $summary = $stmt->fetch();
        
        $data = [
            'title' => 'Reporte de Accesos',
            'access_data' => $access_data,
            'summary' => $summary,
            'date_from' => $date_from,
            'date_to' => $date_to
        ];
        
        $this->view('reports/access', $data);
    }
    
    /**
     * Reporte de mantenimiento
     */
    public function maintenance() {
        $status_filter = $this->get('status');
        
        $where = [];
        $params = [];
        
        if ($status_filter) {
            $where[] = "status = ?";
            $params[] = $status_filter;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("
            SELECT 
                mr.*,
                p.property_number,
                u.first_name,
                u.last_name
            FROM maintenance_reports mr
            JOIN properties p ON mr.property_id = p.id
            LEFT JOIN users u ON mr.reported_by = u.id
            $whereClause
            ORDER BY mr.created_at DESC
        ");
        $stmt->execute($params);
        $reports = $stmt->fetchAll();
        
        // Estadísticas
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM maintenance_reports
        ");
        $stats = $stmt->fetch();
        
        $data = [
            'title' => 'Reporte de Mantenimiento',
            'reports' => $reports,
            'stats' => $stats,
            'status_filter' => $status_filter
        ];
        
        $this->view('reports/maintenance', $data);
    }
    
    /**
     * Reporte de residentes
     */
    public function residents() {
        // Obtener todos los residentes con sus propiedades
        $stmt = $this->db->query("
            SELECT 
                r.*,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                p.property_number,
                p.section,
                COUNT(DISTINCT v.id) as vehicle_count
            FROM residents r
            JOIN users u ON r.user_id = u.id
            JOIN properties p ON r.property_id = p.id
            LEFT JOIN vehicles v ON r.id = v.resident_id AND v.status = 'active'
            GROUP BY r.id
            ORDER BY p.property_number
        ");
        $residents = $stmt->fetchAll();
        
        // Estadísticas
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN relationship = 'propietario' THEN 1 ELSE 0 END) as propietarios,
                SUM(CASE WHEN relationship = 'inquilino' THEN 1 ELSE 0 END) as inquilinos,
                SUM(CASE WHEN relationship = 'familiar' THEN 1 ELSE 0 END) as familiares
            FROM residents
            WHERE status = 'active'
        ");
        $stats = $stmt->fetch();
        
        $data = [
            'title' => 'Reporte de Residentes',
            'residents' => $residents,
            'stats' => $stats
        ];
        
        $this->view('reports/residents', $data);
    }
    
    /**
     * Reporte de membresías/cuotas
     */
    public function memberships() {
        $period = $this->get('period', date('Y-m'));
        
        // Obtener cuotas del período
        $stmt = $this->db->prepare("
            SELECT 
                mf.*,
                p.property_number,
                p.section,
                r.id as resident_id,
                u.first_name,
                u.last_name
            FROM maintenance_fees mf
            JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON p.id = r.property_id AND r.is_primary = 1
            LEFT JOIN users u ON r.user_id = u.id
            WHERE mf.period = ?
            ORDER BY p.property_number
        ");
        $stmt->execute([$period]);
        $fees = $stmt->fetchAll();
        
        // Estadísticas del período
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(amount) as total_amount,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as overdue_amount,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_count
            FROM maintenance_fees
            WHERE period = ?
        ");
        $stmt->execute([$period]);
        $stats = $stmt->fetch();
        
        $data = [
            'title' => 'Reporte de Membresías',
            'fees' => $fees,
            'stats' => $stats,
            'period' => $period
        ];
        
        $this->view('reports/memberships', $data);
    }
}
