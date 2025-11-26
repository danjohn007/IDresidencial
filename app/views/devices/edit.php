<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">✏️ Editar Dispositivo</h1>
                        <p class="text-gray-600 mt-1">Modificar configuración del dispositivo</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/devices" 
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                </div>

                <!-- Error Messages -->
                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/devices/edit/<?php echo $device['id']; ?>">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Dispositivo
                                </label>
                                <input type="text" value="<?php echo ucfirst($device['device_type']); ?>" disabled 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Device ID <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="device_id" required 
                                       value="<?php echo htmlspecialchars($device['device_id'] ?? ''); ?>"
                                       placeholder="shellyswitch25-XXXXXXXXXXXX"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">
                                    Para Shelly: Encuentra el Device ID en la app o web del dispositivo
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="device_name" required 
                                       value="<?php echo htmlspecialchars($device['device_name'] ?? ''); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <?php if ($device['device_type'] === 'hikvision'): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Dirección IP <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="ip_address" required 
                                               value="<?php echo htmlspecialchars($device['ip_address'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Puerto <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" name="port" required 
                                               value="<?php echo $device['port'] ?? 80; ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Usuario <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="username" required 
                                           value="<?php echo htmlspecialchars($device['username'] ?? ''); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contraseña (dejar en blanco para no cambiar)
                                    </label>
                                    <input type="password" name="password" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Número de Puerta
                                    </label>
                                    <select name="door_number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <?php for ($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $device['door_number'] == $i ? 'selected' : ''; ?>>
                                                Puerta <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Token de Autenticación <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="auth_token" required 
                                           value="<?php echo htmlspecialchars($device['auth_token'] ?? ''); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Servidor Cloud
                                    </label>
                                    <input type="text" name="cloud_server" 
                                           value="<?php echo htmlspecialchars($device['cloud_server'] ?? ''); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Área
                                    </label>
                                    <input type="text" name="area" 
                                           value="<?php echo htmlspecialchars($device['area'] ?? ''); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Canal de Entrada
                                        </label>
                                        <select name="input_channel" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $device['input_channel'] == $i ? 'selected' : ''; ?>>
                                                    Canal <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Canal de Salida
                                        </label>
                                        <select name="output_channel" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                            <?php for ($i = 0; $i <= 4; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $device['output_channel'] == $i ? 'selected' : ''; ?>>
                                                    Canal <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Duración Pulso (ms)
                                        </label>
                                        <input type="number" name="pulse_duration" 
                                               value="<?php echo $device['pulse_duration'] ?? 1000; ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Tiempo de Apertura (seg)
                                        </label>
                                        <input type="number" name="open_time" 
                                               value="<?php echo $device['open_time'] ?? 5; ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="inverted" value="1" 
                                               <?php echo $device['inverted'] ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Invertido (off → on)</span>
                                    </label>
                                </div>
                                
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="simultaneous" value="1" 
                                               <?php echo $device['simultaneous'] ? 'checked' : ''; ?>
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Dispositivo simultáneo</span>
                                    </label>
                                </div>
                            <?php endif; ?>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Ubicación
                                </label>
                                <input type="text" name="location" 
                                       value="<?php echo htmlspecialchars($device['location'] ?? ''); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="enabled" value="1" 
                                           <?php echo $device['enabled'] ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Dispositivo habilitado</span>
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="<?php echo BASE_URL; ?>/devices" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
