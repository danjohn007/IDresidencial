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
                    <form method="POST" action="<?php echo BASE_URL; ?>/financial/create" enctype="multipart/form-data">
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
                                       value="<?php echo isset($_GET['amount']) ? htmlspecialchars($_GET['amount']) : ''; ?>"
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
                                <select name="property_id" id="property_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Sin propiedad</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?php echo $property['id']; ?>" 
                                            <?php echo (isset($_GET['property_id']) && $_GET['property_id'] == $property['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($property['property_number']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Residente
                                </label>
                                <select name="resident_id" id="resident_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Sin residente</option>
                                    <?php foreach ($residents as $resident): ?>
                                        <option value="<?php echo $resident['id']; ?>" data-property-id="<?php echo $resident['property_id']; ?>">
                                            <?php echo htmlspecialchars($resident['name']); ?> 
                                            (<?php echo htmlspecialchars($resident['property_number']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Selector de cuota pendiente (visible solo para Cuota de Mantenimiento) -->
                            <div class="md:col-span-2" id="maintenance_fee_div" style="display:none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Cuota Pendiente <span class="text-red-500">*</span>
                                    <span class="text-xs text-gray-500 ml-1">(Seleccione la cuota a pagar)</span>
                                </label>
                                <select name="maintenance_fee_id" id="maintenance_fee_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">— Seleccione una propiedad primero —</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1" id="fee_amount_hint"></p>
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

                            <!-- Imprevisto (visible only for egreso) -->
                            <div class="md:col-span-2" id="unforeseen_div" style="display:none;">
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" name="is_unforeseen" value="1"
                                           class="w-4 h-4 text-orange-600 border-gray-300 rounded">
                                    <span class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-exclamation-triangle text-orange-500 mr-1"></i>
                                        Marcar como Imprevisto
                                    </span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1 ml-7">Activa esta opción si este egreso no estaba planeado</p>
                            </div>

                            <!-- Adjuntar Evidencia -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-paperclip mr-1"></i>Adjuntar Evidencia
                                </label>
                                <input type="file" name="evidence_file[]" accept=".pdf,.jpg,.jpeg,.png" multiple
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <p class="text-xs text-gray-500 mt-1">Opcional. Puedes seleccionar múltiples archivos. Formatos aceptados: PDF, JPG, PNG</p>
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

// Track if current movement type is "Cuota de Mantenimiento"
let isMaintenanceFeeType = false;

function checkMaintenanceFeeType() {
    const movTypeId = document.getElementById('movement_type_id').value;
    const transType = document.getElementById('transaction_type').value;
    const selectedType = movementTypes.find(t => t.id == movTypeId);
    isMaintenanceFeeType = selectedType && transType === 'ingreso' &&
        (selectedType.name.toLowerCase().includes('mantenimiento') || selectedType.name.toLowerCase().includes('cuota'));
    toggleMaintenanceFeeDiv();
}

function toggleMaintenanceFeeDiv() {
    const feeDiv = document.getElementById('maintenance_fee_div');
    const propertyId = document.getElementById('property_id').value;
    if (isMaintenanceFeeType && propertyId) {
        feeDiv.style.display = 'block';
        loadPendingFees(propertyId);
    } else {
        feeDiv.style.display = 'none';
        document.getElementById('maintenance_fee_id').innerHTML = '<option value="">— Seleccione una propiedad primero —</option>';
        document.getElementById('fee_amount_hint').textContent = '';
    }
}

function loadPendingFees(propertyId) {
    const feeSelect = document.getElementById('maintenance_fee_id');
    feeSelect.innerHTML = '<option value="">Cargando...</option>';
    fetch(`<?php echo BASE_URL; ?>/financial/getPendingFees/${propertyId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.fees.length > 0) {
                feeSelect.innerHTML = '<option value="">— Seleccione cuota pendiente —</option>';
                data.fees.forEach(fee => {
                    const opt = document.createElement('option');
                    opt.value = fee.id;
                    opt.textContent = `${fee.period_label} — ${fee.amount_label} (${fee.status_label})`;
                    opt.dataset.amount = fee.amount;
                    feeSelect.appendChild(opt);
                });
                // Make field required when visible
                feeSelect.required = true;
            } else {
                feeSelect.innerHTML = '<option value="">Sin cuotas pendientes para esta propiedad</option>';
                feeSelect.required = false;
            }
            updateFeeAmount();
        })
        .catch(() => {
            feeSelect.innerHTML = '<option value="">Error al cargar cuotas</option>';
            feeSelect.required = false;
        });
}

function updateFeeAmount() {
    const feeSelect = document.getElementById('maintenance_fee_id');
    const amountInput = document.querySelector('input[name="amount"]');
    const hint = document.getElementById('fee_amount_hint');
    const selectedOption = feeSelect.options[feeSelect.selectedIndex];
    if (selectedOption && selectedOption.dataset.amount) {
        const feeAmount = parseFloat(selectedOption.dataset.amount);
        amountInput.value = feeAmount.toFixed(2);
        amountInput.readOnly = true;
        hint.textContent = 'El monto es igual al total pendiente de la cuota seleccionada.';
        hint.className = 'text-xs text-blue-600 mt-1';
    } else {
        amountInput.readOnly = false;
        hint.textContent = '';
    }
}

document.getElementById('transaction_type').addEventListener('change', function() {
    const transactionType = this.value;
    const movementTypeSelect = document.getElementById('movement_type_id');
    const unforeseenDiv = document.getElementById('unforeseen_div');
    
    // Show/hide imprevisto checkbox
    if (unforeseenDiv) {
        unforeseenDiv.style.display = transactionType === 'egreso' ? 'block' : 'none';
        if (transactionType !== 'egreso') {
            unforeseenDiv.querySelector('input[type="checkbox"]').checked = false;
        }
    }
    
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
    checkMaintenanceFeeType();
});

document.getElementById('movement_type_id').addEventListener('change', checkMaintenanceFeeType);

// Auto-populate residents when property is selected
document.getElementById('property_id').addEventListener('change', function() {
    const propertyId = this.value;
    const residentSelect = document.getElementById('resident_id');
    
    // Save all options on first run
    if (!residentSelect.dataset.allOptions) {
        residentSelect.dataset.allOptions = residentSelect.innerHTML;
    }
    
    if (!propertyId) {
        // Reset to show all residents
        residentSelect.innerHTML = residentSelect.dataset.allOptions;
        residentSelect.value = '';
    } else {
        // Rebuild options showing only matching residents
        const allOptions = residentSelect.dataset.allOptions;
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = allOptions;
        
        residentSelect.innerHTML = '<option value="">Sin residente</option>';
        
        let firstMatch = null;
        Array.from(tempDiv.querySelectorAll('option')).forEach(option => {
            if (option.value === '') return;
            const residentPropertyId = option.getAttribute('data-property-id');
            if (residentPropertyId === propertyId) {
                residentSelect.appendChild(option.cloneNode(true));
                if (!firstMatch) firstMatch = option.value;
            }
        });
        
        if (firstMatch) {
            residentSelect.value = firstMatch;
        }
    }
    
    toggleMaintenanceFeeDiv();
});

document.getElementById('maintenance_fee_id').addEventListener('change', updateFeeAmount);

// Trigger on page load if property_id is in URL
window.addEventListener('DOMContentLoaded', function() {
    const propertySelect = document.getElementById('property_id');
    if (propertySelect.value) {
        propertySelect.dispatchEvent(new Event('change'));
    }
    const txTypeSelect = document.getElementById('transaction_type');
    if (txTypeSelect.value) {
        txTypeSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
