<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">📍 Alta de Áreas</h1>
                        <p class="text-gray-600 mt-1">Catálogo de áreas para dispositivos de control de acceso</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/devices" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Dispositivos
                    </a>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Add Area Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Nueva Área</h3>
                        <form method="POST" action="<?php echo BASE_URL; ?>/devices/areas">
                            <input type="hidden" name="action" value="create">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del Área <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: Entrada Principal">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                <input type="text" name="description"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Descripción opcional">
                            </div>
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i> Agregar Área
                            </button>
                        </form>
                    </div>

                    <!-- Areas List -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Áreas Configuradas</h3>
                        <?php if (empty($areas)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-map-marker-alt text-4xl mb-3 block text-gray-300"></i>
                                <p>No hay áreas configuradas</p>
                                <p class="text-sm">Agrega la primera área usando el formulario</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-2">
                                <?php foreach ($areas as $area): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($area['name']); ?></p>
                                        <?php if ($area['description']): ?>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($area['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editArea(<?php echo $area['id']; ?>, '<?php echo addslashes($area['name']); ?>', '<?php echo addslashes($area['description'] ?? ''); ?>')"
                                                class="text-green-600 hover:text-green-900" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/devices/areas" class="inline"
                                              onsubmit="return confirm('¿Eliminar área <?php echo addslashes($area['name']); ?>?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="area_id" value="<?php echo $area['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Edit Area Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Editar Área</h3>
        <form method="POST" action="<?php echo BASE_URL; ?>/devices/areas">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="area_id" id="editAreaId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                <input type="text" name="name" id="editAreaName" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                <input type="text" name="description" id="editAreaDesc"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editArea(id, name, desc) {
    document.getElementById('editAreaId').value = id;
    document.getElementById('editAreaName').value = name;
    document.getElementById('editAreaDesc').value = desc;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
