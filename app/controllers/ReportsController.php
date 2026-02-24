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
            'title' => 'Reportes'
        ];
        
        $this->view('reports/index', $data);
    }
    
    /**
     * Reporte financiero
     */
    public function financial() {
        $dateFrom = $this->get('date_from', date('Y-m-d', strtotime('-12 months')));
        $dateTo = $this->get('date_to', date('Y-m-d'));
        
        // Obtener datos financieros
        $financialModel = $this->model('Financial');
        $stats = $financialModel->getStats($dateFrom, $dateTo);
        $movements = $financialModel->getMovements([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        
        $data = [
            'title' => 'Reporte Financiero',
            'stats' => $stats,
            'movements' => $movements,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $this->view('reports/financial', $data);
    }
    
    /**
     * Reporte de accesos
     */
    public function access() {
        $dateFrom = $this->get('date_from', date('Y-m-d'));
        $dateTo = $this->get('date_to', date('Y-m-d'));
        
        // Obtener estadísticas de accesos
        $stmt = $this->db->prepare("
            SELECT 
                DATE(timestamp) as date,
                log_type,
                access_type,
                COUNT(*) as total
            FROM access_logs
            WHERE DATE(timestamp) BETWEEN ? AND ?
            GROUP BY DATE(timestamp), log_type, access_type
            ORDER BY date DESC
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $accessStats = $stmt->fetchAll();
        
        // Total de accesos por tipo
        $stmt = $this->db->prepare("
            SELECT 
                log_type,
                COUNT(*) as total
            FROM access_logs
            WHERE DATE(timestamp) BETWEEN ? AND ?
            GROUP BY log_type
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $accessByType = $stmt->fetchAll();
        
        $data = [
            'title' => 'Reporte de Accesos',
            'accessStats' => $accessStats,
            'accessByType' => $accessByType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $this->view('reports/access', $data);
    }
    
    /**
     * Reporte de mantenimiento
     */
    public function maintenance() {
        $dateFrom = $this->get('date_from', date('Y-m-01'));
        $dateTo = $this->get('date_to', date('Y-m-d'));
        
        // Estadísticas de reportes
        $stmt = $this->db->prepare("
            SELECT 
                status,
                priority,
                category,
                COUNT(*) as total
            FROM maintenance_reports
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY status, priority, category
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $maintenanceStats = $stmt->fetchAll();
        
        // Tiempo promedio de resolución
        $stmt = $this->db->prepare("
            SELECT 
                AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours
            FROM maintenance_reports
            WHERE status = 'completado'
            AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        $avgResolutionTime = $stmt->fetch()['avg_hours'] ?? 0;
        
        $data = [
            'title' => 'Reporte de Mantenimiento',
            'maintenanceStats' => $maintenanceStats,
            'avgResolutionTime' => round($avgResolutionTime, 2),
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $this->view('reports/maintenance', $data);
    }
    
    /**
     * Reporte de residentes
     */
    public function residents() {
        // Estadísticas generales
        $stmt = $this->db->query("
            SELECT 
                COUNT(DISTINCT r.id) as total_residents,
                COUNT(DISTINCT r.property_id) as occupied_properties,
                COUNT(DISTINCT CASE WHEN r.relationship = 'propietario' THEN r.id END) as owners,
                COUNT(DISTINCT CASE WHEN r.relationship = 'inquilino' THEN r.id END) as tenants
            FROM residents r
            WHERE r.status = 'active'
        ");
        $stats = $stmt->fetch();
        
        // Residentes por propiedad
        $stmt = $this->db->query("
            SELECT 
                p.property_number,
                COUNT(r.id) as resident_count,
                GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') as residents
            FROM properties p
            LEFT JOIN residents r ON p.id = r.property_id AND r.status = 'active'
            LEFT JOIN users u ON r.user_id = u.id
            GROUP BY p.id
            ORDER BY p.property_number
        ");
        $propertiesData = $stmt->fetchAll();
        
        $data = [
            'title' => 'Reporte de Residentes',
            'stats' => $stats,
            'propertiesData' => $propertiesData
        ];
        
        $this->view('reports/residents', $data);
    }
    
    /**
     * Reporte de Imprevistos
     */
    public function unforeseen() {
        $page = max(1, intval($this->get('page', 1)));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        $search = $this->get('search', '');
        $date_from = $this->get('date_from', date('Y-m-01'));
        $date_to = $this->get('date_to', date('Y-m-d'));
        
        $where = ["fm.is_unforeseen = 1", "fm.transaction_type = 'egreso'"];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(fm.description LIKE ? OR fmt.name LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        
        if (!empty($date_from)) {
            $where[] = "fm.transaction_date >= ?";
            $params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $where[] = "fm.transaction_date <= ?";
            $params[] = $date_to;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $where);
        
        $countParams = $params;
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM financial_movements fm
            INNER JOIN financial_movement_types fmt ON fm.movement_type_id = fmt.id
            $whereClause
        ");
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        $total_pages = ceil($total / $per_page);
        
        $params[] = $per_page;
        $params[] = $offset;
        
        $stmt = $this->db->prepare("
            SELECT fm.*, fmt.name as movement_type_name,
                   p.property_number,
                   CONCAT(u.first_name, ' ', u.last_name) as created_by_name
            FROM financial_movements fm
            INNER JOIN financial_movement_types fmt ON fm.movement_type_id = fmt.id
            LEFT JOIN properties p ON fm.property_id = p.id
            LEFT JOIN users u ON fm.created_by = u.id
            $whereClause
            ORDER BY fm.transaction_date DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $records = $stmt->fetchAll();
        
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total_amount
            FROM financial_movements
            WHERE is_unforeseen = 1 AND transaction_type = 'egreso'
            AND transaction_date BETWEEN ? AND ?
        ");
        $stmt->execute([$date_from, $date_to]);
        $totalAmount = $stmt->fetch()['total_amount'];
        
        $data = [
            'title' => 'Reporte de Imprevistos',
            'records' => $records,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'search' => $search,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'totalAmount' => $totalAmount
        ];
        
        $this->view('reports/unforeseen', $data);
    }
    
    /**
     * Reporte de membresías
     */
    public function memberships() {
        $membershipModel = $this->model('Membership');
        $stats = $membershipModel->getStats();
        $memberships = $membershipModel->getAll();
        
        $data = [
            'title' => 'Reporte de Membresías',
            'stats' => $stats,
            'memberships' => $memberships
        ];
        
        $this->view('reports/memberships', $data);
    }
}
