<?php
/**
 * Controlador de Mantenimiento
 */

class MaintenanceController extends Controller {
    
    private $maintenanceModel;
    private $residentModel;
    private $catalogModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->maintenanceModel = $this->model('MaintenanceReport');
        $this->residentModel    = $this->model('Resident');
        $this->catalogModel     = $this->model('MaintenanceCatalog');
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
     * Vista de Áreas Comunes
     */
    public function commonAreas() {
        $filters = [
            'status' => $this->get('status'),
            'category' => $this->get('category'),
            'priority' => $this->get('priority')
        ];

        $db = Database::getInstance()->getConnection();

        $where = ['mr.property_id IS NULL'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'mr.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'mr.category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['priority'])) {
            $where[] = 'mr.priority = ?';
            $params[] = $filters['priority'];
        }
        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $stmt = $db->prepare("
            SELECT mr.*,
                   COALESCE(u.first_name, '') as first_name,
                   COALESCE(u.last_name, '') as last_name,
                   '' as property_number
            FROM maintenance_reports mr
            LEFT JOIN users u ON mr.resident_id IS NOT NULL AND u.id = (
                SELECT r2.user_id FROM residents r2 WHERE r2.id = mr.resident_id LIMIT 1
            )
            $whereClause
            ORDER BY mr.created_at DESC
        ");
        $stmt->execute($params);
        $reports = $stmt->fetchAll();

        $data = [
            'title' => 'Áreas Comunes - Mantenimiento',
            'reports' => $reports,
            'filters' => $filters
        ];

        $this->view('maintenance/common_areas', $data);
    }

    /**
     * Crear reporte de área común
     */
    public function createCommonArea() {
        $this->requireRole(['superadmin', 'administrador', 'guardia']);

        $data = [
            'title' => 'Nuevo Reporte - Área Común',
            'error' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

            if ($this->maintenanceModel->create($reportData)) {
                $_SESSION['success_message'] = 'Reporte de área común creado exitosamente';
                $this->redirect('maintenance/commonAreas');
            } else {
                $data['error'] = 'Error al crear el reporte';
            }
        }

        $this->view('maintenance/create_common_area', $data);
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

        $redirectTo = $this->post('_redirect', 'maintenance');
        $allowed = ['maintenance', 'maintenance/commonAreas'];
        $this->redirect(in_array($redirectTo, $allowed) ? $redirectTo : 'maintenance');
    }

    // ------------------------------------------------------------------
    // Catálogo de Incidencias Fijas
    // ------------------------------------------------------------------

    /**
     * Lista del catálogo de incidencias recurrentes
     */
    public function catalog() {
        $this->requireRole(['superadmin', 'administrador']);

        $items = $this->catalogModel->getAll();

        $data = [
            'title' => 'Catálogo de Incidencias Fijas',
            'items' => $items,
        ];

        $this->view('maintenance/catalog', $data);
    }

    /**
     * Crear / guardar nueva entrada del catálogo
     */
    public function catalogCreate() {
        $this->requireRole(['superadmin', 'administrador']);

        $data = [
            'title' => 'Nueva Incidencia Fija',
            'item'  => null,
            'error' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fields = [
                'title'          => trim($this->post('title')),
                'description'    => trim($this->post('description')),
                'category'       => $this->post('category'),
                'location'       => trim($this->post('location')),
                'priority'       => $this->post('priority', 'media'),
                'interval_value' => (int) $this->post('interval_value'),
                'interval_unit'  => $this->post('interval_unit', 'meses'),
            ];

            if (empty($fields['title']) || empty($fields['category']) || $fields['interval_value'] < 1) {
                $data['error'] = 'Por favor complete todos los campos obligatorios correctamente.';
            } else {
                if ($this->catalogModel->create($fields)) {
                    $_SESSION['success_message'] = 'Incidencia fija creada exitosamente.';
                    $this->redirect('maintenance/catalog');
                } else {
                    $data['error'] = 'Error al guardar la incidencia fija.';
                }
            }
        }

        $this->view('maintenance/catalog_form', $data);
    }

    /**
     * Editar entrada del catálogo
     */
    public function catalogEdit($id) {
        $this->requireRole(['superadmin', 'administrador']);

        $item = $this->catalogModel->findById($id);
        if (!$item) {
            $_SESSION['error_message'] = 'Incidencia fija no encontrada.';
            $this->redirect('maintenance/catalog');
        }

        $data = [
            'title' => 'Editar Incidencia Fija',
            'item'  => $item,
            'error' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fields = [
                'title'          => trim($this->post('title')),
                'description'    => trim($this->post('description')),
                'category'       => $this->post('category'),
                'location'       => trim($this->post('location')),
                'priority'       => $this->post('priority', 'media'),
                'interval_value' => (int) $this->post('interval_value'),
                'interval_unit'  => $this->post('interval_unit', 'meses'),
                'active'         => (int) $this->post('active', 1),
            ];

            if (empty($fields['title']) || empty($fields['category']) || $fields['interval_value'] < 1) {
                $data['error'] = 'Por favor complete todos los campos obligatorios correctamente.';
            } else {
                if ($this->catalogModel->update($id, $fields)) {
                    $_SESSION['success_message'] = 'Incidencia fija actualizada exitosamente.';
                    $this->redirect('maintenance/catalog');
                } else {
                    $data['error'] = 'Error al actualizar la incidencia fija.';
                }
            }
        }

        $this->view('maintenance/catalog_form', $data);
    }

    /**
     * Eliminar entrada del catálogo
     */
    public function catalogDelete($id) {
        $this->requireRole(['superadmin', 'administrador']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->catalogModel->delete($id)) {
                $_SESSION['success_message'] = 'Incidencia fija eliminada.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la incidencia fija.';
            }
        }

        $this->redirect('maintenance/catalog');
    }

    /**
     * Generar reportes pendientes del catálogo manualmente (también ejecutado por cron)
     */
    public function catalogGenerate() {
        $this->requireRole(['superadmin', 'administrador']);

        $due     = $this->catalogModel->getDueItems();
        $created = 0;

        foreach ($due as $item) {
            if ($this->catalogModel->generateReport($item) !== false) {
                $created++;
            }
        }

        if ($created > 0) {
            $_SESSION['success_message'] = "Se generaron {$created} reporte(s) automático(s) del catálogo.";
        } else {
            $_SESSION['success_message'] = 'No hay incidencias fijas con reportes pendientes de generar.';
        }

        $this->redirect('maintenance/catalog');
    }
}
