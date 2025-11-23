<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">📱 Dispositivos</h1>
                <p class="text-gray-600 mt-1">Gestión de dispositivos de control de acceso</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Dispositivos HikVision -->
                <a href="#hikvision" class="bg-blue-600 text-white p-6 rounded-lg shadow-lg hover:bg-blue-700 transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                            <i class="fas fa-video text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Dispositivos HikVision</h3>
                            <p class="text-sm text-blue-100">Gestión de control de acceso HikVision</p>
                        </div>
                    </div>
                </a>

                <!-- Dispositivos Inhabilitados -->
                <a href="<?php echo BASE_URL; ?>/devices/disabled" class="bg-orange-600 text-white p-6 rounded-lg shadow-lg hover:bg-orange-700 transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                            <i class="fas fa-ban text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Dispositivos Inhabilitados</h3>
                            <p class="text-sm text-orange-100">Ver y habilitar dispositivos desactivados</p>
                        </div>
                    </div>
                </a>

                <!-- Agregar Dispositivo -->
                <div class="bg-green-600 text-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                            <i class="fas fa-plus-circle text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Agregar Dispositivo</h3>
                            <p class="text-sm text-green-100">Registrar nuevo dispositivo Shelly</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mb-6">
                <button onclick="location.href='<?php echo BASE_URL; ?>/devices/updateStatus'" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar Estados
                </button>
                <div class="flex space-x-2">
                    <button onclick="location.href='<?php echo BASE_URL; ?>/devices/createHikvision'" 
                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-video mr-2"></i> Nuevo Dispositivo Hikvision
                    </button>
                    <button onclick="location.href='<?php echo BASE_URL; ?>/devices/createShelly'" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-plus mr-2"></i> Nuevo Dispositivo Shelly
                    </button>
                </div>
            </div>

            <!-- Dispositivos Shelly Section -->
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <i class="fas fa-wifi text-green-600 text-2xl mr-3"></i>
                    <h2 class="text-2xl font-bold text-gray-900">Dispositivos Shelly</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    $shellyDevices = array_filter($devices, fn($d) => $d['device_type'] === 'shelly' && $d['enabled']);
                    if (empty($shellyDevices)): 
                    ?>
                        <div class="col-span-full text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3"></i>
                            <p>No hay dispositivos Shelly registrados</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($shellyDevices as $device): ?>
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($device['device_name']); ?></h3>
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium <?php echo $device['status'] === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $device['status'] === 'online' ? 'Online' : 'Offline'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-fingerprint w-5"></i>
                                        <span><?php echo htmlspecialchars($device['device_id']); ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-building w-5"></i>
                                        <span><?php echo htmlspecialchars($device['location'] ?? 'Sin ubicación'); ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-map-marker-alt w-5"></i>
                                        <span><?php echo htmlspecialchars($device['area'] ?? 'Sin área'); ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock w-5"></i>
                                        <span>Apertura: <?php echo $device['open_time']; ?>s</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-hourglass-half w-5"></i>
                                        <span>Última actualización: <?php echo date('d/m/Y H:i', strtotime($device['updated_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button onclick="testDevice(<?php echo $device['id']; ?>)" 
                                            class="flex-1 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition text-sm">
                                        <i class="fas fa-vial mr-1"></i> Probar
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>/devices/edit/<?php echo $device['id']; ?>" 
                                       class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm text-center">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Integración con Shelly Cloud -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 text-xl mr-3 mt-1"></i>
                    <div>
                        <h3 class="font-bold text-blue-900 mb-2">Integración con Shelly Cloud</h3>
                        <p class="text-sm text-blue-700 mb-2">
                            Los dispositivos Shelly permiten el control remoto de puertas magnéticas.
                            Configure cada dispositivo con su token de autenticación y servidor de Shelly Cloud para habilitar el control remoto.
                        </p>
                        <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                            <li>Token de autenticación requerido para cada dispositivo</li>
                            <li>Soporte para canales de entrada y salida configurables</li>
                            <li>Duración de pulso personalizable (por defecto 4000ms)</li>
                            <li>Tiempo de apertura ajustable (1-60 segundos)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function testDevice(deviceId) {
    if (confirm('¿Desea probar este dispositivo?')) {
        window.location.href = '<?php echo BASE_URL; ?>/devices/test/' + deviceId;
    }
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
