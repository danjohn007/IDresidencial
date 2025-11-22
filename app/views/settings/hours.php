<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🕐 Configuración de Horarios</h1>
                    <p class="text-gray-600 mt-1">Horarios de atención y servicio</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/hours">
                        <!-- Office Hours Weekday -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Horario de Oficina (Lunes a Viernes)
                            </label>
                            <input type="text" name="hours_office_weekday"
                                   value="<?php echo htmlspecialchars($current['hours_office_weekday'] ?? '9:00 AM - 6:00 PM'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="9:00 AM - 6:00 PM">
                        </div>

                        <!-- Office Hours Weekend -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Horario de Oficina (Fines de Semana)
                            </label>
                            <input type="text" name="hours_office_weekend"
                                   value="<?php echo htmlspecialchars($current['hours_office_weekend'] ?? '10:00 AM - 2:00 PM'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="10:00 AM - 2:00 PM">
                        </div>

                        <!-- Amenities Hours Weekday -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Horario de Amenidades (Lunes a Viernes)
                            </label>
                            <input type="text" name="hours_amenities_weekday"
                                   value="<?php echo htmlspecialchars($current['hours_amenities_weekday'] ?? '6:00 AM - 10:00 PM'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="6:00 AM - 10:00 PM">
                        </div>

                        <!-- Amenities Hours Weekend -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Horario de Amenidades (Fines de Semana)
                            </label>
                            <input type="text" name="hours_amenities_weekend"
                                   value="<?php echo htmlspecialchars($current['hours_amenities_weekend'] ?? '7:00 AM - 11:00 PM'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="7:00 AM - 11:00 PM">
                        </div>

                        <!-- Guard 24/7 -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="hours_guard_24_7" value="1" 
                                       <?php echo (isset($current['hours_guard_24_7']) && $current['hours_guard_24_7'] == '1') ? 'checked' : 'checked'; ?>
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Seguridad/Guardia 24/7</span>
                            </label>
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
