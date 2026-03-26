<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">📥 Importar Datos</h1>
                <p class="text-gray-600 mt-1">Importación masiva de datos desde archivos CSV</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Import Residents -->
                <a href="<?php echo BASE_URL; ?>/import/residents" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <i class="fas fa-users text-blue-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Importar Residentes</h3>
                            <p class="text-sm text-gray-600">Carga masiva de residentes desde CSV</p>
                        </div>
                    </div>
                </a>

                <!-- Import Properties -->
                <a href="<?php echo BASE_URL; ?>/import/properties" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-100 p-4 rounded-lg">
                            <i class="fas fa-home text-green-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Importar Propiedades</h3>
                            <p class="text-sm text-gray-600">Casas, departamentos y torres desde CSV</p>
                        </div>
                    </div>
                </a>

                <!-- Import Users -->
                <a href="<?php echo BASE_URL; ?>/import/users" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-purple-100 p-4 rounded-lg">
                            <i class="fas fa-user-shield text-purple-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Importar Usuarios</h3>
                            <p class="text-sm text-gray-600">Administradores, guardias y residentes desde CSV</p>
                        </div>
                    </div>
                </a>

                <!-- Import Maintenance Fees -->
                <a href="<?php echo BASE_URL; ?>/import/maintenanceFees" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-yellow-100 p-4 rounded-lg">
                            <i class="fas fa-file-invoice-dollar text-yellow-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Importar Cuotas</h3>
                            <p class="text-sm text-gray-600">Cuotas de mantenimiento desde CSV</p>
                        </div>
                    </div>
                </a>

                <!-- Import Amenities -->
                <a href="<?php echo BASE_URL; ?>/import/amenities" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-teal-100 p-4 rounded-lg">
                            <i class="fas fa-swimming-pool text-teal-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Importar Amenidades</h3>
                            <p class="text-sm text-gray-600">Albercas, salones y más desde CSV</p>
                        </div>
                    </div>
                </a>

                <!-- Import Financial Movements -->
                <a href="<?php echo BASE_URL; ?>/import/financialMovements" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-red-100 p-4 rounded-lg">
                            <i class="fas fa-exchange-alt text-red-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Importar Movimientos Financieros</h3>
                            <p class="text-sm text-gray-600">Ingresos y egresos desde CSV</p>
                        </div>
                    </div>
                </a>

                <!-- Import CFDI Config -->
                <a href="<?php echo BASE_URL; ?>/import/cfdiConfig" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-indigo-100 p-4 rounded-lg">
                            <i class="fas fa-receipt text-indigo-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Configuración CFDI</h3>
                            <p class="text-sm text-gray-600">RFC, régimen fiscal y datos de facturación</p>
                        </div>
                    </div>
                </a>

                <!-- Import PayPal Config -->
                <a href="<?php echo BASE_URL; ?>/import/paypalConfig" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="flex items-center space-x-4">
                        <div class="bg-sky-100 p-4 rounded-lg">
                            <i class="fab fa-paypal text-sky-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Cuenta PayPal</h3>
                            <p class="text-sm text-gray-600">Credenciales y configuración de PayPal</p>
                        </div>
                    </div>
                </a>

                <!-- Importación Masiva -->
                <a href="<?php echo BASE_URL; ?>/import/bulkImport" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition border-2 border-orange-200">
                    <div class="flex items-center space-x-4">
                        <div class="bg-orange-100 p-4 rounded-lg">
                            <i class="fas fa-layer-group text-orange-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">Importación Masiva</h3>
                            <p class="text-sm text-gray-600">Importar todos los datos desde un único archivo Excel</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Info Box -->
            <div class="mt-6 p-6 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-semibold text-blue-900 mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    Instrucciones para Importar
                </h4>
                <ul class="text-sm text-blue-800 space-y-2 list-disc list-inside">
                    <li>Los archivos deben estar en formato CSV</li>
                    <li>La primera fila debe contener los encabezados de las columnas</li>
                    <li>Asegúrate de que los datos estén correctamente formateados</li>
                    <li>Los registros duplicados serán omitidos o actualizados según el módulo</li>
                    <li>Se recomienda importar primero Propiedades, luego Residentes y finalmente Cuotas</li>
                </ul>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>

