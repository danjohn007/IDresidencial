<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">💰 Módulo Financiero</h1>
                <p class="text-gray-600 mt-1">Gestión de ingresos y egresos del residencial</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Ingresos</p>
                            <p class="text-2xl font-bold text-green-600">$<?php echo number_format($stats['total_ingresos'], 2); ?></p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Egresos</p>
                            <p class="text-2xl font-bold text-red-600">$<?php echo number_format($stats['total_egresos'], 2); ?></p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Balance</p>
                            <p class="text-2xl font-bold <?php echo $stats['balance'] >= 0 ? 'text-blue-600' : 'text-orange-600'; ?>">
                                $<?php echo number_format($stats['balance'], 2); ?>
                            </p>
                        </div>
                        <div class="p-3 <?php echo $stats['balance'] >= 0 ? 'bg-blue-100' : 'bg-orange-100'; ?> rounded-full">
                            <i class="fas fa-balance-scale <?php echo $stats['balance'] >= 0 ? 'text-blue-600' : 'text-orange-600'; ?> text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Período</p>
                            <p class="text-sm font-semibold text-gray-700">
                                <?php echo date('d/m/Y', strtotime($stats['date_from'])); ?><br>
                                <?php echo date('d/m/Y', strtotime($stats['date_to'])); ?>
                            </p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-calendar text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Movimientos por Mes</h3>
                    <div style="height: 300px; position: relative;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Movimientos por Tipo</h3>
                    <div style="height: 300px; position: relative;">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/financial" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Transacción</label>
                        <select name="transaction_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todos</option>
                            <option value="ingreso" <?php echo $filters['transaction_type'] === 'ingreso' ? 'selected' : ''; ?>>Ingresos</option>
                            <option value="egreso" <?php echo $filters['transaction_type'] === 'egreso' ? 'selected' : ''; ?>>Egresos</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimiento</label>
                        <select name="movement_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todos</option>
                            <?php foreach ($movementTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>" <?php echo $filters['movement_type_id'] == $type['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input type="date" name="date_from" value="<?php echo $filters['date_from']; ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input type="date" name="date_to" value="<?php echo $filters['date_to']; ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Propiedad</label>
                        <select name="property_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todas</option>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?php echo $property['id']; ?>" <?php echo $filters['property_id'] == $property['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($property['property_number']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i> Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>/financial" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Acciones -->
            <div class="flex justify-between items-center mb-4">
                <a href="<?php echo BASE_URL; ?>/financial/create" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-plus mr-2"></i> Nuevo Movimiento
                </a>
                <div class="flex space-x-2">
                    <a href="<?php echo BASE_URL; ?>/financial/movementTypes" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        <i class="fas fa-list mr-2"></i> Catálogo de Tipos
                    </a>
                    <a href="<?php echo BASE_URL; ?>/financial/report" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-file-alt mr-2"></i> Reporte Detallado
                    </a>
                </div>
            </div>

            <!-- Tabla de Movimientos -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($movements)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No hay movimientos registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movements as $movement): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($movement['transaction_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?php echo $movement['transaction_type'] === 'ingreso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $movement['transaction_type'] === 'ingreso' ? 'Ingreso' : 'Egreso'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="font-medium"><?php echo htmlspecialchars($movement['movement_type_name']); ?></div>
                                        <div class="text-gray-500 text-xs"><?php echo htmlspecialchars(substr($movement['description'], 0, 50)); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $movement['property_number'] ? htmlspecialchars($movement['property_number']) : '-'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold <?php echo $movement['transaction_type'] === 'ingreso' ? 'text-green-600' : 'text-red-600'; ?>">
                                        $<?php echo number_format($movement['amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <a href="<?php echo BASE_URL; ?>/financial/view/<?php echo $movement['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (!$movement['reference_type']): ?>
                                            <a href="<?php echo BASE_URL; ?>/financial/edit/<?php echo $movement['id']; ?>" 
                                               class="text-yellow-600 hover:text-yellow-900 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/financial/delete/<?php echo $movement['id']; ?>" 
                                               onclick="return confirm('¿Está seguro de eliminar este movimiento?')"
                                               class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
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

<!-- Chart.js para gráficas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Preparar datos para gráfica de movimientos por mes
const monthlyData = <?php echo json_encode($stats['movements_by_month']); ?>;
const months = [...new Set(monthlyData.map(m => m.month))];
const ingresosByMonth = months.map(month => {
    const found = monthlyData.find(m => m.month === month && m.transaction_type === 'ingreso');
    return found ? parseFloat(found.total) : 0;
});
const egresosByMonth = months.map(month => {
    const found = monthlyData.find(m => m.month === month && m.transaction_type === 'egreso');
    return found ? parseFloat(found.total) : 0;
});

// Gráfica de movimientos por mes
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Ingresos',
            data: ingresosByMonth,
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4
        }, {
            label: 'Egresos',
            data: egresosByMonth,
            borderColor: 'rgb(239, 68, 68)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfica de movimientos por tipo
const typeData = <?php echo json_encode($stats['movements_by_type']); ?>;
const typeLabels = typeData.map(t => t.name);
const typeAmounts = typeData.map(t => parseFloat(t.total));
const typeColors = typeData.map(t => t.transaction_type === 'ingreso' ? 'rgba(34, 197, 94, 0.8)' : 'rgba(239, 68, 68, 0.8)');

const typeCtx = document.getElementById('typeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'bar',
    data: {
        labels: typeLabels,
        datasets: [{
            label: 'Monto',
            data: typeAmounts,
            backgroundColor: typeColors
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
