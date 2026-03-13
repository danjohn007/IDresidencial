<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>Solicitudes de Servicio
                        <?php if (!empty($requests)): ?>
                        <span class="ml-2 px-3 py-1 text-lg bg-blue-100 text-blue-700 rounded-full"><?php echo count($requests); ?></span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Servicios para la propiedad
                        <span class="font-medium"><?php echo htmlspecialchars($resident['property_number']); ?></span>
                    </p>
                </div>
                <button onclick="openRequestModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Nueva Solicitud
                </button>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/residents/serviceRequests" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <div class="flex space-x-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>/residents/serviceRequests" class="px-3 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
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
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Solicitada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Programada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Costo</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Detalles</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl mb-2 text-gray-300"></i>
                                <p>No se encontraron solicitudes de servicio para su propiedad</p>
                                <button onclick="openRequestModal()" class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i> Crear Primera Solicitud
                                </button>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($req['title']); ?></div>
                                <?php if ($req['category']): ?>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($req['category']); ?></div>
                                <?php endif; ?>
                                <?php if ($req['description']): ?>
                                <div class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($req['description']); ?></div>
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
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo $req['scheduled_date'] ? date('d/m/Y', strtotime($req['scheduled_date'])) : '—'; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php if ($req['actual_cost']): ?>
                                <span class="font-medium">$<?php echo number_format($req['actual_cost'], 2); ?></span>
                                <?php elseif ($req['estimated_cost']): ?>
                                <span class="text-gray-400">~$<?php echo number_format($req['estimated_cost'], 2); ?></span>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="toggleDetails(<?php echo $req['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-800 text-sm"
                                        title="Ver más detalles">
                                    <i class="fas fa-chevron-down" id="icon-<?php echo $req['id']; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <!-- Fila expandible con detalles adicionales -->
                        <tr id="details-<?php echo $req['id']; ?>" class="hidden bg-gray-50">
                            <td colspan="8" class="px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-700">Descripción:</span>
                                        <p class="text-gray-600 mt-1"><?php echo nl2br(htmlspecialchars($req['description'] ?: '—')); ?></p>
                                    </div>
                                    <?php if ($req['area']): ?>
                                    <div>
                                        <span class="font-medium text-gray-700">Área:</span>
                                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($req['area']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($req['notes']): ?>
                                    <div class="md:col-span-2">
                                        <span class="font-medium text-gray-700">Notas:</span>
                                        <p class="text-gray-600 mt-1"><?php echo nl2br(htmlspecialchars($req['notes'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($req['image_path'])): ?>
                                    <div class="md:col-span-2">
                                        <span class="font-medium text-gray-700">Imagen Adjunta:</span>
                                        <div class="mt-2">
                                            <a href="<?php echo BASE_URL . htmlspecialchars($req['image_path']); ?>" target="_blank" class="inline-block">
                                                <img src="<?php echo BASE_URL . htmlspecialchars($req['image_path']); ?>" 
                                                     alt="Imagen de servicio" 
                                                     class="max-w-xs max-h-64 rounded-lg shadow-md border border-gray-200 hover:opacity-90 transition-opacity cursor-pointer"
                                                     onerror="this.parentElement.innerHTML='<span class=\'text-red-500 text-xs\'>Error al cargar imagen</span>'">
                                            </a>
                                            <p class="text-xs text-gray-500 mt-1">Click para ver en tamaño completo</p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($req['completed_date']): ?>
                                    <div>
                                        <span class="font-medium text-gray-700">Fecha de Completado:</span>
                                        <p class="text-gray-600 mt-1"><?php echo date('d/m/Y', strtotime($req['completed_date'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <span class="font-medium text-gray-700">Creada el:</span>
                                        <p class="text-gray-600 mt-1"><?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?></p>
                                    </div>
                                    <?php if ($req['updated_at'] && $req['updated_at'] !== $req['created_at']): ?>
                                    <div>
                                        <span class="font-medium text-gray-700">Última actualización:</span>
                                        <p class="text-gray-600 mt-1"><?php echo date('d/m/Y H:i', strtotime($req['updated_at'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
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

<!-- Modal for New Service Request -->
<div id="requestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-plus-circle text-blue-600 mr-2"></i>Nueva Solicitud de Servicio
            </h3>
            <button type="button" onclick="closeRequestModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="requestForm" method="POST" action="<?php echo BASE_URL; ?>/residents/createServiceRequest" enctype="multipart/form-data">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required maxlength="200"
                           placeholder="Ej: Reparación de tubería en baño"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="provider_id" class="block text-sm font-medium text-gray-700 mb-1">Proveedor Preferido</label>
                    <select id="provider_id" name="provider_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Sin preferencia (la administración asignará)</option>
                        <?php foreach ($providers as $prov): ?>
                        <option value="<?php echo $prov['id']; ?>">
                            <?php echo htmlspecialchars($prov['company_name']); ?><?php if (!empty($prov['category'])): ?> — <?php echo htmlspecialchars($prov['category']); ?><?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccione una categoría...</option>
                            <option value="Plomería">Plomería</option>
                            <option value="Electricidad">Electricidad</option>
                            <option value="Carpintería">Carpintería</option>
                            <option value="Pintura">Pintura</option>
                            <option value="Limpieza">Limpieza</option>
                            <option value="Jardinería">Jardinería</option>
                            <option value="Albañilería">Albañilería</option>
                            <option value="Seguridad">Seguridad</option>
                            <option value="Climatización">Climatización</option>
                            <option value="Computación">Computación</option>
                            <option value="General">General</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad <span class="text-red-500">*</span></label>
                        <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="low">Baja</option>
                            <option value="medium" selected>Media</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                    <textarea name="description" required rows="4" maxlength="1000"
                              placeholder="Describa detalladamente el servicio que necesita..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Máximo 1000 caracteres</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Área</label>
                    <input type="text" name="area" maxlength="100"
                           placeholder="Ej: Sala, Cocina, Baño principal, Jardín..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Deseada</label>
                    <input type="date" name="requested_date"
                           min="<?php echo date('Y-m-d'); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas Adicionales</label>
                    <textarea name="notes" rows="2" maxlength="500"
                              placeholder="Información adicional relevante..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-image text-gray-400 mr-1"></i>Imagen (Opcional)
                    </label>
                    <input type="file" name="service_image" id="serviceImage" accept="image/jpeg,image/jpg,image/png,image/webp"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG o WEBP. Máximo 5MB</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Su solicitud será registrada para la propiedad <strong><?php echo htmlspecialchars($resident['property_number']); ?></strong>. Si selecciona un proveedor preferido, la administración lo tomará en cuenta al asignar el servicio.
                    </p>
                </div>
            </div>
            
            <div id="requestModalError" class="hidden mx-6 mb-2 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded"></div>
            
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3 sticky bottom-0 bg-white">
                <button type="button" onclick="closeRequestModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancelar
                </button>
                <button type="submit" id="submitRequestBtn"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-[9999] space-y-2"></div>

<style>
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.toast-enter {
    animation: slideInRight 0.3s ease-out forwards;
}

.toast-exit {
    animation: slideOutRight 0.3s ease-in forwards;
}
</style>

<script>
function toggleDetails(requestId) {
    const detailsRow = document.getElementById('details-' + requestId);
    const icon = document.getElementById('icon-' + requestId);
    
    if (detailsRow.classList.contains('hidden')) {
        detailsRow.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        detailsRow.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function openRequestModal() {
    document.getElementById('requestModal').classList.remove('hidden');
}

function closeRequestModal() {
    document.getElementById('requestModal').classList.add('hidden');
    document.getElementById('requestForm').reset();
    document.getElementById('requestModalError').classList.add('hidden');
}

function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const toastId = 'toast-' + Date.now();
    toast.id = toastId;
    toast.className = 'flex items-start p-4 rounded-lg shadow-lg min-w-[300px] max-w-md toast-enter';
    
    const typeStyles = {
        success: {
            bg: 'bg-green-50 border border-green-200',
            icon: 'fas fa-check-circle text-green-500',
            text: 'text-green-800'
        },
        error: {
            bg: 'bg-red-50 border border-red-200',
            icon: 'fas fa-exclamation-circle text-red-500',
            text: 'text-red-800'
        },
        info: {
            bg: 'bg-blue-50 border border-blue-200',
            icon: 'fas fa-info-circle text-blue-500',
            text: 'text-blue-800'
        }
    };
    
    const style = typeStyles[type] || typeStyles.info;
    toast.className += ' ' + style.bg;
    
    toast.innerHTML = `
        <div class="flex-shrink-0">
            <i class="${style.icon} text-xl"></i>
        </div>
        <div class="ml-3 flex-1">
            <p class="${style.text} text-sm font-medium">${message}</p>
        </div>
        <button onclick="closeToast('${toastId}')" class="ml-3 flex-shrink-0 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    setTimeout(() => closeToast(toastId), duration);
}

function closeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.remove('toast-enter');
        toast.classList.add('toast-exit');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

document.getElementById('requestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitRequestBtn');
    const errorDiv = document.getElementById('requestModalError');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
    errorDiv.classList.add('hidden');
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/residents/createServiceRequest', {
        method: 'POST',
        body: formData
    })
    .then(r => {
        if (!r.ok) {
            throw new Error('HTTP error! status: ' + r.status);
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            closeRequestModal();
            showToast('✅ Solicitud enviada exitosamente. La administración la revisará pronto.', 'success', 5000);
            setTimeout(() => location.reload(), 2000);
        } else {
            errorDiv.textContent = data.message || 'Error al enviar la solicitud';
            errorDiv.classList.remove('hidden');
            showToast(data.message || 'Error al enviar la solicitud', 'error', 5000);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar Solicitud';
        }
    })
    .catch((error) => {
        console.error('Error details:', error);
        errorDiv.textContent = 'Error de conexión: ' + error.message;
        errorDiv.classList.remove('hidden');
        showToast('Error de conexión. Intente nuevamente.', 'error', 5000);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar Solicitud';
    });
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
