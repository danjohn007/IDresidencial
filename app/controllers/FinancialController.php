<?php
/**
 * Controlador de Módulo Financiero
 */

require_once APP_PATH . '/controllers/AuditController.php';

class FinancialController extends Controller {
    
    private $financialModel;
    private $db;
    private const EVIDENCE_UPLOAD_DIR = '/uploads/evidence/';
    private const EVIDENCE_ALLOWED_EXT = ['pdf', 'jpg', 'jpeg', 'png'];
    private const EVIDENCE_ALLOWED_MIME = ['application/pdf', 'image/jpeg', 'image/png'];
    
    /**
     * Upload evidence file with validation. Returns relative path or null.
     */
    private function uploadEvidenceFile(): ?string {
        if (empty($_FILES['evidence_file']['name']) || $_FILES['evidence_file']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext = strtolower(pathinfo($_FILES['evidence_file']['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($_FILES['evidence_file']['tmp_name']);
        if (!in_array($ext, self::EVIDENCE_ALLOWED_EXT) || !in_array($mime, self::EVIDENCE_ALLOWED_MIME)) {
            return null;
        }
        $uploadDir = PUBLIC_PATH . self::EVIDENCE_UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = 'evidence_' . time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['evidence_file']['tmp_name'], $uploadDir . $filename)) {
            return ltrim(self::EVIDENCE_UPLOAD_DIR, '/') . $filename;
        }
        return null;
    }
    
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
                'notes' => $this->post('notes') ?: null,
                'is_unforeseen' => ($this->post('transaction_type') === 'egreso' && $this->post('is_unforeseen')) ? 1 : 0,
                'evidence_file' => null
            ];
            
            // Handle evidence file upload (validated)
            $uploadedFile = $this->uploadEvidenceFile();
            if ($uploadedFile !== null) {
                $movementData['evidence_file'] = $uploadedFile;
            }
            
            $movementId = $this->financialModel->create($movementData);
            if ($movementId) {
                // Check if this is a maintenance fee payment
                $movementType = $this->db->prepare("
                    SELECT name, category 
                    FROM financial_movement_types 
                    WHERE id = ?
                ");
                $movementType->execute([$movementData['movement_type_id']]);
                $typeInfo = $movementType->fetch();
                
                // If this is a maintenance fee payment (income with 'mantenimiento' in name) and has a property
                // NOTE: Keyword matching is used here for simplicity. For production, consider adding 
                // an 'is_maintenance_fee' boolean flag to financial_movement_types table.
                if ($typeInfo && 
                    $movementData['transaction_type'] === 'ingreso' &&
                    (stripos($typeInfo['name'], 'mantenimiento') !== false || stripos($typeInfo['name'], 'cuota') !== false) && 
                    $movementData['property_id']) {
                    
                    // Try to find the oldest unpaid maintenance fee for this property
                    // This allows paying multiple months in order
                    // Use a small tolerance for amount comparison to handle floating-point precision
                    $feeStmt = $this->db->prepare("
                        SELECT id, period, amount FROM maintenance_fees 
                        WHERE property_id = ? 
                        AND status IN ('pending', 'overdue')
                        AND ABS(amount - ?) < 0.01
                        ORDER BY due_date ASC
                        LIMIT 1
                    ");
                    $feeStmt->execute([$movementData['property_id'], $movementData['amount']]);
                    $fee = $feeStmt->fetch();
                    
                    // If no exact amount match, try to find any unpaid fee for this property
                    if (!$fee) {
                        $feeStmt = $this->db->prepare("
                            SELECT id, period, amount FROM maintenance_fees 
                            WHERE property_id = ? 
                            AND status IN ('pending', 'overdue')
                            ORDER BY due_date ASC
                            LIMIT 1
                        ");
                        $feeStmt->execute([$movementData['property_id']]);
                        $fee = $feeStmt->fetch();
                    }
                    
                    if ($fee) {
                        // Update the maintenance fee status
                        $updateFeeStmt = $this->db->prepare("
                            UPDATE maintenance_fees 
                            SET status = 'paid', 
                                paid_date = ?,
                                payment_method = ?,
                                payment_reference = ?
                            WHERE id = ?
                        ");
                        $updateFeeStmt->execute([
                            $movementData['transaction_date'],
                            $movementData['payment_method'],
                            $movementData['payment_reference'],
                            $fee['id']
                        ]);
                        
                        // Update the financial movement with reference
                        $updateMovementStmt = $this->db->prepare("
                            UPDATE financial_movements 
                            SET reference_type = 'maintenance_fee', reference_id = ?
                            WHERE id = ?
                        ");
                        $updateMovementStmt->execute([$fee['id'], $movementId]);
                    }
                }
                
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
                'notes' => $this->post('notes') ?: null,
                'is_unforeseen' => ($this->post('transaction_type') === 'egreso' && $this->post('is_unforeseen')) ? 1 : 0,
                'evidence_file' => $movement['evidence_file'] ?? null
            ];
            
            // Handle evidence file upload (validated)
            $uploadedFile = $this->uploadEvidenceFile();
            if ($uploadedFile !== null) {
                $movementData['evidence_file'] = $uploadedFile;
            }
            
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
     * Presupuesto y Proyecciones
     */
    public function budget() {
        $currentMonth = date('Y-m');
        $nextMonth = date('Y-m', strtotime('+1 month'));
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
            FROM maintenance_fees 
            WHERE period = ? AND status = 'paid'
        ");
        $stmt->execute([$currentMonth]);
        $paidCurrent = $stmt->fetch();
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
            FROM maintenance_fees 
            WHERE period = ? AND status IN ('pending', 'overdue')
        ");
        $stmt->execute([$currentMonth]);
        $pendingCurrent = $stmt->fetch();
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
            FROM maintenance_fees 
            WHERE period = ?
        ");
        $stmt->execute([$nextMonth]);
        $nextMonthFees = $stmt->fetch();
        
        $stmt = $this->db->query("
            SELECT COALESCE(AVG(monthly_total), 0) as avg_income
            FROM (
                SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month, SUM(amount) as monthly_total
                FROM financial_movements
                WHERE transaction_type = 'ingreso'
                AND transaction_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                GROUP BY month
            ) as monthly_data
        ");
        $avgIncome = $stmt->fetch()['avg_income'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM properties");
        $totalProperties = $stmt->fetch()['total'];
        
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as count, COALESCE(SUM(amount), 0) as total
            FROM maintenance_fees
            WHERE period = ?
            GROUP BY status
        ");
        $stmt->execute([$currentMonth]);
        $currentMonthStats = $stmt->fetchAll();
        
        $data = [
            'title' => 'Presupuesto y Proyecciones',
            'currentMonth' => $currentMonth,
            'nextMonth' => $nextMonth,
            'paidCurrent' => $paidCurrent,
            'pendingCurrent' => $pendingCurrent,
            'nextMonthFees' => $nextMonthFees,
            'avgIncome' => $avgIncome,
            'totalProperties' => $totalProperties,
            'currentMonthStats' => $currentMonthStats
        ];
        
        $this->view('financial/budget', $data);
    }
    
    /**
     * Cartera Vencida
     */
    public function overdueAccounts() {
        $page = max(1, intval($this->get('page', 1)));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        $search = $this->get('search', '');
        $date_from = $this->get('date_from', date('Y-m-01'));
        $date_to = $this->get('date_to', date('Y-m-d'));
        
        $where = ["mf.status IN ('pending', 'overdue')"];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(p.property_number LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        
        if (!empty($date_from)) {
            $where[] = "mf.due_date >= ?";
            $params[] = $date_from;
        }
        
        if (!empty($date_to)) {
            $where[] = "mf.due_date <= ?";
            $params[] = $date_to;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $where);
        
        $countParams = $params;
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM maintenance_fees mf
            INNER JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1
            LEFT JOIN users u ON r.user_id = u.id
            $whereClause
        ");
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        $total_pages = ceil($total / $per_page);
        
        $params[] = $per_page;
        $params[] = $offset;
        
        $stmt = $this->db->prepare("
            SELECT mf.*, p.property_number,
                   CONCAT(u.first_name, ' ', u.last_name) as resident_name,
                   u.phone as resident_phone,
                   DATEDIFF(NOW(), mf.due_date) as days_overdue
            FROM maintenance_fees mf
            INNER JOIN properties p ON mf.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1
            LEFT JOIN users u ON r.user_id = u.id
            $whereClause
            ORDER BY mf.due_date ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $records = $stmt->fetchAll();
        
        $stmt = $this->db->query("
            SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total
            FROM maintenance_fees WHERE status IN ('pending', 'overdue')
        ");
        $overallStats = $stmt->fetch();
        
        $data = [
            'title' => 'Cartera Vencida',
            'records' => $records,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'search' => $search,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'overallStats' => $overallStats
        ];
        
        $this->view('financial/overdue', $data);
    }
    
    /**
     * Importar CSV Bancario
     */
    public function importCSV() {
        $data = [
            'title' => 'Importar CSV Bancario',
            'error' => '',
            'preview' => [],
            'movementTypes' => $this->financialModel->getMovementTypes()
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file'];
            
            if ($file['error'] === UPLOAD_ERR_OK) {
                $handle = fopen($file['tmp_name'], 'r');
                $rows = [];
                $headers = null;
                
                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    if (!$headers) {
                        $headers = $row;
                        continue;
                    }
                    $rows[] = $row;
                }
                fclose($handle);
                
                if (isset($_POST['confirm_import'])) {
                    $imported = 0;
                    foreach ($rows as $rowIndex => $row) {
                        $amount = floatval(str_replace(['$', ',', ' '], '', $row[2] ?? 0));
                        $transType = $amount >= 0 ? 'ingreso' : 'egreso';
                        $amount = abs($amount);
                        
                        $movTypeId = isset($_POST['movement_type_id_' . $rowIndex]) ? intval($_POST['movement_type_id_' . $rowIndex]) : 1;
                        
                        $stmt = $this->db->prepare("
                            INSERT INTO financial_movements 
                            (movement_type_id, transaction_type, amount, description, transaction_date, created_by, notes)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $movTypeId,
                            $transType,
                            $amount,
                            $row[3] ?? 'Importado de CSV',
                            !empty($row[0]) ? date('Y-m-d', strtotime($row[0])) : date('Y-m-d'),
                            $_SESSION['user_id'],
                            'Importado desde CSV bancario'
                        ]);
                        $imported++;
                    }
                    
                    AuditController::log('create', "Importación CSV: $imported movimientos", 'financial_movements', null);
                    $_SESSION['success_message'] = "Se importaron $imported movimientos exitosamente";
                    $this->redirect('financial');
                } else {
                    $data['preview'] = $rows;
                    $data['headers'] = $headers ?? [];
                }
            } else {
                $data['error'] = 'Error al cargar el archivo CSV';
            }
        }
        
        $this->view('financial/import_csv', $data);
    }
    
    /**
     * Catálogo de tipos de movimiento
     */
    public function movementTypes() {
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
