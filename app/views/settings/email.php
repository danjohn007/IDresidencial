<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📧 Configuración de Correo</h1>
                    <p class="text-gray-600 mt-1">Configuración SMTP para envío de correos</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/email">
                        <!-- Email Host -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Servidor SMTP (Host) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="email_host" required 
                                   value="<?php echo htmlspecialchars($current['email_host'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="smtp.gmail.com">
                        </div>

                        <!-- Email Port -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Puerto SMTP <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="email_port" required 
                                   value="<?php echo htmlspecialchars($current['email_port'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="587">
                        </div>

                        <!-- Email User -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Usuario SMTP <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="email_user" required 
                                   value="<?php echo htmlspecialchars($current['email_user'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="usuario@gmail.com">
                        </div>

                        <!-- Email Password -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Contraseña SMTP <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="email_password" 
                                   value="<?php echo htmlspecialchars($current['email_password'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="********">
                            <?php if (!empty($current['email_password'])): ?>
                                <p class="text-xs text-gray-500 mt-1">Dejar en blanco para mantener la contraseña actual</p>
                            <?php endif; ?>
                        </div>

                        <!-- Email From -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Remitente (From) <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email_from" required 
                                   value="<?php echo htmlspecialchars($current['email_from'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="noreply@residencial.com">
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/settings" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Volver
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Box -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Configuración Común
                    </h4>
                    <div class="text-sm text-blue-800 space-y-2">
                        <div>
                            <strong>Gmail:</strong> smtp.gmail.com, Puerto 587
                            <br>
                            <span class="text-xs">Nota: Necesitas habilitar "Aplicaciones menos seguras" o usar una contraseña de aplicación</span>
                        </div>
                        <div>
                            <strong>Outlook/Hotmail:</strong> smtp-mail.outlook.com, Puerto 587
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
