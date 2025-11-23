<?php
/**
 * Controlador de Amenidades
 */

class AmenitiesController extends Controller {
    
    private $amenityModel;
    private $reservationModel;
    private $residentModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->amenityModel = $this->model('Amenity');
        $this->reservationModel = $this->model('Reservation');
        $this->residentModel = $this->model('Resident');
    }
    
    /**
     * Vista principal de amenidades
     */
    public function index() {
        $amenities = $this->amenityModel->getAll('active');
        
        $data = [
            'title' => 'Amenidades',
            'amenities' => $amenities
        ];
        
        $this->view('amenities/index', $data);
    }
    
    /**
     * Crear reservación
     */
    public function reserve($amenityId = null) {
        if (!$amenityId) {
            $this->redirect('amenities');
        }
        
        $amenity = $this->amenityModel->findById($amenityId);
        if (!$amenity) {
            $_SESSION['error_message'] = 'Amenidad no encontrada';
            $this->redirect('amenities');
        }
        
        $data = [
            'title' => 'Reservar ' . $amenity['name'],
            'amenity' => $amenity,
            'error' => '',
            'success' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
            
            if (!$resident) {
                $data['error'] = 'No se encontró información de residente';
            } else {
                $reservationData = [
                    'amenity_id' => $amenityId,
                    'resident_id' => $resident['id'],
                    'reservation_date' => $this->post('reservation_date'),
                    'start_time' => $this->post('start_time'),
                    'end_time' => $this->post('end_time'),
                    'guests_count' => $this->post('guests_count', 0),
                    'amount' => $amenity['hourly_rate'],
                    'payment_status' => 'pending',
                    'status' => 'pending',
                    'notes' => $this->post('notes')
                ];
                
                // Verificar disponibilidad
                if (!$this->reservationModel->checkAvailability(
                    $amenityId, 
                    $reservationData['reservation_date'],
                    $reservationData['start_time'],
                    $reservationData['end_time']
                )) {
                    $data['error'] = 'El horario seleccionado no está disponible';
                } else {
                    if ($this->reservationModel->create($reservationData)) {
                        $_SESSION['success_message'] = 'Reservación creada exitosamente';
                        $this->redirect('amenities/myReservations');
                    } else {
                        $data['error'] = 'Error al crear la reservación';
                    }
                }
            }
        }
        
        $this->view('amenities/reserve', $data);
    }
    
    /**
     * Mis reservaciones
     */
    public function myReservations() {
        $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
        
        if (!$resident) {
            $_SESSION['error_message'] = 'No se encontró información de residente';
            $this->redirect('dashboard');
        }
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT r.*, a.name as amenity_name
            FROM reservations r
            JOIN amenities a ON r.amenity_id = a.id
            WHERE r.resident_id = ?
            ORDER BY r.reservation_date DESC, r.start_time DESC
        ");
        $stmt->execute([$resident['id']]);
        $reservations = $stmt->fetchAll();
        
        $data = [
            'title' => 'Mis Reservaciones',
            'reservations' => $reservations
        ];
        
        $this->view('amenities/my_reservations', $data);
    }
    
    /**
     * Cancelar reservación
     */
    public function cancel($id) {
        $reservation = $this->reservationModel->findById($id);
        
        if (!$reservation) {
            $_SESSION['error_message'] = 'Reservación no encontrada';
            $this->redirect('amenities/myReservations');
        }
        
        // Verificar que sea del usuario o sea admin
        $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
        if ($resident && $reservation['resident_id'] == $resident['id'] || in_array($_SESSION['role'], ['superadmin', 'administrador'])) {
            if ($this->reservationModel->updateStatus($id, 'cancelled')) {
                $_SESSION['success_message'] = 'Reservación cancelada exitosamente';
            } else {
                $_SESSION['error_message'] = 'Error al cancelar la reservación';
            }
        } else {
            $_SESSION['error_message'] = 'No tienes permiso para cancelar esta reservación';
        }
        
        $this->redirect('amenities/myReservations');
    }
    
    /**
     * Gestionar amenidades (admin)
     */
    public function manage() {
        $this->requireRole(['superadmin', 'administrador']);
        
        $amenities = $this->amenityModel->getAll(null);
        
        $data = [
            'title' => 'Gestionar Amenidades',
            'amenities' => $amenities
        ];
        
        $this->view('amenities/manage', $data);
    }
    
    /**
     * Crear nueva amenidad
     */
    public function create() {
        $this->requireRole(['superadmin']);
        
        $data = [
            'title' => 'Nueva Amenidad',
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amenityData = [
                'name' => $this->post('name'),
                'description' => $this->post('description'),
                'amenity_type' => $this->post('amenity_type'),
                'capacity' => $this->post('capacity'),
                'hours_open' => $this->post('hours_open'),
                'hours_close' => $this->post('hours_close'),
                'requires_payment' => $this->post('requires_payment', 0),
                'hourly_rate' => $this->post('hourly_rate', 0),
                'status' => 'active'
            ];
            
            if ($this->amenityModel->create($amenityData)) {
                $_SESSION['success_message'] = 'Amenidad creada exitosamente';
                $this->redirect('amenities/manage');
            } else {
                $data['error'] = 'Error al crear la amenidad';
            }
        }
        
        $this->view('amenities/create', $data);
    }
    
    /**
     * Editar amenidad
     */
    public function edit($id) {
        $this->requireRole(['superadmin']);
        
        $amenity = $this->amenityModel->findById($id);
        
        if (!$amenity) {
            $_SESSION['error_message'] = 'Amenidad no encontrada';
            $this->redirect('amenities/manage');
        }
        
        $data = [
            'title' => 'Editar Amenidad',
            'amenity' => $amenity,
            'error' => ''
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amenityData = [
                'name' => $this->post('name'),
                'description' => $this->post('description'),
                'amenity_type' => $this->post('amenity_type'),
                'capacity' => $this->post('capacity'),
                'hours_open' => $this->post('hours_open'),
                'hours_close' => $this->post('hours_close'),
                'requires_payment' => $this->post('requires_payment', 0),
                'hourly_rate' => $this->post('hourly_rate', 0)
            ];
            
            if ($this->amenityModel->update($id, $amenityData)) {
                $_SESSION['success_message'] = 'Amenidad actualizada exitosamente';
                $this->redirect('amenities/manage');
            } else {
                $data['error'] = 'Error al actualizar la amenidad';
            }
        }
        
        $this->view('amenities/edit', $data);
    }
    
    /**
     * Cambiar estado de amenidad
     */
    public function toggleStatus($id) {
        $this->requireRole(['superadmin']);
        
        $amenity = $this->amenityModel->findById($id);
        
        if (!$amenity) {
            $_SESSION['error_message'] = 'Amenidad no encontrada';
            $this->redirect('amenities/manage');
        }
        
        $newStatus = $amenity['status'] === 'active' ? 'inactive' : 'active';
        
        if ($this->amenityModel->update($id, ['status' => $newStatus])) {
            $_SESSION['success_message'] = 'Estado de la amenidad actualizado';
        } else {
            $_SESSION['error_message'] = 'Error al actualizar el estado';
        }
        
        $this->redirect('amenities/manage');
    }
}
