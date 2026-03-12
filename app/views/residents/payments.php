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
                            <i class="fas fa-search mr-2"></i>Buscar por Nombre, Teléfono o Propiedad
                        </label>
                        <input type="text" 
                               name="search" 
                               id="searchInput"
                               value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                               placeholder="Escribe el nombre, teléfono o número de propiedad..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Busca por nombre, apellido, número de teléfono o número de propiedad del residente</p>
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
                                            <?php if ($fee['period'] === date('Y-m')): ?>
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
                                            <?php endif; ?>
                                            <button type="button"
                                               data-property-id="<?php echo (int)$fee['property_id']; ?>"
                                               data-property="<?php echo htmlspecialchars($fee['property_number'], ENT_QUOTES); ?>"
                                               data-resident="<?php echo htmlspecialchars(trim($fee['first_name'] . ' ' . $fee['last_name']), ENT_QUOTES); ?>"
                                               data-amount="<?php echo (float)$fee['amount']; ?>"
                                               onclick="openUpcomingMonthsModal(this)"
                                               title="Pagar Meses Próximos"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 transition-colors">
                                                <i class="fas fa-calendar-alt text-sm"></i>
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
                                            <button type="button"
                                               data-property-id="<?php echo (int)$fee['property_id']; ?>"
                                               data-property="<?php echo htmlspecialchars($fee['property_number'], ENT_QUOTES); ?>"
                                               data-resident="<?php echo htmlspecialchars(trim($fee['first_name'] . ' ' . $fee['last_name']), ENT_QUOTES); ?>"
                                               data-amount="<?php echo (float)$fee['amount']; ?>"
                                               onclick="openUpcomingMonthsModal(this)"
                                               title="Pagar Meses Próximos"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 hover:bg-purple-200 transition-colors">
                                                <i class="fas fa-calendar-alt text-sm"></i>
                                            </button>
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

<!-- Upcoming Months Modal -->
<div id="upcomingMonthsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-calendar-alt text-purple-600 mr-2"></i>Próximos 12 Meses</h3>
            <button type="button" onclick="closeUpcomingMonthsModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="upcomingModalInfo" class="px-6 py-3 bg-blue-50 border-b border-blue-100 text-sm text-blue-700"></div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-4">Selecciona uno o más meses para registrar el pago:</p>
            <div id="monthsGrid" class="grid grid-cols-3 gap-3"></div>
            <div id="selectedMonthsSummary" class="hidden mt-4 p-3 bg-purple-50 border border-purple-200 rounded">
                <p class="text-sm text-gray-700 mb-1">Meses seleccionados: <span id="selectedCount" class="font-bold">0</span></p>
                <p class="text-sm text-gray-900 font-semibold">Total: $<span id="selectedTotal">0.00</span></p>
            </div>
            <div id="upcomingModalLoading" class="hidden text-center py-4">
                <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                <p class="text-sm text-gray-500 mt-2">Cargando meses...</p>
            </div>
            <div id="upcomingModalError" class="hidden mt-3 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded"></div>
        </div>
        <div class="p-4 border-t border-gray-200 flex justify-between items-center">
            <button type="button" onclick="closeUpcomingMonthsModal()"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                Cancelar
            </button>
            <button type="button" id="paySelectedBtn" onclick="paySelectedMonths()" disabled
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                <i class="fas fa-credit-card mr-2"></i>Pagar Seleccionados
            </button>
        </div>
    </div>
</div>

<!-- Payment Registration Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900"><i class="fas fa-dollar-sign text-green-600 mr-2"></i>Registrar Pago - Cuota de Mantenimiento</h3>
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

<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-[9999] space-y-2"></div>

<style>
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.toast-enter {
    animation: slideInRight 0.3s ease-out forwards;
}

.toast-exit {
    animation: slideOutRight 0.3s ease-in forwards;
}
</style>

<script>
/**
 * Show a toast notification
 * @param {string} message - The message to display
 * @param {string} type - Type of toast: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duration in milliseconds (default: 4000)
 */
