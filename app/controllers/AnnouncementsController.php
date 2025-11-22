<?php
/**
 * Controlador de Comunicados
 */

class AnnouncementsController extends Controller {
    
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de comunicados
     */
    public function index() {
        $role = $_SESSION['role'];
        
        if (in_array($role, ['superadmin', 'administrador'])) {
            // Administradores ven todos los comunicados
            $stmt = $this->db->query("
                SELECT a.*, u.first_name, u.last_name
                FROM announcements a
                JOIN users u ON a.created_by = u.id
                ORDER BY a.created_at DESC
            ");
        } else {
            // Residentes solo ven comunicados enviados
            $stmt = $this->db->query("
                SELECT a.*, u.first_name, u.last_name
                FROM announcements a
                JOIN users u ON a.created_by = u.id
                WHERE a.status = 'sent'
                ORDER BY a.created_at DESC
            ");
        }
        
        $announcements = $stmt->fetchAll();
        
        $data = [
            'title' => 'Comunicados',
            'announcements' => $announcements
        ];
        
        $this->view('announcements/index', $data);
    }
    
    /**
     * Crear nuevo comunicado
     */
    public function create() {
        $this->requireRole(['superadmin', 'administrador']);
        
        $data = [
            'title' => 'Nuevo Comunicado',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sql = "INSERT INTO announcements (created_by, title, content, priority, 
                    target_audience, status, sent_at) 
                    VALUES (?, ?, ?, ?, ?, 'sent', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $_SESSION['user_id'],
                $this->post('title'),
                $this->post('content'),
                $this->post('priority', 'normal'),
                $this->post('target_audience', 'all')
            ]);
            
            $_SESSION['success_message'] = 'Comunicado publicado exitosamente';
            $this->redirect('announcements');
        }
        
        $this->view('announcements/create', $data);
    }
    
    /**
     * Ver detalle de comunicado
     */
    public function viewDetails($id) {
        $stmt = $this->db->prepare("
            SELECT a.*, u.first_name, u.last_name
            FROM announcements a
            JOIN users u ON a.created_by = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $announcement = $stmt->fetch();
        
        if (!$announcement) {
            $_SESSION['error_message'] = 'Comunicado no encontrado';
            $this->redirect('announcements');
        }
        
        $data = [
            'title' => 'Detalle del Comunicado',
            'announcement' => $announcement
        ];
        
        $this->view('announcements/view', $data);
    }
}
