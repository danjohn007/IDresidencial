<?php
/**
 * Controlador de Mantenimiento
 */

class MaintenanceController extends Controller {
    
    private $maintenanceModel;
    private $residentModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->maintenanceModel = $this->model('MaintenanceReport');
        $this->residentModel = $this->model('Resident');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $filters = [
            'status' => $this->get('status'),
            'category' => $this->get('category'),
            'priority' => $this->get('priority')
        ];
        
        $reports = $this->maintenanceModel->getAll($filters);
        
        $data = [
            'title' => 'Mantenimiento',
            'reports' => $reports,
            'filters' => $filters
        ];
        
        $this->view('maintenance/index', $data);
    }
    
    /**
     * Crear reporte
     */
    public function create() {
        $data = [
            'title' => 'Reportar Incidencia',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
            
            if (!$resident) {
                $data['error'] = 'No se encontró información de residente';
            } else {
                $reportData = [
                    'resident_id' => $resident['id'],
                    'property_id' => $resident['property_id'],
                    'category' => $this->post('category'),
                    'title' => $this->post('title'),
                    'description' => $this->post('description'),
                    'priority' => $this->post('priority', 'media'),
                    'location' => $this->post('location'),
                    'status' => 'pendiente'
                ];
                
                if ($this->maintenanceModel->create($reportData)) {
                    $_SESSION['success_message'] = 'Reporte creado exitosamente';
                    $this->redirect('maintenance');
                } else {
                    $data['error'] = 'Error al crear el reporte';
                }
            }
        }
        
        $this->view('maintenance/create', $data);
    }
    
    /**
     * Ver detalles del reporte
     */
    public function view($id) {
        $report = $this->maintenanceModel->findById($id);
        
        if (!$report) {
            $_SESSION['error_message'] = 'Reporte no encontrado';
            $this->redirect('maintenance');
        }
        
        $data = [
            'title' => 'Detalles del Reporte',
            'report' => $report
        ];
        
        $this->view('maintenance/view', $data);
    }
}
