<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">📅 Mis Reservaciones</h1>
                    <p class="text-gray-600 mt-1">
                        <?php if (in_array($_SESSION['role'], ['superadmin', 'administrador'])): ?>
                            Todas las reservaciones del sistema
                        <?php else: ?>
                            Historial de tus reservaciones
                        <?php endif; ?>
                    </p>
                </div>
                <a href="<?php echo BASE_URL; ?>/amenities" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a Amenidades
                </a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded alert-auto-hide">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Reservations Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amenidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horario</th>
                            <?php if (in_array($_SESSION['role'], ['superadmin', 'administrador'])): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <?php endif; ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invitados</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($reservations)): ?>
                            <tr>
                                <td colspan="<?php echo in_array($_SESSION['role'], ['superadmin', 'administrador']) ? '8' : '6'; ?>" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                                    <p>No hay reservaciones registradas</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservations as $reservation): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($reservation['amenity_name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('H:i', strtotime($reservation['start_time'])); ?> - 
                                        <?php echo date('H:i', strtotime($reservation['end_time'])); ?>
                                    </td>
                                    <?php if (in_array($_SESSION['role'], ['superadmin', 'administrador'])): ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($reservation['property_number'] ?? 'N/A'); ?>
                                        </td>
                                    <?php endif; ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $reservation['guests_count']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?php 
                                            echo match($reservation['status']) {
                                                'confirmed' => 'bg-green-100 text-green-800',
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'completed' => 'bg-blue-100 text-blue-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                'no_show' => 'bg-gray-100 text-gray-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?php 
                                            echo match($reservation['status']) {
                                                'confirmed' => 'Confirmada',
                                                'pending' => 'Pendiente',
                                                'completed' => 'Completada',
                                                'cancelled' => 'Cancelada',
                                                'no_show' => 'No asistió',
                                                default => ucfirst($reservation['status'])
                                            };
                                            ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <?php if (in_array($reservation['status'], ['pending', 'confirmed'])): ?>
                                            <a href="<?php echo BASE_URL; ?>/amenities/cancel/<?php echo $reservation['id']; ?>" 
                                               onclick="return confirm('¿Está seguro de cancelar esta reservación?')"
                                               class="text-red-600 hover:text-red-900" title="Cancelar">
                                                <i class="fas fa-times-circle"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-300">
                                                <i class="fas fa-times-circle"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Stats (for admin only) -->
            <?php if (in_array($_SESSION['role'], ['superadmin', 'administrador']) && !empty($reservations)): ?>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <?php
                    $stats = [
                        'total' => count($reservations),
                        'confirmed' => count(array_filter($reservations, fn($r) => $r['status'] === 'confirmed')),
                        'pending' => count(array_filter($reservations, fn($r) => $r['status'] === 'pending')),
                        'completed' => count(array_filter($reservations, fn($r) => $r['status'] === 'completed'))
                    ];
                    ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-600 mb-1">Total</p>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-600 mb-1">Confirmadas</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo $stats['confirmed']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-600 mb-1">Pendientes</p>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-600 mb-1">Completadas</p>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $stats['completed']; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
