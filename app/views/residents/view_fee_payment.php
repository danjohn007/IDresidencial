<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">💰 Detalle de Pago</h1>
                        <p class="text-gray-600 mt-1">Información del pago de cuota de mantenimiento</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/residents/payments"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200 bg-green-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800">Pagado</span>
                                <h2 class="text-2xl font-bold text-gray-900 mt-2">
                                    Cuota de Mantenimiento - <?php echo htmlspecialchars($fee['period']); ?>
                                </h2>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-green-600">$<?php echo number_format($fee['amount'], 2); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Propiedad</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo htmlspecialchars($fee['property_number']); ?>
                                    <?php if ($fee['section']): ?>
                                        <span class="text-sm text-gray-500"> - <?php echo htmlspecialchars($fee['section']); ?></span>
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Residente</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo htmlspecialchars(trim($fee['first_name'] . ' ' . $fee['last_name'])) ?: '-'; ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Pago</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo $fee['paid_date'] ? date('d/m/Y', strtotime($fee['paid_date'])) : '-'; ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Vencimiento</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo date('d/m/Y', strtotime($fee['due_date'])); ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Método de Pago</dt>
                                <dd class="mt-1 text-lg text-gray-900 capitalize">
                                    <?php echo $fee['payment_method'] ? htmlspecialchars($fee['payment_method']) : '-'; ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Referencia</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo $fee['payment_reference'] ? htmlspecialchars($fee['payment_reference']) : '-'; ?>
                                </dd>
                            </div>
                            <?php if ($fee['notes']): ?>
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Notas</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo nl2br(htmlspecialchars($fee['notes'])); ?>
                                </dd>
                            </div>
                            <?php endif; ?>
                            <?php if ($fee['payment_confirmation']): ?>
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Evidencia</dt>
                                <dd class="mt-1">
                                    <?php
                                    $ext = strtolower(pathinfo($fee['payment_confirmation'], PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])):
                                    ?>
                                        <img src="<?php echo BASE_URL . '/public/' . htmlspecialchars($fee['payment_confirmation']); ?>"
                                             alt="Evidencia de pago" class="max-w-xs rounded border border-gray-200">
                                    <?php elseif ($ext === 'pdf'): ?>
                                        <a href="<?php echo BASE_URL . '/public/' . htmlspecialchars($fee['payment_confirmation']); ?>"
                                           target="_blank" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-file-pdf mr-1"></i>Ver PDF
                                        </a>
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <?php endif; ?>
                        </dl>

                        <div class="mt-6 flex space-x-3">
                            <a href="<?php echo BASE_URL; ?>/residents/editFeePayment/<?php echo (int)$fee['id']; ?>"
                               class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                                <i class="fas fa-edit mr-2"></i> Editar
                            </a>
                            <a href="<?php echo BASE_URL; ?>/residents/printFeePayment/<?php echo (int)$fee['id']; ?>"
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                <i class="fas fa-print mr-2"></i> Imprimir
                            </a>
                            <a href="<?php echo BASE_URL; ?>/residents/deleteFeePayment/<?php echo (int)$fee['id']; ?>"
                               onclick="return confirm('¿Está seguro de eliminar este pago?')"
                               class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <i class="fas fa-trash mr-2"></i> Eliminar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Historial de Pagos de esta Propiedad -->
                <?php if (!empty($allFees) && count($allFees) > 1): ?>
                <div class="bg-white rounded-lg shadow overflow-hidden mt-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-history text-blue-600 mr-2"></i>Historial de Cuotas - <?php echo htmlspecialchars($fee['property_number']); ?>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Últimas 24 cuotas de esta propiedad</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Pago</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($allFees as $histFee): ?>
                                <tr class="hover:bg-gray-50 <?php echo $histFee['id'] == $fee['id'] ? 'bg-blue-50' : ''; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($histFee['period']); ?>
                                            <?php if ($histFee['id'] == $fee['id']): ?>
                                                <span class="ml-2 text-xs text-blue-600">(Actual)</span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">$<?php echo number_format($histFee['amount'], 2); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($histFee['due_date'])); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">
                                            <?php echo $histFee['paid_date'] ? date('d/m/Y', strtotime($histFee['paid_date'])) : '-'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status = $histFee['status'];
                                        $statusColors = [
                                            'paid' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'overdue' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusLabels = [
                                            'paid' => 'Pagado',
                                            'pending' => 'Pendiente',
                                            'overdue' => 'Vencido'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 text-xs rounded-full <?php echo $statusColors[$status] ?? 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $statusLabels[$status] ?? ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($histFee['status'] === 'paid' && $histFee['id'] != $fee['id']): ?>
                                            <a href="<?php echo BASE_URL; ?>/residents/viewFeePayment/<?php echo (int)$histFee['id']; ?>"
                                               class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        <?php elseif ($histFee['id'] == $fee['id']): ?>
                                            <span class="text-gray-400"><i class="fas fa-check"></i> Visualizando</span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
