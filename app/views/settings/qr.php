<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📱 Configuración de Códigos QR</h1>
                    <p class="text-gray-600 mt-1">API y configuración de códigos QR para visitantes</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/qr">
                        <!-- QR Enabled -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="qr_enabled" value="1" 
                                       <?php echo (isset($current['qr_enabled']) && $current['qr_enabled'] == '1') ? 'checked' : ''; ?>
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Habilitar sistema de códigos QR</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-6">Permite generar códigos QR para el acceso de visitantes</p>
                        </div>

                        <!-- QR Expiration Hours -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tiempo de Expiración (horas)
                            </label>
                            <input type="number" name="qr_expiration_hours" min="1" max="168"
                                   value="<?php echo htmlspecialchars($current['qr_expiration_hours'] ?? '24'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="24">
                            <p class="text-xs text-gray-500 mt-1">Los códigos QR expirarán después de este tiempo (1-168 horas)</p>
                        </div>

                        <!-- API Key -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Clave API
                            </label>
                            <input type="text" name="qr_api_key"
                                   value="<?php echo htmlspecialchars($current['qr_api_key'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Ingrese la clave API para servicios QR externos">
                            <p class="text-xs text-gray-500 mt-1">Opcional: Para integración con servicios externos de códigos QR</p>
                        </div>

                        <!-- Logo Enabled -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="qr_logo_enabled" value="1" 
                                       <?php echo (!isset($current['qr_logo_enabled']) || $current['qr_logo_enabled'] == '1') ? 'checked' : ''; ?>
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Incluir logo del residencial en códigos QR</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-6">Personaliza los códigos QR con el logo del residencial</p>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        Los códigos QR permiten a los residentes generar accesos temporales para sus visitantes.
                                        Los guardias pueden escanear estos códigos para verificar y registrar el acceso.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="<?php echo BASE_URL; ?>/settings" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Volver
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
