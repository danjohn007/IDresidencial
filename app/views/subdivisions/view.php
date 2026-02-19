<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <a href="<?php echo BASE_URL; ?>/subdivisions" 
                       class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 mb-4">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Fraccionamientos
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">🏘️ <?php echo htmlspecialchars($subdivision['name']); ?></h1>
                    <p class="text-gray-600 mt-1">Detalles del fraccionamiento</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/subdivisions/edit/<?php echo $subdivision['id']; ?>" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
            </div>

            <!-- Info Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Información General</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nombre</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($subdivision['name']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
                            <p>
                                <span class="px-3 py-1 rounded-full text-sm <?php echo $subdivision['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $subdivision['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Descripción</label>
                            <p class="text-gray-900"><?php echo !empty($subdivision['description']) ? htmlspecialchars($subdivision['description']) : '<span class="text-gray-400">-</span>'; ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dirección</label>
                            <p class="text-gray-900"><?php echo !empty($subdivision['address']) ? htmlspecialchars($subdivision['address']) : '<span class="text-gray-400">-</span>'; ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Ciudad</label>
                            <p class="text-gray-900"><?php echo !empty($subdivision['city']) ? htmlspecialchars($subdivision['city']) : '<span class="text-gray-400">-</span>'; ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Estado</label>
                            <p class="text-gray-900"><?php echo !empty($subdivision['state']) ? htmlspecialchars($subdivision['state']) : '<span class="text-gray-400">-</span>'; ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Código Postal</label>
                            <p class="text-gray-900"><?php echo !empty($subdivision['postal_code']) ? htmlspecialchars($subdivision['postal_code']) : '<span class="text-gray-400">-</span>'; ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Teléfono</label>
                            <p class="text-gray-900"><?php echo !empty($subdivision['phone']) ? htmlspecialchars($subdivision['phone']) : '<span class="text-gray-400">-</span>'; ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                            <p class="text-gray-900"><?php echo !empty($subdivision['email']) ? htmlspecialchars($subdivision['email']) : '<span class="text-gray-400">-</span>'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-home text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Propiedades</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['properties']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-users text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Residentes</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['residents']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <i class="fas fa-car text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Vehículos</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['vehicles']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties List -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Propiedades</h2>
                    <?php if (empty($properties)): ?>
                        <p class="text-gray-500 text-center py-8">No hay propiedades registradas en este fraccionamiento</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sección</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residentes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($properties as $property): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($property['property_number']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo ucfirst($property['property_type'] ?? '-'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($property['section'] ?? '-'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                                    echo match($property['status']) {
                                                        'ocupada' => 'bg-green-100 text-green-800',
                                                        'desocupada' => 'bg-yellow-100 text-yellow-800',
                                                        'en_construccion' => 'bg-blue-100 text-blue-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                ?>">
                                                    <?php 
                                                    echo match($property['status']) {
                                                        'ocupada' => 'Ocupada',
                                                        'desocupada' => 'Desocupada',
                                                        'en_construccion' => 'En Construcción',
                                                        default => ucfirst($property['status'])
                                                    };
                                                    ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo $property['resident_count']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
