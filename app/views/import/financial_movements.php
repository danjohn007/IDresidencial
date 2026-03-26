<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📥 Importar Movimientos Financieros</h1>
                    <p class="text-gray-600 mt-1">Importar ingresos y egresos desde archivo CSV</p>
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
                            <a href="<?php echo BASE_URL; ?>/import/downloadTemplate/financialMovements"
                               class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-file-download mr-2"></i> Descargar Plantilla
                            </a>
                            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <i class="fas fa-upload mr-2"></i> Importar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-2">Formato del CSV</h4>
                    <p class="text-sm text-blue-800 mb-2">El archivo CSV debe tener las siguientes columnas en orden:</p>
                    <code class="text-xs bg-white p-2 rounded block">movement_type_id,transaction_type,amount,description,payment_method,transaction_date,property_number,notes</code>
                    <ul class="text-xs text-blue-800 mt-3 space-y-1 list-disc list-inside">
                        <li><strong>movement_type_id</strong> – obligatorio; ID numérico <em>o nombre</em> del tipo de movimiento (ej. <code>1</code> o <code>Cuota de Mantenimiento</code>); sin distinción de mayúsculas</li>
                        <li><strong>transaction_type</strong> – obligatorio; valores: <code>ingreso</code>, <code>egreso</code></li>
                        <li><strong>amount</strong> – obligatorio; monto numérico (ej. 1500.00)</li>
                        <li><strong>description</strong> – obligatorio; descripción del movimiento</li>
                        <li><strong>payment_method</strong> – opcional; valores: <code>efectivo</code>, <code>tarjeta</code>, <code>transferencia</code>, <code>paypal</code>, <code>otro</code></li>
                        <li><strong>transaction_date</strong> – obligatorio; fecha en formato YYYY-MM-DD</li>
                        <li><strong>property_number</strong> – opcional; número de propiedad relacionada</li>
                        <li><strong>notes</strong> – opcional; notas adicionales</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
