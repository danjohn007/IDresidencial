<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">⚠️ Reporte de Morosidad</h1>
                    <p class="text-gray-600 mt-1">Propiedades con adeudos vencidos</p>
                </div>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-print mr-2"></i> Imprimir
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6 no-print">
                <form method="GET" action="<?php echo BASE_URL; ?>/residents/delinquencyReport" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>"
                               class="px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>"
                               class="px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                    <p class="text-sm text-gray-600">Propiedades Morosas</p>
                    <p class="text-3xl font-bold text-red-600"><?php echo $totalProperties; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
                    <p class="text-sm text-gray-600">Total Adeudo</p>
                    <p class="text-3xl font-bold text-orange-600">$<?php echo number_format($totalAmount, 2); ?></p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sección</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Meses Vencidos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deuda Vencida</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Más Antiguo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase no-print">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($delinquents)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-check-circle text-green-500 text-3xl mb-2 block"></i>
                                No hay propiedades con adeudos vencidos
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($delinquents as $d): ?>
                        <tr class="hover:bg-gray-50 <?php echo $d['months_overdue'] >= 2 ? 'bg-red-50' : ($d['months_overdue'] === 1 ? 'bg-yellow-50' : ''); ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                <?php echo htmlspecialchars($d['property_number']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($d['section'] ?? '—'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($d['email'] ?? ''); ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($d['phone'] ?? '—'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 text-sm rounded-full font-bold <?php echo $d['months_overdue'] >= 2 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $d['months_overdue']; ?>
                                    <?php if ($d['months_overdue'] >= 2): ?> ⚠️<?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-700">
                                $<?php echo number_format($d['total_overdue'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo $d['oldest_overdue'] ? date('m/Y', strtotime($d['oldest_overdue'])) : '—'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm no-print">
                                <a href="<?php echo BASE_URL; ?>/residents/accountStatement/<?php echo $d['property_id']; ?>"
                                   class="text-blue-600 hover:text-blue-900 mr-3" title="Ver Estado de Cuenta">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </a>
                                <a href="mailto:<?php echo htmlspecialchars($d['email'] ?? ''); ?>"
                                   class="text-green-600 hover:text-green-900" title="Enviar Email">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    aside, nav { display: none !important; }
    main { padding: 0 !important; }
}
</style>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
