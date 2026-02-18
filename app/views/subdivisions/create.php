<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <a href="<?php echo BASE_URL; ?>/subdivisions" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 mb-4">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a Fraccionamientos
                </a>
                
                <h1 class="text-3xl font-bold text-gray-900">➕ Nuevo Fraccionamiento</h1>
                <p class="text-gray-600 mt-1">Complete la información del nuevo fraccionamiento</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <form method="POST" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Nombre del fraccionamiento">
                        </div>

                        <!-- Descripción -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                Descripción
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Descripción del fraccionamiento"></textarea>
                        </div>

                        <!-- Dirección -->
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                                Dirección
                            </label>
                            <input type="text" 
                                   id="address" 
                                   name="address" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Dirección completa">
                        </div>

                        <!-- Ciudad -->
                        <div>
                            <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                                Ciudad
                            </label>
                            <input type="text" 
                                   id="city" 
                                   name="city" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Ciudad">
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">
                                Estado
                            </label>
                            <input type="text" 
                                   id="state" 
                                   name="state" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Estado">
                        </div>

                        <!-- Código Postal -->
                        <div>
                            <label for="postal_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                Código Postal
                            </label>
                            <input type="text" 
                                   id="postal_code" 
                                   name="postal_code" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="CP">
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                Teléfono
                            </label>
                            <input type="text" 
                                   id="phone" 
                                   name="phone" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Teléfono de contacto">
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Email de contacto">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                        <a href="<?php echo BASE_URL; ?>/subdivisions" 
                           class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i> Crear Fraccionamiento
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
