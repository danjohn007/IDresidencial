<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📥 Importar Amenidades</h1>
                    <p class="text-gray-600 mt-1">Importar amenidades (albercas, salones, canchas, etc.) desde archivo CSV</p>
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
                            <a href="<?php echo BASE_URL; ?>/import/downloadTemplate/amenities"
                               class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-file-download mr-2"></i> Descargar Plantilla
                            </a>
                            <button type="submit" class="px-6 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                                <i class="fas fa-upload mr-2"></i> Importar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-2">Formato del CSV</h4>
                    <p class="text-sm text-blue-800 mb-2">El archivo CSV debe tener las siguientes columnas en orden:</p>
                    <code class="text-xs bg-white p-2 rounded block">name,amenity_type,description,capacity,hourly_rate,hours_open,hours_close,requires_payment,status</code>
                    <ul class="text-xs text-blue-800 mt-3 space-y-1 list-disc list-inside">
                        <li><strong>name</strong> – obligatorio; nombre de la amenidad</li>
                        <li><strong>amenity_type</strong> – obligatorio; valores: <code>salon</code>, <code>alberca</code>, <code>asadores</code>, <code>cancha</code>, <code>gimnasio</code>, <code>otro</code></li>
                        <li><strong>description</strong> – opcional; descripción de la amenidad</li>
                        <li><strong>capacity</strong> – opcional; capacidad en personas (número entero)</li>
                        <li><strong>hourly_rate</strong> – opcional; tarifa por hora (ej. 150.00)</li>
                        <li><strong>hours_open</strong> – opcional; hora de apertura en formato HH:MM (ej. 08:00)</li>
                        <li><strong>hours_close</strong> – opcional; hora de cierre en formato HH:MM (ej. 22:00)</li>
                        <li><strong>requires_payment</strong> – opcional; 1 si requiere pago, 0 si no (por defecto: 0)</li>
                        <li><strong>status</strong> – opcional; valores: <code>active</code>, <code>maintenance</code>, <code>inactive</code> (por defecto: active)</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
