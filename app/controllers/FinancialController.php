<?php
/**
 * Controlador de Módulo Financiero
 */

require_once APP_PATH . '/controllers/AuditController.php';

class FinancialController extends Controller {
    
    private $financialModel;
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador']);
        $this->financialModel = $this->model('Financial');
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal del módulo financiero
     */
    public function index() {
        // Filtros
        $filters = [
            'transaction_type' => $this->get('transaction_type'),
            'movement_type_id' => $this->get('movement_type_id'),
            'date_from' => $this->get('date_from', date('Y-m-d', strtotime('-12 months'))),
            'date_to' => $this->get('date_to', date('Y-m-d')),
            'property_id' => $this->get('property_id')
        ];
        
        // Obtener movimientos
        $movements = $this->financialModel->getMovements($filters);
        
        // Obtener estadísticas
        $stats = $this->financialModel->getStats($filters['date_from'], $filters['date_to']);
        
        // Obtener tipos de movimiento para el filtro
        $movementTypes = $this->financialModel->getMovementTypes();
        
        // Obtener propiedades para el filtro
        $stmt = $this->db->query("SELECT id, property_number FROM properties ORDER BY property_number");
        $properties = $stmt->fetchAll();
        
        $data = [
            'title' => 'Módulo Financiero',
            'movements' => $movements,
            'stats' => $stats,
            'movementTypes' => $movementTypes,
            'properties' => $properties,
            'filters' => $filters
        ];
        
        $this->view('financial/index', $data);
    }
    
    /**
     * Crear nuevo movimiento
     */
    public function create() {
        $movementTypes = $this->financialModel->getMovementTypes();
        
        // Obtener propiedades
        $stmt = $this->db->query("SELECT id, property_number FROM properties ORDER BY property_number");
        $properties = $stmt->fetchAll();
        
        // Obtener residentes
        $stmt = $this->db->query("
            SELECT r.id, CONCAT(u.first_name, ' ', u.last_name) as name, p.property_number, r.property_id
            FROM residents r
            INNER JOIN users u ON r.user_id = u.id
            INNER JOIN properties p ON r.property_id = p.id
            WHERE r.status = 'active'
            ORDER BY u.first_name, u.last_name
        ");
        $residents = $stmt->fetchAll();
        
        $data = [
            'title' => 'Nuevo Movimiento Financiero',
            'movementTypes' => $movementTypes,
            'properties' => $properties,
            'residents' => $residents,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $movementData = [
                'movement_type_id' => $this->post('movement_type_id'),
                'transaction_type' => $this->post('transaction_type'),
                'amount' => $this->post('amount'),
                'description' => $this->post('description'),
                'property_id' => $this->post('property_id') ?: null,
                'resident_id' => $this->post('resident_id') ?: null,
                'payment_method' => $this->post('payment_method') ?: null,
                'payment_reference' => $this->post('payment_reference') ?: null,
                'transaction_date' => $this->post('transaction_date'),
                'created_by' => $_SESSION['user_id'],
                'notes' => $this->post('notes') ?: null
            ];
            
            if ($this->financialModel->create($movementData)) {
                AuditController::log('create', 'Movimiento financiero creado: ' . $movementData['description'], 'financial_movements', null);
                $_SESSION['success_message'] = 'Movimiento financiero creado exitosamente';
                $this->redirect('financial');
            } else {
                $data['error'] = 'Error al crear el movimiento financiero';
            }
        }
        
        $this->view('financial/create', $data);
    }
    
    /**
     * Ver detalle de un movimiento
     */
    public function viewDetails($id) {
        $movement = $this->financialModel->findById($id);
        
        if (!$movement) {
            $_SESSION['error_message'] = 'Movimiento no encontrado';
            $this->redirect('financial');
        }
        
        $data = [
            'title' => 'Detalle de Movimiento',
            'movement' => $movement
        ];
        
        $this->view('financial/view', $data);
    }
    
    /**
     * Editar movimiento
     */
    public function edit($id) {
        $movement = $this->financialModel->findById($id);
        
        if (!$movement) {
            $_SESSION['error_message'] = 'Movimiento no encontrado';
            $this->redirect('financial');
        }
        
        // No permitir editar movimientos referenciados
        if ($movement['reference_type'] && $movement['reference_id']) {
            $_SESSION['error_message'] = 'No se puede editar un movimiento generado automáticamente';
            $this->redirect('financial');
        }
        
        $movementTypes = $this->financialModel->getMovementTypes();
        
        // Obtener propiedades
        $stmt = $this->db->query("SELECT id, property_number FROM properties ORDER BY property_number");
        $properties = $stmt->fetchAll();
        
        // Obtener residentes
        $stmt = $this->db->query("
            SELECT r.id, CONCAT(u.first_name, ' ', u.last_name) as name, r.property_id
            FROM residents r
            INNER JOIN users u ON r.user_id = u.id
            WHERE r.status = 'active'
            ORDER BY u.first_name, u.last_name
        ");
        $residents = $stmt->fetchAll();
        
        $data = [
            'title' => 'Editar Movimiento',
            'movement' => $movement,
            'movementTypes' => $movementTypes,
            'properties' => $properties,
            'residents' => $residents,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $movementData = [
                'movement_type_id' => $this->post('movement_type_id'),
                'transaction_type' => $this->post('transaction_type'),
                'amount' => $this->post('amount'),
                'description' => $this->post('description'),
                'property_id' => $this->post('property_id') ?: null,
                'resident_id' => $this->post('resident_id') ?: null,
                'payment_method' => $this->post('payment_method') ?: null,
                'payment_reference' => $this->post('payment_reference') ?: null,
                'transaction_date' => $this->post('transaction_date'),
                'notes' => $this->post('notes') ?: null
            ];
            
            if ($this->financialModel->update($id, $movementData)) {
                AuditController::log('update', 'Movimiento financiero actualizado: ' . $movementData['description'], 'financial_movements', $id);
                $_SESSION['success_message'] = 'Movimiento actualizado exitosamente';
                $this->redirect('financial');
            } else {
                $data['error'] = 'Error al actualizar el movimiento';
            }
        }
        
        $this->view('financial/edit', $data);
    }
    
    /**
     * Eliminar movimiento
     */
    public function delete($id) {
        $movement = $this->financialModel->findById($id);
        
        if (!$movement) {
            $_SESSION['error_message'] = 'Movimiento no encontrado';
            $this->redirect('financial');
        }
        
        // No permitir eliminar movimientos referenciados
        if ($movement['reference_type'] && $movement['reference_id']) {
            $_SESSION['error_message'] = 'No se puede eliminar un movimiento generado automáticamente';
            $this->redirect('financial');
        }
        
        if ($this->financialModel->delete($id)) {
            AuditController::log('delete', 'Movimiento financiero eliminado ID: ' . $id, 'financial_movements', $id);
            $_SESSION['success_message'] = 'Movimiento eliminado exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al eliminar el movimiento';
        }
        
        $this->redirect('financial');
    }
    
    /**
     * Reporte detallado
     */
    public function report() {
        $dateFrom = $this->get('date_from', date('Y-m-d', strtotime('-12 months')));
        $dateTo = $this->get('date_to', date('Y-m-d'));
        
        $stats = $this->financialModel->getStats($dateFrom, $dateTo);
        
        $data = [
            'title' => 'Reporte Financiero Detallado',
            'stats' => $stats,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        $this->view('financial/report', $data);
    }
    
    /**
     * Catálogo de tipos de movimiento
     */
    public function movementTypes() {
        // Handle POST for creating new movement type
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
            $name = $this->post('name');
            $description = $this->post('description');
            $category = $this->post('category');
            
            if (empty($name) || empty($category)) {
                $_SESSION['error_message'] = 'El nombre y la categoría son obligatorios';
            } else {
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO financial_movement_types (name, description, category, is_active)
                        VALUES (?, ?, ?, 1)
                    ");
                    $stmt->execute([$name, $description, $category]);
                    
                    AuditController::log('create', 'Tipo de movimiento creado: ' . $name, 'financial_movement_types', $this->db->lastInsertId());
                    $_SESSION['success_message'] = 'Tipo de movimiento creado exitosamente';
                    $this->redirect('financial/movementTypes');
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = 'Error al crear el tipo de movimiento: ' . $e->getMessage();
                }
            }
        }
        
        // Handle POST for editing movement type
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
            $id = intval($this->post('id'));
            $name = $this->post('name');
            $description = $this->post('description');
            $category = $this->post('category');
            
            if (empty($name) || empty($category)) {
                $_SESSION['error_message'] = 'El nombre y la categoría son obligatorios';
            } else {
                try {
                    $stmt = $this->db->prepare("
                        UPDATE financial_movement_types 
                        SET name = ?, description = ?, category = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $description, $category, $id]);
                    
                    AuditController::log('update', 'Tipo de movimiento actualizado: ' . $name, 'financial_movement_types', $id);
                    $_SESSION['success_message'] = 'Tipo de movimiento actualizado exitosamente';
                    $this->redirect('financial/movementTypes');
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = 'Error al actualizar el tipo de movimiento: ' . $e->getMessage();
                }
            }
        }
        
