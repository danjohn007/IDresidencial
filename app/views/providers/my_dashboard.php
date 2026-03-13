<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-hard-hat mr-2 text-yellow-600"></i>Mi Panel de Proveedor
                    </h1>
                    <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($provider['company_name']); ?></p>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/providers/myDashboard" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>"
                               placeholder="Buscar solicitud..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                    </div>
                    <div>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                            <option value="">Todos los estados</option>
                            <option value="pending"     <?php echo $filters['status'] === 'pending'     ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="in_progress" <?php echo $filters['status'] === 'in_progress' ? 'selected' : ''; ?>>En Proceso</option>
                            <option value="completed"   <?php echo $filters['status'] === 'completed'   ? 'selected' : ''; ?>>Completado</option>
                            <option value="cancelled"   <?php echo $filters['status'] === 'cancelled'   ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                            <option value="">Todas las prioridades</option>
                            <option value="urgent" <?php echo $filters['priority'] === 'urgent' ? 'selected' : ''; ?>>Urgente</option>
                            <option value="high"   <?php echo $filters['priority'] === 'high'   ? 'selected' : ''; ?>>Alta</option>
                            <option value="medium" <?php echo $filters['priority'] === 'medium' ? 'selected' : ''; ?>>Media</option>
                            <option value="low"    <?php echo $filters['priority'] === 'low'    ? 'selected' : ''; ?>>Baja</option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>/providers/myDashboard"
                           class="px-3 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Requests Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitud</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Costo / Tarifa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Imagen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl mb-2 text-gray-300"></i>
                                <p>No se encontraron solicitudes asignadas</p>
                            </td>
                        </tr>
                        <?php else: ?>
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
                        <?php foreach ($requests as $req): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($req['title']); ?></div>
                                <?php if (!empty($req['description'])): ?>
                                <div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars(mb_substr($req['description'], 0, 80)) . (mb_strlen($req['description']) > 80 ? '…' : ''); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($req['category'])): ?>
                                <div class="text-xs text-gray-400 mt-1"><i class="fas fa-tag mr-1"></i><?php echo htmlspecialchars($req['category']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($req['property_number'] ?? '—'); ?>
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
                                <div><span class="font-medium">Real: $<?php echo number_format($req['actual_cost'], 2); ?></span></div>
                                <?php elseif ($req['estimated_cost']): ?>
                                <div><span class="text-gray-400">Est: ~$<?php echo number_format($req['estimated_cost'], 2); ?></span></div>
                                <?php endif; ?>
                                <?php if ($req['rate']): ?>
                                <div class="text-xs text-green-700 mt-1"><i class="fas fa-tag mr-1"></i>Tarifa: $<?php echo number_format($req['rate'], 2); ?></div>
                                <?php endif; ?>
                                <?php if (!$req['actual_cost'] && !$req['estimated_cost'] && !$req['rate']): ?>—<?php endif; ?>
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
                                <button onclick="updateStatus(<?php echo $req['id']; ?>, '<?php echo addslashes($req['status']); ?>', <?php echo $req['actual_cost'] ? $req['actual_cost'] : 'null'; ?>, <?php echo $req['rate'] ? $req['rate'] : 'null'; ?>)"
                                        class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                    <i class="fas fa-sync-alt mr-1"></i> Actualizar
                                </button>
                                <?php else: ?>
                                <span class="text-gray-400 text-xs">—</span>
                                <?php endif; ?>
                                <button onclick="viewDetail(<?php echo htmlspecialchars(json_encode($req), ENT_QUOTES); ?>)"
                                        class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 mt-1">
                                    <i class="fas fa-eye mr-1"></i> Ver detalle
                                </button>
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
        <h3 class="text-lg font-semibold mb-4"><i class="fas fa-sync-alt mr-2 text-blue-600"></i>Actualizar Solicitud</h3>
        <form id="statusForm" method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Estado <span class="text-red-500">*</span></label>
                <select name="status" id="modalStatus" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="pending">Pendiente</option>
                    <option value="in_progress">En Proceso</option>
                    <option value="completed">Completado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mi Tarifa (MXN)</label>
                <input type="number" step="0.01" name="rate" id="modalRate" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="0.00">
                <p class="text-xs text-gray-500 mt-1">Establece tu tarifa para este servicio.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Costo Real (MXN)</label>
                <input type="number" step="0.01" name="actual_cost" id="modalCost" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="0.00">
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Guardar
                </button>
                <button type="button" onclick="closeStatusModal()"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var baseUrl = <?php echo json_encode(BASE_URL); ?>;
function updateStatus(requestId, currentStatus, currentCost, currentRate) {
    document.getElementById('statusForm').action = baseUrl + '/providers/updateRequestStatus/' + requestId;
    document.getElementById('modalStatus').value = currentStatus || 'pending';
    document.getElementById('modalCost').value   = currentCost  || '';
    document.getElementById('modalRate').value   = currentRate  || '';
    document.getElementById('statusModal').classList.remove('hidden');
}
function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

var priorityLabels = {urgent:'Urgente',high:'Alta',medium:'Media',low:'Baja'};
var statusLabels = {pending:'Pendiente',in_progress:'En Proceso',completed:'Completado',cancelled:'Cancelado'};

function viewDetail(req) {
    document.getElementById('detailTitle').textContent = req.title || '—';
    document.getElementById('detailCategory').textContent = req.category || '—';
    document.getElementById('detailArea').textContent = req.area || '—';
    document.getElementById('detailProperty').textContent = req.property_number || '—';
    document.getElementById('detailPriority').textContent = priorityLabels[req.priority] || req.priority;
    document.getElementById('detailStatus').textContent = statusLabels[req.status] || req.status;
    document.getElementById('detailDescription').textContent = req.description || '—';
    document.getElementById('detailNotes').textContent = req.notes || '—';
    document.getElementById('detailRequestedDate').textContent = req.requested_date || '—';
    document.getElementById('detailScheduledDate').textContent = req.scheduled_date || '—';
    document.getElementById('detailCompletedDate').textContent = req.completed_date || '—';
    document.getElementById('detailEstimatedCost').textContent = req.estimated_cost ? '$' + parseFloat(req.estimated_cost).toFixed(2) : '—';
    document.getElementById('detailActualCost').textContent = req.actual_cost ? '$' + parseFloat(req.actual_cost).toFixed(2) : '—';
    document.getElementById('detailRate').textContent = req.rate ? '$' + parseFloat(req.rate).toFixed(2) : '—';
    document.getElementById('detailCreatedAt').textContent = req.created_at || '—';
    var imgWrap = document.getElementById('detailImageWrap');
    if (req.image_path) {
        var imgPath = String(req.image_path).replace(/[^a-zA-Z0-9/_.\-]/g, '');
        var imgUrl = baseUrl + '/../' + imgPath;
        var link = document.createElement('a');
        link.href = imgUrl;
        link.target = '_blank';
        var img = document.createElement('img');
        img.src = imgUrl;
        img.className = 'max-h-48 rounded border border-gray-200';
        img.alt = 'Imagen adjunta';
        link.appendChild(img);
        imgWrap.innerHTML = '';
        imgWrap.appendChild(link);
    } else {
        imgWrap.textContent = 'Sin imagen';
    }
    document.getElementById('detailModal').classList.remove('hidden');
}
function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

// Auto-hide success messages
document.querySelectorAll('.alert-auto-hide').forEach(function(el) {
    setTimeout(function() { el.style.display = 'none'; }, 5000);
});
</script>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-clipboard-list mr-2 text-blue-600"></i>Detalle de Solicitud</h3>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Título</p>
                <p id="detailTitle" class="text-gray-900 font-semibold mt-1"></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Categoría</p>
                    <p id="detailCategory" class="text-gray-700 mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Área</p>
                    <p id="detailArea" class="text-gray-700 mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Propiedad</p>
                    <p id="detailProperty" class="text-gray-700 mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Prioridad</p>
                    <p id="detailPriority" class="mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Estado</p>
                    <p id="detailStatus" class="mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Mi Tarifa</p>
                    <p id="detailRate" class="text-gray-700 mt-1"></p>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Descripción</p>
                <p id="detailDescription" class="text-gray-700 mt-1 whitespace-pre-wrap"></p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Notas</p>
                <p id="detailNotes" class="text-gray-700 mt-1 whitespace-pre-wrap"></p>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Fecha Solicitada</p>
                    <p id="detailRequestedDate" class="text-gray-700 mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Fecha Programada</p>
                    <p id="detailScheduledDate" class="text-gray-700 mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Fecha Completado</p>
                    <p id="detailCompletedDate" class="text-gray-700 mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Costo Estimado</p>
                    <p id="detailEstimatedCost" class="text-gray-700 mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Costo Real</p>
                    <p id="detailActualCost" class="text-gray-700 mt-1"></p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Creada</p>
                    <p id="detailCreatedAt" class="text-gray-700 mt-1"></p>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Imagen</p>
                <div id="detailImageWrap" class="mt-1"></div>
            </div>
        </div>
        <div class="p-4 border-t flex justify-end">
            <button onclick="closeDetailModal()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Cerrar</button>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
