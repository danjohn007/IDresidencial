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
                                        <?php if ($fee['status'] !== 'paid'): ?>
                                            <a href="<?php echo BASE_URL; ?>/financial/create?property_id=<?php echo $fee['property_id'] ?? ''; ?>&amount=<?php echo $fee['amount'] ?? ''; ?>" 
                                               class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-dollar-sign mr-1"></i>Registrar Pago
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
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

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
