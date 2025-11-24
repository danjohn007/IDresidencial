<?php
/**
 * Controlador de Soporte Técnico (Vista Pública)
 */

class SupportController extends Controller {
    
    private $db;
    
    public function __construct() {
        // No requiere autenticación - es público
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista pública de soporte técnico
     */
    public function index() {
        // Obtener configuración de soporte
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'support_%' OR setting_key = 'site_name'");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        $data = [
            'title' => 'Soporte Técnico',
            'settings' => $settings
        ];
        
        // Render public support view
        $this->view('support/index', $data);
    }
}
