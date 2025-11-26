<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">✍️ Registro Manual de Acceso</h1>
                        <p class="text-gray-600 mt-1">Registra entradas y salidas sin código QR</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/guard" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <form id="manualAccessForm" class="space-y-6">
                        <!-- Tipo de Acceso -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Acceso *</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-blue-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="access_type" value="entry" required class="mr-3">
                                    <i class="fas fa-sign-in-alt text-green-600 text-2xl mr-3"></i>
                                    <span class="font-semibold">Entrada</span>
                                </label>
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-orange-50 has-[:checked]:border-orange-600 has-[:checked]:bg-orange-50">
                                    <input type="radio" name="access_type" value="exit" class="mr-3">
                                    <i class="fas fa-sign-out-alt text-orange-600 text-2xl mr-3"></i>
                                    <span class="font-semibold">Salida</span>
                                </label>
                            </div>
                        </div>

                        <!-- Tipo de Registro -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Registro *</label>
                            <select name="log_type" id="logType" required 
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecciona una opción</option>
                                <option value="visit">Visita Programada</option>
                                <option value="resident">Residente</option>
                                <option value="vehicle">Vehículo Registrado</option>
                                <option value="emergency">Visitante sin Pase (Emergencia)</option>
                            </select>
                        </div>

                        <!-- Búsqueda (para visita, residente o vehículo) -->
                        <div id="searchSection" class="hidden" style="position:relative;">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                            <input type="text" id="searchInput" placeholder="Escribe para buscar..."
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                   oninput="searchRecordLive()" autocomplete="off">
                            
                            <!-- Contenedor de resultados -->
                            <div id="searchResults" style="position:relative;"></div>
                            
                            <!-- Campo oculto para el ID seleccionado -->
                            <input type="hidden" id="selected_id" name="selected_id" value="">
                            
                            <!-- Mostrar selección actual -->
                            <div id="selectedRecord" style="margin-top:12px; padding:12px; background:#f0f9ff; border:2px solid #0ea5e9; border-radius:8px; display:none;">
                                <p style="font-weight:600; color:#0c4a6e; margin:0; font-size:1rem;" id="selectedName"></p>
                                <p style="font-size:0.875rem; color:#075985; margin:4px 0 0 0;" id="selectedProperty"></p>
                                <button type="button" onclick="clearSelection()" style="margin-top:8px; padding:6px 12px; background:#dc2626; color:white; border:none; border-radius:4px; cursor:pointer; font-size:0.875rem; font-weight:500;">
                                    ✕ Limpiar selección
                                </button>
                            </div>
                        </div>

                        <!-- Datos Manuales (para emergencia) -->
                        <div id="emergencySection" class="hidden space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo *</label>
                                <input type="text" name="visitor_name" 
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                    <input type="tel" name="phone" maxlength="10" pattern="[0-9]{10}"
                                           placeholder="10 dígitos"
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Propiedad Destino *</label>
                                    <input type="text" name="property_number" 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Motivo de la Visita</label>
                                <textarea name="notes" rows="3" 
                                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="reference_id" id="referenceId">
                        <input type="hidden" name="property_id" id="propertyId">

                        <!-- Notas Adicionales -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                            <textarea name="additional_notes" rows="2" 
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="Información adicional sobre este acceso..."></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-4">
                            <button type="submit" 
                                    class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold">
                                <i class="fas fa-check mr-2"></i>Registrar Acceso
                            </button>
                            <button type="button" onclick="document.getElementById('manualAccessForm').reset(); location.reload();" 
                                    class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.getElementById('logType').addEventListener('change', function() {
    const searchSection = document.getElementById('searchSection');
    const emergencySection = document.getElementById('emergencySection');
    
    if (this.value === 'emergency') {
        searchSection.classList.add('hidden');
        emergencySection.classList.remove('hidden');
    } else if (this.value) {
        searchSection.classList.remove('hidden');
        emergencySection.classList.add('hidden');
    } else {
        searchSection.classList.add('hidden');
        emergencySection.classList.add('hidden');
    }
});
let searchTimeout;

