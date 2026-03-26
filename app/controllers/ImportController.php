<?php
/**
 * Controlador de Importación de Datos
 */

require_once APP_PATH . '/helpers/XlsxWriter.php';
require_once APP_PATH . '/helpers/XlsxReader.php';

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
            'success' => '',
            'error_details' => [],
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processResidentsCSV($_FILES['csv_file']);
            
            if ($result['success']) {
                $data['success'] = $result['message'];
                $data['error_details'] = $result['error_details'] ?? [];
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
            'success' => '',
            'error_details' => [],
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processPropertiesCSV($_FILES['csv_file']);
            
            if ($result['success']) {
                $data['success'] = $result['message'];
                $data['error_details'] = $result['error_details'] ?? [];
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
            'success' => '',
            'error_details' => [],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processUsersCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
                $data['error_details'] = $result['error_details'] ?? [];
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
            'success' => '',
            'error_details' => [],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processMaintenanceFeesCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
                $data['error_details'] = $result['error_details'] ?? [];
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
            'success' => '',
            'error_details' => [],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processAmenitiesCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
                $data['error_details'] = $result['error_details'] ?? [];
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
            'success' => '',
            'error_details' => [],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processFinancialMovementsCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
                $data['error_details'] = $result['error_details'] ?? [];
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
            'success' => '',
            'error_details' => [],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processCfdiConfigCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
                $data['error_details'] = $result['error_details'] ?? [];
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
            'success' => '',
            'error_details' => [],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $result = $this->processPaypalConfigCSV($_FILES['csv_file']);

            if ($result['success']) {
                $data['success'] = $result['message'];
                $data['error_details'] = $result['error_details'] ?? [];
            } else {
                $data['error'] = $result['message'];
            }
        }

        $this->view('import/paypal_config', $data);
    }

    /**
     * Descargar plantilla CSV para cada tipo de importación
     */
    public function downloadTemplate($type = '') {
        $templates = [
            'residents' => [
                'filename' => 'plantilla_residentes.csv',
                'headers'  => ['username', 'email', 'first_name', 'last_name', 'phone', 'property_number', 'relationship'],
            ],
            'properties' => [
                'filename' => 'plantilla_propiedades.csv',
                'headers'  => ['property_number', 'section', 'street', 'property_type', 'tower', 'bedrooms', 'bathrooms', 'area_m2', 'status'],
            ],
            'users' => [
                'filename' => 'plantilla_usuarios.csv',
                'headers'  => ['username', 'email', 'first_name', 'last_name', 'phone', 'role'],
            ],
            'maintenanceFees' => [
                'filename' => 'plantilla_cuotas_mantenimiento.csv',
                'headers'  => ['property_number', 'period', 'amount', 'due_date', 'status'],
            ],
            'amenities' => [
                'filename' => 'plantilla_amenidades.csv',
                'headers'  => ['name', 'amenity_type', 'description', 'capacity', 'hourly_rate', 'hours_open', 'hours_close', 'requires_payment', 'status'],
            ],
            'financialMovements' => [
                'filename' => 'plantilla_movimientos_financieros.csv',
                'headers'  => ['movement_type_id', 'transaction_type', 'amount', 'description', 'payment_method', 'transaction_date', 'property_number', 'notes'],
            ],
            'cfdiConfig' => [
                'filename' => 'plantilla_cfdi.csv',
                'headers'  => ['setting_key', 'setting_value'],
            ],
            'paypalConfig' => [
                'filename' => 'plantilla_paypal.csv',
                'headers'  => ['setting_key', 'setting_value'],
            ],
        ];

        if (!isset($templates[$type])) {
            header('Location: ' . BASE_URL . '/import');
            exit;
        }

        $template = $templates[$type];

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $template['filename'] . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $template['headers']);
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
        $errorDetails = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} registros importados, {$errors} errores",
            'error_details' => $errorDetails,
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
        $errorDetails = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} registros importados, {$errors} errores",
            'error_details' => $errorDetails,
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
        $errorDetails = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} usuarios importados, {$errors} errores",
            'error_details' => $errorDetails,
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
        $errorDetails = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < 4) continue;

            $propertyNumber = trim($row[0]);
            $period         = trim($row[1]);
            $amount         = is_numeric($row[2]) ? (float)$row[2] : null;
            $dueDate        = trim($row[3]);
            $status         = isset($row[4]) && in_array(trim($row[4]), $allowedStatus) ? trim($row[4]) : 'pending';

            if ($amount === null || $propertyNumber === '' || $period === '' || $dueDate === '') {
                $reasons = [];
                if ($propertyNumber === '') $reasons[] = 'número de propiedad vacío';
                if ($period === '') $reasons[] = 'período vacío';
                if ($amount === null) $reasons[] = 'monto inválido';
                if ($dueDate === '') $reasons[] = 'fecha de vencimiento vacía';
                $errorDetails[] = "Fila {$rowNum}: " . implode(', ', $reasons);
                $errors++;
                continue;
            }

            try {
                $propStmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ? LIMIT 1");
                $propStmt->execute([$propertyNumber]);
                $property = $propStmt->fetch();

                if (!$property) {
                    $errors++;
                    $errorDetails[] = "Fila {$rowNum}: propiedad '{$propertyNumber}' no encontrada";
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} cuotas importadas, {$errors} errores",
            'error_details' => $errorDetails,
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
        $errorDetails = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
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
                $errorDetails[] = "Fila {$rowNum}: nombre de amenidad vacío";
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} amenidades importadas, {$errors} errores",
            'error_details' => $errorDetails,
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
        $errorDetails = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < 6) continue;

            $movementTypeRaw = trim($row[0] ?? '');
            $transactionRaw  = strtolower(trim($row[1]));
            $transactionType = in_array($transactionRaw, $allowedTransTypes) ? $transactionRaw : null;
            $amount          = is_numeric($row[2]) ? (float)$row[2] : null;
            $description     = trim($row[3]);
            $paymentRaw      = isset($row[4]) ? strtolower(trim($row[4])) : '';
            $paymentMethod   = in_array($paymentRaw, $allowedPayMethods) ? $paymentRaw : null;
            $transactionDate = $this->parseImportDate($row[5] ?? '');
            $propertyNumber  = isset($row[6]) ? trim($row[6]) : '';
            $notes           = isset($row[7]) ? trim($row[7]) : null;

            if ($movementTypeRaw === '' || $transactionType === null || $amount === null
                || $description === '' || $transactionDate === null) {
                $reasons = [];
                if ($movementTypeRaw === '') $reasons[] = 'tipo de movimiento vacío';
                if ($transactionType === null) $reasons[] = 'tipo de transacción inválido (use: ingreso, egreso)';
                if ($amount === null) $reasons[] = 'monto inválido';
                if ($description === '') $reasons[] = 'descripción vacía';
                if ($transactionDate === null) $reasons[] = 'fecha vacía o con formato inválido (use DD/MM/YYYY)';
                $errorDetails[] = "Fila {$rowNum}: " . implode(', ', $reasons);
                $errors++;
                continue;
            }

            try {
                // Buscar tipo de movimiento por ID (numérico) o por nombre (sin distinción de mayúsculas)
                if (is_numeric($movementTypeRaw)) {
                    $typeStmt = $this->db->prepare("SELECT id FROM financial_movement_types WHERE id = ? AND is_active = 1 LIMIT 1");
                    $typeStmt->execute([(int)$movementTypeRaw]);
                } else {
                    $typeStmt = $this->db->prepare("SELECT id FROM financial_movement_types WHERE name = ? AND is_active = 1 LIMIT 1");
                    $typeStmt->execute([$movementTypeRaw]);
                }
                $typeRow = $typeStmt->fetch();
                if (!$typeRow) {
                    $errors++;
                    $errorDetails[] = "Fila {$rowNum}: tipo de movimiento '{$movementTypeRaw}' no encontrado";
                    continue;
                }
                $movementTypeId = $typeRow['id'];

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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} movimientos importados, {$errors} errores",
            'error_details' => $errorDetails,
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
        $errorDetails = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < 2) continue;

            $key   = trim($row[0]);
            $value = trim($row[1]);

            if (!in_array($key, $allowedKeys)) {
                $errors++;
                $errorDetails[] = "Fila {$rowNum}: clave '{$key}' no permitida";
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} parámetros CFDI importados, {$errors} errores",
            'error_details' => $errorDetails,
        ];
    }

    /**
     * Importación Masiva – muestra el formulario y procesa el archivo Excel subido
     */
    public function bulkImport() {
        $data = [
            'title'   => 'Importación Masiva',
            'error'   => '',
            'success' => '',
            'details' => [],
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xlsx_file'])) {
            $result = $this->processBulkImport($_FILES['xlsx_file']);
            if ($result['success']) {
                $data['success'] = $result['message'];
                $data['details'] = $result['details'] ?? [];
            } else {
                $data['error'] = $result['message'];
            }
        }

        $this->view('import/bulk', $data);
    }

    /**
     * Genera y descarga la plantilla Excel multi-hoja para Importación Masiva
     */
    public function downloadBulkTemplate() {
        $writer = new XlsxWriter();

        $writer->addSheet('Residentes', [
            'username', 'email', 'first_name', 'last_name', 'phone', 'property_number', 'relationship',
        ]);
        $writer->addSheet('Propiedades', [
            'property_number', 'section', 'street', 'property_type', 'tower',
            'bedrooms', 'bathrooms', 'area_m2', 'status',
        ]);
        $writer->addSheet('Usuarios', [
            'username', 'email', 'first_name', 'last_name', 'phone', 'role',
        ]);
        $writer->addSheet('Cuotas', [
            'property_number', 'period', 'amount', 'due_date', 'status',
        ]);
        $writer->addSheet('Amenidades', [
            'name', 'amenity_type', 'description', 'capacity', 'hourly_rate',
            'hours_open', 'hours_close', 'requires_payment', 'status',
        ]);
        $writer->addSheet('Movimientos Financieros', [
            'movement_type_id', 'transaction_type', 'amount', 'description',
            'payment_method', 'transaction_date', 'property_number', 'notes',
        ]);
        $writer->addSheet('CFDI Config', ['setting_key', 'setting_value']);
        $writer->addSheet('PayPal Config', ['setting_key', 'setting_value']);

        $writer->download('plantilla_importacion_masiva.xlsx');
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
        $errorDetails = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < 2) continue;

            $key   = trim($row[0]);
            $value = trim($row[1]);

            if (!in_array($key, $allowedKeys)) {
                $errors++;
                $errorDetails[] = "Fila {$rowNum}: clave '{$key}' no permitida";
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'message' => "Importación completada: {$imported} parámetros PayPal importados, {$errors} errores",
            'error_details' => $errorDetails,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Importación Masiva – procesamiento por hoja
    // ─────────────────────────────────────────────────────────────

    /**
     * Procesa el archivo XLSX de importación masiva.
     * Lee cada hoja y la despacha al procesador correspondiente según el nombre.
     */
    private function processBulkImport($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'xlsx') {
            return ['success' => false, 'message' => 'El archivo debe ser .xlsx (Excel)'];
        }

        try {
            $reader = new XlsxReader($file['tmp_name']);
            $sheets = $reader->getSheets();
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'No se pudo leer el archivo Excel: ' . $e->getMessage()];
        }

        if (empty($sheets)) {
            return ['success' => false, 'message' => 'El archivo Excel no contiene hojas con datos'];
        }

        // Mapa nombre de hoja → método procesador (array de filas, sin cabecera)
        $sheetMap = [
            'Residentes'             => 'processBulkResidentes',
            'Propiedades'            => 'processBulkPropiedades',
            'Usuarios'               => 'processBulkUsuarios',
            'Cuotas'                 => 'processBulkCuotas',
            'Amenidades'             => 'processBulkAmenidades',
            'Movimientos Financieros'=> 'processBulkMovimientosFinancieros',
            'CFDI Config'            => 'processBulkCfdiConfig',
            'PayPal Config'          => 'processBulkPaypalConfig',
        ];

        $details     = [];
        $totalImport = 0;
        $totalErrors = 0;

        foreach ($sheets as $sheetName => $rows) {
            // La primera fila es el encabezado; los datos empiezan en la segunda
            if (count($rows) < 2) continue;

            $dataRows = array_slice($rows, 1); // excluir cabecera

            if (isset($sheetMap[$sheetName])) {
                $method = $sheetMap[$sheetName];
                $result = $this->$method($dataRows);
                $details[] = [
                    'sheet'        => $sheetName,
                    'imported'     => $result['imported'],
                    'errors'       => $result['errors'],
                    'error_details'=> $result['error_details'] ?? [],
                ];
                $totalImport += $result['imported'];
                $totalErrors += $result['errors'];
            }
        }

        return [
            'success' => true,
            'message' => "Importación masiva completada: {$totalImport} registros importados, {$totalErrors} errores.",
            'details' => $details,
        ];
    }

    private function processBulkResidentes(array $rows) {
        $imported = 0;
        $errors   = 0;
        $errorDetails = [];
        $rowNum = 1;
        foreach ($rows as $row) {
            $rowNum++;
            if (count($row) < 5) { $errors++; $errorDetails[] = "Fila {$rowNum}: datos insuficientes"; continue; }
            try {
                $this->db->beginTransaction();
                $randomPassword = bin2hex(random_bytes(16));
                $password = password_hash($randomPassword, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, phone, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'residente', 'active')
                ");
                $stmt->execute([trim($row[0]), trim($row[1]), $password, trim($row[2]), trim($row[3]), trim($row[4])]);
                $userId = $this->db->lastInsertId();

                $propertyNumber = isset($row[5]) ? trim($row[5]) : '';
                if ($propertyNumber !== '') {
                    $propStmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ? LIMIT 1");
                    $propStmt->execute([$propertyNumber]);
                    $property = $propStmt->fetch();
                    if ($property) {
                        $relationship = isset($row[6]) ? trim($row[6]) : 'propietario';
                        if (!in_array($relationship, ['propietario', 'inquilino', 'familiar'])) $relationship = 'propietario';
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }
        return ['imported' => $imported, 'errors' => $errors, 'error_details' => $errorDetails];
    }

    private function processBulkPropiedades(array $rows) {
        $imported = 0;
        $errors   = 0;
        $errorDetails = [];
        $rowNum = 1;
        $allowedTypes  = ['casa', 'departamento', 'lote'];
        $allowedStatus = ['ocupada', 'desocupada', 'en_construccion'];
        foreach ($rows as $row) {
            $rowNum++;
            if (count($row) < 3) { $errors++; $errorDetails[] = "Fila {$rowNum}: datos insuficientes"; continue; }
            $propertyNumber = trim($row[0]);
            $section        = trim($row[1]);
            $street         = trim($row[2]);
            $propertyType   = isset($row[3]) && in_array(trim($row[3]), $allowedTypes) ? trim($row[3]) : 'casa';
            $tower          = isset($row[4]) && trim($row[4]) !== '' ? trim($row[4]) : null;
            $bedrooms       = isset($row[5]) && is_numeric($row[5]) ? (int)$row[5] : 0;
            $bathrooms      = isset($row[6]) && is_numeric($row[6]) ? (int)$row[6] : 0;
            $areaM2         = isset($row[7]) && is_numeric($row[7]) ? (float)$row[7] : null;
            $status         = isset($row[8]) && in_array(trim($row[8]), $allowedStatus) ? trim($row[8]) : 'desocupada';
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO properties
                        (property_number, section, street, property_type, tower, bedrooms, bathrooms, area_m2, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        section=VALUES(section), street=VALUES(street), property_type=VALUES(property_type),
                        tower=VALUES(tower), bedrooms=VALUES(bedrooms), bathrooms=VALUES(bathrooms),
                        area_m2=VALUES(area_m2), status=VALUES(status)
                ");
                $stmt->execute([$propertyNumber, $section, $street, $propertyType, $tower, $bedrooms, $bathrooms, $areaM2, $status]);
                $imported++;
            } catch (Exception $e) {
                $errors++;
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }
        return ['imported' => $imported, 'errors' => $errors, 'error_details' => $errorDetails];
    }

    private function processBulkUsuarios(array $rows) {
        $imported     = 0;
        $errors       = 0;
        $errorDetails = [];
        $rowNum = 1;
        $allowedRoles = ['superadmin', 'administrador', 'guardia', 'residente'];
        foreach ($rows as $row) {
            $rowNum++;
            if (count($row) < 5) { $errors++; $errorDetails[] = "Fila {$rowNum}: datos insuficientes"; continue; }
            $role = isset($row[5]) && in_array(trim($row[5]), $allowedRoles) ? trim($row[5]) : 'residente';
            try {
                $randomPassword = bin2hex(random_bytes(16));
                $password = password_hash($randomPassword, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, phone, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                $stmt->execute([trim($row[0]), trim($row[1]), $password, trim($row[2]), trim($row[3]), trim($row[4]), $role]);
                $imported++;
            } catch (Exception $e) {
                $errors++;
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }
        return ['imported' => $imported, 'errors' => $errors, 'error_details' => $errorDetails];
    }

    private function processBulkCuotas(array $rows) {
        $imported      = 0;
        $errors        = 0;
        $errorDetails  = [];
        $rowNum = 1;
        $allowedStatus = ['pending', 'paid', 'overdue', 'cancelled'];
        foreach ($rows as $row) {
            $rowNum++;
            if (count($row) < 4) { $errors++; $errorDetails[] = "Fila {$rowNum}: datos insuficientes"; continue; }
            $propertyNumber = trim($row[0]);
            $period         = trim($row[1]);
            $amount         = is_numeric($row[2]) ? (float)$row[2] : null;
            $dueDate        = trim($row[3]);
            $status         = isset($row[4]) && in_array(trim($row[4]), $allowedStatus) ? trim($row[4]) : 'pending';
            if ($amount === null || $propertyNumber === '' || $period === '' || $dueDate === '') {
                $reasons = [];
                if ($propertyNumber === '') $reasons[] = 'número de propiedad vacío';
                if ($period === '') $reasons[] = 'período vacío';
                if ($amount === null) $reasons[] = 'monto inválido';
                if ($dueDate === '') $reasons[] = 'fecha de vencimiento vacía';
                $errorDetails[] = "Fila {$rowNum}: " . implode(', ', $reasons);
                $errors++;
                continue;
            }
            try {
                $propStmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ? LIMIT 1");
                $propStmt->execute([$propertyNumber]);
                $property = $propStmt->fetch();
                if (!$property) {
                    $errors++;
                    $errorDetails[] = "Fila {$rowNum}: propiedad '{$propertyNumber}' no encontrada";
                    continue;
                }
                $stmt = $this->db->prepare("
                    INSERT INTO maintenance_fees (property_id, period, amount, due_date, status)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE amount=VALUES(amount), due_date=VALUES(due_date), status=VALUES(status)
                ");
                $stmt->execute([$property['id'], $period, $amount, $dueDate, $status]);
                $imported++;
            } catch (Exception $e) {
                $errors++;
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }
        return ['imported' => $imported, 'errors' => $errors, 'error_details' => $errorDetails];
    }

    private function processBulkAmenidades(array $rows) {
        $imported      = 0;
        $errors        = 0;
        $errorDetails  = [];
        $rowNum = 1;
        $allowedTypes  = ['salon', 'alberca', 'asadores', 'cancha', 'gimnasio', 'otro'];
        $allowedStatus = ['active', 'maintenance', 'inactive'];
        foreach ($rows as $row) {
            $rowNum++;
            if (count($row) < 1) { $errors++; $errorDetails[] = "Fila {$rowNum}: datos insuficientes"; continue; }
            $name            = trim($row[0]);
            if ($name === '') { $errors++; $errorDetails[] = "Fila {$rowNum}: nombre de amenidad vacío"; continue; }
            $amenityType     = isset($row[1]) && in_array(trim($row[1]), $allowedTypes) ? trim($row[1]) : 'otro';
            $description     = isset($row[2]) && trim($row[2]) !== '' ? trim($row[2]) : null;
            $capacity        = isset($row[3]) && is_numeric($row[3]) ? (int)$row[3] : 0;
            $hourlyRate      = isset($row[4]) && is_numeric($row[4]) ? (float)$row[4] : 0.00;
            $hoursOpen       = isset($row[5]) && trim($row[5]) !== '' ? trim($row[5]) : null;
            $hoursClose      = isset($row[6]) && trim($row[6]) !== '' ? trim($row[6]) : null;
            $requiresPayment = isset($row[7]) ? (int)(bool)$row[7] : 0;
            $status          = isset($row[8]) && in_array(trim($row[8]), $allowedStatus) ? trim($row[8]) : 'active';
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO amenities
                        (name, amenity_type, description, capacity, hourly_rate,
                         hours_open, hours_close, requires_payment, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $amenityType, $description, $capacity, $hourlyRate, $hoursOpen, $hoursClose, $requiresPayment, $status]);
                $imported++;
            } catch (Exception $e) {
                $errors++;
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }
        return ['imported' => $imported, 'errors' => $errors, 'error_details' => $errorDetails];
    }

    private function processBulkMovimientosFinancieros(array $rows) {
        $imported          = 0;
        $errors            = 0;
        $errorDetails      = [];
        $rowNum            = 1;
        $allowedTransTypes = ['ingreso', 'egreso'];
        $allowedPayMethods = ['efectivo', 'tarjeta', 'transferencia', 'paypal', 'otro'];
        $currentUser       = $this->getCurrentUser();
        $createdBy         = $currentUser['id'];
        foreach ($rows as $row) {
            $rowNum++;
            if (count($row) < 6) { $errors++; $errorDetails[] = "Fila {$rowNum}: datos insuficientes"; continue; }
            $movementTypeRaw = trim($row[0] ?? '');
            $transactionRaw  = strtolower(trim($row[1]));
            $transactionType = in_array($transactionRaw, $allowedTransTypes) ? $transactionRaw : null;
            $amount          = is_numeric($row[2]) ? (float)$row[2] : null;
            $description     = trim($row[3]);
            $paymentRaw      = isset($row[4]) ? strtolower(trim($row[4])) : '';
            $paymentMethod   = in_array($paymentRaw, $allowedPayMethods) ? $paymentRaw : null;
            $transactionDate = $this->parseImportDate($row[5] ?? '');
            $propertyNumber  = isset($row[6]) ? trim($row[6]) : '';
            $notes           = isset($row[7]) && trim($row[7]) !== '' ? trim($row[7]) : null;
            if ($movementTypeRaw === '' || $transactionType === null || $amount === null
                || $description === '' || $transactionDate === null) {
                $reasons = [];
                if ($movementTypeRaw === '') $reasons[] = 'tipo de movimiento vacío';
                if ($transactionType === null) $reasons[] = 'tipo de transacción inválido (use: ingreso, egreso)';
                if ($amount === null) $reasons[] = 'monto inválido';
                if ($description === '') $reasons[] = 'descripción vacía';
                if ($transactionDate === null) $reasons[] = 'fecha vacía o con formato inválido (use DD/MM/YYYY)';
                $errorDetails[] = "Fila {$rowNum}: " . implode(', ', $reasons);
                $errors++;
                continue;
            }
            try {
                // Buscar tipo de movimiento por ID (numérico) o por nombre (sin distinción de mayúsculas)
                if (is_numeric($movementTypeRaw)) {
                    $typeStmt = $this->db->prepare("SELECT id FROM financial_movement_types WHERE id = ? AND is_active = 1 LIMIT 1");
                    $typeStmt->execute([(int)$movementTypeRaw]);
                } else {
                    $typeStmt = $this->db->prepare("SELECT id FROM financial_movement_types WHERE name = ? AND is_active = 1 LIMIT 1");
                    $typeStmt->execute([$movementTypeRaw]);
                }
                $typeRow = $typeStmt->fetch();
                if (!$typeRow) {
                    $errors++;
                    $errorDetails[] = "Fila {$rowNum}: tipo de movimiento '{$movementTypeRaw}' no encontrado";
                    continue;
                }
                $movementTypeId = $typeRow['id'];
                $propertyId = null;
                if ($propertyNumber !== '') {
                    $propStmt = $this->db->prepare("SELECT id FROM properties WHERE property_number = ? LIMIT 1");
                    $propStmt->execute([$propertyNumber]);
                    $property = $propStmt->fetch();
                    if ($property) $propertyId = $property['id'];
                }
                $stmt = $this->db->prepare("
                    INSERT INTO financial_movements
                        (movement_type_id, transaction_type, amount, description,
                         payment_method, transaction_date, property_id, notes, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$movementTypeId, $transactionType, $amount, $description,
                    $paymentMethod, $transactionDate, $propertyId, $notes, $createdBy]);
                $imported++;
            } catch (Exception $e) {
                $errors++;
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }
        return ['imported' => $imported, 'errors' => $errors, 'error_details' => $errorDetails];
    }

    private function processBulkCfdiConfig(array $rows) {
        $imported    = 0;
        $errors      = 0;
        $errorDetails = [];
        $rowNum = 1;
        $allowedKeys = [
            'cfdi_rfc', 'cfdi_razon_social', 'cfdi_regimen_fiscal',
            'cfdi_cp', 'cfdi_uso_cfdi', 'cfdi_metodo_pago',
            'cfdi_forma_pago', 'cfdi_serie', 'cfdi_folio_inicio',
        ];
        foreach ($rows as $row) {
            $rowNum++;
            if (count($row) < 2) { $errors++; $errorDetails[] = "Fila {$rowNum}: datos insuficientes"; continue; }
            $key   = trim($row[0]);
            $value = trim($row[1]);
            if (!in_array($key, $allowedKeys)) { $errors++; $errorDetails[] = "Fila {$rowNum}: clave '{$key}' no permitida"; continue; }
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }
        return ['imported' => $imported, 'errors' => $errors, 'error_details' => $errorDetails];
    }

    private function processBulkPaypalConfig(array $rows) {
        $imported    = 0;
        $errors      = 0;
        $errorDetails = [];
        $rowNum = 1;
        $allowedKeys = ['paypal_enabled', 'paypal_mode', 'paypal_client_id', 'paypal_secret'];
        foreach ($rows as $row) {
            $rowNum++;
            if (count($row) < 2) { $errors++; $errorDetails[] = "Fila {$rowNum}: datos insuficientes"; continue; }
            $key   = trim($row[0]);
            $value = trim($row[1]);
            if (!in_array($key, $allowedKeys)) { $errors++; $errorDetails[] = "Fila {$rowNum}: clave '{$key}' no permitida"; continue; }
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
                $errorDetails[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }
        return ['imported' => $imported, 'errors' => $errors, 'error_details' => $errorDetails];
    }

    /**
     * Convierte una fecha de importación al formato MySQL (YYYY-MM-DD).
     * Soporta:
     *   - DD/MM/YYYY  (ej. 26/03/2026)
     *   - DD-MM-YYYY  (ej. 26-03-2026)
     *   - YYYY-MM-DD  (ya en formato MySQL)
     *   - Número de serie de Excel (días desde 1900-01-01)
     *
     * @param  string $value  Valor crudo leído del archivo de importación.
     * @return string|null    Fecha en formato YYYY-MM-DD, o null si no se puede parsear.
     */
    private function parseImportDate($value) {
        $value = trim($value);
        if ($value === '') return null;

        // Número de serie de Excel (solo dígitos, posiblemente con decimales de tiempo)
        if (is_numeric($value) && !str_contains($value, '/') && !str_contains($value, '-')) {
            $serial = (int)$value;
            // Excel epoch: 1899-12-30 (ajustado por el bug del año bisiesto 1900 de Excel)
            $unixTimestamp = ($serial - 25569) * 86400;
            $date = DateTime::createFromFormat('U', (string)$unixTimestamp);
            if ($date !== false) return $date->format('Y-m-d');
            return null;
        }

        // Intentar formatos comunes en orden de prioridad
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'];
        foreach ($formats as $fmt) {
            $date = DateTime::createFromFormat($fmt, $value);
            if ($date !== false && $date->format($fmt) === $value) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }
}
