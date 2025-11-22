<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🚨 Nueva Alerta de Seguridad</h1>
                    <p class="text-gray-600 mt-1">Registrar una nueva alerta o incidente de seguridad</p>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/security/createAlert">
                        <!-- Alert Type -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Alerta <span class="text-red-500">*</span>
                            </label>
                            <select name="alert_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Seleccionar tipo...</option>
                                <option value="intrusion">Intrusión</option>
                                <option value="vandalism">Vandalismo</option>
                                <option value="suspicious_activity">Actividad Sospechosa</option>
                                <option value="fire">Incendio</option>
                                <option value="medical_emergency">Emergencia Médica</option>
                                <option value="noise">Ruido Excesivo</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>

                        <!-- Severity -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Severidad <span class="text-red-500">*</span>
                            </label>
                            <select name="severity" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="low">Baja</option>
                                <option value="medium" selected>Media</option>
                                <option value="high">Alta</option>
                                <option value="critical">Crítica</option>
                            </select>
                        </div>

                        <!-- Location -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ubicación <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="location" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Ej: Entrada principal, Sección A, Casa 123">
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" required rows="6"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Describa detalladamente el incidente..."></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/security" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Crear Alerta
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Box -->
                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h4 class="font-semibold text-red-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Importante
                    </h4>
                    <ul class="text-sm text-red-800 space-y-1 list-disc list-inside">
                        <li>Las alertas críticas generarán notificaciones inmediatas</li>
                        <li>El personal de seguridad será notificado automáticamente</li>
                        <li>Para emergencias reales, contacte directamente al 911</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
