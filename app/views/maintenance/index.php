<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Mantenimiento</h1>
                    <p class="text-gray-600 mt-1">Reportes e incidencias del residencial</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/maintenance/create" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i> Nuevo Reporte
                </a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?php echo $filters['status'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="en_proceso" <?php echo $filters['status'] === 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="completado" <?php echo $filters['status'] === 'completado' ? 'selected' : ''; ?>>Completado</option>
                    </select>
                    <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Todas las categorías</option>
                        <option value="alumbrado">Alumbrado</option>
                        <option value="jardineria">Jardinería</option>
                        <option value="plomeria">Plomería</option>
                        <option value="seguridad">Seguridad</option>
                        <option value="limpieza">Limpieza</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                </form>
            </div>

            <!-- Reports Grid -->
            <div class="grid grid-cols-1 gap-4">
                <?php foreach ($reports as $report): ?>
                    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($report['title']); ?></h3>
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo match($report['priority']) {
                                            'urgente' => 'bg-red-100 text-red-800',
                                            'alta' => 'bg-orange-100 text-orange-800',
                                            'media' => 'bg-yellow-100 text-yellow-800',
                                            'baja' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($report['priority']); ?>
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo match($report['status']) {
                                            'pendiente' => 'bg-yellow-100 text-yellow-800',
                                            'en_proceso' => 'bg-blue-100 text-blue-800',
                                            'completado' => 'bg-green-100 text-green-800',
                                            'cancelado' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 mb-3"><?php echo htmlspecialchars(substr($report['description'], 0, 150)); ?>...</p>
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span><i class="fas fa-tag mr-1"></i> <?php echo ucfirst($report['category']); ?></span>
                                    <span><i class="fas fa-user mr-1"></i> <?php echo $report['first_name'] . ' ' . $report['last_name']; ?></span>
                                    <span><i class="fas fa-home mr-1"></i> <?php echo $report['property_number']; ?></span>
                                    <span><i class="fas fa-calendar mr-1"></i> <?php echo date('d/m/Y', strtotime($report['created_at'])); ?></span>
                                </div>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/maintenance/view/<?php echo $report['id']; ?>" 
                               class="ml-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
