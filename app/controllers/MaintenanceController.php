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
            'error' => '',
            'resident' => null
        ];
        
        // Load resident information for view
        $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
        $data['resident'] = $resident;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Re-check resident on POST
            $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
            
            if (!$resident) {
                // If user is not a resident, still allow report with null resident_id and property_id
                // This allows administrators and guards to create reports for common areas
                $reportData = [
                    'resident_id' => null,
                    'property_id' => null,
                    'category' => $this->post('category'),
                    'title' => $this->post('title'),
                    'description' => $this->post('description'),
                    'priority' => $this->post('priority', 'media'),
                    'location' => $this->post('location'),
                    'status' => 'pendiente'
                ];
                
                // Allow admins and guards to create reports
                if (in_array($_SESSION['role'], ['superadmin', 'administrador', 'guardia'])) {
                    if ($this->maintenanceModel->create($reportData)) {
                        $_SESSION['success_message'] = 'Reporte creado exitosamente';
                        $this->redirect('maintenance');
                    } else {
                        $data['error'] = 'Error al crear el reporte';
                    }
                } else {
                    $data['error'] = 'No se encontró información de residente. Por favor contacte al administrador para vincular su cuenta a una propiedad.';
                }
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
    public function viewDetails($id) {
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
    
    /**
     * Cambiar estado del reporte
     */
    public function updateStatus($id) {
        $this->requireRole(['superadmin', 'administrador']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newStatus = $this->post('status');
            
            if (in_array($newStatus, ['pendiente', 'en_proceso', 'completado', 'cancelado'])) {
                if ($this->maintenanceModel->updateStatus($id, $newStatus)) {
                    $_SESSION['success_message'] = 'Estado actualizado exitosamente';
                } else {
                    $_SESSION['error_message'] = 'Error al actualizar el estado';
                }
            } else {
                $_SESSION['error_message'] = 'Estado inválido';
            }
        }
        
        $this->redirect('maintenance');
    }
}
