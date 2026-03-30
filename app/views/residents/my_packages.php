<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-box mr-2 text-blue-600"></i>Mensajería
                </h1>
                <p class="text-gray-600 mt-1">Historial de paquetes de la propiedad
                    <span class="font-medium"><?php echo htmlspecialchars($resident['property_number']); ?></span>
                </p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded alert-auto-hide">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-600">Pendientes</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo ($stats['pendiente'] ?? 0) + ($stats['entregado_pendiente'] ?? 0); ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600">Entregados</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['entregado'] ?? 0; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo array_sum($stats); ?></p>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/residents/myPackages" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="pendiente" <?php echo $status === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="entregado_pendiente" <?php echo $status === 'entregado_pendiente' ? 'selected' : ''; ?>>Entregado, Pend. Confirmación</option>
                            <option value="entregado" <?php echo $status === 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>/residents/myPackages" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remitente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rastreo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Recibido</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($packages)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-box-open text-4xl mb-3 block"></i>
                                    No hay paquetes registrados<?php echo !empty($status) ? ' con ese estado' : ''; ?>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($packages as $i => $pkg): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900"><?php echo $i + 1; ?></td>
                                <td class="px-6 py-4 text-sm text-blue-600 capitalize">
                                    <?php echo htmlspecialchars($pkg['package_type']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($pkg['sender'] ?? '-'); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                    <?php echo htmlspecialchars($pkg['tracking_number'] ?? '-'); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo date('d/m/Y H:i', strtotime($pkg['received_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($pkg['status'] === 'pendiente'): ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pendiente</span>
                                    <?php elseif ($pkg['status'] === 'entregado_pendiente'): ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">Entregado, Pend. Confirmación</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Entregado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($pkg['status'] === 'pendiente' || $pkg['status'] === 'entregado_pendiente'): ?>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/residents/confirmPackageReceipt/<?php echo $pkg['id']; ?>"
                                          onsubmit="return confirm('¿Confirmas que recibiste este paquete?')">
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                                            <i class="fas fa-check mr-1"></i> Confirmar recibido
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <?php if (!empty($pkg['delivered_at'])): ?>
                                    <span class="text-xs text-gray-400">
                                        Recibido <?php echo date('d/m/Y H:i', strtotime($pkg['delivered_at'])); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400">—</span>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
