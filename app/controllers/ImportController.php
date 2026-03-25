<?php
/**
 * Controlador de Importación de Datos
 */

class ImportController extends Controller {
    
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin']);
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de importación
     */
    public function index() {
        $data = [
            'title' => 'Importar Datos',
            'error' => '',
            'success' => ''
        ];
        
        $this->view('import/index', $data);
    }
    
    /**
     * Importar residentes desde CSV
     */
    public function residents() {
        $data = [
            'title' => 'Importar Residentes',
            'error' => '',
            'success' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processResidentsCSV($_FILES['csv_file']);
            
            if ($result['success']) {
                $data['success'] = $result['message'];
            } else {
                $data['error'] = $result['message'];
            }
        }
        
        $this->view('import/residents', $data);
    }
    
    /**
     * Importar propiedades (casas/departamentos/torres) desde CSV
     */
    public function properties() {
        $data = [
            'title' => 'Importar Propiedades',
            'error' => '',
            'success' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processPropertiesCSV($_FILES['csv_file']);
            
            if ($result['success']) {
                $data['success'] = $result['message'];
            } else {
                $data['error'] = $result['message'];
            }
        }
        
        $this->view('import/properties', $data);
    }

    /**
     * Importar usuarios desde CSV
     */
    public function users() {
        $data = [
            'title' => 'Importar Usuarios',
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processUsersCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
            } else {
                $data['error'] = $result['message'];
            }
        }

