<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">💳 Cartera Vencida</h1>
                    <p class="text-gray-600 mt-1">Cuotas pendientes y vencidas</p>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="bg-white rounded-lg shadow p-6 mb-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Cartera Vencida</p>
                        <p class="text-3xl font-bold text-red-600">$<?php echo number_format($overallStats['total'], 2); ?></p>
                        <p class="text-sm text-gray-500"><?php echo $overallStats['count']; ?> cuotas pendientes/vencidas</p>
                    </div>
                    <div class="p-4 bg-red-100 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/financial/overdueAccounts" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Propiedad o residente..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Días Vencido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No se encontraron registros</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($records as $record): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($record['property_number']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($record['resident_name'] ?? 'Sin asignar'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($record['resident_phone'] ?? '-'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($record['period']); ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">$<?php echo number_format($record['amount'], 2); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($record['due_date'])); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="<?php echo $record['days_overdue'] > 0 ? 'text-red-600 font-semibold' : 'text-yellow-600'; ?>">
                                    <?php echo max(0, $record['days_overdue']); ?> días
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $record['status'] === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $record['status'] === 'overdue' ? 'Vencido' : 'Pendiente'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center mt-6 space-x-2">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                   class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