function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    
    // Set toast ID
    const toastId = 'toast-' + Date.now();
    toast.id = toastId;
    
    // Base classes
    toast.className = 'flex items-start p-4 rounded-lg shadow-lg min-w-[300px] max-w-md toast-enter';
    
    // Type-specific styles
    const typeStyles = {
        success: {
            bg: 'bg-green-50 border border-green-200',
            icon: 'fas fa-check-circle text-green-500',
            text: 'text-green-800'
        },
        error: {
            bg: 'bg-red-50 border border-red-200',
            icon: 'fas fa-exclamation-circle text-red-500',
            text: 'text-red-800'
        },
        warning: {
            bg: 'bg-yellow-50 border border-yellow-200',
            icon: 'fas fa-exclamation-triangle text-yellow-500',
            text: 'text-yellow-800'
        },
        info: {
            bg: 'bg-blue-50 border border-blue-200',
            icon: 'fas fa-info-circle text-blue-500',
            text: 'text-blue-800'
        }
    };
    
    const style = typeStyles[type] || typeStyles.info;
    toast.className += ' ' + style.bg;
    
    // Toast content
    toast.innerHTML = `
        <div class="flex-shrink-0">
            <i class="${style.icon} text-xl"></i>
        </div>
        <div class="ml-3 flex-1">
            <p class="${style.text} text-sm font-medium">${message}</p>
        </div>
        <button onclick="closeToast('${toastId}')" class="ml-3 flex-shrink-0 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto-remove after duration
    setTimeout(() => {
        closeToast(toastId);
    }, duration);
}

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.remove('toast-enter');
        toast.classList.add('toast-exit');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

function openPaymentModal(btn) {
    var feeId = btn.getAttribute('data-fee-id');
    var propertyNumber = btn.getAttribute('data-property');
    var residentName = btn.getAttribute('data-resident');
    var period = btn.getAttribute('data-period');
    var amount = parseFloat(btn.getAttribute('data-amount'));

    document.getElementById('modalFeeId').value = feeId;
    
    // Actualizar título del modal para pago simple
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-dollar-sign text-green-600 mr-2"></i>Registrar Pago - Cuota de Mantenimiento';

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
    const feeIdValue = formData.get('fee_id');
    
    // Detectar si es pago múltiple (fee_id es un array JSON) o simple
    let isMultiple = false;
    try {
        const parsed = JSON.parse(feeIdValue);
        if (Array.isArray(parsed)) {
            isMultiple = true;
            formData.set('fee_ids', feeIdValue); // Renombrar para el endpoint múltiple
            formData.delete('fee_id');
        }
    } catch (e) {
        // Si no es JSON válido, es un pago simple
    }
    
    const endpoint = isMultiple 
        ? '<?php echo BASE_URL; ?>/residents/registerMultipleFeePayments'
        : '<?php echo BASE_URL; ?>/residents/registerFeePayment';
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closePaymentModal();
            
            // Determinar mensaje según tipo de pago
            let message = '';
            if (isMultiple && data.periods && data.periods.length > 0) {
                const count = data.periods.length;
                const total = data.total_amount ? '$' + parseFloat(data.total_amount).toFixed(2) : '';
                message = `✅ ${count} ${count === 1 ? 'pago registrado' : 'pagos registrados'} exitosamente ${total ? '(Total: ' + total + ')' : ''}`;
            } else {
                message = '✅ Pago registrado exitosamente';
            }
            
            showToast(message, 'success', 4000);
            
            // Recargar después de 2 segundos para que el usuario vea el toast
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            errorDiv.textContent = data.message || 'Error al registrar el pago';
            errorDiv.classList.remove('hidden');
            showToast(data.message || 'Error al registrar el pago', 'error', 5000);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check mr-2"></i>Registrar Pago';
        }
    })
    .catch(() => {
        errorDiv.textContent = 'Error de conexión. Intente nuevamente.';
        errorDiv.classList.remove('hidden');
        showToast('Error de conexión. Intente nuevamente.', 'error', 5000);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>Registrar Pago';
    });
});

var _upcomingPropertyId = null;
var _upcomingPropertyNumber = null;
var _upcomingResidentName = null;
var _upcomingAmount = null;
var _monthsData = {};
var _selectedMonths = [];

function openUpcomingMonthsModal(btn) {
    _upcomingPropertyId = btn.getAttribute('data-property-id');
    _upcomingPropertyNumber = btn.getAttribute('data-property');
    _upcomingResidentName = btn.getAttribute('data-resident');
    _upcomingAmount = parseFloat(btn.getAttribute('data-amount'));
    _selectedMonths = [];

    var info = document.getElementById('upcomingModalInfo');
    var b1 = document.createElement('strong');
    b1.textContent = 'Propiedad: ';
    var b2 = document.createElement('strong');
    b2.textContent = 'Residente: ';
    info.innerHTML = '';
    info.appendChild(b1);
    info.appendChild(document.createTextNode(_upcomingPropertyNumber));
    info.appendChild(document.createTextNode(' \u00a0|\u00a0 '));
    info.appendChild(b2);
    info.appendChild(document.createTextNode(_upcomingResidentName));

    document.getElementById('upcomingModalError').classList.add('hidden');
    document.getElementById('selectedMonthsSummary').classList.add('hidden');
    loadMonthsStatus();
    document.getElementById('upcomingMonthsModal').classList.remove('hidden');
}

function closeUpcomingMonthsModal() {
    document.getElementById('upcomingMonthsModal').classList.add('hidden');
    _upcomingPropertyId = null;
    _selectedMonths = [];
    _monthsData = {};
}

function viewPaidFeeDetail(feeId) {
    if (!feeId) {
        showToast('No se encontr\u00f3 el ID de la cuota', 'error', 3000);
        return;
    }
    // Abrir en nueva pesta\u00f1a o en la misma ventana
    window.location.href = '<?php echo BASE_URL; ?>/residents/viewFeePayment/' + feeId;
}

function loadMonthsStatus() {
    var loading = document.getElementById('upcomingModalLoading');
    var grid = document.getElementById('monthsGrid');
    var errorDiv = document.getElementById('upcomingModalError');
    
    loading.classList.remove('hidden');
    grid.innerHTML = '';
    errorDiv.classList.add('hidden');
    
    var formData = new FormData();
    formData.append('property_id', _upcomingPropertyId);
    
    fetch('<?php echo BASE_URL; ?>/residents/getUpcomingMonthsStatus', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        loading.classList.add('hidden');
        if (data.success) {
            _monthsData = {};
            data.months.forEach(m => {
                _monthsData[m.period] = m;
            });
            generateMonthsGrid(data.months);
        } else {
            errorDiv.textContent = data.message || 'Error al cargar los meses';
            errorDiv.classList.remove('hidden');
        }
    })
    .catch(() => {
        loading.classList.add('hidden');
        errorDiv.textContent = 'Error de conexión';
        errorDiv.classList.remove('hidden');
    });
}

function generateMonthsGrid(monthsData) {
    var grid = document.getElementById('monthsGrid');
    grid.innerHTML = '';
    var monthNames = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

    monthsData.forEach((monthData, idx) => {
        var period = monthData.period;
        var [year, month] = period.split('-');
        var monthIdx = parseInt(month) - 1;
        var isPaid = monthData.status === 'paid';
        var isCurrent = monthData.isCurrent;
        
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.dataset.period = period;
        btn.dataset.feeId = monthData.feeId || '';
        btn.dataset.amount = monthData.amount || _upcomingAmount;
        btn.dataset.status = monthData.status;
        
        var baseClass = 'py-3 px-2 text-sm rounded-lg border-2 transition-all text-center ';
        
        if (isPaid) {
            // Mes pagado - verde suave, clic para ver detalle
            btn.className = baseClass + 'border-green-300 bg-green-50 text-green-700 hover:border-green-500 hover:bg-green-100 cursor-pointer';
            btn.innerHTML = `<div class="font-semibold">${monthNames[monthIdx]}</div>` +
                          `<div class="text-xs">${year}</div>` +
                          `<div class="text-xs mt-1"><i class="fas fa-check-circle"></i> Pagado</div>` +
                          `<div class="text-xs text-green-600"><i class="fas fa-eye"></i></div>`;
            btn.addEventListener('click', function() { viewPaidFeeDetail(monthData.feeId); });
        } else {
            // Mes no pagado - seleccionable
            if (isCurrent) {
                // Mes actual - azul suave
                btn.className = baseClass + 'border-blue-400 bg-blue-50 hover:border-blue-600 hover:bg-blue-100 cursor-pointer';
                btn.innerHTML = `<div class="font-semibold">${monthNames[monthIdx]}</div>` +
                              `<div class="text-xs">${year}</div>` +
                              `<div class="text-xs text-blue-600 mt-1">Actual</div>`;
            } else {
                // Meses futuros - gris
                btn.className = baseClass + 'border-gray-200 hover:border-purple-400 hover:bg-purple-50 cursor-pointer';
                btn.innerHTML = `<div class="font-semibold">${monthNames[monthIdx]}</div>` +
                              `<div class="text-xs text-gray-500">${year}</div>`;
            }
            btn.addEventListener('click', function() { toggleMonthSelection(this); });
        }
        
        grid.appendChild(btn);
    });
}

function toggleMonthSelection(btn) {
    var period = btn.dataset.period;
    var feeId = btn.dataset.feeId;
    var amount = parseFloat(btn.dataset.amount);
    var monthData = _monthsData[period];
    
    var idx = _selectedMonths.findIndex(m => m.period === period);
    
    if (idx >= 0) {
        // Deseleccionar
        _selectedMonths.splice(idx, 1);
        btn.classList.remove('border-purple-600', 'bg-purple-100', 'ring-2', 'ring-purple-400');
    } else {
        // Seleccionar
        _selectedMonths.push({
            period: period,
            feeId: feeId || null,
            amount: amount,
            needsCreation: !feeId
        });
        btn.classList.add('border-purple-600', 'bg-purple-100', 'ring-2', 'ring-purple-400');
    }
    
    updateSelectedSummary();
}

function updateSelectedSummary() {
    var summary = document.getElementById('selectedMonthsSummary');
    var countSpan = document.getElementById('selectedCount');
    var totalSpan = document.getElementById('selectedTotal');
    var payBtn = document.getElementById('paySelectedBtn');
    
    if (_selectedMonths.length === 0) {
        summary.classList.add('hidden');
        payBtn.disabled = true;
    } else {
        summary.classList.remove('hidden');
        payBtn.disabled = false;
        countSpan.textContent = _selectedMonths.length;
        var total = _selectedMonths.reduce((sum, m) => sum + m.amount, 0);
        totalSpan.textContent = total.toFixed(2);
    }
}

function paySelectedMonths() {
    // Validar que haya meses seleccionados
    if (!_selectedMonths || _selectedMonths.length === 0) {
        showToast('Debe seleccionar al menos un mes', 'warning', 3000);
        return;
    }
    
    var loading = document.getElementById('upcomingModalLoading');
    var grid = document.getElementById('monthsGrid');
    var errorDiv = document.getElementById('upcomingModalError');
    
    loading.classList.remove('hidden');
    grid.style.opacity = '0.5';
    grid.style.pointerEvents = 'none';
    errorDiv.classList.add('hidden');
    
    // Crear fees para los meses que no tienen ID (necesitan creación)
    var needCreation = _selectedMonths.filter(m => m.needsCreation);
    
    // Mostrar toast informativo si hay meses que necesitan creación
    if (needCreation.length > 0) {
        showToast(`Preparando ${needCreation.length} ${needCreation.length === 1 ? 'cuota' : 'cuotas'}...`, 'info', 3000);
    }
    
    console.log('Meses seleccionados:', _selectedMonths.length);
    console.log('Meses que necesitan creación:', needCreation.length);
    
    var promises = needCreation.map(m => {
        var fd = new FormData();
        fd.append('property_id', _upcomingPropertyId);
        fd.append('period', m.period);
        return fetch('<?php echo BASE_URL; ?>/residents/getOrCreateFeeForPeriod', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.fee && data.fee.id) {
                m.feeId = data.fee.id;
                console.log('Fee creada/obtenida para período ' + m.period + ': ID=' + m.feeId);
                return true;
            }
            console.error('Error creando fee para período ' + m.period + ':', data);
            return false;
        })
        .catch(err => {
            console.error('Error de red al crear fee para período ' + m.period + ':', err);
            return false;
        });
    });
    
    Promise.all(promises)
    .then(() => {
        // Filtrar y validar que todos los meses tengan feeId válido
        var feeIds = _selectedMonths.map(m => m.feeId).filter(id => id != null && id !== '');
        
        if (feeIds.length === 0 || feeIds.length !== _selectedMonths.length) {
            throw new Error('No se pudieron crear todas las cuotas necesarias');
        }
        
        // Verificar que no haya valores null o undefined
        var allHaveIds = _selectedMonths.every(m => m.feeId && m.feeId !== '');
        if (!allHaveIds) {
            throw new Error('Algunas cuotas no tienen ID válido');
        }
        
        // Preparar datos ANTES de cerrar el modal (que limpia _selectedMonths)
        var periods = _selectedMonths.map(m => m.period).join(', ');
        var total = _selectedMonths.reduce((sum, m) => sum + m.amount, 0);
        var periodsCount = _selectedMonths.length;
        
        // Abrir modal de pago con múltiples fees
        loading.classList.add('hidden');
        grid.style.opacity = '';
        grid.style.pointerEvents = '';
        closeUpcomingMonthsModal();
        
        openMultiplePaymentModal({
            feeIds: feeIds,
            periods: periods,
            periodsCount: periodsCount,
            totalAmount: total,
            property: _upcomingPropertyNumber,
            resident: _upcomingResidentName
        });
    })
    .catch(err => {
        loading.classList.add('hidden');
        grid.style.opacity = '';
        grid.style.pointerEvents = '';
        console.error('Error en paySelectedMonths:', err);
        const errorMessage = err.message || 'Error al preparar el pago múltiple. Revise la consola para más detalles.';
        errorDiv.textContent = errorMessage;
        errorDiv.classList.remove('hidden');
        showToast(errorMessage, 'error', 5000);
    });
}

function openMultiplePaymentModal(data) {
    // Validar que los datos sean correctos
    if (!data || !data.feeIds || !Array.isArray(data.feeIds) || data.feeIds.length === 0) {
        console.error('openMultiplePaymentModal: feeIds inválidos', data);
        showToast('Error: No se pudieron cargar los datos de los pagos seleccionados', 'error', 5000);
        return;
    }
    
    // Filtrar valores null/undefined
    var validFeeIds = data.feeIds.filter(id => id != null && id !== '');
    if (validFeeIds.length === 0) {
        console.error('openMultiplePaymentModal: no hay feeIds válidos', data.feeIds);
        showToast('Error: No hay cuotas válidas para procesar', 'error', 5000);
        return;
    }
    
    var modal = document.getElementById('paymentModal');
    var form = document.getElementById('paymentForm');
    var info = document.getElementById('modalInfo');
    var feeIdInput = document.getElementById('modalFeeId');
    var descInput = document.getElementById('modalDescription');
    var modalTitle = document.getElementById('modalTitle');
    
    // Usar el campo fee_id para almacenar el array como JSON
    feeIdInput.value = JSON.stringify(validFeeIds);
    
    // Actualizar título del modal
    var titleText = validFeeIds.length === 1 
        ? 'Registrar Pago - Cuota de Mantenimiento'
        : `Registrar Pago - ${data.periodsCount} Meses de Mantenimiento`;
    modalTitle.innerHTML = '<i class="fas fa-dollar-sign text-green-600 mr-2"></i>' + titleText;
    
    info.innerHTML = '';
    info.appendChild(Object.assign(document.createElement('span'), {innerHTML: '<strong>Propiedad:</strong> '}));
    info.appendChild(document.createTextNode(data.property || 'N/A'));
    info.appendChild(document.createTextNode(' \u00a0|\u00a0 '));
    info.appendChild(Object.assign(document.createElement('span'), {innerHTML: '<strong>Residente:</strong> '}));
    info.appendChild(document.createTextNode(data.resident || 'N/A'));
    info.appendChild(document.createElement('br'));
    info.appendChild(Object.assign(document.createElement('span'), {innerHTML: '<strong>Períodos:</strong> '}));
    info.appendChild(document.createTextNode(data.periods || 'N/A'));
    info.appendChild(document.createTextNode(' \u00a0|\u00a0 '));
    info.appendChild(Object.assign(document.createElement('span'), {innerHTML: '<strong>Total:</strong> $'}));
    info.appendChild(document.createTextNode((data.totalAmount || 0).toFixed(2)));
    
    descInput.value = 'Pago de cuotas de mantenimiento - ' + (data.periods || '');
    document.getElementById('modalError').classList.add('hidden');
    modal.classList.remove('hidden');
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
