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
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
