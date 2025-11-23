<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">💰 Reporte Financiero</h1>
                <p class="text-gray-600 mt-1">Estado de pagos y cuotas de mantenimiento</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Total Pagado</p>
                    <p class="text-3xl font-bold text-green-600">$<?php echo number_format($summary['total_paid'], 2); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Total Pendiente</p>
                    <p class="text-3xl font-bold text-yellow-600">$<?php echo number_format($summary['total_pending'], 2); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Total Vencido</p>
                    <p class="text-3xl font-bold text-red-600">$<?php echo number_format($summary['total_overdue'], 2); ?></p>
                </div>
            </div>

            <!-- Monthly Data -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-900">Datos Mensuales (Últimos 12 Meses)</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pagado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pendiente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cuotas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($monthly_data as $month): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo date('F Y', strtotime($month['period'] . '-01')); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                    $<?php echo number_format($month['paid'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                    $<?php echo number_format($month['pending'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                    $<?php echo number_format($month['overdue'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $month['total']; ?>
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
