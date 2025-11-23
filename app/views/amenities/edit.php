<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🏊 Editar Amenidad</h1>
                    <p class="text-gray-600 mt-1">Actualizar información de la amenidad</p>
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
                    <form method="POST" action="<?php echo BASE_URL; ?>/amenities/edit/<?php echo $amenity['id']; ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required value="<?php echo htmlspecialchars($amenity['name']); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Alberca Principal">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Descripción
                                </label>
                                <textarea name="description" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Descripción de la amenidad"><?php echo htmlspecialchars($amenity['description']); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo <span class="text-red-500">*</span>
                                </label>
                                <select name="amenity_type" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Seleccionar...</option>
                                    <option value="salon" <?php echo $amenity['amenity_type'] === 'salon' ? 'selected' : ''; ?>>Salón de Eventos</option>
                                    <option value="alberca" <?php echo $amenity['amenity_type'] === 'alberca' ? 'selected' : ''; ?>>Alberca</option>
                                    <option value="asadores" <?php echo $amenity['amenity_type'] === 'asadores' ? 'selected' : ''; ?>>Asadores</option>
                                    <option value="cancha" <?php echo $amenity['amenity_type'] === 'cancha' ? 'selected' : ''; ?>>Cancha Deportiva</option>
                                    <option value="gimnasio" <?php echo $amenity['amenity_type'] === 'gimnasio' ? 'selected' : ''; ?>>Gimnasio</option>
                                    <option value="otro" <?php echo $amenity['amenity_type'] === 'otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Capacidad (personas) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="capacity" required min="1" value="<?php echo $amenity['capacity']; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: 50">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Horario de Apertura <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="hours_open" required value="<?php echo date('H:i', strtotime($amenity['hours_open'])); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Horario de Cierre <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="hours_close" required value="<?php echo date('H:i', strtotime($amenity['hours_close'])); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    ¿Requiere Pago?
                                </label>
                                <select name="requires_payment" id="requires_payment"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="0" <?php echo $amenity['requires_payment'] == 0 ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?php echo $amenity['requires_payment'] == 1 ? 'selected' : ''; ?>>Sí</option>
                                </select>
                            </div>
                            
                            <div id="rate_field" style="display: <?php echo $amenity['requires_payment'] ? 'block' : 'none'; ?>;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tarifa por Hora
                                </label>
                                <input type="number" name="hourly_rate" step="0.01" min="0" value="<?php echo $amenity['hourly_rate']; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/amenities/manage" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Actualizar Amenidad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.getElementById('requires_payment').addEventListener('change', function() {
    document.getElementById('rate_field').style.display = this.value === '1' ? 'block' : 'none';
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
