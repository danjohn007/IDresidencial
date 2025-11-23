<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">⚙️ Configuración General</h1>
                    <p class="text-gray-600 mt-1">Configuración básica del sistema</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/general" enctype="multipart/form-data">
                        <!-- Site Logo -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Logo del Sitio
                            </label>
                            <?php if (!empty($current['site_logo'])): ?>
                                <div class="mb-4">
                                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($current['site_logo']); ?>" 
                                         alt="Logo" class="h-20 object-contain">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="site_logo" accept=".jpg,.jpeg,.png,.svg"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Formatos aceptados: JPG, PNG, SVG. Tamaño máximo: 2MB. Validado en el servidor.</p>
                        </div>
                        
                        <!-- Site Name -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Sitio <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="site_name" required 
                                   value="<?php echo htmlspecialchars($current['site_name'] ?? SITE_NAME); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Nombre del residencial">
                        </div>

                        <!-- Site Email -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email de Contacto <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="site_email" required 
                                   value="<?php echo htmlspecialchars($current['site_email'] ?? SITE_EMAIL); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="contacto@residencial.com">
                        </div>

                        <!-- Site Phone -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono de Contacto <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="site_phone" required 
                                   value="<?php echo htmlspecialchars($current['site_phone'] ?? SITE_PHONE); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="+52 442 123 4567">
                        </div>

                        <!-- Default Maintenance Fee -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cuota de Mantenimiento por Defecto
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-2 text-gray-500">$</span>
                                <input type="number" name="maintenance_fee_default" step="0.01"
                                       value="<?php echo htmlspecialchars($current['maintenance_fee_default'] ?? '500.00'); ?>"
                                       class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="500.00">
                            </div>
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
