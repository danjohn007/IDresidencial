<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <a href="<?php echo BASE_URL; ?>/users" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 mb-4">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a Usuarios
                </a>
                
                <h1 class="text-3xl font-bold text-gray-900">👤 Detalles de Usuario</h1>
                <p class="text-gray-600 mt-1">Información completa del usuario</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- User Profile Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white text-center">
                            <?php if (!empty($user['photo'])): ?>
                                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($user['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($user['first_name']); ?>"
                                     class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-white">
                            <?php else: ?>
                                <div class="w-24 h-24 rounded-full bg-white text-blue-600 flex items-center justify-center text-3xl font-bold mx-auto mb-4">
                                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <h2 class="text-xl font-bold">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            </h2>
                            <p class="text-sm opacity-90">@<?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        
                        <div class="p-6">
                            <div class="mb-4">
                                <label class="text-sm font-semibold text-gray-600">Rol</label>
                                <p class="mt-1">
                                    <?php
                                    $roleColors = [
                                        'superadmin' => 'bg-purple-100 text-purple-800',
                                        'administrador' => 'bg-blue-100 text-blue-800',
                                        'guardia' => 'bg-green-100 text-green-800',
                                        'residente' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $roleNames = [
                                        'superadmin' => 'Super Administrador',
                                        'administrador' => 'Administrador',
                                        'guardia' => 'Guardia',
                                        'residente' => 'Residente'
                                    ];
                                    $colorClass = $roleColors[$user['role']] ?? 'bg-gray-100 text-gray-800';
                                    $roleName = $roleNames[$user['role']] ?? ucfirst($user['role']);
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?php echo $colorClass; ?>">
                                        <?php echo $roleName; ?>
                                    </span>
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="text-sm font-semibold text-gray-600">Estado</label>
                                <p class="mt-1">
                                    <?php if ($user['status'] === 'active'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Activo
                                        </span>
                                    <?php elseif ($user['status'] === 'inactive'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle mr-1"></i> Inactivo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                            <i class="fas fa-ban mr-1"></i> Bloqueado
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="text-sm font-semibold text-gray-600">Email</label>
                                <p class="text-gray-900 mt-1">
                                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </p>
                            </div>

                            <?php if (!empty($user['phone'])): ?>
                                <div class="mb-4">
                                    <label class="text-sm font-semibold text-gray-600">Teléfono</label>
                                    <p class="text-gray-900 mt-1">
                                        <i class="fas fa-phone text-gray-400 mr-2"></i>
                                        <?php echo htmlspecialchars($user['phone']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <label class="text-sm font-semibold text-gray-600">Fecha de Registro</label>
                                <p class="text-gray-900 mt-1">
                                    <i class="far fa-calendar text-gray-400 mr-2"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                                </p>
                            </div>

                            <?php if (!empty($user['last_login'])): ?>
                                <div class="mb-4">
                                    <label class="text-sm font-semibold text-gray-600">Último Acceso</label>
                                    <p class="text-gray-900 mt-1">
                                        <i class="fas fa-sign-in-alt text-gray-400 mr-2"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="flex space-x-2 mt-6">
                                <a href="<?php echo BASE_URL; ?>/users/edit/<?php echo $user['id']; ?>" 
                                   class="flex-1 text-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </a>
                                <a href="<?php echo BASE_URL; ?>/users/toggleStatus/<?php echo $user['id']; ?>" 
                                   class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                                   onclick="return confirm('¿Está seguro de cambiar el estado?')">
                                    <i class="fas fa-toggle-on"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-gray-100 px-6 py-4 border-b">
                            <h3 class="text-lg font-bold text-gray-900">
                                <i class="fas fa-history mr-2"></i>Actividad Reciente
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            <?php if (!empty($activity)): ?>
                                <div class="space-y-4">
                                    <?php foreach ($activity as $log): ?>
                                        <div class="flex items-start border-l-4 border-blue-500 pl-4 py-2">
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    <?php echo htmlspecialchars($log['action_type']); ?>
                                                </p>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    <?php echo htmlspecialchars($log['description']); ?>
                                                </p>
                                                <?php if (!empty($log['entity_type'])): ?>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        <span class="font-semibold">Entidad:</span> 
                                                        <?php echo htmlspecialchars($log['entity_type']); ?>
                                                        <?php if (!empty($log['entity_id'])): ?>
                                                            #<?php echo htmlspecialchars($log['entity_id']); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-right text-xs text-gray-500 ml-4">
                                                <i class="far fa-clock mr-1"></i>
                                                <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-3"></i>
                                    <p class="text-gray-600">No hay actividad reciente registrada</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
