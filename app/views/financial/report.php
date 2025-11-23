<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">📊 Reporte Financiero Detallado</h1>
                <p class="text-gray-600 mt-1">Análisis detallado de movimientos financieros</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Filtros de Fecha -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Filtros de Reporte</h2>
                <form method="GET" action="<?php echo BASE_URL; ?>/financial/report" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input type="date" 
                               id="date_from" 
                               name="date_from" 
                               value="<?php echo $date_from ?? ''; ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input type="date" 
                               id="date_to" 
                               name="date_to" 
                               value="<?php echo $date_to ?? ''; ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i>Filtrar
                        </button>
                    </div>
                    <div class="flex items-end">
                        <a href="<?php echo BASE_URL; ?>/financial" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                    </div>
                </form>
            </div>

            <!-- Resumen Estadístico -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Ingresos</p>
                            <p class="text-2xl font-bold text-green-600">$<?php echo number_format($stats['total_ingresos'] ?? 0, 2); ?></p>
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
                            <p class="text-2xl font-bold text-red-600">$<?php echo number_format($stats['total_egresos'] ?? 0, 2); ?></p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Balance Neto</p>
                            <p class="text-2xl font-bold <?php echo ($stats['balance'] ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600'; ?>">
                                $<?php echo number_format($stats['balance'] ?? 0, 2); ?>
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-balance-scale text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Movimientos</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_movimientos'] ?? 0; ?></p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-full">
                            <i class="fas fa-list text-gray-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Distribución de Ingresos vs Egresos</h3>
                    <canvas id="incomeExpenseChart"></canvas>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Tendencia Mensual</h3>
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>

            <!-- Tabla de Resumen por Tipo -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Resumen por Tipo de Movimiento</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Categoría
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cantidad
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Monto Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (isset($stats['by_type']) && !empty($stats['by_type'])): ?>
                                <?php foreach ($stats['by_type'] as $type): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($type['name'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($type['transaction_type'] === 'ingreso'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Ingreso
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Egreso
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            <?php echo $type['count'] ?? 0; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right <?php echo $type['transaction_type'] === 'ingreso' ? 'text-green-600' : 'text-red-600'; ?>">
                                            $<?php echo number_format($type['total'] ?? 0, 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        No hay datos disponibles para el período seleccionado
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-end space-x-4">
                <button onclick="window.print()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-print mr-2"></i>Imprimir
                </button>
                <button onclick="exportToExcel()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-file-excel mr-2"></i>Exportar a Excel
                </button>
            </div>
        </main>
    </div>
</div>

<script>
// Verificar que Chart.js esté cargado
if (typeof Chart !== 'undefined') {
    // Gráfica de Ingresos vs Egresos
    const pieCanvas = document.getElementById('incomeExpenseChart');
    if (pieCanvas) {
        const ctxPie = pieCanvas.getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['Ingresos', 'Egresos'],
                datasets: [{
                    data: [
                        <?php echo $stats['total_ingresos'] ?? 0; ?>,
                        <?php echo $stats['total_egresos'] ?? 0; ?>
                    ],
                    backgroundColor: ['#10b981', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Gráfica de tendencia mensual (ejemplo simplificado)
    const lineCanvas = document.getElementById('monthlyTrendChart');
    if (lineCanvas) {
        const ctxLine = lineCanvas.getContext('2d');
        new Chart(ctxLine, {
    type: 'line',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        datasets: [
            {
                label: 'Ingresos',
                data: [0, 0, 0, 0, 0, <?php echo $stats['total_ingresos'] ?? 0; ?>],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4
            },
            {
                label: 'Egresos',
                data: [0, 0, 0, 0, 0, <?php echo $stats['total_egresos'] ?? 0; ?>],
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    });
    }
} else {
    console.error('Chart.js no está cargado');
}

function exportToExcel() {
    alert('Función de exportación a Excel en desarrollo');
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
