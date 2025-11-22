<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <!-- Dashboard Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <!-- Welcome Section -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">
                    ¡Bienvenido, <?php echo $user['first_name']; ?>!
                </h1>
                <p class="text-gray-600 mt-1">
                    Este es tu panel de control del sistema residencial
                </p>
            </div>
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                <?php if (in_array($user['role'], ['superadmin', 'administrador'])): ?>
                <!-- Residentes -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Residentes</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_residents']; ?></p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-users text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (in_array($user['role'], ['superadmin', 'administrador', 'guardia'])): ?>
                <!-- Visitas Hoy -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Visitas Hoy</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_visits_today']; ?></p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-door-open text-green-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Mantenimientos -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Mantenimientos</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_maintenance']; ?></p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-tools text-yellow-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Reservaciones -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Reservaciones</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_reservations']; ?></p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <i class="fas fa-calendar-check text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <?php if (in_array($user['role'], ['superadmin', 'administrador'])): ?>
                <!-- Pagos Pendientes -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Pagos Pendientes</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['pending_payments']; ?></p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="fas fa-credit-card text-red-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (in_array($user['role'], ['superadmin', 'administrador', 'guardia'])): ?>
                <!-- Alertas Activas -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Alertas Activas</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['active_alerts']; ?></p>
                        </div>
                        <div class="bg-orange-100 rounded-full p-3">
                            <i class="fas fa-exclamation-triangle text-orange-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php if (in_array($user['role'], ['superadmin', 'administrador', 'guardia']) && !empty($recentActivity)): ?>
                <!-- Actividad Reciente -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-history text-blue-600 mr-2"></i>
                            Actividad Reciente
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="flex items-start space-x-3 pb-4 border-b last:border-b-0 last:pb-0">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-<?php echo $activity['access_type'] === 'entry' ? 'sign-in-alt' : 'sign-out-alt'; ?> text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($activity['name']); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo ucfirst($activity['log_type']); ?> - <?php echo ucfirst($activity['access_type']); ?>
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <?php echo date('d/m/Y H:i', strtotime($activity['timestamp'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Próximas Reservaciones -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                            Próximas Reservaciones
                        </h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($upcomingReservations)): ?>
                            <p class="text-gray-500 text-center py-8">No hay reservaciones próximas</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($upcomingReservations as $reservation): ?>
                                    <div class="flex items-start space-x-3 pb-4 border-b last:border-b-0 last:pb-0">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                <i class="fas fa-swimming-pool text-purple-600"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($reservation['amenity_name']); ?>
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                <?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?>
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                <?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?> 
                                                - <?php echo date('H:i', strtotime($reservation['start_time'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Mantenimientos Pendientes -->
                <div class="bg-white rounded-lg shadow lg:col-span-2">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-wrench text-yellow-600 mr-2"></i>
                            Mantenimientos Pendientes
                        </h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($pendingMaintenance)): ?>
                            <p class="text-gray-500 text-center py-8">No hay mantenimientos pendientes</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reportado por</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridad</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($pendingMaintenance as $maint): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo ucfirst($maint['category']); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($maint['title']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo $maint['first_name'] . ' ' . $maint['last_name']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full
                                                        <?php 
                                                            echo match($maint['priority']) {
                                                                'urgente' => 'bg-red-100 text-red-800',
                                                                'alta' => 'bg-orange-100 text-orange-800',
                                                                'media' => 'bg-yellow-100 text-yellow-800',
                                                                'baja' => 'bg-green-100 text-green-800',
                                                                default => 'bg-gray-100 text-gray-800'
                                                            };
                                                        ?>">
                                                        <?php echo ucfirst($maint['priority']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                        <?php echo ucfirst($maint['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo date('d/m/Y', strtotime($maint['created_at'])); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
