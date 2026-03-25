<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📥 Configuración CFDI</h1>
                    <p class="text-gray-600 mt-1">Importar datos de facturación electrónica (RFC, régimen fiscal, etc.) desde archivo CSV</p>
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
                            <a href="<?php echo BASE_URL; ?>/import/downloadTemplate/cfdiConfig"
                               class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-file-download mr-2"></i> Descargar Plantilla
                            </a>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
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
                                <th class="text-left p-1 border border-blue-200">Ejemplo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="p-1 border border-blue-200"><code>cfdi_rfc</code></td><td class="p-1 border border-blue-200">RFC del emisor</td><td class="p-1 border border-blue-200">XAXX010101000</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>cfdi_razon_social</code></td><td class="p-1 border border-blue-200">Razón social / Nombre</td><td class="p-1 border border-blue-200">Mi Residencial SA de CV</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>cfdi_regimen_fiscal</code></td><td class="p-1 border border-blue-200">Clave de régimen fiscal SAT</td><td class="p-1 border border-blue-200">601</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>cfdi_cp</code></td><td class="p-1 border border-blue-200">Código postal del emisor</td><td class="p-1 border border-blue-200">76000</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>cfdi_uso_cfdi</code></td><td class="p-1 border border-blue-200">Clave de uso CFDI</td><td class="p-1 border border-blue-200">G03</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>cfdi_metodo_pago</code></td><td class="p-1 border border-blue-200">Método de pago SAT</td><td class="p-1 border border-blue-200">PUE</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>cfdi_forma_pago</code></td><td class="p-1 border border-blue-200">Forma de pago SAT</td><td class="p-1 border border-blue-200">01</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>cfdi_serie</code></td><td class="p-1 border border-blue-200">Serie de los CFDIs</td><td class="p-1 border border-blue-200">A</td></tr>
                            <tr><td class="p-1 border border-blue-200"><code>cfdi_folio_inicio</code></td><td class="p-1 border border-blue-200">Folio inicial</td><td class="p-1 border border-blue-200">1</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
