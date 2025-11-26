<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">🛡️ Consola de Guardia</h1>
                <p class="text-gray-600 mt-1">Control de accesos en tiempo real</p>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <a href="<?php echo BASE_URL; ?>/guard/quickScan" class="bg-blue-600 text-white p-6 rounded-lg hover:bg-blue-700 transition text-center">
                    <i class="fas fa-qrcode text-4xl mb-2"></i>
                    <p class="text-lg font-semibold">Escanear QR</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/guard/manualAccess" class="bg-green-600 text-white p-6 rounded-lg hover:bg-green-700 transition text-center">
                    <i class="fas fa-edit text-4xl mb-2"></i>
                    <p class="text-lg font-semibold">Registro Manual</p>
                </a>
                <a href="<?php echo BASE_URL; ?>/guard/alerts" class="bg-red-600 text-white p-6 rounded-lg hover:bg-red-700 transition text-center">
                    <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
                    <p class="text-lg font-semibold">Ver Alertas</p>
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-2xl font-bold text-blue-600"><?php echo $stats['total_visits']; ?></p>
                    <p class="text-sm text-gray-600">Total Visitas</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-2xl font-bold text-green-600"><?php echo $stats['active_visits']; ?></p>
                    <p class="text-sm text-gray-600">Activas</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending_visits']; ?></p>
                    <p class="text-sm text-gray-600">Pendientes</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-2xl font-bold text-gray-600"><?php echo $stats['completed_visits']; ?></p>
                    <p class="text-sm text-gray-600">Completadas</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-2xl font-bold text-blue-600"><?php echo $stats['total_entries']; ?></p>
                    <p class="text-sm text-gray-600">Entradas</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow text-center">
                    <p class="text-2xl font-bold text-orange-600"><?php echo $stats['total_exits']; ?></p>
                    <p class="text-sm text-gray-600">Salidas</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Visitas Programadas -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-bold">Visitas Programadas Hoy</h2>
                    </div>
                    <div class="p-4 max-h-96 overflow-y-auto">
                        <?php if (empty($visits)): ?>
                            <p class="text-gray-500 text-center py-4">No hay visitas programadas</p>
                        <?php else: ?>
                            <div class="space-y-2">
                                <?php foreach ($visits as $visit): ?>
                                    <div class="p-3 border rounded-lg hover:bg-gray-50">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-semibold"><?php echo htmlspecialchars($visit['visitor_name']); ?></p>
                                                <p class="text-sm text-gray-600">
                                                    <?php echo $visit['property_number']; ?> - 
                                                    <?php echo $visit['first_name'] . ' ' . $visit['last_name']; ?>
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    <?php echo date('H:i', strtotime($visit['valid_from'])); ?> - 
                                                    <?php echo date('H:i', strtotime($visit['valid_until'])); ?>
                                                </p>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full <?php 
                                                echo match($visit['status']) {
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'active' => 'bg-green-100 text-green-800',
                                                    'completed' => 'bg-gray-100 text-gray-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            ?>">
                                                <?php echo ucfirst($visit['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Accesos Recientes -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-bold">Accesos Recientes</h2>
                    </div>
                    <div class="p-4 max-h-96 overflow-y-auto">
                        <?php if (empty($recentAccess)): ?>
                            <p class="text-gray-500 text-center py-4">No hay accesos registrados</p>
                        <?php else: ?>
                            <div class="space-y-2">
                                <?php foreach ($recentAccess as $access): ?>
                                    <div class="flex items-start space-x-3 p-2 border-b">
                                        <div class="flex-shrink-0 mt-1">
                                            <i class="fas fa-<?php echo $access['access_type'] === 'entry' ? 'sign-in-alt text-green-600' : 'sign-out-alt text-orange-600'; ?>"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($access['name'] ?? 'Sin nombre'); ?></p>
                                            <p class="text-xs text-gray-500">
                                                <?php echo ucfirst($access['log_type']); ?> - 
                                                <?php echo date('H:i:s', strtotime($access['timestamp'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
