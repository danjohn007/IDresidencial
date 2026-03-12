<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-clipboard-list mr-2 text-blue-600"></i>Solicitudes de Servicio</h1>
                    <p class="text-gray-600 mt-1">Seguimiento de servicios y solicitudes de mantenimiento</p>
                </div>
                <div class="flex space-x-2">
                    <a href="<?php echo BASE_URL; ?>/providers/createRequest" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Nueva Solicitud
                    </a>
                    <a href="<?php echo BASE_URL; ?>/providers" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i> Proveedores
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/providers/requests" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>"
                               placeholder="Buscar por título o descripción..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los estados</option>
                            <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="in_progress" <?php echo $filters['status'] === 'in_progress' ? 'selected' : ''; ?>>En Proceso</option>
                            <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completado</option>
                            <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas las prioridades</option>
                            <option value="urgent" <?php echo $filters['priority'] === 'urgent' ? 'selected' : ''; ?>>Urgente</option>
                            <option value="high" <?php echo $filters['priority'] === 'high' ? 'selected' : ''; ?>>Alta</option>
                            <option value="medium" <?php echo $filters['priority'] === 'medium' ? 'selected' : ''; ?>>Media</option>
                            <option value="low" <?php echo $filters['priority'] === 'low' ? 'selected' : ''; ?>>Baja</option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>/providers/requests" class="px-3 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Solicitada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Costo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Imagen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl mb-2 text-gray-300"></i>
                                <p>No se encontraron solicitudes</p>
                                <a href="<?php echo BASE_URL; ?>/providers/createRequest" class="mt-2 inline-block text-blue-600 hover:underline">
                                    Crear la primera solicitud
                                </a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                        <?php
                            $priorityColors = [
                                'urgent' => 'bg-red-100 text-red-800',
                                'high'   => 'bg-orange-100 text-orange-800',
                                'medium' => 'bg-yellow-100 text-yellow-800',
                                'low'    => 'bg-gray-100 text-gray-600',
                            ];
                            $statusColors = [
                                'pending'     => 'bg-yellow-100 text-yellow-800',
                                'in_progress' => 'bg-blue-100 text-blue-800',
                                'completed'   => 'bg-green-100 text-green-800',
                                'cancelled'   => 'bg-red-100 text-red-800',
                            ];
                            $statusLabels = [
                                'pending'     => 'Pendiente',
                                'in_progress' => 'En Proceso',
                                'completed'   => 'Completado',
                                'cancelled'   => 'Cancelado',
                            ];
                            $priorityLabels = [
                                'urgent' => 'Urgente',
                                'high'   => 'Alta',
                                'medium' => 'Media',
                                'low'    => 'Baja',
                            ];
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($req['title']); ?></div>
                                <?php if ($req['category']): ?>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($req['category']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($req['provider_name'] ?? '—'); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $priorityColors[$req['priority']] ?? 'bg-gray-100 text-gray-600'; ?>">
                                    <?php echo $priorityLabels[$req['priority']] ?? $req['priority']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $statusColors[$req['status']] ?? 'bg-gray-100 text-gray-600'; ?>">
                                    <?php echo $statusLabels[$req['status']] ?? $req['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo $req['requested_date'] ? date('d/m/Y', strtotime($req['requested_date'])) : '—'; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php if ($req['actual_cost']): ?>
                                <span class="font-medium">$<?php echo number_format($req['actual_cost'], 2); ?></span>
                                <?php elseif ($req['estimated_cost']): ?>
                                <span class="text-gray-400">~$<?php echo number_format($req['estimated_cost'], 2); ?></span>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if (!empty($req['image_path'])): ?>
                                <a href="<?php echo PUBLIC_URL . '/' . htmlspecialchars($req['image_path']); ?>"
                                   target="_blank" title="Ver imagen adjunta">
                                    <img src="<?php echo PUBLIC_URL . '/' . htmlspecialchars($req['image_path']); ?>"
                                         alt="Imagen adjunta"
                                         class="w-12 h-12 object-cover rounded border border-gray-200 hover:opacity-80">
                                </a>
                                <?php else: ?>
                                <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($req['status'] !== 'completed' && $req['status'] !== 'cancelled'): ?>
                                <button onclick="updateStatus(<?php echo $req['id']; ?>)"
                                        class="text-blue-600 hover:text-blue-900 mr-2" title="Actualizar estado">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Actualizar Estado</h3>
        <form id="statusForm" method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Estado</label>
                <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="pending">Pendiente</option>
                    <option value="in_progress">En Proceso</option>
                    <option value="completed">Completado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Costo Real (opcional)</label>
                <input type="number" step="0.01" name="actual_cost" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                       placeholder="0.00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tarifa del Proveedor (opcional)</label>
                <input type="number" step="0.01" name="rate" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                       placeholder="0.00">
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Actualizar</button>
                <button type="button" onclick="closeStatusModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
var baseUrl = <?php echo json_encode(BASE_URL); ?>;
function updateStatus(requestId) {
    document.getElementById('statusForm').action = baseUrl + '/providers/updateRequestStatus/' + requestId;
    document.getElementById('statusModal').classList.remove('hidden');
}
function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
