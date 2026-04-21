<?php
/**
 * Controlador de Mensajería - Recepción de Paquetes
 */

require_once APP_PATH . '/controllers/AuditController.php';

class MessagingController extends Controller {

    private $db;
    private const DELIVERY_KEY_LENGTH = 8;

    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador', 'guardia']);
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Vista principal - lista de paquetes
     */
    public function index() {
        $page = max(1, intval($this->get('page', 1)));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        $search = $this->get('search', '');
        $status = $this->get('status', '');

        $where = ['1=1'];
        $params = [];

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $where[] = "(p.property_number LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR pkg.tracking_number LIKE ? OR pkg.sender LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($status)) {
            $where[] = 'pkg.status = ?';
            $params[] = $status;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $countParams = $params;
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM packages pkg
            LEFT JOIN properties p ON pkg.property_id = p.id
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
            SELECT pkg.*,
                   p.property_number,
                   CONCAT(u.first_name, ' ', u.last_name) as resident_name,
                   u.phone as resident_phone
            FROM packages pkg
            LEFT JOIN properties p ON pkg.property_id = p.id
            LEFT JOIN residents r ON r.property_id = p.id AND r.is_primary = 1
            LEFT JOIN users u ON r.user_id = u.id
            $whereClause
            ORDER BY pkg.received_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $packages = $stmt->fetchAll();

        // Stats
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM packages GROUP BY status");
        $statsRaw = $stmt->fetchAll();
        $stats = [];
        foreach ($statsRaw as $row) {
            $stats[$row['status']] = $row['count'];
        }

        $data = [
            'title' => 'Mensajería - Recepción de Paquetes',
            'packages' => $packages,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'search' => $search,
            'status' => $status,
            'stats' => $stats
        ];

        $this->view('messaging/index', $data);
    }

    /**
     * Registrar nuevo paquete
     */
    public function create() {
        $data = [
            'title' => 'Registrar Paquete',
            'error' => '',
            'properties' => []
        ];

        $stmt = $this->db->query("SELECT id, property_number FROM properties ORDER BY property_number");
        $data['properties'] = $stmt->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propertyId = intval($this->post('property_id'));
            $trackingNumber = trim($this->post('tracking_number', ''));
            $sender = trim($this->post('sender', ''));
            $description = trim($this->post('description', ''));
            $packageType = $this->post('package_type', 'paquete');
            $notes = trim($this->post('notes', ''));
            $deliveryKey = $this->generateDeliveryKey();

            if (!$propertyId) {
                $data['error'] = 'Debes seleccionar una propiedad.';
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO packages (property_id, tracking_number, sender, description, package_type, notes, delivery_key, status, received_at, received_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW(), ?)
                ");
                $ok = $stmt->execute([$propertyId, $trackingNumber ?: null, $sender ?: null, $description ?: null, $packageType, $notes ?: null, $deliveryKey, $_SESSION['user_id']]);

                if ($ok) {
                    $packageId = $this->db->lastInsertId();
                    AuditController::log('create', 'Paquete registrado #' . $packageId, 'packages', $packageId);
                    $_SESSION['success_message'] = 'Paquete registrado exitosamente';
                    $this->redirect('messaging');
                } else {
                    $data['error'] = 'Error al registrar el paquete. Intenta de nuevo.';
                }
            }
        }

        $this->view('messaging/create', $data);
    }

    /**
     * Marcar paquete como entregado
     */
    public function deliver($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('messaging');
        }

        $receiverName = trim($this->post('receiver_name', ''));
        $deliveryKey = strtoupper(trim($this->post('delivery_key', '')));

        if ($receiverName === '' || $deliveryKey === '') {
            $_SESSION['error_message'] = 'Debes ingresar quién recibe y la clave de entrega';
            $this->redirect('messaging');
        }

        $stmt = $this->db->prepare("SELECT delivery_key FROM packages WHERE id = ? AND status = 'pendiente' LIMIT 1");
        $stmt->execute([$id]);
        $package = $stmt->fetch();

        if (!$package) {
            $_SESSION['error_message'] = 'No se pudo actualizar el paquete';
            $this->redirect('messaging');
        }

        $storedKey = strtoupper((string)($package['delivery_key'] ?? ''));
        if ($storedKey === '' || !hash_equals($storedKey, $deliveryKey)) {
            $_SESSION['error_message'] = 'La clave de entrega no coincide';
            $this->redirect('messaging');
        }

        $evidencePath = null;
        if (!empty($_FILES['delivery_evidence']) && $_FILES['delivery_evidence']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileType = $finfo->file($_FILES['delivery_evidence']['tmp_name']);

            if (!in_array($fileType, $allowedTypes, true)) {
                $_SESSION['error_message'] = 'La evidencia debe ser una imagen JPG, PNG, WEBP o GIF';
                $this->redirect('messaging');
            }

            $extension = strtolower(pathinfo($_FILES['delivery_evidence']['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions, true)) {
                $_SESSION['error_message'] = 'Formato de evidencia no permitido';
                $this->redirect('messaging');
            }

            if ($_FILES['delivery_evidence']['size'] > (5 * 1024 * 1024)) {
                $_SESSION['error_message'] = 'La evidencia no puede superar 5MB';
                $this->redirect('messaging');
            }

            $uploadDir = PUBLIC_PATH . '/uploads/package_evidence/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'pkg_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $destination = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['delivery_evidence']['tmp_name'], $destination)) {
                $_SESSION['error_message'] = 'No se pudo guardar la evidencia';
                $this->redirect('messaging');
            }

            $evidencePath = 'uploads/package_evidence/' . $fileName;
        }

        $stmt = $this->db->prepare("
            UPDATE packages
            SET status = 'entregado_pendiente',
                delivered_at = NOW(),
                delivered_by = ?,
                receiver_name = ?,
                delivery_evidence_path = ?
            WHERE id = ? AND status = 'pendiente'
        ");
        if ($stmt->execute([$_SESSION['user_id'], $receiverName, $evidencePath, $id]) && $stmt->rowCount() > 0) {
            AuditController::log('update', 'Paquete entregado #' . $id, 'packages', $id);
            $_SESSION['success_message'] = 'Paquete marcado como entregado, pendiente de confirmación por el residente';
        } else {
            $_SESSION['error_message'] = 'No se pudo actualizar el paquete';
        }

        $this->redirect('messaging');
    }

    private function generateDeliveryKey() {
        // Se omiten caracteres ambiguos (I, O, 0, 1) para evitar errores al dictar o capturar la clave.
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $maxIndex = strlen($chars) - 1;
        $key = '';

        for ($i = 0; $i < self::DELIVERY_KEY_LENGTH; $i++) {
            $key .= $chars[random_int(0, $maxIndex)];
        }

        return $key;
    }
}
