<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">💳 Mis Pagos</h1>
                    <p class="text-gray-600 mt-1">Historial de pagos y adeudos de tu propiedad</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Resumen -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Adeudo Pendiente</p>
                                <p class="text-3xl font-bold text-yellow-600">
                                    $<?php echo number_format($summary['pending_amount'] ?? 0, 2); ?>
                                </p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-3">
                                <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Adeudo Vencido</p>
                                <p class="text-3xl font-bold text-red-600">
                                    $<?php echo number_format($summary['overdue_amount'] ?? 0, 2); ?>
                                </p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3">
                                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Pagado</p>
                                <p class="text-3xl font-bold text-green-600">
                                    $<?php echo number_format($summary['paid_amount'] ?? 0, 2); ?>
                                </p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de la propiedad -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-home text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">
                                Propiedad: <strong><?php echo htmlspecialchars($resident['property_number']); ?></strong>
                            </p>
                            <p class="text-xs text-blue-700 mt-1">
                                <?php echo $summary['pending_count'] ?? 0; ?> pago(s) pendiente(s)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Historial de Pagos -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-history text-blue-600 mr-2"></i>
                            Historial de Pagos
                        </h2>
                    </div>
                    
                    <?php if (empty($payments)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No hay pagos registrados</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Vencimiento</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Pago</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($payments as $payment): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($payment['period']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                                $<?php echo number_format($payment['amount'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo date('d/m/Y', strtotime($payment['due_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $statusClasses = [
                                                    'paid' => 'bg-green-100 text-green-800',
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'overdue' => 'bg-red-100 text-red-800',
                                                    'cancelled' => 'bg-gray-100 text-gray-800'
                                                ];
                                                $statusLabels = [
                                                    'paid' => 'Pagado',
                                                    'pending' => 'Pendiente',
                                                    'overdue' => 'Vencido',
                                                    'cancelled' => 'Cancelado'
                                                ];
                                                $statusClass = $statusClasses[$payment['status']] ?? 'bg-gray-100 text-gray-800';
                                                $statusLabel = $statusLabels[$payment['status']] ?? $payment['status'];
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo $statusLabel; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $payment['paid_date'] ? date('d/m/Y', strtotime($payment['paid_date'])) : '-'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <?php if ($payment['status'] !== 'paid'): ?>
                                                    <a href="<?php echo BASE_URL; ?>/residents/makePayment/<?php echo $payment['id']; ?>" 
                                                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                                        <i class="fas fa-credit-card mr-1"></i> Pagar
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-gray-400">
                                                        <i class="fas fa-check-circle"></i> Pagado
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
