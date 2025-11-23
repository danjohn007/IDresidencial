<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">➕ Nueva Amenidad</h1>
                    <p class="text-gray-600 mt-1">Agregar una nueva amenidad al residencial</p>
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
                    <form method="POST" action="<?php echo BASE_URL; ?>/amenities/create">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Alberca Principal">
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Descripción <span class="text-red-500">*</span>
                                </label>
                                <textarea name="description" required rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Descripción de la amenidad"></textarea>
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo <span class="text-red-500">*</span>
                                </label>
                                <select name="amenity_type" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Seleccionar...</option>
                                    <option value="salon">Salón</option>
                                    <option value="alberca">Alberca</option>
                                    <option value="asadores">Asadores</option>
                                    <option value="cancha">Cancha Deportiva</option>
                                    <option value="gimnasio">Gimnasio</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <!-- Capacity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Capacidad <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="capacity" required min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Número de personas">
                            </div>

                            <!-- Hours Open -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Hora de Apertura <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="hours_open" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Hours Close -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Hora de Cierre <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="hours_close" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Requires Payment -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    ¿Requiere Pago?
                                </label>
                                <select name="requires_payment" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="document.getElementById('hourly_rate').disabled = this.value == '0'">
                                    <option value="0">No (Gratis)</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>

                            <!-- Hourly Rate -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tarifa por Hora
                                </label>
                                <input type="number" name="hourly_rate" id="hourly_rate" min="0" step="0.01" disabled
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="<?php echo BASE_URL; ?>/amenities/manage" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Amenidad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
