<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">💳 Realizar Pago</h1>
                    <p class="text-gray-600 mt-1">Paga tu cuota de mantenimiento en línea</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Payment Details -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Detalles del Pago</h2>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between py-2 border-b">
                                    <span class="text-gray-600">Propiedad:</span>
                                    <span class="font-semibold"><?php echo $fee['property_number']; ?></span>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <span class="text-gray-600">Período:</span>
                                    <span class="font-semibold"><?php echo $fee['period']; ?></span>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <span class="text-gray-600">Fecha de Vencimiento:</span>
                                    <span class="font-semibold <?php echo strtotime($fee['due_date']) < time() ? 'text-red-600' : ''; ?>">
                                        <?php echo date('d/m/Y', strtotime($fee['due_date'])); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between py-3 bg-gray-50 px-4 rounded-lg">
                                    <span class="text-lg font-semibold text-gray-900">Monto Total:</span>
                                    <span class="text-2xl font-bold text-green-600">$<?php echo number_format($fee['amount'], 2); ?> MXN</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Método de Pago</h2>
                            
                            <!-- PayPal Button -->
                            <?php if (!empty($paypalSettings['paypal_enabled']) && $paypalSettings['paypal_enabled'] === '1'): ?>
                            <div class="mb-4">
                                <div id="paypal-button-container" class="mb-4"></div>
                            </div>
                            <?php else: ?>
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                                <p class="text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Los pagos en línea no están configurados actualmente.
                                </p>
                            </div>
                            <?php endif; ?>

                            <!-- Other Payment Methods Info -->
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="font-semibold text-gray-900 mb-3">Otras Opciones de Pago:</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="fas fa-building text-blue-600 w-5 mr-2 mt-0.5"></i>
                                        <span>Acudir a administración en horario de oficina</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-university text-blue-600 w-5 mr-2 mt-0.5"></i>
                                        <span>Transferencia bancaria y enviar comprobante</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white sticky top-6">
                            <h3 class="text-lg font-semibold mb-4">Resumen</h3>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-sm">
                                    <span class="opacity-90">Subtotal:</span>
                                    <span class="font-semibold">$<?php echo number_format($fee['amount'], 2); ?></span>
                                </div>
                                <div class="border-t border-blue-400 opacity-50"></div>
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total a Pagar:</span>
                                    <span>$<?php echo number_format($fee['amount'], 2); ?></span>
                                </div>
                            </div>

                            <div class="bg-white bg-opacity-20 rounded-lg p-3 text-sm">
                                <p class="opacity-90">
                                    <i class="fas fa-shield-alt mr-2"></i>
                                    Pago 100% seguro
                                </p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="<?php echo BASE_URL; ?>/residents/myPayments" 
                               class="block w-full px-4 py-2 text-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i> Volver a Mis Pagos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php if (!empty($paypalSettings['paypal_enabled']) && $paypalSettings['paypal_enabled'] === '1'): ?>
<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypalSettings['paypal_client_id'] ?? ''; ?>&currency=MXN"></script>
<script>
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '<?php echo number_format($fee['amount'], 2, '.', ''); ?>'
                    },
                    description: 'Cuota de Mantenimiento - <?php echo $fee['period']; ?>'
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Send payment info to server
                fetch('<?php echo BASE_URL; ?>/residents/processPayment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'fee_id': '<?php echo $fee['id']; ?>',
                        'payment_id': details.id,
                        'payer_id': details.payer.payer_id,
                        'payment_method': 'paypal'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('¡Pago realizado exitosamente!');
                        window.location.href = '<?php echo BASE_URL; ?>/residents/myPayments';
                    } else {
                        alert('Error al procesar el pago: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar el pago');
                });
            });
        },
        onError: function(err) {
            console.error('PayPal error:', err);
            alert('Error al procesar el pago con PayPal');
        }
    }).render('#paypal-button-container');
</script>
<?php endif; ?>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
