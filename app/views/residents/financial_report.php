<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">💰 Mis Ingresos y Egresos</h1>
                <p class="text-gray-600 mt-1">Historial de pagos de tu propiedad</p>
            </div>

            <?php if (!$residentInfo): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                <p class="text-yellow-700">No se encontró información de propiedad asociada a tu cuenta.</p>
            </div>
            <?php else: ?>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600 mb-1">Propiedad</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo htmlspecialchars($residentInfo['property_number']); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600 mb-1">Total Pagado (período)</p>
                    <p class="text-2xl font-bold text-green-600">$<?php echo number_format($totalPaid, 2); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                    <p class="text-sm text-gray-600 mb-1">Total Pendiente</p>
                    <p class="text-2xl font-bold text-red-600">$<?php echo number_format($totalPending, 2); ?></p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/residents/financialReport" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Payments Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No se encontraron pagos en el período seleccionado</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($payment['period']); ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">$<?php echo number_format($payment['amount'], 2); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo $payment['paid_date'] ? date('d/m/Y', strtotime($payment['paid_date'])) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($payment['payment_method'] ?? '-'); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Pagado</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
