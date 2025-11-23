<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">💰 Nuevo Movimiento Financiero</h1>
                    <p class="text-gray-600 mt-1">Registrar un nuevo ingreso o egreso</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/financial/create">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Transacción <span class="text-red-500">*</span>
                                </label>
                                <select name="transaction_type" id="transaction_type" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Seleccionar...</option>
                                    <option value="ingreso">Ingreso</option>
                                    <option value="egreso">Egreso</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Movimiento <span class="text-red-500">*</span>
                                </label>
                                <select name="movement_type_id" id="movement_type_id" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Seleccionar tipo de transacción primero</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Monto <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="amount" step="0.01" min="0" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Descripción <span class="text-red-500">*</span>
                                </label>
                                <textarea name="description" rows="3" required 
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Propiedad
                                </label>
                                <select name="property_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Sin propiedad</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?php echo $property['id']; ?>">
                                            <?php echo htmlspecialchars($property['property_number']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Residente
                                </label>
                                <select name="resident_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Sin residente</option>
                                    <?php foreach ($residents as $resident): ?>
                                        <option value="<?php echo $resident['id']; ?>">
                                            <?php echo htmlspecialchars($resident['name']); ?> 
                                            (<?php echo htmlspecialchars($resident['property_number']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Método de Pago
                                </label>
                                <select name="payment_method" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Seleccionar...</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Referencia de Pago
                                </label>
                                <input type="text" name="payment_reference" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="Número de referencia o folio">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Notas Adicionales
                                </label>
                                <textarea name="notes" rows="2" 
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <a href="<?php echo BASE_URL; ?>/financial" 
                               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Tipos de movimiento por categoría
const movementTypes = <?php echo json_encode($movementTypes); ?>;

document.getElementById('transaction_type').addEventListener('change', function() {
    const transactionType = this.value;
    const movementTypeSelect = document.getElementById('movement_type_id');
    
    // Limpiar opciones
    movementTypeSelect.innerHTML = '<option value="">Seleccionar...</option>';
    
    if (transactionType) {
        // Filtrar tipos de movimiento
        const filtered = movementTypes.filter(type => 
            type.category === transactionType || type.category === 'ambos'
        );
        
        filtered.forEach(type => {
            const option = document.createElement('option');
            option.value = type.id;
            option.textContent = type.name;
            movementTypeSelect.appendChild(option);
        });
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
