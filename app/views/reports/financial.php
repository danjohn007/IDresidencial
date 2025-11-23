<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">💰 Reporte Financiero</h1>
                <p class="text-gray-600 mt-1">Estado financiero del periodo seleccionado</p>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/reports/financial" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Ingresos Totales</p>
                    <p class="text-3xl font-bold text-green-600">$<?php echo number_format($stats['total_income'] ?? 0, 2); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Egresos Totales</p>
                    <p class="text-3xl font-bold text-red-600">$<?php echo number_format($stats['total_expense'] ?? 0, 2); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Balance</p>
                    <p class="text-3xl font-bold <?php echo ($stats['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                        $<?php echo number_format($stats['balance'] ?? 0, 2); ?>
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Movimientos</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo count($movements); ?></p>
                </div>
            </div>

            <!-- Tabla de Movimientos -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($movements as $movement): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('d/m/Y', strtotime($movement['transaction_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo $movement['transaction_type'] === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                    ?>">
                                        <?php echo $movement['transaction_type'] === 'income' ? 'Ingreso' : 'Egreso'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($movement['description']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium <?php 
                                    echo $movement['transaction_type'] === 'income' ? 'text-green-600' : 'text-red-600';
                                ?>">
                                    <?php echo $movement['transaction_type'] === 'income' ? '+' : '-'; ?>$<?php echo number_format($movement['amount'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
