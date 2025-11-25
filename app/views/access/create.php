<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Generar Pase de Visita</h1>
                    <p class="text-gray-600 mt-1">Crea un pase de visita con código QR</p>
                </div>

                <!-- Error/Success Messages -->
                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/access/create">
                        <!-- Residente ID (hidden for residents) -->
                        <?php if ($resident): ?>
                            <input type="hidden" name="resident_id" value="<?php echo $resident['id']; ?>">
                            
                            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                                <p class="text-sm font-medium text-blue-800">Generando pase para:</p>
                                <p class="text-lg font-bold text-blue-900">
                                    <?php echo $resident['property_number']; ?> - <?php echo $resident['section']; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <!-- Selector de residente (para admin/guardia) -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Residente <span class="text-red-500">*</span>
                                </label>
                                <select name="resident_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Seleccionar residente...</option>
                                    <?php
                                    $residentModel = new Resident();
                                    $residents = $residentModel->getAll();
                                    foreach ($residents as $r):
                                    ?>
                                        <option value="<?php echo $r['id']; ?>">
                                            <?php echo $r['property_number']; ?> - <?php echo $r['first_name'] . ' ' . $r['last_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Información del Visitante -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Visitante</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre Completo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="visitor_name" required 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Nombre del visitante">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Identificación
                                    </label>
                                    <input type="text" name="visitor_id" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="INE, Pasaporte, etc.">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Teléfono/WhatsApp <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="visitor_phone" required
                                           maxlength="10" pattern="[0-9]{10}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="4421234567"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)">
                                    <p class="text-xs text-gray-500 mt-1">10 dígitos sin espacios ni guiones</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Placa del Vehículo
                                    </label>
                                    <input type="text" name="vehicle_plate" id="vehicle_plate"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="ABC-123-D">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Visita
                                    </label>
                                    <select name="visit_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="personal">Personal</option>
                                        <option value="proveedor">Proveedor</option>
                                        <option value="delivery">Delivery</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Placa del Residente (Oculto inicialmente) -->
                            <div id="plate-comparison-container" class="mt-4 hidden">
                                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg shadow-md p-4 border border-blue-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i class="fas fa-car text-blue-600 text-xl mr-3"></i>
                                            <div>
                                                <p class="text-xs font-medium text-gray-600">PLACA REGISTRADA</p>
                                                <p id="saved-plate" class="text-2xl font-bold text-gray-900">-</p>
                                            </div>
                                        </div>
                                        <button type="button" id="detect-plate-btn" 
                                                class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition flex items-center">
                                            <i class="fas fa-sync-alt mr-2"></i>
                                            Actualizar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vigencia del Pase -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Vigencia del Pase</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Válido Desde <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" name="valid_from" required 
                                           value="<?php echo date('Y-m-d\TH:i'); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Válido Hasta <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" name="valid_until" required 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime('+4 hours')); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Notas Adicionales
                            </label>
                            <textarea name="notes" rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Información adicional sobre la visita..."></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/access" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-qrcode mr-2"></i>
                                Generar Pase QR
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Box -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información Importante
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li>El código QR será único para cada visita</li>
                        <li>El visitante deberá presentar el código QR en la entrada</li>
                        <li>El pase expirará automáticamente después de la fecha establecida</li>
                        <li>El guardia podrá validar y registrar la entrada escaneando el QR</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const residentSelect = document.querySelector('select[name="resident_id"]');
    const plateInput = document.getElementById('vehicle_plate');
    const comparisonContainer = document.getElementById('plate-comparison-container');
    const detectPlateBtn = document.getElementById('detect-plate-btn');
    
    // Cuando se selecciona un residente
    if (residentSelect) {
        residentSelect.addEventListener('change', function() {
            const residentId = this.value;
            
            if (residentId) {
                // Obtener placas del residente y placas detectadas
                fetchPlateComparison(residentId);
            } else {
                // Ocultar comparación si no hay residente seleccionado
                comparisonContainer.classList.add('hidden');
            }
        });
    }
    
    // Botón para detectar placa nuevamente
    if (detectPlateBtn) {
        detectPlateBtn.addEventListener('click', function() {
            const residentId = residentSelect ? residentSelect.value : null;
            if (residentId) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Detectando...';
                this.disabled = true;
                
                fetchPlateComparison(residentId);
                
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Detectar Placa Nuevamente';
                    this.disabled = false;
                }, 1500);
            }
        });
    }
    
    function fetchPlateComparison(residentId) {
        console.log('Buscando placas para residente ID:', residentId);
        
        fetch('<?php echo BASE_URL; ?>/api/getPlateComparison/' + residentId)
            .then(response => {
                console.log('Respuesta recibida:', response);
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    // Mostrar el contenedor solo si hay placa registrada
                    if (data.saved_plate) {
                        comparisonContainer.classList.remove('hidden');
                        
                        // Actualizar placa registrada
                        const savedPlateEl = document.getElementById('saved-plate');
                        savedPlateEl.textContent = data.saved_plate;
                        
                        // Auto-llenar el campo de placa
                        if (plateInput) {
                            plateInput.value = data.saved_plate;
                        }
                    } else {
                        comparisonContainer.classList.add('hidden');
                    }
                } else {
                    console.error('Error en respuesta:', data.error || 'Error desconocido');
                }
            })
            .catch(error => {
                console.error('Error en fetch:', error);
            });
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
