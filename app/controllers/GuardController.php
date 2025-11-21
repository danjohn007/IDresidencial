<?php
/**
 * Controlador de Consola de Guardia
 */

class GuardController extends Controller {
    
    private $visitModel;
    private $accessLogModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['guardia', 'superadmin', 'administrador']);
        $this->visitModel = $this->model('Visit');
        $this->accessLogModel = $this->model('AccessLog');
    }
    
    /**
     * Vista principal de la consola de guardia
     */
    public function index() {
        // Obtener visitas programadas para hoy
        $todayVisits = $this->visitModel->getTodayVisits();
        
        // Obtener accesos recientes
        $recentAccess = $this->accessLogModel->getToday();
        
        // Estadísticas del día
        $stats = [
            'total_visits' => count($todayVisits),
            'active_visits' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'active')),
            'completed_visits' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'completed')),
            'pending_visits' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'pending')),
            'total_entries' => count(array_filter($recentAccess, fn($a) => $a['access_type'] === 'entry')),
            'total_exits' => count(array_filter($recentAccess, fn($a) => $a['access_type'] === 'exit'))
        ];
        
        $data = [
            'title' => 'Consola de Guardia',
            'visits' => $todayVisits,
            'recentAccess' => array_slice($recentAccess, 0, 20),
            'stats' => $stats
        ];
        
        $this->view('guard/index', $data);
    }
    
    /**
     * Registrar acceso manual
     */
    public function manualAccess() {
        $data = [
            'title' => 'Registro Manual de Acceso',
            'error' => '',
            'success' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accessData = [
                'log_type' => $this->post('log_type'),
                'access_type' => $this->post('access_type'),
                'access_method' => 'manual',
                'name' => $this->post('name'),
                'vehicle_plate' => $this->post('vehicle_plate'),
                'property_id' => $this->post('property_id') ?: null,
                'guard_id' => $_SESSION['user_id'],
                'notes' => $this->post('notes')
            ];
            
            if (empty($accessData['name'])) {
                $data['error'] = 'El nombre es requerido';
            } else {
                if ($this->accessLogModel->create($accessData)) {
                    $_SESSION['success_message'] = 'Acceso registrado exitosamente';
                    $this->redirect('guard');
                } else {
                    $data['error'] = 'Error al registrar el acceso';
                }
            }
        }
        
        $this->view('guard/manual_access', $data);
    }
    
    /**
     * Escanear QR rápido
     */
    public function quickScan() {
        $data = [
            'title' => 'Escaneo Rápido QR',
            'result' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $qrCode = $this->post('qr_code');
            $visit = $this->visitModel->validateVisit($qrCode);
            
            if ($visit) {
                $visitDetails = $this->visitModel->findByQR($qrCode);
                
                // Registrar entrada automáticamente
                if ($visitDetails['status'] === 'pending') {
                    $this->visitModel->registerEntry($visitDetails['id'], $_SESSION['user_id']);
                    
                    $this->accessLogModel->create([
                        'log_type' => 'visit',
                        'reference_id' => $visitDetails['id'],
                        'access_type' => 'entry',
                        'access_method' => 'qr',
                        'name' => $visitDetails['visitor_name'],
                        'vehicle_plate' => $visitDetails['vehicle_plate'],
                        'guard_id' => $_SESSION['user_id']
                    ]);
                    
                    $data['result'] = [
                        'success' => true,
                        'message' => 'Entrada registrada exitosamente',
                        'visit' => $visitDetails
                    ];
                } else {
                    $data['result'] = [
                        'success' => true,
                        'message' => 'Visita ya registrada',
                        'visit' => $visitDetails
                    ];
                }
            } else {
                $data['result'] = [
                    'success' => false,
                    'message' => 'Código QR inválido o expirado'
                ];
            }
        }
        
        $this->view('guard/quick_scan', $data);
    }
    
    /**
     * Ver alertas activas
     */
    public function alerts() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->query("
            SELECT * FROM security_alerts 
            WHERE status IN ('open', 'in_progress') 
            ORDER BY severity DESC, created_at DESC
        ");
        $alerts = $stmt->fetchAll();
        
        $data = [
            'title' => 'Alertas Activas',
            'alerts' => $alerts
        ];
        
        $this->view('guard/alerts', $data);
    }
}
