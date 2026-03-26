<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📦 Importación Masiva</h1>
                    <p class="text-gray-600 mt-1">Importar todos los tipos de datos desde un único archivo Excel (.xlsx)</p>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php if (!empty($details)): ?>
                        <div class="mb-4 overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow text-sm">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-700">
                                        <th class="px-4 py-2 text-left">Hoja</th>
                                        <th class="px-4 py-2 text-right">Importados</th>
                                        <th class="px-4 py-2 text-right">Errores</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($details as $d): ?>
                                        <tr class="border-t border-gray-100">
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($d['sheet']); ?></td>
                                            <td class="px-4 py-2 text-right text-green-700 font-semibold"><?php echo (int)$d['imported']; ?></td>
                                            <td class="px-4 py-2 text-right <?php echo $d['errors'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-500'; ?>"><?php echo (int)$d['errors']; ?></td>
                                        </tr>
                                        <?php if (!empty($d['error_details'])): ?>
                                            <tr class="border-t border-yellow-100 bg-yellow-50">
                                                <td colspan="3" class="px-4 py-2">
                                                    <ul class="text-xs text-yellow-800 list-disc list-inside space-y-1">
                                                        <?php foreach ($d['error_details'] as $detail): ?>
                                                            <li><?php echo htmlspecialchars($detail); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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
                                Archivo Excel (.xlsx) <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="xlsx_file" accept=".xlsx" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/import"
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Volver
                            </a>
                            <a href="<?php echo BASE_URL; ?>/import/downloadBulkTemplate"
                               class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-file-excel mr-2"></i> Descargar Plantilla
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-upload mr-2"></i> Importar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Información de hojas -->
                <div class="mt-6 p-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        Hojas incluidas en la plantilla
                    </h4>
                    <p class="text-sm text-blue-800 mb-3">
                        La plantilla Excel contiene una hoja por cada tipo de dato. Llena únicamente las hojas que necesites importar.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-blue-800">
                        <div><span class="font-semibold">Hoja 1 – Residentes:</span> username, email, first_name, last_name, phone, property_number, relationship</div>
                        <div><span class="font-semibold">Hoja 2 – Propiedades:</span> property_number, section, street, property_type, tower, bedrooms, bathrooms, area_m2, status</div>
                        <div><span class="font-semibold">Hoja 3 – Usuarios:</span> username, email, first_name, last_name, phone, role</div>
                        <div><span class="font-semibold">Hoja 4 – Cuotas:</span> property_number, period, amount, due_date, status</div>
                        <div><span class="font-semibold">Hoja 5 – Amenidades:</span> name, amenity_type, description, capacity, hourly_rate, hours_open, hours_close, requires_payment, status</div>
                        <div><span class="font-semibold">Hoja 6 – Movimientos Financieros:</span> movement_type_id (ID numérico o nombre del tipo, sin distinción de mayúsculas), transaction_type, amount, description, payment_method, transaction_date, property_number, notes</div>
                        <div><span class="font-semibold">Hoja 7 – CFDI Config:</span> setting_key, setting_value</div>
                        <div><span class="font-semibold">Hoja 8 – PayPal Config:</span> setting_key, setting_value</div>
                    </div>
                    <p class="text-xs text-blue-700 mt-3">
                        <i class="fas fa-lightbulb mr-1"></i>
                        Se recomienda importar primero Propiedades, después Residentes y finalmente Cuotas de Mantenimiento.
                    </p>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
