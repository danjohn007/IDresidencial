<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Control de Accesos</h1>
                    <p class="text-gray-600 mt-1">Gestión de visitas y registro de accesos</p>
                </div>
                <div class="space-x-2">
                    <a href="<?php echo BASE_URL; ?>/access/create" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-qrcode mr-2"></i> Generar Pase
                    </a>
                    <a href="<?php echo BASE_URL; ?>/access/validate" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-check-circle mr-2"></i> Validar QR
                    </a>
                    <a href="<?php echo BASE_URL; ?>/access/logs" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-book mr-2"></i> Bitácora
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Hoy</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_today']; ?></p>
                        </div>
                        <i class="fas fa-users text-blue-600 text-3xl"></i>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Activas</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['active']; ?></p>
                        </div>
                        <i class="fas fa-user-check text-green-600 text-3xl"></i>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pendientes</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['pending']; ?></p>
                        </div>
                        <i class="fas fa-clock text-yellow-600 text-3xl"></i>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Completadas</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['completed']; ?></p>
                        </div>
                        <i class="fas fa-check-double text-gray-600 text-3xl"></i>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
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

            <!-- Visitas de Hoy -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                        Visitas de Hoy
                    </h2>
                </div>
                
                <div class="overflow-x-auto">
                    <?php if (empty($visits)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-6xl mb-4"></i>
                            <p class="text-lg">No hay visitas registradas hoy</p>
                        </div>
                    <?php else: ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visitante</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehículo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($visits as $visit): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div>
                                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($visit['visitor_name']); ?></p>
                                                <?php if ($visit['visitor_phone']): ?>
                                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($visit['visitor_phone']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?php echo $visit['first_name'] . ' ' . $visit['last_name']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div>
                                                <p class="font-medium"><?php echo $visit['property_number']; ?></p>
                                                <p class="text-xs text-gray-500"><?php echo $visit['section']; ?></p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?php echo $visit['vehicle_plate'] ?: '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div>
                                                <p><?php echo date('H:i', strtotime($visit['valid_from'])); ?></p>
                                                <p class="text-xs text-gray-500">a <?php echo date('H:i', strtotime($visit['valid_until'])); ?></p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full <?php 
                                                echo match($visit['status']) {
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'active' => 'bg-green-100 text-green-800',
                                                    'completed' => 'bg-gray-100 text-gray-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    'expired' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            ?>">
                                                <?php echo ucfirst($visit['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex space-x-2">
                                                <?php if ($visit['status'] === 'pending'): ?>
                                                    <a href="<?php echo BASE_URL; ?>/access/registerEntry/<?php echo $visit['id']; ?>" 
                                                       class="text-green-600 hover:text-green-900" title="Registrar Entrada">
                                                        <i class="fas fa-sign-in-alt"></i>
                                                    </a>
                                                <?php elseif ($visit['status'] === 'active'): ?>
                                                    <a href="<?php echo BASE_URL; ?>/access/registerExit/<?php echo $visit['id']; ?>" 
                                                       class="text-blue-600 hover:text-blue-900" title="Registrar Salida">
                                                        <i class="fas fa-sign-out-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?php echo BASE_URL; ?>/access/viewDetails/<?php echo $visit['qr_code']; ?>" 
                                                   class="text-gray-600 hover:text-gray-900" title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
