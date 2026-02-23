<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">💳 Pagos y Cuotas</h1>
                    <p class="text-gray-600 mt-1">Estado de pagos de mantenimiento</p>
                </div>
                <div class="flex space-x-2">
                    <a href="<?php echo BASE_URL; ?>/financial/create" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Nuevo Pago
                    </a>
                    <a href="<?php echo BASE_URL; ?>/residents" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Residentes
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/residents/payments" class="space-y-4">
                    <!-- Search Bar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2"></i>Buscar por Nombre o Teléfono
                        </label>
                        <input type="text" 
                               name="search" 
                               id="searchInput"
                               value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                               placeholder="Escribe el nombre del residente o teléfono..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Busca por nombre, apellido o número de teléfono del residente</p>
                    </div>
                    
                    <!-- Other Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="paid" <?php echo $filters['status'] === 'paid' ? 'selected' : ''; ?>>Pagado</option>
                                <option value="overdue" <?php echo $filters['status'] === 'overdue' ? 'selected' : ''; ?>>Vencido</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                            <input type="date" name="date_from" value="<?php echo $filters['date_from']; ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                            <input type="date" name="date_to" value="<?php echo $filters['date_to']; ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-filter mr-2"></i> Filtrar
                            </button>
                            <a href="<?php echo BASE_URL; ?>/residents/payments" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Payments Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periodo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($fees)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>No hay registros de pagos</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fees as $fee): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo $fee['property_number']; ?>
                                        <?php if ($fee['section']): ?>
                                            <span class="text-xs text-gray-500 block"><?php echo $fee['section']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        if (!empty($fee['first_name']) || !empty($fee['last_name'])) {
                                            echo htmlspecialchars($fee['first_name'] . ' ' . $fee['last_name']); 
                                        } else {
                                            echo '<span class="text-gray-400">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo !empty($fee['phone']) ? htmlspecialchars($fee['phone']) : '<span class="text-gray-400">-</span>'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('M Y', strtotime($fee['period'] . '-01')); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        $<?php echo number_format($fee['amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($fee['due_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?php 
                                            echo match($fee['status']) {
                                                'paid' => 'bg-green-100 text-green-800',
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'overdue' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?php 
                                            echo match($fee['status']) {
                                                'paid' => 'Pagado',
                                                'pending' => 'Pendiente',
                                                'overdue' => 'Vencido',
                                                default => ucfirst($fee['status'])
                                            };
                                            ?>
                                        </span>
                                        <?php if (isset($fee['paid_date']) && $fee['paid_date']): ?>
                                            <span class="text-xs text-gray-500 block mt-1"><?php echo date('d/m/Y', strtotime($fee['paid_date'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-2">
                                        <?php if ($fee['status'] !== 'paid'): ?>
                                            <button type="button"
                                               data-fee-id="<?php echo (int)$fee['id']; ?>"
                                               data-property="<?php echo htmlspecialchars($fee['property_number'], ENT_QUOTES); ?>"
                                               data-resident="<?php echo htmlspecialchars(trim($fee['first_name'] . ' ' . $fee['last_name']), ENT_QUOTES); ?>"
                                               data-period="<?php echo htmlspecialchars($fee['period'], ENT_QUOTES); ?>"
                                               data-amount="<?php echo (float)$fee['amount']; ?>"
                                               onclick="openPaymentModal(this)"
                                               title="Registrar Pago"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition-colors">
                                                <i class="fas fa-dollar-sign text-sm"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_URL; ?>/residents/viewFeePayment/<?php echo (int)$fee['id']; ?>"
                                               title="Ver Pago"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors">
                                                <i class="fas fa-eye text-sm"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/residents/editFeePayment/<?php echo (int)$fee['id']; ?>"
                                               title="Editar Pago"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition-colors">
                                                <i class="fas fa-edit text-sm"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/residents/printFeePayment/<?php echo (int)$fee['id']; ?>"
                                               target="_blank"
                                               title="Imprimir Recibo"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                                <i class="fas fa-print text-sm"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/residents/deleteFeePayment/<?php echo (int)$fee['id']; ?>"
                                               onclick="return confirm('¿Está seguro de eliminar este pago? La cuota volverá a estado pendiente/vencido.')"
                                               title="Eliminar Pago"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors">
                                                <i class="fas fa-trash text-sm"></i>
                                            </a>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrando página <?php echo $page; ?> de <?php echo $total_pages; ?> (Total: <?php echo $total; ?> registros)
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo BASE_URL; ?>/residents/payments?page=<?php echo ($page - 1); ?><?php echo !empty($filters['status']) ? '&status=' . $filters['status'] : ''; ?>&date_from=<?php echo $filters['date_from']; ?>&date_to=<?php echo $filters['date_to']; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?>" 
                               class="px-3 py-1 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="<?php echo BASE_URL; ?>/residents/payments?page=<?php echo $i; ?><?php echo !empty($filters['status']) ? '&status=' . $filters['status'] : ''; ?>&date_from=<?php echo $filters['date_from']; ?>&date_to=<?php echo $filters['date_to']; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?>" 
                               class="px-3 py-1 border <?php echo $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50'; ?> rounded">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo BASE_URL; ?>/residents/payments?page=<?php echo ($page + 1); ?><?php echo !empty($filters['status']) ? '&status=' . $filters['status'] : ''; ?>&date_from=<?php echo $filters['date_from']; ?>&date_to=<?php echo $filters['date_to']; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?>" 
                               class="px-3 py-1 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Summary -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Total Registros</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total']; ?></p>
                    <p class="text-sm text-gray-500 mt-1">$<?php echo number_format($stats['total_amount'] ?? 0, 2); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Pagos Realizados</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['paid']; ?></p>
                    <p class="text-sm text-gray-500 mt-1">$<?php echo number_format($stats['paid_amount'] ?? 0, 2); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Pagos Pendientes</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                    <p class="text-sm text-gray-500 mt-1">$<?php echo number_format($stats['pending_amount'] ?? 0, 2); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Pagos Vencidos</p>
                    <p class="text-3xl font-bold text-red-600"><?php echo $stats['overdue']; ?></p>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Payment Registration Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-dollar-sign text-green-600 mr-2"></i>Registrar Pago - Cuota de Mantenimiento</h3>
            <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="modalInfo" class="px-6 py-3 bg-blue-50 border-b border-blue-100 text-sm text-blue-700"></div>
        <form id="paymentForm" enctype="multipart/form-data">
            <input type="hidden" name="fee_id" id="modalFeeId">
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha <span class="text-red-500">*</span></label>
                        <input type="date" name="transaction_date" id="modalDate" required
                               value="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago <span class="text-red-500">*</span></label>
                        <select name="payment_method" id="modalPaymentMethod" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccionar...</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="paypal">PayPal</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <input type="text" name="description" id="modalDescription"
                           placeholder="Descripción del pago..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Referencia</label>
                    <input type="text" name="payment_reference" id="modalReference"
                           placeholder="Número de referencia o folio..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adjuntar Evidencia</label>
                    <input type="file" name="evidence" id="modalEvidence" accept=".jpg,.jpeg,.png,.gif,.pdf"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF o PDF. Máximo 5MB.</p>
                </div>
            </div>
            <div id="modalError" class="hidden mx-6 mb-2 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded"></div>
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closePaymentModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancelar
                </button>
                <button type="submit" id="modalSubmitBtn"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                    <i class="fas fa-check mr-2"></i>Registrar Pago
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal(btn) {
    var feeId = btn.getAttribute('data-fee-id');
    var propertyNumber = btn.getAttribute('data-property');
    var residentName = btn.getAttribute('data-resident');
    var period = btn.getAttribute('data-period');
    var amount = parseFloat(btn.getAttribute('data-amount'));

    document.getElementById('modalFeeId').value = feeId;

    var info = document.getElementById('modalInfo');
    // Use textContent for individual parts to avoid XSS
    info.innerHTML = '';
    info.appendChild(Object.assign(document.createElement('span'), {innerHTML: '<strong>Propiedad:</strong> '}));
    info.appendChild(document.createTextNode(propertyNumber));
    info.appendChild(document.createTextNode(' \u00a0|\u00a0 '));
    info.appendChild(Object.assign(document.createElement('span'), {innerHTML: '<strong>Residente:</strong> '}));
    info.appendChild(document.createTextNode(residentName));
    info.appendChild(document.createTextNode(' \u00a0|\u00a0 '));
    info.appendChild(Object.assign(document.createElement('span'), {innerHTML: '<strong>Periodo:</strong> '}));
    info.appendChild(document.createTextNode(period));
    info.appendChild(document.createTextNode(' \u00a0|\u00a0 '));
    info.appendChild(Object.assign(document.createElement('span'), {innerHTML: '<strong>Monto:</strong> $'}));
    info.appendChild(document.createTextNode(amount.toFixed(2)));

    document.getElementById('modalDescription').value = 'Pago de cuota de mantenimiento - ' + period;
    document.getElementById('modalError').classList.add('hidden');
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentForm').reset();
    document.getElementById('modalDate').value = new Date().toISOString().split('T')[0];
}

document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('modalSubmitBtn');
    const errorDiv = document.getElementById('modalError');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
    errorDiv.classList.add('hidden');

    const formData = new FormData(this);
    fetch('<?php echo BASE_URL; ?>/residents/registerFeePayment', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closePaymentModal();
            location.reload();
        } else {
            errorDiv.textContent = data.message || 'Error al registrar el pago';
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check mr-2"></i>Registrar Pago';
        }
    })
    .catch(() => {
        errorDiv.textContent = 'Error de conexión. Intente nuevamente.';
        errorDiv.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>Registrar Pago';
    });
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
