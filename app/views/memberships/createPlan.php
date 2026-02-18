<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <a href="<?php echo BASE_URL; ?>/memberships/plans" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 mb-4">
                    <i class="fas fa-arrow-left mr-2"></i> Volver a Planes
                </a>
                
                <h1 class="text-3xl font-bold text-gray-900">➕ Nuevo Plan de Membresía</h1>
                <p class="text-gray-600 mt-1">Complete la información del nuevo plan</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <form method="POST" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre del Plan -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nombre del Plan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Ej: Básico, Premium, VIP">
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
                                      placeholder="Descripción del plan"></textarea>
                        </div>

                        <!-- Costo Mensual -->
                        <div>
                            <label for="monthly_cost" class="block text-sm font-semibold text-gray-700 mb-2">
                                Costo Mensual <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" 
                                       id="monthly_cost" 
                                       name="monthly_cost" 
                                       step="0.01"
                                       min="0"
                                       required
                                       class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="is_active" class="block text-sm font-semibold text-gray-700 mb-2">
                                Estado
                            </label>
                            <select id="is_active" 
                                    name="is_active"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <!-- Beneficios -->
                        <div class="md:col-span-2">
                            <label for="benefits" class="block text-sm font-semibold text-gray-700 mb-2">
                                Beneficios <span class="text-gray-500">(uno por línea)</span>
                            </label>
                            <textarea id="benefits" 
                                      name="benefits" 
                                      rows="6"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                                      placeholder="Acceso a alberca&#10;Acceso a gimnasio&#10;2 reservaciones mensuales"></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle"></i> Escriba cada beneficio en una línea nueva
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                        <a href="<?php echo BASE_URL; ?>/memberships/plans" 
                           class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i> Crear Plan
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
