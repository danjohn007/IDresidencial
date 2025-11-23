<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">📊 Reportes del Sistema</h1>
                <p class="text-gray-600 mt-1">Consulta reportes y estadísticas del residencial</p>
            </div>

            <!-- Report Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Financial Report -->
                <a href="<?php echo BASE_URL; ?>/reports/financial" class="block bg-white rounded-lg shadow-lg hover:shadow-xl transition p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="ml-4 text-xl font-bold text-gray-900">Reporte Financiero</h3>
                    </div>
                    <p class="text-gray-600 text-sm">Estado de pagos, cuotas de mantenimiento y balance general</p>
                </a>

                <!-- Access Report -->
                <a href="<?php echo BASE_URL; ?>/reports/access" class="block bg-white rounded-lg shadow-lg hover:shadow-xl transition p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-door-open text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="ml-4 text-xl font-bold text-gray-900">Reporte de Accesos</h3>
                    </div>
                    <p class="text-gray-600 text-sm">Historial de entradas y salidas del residencial</p>
                </a>

                <!-- Maintenance Report -->
                <a href="<?php echo BASE_URL; ?>/reports/maintenance" class="block bg-white rounded-lg shadow-lg hover:shadow-xl transition p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tools text-yellow-600 text-2xl"></i>
                        </div>
                        <h3 class="ml-4 text-xl font-bold text-gray-900">Reporte de Mantenimiento</h3>
                    </div>
                    <p class="text-gray-600 text-sm">Solicitudes y estados de mantenimiento</p>
                </a>

                <!-- Residents Report -->
                <a href="<?php echo BASE_URL; ?>/reports/residents" class="block bg-white rounded-lg shadow-lg hover:shadow-xl transition p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="ml-4 text-xl font-bold text-gray-900">Reporte de Residentes</h3>
                    </div>
                    <p class="text-gray-600 text-sm">Listado completo de residentes y sus datos</p>
                </a>

                <!-- Memberships Report -->
                <a href="<?php echo BASE_URL; ?>/reports/memberships" class="block bg-white rounded-lg shadow-lg hover:shadow-xl transition p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-id-card text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="ml-4 text-xl font-bold text-gray-900">Reporte de Cuotas</h3>
                    </div>
                    <p class="text-gray-600 text-sm">Estado de cuotas de mantenimiento por período</p>
                </a>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