function searchRecordLive() {
    clearTimeout(searchTimeout);
    
    const logType = document.getElementById('logType').value;
    const query = document.getElementById('searchInput').value.trim();
    const resultsDiv = document.getElementById('searchResults');
    
    console.log('Buscando:', {logType, query});
    
    if (!query || query.length < 2) {
        resultsDiv.innerHTML = '';
        return;
    }
    
    resultsDiv.innerHTML = '<div class="text-center py-2"><i class="fas fa-spinner fa-spin text-blue-600"></i></div>';
    
    searchTimeout = setTimeout(() => {
        const url = `<?php echo BASE_URL; ?>/guard/search?type=${logType}&query=${encodeURIComponent(query)}`;
        console.log('Fetching:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Data received:', data);
                
                // FORZAR que se vea algo SIEMPRE
                resultsDiv.style.display = 'block';
                resultsDiv.style.visibility = 'visible';
                resultsDiv.style.opacity = '1';
                
                if (data.success && data.results && data.results.length > 0) {
                    console.log(`✅ Encontrados ${data.results.length} resultados`);
                    
                    // Lista simple debajo del campo
                    let html = '<div style="margin-top:8px; border:2px solid #3b82f6; border-radius:8px; background:white; box-shadow:0 4px 12px rgba(0,0,0,0.15); overflow:hidden;">';
                    html += `<div style="background:#3b82f6; color:white; padding:12px; font-weight:700; font-size:14px;">📋 ${data.results.length} resultado(s) encontrado(s) - Click para seleccionar:</div>`;
                    
                    data.results.forEach((result, index) => {
                        const name = result.name || 'Sin nombre';
                        const details = result.details || 'Sin detalles';
                        const property = result.property || '';
                        const propertyId = result.property_id || '';
                        const safeId = result.id;
                        
                        console.log(`Resultado ${index + 1}:`, {name, property, propertyId});
                        
                        html += `
                            <div style="padding:16px; cursor:pointer; border-bottom:1px solid #e5e7eb; background:white;" 
                                 onmouseover="this.style.backgroundColor='#dbeafe'" 
                                 onmouseout="this.style.backgroundColor='white'"
                                 onclick='selectRecord(${safeId}, "${escapeJs(name)}", "${escapeJs(property)}", ${propertyId}); document.getElementById("searchResults").innerHTML="";'>
                                <div style="font-weight:700; color:#1e293b; margin-bottom:4px; font-size:16px;">👤 ${escapeHtml(name)}</div>
                                <div style="font-size:14px; color:#64748b;">📍 ${escapeHtml(details)}</div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    
                    console.log('📝 Insertando HTML en resultsDiv');
                    resultsDiv.innerHTML = html;
                    console.log('✅ HTML insertado');
                } else {
                    const msg = data.message || 'No se encontraron resultados';
                    resultsDiv.innerHTML = `<p class="text-gray-500 text-center py-3 text-sm">${msg}</p>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultsDiv.innerHTML = `<p class="text-red-600 text-sm">Error: ${error.message}</p>`;
            });
    }, 300);
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function escapeJs(text) {
    if (!text) return '';
    return String(text).replace(/\\/g, '\\\\').replace(/"/g, '\\"').replace(/'/g, "\\'").replace(/\n/g, '\\n');
}

function selectRecord(id, name, property, propertyId) {
    // Guardar en campos ocultos
    document.getElementById('referenceId').value = id;
    document.getElementById('propertyId').value = propertyId; // Ahora guarda el ID, no el número
    document.getElementById('selected_id').value = id;
    
    // Mostrar la selección visual
    document.getElementById('selectedName').textContent = name;
    document.getElementById('selectedProperty').textContent = property ? `Propiedad: ${property}` : '';
    document.getElementById('selectedRecord').style.display = 'block';
    
    // Limpiar búsqueda y resultados
    document.getElementById('searchInput').value = '';
    document.getElementById('searchResults').innerHTML = '';
    
    console.log('✅ Registro seleccionado:', {id, name, property, propertyId});
}

function clearSelection() {
    document.getElementById('referenceId').value = '';
    document.getElementById('propertyId').value = '';
    document.getElementById('selected_id').value = '';
    document.getElementById('selectedRecord').style.display = 'none';
    document.getElementById('selectedName').textContent = '';
    document.getElementById('selectedProperty').textContent = '';
    document.getElementById('searchInput').value = '';
    console.log('🔄 Selección limpiada');
}

document.getElementById('manualAccessForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validar que se haya seleccionado un registro (excepto para emergencias)
    const logType = document.getElementById('logType').value;
    const accessType = document.querySelector('input[name="access_type"]:checked')?.value;
    const referenceId = document.getElementById('referenceId').value;
    
    console.log('📋 Datos del formulario:', {
        logType,
        accessType,
        referenceId,
        propertyId: document.getElementById('propertyId').value
    });
    
    if (!accessType) {
        alert('❌ Error: Debes seleccionar Entrada o Salida');
        return;
    }
    
    if (!logType) {
        alert('❌ Error: Debes seleccionar el tipo de registro');
        return;
    }
    
    if (logType !== 'emergency' && !referenceId) {
        alert('❌ Error: Debes seleccionar un registro de la lista');
        return;
    }
    
    const formData = new FormData(this);
    
    // Debug: Mostrar todos los datos del formulario
    console.log('📤 Enviando datos:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    const url = '<?php echo BASE_URL; ?>/guard/registerManualAccess';
    console.log('🌐 URL:', url);
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📥 Response status:', response.status);
        console.log('📥 Response headers:', response.headers.get('content-type'));
        return response.text();
    })
    .then(text => {
        console.log('📄 Respuesta completa del servidor:');
        console.log(text);
        
        try {
            const data = JSON.parse(text);
            console.log('✅ JSON parseado:', data);
            
            if (data.success) {
                alert('✅ Acceso registrado exitosamente');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        } catch (e) {
            console.error('❌ Error al parsear JSON:', e);
            console.error('Primeros 500 caracteres de la respuesta:', text.substring(0, 500));
            alert('❌ Error: El servidor devolvió HTML en lugar de JSON. Revisa la consola para más detalles.');
        }
    })
    .catch(error => {
        console.error('❌ Error completo:', error);
        alert('❌ Error de conexión: ' + error.message);
    });
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