        $this->view('import/users', $data);
    }

    /**
     * Importar cuotas de mantenimiento desde CSV
     */
    public function maintenanceFees() {
        $data = [
            'title' => 'Importar Cuotas de Mantenimiento',
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processMaintenanceFeesCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
            } else {
                $data['error'] = $result['message'];
            }
        }

        $this->view('import/maintenance_fees', $data);
    }

    /**
     * Importar amenidades desde CSV
     */
    public function amenities() {
        $data = [
            'title' => 'Importar Amenidades',
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processAmenitiesCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
            } else {
                $data['error'] = $result['message'];
            }
        }

        $this->view('import/amenities', $data);
    }

    /**
     * Importar movimientos financieros desde CSV
     */
    public function financialMovements() {
        $data = [
            'title' => 'Importar Movimientos Financieros',
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processFinancialMovementsCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
            } else {
                $data['error'] = $result['message'];
            }
        }

        $this->view('import/financial_movements', $data);
    }

    /**
     * Importar configuración CFDI desde CSV
     */
    public function cfdiConfig() {
        $data = [
            'title' => 'Importar Configuración CFDI',
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processCfdiConfigCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
            } else {
                $data['error'] = $result['message'];
            }
        }

        $this->view('import/cfdi_config', $data);
    }

    /**
     * Importar configuración PayPal desde CSV
     */
    public function paypalConfig() {
        $data = [
            'title' => 'Importar Configuración PayPal',
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processPaypalConfigCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
            } else {
                $data['error'] = $result['message'];
            }
        }

        $this->view('import/paypal_config', $data);
    }

    // ─────────────────────────────────────────────────────────────
    //  Descarga de plantillas CSV
    // ─────────────────────────────────────────────────────────────

    /**
     * Descargar plantilla CSV para residentes
     */
    public function downloadTemplateResidents() {
        $this->requireRole('superadmin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_residentes.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['username', 'email', 'first_name', 'last_name', 'phone', 'property_number', 'relationship']);
        fputcsv($output, ['jperez', 'jperez@example.com', 'Juan', 'Pérez', '5551234567', 'A-101', 'propietario']);
        fputcsv($output, ['mlopez', 'mlopez@example.com', 'María', 'López', '5559876543', 'B-202', 'inquilino']);
        fclose($output);
        exit;
    }

    /**
     * Descargar plantilla CSV para propiedades
     */
    public function downloadTemplateProperties() {
        $this->requireRole('superadmin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_propiedades.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['property_number', 'section', 'street', 'property_type', 'tower', 'bedrooms', 'bathrooms', 'area_m2', 'status']);
        fputcsv($output, ['A-101', 'A', 'Calle Principal', 'casa', '', '3', '2', '120.00', 'desocupada']);
        fputcsv($output, ['B-202', 'B', 'Av. Central', 'departamento', 'Torre 1', '2', '1', '75.50', 'ocupada']);
        fclose($output);
        exit;
    }

    /**
     * Descargar plantilla CSV para usuarios
     */
    public function downloadTemplateUsers() {
        $this->requireRole('superadmin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_usuarios.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['username', 'email', 'first_name', 'last_name', 'phone', 'role']);
        fputcsv($output, ['jperez', 'jperez@example.com', 'Juan', 'Pérez', '5551234567', 'residente']);
        fputcsv($output, ['aguardia', 'aguardia@example.com', 'Ana', 'Guardia', '5557654321', 'guardia']);
        fclose($output);
        exit;
    }

    /**
     * Descargar plantilla CSV para cuotas de mantenimiento
     */
    public function downloadTemplateMaintenanceFees() {
        $this->requireRole('superadmin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_cuotas.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['property_number', 'period', 'amount', 'due_date', 'status']);
        fputcsv($output, ['A-101', date('Y-m'), '1500.00', date('Y-m-d', strtotime('last day of this month')), 'pending']);
        fputcsv($output, ['B-202', date('Y-m'), '1500.00', date('Y-m-d', strtotime('last day of this month')), 'paid']);
        fclose($output);
        exit;
    }

    /**
     * Descargar plantilla CSV para amenidades
     */
    public function downloadTemplateAmenities() {
        $this->requireRole('superadmin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_amenidades.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['name', 'amenity_type', 'description', 'capacity', 'hourly_rate', 'hours_open', 'hours_close', 'requires_payment', 'status']);
        fputcsv($output, ['Alberca Principal', 'alberca', 'Alberca olímpica con área de descanso', '50', '0.00', '07:00', '21:00', '0', 'active']);
        fputcsv($output, ['Salón de Eventos', 'salon', 'Salón para 100 personas con cocina', '100', '500.00', '08:00', '22:00', '1', 'active']);
        fclose($output);
        exit;
    }

    /**
     * Descargar plantilla CSV para movimientos financieros
     */
    public function downloadTemplateFinancialMovements() {
        $this->requireRole('superadmin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_movimientos_financieros.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['movement_type_id', 'transaction_type', 'amount', 'description', 'payment_method', 'transaction_date', 'property_number', 'notes']);
        fputcsv($output, ['1', 'ingreso', '1500.00', 'Cuota de mantenimiento enero', 'transferencia', date('Y-m-d'), 'A-101', '']);
        fputcsv($output, ['2', 'egreso', '500.00', 'Pago de servicio de limpieza', 'efectivo', date('Y-m-d'), '', 'Servicio mensual']);
        fclose($output);
        exit;
    }

    /**
     * Descargar plantilla CSV para configuración CFDI
     */
    public function downloadTemplateCfdiConfig() {
        $this->requireRole('superadmin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_cfdi_config.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['setting_key', 'setting_value']);
        fputcsv($output, ['cfdi_rfc', 'XAXX010101000']);
        fputcsv($output, ['cfdi_razon_social', 'Mi Residencial SA de CV']);
        fputcsv($output, ['cfdi_regimen_fiscal', '601']);
        fputcsv($output, ['cfdi_cp', '76000']);
        fputcsv($output, ['cfdi_uso_cfdi', 'G03']);
        fputcsv($output, ['cfdi_metodo_pago', 'PUE']);
        fputcsv($output, ['cfdi_forma_pago', '01']);
        fputcsv($output, ['cfdi_serie', 'A']);
        fputcsv($output, ['cfdi_folio_inicio', '1']);
        fclose($output);
        exit;
    }

    /**
     * Descargar plantilla CSV para configuración PayPal
     */
    public function downloadTemplatePaypalConfig() {
        $this->requireRole('superadmin');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_paypal_config.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['setting_key', 'setting_value']);
        fputcsv($output, ['paypal_enabled', '1']);
        fputcsv($output, ['paypal_mode', 'sandbox']);
        fputcsv($output, ['paypal_client_id', 'TU_CLIENT_ID_AQUI']);
        fputcsv($output, ['paypal_secret', 'TU_SECRET_KEY_AQUI']);
        fclose($output);
        exit;
    }

    // ─────────────────────────────────────────────────────────────
    //  Métodos privados de procesamiento
    // ─────────────────────────────────────────────────────────────

    /**
     * Validación básica de archivo CSV subido
     */
    private function validateUploadedCsv($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo'];
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            return ['success' => false, 'message' => 'El archivo debe ser CSV'];
        }
        return ['success' => true];
    }

    /**
     * Procesar CSV de residentes
     * Columnas: username,email,first_name,last_name,phone,property_number,relationship
     * property_number y relationship son opcionales
     */
    private function processResidentsCSV($file) {
        $validation = $this->validateUploadedCsv($file);
        if (!$validation['success']) return $validation;

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 5) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido: se requieren al menos 5 columnas'];
        }

        $imported = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;

            try {
                $this->db->beginTransaction();

                $randomPassword = bin2hex(random_bytes(16));
                $password = password_hash($randomPassword, PASSWORD_DEFAULT);

                $stmt = $this->db->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, phone, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'residente', 'active')
                ");
                $stmt->execute([$row[0], $row[1], $password, $row[2], $row[3], $row[4]]);
                $userId = $this->db->lastInsertId();

                // Si se proporcionó número de propiedad, crear registro en residents
                $propertyNumber = isset($row[5]) ? trim($row[5]) : '';
                if ($propertyNumber !== '') {
                    $propStmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ? LIMIT 1");
                    $propStmt->execute([$propertyNumber]);
                    $property = $propStmt->fetch();

                    if ($property) {
                        $relationship = isset($row[6]) ? trim($row[6]) : 'propietario';
                        $allowedRel   = ['propietario', 'inquilino', 'familiar'];
                        if (!in_array($relationship, $allowedRel)) {
                            $relationship = 'propietario';
                        }

                        $resStmt = $this->db->prepare("
                            INSERT INTO residents (user_id, property_id, relationship, is_primary, status)
                            VALUES (?, ?, ?, 1, 'active')
                        ");
                        $resStmt->execute([$userId, $property['id'], $relationship]);
                    }
                }

                $imported++;
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollBack();
                $errors++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} registros importados, {$errors} errores"
        ];
    }

    /**
     * Procesar CSV de propiedades (casas/departamentos/torres)
     * Columnas: property_number,section,street,property_type,tower,bedrooms,bathrooms,area_m2,status
     */
    private function processPropertiesCSV($file) {
        $validation = $this->validateUploadedCsv($file);
        if (!$validation['success']) return $validation;

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 3) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido: se requieren al menos 3 columnas'];
        }

        $imported = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) continue;

            $propertyNumber = trim($row[0]);
            $section        = trim($row[1]);
            $street         = trim($row[2]);
            $propertyType   = isset($row[3]) && trim($row[3]) !== '' ? trim($row[3]) : 'casa';
            $tower          = isset($row[4]) ? trim($row[4]) : null;
            $bedrooms       = isset($row[5]) && is_numeric($row[5]) ? (int)$row[5] : 0;
            $bathrooms      = isset($row[6]) && is_numeric($row[6]) ? (int)$row[6] : 0;
            $areaM2         = isset($row[7]) && is_numeric($row[7]) ? (float)$row[7] : null;
            $status         = isset($row[8]) && trim($row[8]) !== '' ? trim($row[8]) : 'desocupada';

            $allowedTypes   = ['casa', 'departamento', 'lote'];
            $allowedStatus  = ['ocupada', 'desocupada', 'en_construccion'];
            if (!in_array($propertyType, $allowedTypes)) $propertyType = 'casa';
            if (!in_array($status, $allowedStatus))      $status = 'desocupada';
            if ($tower === '') $tower = null;

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO properties
                        (property_number, section, street, property_type, tower, bedrooms, bathrooms, area_m2, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        section       = VALUES(section),
                        street        = VALUES(street),
                        property_type = VALUES(property_type),
                        tower         = VALUES(tower),
                        bedrooms      = VALUES(bedrooms),
                        bathrooms     = VALUES(bathrooms),
                        area_m2       = VALUES(area_m2),
                        status        = VALUES(status)
                ");
                $stmt->execute([
                    $propertyNumber, $section, $street, $propertyType,
                    $tower, $bedrooms, $bathrooms, $areaM2, $status
                ]);

                $imported++;
            } catch (Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} registros importados, {$errors} errores"
        ];
    }

    /**
     * Procesar CSV de usuarios con soporte de roles
     * Columnas: username,email,first_name,last_name,phone,role
     */
    private function processUsersCSV($file) {
        $validation = $this->validateUploadedCsv($file);
        if (!$validation['success']) return $validation;

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 5) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido: se requieren al menos 5 columnas'];
        }

        $allowedRoles = ['superadmin', 'administrador', 'guardia', 'residente'];
        $imported = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;

            $role = isset($row[5]) && in_array(trim($row[5]), $allowedRoles) ? trim($row[5]) : 'residente';

            try {
                $randomPassword = bin2hex(random_bytes(16));
                $password = password_hash($randomPassword, PASSWORD_DEFAULT);

                $stmt = $this->db->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, phone, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                $stmt->execute([$row[0], $row[1], $password, $row[2], $row[3], $row[4], $role]);

                $imported++;
            } catch (Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} usuarios importados, {$errors} errores"
        ];
    }

    /**
     * Procesar CSV de cuotas de mantenimiento
     * Columnas: property_number,period,amount,due_date,status
     */
    private function processMaintenanceFeesCSV($file) {
        $validation = $this->validateUploadedCsv($file);
        if (!$validation['success']) return $validation;

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 4) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido: se requieren al menos 4 columnas'];
        }

        $allowedStatus = ['pending', 'paid', 'overdue', 'cancelled'];
        $imported = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            $propertyNumber = trim($row[0]);
            $period         = trim($row[1]);
            $amount         = is_numeric($row[2]) ? (float)$row[2] : null;
            $dueDate        = trim($row[3]);
            $status         = isset($row[4]) && in_array(trim($row[4]), $allowedStatus) ? trim($row[4]) : 'pending';

            if ($amount === null || $propertyNumber === '' || $period === '' || $dueDate === '') {
                $errors++;
                continue;
            }

            try {
                $propStmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ? LIMIT 1");
                $propStmt->execute([$propertyNumber]);
                $property = $propStmt->fetch();

                if (!$property) {
                    $errors++;
                    continue;
                }

                $stmt = $this->db->prepare("
                    INSERT INTO maintenance_fees (property_id, period, amount, due_date, status)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        amount   = VALUES(amount),
                        due_date = VALUES(due_date),
                        status   = VALUES(status)
                ");
                $stmt->execute([$property['id'], $period, $amount, $dueDate, $status]);

                $imported++;
            } catch (Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} cuotas importadas, {$errors} errores"
        ];
    }

    /**
     * Procesar CSV de amenidades
     * Columnas: name,amenity_type,description,capacity,hourly_rate,hours_open,hours_close,requires_payment,status
     */
    private function processAmenitiesCSV($file) {
        $validation = $this->validateUploadedCsv($file);
        if (!$validation['success']) return $validation;

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 2) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido: se requieren al menos 2 columnas'];
        }

        $allowedTypes  = ['salon', 'alberca', 'asadores', 'cancha', 'gimnasio', 'otro'];
        $allowedStatus = ['active', 'maintenance', 'inactive'];
        $imported = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;

            $name            = trim($row[0]);
            $amenityType     = isset($row[1]) && in_array(trim($row[1]), $allowedTypes) ? trim($row[1]) : 'otro';
            $description     = isset($row[2]) ? trim($row[2]) : null;
            $capacity        = isset($row[3]) && is_numeric($row[3]) ? (int)$row[3] : 0;
            $hourlyRate      = isset($row[4]) && is_numeric($row[4]) ? (float)$row[4] : 0.00;
            $hoursOpen       = isset($row[5]) && trim($row[5]) !== '' ? trim($row[5]) : null;
            $hoursClose      = isset($row[6]) && trim($row[6]) !== '' ? trim($row[6]) : null;
            $requiresPayment = isset($row[7]) ? (int)(bool)$row[7] : 0;
            $status          = isset($row[8]) && in_array(trim($row[8]), $allowedStatus) ? trim($row[8]) : 'active';

            if ($name === '') {
                $errors++;
                continue;
            }

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO amenities
                        (name, amenity_type, description, capacity, hourly_rate,
                         hours_open, hours_close, requires_payment, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name, $amenityType, $description, $capacity, $hourlyRate,
                    $hoursOpen, $hoursClose, $requiresPayment, $status
                ]);

                $imported++;
            } catch (Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} amenidades importadas, {$errors} errores"
        ];
    }

    /**
     * Procesar CSV de movimientos financieros
     * Columnas: movement_type_id,transaction_type,amount,description,payment_method,transaction_date,property_number,notes
     */
    private function processFinancialMovementsCSV($file) {
        $validation = $this->validateUploadedCsv($file);
        if (!$validation['success']) return $validation;

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 6) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido: se requieren al menos 6 columnas'];
        }

        $allowedTransTypes  = ['ingreso', 'egreso'];
        $allowedPayMethods  = ['efectivo', 'tarjeta', 'transferencia', 'paypal', 'otro'];
        $currentUser        = $this->getCurrentUser();
        $createdBy          = $currentUser['id'];
        $imported = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue;

            $movementTypeId  = is_numeric($row[0]) ? (int)$row[0] : null;
            $transactionType = in_array(trim($row[1]), $allowedTransTypes) ? trim($row[1]) : null;
            $amount          = is_numeric($row[2]) ? (float)$row[2] : null;
            $description     = trim($row[3]);
            $paymentMethod   = isset($row[4]) && in_array(trim($row[4]), $allowedPayMethods) ? trim($row[4]) : null;
            $transactionDate = trim($row[5]);
            $propertyNumber  = isset($row[6]) ? trim($row[6]) : '';
            $notes           = isset($row[7]) ? trim($row[7]) : null;

            if ($movementTypeId === null || $transactionType === null || $amount === null
                || $description === '' || $transactionDate === '') {
                $errors++;
                continue;
            }

            try {
                // Verificar que el tipo de movimiento existe
                $typeStmt = $this->db->prepare("SELECT id FROM financial_movement_types WHERE id = ? AND is_active = 1 LIMIT 1");
                $typeStmt->execute([$movementTypeId]);
                if (!$typeStmt->fetch()) {
                    $errors++;
                    continue;
                }

                $propertyId = null;
                if ($propertyNumber !== '') {
                    $propStmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ? LIMIT 1");
                    $propStmt->execute([$propertyNumber]);
                    $property = $propStmt->fetch();
                    if ($property) {
                        $propertyId = $property['id'];
                    }
                }

                $stmt = $this->db->prepare("
                    INSERT INTO financial_movements
                        (movement_type_id, transaction_type, amount, description,
                         payment_method, transaction_date, property_id, notes, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $movementTypeId, $transactionType, $amount, $description,
                    $paymentMethod, $transactionDate, $propertyId, $notes, $createdBy
                ]);

                $imported++;
            } catch (Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} movimientos importados, {$errors} errores"
        ];
    }

    /**
     * Procesar CSV de configuración CFDI
     * Columnas: setting_key,setting_value
     * Las claves permitidas tienen el prefijo cfdi_
     */
    private function processCfdiConfigCSV($file) {
        $validation = $this->validateUploadedCsv($file);
        if (!$validation['success']) return $validation;

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 2) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido: se requieren 2 columnas (setting_key, setting_value)'];
        }

        // Claves CFDI permitidas
        $allowedKeys = [
            'cfdi_rfc', 'cfdi_razon_social', 'cfdi_regimen_fiscal',
            'cfdi_cp', 'cfdi_uso_cfdi', 'cfdi_metodo_pago',
            'cfdi_forma_pago', 'cfdi_serie', 'cfdi_folio_inicio',
        ];

        $imported = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;

            $key   = trim($row[0]);
            $value = trim($row[1]);

            if (!in_array($key, $allowedKeys)) {
                $errors++;
                continue;
            }

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_type, description)
                    VALUES (?, ?, 'text', 'Configuración CFDI importada')
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([$key, $value]);

                $imported++;
            } catch (Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} parámetros CFDI importados, {$errors} errores"
        ];
    }

    /**
     * Procesar CSV de configuración PayPal
     * Columnas: setting_key,setting_value
     * Las claves permitidas son las de PayPal en system_settings
     */
    private function processPaypalConfigCSV($file) {
        $validation = $this->validateUploadedCsv($file);
        if (!$validation['success']) return $validation;

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 2) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido: se requieren 2 columnas (setting_key, setting_value)'];
        }

        // Claves PayPal permitidas
        $allowedKeys = [
            'paypal_enabled', 'paypal_mode', 'paypal_client_id', 'paypal_secret',
        ];

        $imported = 0;
        $errors   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;

            $key   = trim($row[0]);
            $value = trim($row[1]);

            if (!in_array($key, $allowedKeys)) {
                $errors++;
                continue;
            }

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_type, description)
                    VALUES (?, ?, 'text', 'Configuración PayPal importada')
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([$key, $value]);

                $imported++;
            } catch (Exception $e) {
                $errors++;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} parámetros PayPal importados, {$errors} errores"
        ];
    }
}
