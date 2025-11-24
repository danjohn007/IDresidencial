<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">📋 Catálogo de Tipos de Movimiento</h1>
                <p class="text-gray-600 mt-1">Tipos de movimientos financieros disponibles en el sistema</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Form to create new movement type -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-plus-circle mr-2 text-blue-600"></i>Agregar Nuevo Tipo de Movimiento
                </h2>
                <form method="POST" action="<?php echo BASE_URL; ?>/financial/movementTypes" class="space-y-4">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="Ej: Cuota Extraordinaria">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Categoría <span class="text-red-500">*</span>
                            </label>
                            <select name="category" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccionar...</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Egreso</option>
                                <option value="ambos">Ambos</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción
                            </label>
                            <input type="text" name="description" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="Descripción opcional">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>Guardar Tipo de Movimiento
                        </button>
                    </div>
                </form>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form method="GET" class="flex items-center space-x-4">
                    <select name="category" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Todas las categorías</option>
                        <option value="ingreso" <?php echo $category_filter === 'ingreso' ? 'selected' : ''; ?>>Ingreso</option>
                        <option value="egreso" <?php echo $category_filter === 'egreso' ? 'selected' : ''; ?>>Egreso</option>
                        <option value="ambos" <?php echo $category_filter === 'ambos' ? 'selected' : ''; ?>>Ambos</option>
                    </select>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                    <?php if (!empty($category_filter)): ?>
                        <a href="<?php echo BASE_URL; ?>/financial/movementTypes" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            <i class="fas fa-times mr-2"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Tipos de Movimiento</h2>
                            <p class="text-sm text-gray-500 mt-1">Total: <?php echo $total; ?> registros</p>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/financial" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Módulo Financiero
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nombre
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo de Transacción
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($movementTypes)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>No hay tipos de movimiento registrados</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($movementTypes as $type): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $type['id']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($type['name']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (isset($type['category'])): ?>
                                                <?php if ($type['category'] === 'ingreso'): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        <i class="fas fa-arrow-up mr-1"></i> Ingreso
                                                    </span>
                                                <?php elseif ($type['category'] === 'egreso'): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        <i class="fas fa-arrow-down mr-1"></i> Egreso
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        <i class="fas fa-arrows-alt-h mr-1"></i> Ambos
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <?php echo htmlspecialchars($type['description'] ?? 'Sin descripción'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (isset($type['is_active']) && $type['is_active']): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Activo
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Inactivo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="editMovementType(<?php echo htmlspecialchars(json_encode($type)); ?>)" 
                                                    class="text-blue-600 hover:text-blue-900 mr-3" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="<?php echo BASE_URL; ?>/financial/movementTypes?toggle=1&id=<?php echo $type['id']; ?>" 
                                               class="text-yellow-600 hover:text-yellow-900 mr-3" 
                                               title="<?php echo $type['is_active'] ? 'Suspender' : 'Activar'; ?>"
                                               onclick="return confirm('¿Está seguro de cambiar el estado?')">
                                                <i class="fas fa-<?php echo $type['is_active'] ? 'pause' : 'play'; ?>-circle"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/financial/movementTypes?delete=1&id=<?php echo $type['id']; ?>" 
                                               class="text-red-600 hover:text-red-900" 
                                               title="Eliminar"
                                               onclick="return confirm('¿Está seguro de eliminar este tipo de movimiento? Esta acción no se puede deshacer.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrando página <?php echo $page; ?> de <?php echo $total_pages; ?>
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo BASE_URL; ?>/financial/movementTypes?page=<?php echo ($page - 1); ?><?php echo !empty($category_filter) ? '&category=' . $category_filter : ''; ?>" 
                               class="px-3 py-1 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="<?php echo BASE_URL; ?>/financial/movementTypes?page=<?php echo $i; ?><?php echo !empty($category_filter) ? '&category=' . $category_filter : ''; ?>" 
                               class="px-3 py-1 border <?php echo $i === $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50'; ?> rounded">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo BASE_URL; ?>/financial/movementTypes?page=<?php echo ($page + 1); ?><?php echo !empty($category_filter) ? '&category=' . $category_filter : ''; ?>" 
                               class="px-3 py-1 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Información adicional -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Información</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Los tipos de movimiento se utilizan para categorizar las transacciones financieras del sistema.</p>
                            <p class="mt-1">• <strong>Ingresos:</strong> Pagos de cuotas, reservaciones de amenidades, etc.</p>
                            <p>• <strong>Egresos:</strong> Gastos de mantenimiento, servicios, nómina, etc.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900">Editar Tipo de Movimiento</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/financial/movementTypes">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="edit_name" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Categoría <span class="text-red-500">*</span>
                    </label>
                    <select name="category" id="edit_category" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar...</option>
                        <option value="ingreso">Ingreso</option>
                        <option value="egreso">Egreso</option>
                        <option value="ambos">Ambos</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Descripción
                    </label>
                    <input type="text" name="description" id="edit_description" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeEditModal()" 
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editMovementType(type) {
    document.getElementById('edit_id').value = type.id;
    document.getElementById('edit_name').value = type.name;
    document.getElementById('edit_category').value = type.category;
    document.getElementById('edit_description').value = type.description || '';
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
