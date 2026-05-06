<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🔑 Generar Pase de Acceso</h1>
                    <p class="text-gray-600 mt-1">Crea pases de acceso para visitantes o servicios</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" class="space-y-6">
                        <!-- Información del Visitante -->
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 mb-3">Información del Visitante</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre Completo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="visitor_name" required maxlength="255"
                                           value="<?php echo htmlspecialchars($_POST['visitor_name'] ?? ''); ?>"
                                           placeholder="Nombre del visitante"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Visita <span class="text-red-500">*</span>
                                    </label>
                                    <select name="visit_type" required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="personal" <?php echo (($_POST['visit_type'] ?? 'personal') === 'personal') ? 'selected' : ''; ?>>Personal</option>
                                        <option value="proveedor" <?php echo (($_POST['visit_type'] ?? '') === 'proveedor') ? 'selected' : ''; ?>>Proveedor</option>
                                        <option value="delivery" <?php echo (($_POST['visit_type'] ?? '') === 'delivery') ? 'selected' : ''; ?>>Delivery</option>
                                        <option value="otro" <?php echo (($_POST['visit_type'] ?? '') === 'otro') ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Placa del Vehículo
                                    </label>
                                    <input type="text" name="vehicle_plate" maxlength="20"
                                           value="<?php echo htmlspecialchars($_POST['vehicle_plate'] ?? ''); ?>"
                                           placeholder="ABC-123-D"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Identificación (INE)
                                    </label>
                                    <input type="text" name="visitor_id" maxlength="100"
                                           value="<?php echo htmlspecialchars($_POST['visitor_id'] ?? ''); ?>"
                                           placeholder="INE, Pasaporte, etc."
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Teléfono / WhatsApp
                                    </label>
                                    <input type="text" name="visitor_phone" maxlength="20"
                                           value="<?php echo htmlspecialchars($_POST['visitor_phone'] ?? ''); ?>"
                                           placeholder="10 dígitos sin espacios ni guiones"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Vigencia del Pase -->
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 mb-3">Vigencia del Pase</h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Pase <span class="text-red-500">*</span>
                            </label>
                            <select name="pass_type" id="pass_type" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    onchange="updateMaxUsesField()">
                                <option value="single_use">Uso Único</option>
                                <option value="temporary">Temporal (múltiples usos)</option>
                                <option value="permanent">Permanente</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Válido Desde <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" name="valid_from" 
                                       value="<?php echo date('Y-m-d\TH:i'); ?>" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Válido Hasta <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" name="valid_until" 
                                       value="<?php echo date('Y-m-d\TH:i', strtotime('+1 day')); ?>" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div id="max_uses_container">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Usos Máximos
                            </label>
                            <input type="number" name="max_uses" id="max_uses" value="1" min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1" id="max_uses_hint">Para pases de uso único, dejar en 1</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Notas Adicionales
                            </label>
                            <textarea name="notes" rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="Información adicional sobre la visita..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="<?php echo BASE_URL; ?>/residents/myAccesses" 
                               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-qrcode mr-2"></i> Generar Pase
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Información</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>• <strong>Uso Único:</strong> El pase expira después del primer uso</p>
                                <p>• <strong>Temporal:</strong> Válido para múltiples usos dentro del período especificado</p>
                                <p>• <strong>Permanente:</strong> Sin fecha de expiración (requiere aprobación de administración)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function updateMaxUsesField() {
    var passType = document.getElementById('pass_type').value;
    var container = document.getElementById('max_uses_container');
    var input = document.getElementById('max_uses');
    var hint = document.getElementById('max_uses_hint');

    if (passType === 'permanent') {
        container.style.display = 'none';
        input.removeAttribute('required');
    } else if (passType === 'single_use') {
        container.style.display = '';
        input.value = 1;
        input.setAttribute('readonly', 'readonly');
        hint.textContent = 'El pase de uso único siempre permite exactamente 1 uso.';
    } else {
        container.style.display = '';
        input.removeAttribute('readonly');
        input.value = input.value < 2 ? 2 : input.value;
        hint.textContent = 'Número de veces que se puede usar el pase dentro del período.';
    }
}
// Initialize on page load
document.addEventListener('DOMContentLoaded', updateMaxUsesField);
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
