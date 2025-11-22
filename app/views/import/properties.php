<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">📥 Importar Propiedades</h1>
                    <p class="text-gray-600 mt-1">Importar propiedades desde archivo CSV</p>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Archivo CSV <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="csv_file" accept=".csv" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/import" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Volver
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-upload mr-2"></i> Importar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-2">Formato del CSV</h4>
                    <p class="text-sm text-blue-800 mb-2">El archivo CSV debe tener las siguientes columnas en orden:</p>
                    <code class="text-xs bg-white p-2 rounded block">property_number,section,street,property_type</code>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
