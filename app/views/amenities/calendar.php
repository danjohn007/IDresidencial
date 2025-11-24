<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<!-- Include FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">📅 Calendario de Reservaciones</h1>
                <p class="text-gray-600 mt-1">Calendario global de amenidades</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if ($isResident && $residentReservationsToday > 0): ?>
                <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700 rounded">
                    <i class="fas fa-info-circle mr-2"></i>
                    Ya tienes <?php echo $residentReservationsToday; ?> reservación(es) para hoy. Máximo una reservación por día por amenidad.
                </div>
            <?php endif; ?>

            <!-- Calendar Controls -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button onclick="calendar.prev()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </button>
                        <button onclick="calendar.today()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Hoy
                        </button>
                        <button onclick="calendar.next()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="flex space-x-2">
                        <?php if (!$isResident): ?>
                        <a href="<?php echo BASE_URL; ?>/amenities/manage" 
                           class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            <i class="fas fa-cog mr-2"></i>Gestionar Amenidades
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/amenities/myReservations" 
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fas fa-list mr-2"></i>Mis Reservaciones
                        </a>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">Leyenda</h3>
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded bg-blue-500 mr-2"></div>
                        <span class="text-sm">Confirmada</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded bg-yellow-500 mr-2"></div>
                        <span class="text-sm">Pendiente</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded bg-green-500 mr-2"></div>
                        <span class="text-sm">Completada</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rounded bg-red-500 mr-2"></div>
                        <span class="text-sm">No Show</span>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="bg-white rounded-lg shadow p-6">
                <div id="calendar"></div>
            </div>
        </main>
    </div>
</div>

<!-- Reservation Details Modal -->
<div id="reservationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Detalles de Reservación</h3>
            <button onclick="closeModal()" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="modalContent" class="text-gray-700">
            <!-- Content will be inserted here -->
        </div>
    </div>
</div>

<!-- Include FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
let calendar;

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    const events = <?php 
        $eventsData = [];
        foreach ($reservations as $reservation) {
            $eventsData[] = [
                'id' => $reservation['id'],
                'title' => $reservation['amenity_name'],
                'start' => $reservation['reservation_date'] . 'T' . $reservation['start_time'],
                'end' => $reservation['reservation_date'] . 'T' . $reservation['end_time'],
                'backgroundColor' => match($reservation['status']) {
                    'confirmed' => '#3B82F6',
                    'pending' => '#EAB308',
                    'completed' => '#10B981',
                    'no_show' => '#EF4444',
                    default => '#6B7280'
                },
                'extendedProps' => [
                    'amenity' => $reservation['amenity_name'],
                    'resident' => $reservation['first_name'] . ' ' . $reservation['last_name'],
                    'property' => $reservation['property_number'] ?? 'N/A',
                    'status' => $reservation['status'],
                    'guests' => $reservation['guests_count'],
                    'amount' => $reservation['amount']
                ]
            ];
        }
        echo json_encode($eventsData);
    ?>;
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'title',
            right: ''
        },
        locale: 'es',
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },
        events: events,
        eventClick: function(info) {
            showReservationDetails(info.event);
        },
        dateClick: function(info) {
            <?php if ($isResident): ?>
            // Residents can reserve by clicking on a date
            showAmenitySelection(info.dateStr);
            <?php endif; ?>
        },
        height: 'auto',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }
    });
    
    calendar.render();
});

function showReservationDetails(event) {
    const props = event.extendedProps;
    const startTime = event.start.toLocaleTimeString('es-MX', {hour: '2-digit', minute: '2-digit'});
    const endTime = event.end ? event.end.toLocaleTimeString('es-MX', {hour: '2-digit', minute: '2-digit'}) : '';
    
    const statusBadge = {
        'confirmed': '<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Confirmada</span>',
        'pending': '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pendiente</span>',
        'completed': '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Completada</span>',
        'no_show': '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">No Show</span>'
    };
    
    document.getElementById('modalContent').innerHTML = `
        <div class="space-y-3">
            <div>
                <p class="text-sm text-gray-600">Amenidad</p>
                <p class="font-medium">${props.amenity}</p>
            </div>
            <?php if (!$isResident): ?>
            <div>
                <p class="text-sm text-gray-600">Residente</p>
                <p class="font-medium">${props.resident} - ${props.property}</p>
            </div>
            <?php endif; ?>
            <div>
                <p class="text-sm text-gray-600">Horario</p>
                <p class="font-medium">${startTime} - ${endTime}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Invitados</p>
                <p class="font-medium">${props.guests}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Estado</p>
                <p class="font-medium">${statusBadge[props.status]}</p>
            </div>
            <?php if (!$isResident): ?>
            <div>
                <p class="text-sm text-gray-600">Monto</p>
                <p class="font-medium">$${props.amount}</p>
            </div>
            <?php endif; ?>
            <div class="pt-4 border-t">
                <a href="<?php echo BASE_URL; ?>/amenities/myReservations" 
                   class="inline-block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Ver Todas Mis Reservaciones
                </a>
            </div>
        </div>
    `;
    
    document.getElementById('reservationModal').classList.remove('hidden');
}

function showAmenitySelection(dateStr) {
    <?php if ($isResident): ?>
    // Redirect to reserve page with pre-filled date
    window.location.href = '<?php echo BASE_URL; ?>/amenities?date=' + dateStr;
    <?php endif; ?>
}

function closeModal() {
    document.getElementById('reservationModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('reservationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
