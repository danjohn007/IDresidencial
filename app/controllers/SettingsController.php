<?php
/**
 * Controlador de Configuración del Sistema
 */

class SettingsController extends Controller {
    
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador']);
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de configuración
     */
    public function index() {
        // Obtener todas las configuraciones
        $stmt = $this->db->query("SELECT * FROM system_settings ORDER BY setting_key");
        $settings = $stmt->fetchAll();
        
        // Organizar por categorías
        $settingsByCategory = [
            'general' => [],
            'email' => [],
            'payment' => [],
            'theme' => [],
            'other' => []
        ];
        
        foreach ($settings as $setting) {
            if (strpos($setting['setting_key'], 'site_') === 0) {
                $settingsByCategory['general'][] = $setting;
            } elseif (strpos($setting['setting_key'], 'email_') === 0 || strpos($setting['setting_key'], 'smtp_') === 0) {
                $settingsByCategory['email'][] = $setting;
            } elseif (strpos($setting['setting_key'], 'paypal_') === 0 || strpos($setting['setting_key'], 'payment_') === 0) {
                $settingsByCategory['payment'][] = $setting;
            } elseif (strpos($setting['setting_key'], 'theme_') === 0) {
                $settingsByCategory['theme'][] = $setting;
            } else {
                $settingsByCategory['other'][] = $setting;
            }
        }
        
        $data = [
            'title' => 'Configuración del Sistema',
            'settings' => $settingsByCategory
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSettings();
            $_SESSION['success_message'] = 'Configuración actualizada exitosamente';
            $this->redirect('settings');
        }
        
        $this->view('settings/index', $data);
    }
    
    /**
     * Actualizar configuraciones
     */
    private function updateSettings() {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = str_replace('setting_', '', $key);
                
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$settingKey, $value, $value]);
            }
        }
    }
    
    /**
     * Configuración general
     */
    public function general() {
        $data = [
            'title' => 'Configuración General'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = [
                'site_name' => $this->post('site_name'),
                'site_email' => $this->post('site_email'),
                'site_phone' => $this->post('site_phone'),
                'maintenance_fee_default' => $this->post('maintenance_fee_default')
            ];
            
            // Handle logo upload
            if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = PUBLIC_PATH . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
                $filename = 'logo_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $uploadPath)) {
                    $settings['site_logo'] = 'uploads/' . $filename;
                }
            }
            
            foreach ($settings as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            $_SESSION['success_message'] = 'Configuración general actualizada';
            $this->redirect('settings/general');
        }
        
        // Obtener configuración actual
        $stmt = $this->db->query("SELECT * FROM system_settings WHERE setting_key LIKE 'site_%' OR setting_key = 'maintenance_fee_default'");
        $currentSettings = [];
        while ($row = $stmt->fetch()) {
            $currentSettings[$row['setting_key']] = $row['setting_value'];
        }
        
        $data['current'] = $currentSettings;
        $this->view('settings/general', $data);
    }
    
    /**
     * Configuración de colores/tema
     */
    public function theme() {
        $data = [
            'title' => 'Personalización de Tema'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $themeColor = $this->post('theme_color', 'blue');
            
            $stmt = $this->db->prepare("
                INSERT INTO system_settings (setting_key, setting_value) 
                VALUES ('theme_color', ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            $stmt->execute([$themeColor, $themeColor]);
            
            $_SESSION['success_message'] = 'Tema actualizado exitosamente';
            $this->redirect('settings/theme');
        }
        
        $this->view('settings/theme', $data);
    }
    
    /**
     * Configuración de correo
     */
    public function email() {
        $data = [
            'title' => 'Configuración de Correo'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $emailSettings = [
                'email_host' => $this->post('email_host'),
                'email_port' => $this->post('email_port'),
                'email_user' => $this->post('email_user'),
                'email_password' => $this->post('email_password'),
                'email_from' => $this->post('email_from')
            ];
            
            foreach ($emailSettings as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            $_SESSION['success_message'] = 'Configuración de correo actualizada';
            $this->redirect('settings/email');
        }
        
        $this->view('settings/email', $data);
    }
    
    /**
     * Configuración de PayPal
     */
    public function payment() {
        $data = [
            'title' => 'Configuración de Pagos'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentSettings = [
                'paypal_enabled' => $this->post('paypal_enabled', '0'),
                'paypal_mode' => $this->post('paypal_mode', 'sandbox'),
                'paypal_client_id' => $this->post('paypal_client_id'),
                'paypal_secret' => $this->post('paypal_secret')
            ];
            
            foreach ($paymentSettings as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            $_SESSION['success_message'] = 'Configuración de pagos actualizada';
            $this->redirect('settings/payment');
        }
        
        $this->view('settings/payment', $data);
    }
    
    /**
     * Configuración de horarios
     */
    public function hours() {
        $data = [
            'title' => 'Configuración de Horarios'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hoursSettings = [
                'hours_office_weekday' => $this->post('hours_office_weekday'),
                'hours_office_weekend' => $this->post('hours_office_weekend'),
                'hours_amenities_weekday' => $this->post('hours_amenities_weekday'),
                'hours_amenities_weekend' => $this->post('hours_amenities_weekend'),
                'hours_guard_24_7' => $this->post('hours_guard_24_7', '1')
            ];
            
            foreach ($hoursSettings as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            $_SESSION['success_message'] = 'Configuración de horarios actualizada';
            $this->redirect('settings/hours');
        }
        
        // Obtener configuración actual
        $stmt = $this->db->query("SELECT * FROM system_settings WHERE setting_key LIKE 'hours_%'");
        $currentSettings = [];
        while ($row = $stmt->fetch()) {
            $currentSettings[$row['setting_key']] = $row['setting_value'];
        }
        
        $data['current'] = $currentSettings;
        $this->view('settings/hours', $data);
    }
    
    /**
     * Configuración de QR
     */
    public function qr() {
        $data = [
            'title' => 'Configuración de Códigos QR'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $qrSettings = [
                'qr_enabled' => $this->post('qr_enabled', '0'),
                'qr_expiration_hours' => $this->post('qr_expiration_hours', '24'),
                'qr_api_key' => $this->post('qr_api_key'),
                'qr_logo_enabled' => $this->post('qr_logo_enabled', '1')
            ];
            
            foreach ($qrSettings as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            $_SESSION['success_message'] = 'Configuración de QR actualizada';
            $this->redirect('settings/qr');
        }
        
        // Obtener configuración actual
        $stmt = $this->db->query("SELECT * FROM system_settings WHERE setting_key LIKE 'qr_%'");
        $currentSettings = [];
        while ($row = $stmt->fetch()) {
            $currentSettings[$row['setting_key']] = $row['setting_value'];
        }
        
        $data['current'] = $currentSettings;
        $this->view('settings/qr', $data);
    }
    
    /**
     * Obtener configuración por clave
     */
    public static function getSetting($key, $default = null) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : $default;
    }
}
