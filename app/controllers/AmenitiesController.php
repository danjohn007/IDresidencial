<?php
/**
 * Controlador de Amenidades
 */

require_once APP_PATH . '/controllers/AuditController.php';

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
                        AuditController::log('create', 'Reservación de amenidad creada: ' . $amenity['name'], 'reservations', null);
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
        $db = Database::getInstance()->getConnection();
        
        // For residents, show only their reservations
        // For superadmin/admin, show all reservations
        if (in_array($_SESSION['role'], ['superadmin', 'administrador'])) {
            $stmt = $db->query("
                SELECT r.*, a.name as amenity_name, 
                       u.first_name, u.last_name, p.property_number
                FROM reservations r
                JOIN amenities a ON r.amenity_id = a.id
                JOIN residents res ON r.resident_id = res.id
                JOIN users u ON res.user_id = u.id
                LEFT JOIN properties p ON res.property_id = p.id
                ORDER BY r.reservation_date DESC, r.start_time DESC
            ");
            $reservations = $stmt->fetchAll();
        } else {
            $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
            
            if (!$resident) {
                $_SESSION['error_message'] = 'No se encontró información de residente';
                $this->redirect('dashboard');
                return;
            }
            
            $stmt = $db->prepare("
                SELECT r.*, a.name as amenity_name
                FROM reservations r
                JOIN amenities a ON r.amenity_id = a.id
                WHERE r.resident_id = ?
                ORDER BY r.reservation_date DESC, r.start_time DESC
            ");
            $stmt->execute([$resident['id']]);
            $reservations = $stmt->fetchAll();
        }
        
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
                AuditController::log('update', 'Reservación cancelada ID: ' . $id, 'reservations', $id);
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
    
    /**
     * Calendario global de reservaciones
     */
    public function calendar() {
        $db = Database::getInstance()->getConnection();
        
        // Get all amenities
        $amenities = $this->amenityModel->getAll('active');
        
        // Get date range (current month by default)
        $currentDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        $startDate = date('Y-m-01', strtotime($currentDate));
        $endDate = date('Y-m-t', strtotime($currentDate));
        
        // Get all reservations for the date range
        $stmt = $db->prepare("
            SELECT r.*, a.name as amenity_name, a.amenity_type,
                   u.first_name, u.last_name, p.property_number
            FROM reservations r
            JOIN amenities a ON r.amenity_id = a.id
            JOIN residents res ON r.resident_id = res.id
            JOIN users u ON res.user_id = u.id
            LEFT JOIN properties p ON res.property_id = p.id
            WHERE r.reservation_date BETWEEN ? AND ?
            AND r.status NOT IN ('cancelled')
            ORDER BY r.reservation_date, r.start_time
        ");
        $stmt->execute([$startDate, $endDate]);
        $reservations = $stmt->fetchAll();
        
        // Check if user is resident
        $isResident = ($_SESSION['role'] === 'residente');
        $residentReservationsToday = 0;
        
        if ($isResident) {
            $resident = $this->residentModel->findByUserId($_SESSION['user_id']);
            if ($resident) {
                // Check reservations for today
                $stmt = $db->prepare("
                    SELECT COUNT(*) as count 
                    FROM reservations 
                    WHERE resident_id = ? 
                    AND reservation_date = CURDATE()
                    AND status NOT IN ('cancelled')
                ");
                $stmt->execute([$resident['id']]);
                $residentReservationsToday = $stmt->fetch()['count'];
            }
        }
        
        $data = [
            'title' => 'Calendario de Reservaciones',
            'amenities' => $amenities,
            'reservations' => $reservations,
            'currentDate' => $currentDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'isResident' => $isResident,
            'residentReservationsToday' => $residentReservationsToday
        ];
        
        $this->view('amenities/calendar', $data);
    }
}
