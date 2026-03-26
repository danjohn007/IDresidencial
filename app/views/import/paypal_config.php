<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📥 Cuenta PayPal</h1>
                    <p class="text-gray-600 mt-1">Importar credenciales y configuración de PayPal desde archivo CSV</p>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_details)): ?>
                    <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 rounded">
                        <p class="font-semibold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Detalle de errores:</p>
                        <ul class="text-sm list-disc list-inside space-y-1">
                            <?php foreach ($error_details as $detail): ?>
                                <li><?php echo htmlspecialchars($detail); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Archivo CSV <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="csv_file" accept=".csv" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/import"
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Volver
                            </a>
                            <a href="<?php echo BASE_URL; ?>/import/downloadTemplate/paypalConfig"
                               class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-file-download mr-2"></i> Descargar Plantilla
                            </a>
                            <button type="submit" class="px-6 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700">
                                <i class="fas fa-upload mr-2"></i> Importar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-2">Formato del CSV</h4>
                    <p class="text-sm text-blue-800 mb-2">El archivo CSV debe tener dos columnas: <strong>setting_key</strong> y <strong>setting_value</strong>.</p>
                    <code class="text-xs bg-white p-2 rounded block">setting_key,setting_value</code>
                    <p class="text-sm text-blue-800 mt-3 mb-1">Claves permitidas:</p>
                    <table class="text-xs text-blue-800 w-full border-collapse">
                        <thead>
                            <tr class="bg-blue-100">
                                <th class="text-left p-1 border border-blue-200">Clave</th>
                                <th class="text-left p-1 border border-blue-200">Descripción</th>
                                <th class="text-left p-1 border border-blue-200">Valores posibles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="p-1 border border-blue-200"><code>paypal_enabled</code></td><td class="p-1 border border-blue-200">Habilitar pagos PayPal</td><td class="p-1 border border-blue-200">1 (habilitado) / 0 (deshabilitado)</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>paypal_mode</code></td><td class="p-1 border border-blue-200">Modo de operación</td><td class="p-1 border border-blue-200">sandbox / live</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>paypal_client_id</code></td><td class="p-1 border border-blue-200">Client ID de PayPal</td><td class="p-1 border border-blue-200">Tu Client ID de la app PayPal</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>paypal_secret</code></td><td class="p-1 border border-blue-200">Secret Key de PayPal</td><td class="p-1 border border-blue-200">Tu Secret Key de la app PayPal</td></tr>
                        </tbody>
                    </table>
                    <p class="text-xs text-red-700 mt-3 font-semibold">
                        <i class="fas fa-lock mr-1"></i>
                        Las credenciales de PayPal son sensibles. Asegúrate de que el archivo CSV se maneje de forma segura y no quede almacenado en el servidor.
                    </p>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
