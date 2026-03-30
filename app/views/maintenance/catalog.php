<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <!-- Page header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-book mr-2 text-blue-600"></i>Catálogo de Incidencias Fijas
                    </h1>
                    <p class="text-gray-600 mt-1">Incidencias de mantenimiento recurrentes que generan reportes automáticamente</p>
                </div>
                <div class="flex items-center space-x-2">
                    <form method="POST" action="<?php echo BASE_URL; ?>/maintenance/catalogGenerate"
                          onsubmit="return confirm('¿Generar reportes para todas las incidencias vencidas?');">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-sync-alt mr-2"></i> Generar Reportes
                        </button>
                    </form>
                    <a href="<?php echo BASE_URL; ?>/maintenance/catalogCreate"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i> Nueva Incidencia
                    </a>
                    <a href="<?php echo BASE_URL; ?>/maintenance/commonAreas"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                </div>
            </div>

            <!-- Flash messages -->
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

            <!-- Catalog list -->
            <?php if (empty($items)): ?>
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                    <i class="fas fa-book text-4xl mb-3 text-gray-300"></i>
                    <p class="mb-2">No hay incidencias fijas registradas en el catálogo.</p>
                    <a href="<?php echo BASE_URL; ?>/maintenance/catalogCreate"
                       class="inline-flex items-center mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i> Agregar Primera Incidencia
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($items as $item): ?>
                        <?php
                            $isDue     = $item['next_due'] && $item['next_due'] <= date('Y-m-d') && $item['active'];
                            $isSoon    = !$isDue && $item['next_due'] && $item['next_due'] <= date('Y-m-d', strtotime('+7 days')) && $item['active'];
                        ?>
                        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition <?php echo $isDue ? 'border-l-4 border-red-500' : ($isSoon ? 'border-l-4 border-yellow-400' : ''); ?>">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2 flex-wrap gap-y-1">
                                        <h3 class="text-lg font-bold text-gray-900">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </h3>

                                        <!-- Active / Inactive badge -->
                                        <?php if ($item['active']): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activa</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">Inactiva</span>
                                        <?php endif; ?>

                                        <!-- Due badge -->
                                        <?php if ($isDue): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Vencida
                                            </span>
                                        <?php elseif ($isSoon): ?>
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>Próxima
                                            </span>
                                        <?php endif; ?>

                                        <!-- Priority badge -->
                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                            echo match($item['priority']) {
                                                'urgente' => 'bg-red-100 text-red-800',
                                                'alta'    => 'bg-orange-100 text-orange-800',
                                                'media'   => 'bg-yellow-100 text-yellow-800',
                                                'baja'    => 'bg-green-100 text-green-800',
                                                default   => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?php echo ucfirst($item['priority']); ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($item['description'])): ?>
                                        <p class="text-gray-600 mb-3 text-sm">
                                            <?php echo htmlspecialchars(mb_substr($item['description'], 0, 180)); ?>
                                            <?php echo mb_strlen($item['description']) > 180 ? '...' : ''; ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500">
                                        <span><i class="fas fa-tag mr-1"></i> <?php echo ucfirst($item['category']); ?></span>
                                        <?php if (!empty($item['location'])): ?>
                                            <span><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($item['location']); ?></span>
                                        <?php endif; ?>
                                        <span>
                                            <i class="fas fa-redo mr-1"></i>
                                            <?php echo MaintenanceCatalog::intervalLabel($item['interval_value'], $item['interval_unit']); ?>
                                        </span>
                                        <?php if ($item['last_generated']): ?>
                                            <span><i class="fas fa-history mr-1"></i> Último: <?php echo date('d/m/Y', strtotime($item['last_generated'])); ?></span>
                                        <?php endif; ?>
                                        <?php if ($item['next_due']): ?>
                                            <span class="<?php echo $isDue ? 'text-red-600 font-semibold' : ''; ?>">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                Próximo: <?php echo date('d/m/Y', strtotime($item['next_due'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="ml-4 flex flex-col space-y-2 flex-shrink-0">
                                    <a href="<?php echo BASE_URL; ?>/maintenance/catalogEdit/<?php echo $item['id']; ?>"
                                       class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition text-center text-sm">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>
                                    <form method="POST"
                                          action="<?php echo BASE_URL; ?>/maintenance/catalogDelete/<?php echo $item['id']; ?>"
                                          onsubmit="return confirm('¿Eliminar esta incidencia fija del catálogo?');">
                                        <button type="submit"
                                                class="w-full px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition text-sm">
                                            <i class="fas fa-trash mr-1"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
