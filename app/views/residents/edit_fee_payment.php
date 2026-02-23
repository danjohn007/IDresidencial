<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-2xl mx-auto">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">✏️ Editar Pago</h1>
                        <p class="text-gray-600 mt-1">Editar información del pago de cuota de mantenimiento</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/residents/payments"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                </div>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="mb-4 p-4 bg-blue-50 rounded-lg text-sm text-blue-700">
                        <strong>Propiedad:</strong> <?php echo htmlspecialchars($fee['property_number']); ?> &nbsp;|&nbsp;
                        <strong>Residente:</strong> <?php echo htmlspecialchars(trim($fee['first_name'] . ' ' . $fee['last_name'])) ?: 'Sin asignar'; ?> &nbsp;|&nbsp;
                        <strong>Periodo:</strong> <?php echo htmlspecialchars($fee['period']); ?> &nbsp;|&nbsp;
                        <strong>Monto:</strong> $<?php echo number_format($fee['amount'], 2); ?>
                    </div>

                    <form method="POST" action="<?php echo BASE_URL; ?>/residents/editFeePayment/<?php echo (int)$fee['id']; ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Pago <span class="text-red-500">*</span></label>
                                <input type="date" name="transaction_date" required
                                       value="<?php echo htmlspecialchars($fee['paid_date'] ?? date('Y-m-d')); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago <span class="text-red-500">*</span></label>
                                <select name="payment_method" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach (['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia', 'paypal' => 'PayPal', 'otro' => 'Otro'] as $val => $label): ?>
                                        <option value="<?php echo $val; ?>" <?php echo $fee['payment_method'] === $val ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Referencia</label>
                            <input type="text" name="payment_reference"
                                   value="<?php echo htmlspecialchars($fee['payment_reference'] ?? ''); ?>"
                                   placeholder="Número de referencia o folio..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea name="notes" rows="3"
                                      placeholder="Notas adicionales..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($fee['notes'] ?? ''); ?></textarea>
                        </div>
                        <div class="flex space-x-3">
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                            <a href="<?php echo BASE_URL; ?>/residents/payments"
                               class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