        // Handle toggle active status
        if (isset($_GET['toggle']) && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $this->db->prepare("
                UPDATE financial_movement_types 
                SET is_active = NOT is_active 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            AuditController::log('update', 'Estado de tipo de movimiento actualizado', 'financial_movement_types', $id);
            $_SESSION['success_message'] = 'Estado actualizado exitosamente';
            $this->redirect('financial/movementTypes');
        }
        
        // Handle delete
        if (isset($_GET['delete']) && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            
            // Check if type is in use
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM financial_movements WHERE movement_type_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $_SESSION['error_message'] = 'No se puede eliminar el tipo de movimiento porque está en uso';
            } else {
                $stmt = $this->db->prepare("DELETE FROM financial_movement_types WHERE id = ?");
                $stmt->execute([$id]);
                
                AuditController::log('delete', 'Tipo de movimiento eliminado', 'financial_movement_types', $id);
                $_SESSION['success_message'] = 'Tipo de movimiento eliminado exitosamente';
            }
            $this->redirect('financial/movementTypes');
        }
        
        // Filtros y paginación
        $category_filter = $this->get('category');
        $page = max(1, intval($this->get('page', 1)));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        // Construir query con filtros
        $where = [];
        $params = [];
        
        if (!empty($category_filter)) {
            $where[] = "category = ?";
            $params[] = $category_filter;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Obtener total de registros
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM financial_movement_types $whereClause");
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        $total_pages = ceil($total / $per_page);
        
        // Obtener registros paginados
        $params[] = $per_page;
        $params[] = $offset;
        $stmt = $this->db->prepare("
            SELECT * FROM financial_movement_types 
            $whereClause 
            ORDER BY category, name 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $movementTypes = $stmt->fetchAll();
        
        $data = [
            'title' => 'Catálogo de Tipos de Movimiento',
            'movementTypes' => $movementTypes,
            'category_filter' => $category_filter,
            'page' => $page,
            'total_pages' => $total_pages,
            'total' => $total,
            'error' => ''
        ];
        
        $this->view('financial/movement_types', $data);
    }
}
