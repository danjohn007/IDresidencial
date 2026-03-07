<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">👥 Gestión de Usuarios</h1>
                    <p class="text-gray-600 mt-1">Administración de usuarios del sistema</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/users/create" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-user-plus mr-2"></i> Nuevo Usuario
                </a>
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

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Total Usuarios</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Activos</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['active']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Administradores</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $stats['administrador'] + $stats['superadmin']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Residentes</p>
                    <p class="text-3xl font-bold text-orange-600"><?php echo $stats['residente']; ?></p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/users" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search by name, email, or phone -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                            <input type="text" name="search" 
                                   value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                                   placeholder="Nombre, correo o teléfono..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <!-- Filter by role -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                            <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos los roles</option>
                                <option value="superadmin" <?php echo ($filters['role'] ?? '') === 'superadmin' ? 'selected' : ''; ?>>Super Admin</option>
                                <option value="administrador" <?php echo ($filters['role'] ?? '') === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                <option value="guardia" <?php echo ($filters['role'] ?? '') === 'guardia' ? 'selected' : ''; ?>>Guardia</option>
                                <option value="residente" <?php echo ($filters['role'] ?? '') === 'residente' ? 'selected' : ''; ?>>Residente</option>
                            </select>
                        </div>
                        
                        <!-- Filter by status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos los estados</option>
                                <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Activo</option>
                                <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                                <option value="blocked" <?php echo ($filters['status'] ?? '') === 'blocked' ? 'selected' : ''; ?>>Bloqueado</option>
                                <option value="pending" <?php echo ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <a href="<?php echo BASE_URL; ?>/users" 
                           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-times mr-2"></i>Limpiar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Último Login</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">C. Vigilancia</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo match($user['role']) {
                                            'superadmin' => 'bg-purple-100 text-purple-800',
                                            'administrador' => 'bg-blue-100 text-blue-800',
                                            'guardia' => 'bg-green-100 text-green-800',
                                            'residente' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                    ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <?php if (!empty($user['is_vigilance_committee'])): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Sí</span>
                                    <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <a href="<?php echo BASE_URL; ?>/users/viewDetails/<?php echo $user['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/users/edit/<?php echo $user['id']; ?>" 
                                       class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="<?php echo BASE_URL; ?>/users/toggleStatus/<?php echo $user['id']; ?>" 
                                           class="text-yellow-600 hover:text-yellow-900"
                                           onclick="return confirm('¿Cambiar el estado de este usuario?')">
                                            <i class="fas fa-power-off"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
