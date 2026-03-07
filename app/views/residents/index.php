<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">👥 Residentes</h1>
                    <p class="text-gray-600 mt-1">Gestión de residentes y propiedades</p>
                </div>
                <div class="space-x-2">
                    <a href="<?php echo BASE_URL; ?>/residents/create" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-user-plus mr-2"></i> Nuevo Residente
                    </a>
                    <a href="<?php echo BASE_URL; ?>/residents/properties" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-home mr-2"></i> Propiedades
                    </a>
                    <a href="<?php echo BASE_URL; ?>/residents/payments" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-dollar-sign mr-2"></i> Pagos
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Total Residentes</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Propietarios</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['propietarios']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Inquilinos</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['inquilinos']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Familiares</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $stats['familiares']; ?></p>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/residents" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2"></i>Buscar por Nombre, Teléfono o Correo
                        </label>
                        <input type="text" name="search"
                               value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                               placeholder="Nombre, apellido, teléfono o correo..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Activo</option>
                                <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Relación</label>
                            <select name="relationship" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <option value="propietario" <?php echo ($filters['relationship'] ?? '') === 'propietario' ? 'selected' : ''; ?>>Propietario</option>
                                <option value="inquilino" <?php echo ($filters['relationship'] ?? '') === 'inquilino' ? 'selected' : ''; ?>>Inquilino</option>
                                <option value="familiar" <?php echo ($filters['relationship'] ?? '') === 'familiar' ? 'selected' : ''; ?>>Familiar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sección</label>
                            <select name="section" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <?php foreach ($sections as $sec): ?>
                                    <option value="<?php echo htmlspecialchars($sec); ?>" <?php echo ($filters['section'] ?? '') === $sec ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sec); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2 pt-5">
                            <input type="checkbox" name="is_vigilance_committee" id="committee_filter" value="1"
                                   <?php echo !empty($filters['is_vigilance_committee']) ? 'checked' : ''; ?>
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="committee_filter" class="text-sm text-gray-700">Comité de Vigilancia y Administración</label>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                            <a href="<?php echo BASE_URL; ?>/residents" class="px-3 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Residents Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sección</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Relación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de Ingreso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($residents as $resident): ?>
                            <?php $isCommittee = !empty($resident['is_vigilance_committee']); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 <?php echo $isCommittee ? 'bg-orange-500' : 'bg-blue-600'; ?> rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                            <?php echo strtoupper(substr($resident['first_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <a href="<?php echo BASE_URL; ?>/residents/viewDetails/<?php echo $resident['id']; ?>"
                                               class="font-medium hover:underline <?php echo $isCommittee ? 'text-orange-600' : 'text-gray-900'; ?>">
                                                <?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?>
                                                <?php if ($isCommittee): ?>
                                                    <i class="fas fa-shield-alt text-xs ml-1" title="Comité de Vigilancia y Administración"></i>
                                                <?php endif; ?>
                                            </a>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($resident['email']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($resident['property_number']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($resident['section']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo match($resident['relationship']) {
                                            'propietario' => 'bg-green-100 text-green-800',
                                            'inquilino' => 'bg-yellow-100 text-yellow-800',
                                            'familiar' => 'bg-purple-100 text-purple-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($resident['relationship']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($resident['phone']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo !empty($resident['created_at']) ? date('d/m/Y', strtotime($resident['created_at'])) : '—'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo $resident['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                    ?>">
                                        <?php echo ucfirst($resident['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?php echo BASE_URL; ?>/residents/viewDetails/<?php echo $resident['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/residents/edit/<?php echo $resident['id']; ?>" 
                                           class="text-green-600 hover:text-green-900" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($resident['status'] === 'active'): ?>
                                        <button onclick="confirmSuspend(<?php echo $resident['id']; ?>, '<?php echo addslashes($resident['first_name'] . ' ' . $resident['last_name']); ?>')" 
                                                class="text-yellow-600 hover:text-yellow-900" title="Suspender">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                        <?php else: ?>
                                        <button onclick="confirmActivate(<?php echo $resident['id']; ?>, '<?php echo addslashes($resident['first_name'] . ' ' . $resident['last_name']); ?>')" 
                                                class="text-green-600 hover:text-green-900" title="Activar">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_URL; ?>/residents/accountStatement/<?php echo $resident['id']; ?>"
                                           class="text-purple-600 hover:text-purple-900" title="Ver Estado de Cuenta">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script>
function confirmSuspend(residentId, residentName) {
    if (confirm(`¿Está seguro que desea suspender al residente ${residentName}?`)) {
        window.location.href = `<?php echo BASE_URL; ?>/residents/suspend/${residentId}`;
    }
}

function confirmActivate(residentId, residentName) {
    if (confirm(`¿Está seguro que desea activar al residente ${residentName}?`)) {
        window.location.href = `<?php echo BASE_URL; ?>/residents/activate/${residentId}`;
    }
}

function confirmDelete(residentId, residentName) {
    if (confirm(`¿Está seguro que desea eliminar al residente ${residentName}?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = `<?php echo BASE_URL; ?>/residents/delete/${residentId}`;
    }
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
