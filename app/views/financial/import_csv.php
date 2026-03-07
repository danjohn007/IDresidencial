<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📂 Importar CSV Bancario</h1>
                    <p class="text-gray-600 mt-1">Importar movimientos desde un archivo CSV del banco</p>
                </div>

                <?php if (!empty($error)): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <?php if (empty($preview)): ?>
                <!-- Upload form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Seleccionar archivo CSV</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        El archivo CSV debe tener las columnas: <strong>Fecha, Concepto, Monto, Descripción</strong> (separadas por comas).
                    </p>
                    <form method="POST" action="<?php echo BASE_URL; ?>/financial/importCSV" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Archivo CSV</label>
                            <input type="file" name="csv_file" accept=".csv" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="flex justify-end space-x-3">
                            <a href="<?php echo BASE_URL; ?>/financial" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-upload mr-2"></i>Cargar Archivo
                            </button>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <!-- Preview table -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Vista Previa (<?php echo count($preview); ?> registros)</h3>
                    <form method="POST" action="<?php echo BASE_URL; ?>/financial/importCSV" enctype="multipart/form-data">
                        <input type="hidden" name="confirm_import" value="1">
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <?php if (!empty($headers)): ?>
                                        <?php foreach ($headers as $header): ?>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo htmlspecialchars($header); ?></th>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo de Movimiento</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($preview as $idx => $row): ?>
                                    <tr>
                                        <?php foreach ($row as $cell): ?>
                                        <td class="px-4 py-3 text-gray-900"><?php echo htmlspecialchars($cell); ?></td>
                                        <?php endforeach; ?>
                                        <td class="px-4 py-3">
                                            <select name="movement_type_id_<?php echo $idx; ?>" class="px-2 py-1 border border-gray-300 rounded text-sm">
                                                <?php foreach ($movementTypes as $mt): ?>
                                                <option value="<?php echo $mt['id']; ?>"><?php echo htmlspecialchars($mt['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <a href="<?php echo BASE_URL; ?>/financial/importCSV" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-check mr-2"></i>Confirmar Importación
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
