<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">📊 Módulo de Reportes</h1>
                <p class="text-gray-600 mt-1">Reportes y análisis del sistema</p>
            </div>

            <!-- Reportes Disponibles -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Reporte Financiero -->
                <a href="<?php echo BASE_URL; ?>/reports/financial" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-green-100 rounded-full">
                            <i class="fas fa-chart-line text-green-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Reporte Financiero</h3>
                            <p class="text-sm text-gray-600">Ingresos, egresos y balance</p>
                        </div>
                    </div>
                </a>

                <!-- Reporte de Accesos -->
                <a href="<?php echo BASE_URL; ?>/reports/access" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-blue-100 rounded-full">
                            <i class="fas fa-door-open text-blue-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Reporte de Accesos</h3>
                            <p class="text-sm text-gray-600">Visitas y control de acceso</p>
                        </div>
                    </div>
                </a>

                <!-- Reporte de Mantenimiento -->
                <a href="<?php echo BASE_URL; ?>/reports/maintenance" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-orange-100 rounded-full">
                            <i class="fas fa-tools text-orange-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Reporte de Mantenimiento</h3>
                            <p class="text-sm text-gray-600">Incidencias y reparaciones</p>
                        </div>
                    </div>
                </a>

                <!-- Reporte de Residentes -->
                <a href="<?php echo BASE_URL; ?>/reports/residents" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-purple-100 rounded-full">
                            <i class="fas fa-users text-purple-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Reporte de Residentes</h3>
                            <p class="text-sm text-gray-600">Ocupación y propiedades</p>
                        </div>
                    </div>
                </a>

                <!-- Reporte de Membresías -->
                <a href="<?php echo BASE_URL; ?>/reports/memberships" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-pink-100 rounded-full">
                            <i class="fas fa-id-card text-pink-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Reporte de Membresías</h3>
                            <p class="text-sm text-gray-600">Planes y suscripciones</p>
                        </div>
                    </div>
                </a>

                <!-- Reporte de Amenidades -->
                <a href="<?php echo BASE_URL; ?>/amenities" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-teal-100 rounded-full">
                            <i class="fas fa-swimming-pool text-teal-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Reporte de Amenidades</h3>
                            <p class="text-sm text-gray-600">Reservaciones y uso</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Información Adicional -->
            <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Información sobre los reportes</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Los reportes están disponibles solo para administradores y superadministradores.</p>
                            <p class="mt-1">Puedes filtrar los datos por fechas para obtener información específica del período que necesites.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
