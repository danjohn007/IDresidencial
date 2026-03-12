<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-file-csv mr-2 text-emerald-600"></i>Importar Residentes desde CSV</h1>
                    <p class="text-gray-600 mt-1">Carga masiva de residentes usando un archivo CSV</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/residents" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a Residentes
                </a>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                <?php foreach ($errors as $err): ?>
                <p class="text-red-700"><i class="fas fa-exclamation-circle mr-1"></i><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($imported > 0 || $skipped > 0): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                <p class="text-green-700 font-semibold">
                    <i class="fas fa-check-circle mr-1"></i>
                    Importación completada: <strong><?php echo $imported; ?></strong> residentes importados,
                    <strong><?php echo $skipped; ?></strong> omitidos.
                </p>
            </div>
            <?php endif; ?>

            <!-- Instructions -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded mb-6">
                <h3 class="font-semibold text-blue-800 mb-2"><i class="fas fa-info-circle mr-1"></i>Instrucciones</h3>
                <ul class="text-blue-700 text-sm space-y-1 list-disc ml-4">
                    <li>El archivo debe ser <strong>CSV</strong> (valores separados por comas).</li>
                    <li>La primera fila debe ser el encabezado con los nombres de columnas.</li>
                    <li>Columnas <strong>requeridas</strong>: <code>nombre, apellido, email, telefono, propiedad, relacion</code></li>
                    <li>Columnas opcionales: <code>seccion, password</code></li>
                    <li>El campo <strong>relacion</strong> acepta: <code>propietario</code>, <code>inquilino</code>, <code>familiar</code></li>
                    <li>Si la propiedad no existe, se creará automáticamente.</li>
                    <li>Los emails duplicados serán omitidos.</li>
                    <li>Si no se provee <strong>password</strong>, se genera una contraseña aleatoria temporal. El residente deberá usar la función de recuperación de contraseña para acceder.</li>
                </ul>
            </div>

            <!-- Download template -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="font-semibold text-gray-800 mb-3"><i class="fas fa-download mr-1 text-emerald-600"></i>Plantilla de Ejemplo</h3>
                <p class="text-sm text-gray-600 mb-3">Descarga esta plantilla CSV para ver el formato correcto:</p>
                <a href="data:text/csv;charset=utf-8,nombre,apellido,email,telefono,propiedad,relacion,seccion,password%0AJuan,Garcia,juan.garcia@example.com,5551234567,A-101,propietario,Sección A,MiPassword123%0AMaría,López,maria.lopez@example.com,5559876543,B-202,inquilino,Sección B,"
                   download="plantilla_residentes.csv"
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">
                    <i class="fas fa-file-csv mr-2"></i>Descargar Plantilla CSV
                </a>
            </div>

            <!-- Upload Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-upload mr-1 text-blue-600"></i>Subir Archivo</h3>
                <form method="POST" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/residents/importExcel"
                      id="importForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar archivo CSV</label>
                        <div class="flex items-center space-x-4">
                            <input type="file" name="excel_file" id="excelFile" accept=".csv,.txt"
                                   required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Máximo 5MB. Solo archivos CSV.</p>
                    </div>
                    <div class="flex space-x-3">
                        <button type="submit" id="submitBtn"
                                class="inline-flex items-center px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium">
                            <i class="fas fa-upload mr-2"></i>Importar Residentes
                        </button>
                        <a href="<?php echo BASE_URL; ?>/residents"
                           class="inline-flex items-center px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <?php if (!empty($results)): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-list mr-1"></i>Resultados de la Importación</h3>
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Línea</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($results as $res): ?>
                            <tr>
                                <td class="px-4 py-2 text-gray-500"><?php echo $res['line']; ?></td>
                                <td class="px-4 py-2">
                                    <?php if ($res['status'] === 'ok'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>OK</span>
                                    <?php elseif ($res['status'] === 'skip'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-minus-circle mr-1"></i>Omitido</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1"></i>Error</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($res['message']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
document.getElementById('importForm').addEventListener('submit', function() {
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importando...';
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
