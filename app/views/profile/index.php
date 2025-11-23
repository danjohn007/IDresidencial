<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">👤 Mi Perfil</h1>
                    <p class="text-gray-600 mt-1">Gestiona tu información personal y configuración</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded alert-auto-hide">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Profile Info Card -->
                    <div class="md:col-span-1">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="text-center">
                                <div class="relative inline-block">
                                    <?php if (!empty($user['photo'])): ?>
                                        <img src="<?php echo PUBLIC_URL . '/uploads/profiles/' . htmlspecialchars($user['photo']); ?>" 
                                             alt="Foto de perfil" 
                                             class="w-24 h-24 rounded-full object-cover border-4 border-blue-500">
                                    <?php else: ?>
                                        <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-600 rounded-full">
                                            <span class="text-4xl font-bold text-white">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <button onclick="document.getElementById('photoInput').click()" 
                                            class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-2 hover:bg-blue-700 transition shadow-lg">
                                        <i class="fas fa-camera text-xs"></i>
                                    </button>
                                </div>
                                <form id="photoForm" method="POST" action="<?php echo BASE_URL; ?>/profile/updatePhoto" enctype="multipart/form-data" class="hidden">
                                    <input type="file" id="photoInput" name="photo" accept="image/*" onchange="document.getElementById('photoForm').submit()">
                                </form>
                                <h3 class="text-xl font-bold text-gray-900 mt-4"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                                <p class="text-gray-600 text-sm mt-1">@<?php echo htmlspecialchars($user['username']); ?></p>
                                <span class="inline-block px-3 py-1 mt-3 text-xs rounded-full <?php 
                                    echo match($user['role']) {
                                        'superadmin' => 'bg-purple-100 text-purple-800',
                                        'administrador' => 'bg-blue-100 text-blue-800',
                                        'guardia' => 'bg-green-100 text-green-800',
                                        'residente' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </div>
                            
                            <div class="mt-6 space-y-3 text-sm">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-envelope w-5 mr-3"></i>
                                    <span class="break-all"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone w-5 mr-3"></i>
                                    <span><?php echo htmlspecialchars($user['phone'] ?? 'No especificado'); ?></span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-calendar w-5 mr-3"></i>
                                    <span>Miembro desde <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Forms Column -->
                    <div class="md:col-span-2 space-y-6">
                        <!-- Contact Information Form -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">
                                <i class="fas fa-user-edit mr-2 text-blue-600"></i>
                                Información de Contacto
                            </h2>
                            <form method="POST" action="<?php echo BASE_URL; ?>/profile/updateContact">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Nombre <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="first_name" required 
                                               value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Apellido <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="last_name" required 
                                               value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" name="email" required 
                                               value="<?php echo htmlspecialchars($user['email']); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Teléfono <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel" name="phone" required 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                
                                <div class="flex justify-end mt-4">
                                    <button type="submit" 
                                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                        <i class="fas fa-save mr-2"></i>
                                        Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password Form -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">
                                <i class="fas fa-lock mr-2 text-red-600"></i>
                                Cambiar Contraseña
                            </h2>
                            <form method="POST" action="<?php echo BASE_URL; ?>/profile/changePassword">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Contraseña Actual <span class="text-red-500">*</span>
                                        </label>
                                        <input type="password" name="current_password" required 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Nueva Contraseña <span class="text-red-500">*</span>
                                        </label>
                                        <input type="password" name="new_password" required minlength="6"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <p class="text-xs text-gray-500 mt-1">Mínimo 6 caracteres</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Confirmar Nueva Contraseña <span class="text-red-500">*</span>
                                        </label>
                                        <input type="password" name="confirm_password" required minlength="6"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                                
                                <div class="flex justify-end mt-4">
                                    <button type="submit" 
                                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                        <i class="fas fa-key mr-2"></i>
                                        Cambiar Contraseña
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Auto-hide alerts after 5 seconds
document.querySelectorAll('.alert-auto-hide').forEach(function(alert) {
    setTimeout(function() {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 500);
    }, 5000);
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
