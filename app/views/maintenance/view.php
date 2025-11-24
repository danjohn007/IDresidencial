<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🔧 Detalles del Reporte</h1>
                    <p class="text-gray-600 mt-1">Información completa del reporte de mantenimiento</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/maintenance" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a Mantenimiento
                </a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900"><?php echo htmlspecialchars($report['title']); ?></h2>
                            <p class="text-sm text-gray-500 mt-1">Reporte #<?php echo $report['id']; ?></p>
                        </div>
                        <span class="px-3 py-1 text-sm rounded-full <?php 
                            echo match($report['status']) {
                                'pendiente' => 'bg-yellow-100 text-yellow-800',
                                'en_proceso' => 'bg-blue-100 text-blue-800',
                                'completado' => 'bg-green-100 text-green-800',
                                'cancelado' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        ?>">
                            <?php 
                            echo match($report['status']) {
                                'pendiente' => 'Pendiente',
                                'en_proceso' => 'En Proceso',
                                'completado' => 'Completado',
                                'cancelado' => 'Cancelado',
                                default => ucfirst($report['status'])
                            };
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Details -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <p class="text-gray-900"><?php echo ucfirst($report['category']); ?></p>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
                            <span class="px-3 py-1 text-sm rounded-full <?php 
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
                        </div>

                        <!-- Location -->
                        <?php if (!empty($report['location'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($report['location']); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Created Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Reporte</label>
                            <p class="text-gray-900"><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></p>
                        </div>

                        <!-- Estimated Completion -->
                        <?php if (!empty($report['estimated_completion'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Estimada de Finalización</label>
                            <p class="text-gray-900"><?php echo date('d/m/Y', strtotime($report['estimated_completion'])); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Completed Date -->
                        <?php if (!empty($report['completed_at'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Finalización</label>
                            <p class="text-gray-900"><?php echo date('d/m/Y H:i', strtotime($report['completed_at'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <p class="text-gray-900 whitespace-pre-wrap"><?php echo htmlspecialchars($report['description']); ?></p>
                    </div>

                    <!-- Photos -->
                    <?php if (!empty($report['photos'])): ?>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Fotografías</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php 
                            $photos = json_decode($report['photos'], true);
                            if (is_array($photos)):
                                foreach ($photos as $photo): ?>
                                <div class="relative group">
                                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($photo); ?>" 
                                         alt="Foto del reporte" 
                                         class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition">
                                </div>
                            <?php endforeach; 
                            endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
