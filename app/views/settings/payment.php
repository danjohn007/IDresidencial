<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">💳 Configuración de Pagos</h1>
                    <p class="text-gray-600 mt-1">Configuración de PayPal y otros métodos de pago</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/payment">
                        <!-- PayPal Enabled -->
                        <div class="mb-6">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="paypal_enabled" value="1" 
                                       class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                                <span class="ml-3 text-sm font-medium text-gray-700">
                                    Habilitar pagos con PayPal
                                </span>
                            </label>
                        </div>

                        <!-- PayPal Mode -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Modo de PayPal
                            </label>
                            <select name="paypal_mode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="sandbox">Sandbox (Pruebas)</option>
                                <option value="live">Live (Producción)</option>
                            </select>
                        </div>

                        <!-- PayPal Client ID -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Client ID de PayPal
                            </label>
                            <input type="text" name="paypal_client_id" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="AXXXxxxXXXxxx...">
                        </div>

                        <!-- PayPal Secret -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Secret de PayPal
                            </label>
                            <input type="password" name="paypal_secret" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="EXXXxxxXXXxxx...">
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
                        Cómo obtener credenciales de PayPal
                    </h4>
                    <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
                        <li>Accede a <a href="https://developer.paypal.com" target="_blank" class="underline">developer.paypal.com</a></li>
                        <li>Crea una cuenta de desarrollador si no tienes una</li>
                        <li>Ve a "My Apps & Credentials"</li>
                        <li>Crea una nueva app para obtener Client ID y Secret</li>
                        <li>Usa las credenciales de Sandbox para pruebas</li>
                    </ol>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
