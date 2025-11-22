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
     * Importar propiedades desde CSV
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
     * Procesar CSV de residentes
     */
    private function processResidentsCSV($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo'];
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'csv') {
            return ['success' => false, 'message' => 'El archivo debe ser CSV'];
        }
        
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }
        
        // Leer encabezados
        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 5) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido'];
        }
        
        $imported = 0;
        $errors = 0;
        
        // Procesar cada fila
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;
            
            try {
                $this->db->beginTransaction();
                
                // Crear usuario
                $stmt = $this->db->prepare("
                    INSERT INTO users (username, email, password, first_name, last_name, phone, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'residente', 'active')
                ");
                $password = password_hash('default123', PASSWORD_DEFAULT);
                $stmt->execute([$row[0], $row[1], $password, $row[2], $row[3], $row[4]]);
                
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
     * Procesar CSV de propiedades
     */
    private function processPropertiesCSV($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo'];
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'csv') {
            return ['success' => false, 'message' => 'El archivo debe ser CSV'];
        }
        
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo'];
        }
        
        // Leer encabezados
        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 3) {
            fclose($handle);
            return ['success' => false, 'message' => 'Formato de CSV inválido'];
        }
        
        $imported = 0;
        $errors = 0;
        
        // Procesar cada fila
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) continue;
            
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO properties (property_number, section, street, property_type, status)
                    VALUES (?, ?, ?, ?, 'desocupada')
                    ON DUPLICATE KEY UPDATE section = VALUES(section), street = VALUES(street)
                ");
                $stmt->execute([$row[0], $row[1], $row[2], $row[3] ?? 'casa']);
                
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
}
