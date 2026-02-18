<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">📋 Planes de Membresía</h1>
                <p class="text-gray-600 mt-1">Catálogo de planes disponibles</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="mb-4 flex justify-between items-center">
                <a href="<?php echo BASE_URL; ?>/memberships" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a Membresías
                </a>
                
                <a href="<?php echo BASE_URL; ?>/memberships/createPlan" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Nuevo Plan
                </a>
            </div>

            <!-- Planes de Membresía -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($plans as $plan): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden <?php echo $plan['is_active'] ? '' : 'opacity-60'; ?>">
                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
                            <h3 class="text-2xl font-bold"><?php echo htmlspecialchars($plan['name']); ?></h3>
                            <p class="text-3xl font-bold mt-4">
                                $<?php echo number_format($plan['monthly_cost'], 2); ?>
                                <span class="text-sm font-normal">/mes</span>
                            </p>
                        </div>
                        
                        <div class="p-6">
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($plan['description']); ?></p>
                            
                            <h4 class="font-semibold text-gray-900 mb-2">Beneficios:</h4>
                            <ul class="space-y-2 mb-4">
                                <?php
                                $benefits = json_decode($plan['benefits'], true);
                                if (is_array($benefits)) {
                                    foreach ($benefits as $benefit) {
                                        echo '<li class="flex items-start">';
                                        echo '<i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>';
                                        echo '<span class="text-gray-700">' . htmlspecialchars($benefit) . '</span>';
                                        echo '</li>';
                                    }
                                } else {
                                    echo '<li class="text-gray-700">' . htmlspecialchars($plan['benefits']) . '</li>';
                                }
                                ?>
                            </ul>
                            
                            <div class="mt-6">
                                <?php if ($plan['is_active']): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                        <i class="fas fa-times-circle mr-1"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex space-x-2 mt-4">
                                <a href="<?php echo BASE_URL; ?>/memberships/editPlan/<?php echo $plan['id']; ?>" 
                                   class="flex-1 text-center px-3 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 text-sm">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </a>
                                <a href="<?php echo BASE_URL; ?>/memberships/togglePlanStatus/<?php echo $plan['id']; ?>" 
                                   class="px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-sm"
                                   onclick="return confirm('¿Está seguro de cambiar el estado?')">
                                    <i class="fas fa-toggle-on"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/memberships/deletePlan/<?php echo $plan['id']; ?>" 
                                   class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                   onclick="return confirm('¿Está seguro de eliminar este plan? Esta acción no se puede deshacer.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($plans)): ?>
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <i class="fas fa-folder-open text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-600">No hay planes de membresía configurados</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
