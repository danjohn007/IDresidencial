<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">📋 Auditoría del Sistema</h1>
                    <p class="text-gray-600 mt-1">Registro de actividades y cambios en el sistema</p>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Actividad Hoy</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total_today']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Última Semana</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['total_week']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Usuarios Activos Hoy</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $stats['unique_users']; ?></p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/audit" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                        <select name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $filters['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Acción</label>
                        <select name="action" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas</option>
                            <option value="login" <?php echo $filters['action'] === 'login' ? 'selected' : ''; ?>>Login</option>
                            <option value="create" <?php echo $filters['action'] === 'create' ? 'selected' : ''; ?>>Crear</option>
                            <option value="update" <?php echo $filters['action'] === 'update' ? 'selected' : ''; ?>>Actualizar</option>
                            <option value="delete" <?php echo $filters['action'] === 'delete' ? 'selected' : ''; ?>>Eliminar</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                        <input type="date" name="date_from" value="<?php echo $filters['date_from']; ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                        <input type="date" name="date_to" value="<?php echo $filters['date_to']; ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-4 flex justify-end space-x-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i> Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>/audit" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>No hay registros de auditoría</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($log['username'] ?? 'Sistema'); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($log['role'] ?? ''); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?php 
                                            echo match($log['action']) {
                                                'login' => 'bg-blue-100 text-blue-800',
                                                'create' => 'bg-green-100 text-green-800',
                                                'update' => 'bg-yellow-100 text-yellow-800',
                                                'delete' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?php echo ucfirst($log['action']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($log['description']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($pagination['totalPages'] > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrando página <?php echo $pagination['page']; ?> de <?php echo $pagination['totalPages']; ?>
                        (<?php echo $pagination['totalRecords']; ?> registros totales)
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($pagination['page'] > 1): ?>
                            <a href="<?php echo BASE_URL; ?>/audit?page=<?php echo $pagination['page'] - 1; ?><?php 
                                echo $filters['user_id'] ? '&user_id=' . $filters['user_id'] : '';
                                echo $filters['action'] ? '&action=' . $filters['action'] : '';
                                echo $filters['date_from'] ? '&date_from=' . $filters['date_from'] : '';
                                echo $filters['date_to'] ? '&date_to=' . $filters['date_to'] : '';
                            ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                            <a href="<?php echo BASE_URL; ?>/audit?page=<?php echo $pagination['page'] + 1; ?><?php 
                                echo $filters['user_id'] ? '&user_id=' . $filters['user_id'] : '';
                                echo $filters['action'] ? '&action=' . $filters['action'] : '';
                                echo $filters['date_from'] ? '&date_from=' . $filters['date_from'] : '';
                                echo $filters['date_to'] ? '&date_to=' . $filters['date_to'] : '';
                            ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Siguiente
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
