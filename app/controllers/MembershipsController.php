<?php
/**
 * Controlador de Membresías
 */

class MembershipsController extends Controller {
    
    private $membershipModel;
    private $db;
    
    public function __construct() {
        $this->requireAuth();
        $this->requireRole(['superadmin', 'administrador']);
        $this->membershipModel = $this->model('Membership');
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Vista principal de membresías
     */
    public function index() {
        $filters = [
            'status' => $this->get('status'),
            'resident_id' => $this->get('resident_id')
        ];
        
        $memberships = $this->membershipModel->getAll($filters);
        $stats = $this->membershipModel->getStats();
        
        $data = [
            'title' => 'Membresías',
            'memberships' => $memberships,
            'stats' => $stats,
            'filters' => $filters
        ];
        
        $this->view('memberships/index', $data);
    }
    
    /**
     * Planes de membresía
     */
    public function plans() {
        $plans = $this->membershipModel->getPlans(false);
        
        $data = [
            'title' => 'Planes de Membresía',
            'plans' => $plans
        ];
        
        $this->view('memberships/plans', $data);
    }
    
    /**
     * Crear nueva membresía
     */
    public function create() {
        $plans = $this->membershipModel->getPlans();
        
        // Obtener residentes
        $stmt = $this->db->query("
            SELECT r.id, CONCAT(u.first_name, ' ', u.last_name) as name, p.property_number
            FROM residents r
            INNER JOIN users u ON r.user_id = u.id
            INNER JOIN properties p ON r.property_id = p.id
            WHERE r.status = 'active'
            ORDER BY u.first_name, u.last_name
        ");
        $residents = $stmt->fetchAll();
        
        $data = [
            'title' => 'Nueva Membresía',
            'plans' => $plans,
            'residents' => $residents,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $membershipData = [
                'resident_id' => $this->post('resident_id'),
                'membership_plan_id' => $this->post('membership_plan_id'),
                'start_date' => $this->post('start_date'),
                'end_date' => $this->post('end_date') ?: null,
                'status' => $this->post('status', 'active'),
                'payment_day' => $this->post('payment_day', 1),
                'notes' => $this->post('notes') ?: null
            ];
            
            if ($this->membershipModel->create($membershipData)) {
                $_SESSION['success_message'] = 'Membresía creada exitosamente';
                $this->redirect('memberships');
            } else {
                $data['error'] = 'Error al crear la membresía';
            }
        }
        
        $this->view('memberships/create', $data);
    }
    
    /**
     * Ver detalle de membresía
     */
    public function viewDetails($id) {
        $membership = $this->membershipModel->findById($id);
        
        if (!$membership) {
            $_SESSION['error_message'] = 'Membresía no encontrada';
            $this->redirect('memberships');
        }
        
        $payments = $this->membershipModel->getPayments($id);
        
        $data = [
            'title' => 'Detalle de Membresía',
            'membership' => $membership,
            'payments' => $payments
        ];
        
        $this->view('memberships/view', $data);
    }
    
    /**
     * Editar membresía
     */
    public function edit($id) {
        $membership = $this->membershipModel->findById($id);
        
        if (!$membership) {
            $_SESSION['error_message'] = 'Membresía no encontrada';
            $this->redirect('memberships');
        }
        
        $plans = $this->membershipModel->getPlans();
        
        $data = [
            'title' => 'Editar Membresía',
            'membership' => $membership,
            'plans' => $plans,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $membershipData = [
                'membership_plan_id' => $this->post('membership_plan_id'),
                'end_date' => $this->post('end_date') ?: null,
                'status' => $this->post('status'),
                'payment_day' => $this->post('payment_day'),
                'notes' => $this->post('notes') ?: null
            ];
            
            if ($this->membershipModel->update($id, $membershipData)) {
                $_SESSION['success_message'] = 'Membresía actualizada exitosamente';
                $this->redirect('memberships');
            } else {
                $data['error'] = 'Error al actualizar la membresía';
            }
        }
        
        $this->view('memberships/edit', $data);
    }
}
