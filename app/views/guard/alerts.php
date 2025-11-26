<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">🚨 Alertas de Seguridad</h1>
                        <p class="text-gray-600 mt-1">Monitoreo de placas no autorizadas y eventos inusuales</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/guard" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>

            <!-- Stats de Alertas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Placas No Autorizadas</p>
                            <p class="text-2xl font-bold text-red-600"><?php echo $stats['unauthorized_plates'] ?? 0; ?></p>
                        </div>
                        <i class="fas fa-car text-3xl text-red-400"></i>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Alertas Hoy</p>
                            <p class="text-2xl font-bold text-orange-600"><?php echo $stats['today_alerts'] ?? 0; ?></p>
                        </div>
                        <i class="fas fa-exclamation-triangle text-3xl text-orange-400"></i>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Visitas Expiradas</p>
                            <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['expired_visits'] ?? 0; ?></p>
                        </div>
                        <i class="fas fa-clock text-3xl text-yellow-400"></i>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Accesos Recientes</p>
                            <p class="text-2xl font-bold text-blue-600"><?php echo $stats['recent_access'] ?? 0; ?></p>
                        </div>
                        <i class="fas fa-door-open text-3xl text-blue-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="flex gap-4 flex-wrap">
                    <select id="filterType" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all" <?php echo ($filterType ?? 'unauthorized_plate') === 'all' ? 'selected' : ''; ?>>Todas las Alertas</option>
                        <option value="unauthorized_plate" <?php echo ($filterType ?? 'unauthorized_plate') === 'unauthorized_plate' ? 'selected' : ''; ?>>Placas No Autorizadas</option>
                        <option value="expired_visit">Visitas Expiradas</option>
                        <option value="suspicious">Actividad Sospechosa</option>
                    </select>
                    
                    <select id="filterTime" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="today" <?php echo ($filterTime ?? 'today') === 'today' ? 'selected' : ''; ?>>Hoy</option>
                        <option value="week" <?php echo ($filterTime ?? 'today') === 'week' ? 'selected' : ''; ?>>Última Semana</option>
                        <option value="month" <?php echo ($filterTime ?? 'today') === 'month' ? 'selected' : ''; ?>>Último Mes</option>
                        <option value="all" <?php echo ($filterTime ?? 'today') === 'all' ? 'selected' : ''; ?>>Todo</option>
                    </select>
                    
                    <button onclick="applyFilters()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i>Aplicar Filtros
                    </button>
                    
                    <button onclick="refreshAlerts()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-sync-alt mr-2"></i>Actualizar
                    </button>
                </div>
            </div>

            <!-- Placas No Autorizadas -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-4 border-b flex justify-between items-center">
                    <h2 class="text-lg font-bold">🚗 Placas No Autorizadas Detectadas</h2>
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                        <?php echo count($unauthorizedPlates ?? []); ?> Detecciones
                    </span>
                </div>
                <div class="p-4">
                    <?php if (empty($unauthorizedPlates)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-check-circle text-5xl text-green-500 mb-3"></i>
                            <p class="text-gray-600 font-semibold mb-2">No hay placas no autorizadas detectadas</p>
                            <p class="text-sm text-gray-500">
                                <?php 
                                echo match($filterTime ?? 'today') {
                                    'today' => 'No se han detectado placas no autorizadas hoy',
                                    'week' => 'No se han detectado placas no autorizadas esta semana',
                                    'month' => 'No se han detectado placas no autorizadas este mes',
                                    default => 'No hay placas no autorizadas en el sistema'
                                };
                                ?>
                            </p>
                            <button onclick="document.getElementById('filterTime').value='all'; applyFilters();" 
                                    class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                Ver Todas las Detecciones
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Debug Info (temporal) -->
                        <?php if (isset($_GET['debug'])): ?>
                            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-300 rounded">
                                <p class="font-bold mb-2">Debug Info:</p>
                                <pre class="text-xs overflow-x-auto"><?php print_r($unauthorizedPlates[0] ?? 'No hay placas'); ?></pre>
                            </div>
                        <?php endif; ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($unauthorizedPlates as $plate): ?>
                                <div class="border-2 border-red-300 rounded-lg p-4 bg-red-50">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <p class="text-2xl font-bold text-red-700"><?php echo htmlspecialchars($plate['plate_text']); ?></p>
                                            <p class="text-sm text-gray-600">
                                                <?php echo date('d/m/Y H:i', strtotime($plate['captured_at'])); ?>
                                            </p>
                                        </div>
                                        <span class="bg-red-600 text-white px-2 py-1 rounded text-xs font-semibold">
                                            NO AUTORIZADA
                                        </span>
                                    </div>
                                    
                                    <?php 
                                    // Construir URL de la imagen
                                    $imagePath = $plate['image_path'] ?? '';
                                    if (empty($imagePath) && !empty($plate['payload_json'])) {
                                        $payload = json_decode($plate['payload_json'], true);
                                        $imagePath = $payload['image_path'] ?? '';
                                    }
                                    
                                    // Si la imagen tiene ruta completa, extraer solo el nombre
                                    if (!empty($imagePath)) {
                                        $imageName = basename($imagePath);
                                        $imageUrl = "https://janetzy.shop/placas/" . $imageName;
                                    }
                                    ?>
                                    
                                    <?php if (!empty($imagePath)): ?>
                                        <div class="mb-3">
                                            <img src="<?php echo $imageUrl; ?>" 
                                                 alt="Placa <?php echo htmlspecialchars($plate['plate_text']); ?>" 
                                                 class="w-full h-32 object-cover rounded border-2 border-red-400 cursor-pointer hover:opacity-80"
                                                 onclick="viewImage('<?php echo $imageUrl; ?>')"
                                                 onerror="this.parentElement.innerHTML='<div class=\'bg-gray-200 h-32 flex items-center justify-center rounded\'><i class=\'fas fa-image text-gray-400 text-3xl\'></i></div>'">
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-3 bg-gray-200 h-32 flex items-center justify-center rounded border-2 border-gray-300">
                                            <div class="text-center text-gray-500">
                                                <i class="fas fa-camera-slash text-2xl mb-1"></i>
                                                <p class="text-xs">Sin imagen</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex gap-2">
                                        <button onclick="markAsResolved(<?php echo $plate['id']; ?>)" 
                                                class="flex-1 bg-green-600 text-white px-3 py-2 rounded text-sm hover:bg-green-700">
                                            <i class="fas fa-check mr-1"></i>Resolver
                                        </button>
                                        <button onclick="addNote(<?php echo $plate['id']; ?>)" 
                                                class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700">
                                            <i class="fas fa-comment mr-1"></i>Nota
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Otras Alertas -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-bold">⚠️ Otras Alertas y Eventos</h2>
                </div>
                <div class="p-4">
                    <?php if (empty($otherAlerts)): ?>
                        <p class="text-gray-500 text-center py-8">No hay otras alertas registradas</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($otherAlerts as $alert): ?>
                                <div class="flex items-start gap-4 p-4 border rounded-lg hover:bg-gray-50">
                                    <div class="flex-shrink-0 mt-1">
                                        <i class="fas fa-exclamation-circle text-2xl <?php 
                                            echo match($alert['severity'] ?? 'low') {
                                                'high' => 'text-red-600',
                                                'medium' => 'text-orange-600',
                                                default => 'text-yellow-600'
                                            };
                                        ?>"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($alert['title']); ?></p>
                                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($alert['description']); ?></p>
                                            </div>
                                            <span class="text-xs text-gray-500 whitespace-nowrap ml-4">
                                                <?php echo date('H:i', strtotime($alert['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal para ver imagen -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4" onclick="closeImageModal()">
    <div class="max-w-4xl max-h-full">
        <img id="modalImage" src="" alt="Imagen ampliada" class="max-w-full max-h-screen rounded-lg">
    </div>
</div>

<script>
function applyFilters() {
    const type = document.getElementById('filterType').value;
    const time = document.getElementById('filterTime').value;
    window.location.href = `<?php echo BASE_URL; ?>/guard/alerts?type=${type}&time=${time}`;
}

function refreshAlerts() {
    location.reload();
}

function viewImage(url) {
    document.getElementById('modalImage').src = url;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

function markAsResolved(plateId) {
    if (confirm('¿Marcar esta alerta como resuelta?')) {
        fetch(`<?php echo BASE_URL; ?>/guard/resolveAlert`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `plate_id=${plateId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Alerta marcada como resuelta');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        });
    }
}

function addNote(plateId) {
    const note = prompt('Ingresa una nota sobre esta detección:');
    if (note) {
        fetch(`<?php echo BASE_URL; ?>/guard/addNote`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `plate_id=${plateId}&note=${encodeURIComponent(note)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Nota agregada');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        });
    }
}

// Auto-refresh cada 30 segundos
setInterval(refreshAlerts, 30000);
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
