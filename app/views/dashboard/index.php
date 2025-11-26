<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <!-- Dashboard Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <!-- Welcome Section -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        ¡Bienvenido, <?php echo $user['first_name']; ?>!
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Este es tu panel de control del sistema residencial
                    </p>
                </div>
                
                <?php if ($user['role'] === 'superadmin'): ?>
                <!-- Date Range Filter -->
                <form method="GET" class="flex items-center space-x-2">
                    <input type="date" name="date_from" value="<?php echo $dateFrom; ?>" 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <span class="text-gray-500">a</span>
                    <input type="date" name="date_to" value="<?php echo $dateTo; ?>" 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <?php if ($user['role'] === 'superadmin'): ?>
            <!-- Quick Access Shortcuts -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <a href="<?php echo BASE_URL; ?>/financial/create" 
                   class="flex items-center p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow hover:shadow-lg transform hover:-translate-y-1 transition">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 mr-4">
                        <i class="fas fa-dollar-sign text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm opacity-90">Acceso Rápido</p>
                        <p class="font-bold text-lg">Nuevo Pago</p>
                    </div>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/residents/create" 
                   class="flex items-center p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg shadow hover:shadow-lg transform hover:-translate-y-1 transition">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 mr-4">
                        <i class="fas fa-user-plus text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm opacity-90">Acceso Rápido</p>
                        <p class="font-bold text-lg">Alta de Residente</p>
                    </div>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/access" 
                   class="flex items-center p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg shadow hover:shadow-lg transform hover:-translate-y-1 transition">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 mr-4">
                        <i class="fas fa-qrcode text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm opacity-90">Acceso Rápido</p>
                        <p class="font-bold text-lg">Validar QR</p>
                    </div>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/access/detectedPlates" 
                   class="flex items-center p-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg shadow hover:shadow-lg transform hover:-translate-y-1 transition">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 mr-4">
                        <i class="fas fa-camera text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm opacity-90">Acceso Rápido</p>
                        <p class="font-bold text-lg">Placas Detectadas</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Calendar Quick Access (All Users) -->
            <div class="mb-6">
                <a href="<?php echo BASE_URL; ?>/amenities/calendar" 
                   class="flex items-center p-4 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-lg shadow hover:shadow-lg transform hover:-translate-y-1 transition">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 mr-4">
                        <i class="fas fa-calendar-alt text-3xl"></i>
                    </div>
                    <div>
                        <p class="text-sm opacity-90">Acceso Rápido</p>
                        <p class="font-bold text-xl">Calendario de Reservaciones de Amenidades</p>
                        <p class="text-sm opacity-90 mt-1">Ver y gestionar reservaciones de amenidades</p>
                    </div>
                </a>
            </div>
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                <?php if (in_array($user['role'], ['superadmin', 'administrador'])): ?>
                <!-- Residentes -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Residentes</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_residents']; ?></p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-users text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (in_array($user['role'], ['superadmin', 'administrador', 'guardia'])): ?>
                <!-- Visitas Hoy -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Visitas Hoy</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_visits_today']; ?></p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-door-open text-green-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Mantenimientos -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Mantenimientos</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_maintenance']; ?></p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-tools text-yellow-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Reservaciones -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Reservaciones</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total_reservations']; ?></p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <i class="fas fa-calendar-check text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                
                <?php if (in_array($user['role'], ['superadmin', 'administrador'])): ?>
                <!-- Pagos Pendientes -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Pagos Pendientes</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['pending_payments']; ?></p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3">
                            <i class="fas fa-credit-card text-red-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (in_array($user['role'], ['superadmin', 'administrador', 'guardia'])): ?>
                <!-- Alertas Activas -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Alertas Activas</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['active_alerts']; ?></p>
                        </div>
                        <div class="bg-orange-100 rounded-full p-3">
                            <i class="fas fa-exclamation-triangle text-orange-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($user['role'] === 'superadmin' && !empty($chartsData)): ?>
            <!-- Charts Section for SuperAdmin -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                    Gráficas y Análisis
                </h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Chart 1: Financial Movements -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                            Ingresos vs Egresos
                        </h3>
                        <canvas id="financialChart"></canvas>
                    </div>
                    
                    <!-- Chart 2: Daily Visits -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                            Visitas Diarias
                        </h3>
                        <canvas id="visitsChart"></canvas>
                    </div>
                    
                    <!-- Chart 3: Maintenance by Category -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-chart-pie text-yellow-600 mr-2"></i>
                            Mantenimientos por Categoría
                        </h3>
                        <canvas id="maintenanceChart"></canvas>
                    </div>
                    
                    <!-- Chart 4: Payments by Status -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-chart-donut text-purple-600 mr-2"></i>
                            Pagos por Estado
                        </h3>
                        <canvas id="paymentsChart"></canvas>
                    </div>
                </div>
                
                <!-- Reports Section -->
                <h2 class="text-2xl font-bold text-gray-900 mb-4 mt-8">
                    <i class="fas fa-file-invoice-dollar text-green-600 mr-2"></i>
                    Informes de Movimientos
                </h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Report 1: Recent Financial Movements -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-exchange-alt text-blue-600 mr-2"></i>
                                Movimientos Financieros Recientes
                            </h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($chartsData['recentMovements'])): ?>
                                <p class="text-gray-500 text-center py-4">No hay movimientos en el rango seleccionado</p>
                            <?php else: ?>
                                <div class="space-y-3">
                                    <?php foreach ($chartsData['recentMovements'] as $movement): ?>
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900 text-sm">
                                                    <?php echo htmlspecialchars($movement['movement_type_name'] ?? 'N/A'); ?>
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    <?php echo date('d/m/Y', strtotime($movement['transaction_date'])); ?>
                                                    <?php if ($movement['property_number']): ?>
                                                        • <?php echo htmlspecialchars($movement['property_number']); ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <span class="font-bold text-sm <?php echo $movement['transaction_type'] === 'ingreso' ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo $movement['transaction_type'] === 'ingreso' ? '+' : '-'; ?>
                                                $<?php echo number_format($movement['amount'], 2); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4 text-center">
                                    <a href="<?php echo BASE_URL; ?>/financial" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Ver todos los movimientos <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Report 2: Pending Payments Summary -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                                Pagos Pendientes
                            </h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($chartsData['pendingPayments'])): ?>
                                <p class="text-gray-500 text-center py-4">No hay pagos pendientes</p>
                            <?php else: ?>
                                <div class="space-y-3">
                                    <?php foreach ($chartsData['pendingPayments'] as $payment): ?>
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900 text-sm">
                                                    <?php echo htmlspecialchars($payment['property_number'] ?? 'N/A'); ?>
                                                    <?php if (!empty($payment['resident_name'])): ?>
                                                        - <?php echo htmlspecialchars($payment['resident_name']); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    Vence: <?php echo date('d/m/Y', strtotime($payment['due_date'])); ?>
                                                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?php echo $payment['status'] === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                        <?php echo $payment['status'] === 'overdue' ? 'Vencido' : 'Pendiente'; ?>
                                                    </span>
                                                </p>
                                            </div>
                                            <span class="font-bold text-sm text-red-600">
                                                $<?php echo number_format($payment['amount'], 2); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4 text-center">
                                    <a href="<?php echo BASE_URL; ?>/residents/payments" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Ver todos los pagos <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php if (in_array($user['role'], ['superadmin', 'administrador', 'guardia']) && !empty($recentActivity)): ?>
                <!-- Actividad Reciente -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-history text-blue-600 mr-2"></i>
                            Actividad Reciente
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="flex items-start space-x-3 pb-4 border-b last:border-b-0 last:pb-0">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-<?php echo $activity['access_type'] === 'entry' ? 'sign-in-alt' : 'sign-out-alt'; ?> text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($activity['name'] ?? 'Sin nombre'); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo ucfirst($activity['log_type']); ?> - <?php echo ucfirst($activity['access_type']); ?>
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <?php echo date('d/m/Y H:i', strtotime($activity['timestamp'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Próximas Reservaciones -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                            Próximas Reservaciones
                        </h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($upcomingReservations)): ?>
                            <p class="text-gray-500 text-center py-8">No hay reservaciones próximas</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($upcomingReservations as $reservation): ?>
                                    <div class="flex items-start space-x-3 pb-4 border-b last:border-b-0 last:pb-0">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                <i class="fas fa-swimming-pool text-purple-600"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($reservation['amenity_name']); ?>
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                <?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?>
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                <?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?> 
                                                - <?php echo date('H:i', strtotime($reservation['start_time'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Mantenimientos Pendientes -->
                <div class="bg-white rounded-lg shadow lg:col-span-2">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-wrench text-yellow-600 mr-2"></i>
                            Mantenimientos Pendientes
                        </h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($pendingMaintenance)): ?>
                            <p class="text-gray-500 text-center py-8">No hay mantenimientos pendientes</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reportado por</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridad</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($pendingMaintenance as $maint): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo ucfirst($maint['category']); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($maint['title']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo $maint['first_name'] . ' ' . $maint['last_name']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full
                                                        <?php 
                                                            echo match($maint['priority']) {
                                                                'urgente' => 'bg-red-100 text-red-800',
                                                                'alta' => 'bg-orange-100 text-orange-800',
                                                                'media' => 'bg-yellow-100 text-yellow-800',
                                                                'baja' => 'bg-green-100 text-green-800',
                                                                default => 'bg-gray-100 text-gray-800'
                                                            };
                                                        ?>">
                                                        <?php echo ucfirst($maint['priority']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                        <?php echo ucfirst($maint['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo date('d/m/Y', strtotime($maint['created_at'])); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php if ($user['role'] === 'superadmin' && !empty($chartsData)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart 1: Financial Movements (Ingresos vs Egresos)
<?php if (!empty($chartsData['financialMovements'])): ?>
const financialData = <?php echo json_encode($chartsData['financialMovements']); ?>;
const financialCtx = document.getElementById('financialChart').getContext('2d');
new Chart(financialCtx, {
    type: 'bar',
    data: {
        labels: financialData.map(d => {
            const [year, month] = d.month.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('es-MX', { month: 'short', year: 'numeric' });
        }),
        datasets: [{
            label: 'Ingresos',
            data: financialData.map(d => parseFloat(d.ingresos)),
            backgroundColor: 'rgba(34, 197, 94, 0.7)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 2
        }, {
            label: 'Egresos',
            data: financialData.map(d => parseFloat(d.egresos)),
            backgroundColor: 'rgba(239, 68, 68, 0.7)',
            borderColor: 'rgb(239, 68, 68)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString('es-MX');
                    }
                }
            }
        }
    }
});
<?php endif; ?>

// Chart 2: Daily Visits
<?php if (!empty($chartsData['dailyVisits'])): ?>
const visitsData = <?php echo json_encode($chartsData['dailyVisits']); ?>;
const visitsCtx = document.getElementById('visitsChart').getContext('2d');
new Chart(visitsCtx, {
    type: 'line',
    data: {
        labels: visitsData.map(d => new Date(d.date).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' })),
        datasets: [{
            label: 'Visitas',
            data: visitsData.map(d => parseInt(d.total)),
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
<?php endif; ?>

// Chart 3: Maintenance by Category
<?php if (!empty($chartsData['maintenanceByCategory'])): ?>
const maintenanceData = <?php echo json_encode($chartsData['maintenanceByCategory']); ?>;
const maintenanceCtx = document.getElementById('maintenanceChart').getContext('2d');
new Chart(maintenanceCtx, {
    type: 'doughnut',
    data: {
        labels: maintenanceData.map(d => d.category.charAt(0).toUpperCase() + d.category.slice(1)),
        datasets: [{
            data: maintenanceData.map(d => parseInt(d.total)),
            backgroundColor: [
                'rgba(59, 130, 246, 0.7)',
                'rgba(16, 185, 129, 0.7)',
                'rgba(251, 191, 36, 0.7)',
                'rgba(239, 68, 68, 0.7)',
                'rgba(168, 85, 247, 0.7)',
                'rgba(236, 72, 153, 0.7)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>

// Chart 4: Payments by Status
<?php if (!empty($chartsData['paymentsByStatus'])): ?>
const paymentsData = <?php echo json_encode($chartsData['paymentsByStatus']); ?>;
const paymentsCtx = document.getElementById('paymentsChart').getContext('2d');
const statusLabels = {
    'pending': 'Pendiente',
    'paid': 'Pagado',
    'overdue': 'Vencido',
    'cancelled': 'Cancelado'
};
new Chart(paymentsCtx, {
    type: 'pie',
    data: {
        labels: paymentsData.map(d => statusLabels[d.status] || d.status),
        datasets: [{
            data: paymentsData.map(d => parseInt(d.total)),
            backgroundColor: [
                'rgba(251, 191, 36, 0.7)',
                'rgba(34, 197, 94, 0.7)',
                'rgba(239, 68, 68, 0.7)',
                'rgba(156, 163, 175, 0.7)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const amount = paymentsData[context.dataIndex].total_amount;
                        return label + ': ' + value + ' pagos ($' + parseFloat(amount).toLocaleString('es-MX') + ')';
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>
<?php endif; ?>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
