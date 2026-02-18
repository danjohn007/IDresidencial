<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">🏘️ Fraccionamientos</h1>
                <p class="text-gray-600 mt-1">Gestión de fraccionamientos y subdivisiones</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <a href="<?php echo BASE_URL; ?>/subdivisions/create" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Nuevo Fraccionamiento
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($subdivisions as $subdivision): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4 text-white">
                            <h3 class="text-xl font-bold"><?php echo htmlspecialchars($subdivision['name']); ?></h3>
                            <p class="text-sm opacity-90 mt-1">
                                <?php echo htmlspecialchars($subdivision['city'] ?? 'Sin ciudad'); ?>
                            </p>
                        </div>
                        
                        <div class="p-4">
                            <?php if ($subdivision['description']): ?>
                                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($subdivision['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="text-center p-3 bg-blue-50 rounded">
                                    <p class="text-2xl font-bold text-blue-600"><?php echo $subdivision['property_count']; ?></p>
                                    <p class="text-xs text-gray-600">Propiedades</p>
                                </div>
                                <div class="text-center p-3 bg-green-50 rounded">
                                    <p class="text-2xl font-bold text-green-600"><?php echo $subdivision['resident_count']; ?></p>
                                    <p class="text-xs text-gray-600">Residentes</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mb-4">
                                <?php if ($subdivision['status'] === 'active'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        <i class="fas fa-times-circle mr-1"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                                
                                <div class="text-xs text-gray-500">
                                    <i class="far fa-calendar mr-1"></i>
                                    <?php echo date('d/m/Y', strtotime($subdivision['created_at'])); ?>
                                </div>
                            </div>

                            <div class="flex space-x-2">
                                <a href="<?php echo BASE_URL; ?>/subdivisions/viewDetails/<?php echo $subdivision['id']; ?>" 
                                   class="flex-1 text-center px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                    <i class="fas fa-eye mr-1"></i> Ver
                                </a>
                                <a href="<?php echo BASE_URL; ?>/subdivisions/edit/<?php echo $subdivision['id']; ?>" 
                                   class="flex-1 text-center px-3 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </a>
                                <a href="<?php echo BASE_URL; ?>/subdivisions/toggleStatus/<?php echo $subdivision['id']; ?>" 
                                   class="px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm"
                                   onclick="return confirm('¿Está seguro de cambiar el estado?')">
                                    <i class="fas fa-toggle-on"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($subdivisions)): ?>
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <i class="fas fa-folder-open text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-600 mb-4">No hay fraccionamientos registrados</p>
                    <a href="<?php echo BASE_URL; ?>/subdivisions/create" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Crear Primer Fraccionamiento
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
