<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">✏️ Editar Usuario</h1>
                    <p class="text-gray-600 mt-1">Modificar información del usuario</p>
                </div>

                <!-- Error Messages -->
                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/users/edit/<?php echo $user['id']; ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="first_name" required value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Apellido <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="last_name" required value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Usuario
                                </label>
                                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                <p class="text-xs text-gray-500 mt-1">El nombre de usuario no se puede cambiar</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Teléfono
                                </label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nueva Contraseña
                                </label>
                                <input type="password" name="password" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Dejar en blanco para no cambiar">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Rol <span class="text-red-500">*</span>
                                </label>
                                <select name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="residente" <?php echo $user['role'] === 'residente' ? 'selected' : ''; ?>>Residente</option>
                                    <option value="guardia" <?php echo $user['role'] === 'guardia' ? 'selected' : ''; ?>>Guardia</option>
                                    <option value="administrador" <?php echo $user['role'] === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                    <option value="superadmin" <?php echo $user['role'] === 'superadmin' ? 'selected' : ''; ?>>Super Admin</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado
                                </label>
                                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" name="is_vigilance_committee" value="1"
                                           <?php echo !empty($user['is_vigilance_committee']) ? 'checked' : ''; ?>
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                    <span class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-shield-alt text-blue-500 mr-1"></i>
                                        Comité de Vigilancia
                                    </span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1 ml-7">Marcar si este usuario pertenece al Comité de Vigilancia</p>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="<?php echo BASE_URL; ?>/users" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
