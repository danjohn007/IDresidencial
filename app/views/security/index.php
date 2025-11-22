<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🔒 Seguridad</h1>
                    <p class="text-gray-600 mt-1">Monitoreo y control de seguridad</p>
                </div>
                <div class="space-x-2">
                    <a href="<?php echo BASE_URL; ?>/security/createAlert" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Nueva Alerta
                    </a>
                    <button onclick="document.getElementById('patrolModal').classList.remove('hidden')" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-walking mr-2"></i> Iniciar Rondín
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Alertas Activas</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['active_alerts']; ?></p>
                        </div>
                        <i class="fas fa-exclamation-circle text-red-600 text-3xl"></i>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Rondines Activos</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['active_patrols']; ?></p>
                        </div>
                        <i class="fas fa-walking text-blue-600 text-3xl"></i>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Alertas Hoy</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['alerts_today']; ?></p>
                        </div>
                        <i class="fas fa-calendar-day text-yellow-600 text-3xl"></i>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Rondines Hoy</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['patrols_today']; ?></p>
                        </div>
                        <i class="fas fa-clipboard-check text-green-600 text-3xl"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Alertas Activas -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-900">Alertas Activas</h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($alerts)): ?>
                            <p class="text-gray-500 text-center py-8">No hay alertas activas</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($alerts as $alert): ?>
                                    <div class="border-l-4 <?php 
                                        echo match($alert['severity']) {
                                            'critical' => 'border-red-600',
                                            'high' => 'border-orange-600',
                                            'medium' => 'border-yellow-600',
                                            'low' => 'border-blue-600',
                                            default => 'border-gray-600'
                                        };
                                    ?> pl-4 py-3">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-semibold text-gray-900"><?php echo ucfirst($alert['alert_type']); ?></p>
                                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($alert['description']); ?></p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <?php echo $alert['location']; ?> - <?php echo date('d/m/Y H:i', strtotime($alert['created_at'])); ?>
                                                </p>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full <?php 
                                                echo match($alert['severity']) {
                                                    'critical' => 'bg-red-100 text-red-800',
                                                    'high' => 'bg-orange-100 text-orange-800',
                                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                                    'low' => 'bg-blue-100 text-blue-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            ?>">
                                                <?php echo ucfirst($alert['severity']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rondines Activos -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-900">Rondines en Curso</h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($patrols)): ?>
                            <p class="text-gray-500 text-center py-8">No hay rondines activos</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($patrols as $patrol): ?>
                                    <div class="border rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-semibold text-gray-900">
                                                    <?php echo $patrol['first_name'] . ' ' . $patrol['last_name']; ?>
                                                </p>
                                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($patrol['route']); ?></p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Inicio: <?php echo date('H:i', strtotime($patrol['patrol_start'])); ?>
                                                </p>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                En curso
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Modal para iniciar rondín -->
            <div id="patrolModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-xl font-bold mb-4">Iniciar Rondín</h3>
                    <form method="POST" action="<?php echo BASE_URL; ?>/security/startPatrol">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ruta del Rondín</label>
                            <textarea name="route" rows="3" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="Describe la ruta del rondín..."></textarea>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="document.getElementById('patrolModal').classList.add('hidden')"
                                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Iniciar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
