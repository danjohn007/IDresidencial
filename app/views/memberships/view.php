<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">💳 Detalle de Membresía</h1>
                        <p class="text-gray-600 mt-1">Información completa de la membresía</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/memberships" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                </div>

                <!-- Información de la Membresía -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200 bg-blue-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">
                                    <?php echo htmlspecialchars($membership['plan_name'] ?? 'Plan'); ?>
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    <?php echo htmlspecialchars($membership['resident_name'] ?? 'N/A'); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 text-sm rounded-full <?php 
                                    echo match($membership['status']) {
                                        'active' => 'bg-green-100 text-green-800',
                                        'expired' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-yellow-100 text-yellow-800'
                                    };
                                ?>">
                                    <?php echo ucfirst($membership['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Inicio</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo date('d/m/Y', strtotime($membership['start_date'])); ?>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Fin</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo $membership['end_date'] ? date('d/m/Y', strtotime($membership['end_date'])) : 'Indefinido'; ?>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Día de Pago</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    Día <?php echo $membership['payment_day']; ?> de cada mes
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Monto</dt>
                                <dd class="mt-1 text-lg font-bold text-blue-600">
                                    $<?php echo number_format($membership['amount'] ?? 0, 2); ?>
                                </dd>
                            </div>

                            <?php if (!empty($membership['notes'])): ?>
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Notas</dt>
                                <dd class="mt-1 text-gray-900">
                                    <?php echo nl2br(htmlspecialchars($membership['notes'])); ?>
                                </dd>
                            </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>

                <!-- Historial de Pagos -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Historial de Pagos</h2>
                    </div>
                    
                    <?php if (!empty($payments)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($payments as $payment): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                                $<?php echo number_format($payment['amount'], 2); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo ucfirst($payment['payment_method']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                                    echo match($payment['status']) {
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'failed' => 'bg-red-100 text-red-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No hay pagos registrados para esta membresía</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
