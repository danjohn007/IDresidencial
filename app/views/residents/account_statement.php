<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-5xl mx-auto">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between no-print">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">📄 Estado de Cuenta</h1>
                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?> — Propiedad: <?php echo htmlspecialchars($resident['property_number']); ?></p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            <i class="fas fa-print mr-2"></i> Imprimir
                        </button>
                        <a href="<?php echo BASE_URL; ?>/residents" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i> Volver
                        </a>
                    </div>
                </div>

                <!-- Date Filters -->
                <div class="bg-white rounded-lg shadow p-4 mb-6 no-print">
                    <form method="GET" action="<?php echo BASE_URL; ?>/residents/accountStatement/<?php echo $resident['id']; ?>" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                            <select name="year" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                            <input type="date" name="date_from" value="<?php echo $date_from; ?>"
                                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                            <input type="date" name="date_to" value="<?php echo $date_to; ?>"
                                   class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-search mr-2"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Print header (only visible on print) -->
                <div class="print-only mb-6">
                    <h2 class="text-2xl font-bold">Estado de Cuenta</h2>
                    <p><strong>Residente:</strong> <?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?></p>
                    <p><strong>Propiedad:</strong> <?php echo htmlspecialchars($resident['property_number']); ?> — Sección: <?php echo htmlspecialchars($resident['section'] ?? ''); ?></p>
                    <p><strong>Período:</strong> <?php echo date('d/m/Y', strtotime($date_from)); ?> al <?php echo date('d/m/Y', strtotime($date_to)); ?></p>
                    <p><strong>Generado:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                    <hr class="my-3">
                </div>

                <!-- Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                        <p class="text-sm text-gray-600">Total Pagado</p>
                        <p class="text-2xl font-bold text-green-600">$<?php echo number_format($totalPaid, 2); ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                        <p class="text-sm text-gray-600">Total Pendiente</p>
                        <p class="text-2xl font-bold text-yellow-600">$<?php echo number_format($totalPending, 2); ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                        <p class="text-sm text-gray-600">Total Vencido</p>
                        <p class="text-2xl font-bold text-red-600">$<?php echo number_format($totalOverdue, 2); ?></p>
                    </div>
                </div>

                <!-- Fees Table -->
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Pago</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referencia</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($fees)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                    No hay registros en el período seleccionado
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($fees as $fee): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php
                                    $period = $fee['period'] ?? $fee['due_date'] ?? null;
                                    echo $period ? date('M Y', strtotime($period)) : '—';
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    $<?php echo number_format($fee['amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php
                                        echo match($fee['status']) {
                                            'paid' => 'bg-green-100 text-green-800',
                                            'overdue' => 'bg-red-100 text-red-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php
                                        echo match($fee['status']) {
                                            'paid' => 'Pagado',
                                            'overdue' => 'Vencido',
                                            'pending' => 'Pendiente',
                                            default => ucfirst($fee['status'])
                                        };
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $fee['paid_date'] ? date('d/m/Y', strtotime($fee['paid_date'])) : ($fee['payment_date'] ? date('d/m/Y', strtotime($fee['payment_date'])) : '—'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($fee['payment_method'] ?? '—'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($fee['reference_number'] ?? '—'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Send by email (no-print) -->
                <div class="mt-4 flex justify-end space-x-3 no-print">
                    <a href="mailto:<?php echo htmlspecialchars($resident['email'] ?? ''); ?>?subject=Estado+de+Cuenta&body=Estimado+<?php echo urlencode($resident['first_name']); ?>%2C+adjunto+su+estado+de+cuenta."
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-envelope mr-2"></i> Enviar por Email
                    </a>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    aside, nav { display: none !important; }
    main { padding: 0 !important; }
}
@media screen {
    .print-only { display: none; }
}
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
