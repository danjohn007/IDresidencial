<?php
/**
 * Controlador de Control de Accesos
 */

class AccessController extends Controller {
    
    private $visitModel;
    private $accessLogModel;
    private $residentModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->visitModel = $this->model('Visit');
        $this->accessLogModel = $this->model('AccessLog');
        $this->residentModel = $this->model('Resident');
    }
    
    /**
     * Vista principal de control de accesos
     */
    public function index() {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        // Obtener visitas de hoy
        $todayVisits = $this->visitModel->getTodayVisits();
        
        // Obtener estadísticas
        $stats = [
            'total_today' => count($todayVisits),
            'active' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'active')),
            'completed' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'completed')),
            'pending' => count(array_filter($todayVisits, fn($v) => $v['status'] === 'pending'))
        ];
        
        $data = [
            'title' => 'Control de Accesos',
            'visits' => $todayVisits,
            'stats' => $stats
        ];
        
        $this->view('access/index', $data);
    }
    
    /**
     * Crear nueva visita con QR
     */
    public function create() {
        $data = [
            'title' => 'Generar Pase de Visita',
            'error' => '',
            'success' => ''
        ];
        
        // Obtener información del residente
        $resident = null;
        if ($_SESSION['role'] === 'residente') {
            $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $visitData = [
                'resident_id' => $this->post('resident_id'),
                'visitor_name' => $this->post('visitor_name'),
                'visitor_id' => $this->post('visitor_id'),
                'visitor_phone' => $this->post('visitor_phone'),
                'vehicle_plate' => $this->post('vehicle_plate'),
                'visit_type' => $this->post('visit_type', 'personal'),
                'valid_from' => $this->post('valid_from'),
                'valid_until' => $this->post('valid_until'),
                'notes' => $this->post('notes'),
                'status' => 'pending'
            ];
            
            // Validaciones
            if (empty($visitData['visitor_name']) || empty($visitData['valid_from']) || empty($visitData['valid_until'])) {
                $data['error'] = 'Por favor, completa todos los campos requeridos';
            } else {
                // Generar código QR único
                $visitData['qr_code'] = $this->visitModel->generateUniqueQR();
                
                if ($this->visitModel->create($visitData)) {
                    // Generar imagen QR
                    $this->generateQRImage($visitData['qr_code']);
                    
                    $_SESSION['success_message'] = 'Pase de visita generado exitosamente';
                    $this->redirect('access/view/' . $visitData['qr_code']);
                } else {
                    $data['error'] = 'Error al generar el pase de visita';
                }
            }
        }
        
        $data['resident'] = $resident;
        $this->view('access/create', $data);
    }
    
    /**
     * Ver detalles de una visita
     */
    public function view($qrCode = null) {
        if (!$qrCode) {
            $this->redirect('access');
        }
        
        $visit = $this->visitModel->findByQR($qrCode);
        
        if (!$visit) {
            $_SESSION['error_message'] = 'Visita no encontrada';
            $this->redirect('access');
        }
        
        $data = [
            'title' => 'Detalles de Visita',
            'visit' => $visit,
            'qr_image' => $this->getQRImagePath($qrCode)
        ];
        
        $this->view('access/view', $data);
    }
    
    /**
     * Validar QR y registrar entrada
     */
    public function validate() {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        $data = [
            'title' => 'Validar Acceso',
            'error' => '',
            'visit' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $qrCode = $this->post('qr_code');
            
            if (empty($qrCode)) {
                $data['error'] = 'Por favor, ingresa el código QR';
            } else {
                $visit = $this->visitModel->validateVisit($qrCode);
                
                if ($visit) {
                    $data['visit'] = $this->visitModel->findByQR($qrCode);
                    $data['valid'] = true;
                } else {
                    $data['error'] = 'Código QR inválido o expirado';
                    $data['valid'] = false;
                }
            }
        }
        
        $this->view('access/validate', $data);
    }
    
    /**
     * Registrar entrada
     */
    public function registerEntry($visitId) {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        if ($this->visitModel->registerEntry($visitId, $_SESSION['user_id'])) {
            // Registrar en bitácora
            $visit = $this->visitModel->findById($visitId);
            $this->accessLogModel->create([
                'log_type' => 'visit',
                'reference_id' => $visitId,
                'access_type' => 'entry',
                'access_method' => 'qr',
                'property_id' => null,
                'name' => $visit['visitor_name'],
                'vehicle_plate' => $visit['vehicle_plate'],
                'guard_id' => $_SESSION['user_id']
            ]);
            
            $_SESSION['success_message'] = 'Entrada registrada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al registrar entrada';
        }
        
        $this->redirect('access');
    }
    
    /**
     * Registrar salida
     */
    public function registerExit($visitId) {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        if ($this->visitModel->registerExit($visitId, $_SESSION['user_id'])) {
            // Registrar en bitácora
            $visit = $this->visitModel->findById($visitId);
            $this->accessLogModel->create([
                'log_type' => 'visit',
                'reference_id' => $visitId,
                'access_type' => 'exit',
                'access_method' => 'qr',
                'property_id' => null,
                'name' => $visit['visitor_name'],
                'vehicle_plate' => $visit['vehicle_plate'],
                'guard_id' => $_SESSION['user_id']
            ]);
            
            $_SESSION['success_message'] = 'Salida registrada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al registrar salida';
        }
        
        $this->redirect('access');
    }
    
    /**
     * Bitácora de accesos
     */
    public function logs() {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        
        $filters = [
            'date' => $this->get('date', date('Y-m-d')),
            'log_type' => $this->get('log_type'),
            'access_type' => $this->get('access_type'),
            'search' => $this->get('search')
        ];
        
        $logs = $this->accessLogModel->getAll($filters);
        
        $data = [
            'title' => 'Bitácora de Accesos',
            'logs' => $logs,
            'filters' => $filters
        ];
        
        $this->view('access/logs', $data);
    }
    
    /**
     * Generar imagen QR
     */
    private function generateQRImage($qrCode) {
        // Por simplicidad, usaremos un servicio externo de API para generar QR
        // En producción, se recomienda usar una biblioteca PHP como endroid/qr-code
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrCode);
        
        $qrImagePath = PUBLIC_PATH . '/uploads/qr/' . $qrCode . '.png';
        
        // Crear directorio si no existe
        if (!is_dir(PUBLIC_PATH . '/uploads/qr')) {
            mkdir(PUBLIC_PATH . '/uploads/qr', 0755, true);
        }
        
        // Descargar y guardar la imagen
        $imageData = @file_get_contents($qrUrl);
        if ($imageData) {
            file_put_contents($qrImagePath, $imageData);
        }
        
        return $qrImagePath;
    }
    
    /**
     * Obtener ruta de imagen QR
     */
    private function getQRImagePath($qrCode) {
        $path = '/uploads/qr/' . $qrCode . '.png';
        $fullPath = PUBLIC_PATH . $path;
        
        if (file_exists($fullPath)) {
            return PUBLIC_URL . $path;
        }
        
        // Si no existe, generarla
        $this->generateQRImage($qrCode);
        return PUBLIC_URL . $path;
    }
    
    /**
     * Mis visitas (para residentes)
     */
    public function myVisits() {
        $this->requireRole('residente');
        
        $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
        if (!$resident) {
            $_SESSION['error_message'] = 'No se encontró información de residente';
            $this->redirect('dashboard');
        }
        
        $visits = $this->visitModel->getByResident($resident['id']);
        
        $data = [
            'title' => 'Mis Visitas',
            'visits' => $visits,
            'resident' => $resident
        ];
        
        $this->view('access/my_visits', $data);
    }
}
