<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-box mr-2 text-blue-600"></i>Mensajería</h1>
                    <p class="text-gray-600 mt-1">Recepción y gestión de paquetes</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/messaging/create"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i> Registrar Paquete
                </a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-600">Pendientes</p>
                    <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['pendiente'] ?? 0; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
                    <p class="text-sm text-gray-600">Pend. Confirmación</p>
                    <p class="text-2xl font-bold text-orange-600"><?php echo $stats['entregado_pendiente'] ?? 0; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600">Entregados</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo $stats['entregado'] ?? 0; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $total; ?></p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/messaging" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Propiedad, residente, rastreo..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Todos</option>
                            <option value="pendiente" <?php echo $status === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="entregado_pendiente" <?php echo $status === 'entregado_pendiente' ? 'selected' : ''; ?>>Entregado, Pend. Confirmación</option>
                            <option value="entregado" <?php echo $status === 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remitente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rastreo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recibido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($packages)): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">No se encontraron paquetes</td>
                        </tr>
                        <?php else: ?>
        <?php
        $typeLabels = ['paquete' => 'Paquete', 'sobre' => 'Sobre', 'documento' => 'Documento', 'otro' => 'Otro'];
        foreach ($packages as $pkg): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo $pkg['id']; ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($pkg['property_number'] ?? '-'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($pkg['resident_name'] ?? 'Sin asignar'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo $typeLabels[$pkg['package_type']] ?? ucfirst($pkg['package_type']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($pkg['sender'] ?? '-'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono"><?php echo htmlspecialchars($pkg['tracking_number'] ?? '-'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo date('d/m/Y H:i', strtotime($pkg['received_at'])); ?></td>
                            <td class="px-6 py-4">
                                <?php if ($pkg['status'] === 'pendiente'): ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pendiente</span>
                                <?php elseif ($pkg['status'] === 'entregado_pendiente'): ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">Entregado, Pend. Confirmación</span>
                                <?php else: ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Entregado</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ($pkg['status'] === 'pendiente'): ?>
                                    <button type="button"
                                            onclick="toggleDeliveryForm(<?php echo $pkg['id']; ?>)"
                                            class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition">
                                        <i class="fas fa-check mr-1"></i> Entregar
                                    </button>
                                <?php else: ?>
                                <span class="text-xs text-gray-400">
                                    <?php echo $pkg['delivered_at'] ? date('d/m/Y H:i', strtotime($pkg['delivered_at'])) : '-'; ?>
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($pkg['status'] === 'pendiente'): ?>
                        <tr id="delivery-form-row-<?php echo $pkg['id']; ?>" class="hidden bg-gray-50">
                            <td colspan="9" class="px-6 py-4">
                                <form method="POST"
                                      action="<?php echo BASE_URL; ?>/messaging/deliver/<?php echo $pkg['id']; ?>"
                                      enctype="multipart/form-data"
                                      class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                                    <div>
                                        <label for="receiver-name-<?php echo $pkg['id']; ?>" class="block text-xs font-medium text-gray-600 mb-1">Recibe <span class="text-red-500">*</span></label>
                                        <input type="text" name="receiver_name" required
                                               id="receiver-name-<?php echo $pkg['id']; ?>"
                                               aria-label="Recibe"
                                               oninput="toggleDeliverySubmit(<?php echo $pkg['id']; ?>)"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                               placeholder="Nombre de quien recibe">
                                    </div>
                                    <div>
                                        <label for="delivery-evidence-<?php echo $pkg['id']; ?>" class="block text-xs font-medium text-gray-600 mb-1">Evidencia</label>
                                        <input type="file" name="delivery_evidence" accept="image/*"
                                               id="delivery-evidence-<?php echo $pkg['id']; ?>"
                                               aria-label="Evidencia"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                                    </div>
                                    <div>
                                        <label for="delivery-key-input-<?php echo $pkg['id']; ?>" class="block text-xs font-medium text-gray-600 mb-1">Clave de entrega <span class="text-red-500">*</span></label>
                                        <input type="text" name="delivery_key" required
                                               id="delivery-key-input-<?php echo $pkg['id']; ?>"
                                               aria-label="Clave de entrega"
                                               oninput="toggleDeliverySubmit(<?php echo $pkg['id']; ?>)"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase"
                                               placeholder="Ej. ABCD1234">
                                    </div>
                                    <div>
                                        <button type="submit"
                                                id="confirm-delivery-btn-<?php echo $pkg['id']; ?>"
                                                class="hidden w-full inline-flex justify-center items-center px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition"
                                                >
                                            Confirmar entrega
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center mt-6 space-x-2">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>"
                   class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
function toggleDeliveryForm(packageId) {
    const row = document.getElementById('delivery-form-row-' + packageId);
    if (row) {
        row.classList.toggle('hidden');
    }
}

function toggleDeliverySubmit(packageId) {
    const receiver = document.getElementById('receiver-name-' + packageId);
    const key = document.getElementById('delivery-key-input-' + packageId);
    const submit = document.getElementById('confirm-delivery-btn-' + packageId);

    if (!receiver || !key || !submit) {
        return;
    }

    key.value = key.value.toUpperCase();
    const canSubmit = receiver.value.trim() !== '' && key.value.trim() !== '';
    submit.classList.toggle('hidden', !canSubmit);
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
