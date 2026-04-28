<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🏠 Propiedades</h1>
                    <p class="text-gray-600 mt-1">Gestión de propiedades del residencial</p>
                </div>
                <div class="flex space-x-3">
                    <a href="<?php echo BASE_URL; ?>/residents/createProperty" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Nueva Propiedad
                    </a>
                    <a href="<?php echo BASE_URL; ?>/residents" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Residentes
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Search & Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/residents/properties" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>"
                               placeholder="Número, sección, calle..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select name="property_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todos los tipos</option>
                            <option value="casa" <?php echo ($filter_type ?? '') === 'casa' ? 'selected' : ''; ?>>Casa</option>
                            <option value="departamento" <?php echo ($filter_type ?? '') === 'departamento' ? 'selected' : ''; ?>>Departamento</option>
                            <option value="lote" <?php echo ($filter_type ?? '') === 'lote' ? 'selected' : ''; ?>>Lote</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todos los estados</option>
                            <option value="ocupada" <?php echo ($filter_status ?? '') === 'ocupada' ? 'selected' : ''; ?>>Ocupada</option>
                            <option value="desocupada" <?php echo ($filter_status ?? '') === 'desocupada' ? 'selected' : ''; ?>>Desocupada</option>
                            <option value="en_construccion" <?php echo ($filter_status ?? '') === 'en_construccion' ? 'selected' : ''; ?>>En Construcción</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>/residents/properties" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Properties Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sección</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Calle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Área (m²)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residentes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($properties as $property): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo $property['property_number']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $property['section']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $property['street'] ?? 'N/A'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo match($property['property_type']) {
                                            'casa' => 'bg-blue-100 text-blue-800',
                                            'departamento' => 'bg-purple-100 text-purple-800',
                                            'lote' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($property['property_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $property['area_m2'] ? number_format($property['area_m2'], 2) : 'N/A'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                        <?php echo $property['resident_count']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo match($property['status']) {
                                            'ocupada' => 'bg-green-100 text-green-800',
                                            'desocupada' => 'bg-yellow-100 text-yellow-800',
                                            'en_construccion' => 'bg-orange-100 text-orange-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $property['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <a href="<?php echo BASE_URL; ?>/residents/editProperty/<?php echo $property['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-3" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($property['resident_count'] == 0): ?>
                                        <a href="<?php echo BASE_URL; ?>/residents/deleteProperty/<?php echo $property['id']; ?>" 
                                           onclick="return confirm('¿Está seguro de eliminar esta propiedad?')"
                                           class="text-red-600 hover:text-red-900" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-300" title="No se puede eliminar una propiedad con residentes">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Cards -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Total Propiedades</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Ocupadas</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['ocupada']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Desocupadas</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['desocupada']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">En Construcción</p>
                    <p class="text-3xl font-bold text-orange-600"><?php echo $stats['en_construccion']; ?></p>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
