<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🛠️ Soporte Técnico</h1>
                    <p class="text-gray-600 mt-1">Configuración de contacto y acceso a soporte</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/support">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email de Soporte <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="support_email" required 
                                   value="<?php echo htmlspecialchars($current['support_email'] ?? 'soporte@janetzy.shop'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono de Soporte
                            </label>
                            <input type="text" name="support_phone" 
                                   value="<?php echo htmlspecialchars($current['support_phone'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="+52 442 123 4567">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Horario de Atención
                            </label>
                            <input type="text" name="support_hours" 
                                   value="<?php echo htmlspecialchars($current['support_hours'] ?? 'Lunes a Viernes 9:00 - 18:00'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                URL de Soporte Público
                            </label>
                            <input type="url" name="support_url" 
                                   value="<?php echo htmlspecialchars($current['support_url'] ?? BASE_URL . '/support'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="https://ejemplo.com/soporte">
                            <p class="text-xs text-gray-500 mt-1">URL pública donde los usuarios pueden ver información de soporte</p>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/settings" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Volver
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Guardar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Acceso Rápido -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-external-link-alt text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Acceso a Soporte</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p class="mb-2">Los usuarios pueden acceder al soporte técnico desde:</p>
                                <a href="<?php echo $current['support_url'] ?? BASE_URL . '/support'; ?>" 
                                   target="_blank"
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                                    <?php echo $current['support_url'] ?? BASE_URL . '/support'; ?>
                                    <i class="fas fa-external-link-alt ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
