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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <p class="text-sm text-gray-600">Carga masiva de propiedades desde CSV</p>
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
                    <li>Los registros duplicados serán omitidos</li>
                </ul>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
