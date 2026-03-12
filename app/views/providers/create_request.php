<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-plus-circle mr-2 text-blue-600"></i>Nueva Solicitud de Servicio</h1>
                </div>
                <a href="<?php echo BASE_URL; ?>/providers/requests" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>

            <?php if (!empty($error)): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <i class="fas fa-exclamation-circle mr-1"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="<?php echo BASE_URL; ?>/providers/createRequest" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-red-500">*</span></label>
                            <input type="text" name="title" required
                                   value="<?php echo htmlspecialchars($request['title'] ?? ''); ?>"
                                   placeholder="Descripción breve del servicio requerido"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                            <select name="provider_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Sin asignar</option>
                                <?php foreach ($providers as $prov): ?>
                                <option value="<?php echo $prov['id']; ?>" <?php echo ($request['provider_id'] ?? '') == $prov['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prov['company_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <input type="text" name="category"
                                   list="categorySuggestions"
                                   value="<?php echo htmlspecialchars($request['category'] ?? ''); ?>"
                                   placeholder="Ej: Plomería, Electricidad..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <datalist id="categorySuggestions">
                                <option value="Plomería">
                                <option value="Electricidad">
                                <option value="Jardinería">
                                <option value="Limpieza">
                                <option value="Pintura">
                                <option value="Carpintería">
                                <option value="Albañilería">
                                <option value="General">
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Área / Ubicación</label>
                            <input type="text" name="area"
                                   value="<?php echo htmlspecialchars($request['area'] ?? ''); ?>"
                                   placeholder="Ej: Área común, Entrada principal..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Propiedad (si aplica)</label>
                            <select name="property_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Área común</option>
                                <?php foreach ($properties as $prop): ?>
                                <option value="<?php echo $prop['id']; ?>" <?php echo ($request['property_id'] ?? '') == $prop['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prop['property_number']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
                            <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="low" <?php echo ($request['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Baja</option>
                                <option value="medium" <?php echo ($request['priority'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>Media</option>
                                <option value="high" <?php echo ($request['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>Alta</option>
                                <option value="urgent" <?php echo ($request['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Solicitada</label>
                            <input type="date" name="requested_date"
                                   value="<?php echo htmlspecialchars($request['requested_date'] ?? date('Y-m-d')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Programada</label>
                            <input type="date" name="scheduled_date"
                                   value="<?php echo htmlspecialchars($request['scheduled_date'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Costo Estimado</label>
                            <input type="number" name="estimated_cost" step="0.01" min="0"
                                   value="<?php echo htmlspecialchars($request['estimated_cost'] ?? ''); ?>"
                                   placeholder="0.00"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="description" rows="3"
                                  placeholder="Describe el problema o servicio requerido..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  ><?php echo htmlspecialchars($request['description'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas Adicionales</label>
                        <textarea name="notes" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  ><?php echo htmlspecialchars($request['notes'] ?? ''); ?></textarea>
                    </div>
                    <div class="flex space-x-3">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            <i class="fas fa-save mr-2"></i>Guardar Solicitud
                        </button>
                        <a href="<?php echo BASE_URL; ?>/providers/requests" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
