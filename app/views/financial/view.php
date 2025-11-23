<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">💰 Detalle de Movimiento</h1>
                        <p class="text-gray-600 mt-1">Información completa del movimiento financiero</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/financial" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200 <?php echo $movement['transaction_type'] === 'ingreso' ? 'bg-green-50' : 'bg-red-50'; ?>">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="px-3 py-1 text-sm rounded-full <?php echo $movement['transaction_type'] === 'ingreso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $movement['transaction_type'] === 'ingreso' ? 'Ingreso' : 'Egreso'; ?>
                                </span>
                                <h2 class="text-2xl font-bold text-gray-900 mt-2">
                                    <?php echo htmlspecialchars($movement['movement_type_name']); ?>
                                </h2>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold <?php echo $movement['transaction_type'] === 'ingreso' ? 'text-green-600' : 'text-red-600'; ?>">
                                    $<?php echo number_format($movement['amount'], 2); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Transacción</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo date('d/m/Y', strtotime($movement['transaction_date'])); ?>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Registrado por</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo htmlspecialchars($movement['created_by_name']); ?>
                                </dd>
                            </div>

                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo htmlspecialchars($movement['description']); ?>
                                </dd>
                            </div>

                            <?php if ($movement['property_number']): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Propiedad</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo htmlspecialchars($movement['property_number']); ?>
                                </dd>
                            </div>
                            <?php endif; ?>

                            <?php if ($movement['payment_method']): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Método de Pago</dt>
                                <dd class="mt-1 text-lg text-gray-900 capitalize">
                                    <?php echo htmlspecialchars($movement['payment_method']); ?>
                                </dd>
                            </div>
                            <?php endif; ?>

                            <?php if ($movement['payment_reference']): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Referencia de Pago</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo htmlspecialchars($movement['payment_reference']); ?>
                                </dd>
                            </div>
                            <?php endif; ?>

                            <?php if ($movement['reference_type']): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tipo de Referencia</dt>
                                <dd class="mt-1 text-lg text-gray-900 capitalize">
                                    <?php echo str_replace('_', ' ', htmlspecialchars($movement['reference_type'])); ?>
                                </dd>
                            </div>
                            <?php endif; ?>

                            <?php if ($movement['notes']): ?>
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Notas</dt>
                                <dd class="mt-1 text-lg text-gray-900">
                                    <?php echo nl2br(htmlspecialchars($movement['notes'])); ?>
                                </dd>
                            </div>
                            <?php endif; ?>

                            <div class="md:col-span-2 pt-4 border-t border-gray-200">
                                <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                                <dd class="mt-1 text-sm text-gray-600">
                                    <?php echo date('d/m/Y H:i:s', strtotime($movement['created_at'])); ?>
                                </dd>
                            </div>
                        </dl>

                        <?php if (!$movement['reference_type']): ?>
                        <div class="mt-6 flex space-x-3">
                            <a href="<?php echo BASE_URL; ?>/financial/edit/<?php echo $movement['id']; ?>" 
                               class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                                <i class="fas fa-edit mr-2"></i> Editar
                            </a>
                            <a href="<?php echo BASE_URL; ?>/financial/delete/<?php echo $movement['id']; ?>" 
                               onclick="return confirm('¿Está seguro de eliminar este movimiento?')"
                               class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <i class="fas fa-trash mr-2"></i> Eliminar
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                Este movimiento fue generado automáticamente y no puede ser editado o eliminado.
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
