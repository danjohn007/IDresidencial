<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">⚙️ Configuración del Sistema</h1>
                <p class="text-gray-600 mt-1">Personaliza tu sistema residencial</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- General Settings -->
                <a href="<?php echo BASE_URL; ?>/settings/general" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <i class="fas fa-cog text-blue-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">General</h3>
                            <p class="text-sm text-gray-600">Nombre, logo, contacto</p>
                        </div>
                    </div>
                </a>

                <!-- Theme Settings -->
                <a href="<?php echo BASE_URL; ?>/settings/theme" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-purple-100 p-4 rounded-lg">
                            <i class="fas fa-palette text-purple-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Tema</h3>
                            <p class="text-sm text-gray-600">Colores y estilos</p>
                        </div>
                    </div>
                </a>

                <!-- Email Settings -->
                <a href="<?php echo BASE_URL; ?>/settings/email" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-100 p-4 rounded-lg">
                            <i class="fas fa-envelope text-green-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Correo</h3>
                            <p class="text-sm text-gray-600">SMTP y notificaciones</p>
                        </div>
                    </div>
                </a>

                <!-- Payment Settings -->
                <a href="<?php echo BASE_URL; ?>/settings/payment" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-yellow-100 p-4 rounded-lg">
                            <i class="fas fa-credit-card text-yellow-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Pagos</h3>
                            <p class="text-sm text-gray-600">PayPal, cuotas</p>
                        </div>
                    </div>
                </a>

                <!-- Hours Settings -->
                <a href="<?php echo BASE_URL; ?>/settings/hours" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-red-100 p-4 rounded-lg">
                            <i class="fas fa-clock text-red-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Horarios</h3>
                            <p class="text-sm text-gray-600">Atención y servicio</p>
                        </div>
                    </div>
                </a>

                <!-- QR Settings -->
                <a href="<?php echo BASE_URL; ?>/settings/qr" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-indigo-100 p-4 rounded-lg">
                            <i class="fas fa-qrcode text-indigo-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Códigos QR</h3>
                            <p class="text-sm text-gray-600">API y configuración</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Current Settings Overview -->
            <div class="mt-6 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Configuración Actual</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($settings as $category => $categorySettings): ?>
                        <?php if (!empty($categorySettings)): ?>
                            <div class="border-l-4 border-blue-500 pl-4">
                                <h3 class="font-semibold text-gray-900 mb-2"><?php echo ucfirst($category); ?></h3>
                                <div class="space-y-1">
                                    <?php foreach ($categorySettings as $setting): ?>
                                        <div class="text-sm">
                                            <span class="text-gray-600"><?php echo str_replace('_', ' ', ucfirst($setting['setting_key'])); ?>:</span>
                                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars($setting['setting_value']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
