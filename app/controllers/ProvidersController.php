<?php
/**
 * Controlador de Proveedores de Mantenimiento
 */

require_once APP_PATH . '/controllers/AuditController.php';

class ProvidersController extends Controller {

    private $db;

    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador']);
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lista de proveedores
     */
    public function index() {
        $search = $this->get('search', '');
        $status = $this->get('status', '');
        $category = $this->get('category', '');

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(p.company_name LIKE ? OR p.contact_name LIKE ? OR p.phone LIKE ? OR p.email LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
        }
        if ($status !== '') {
            $where[] = "p.status = ?";
            $params[] = $status;
        }
        if ($category !== '') {
            $where[] = "p.category = ?";
            $params[] = $category;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT p.*,
                   (SELECT COUNT(*) FROM provider_service_requests sr WHERE sr.provider_id = p.id) as total_requests,
                   (SELECT COUNT(*) FROM provider_service_requests sr WHERE sr.provider_id = p.id AND sr.status IN ('pending','in_progress')) as open_requests
            FROM providers p
            $whereClause
            ORDER BY p.company_name ASC
        ");
        $stmt->execute($params);
        $providers = $stmt->fetchAll();

        // Categories list for filter
        $catStmt = $this->db->query("SELECT DISTINCT category FROM providers WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

        // Stats from database (single query, not filtered)
        $statsStmt = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
            FROM providers
        ");
        $stats = $statsStmt->fetch();

        $data = [
            'title'      => 'Proveedores',
            'providers'  => $providers,
            'stats'      => $stats,
            'categories' => $categories,
            'filters'    => compact('search', 'status', 'category')
        ];

        $this->view('providers/index', $data);
    }

    /**
     * Crear proveedor
     */
    public function create() {
        $data = ['title' => 'Nuevo Proveedor', 'error' => '', 'provider' => []];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $providerData = [
                'company_name' => trim($this->post('company_name', '')),
                'contact_name' => trim($this->post('contact_name', '')),
                'phone'        => trim($this->post('phone', '')),
                'email'        => trim($this->post('email', '')),
                'category'     => trim($this->post('category', '')),
                'address'      => trim($this->post('address', '')),
                'rfc'          => trim($this->post('rfc', '')),
                'notes'        => trim($this->post('notes', '')),
                'status'       => 'active',
                'created_by'   => $_SESSION['user_id']
            ];

            if (empty($providerData['company_name'])) {
                $data['error'] = 'El nombre de la empresa es obligatorio.';
                $data['provider'] = $providerData;
                $this->view('providers/create', $data);
                return;
            }

            if (!empty($providerData['email']) && !filter_var($providerData['email'], FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'El correo electrónico no es válido.';
                $data['provider'] = $providerData;
                $this->view('providers/create', $data);
                return;
            }

            $stmt = $this->db->prepare("
                INSERT INTO providers (company_name, contact_name, phone, email, category, address, rfc, notes, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $providerData['company_name'],
                $providerData['contact_name'] ?: null,
                $providerData['phone'] ?: null,
                $providerData['email'] ?: null,
                $providerData['category'] ?: null,
                $providerData['address'] ?: null,
                $providerData['rfc'] ?: null,
                $providerData['notes'] ?: null,
                $providerData['status'],
                $providerData['created_by']
            ]);
            $newId = $this->db->lastInsertId();

            AuditController::log('create', 'Proveedor creado: ' . $providerData['company_name'], 'providers', $newId);
            $_SESSION['success_message'] = 'Proveedor creado exitosamente.';
            $this->redirect('providers');
        }

        $this->view('providers/create', $data);
    }

    /**
     * Ver detalles de proveedor
     */
    public function viewDetails($id) {
        $provider = $this->findProvider($id);

        // Get service requests for this provider
        $stmt = $this->db->prepare("
            SELECT sr.*, p.property_number
            FROM provider_service_requests sr
            LEFT JOIN properties p ON sr.property_id = p.id
            WHERE sr.provider_id = ?
            ORDER BY sr.created_at DESC
        ");
        $stmt->execute([$id]);
        $requests = $stmt->fetchAll();

        $data = [
            'title'    => 'Detalle del Proveedor',
            'provider' => $provider,
            'requests' => $requests
        ];

        $this->view('providers/view', $data);
    }

    /**
     * Editar proveedor
     */
    public function edit($id) {
        $provider = $this->findProvider($id);
        $data = ['title' => 'Editar Proveedor', 'error' => '', 'provider' => $provider];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $providerData = [
                'company_name' => trim($this->post('company_name', '')),
                'contact_name' => trim($this->post('contact_name', '')),
                'phone'        => trim($this->post('phone', '')),
                'email'        => trim($this->post('email', '')),
                'category'     => trim($this->post('category', '')),
                'address'      => trim($this->post('address', '')),
                'rfc'          => trim($this->post('rfc', '')),
                'notes'        => trim($this->post('notes', '')),
                'status'       => $this->post('status', 'active')
            ];

            if (empty($providerData['company_name'])) {
                $data['error'] = 'El nombre de la empresa es obligatorio.';
                $data['provider'] = array_merge($provider, $providerData);
                $this->view('providers/edit', $data);
                return;
            }

            if (!empty($providerData['email']) && !filter_var($providerData['email'], FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'El correo electrónico no es válido.';
                $data['provider'] = array_merge($provider, $providerData);
                $this->view('providers/edit', $data);
                return;
            }

            $stmt = $this->db->prepare("
                UPDATE providers
                SET company_name = ?, contact_name = ?, phone = ?, email = ?,
                    category = ?, address = ?, rfc = ?, notes = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $providerData['company_name'],
                $providerData['contact_name'] ?: null,
                $providerData['phone'] ?: null,
                $providerData['email'] ?: null,
                $providerData['category'] ?: null,
                $providerData['address'] ?: null,
                $providerData['rfc'] ?: null,
                $providerData['notes'] ?: null,
                $providerData['status'],
                $id
            ]);

            AuditController::log('update', 'Proveedor actualizado: ' . $providerData['company_name'], 'providers', $id);
            $_SESSION['success_message'] = 'Proveedor actualizado exitosamente.';
            $this->redirect('providers');
        }

        $this->view('providers/edit', $data);
    }

    /**
     * Eliminar proveedor (soft: set inactive)
     */
    public function delete($id) {
        $provider = $this->findProvider($id);

        $stmt = $this->db->prepare("UPDATE providers SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$id]);

        AuditController::log('delete', 'Proveedor desactivado: ' . $provider['company_name'], 'providers', $id);
        $_SESSION['success_message'] = 'Proveedor desactivado exitosamente.';
        $this->redirect('providers');
    }

    /**
     * Lista de solicitudes de servicio
     */
    public function requests() {
        $status = $this->get('status', '');
        $priority = $this->get('priority', '');
        $providerId = $this->get('provider_id', '');
        $search = $this->get('search', '');

        $where = [];
        $params = [];

        if ($status !== '') {
            $where[] = "sr.status = ?";
            $params[] = $status;
        }
        if ($priority !== '') {
            $where[] = "sr.priority = ?";
            $params[] = $priority;
        }
        if ($providerId !== '') {
            $where[] = "sr.provider_id = ?";
            $params[] = $providerId;
        }
        if ($search !== '') {
            $where[] = "(sr.title LIKE ? OR sr.description LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term; $params[] = $term;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT sr.*, prov.company_name as provider_name, prop.property_number
            FROM provider_service_requests sr
            LEFT JOIN providers prov ON sr.provider_id = prov.id
            LEFT JOIN properties prop ON sr.property_id = prop.id
            $whereClause
            ORDER BY
                CASE sr.priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END,
                sr.created_at DESC
        ");
        $stmt->execute($params);
        $requests = $stmt->fetchAll();

        // Get providers for filter dropdown
        $provStmt = $this->db->query("SELECT id, company_name FROM providers WHERE status = 'active' ORDER BY company_name");
        $providers = $provStmt->fetchAll();

        $data = [
            'title'     => 'Solicitudes de Servicio',
            'requests'  => $requests,
            'providers' => $providers,
            'filters'   => compact('status', 'priority', 'providerId', 'search')
        ];

        $this->view('providers/requests', $data);
    }

    /**
     * Crear solicitud de servicio
     */
    public function createRequest() {
        // Get providers for dropdown
        $provStmt = $this->db->query("SELECT id, company_name FROM providers WHERE status = 'active' ORDER BY company_name");
        $providers = $provStmt->fetchAll();

        // Get properties for dropdown
        $propStmt = $this->db->query("SELECT id, property_number FROM properties ORDER BY property_number");
        $properties = $propStmt->fetchAll();

        $data = [
            'title'      => 'Nueva Solicitud de Servicio',
            'error'      => '',
            'providers'  => $providers,
            'properties' => $properties,
            'request'    => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestData = [
                'provider_id'    => intval($this->post('provider_id', 0)) ?: null,
                'title'          => trim($this->post('title', '')),
                'description'    => trim($this->post('description', '')),
                'category'       => trim($this->post('category', '')),
                'area'           => trim($this->post('area', '')),
                'property_id'    => intval($this->post('property_id', 0)) ?: null,
                'priority'       => $this->post('priority', 'medium'),
                'requested_date' => $this->post('requested_date') ?: date('Y-m-d'),
                'scheduled_date' => $this->post('scheduled_date') ?: null,
                'estimated_cost' => $this->post('estimated_cost') ?: null,
                'notes'          => trim($this->post('notes', '')),
                'status'         => 'pending',
                'created_by'     => $_SESSION['user_id']
            ];

            if (empty($requestData['title'])) {
                $data['error'] = 'El título de la solicitud es obligatorio.';
                $data['request'] = $requestData;
                $this->view('providers/create_request', $data);
                return;
            }

            $stmt = $this->db->prepare("
                INSERT INTO provider_service_requests
                (provider_id, title, description, category, area, property_id, priority,
                 requested_date, scheduled_date, estimated_cost, notes, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $requestData['provider_id'],
                $requestData['title'],
                $requestData['description'] ?: null,
                $requestData['category'] ?: null,
                $requestData['area'] ?: null,
                $requestData['property_id'],
                $requestData['priority'],
                $requestData['requested_date'],
                $requestData['scheduled_date'],
                $requestData['estimated_cost'],
                $requestData['notes'] ?: null,
                $requestData['status'],
                $requestData['created_by']
            ]);
            $newId = $this->db->lastInsertId();

            AuditController::log('create', 'Solicitud de servicio creada: ' . $requestData['title'], 'provider_service_requests', $newId);
            $_SESSION['success_message'] = 'Solicitud de servicio creada exitosamente.';
            $this->redirect('providers/requests');
        }

        $this->view('providers/create_request', $data);
    }

    /**
     * Actualizar estado de una solicitud de servicio (AJAX o redirect)
     */
    public function updateRequestStatus($requestId = null) {
        if (!$requestId) {
            $this->redirect('providers/requests');
        }

        $newStatus = $this->post('status');
        $actualCost = $this->post('actual_cost');
        $completedDate = ($newStatus === 'completed') ? date('Y-m-d') : null;

        $stmt = $this->db->prepare("
            UPDATE provider_service_requests
            SET status = ?,
                actual_cost = ?,
                completed_date = ?
            WHERE id = ?
        ");
        $stmt->execute([$newStatus, $actualCost ?: null, $completedDate, $requestId]);

        AuditController::log('update', 'Estado de solicitud actualizado: ' . $newStatus, 'provider_service_requests', $requestId);
        $_SESSION['success_message'] = 'Estado de la solicitud actualizado.';
        $this->redirect('providers/requests');
    }

    /**
     * Helper: get provider or redirect if not found
     */
    private function findProvider($id) {
        $stmt = $this->db->prepare("SELECT * FROM providers WHERE id = ?");
        $stmt->execute([$id]);
        $provider = $stmt->fetch();
        if (!$provider) {
            $_SESSION['error_message'] = 'Proveedor no encontrado.';
            $this->redirect('providers');
        }
        return $provider;
    }
}
