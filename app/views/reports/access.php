<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">🚪 Reporte de Accesos</h1>
                <p class="text-gray-600 mt-1">Historial de entradas y salidas del residencial</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/reports/access" class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-filter mr-2"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Total Entradas</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $summary['total_entries']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Residentes Únicos</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $summary['total_residents']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Visitantes Únicos</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $summary['total_visitors']; ?></p>
                </div>
            </div>

            <!-- Daily Data -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-900">Accesos Diarios</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Entradas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residentes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Visitantes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($access_data as $day): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo date('d/m/Y', strtotime($day['date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $day['total_entries']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                    <?php echo $day['unique_residents']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600">
                                    <?php echo $day['unique_visitors']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end space-x-4">
                <a href="<?php echo BASE_URL; ?>/reports" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Volver a Reportes
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-print mr-2"></i> Imprimir
                </button>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
