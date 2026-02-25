<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">📊 Presupuesto y Proyecciones</h1>
                <p class="text-gray-600 mt-1">Resumen financiero del mes actual y proyecciones</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600 mb-1">Total Cobrado (<?php echo htmlspecialchars($currentMonth); ?>)</p>
                    <p class="text-3xl font-bold text-green-600">$<?php echo number_format($paidCurrent['total'], 2); ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo $paidCurrent['count']; ?> cuotas pagadas</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                    <p class="text-sm text-gray-600 mb-1">Total Pendiente (<?php echo htmlspecialchars($currentMonth); ?>)</p>
                    <p class="text-3xl font-bold text-red-600">$<?php echo number_format($pendingCurrent['total'], 2); ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo $pendingCurrent['count']; ?> cuotas pendientes</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600 mb-1">Proyección Siguiente Mes (promedio 3 meses)</p>
                    <p class="text-3xl font-bold text-blue-600">$<?php echo number_format($avgIncome, 2); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Basado en ingresos de los últimos 3 meses</p>
                </div>
            </div>

            <!-- Next Month Fees -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cuotas Mes Siguiente (<?php echo htmlspecialchars($nextMonth); ?>)</h3>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-2xl font-bold text-purple-600">$<?php echo number_format($nextMonthFees['total'], 2); ?></p>
                            <p class="text-sm text-gray-500"><?php echo $nextMonthFees['count']; ?> cuotas generadas</p>
                        </div>
                        <div class="p-4 bg-purple-100 rounded-full">
                            <i class="fas fa-calendar-alt text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Propiedades Activas</h3>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-2xl font-bold text-gray-700"><?php echo $totalProperties; ?></p>
                            <p class="text-sm text-gray-500">Total de predios registrados</p>
                        </div>
                        <div class="p-4 bg-gray-100 rounded-full">
                            <i class="fas fa-home text-gray-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Month Breakdown -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Desglose de Cuotas - <?php echo htmlspecialchars($currentMonth); ?></h3>
                <?php if (!empty($currentMonthStats)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($currentMonthStats as $stat): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full <?php
                                        echo match($stat['status']) {
                                            'paid' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'overdue' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($stat['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo $stat['count']; ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">$<?php echo number_format($stat['total'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-sm">No hay cuotas generadas para este mes.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
