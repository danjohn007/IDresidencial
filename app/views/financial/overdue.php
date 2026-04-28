<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">💳 Cartera Vencida</h1>
                    <p class="text-gray-600 mt-1">Cuotas pendientes y vencidas</p>
                </div>
                <div class="flex items-center space-x-2 flex-wrap gap-2">
                    <!-- Export buttons -->
                    <button type="button" onclick="openExportModal('print')"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 shadow text-sm">
                        <i class="fas fa-print mr-2"></i> Imprimir
                    </button>
                    <button type="button" onclick="openExportModal('pdf')"
                            class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 shadow text-sm">
                        <i class="fas fa-file-pdf mr-2"></i> PDF
                    </button>
                    <button type="button" onclick="openExportModal('excel')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow text-sm">
                        <i class="fas fa-file-excel mr-2"></i> Excel
                    </button>
                    <form method="POST" action="<?php echo BASE_URL; ?>/financial/applyPenalties"
                          onsubmit="return confirm('¿Aplicar penalizaciones a todas las cuotas vencidas según las reglas configuradas? Esta acción no se puede deshacer.');">
                        <button type="submit"
                                class="inline-flex items-center px-5 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 shadow">
                            <i class="fas fa-gavel mr-2"></i> Aplicar Penalizaciones
                        </button>
                    </form>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
            <?php endif; ?>

            <!-- Stats Card -->
            <div class="bg-white rounded-lg shadow p-6 mb-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Cartera Vencida</p>
                        <p class="text-3xl font-bold text-red-600">$<?php echo number_format($overallStats['total'], 2); ?></p>
                        <p class="text-sm text-gray-500"><?php echo $overallStats['count']; ?> cuotas pendientes/vencidas</p>
                    </div>
                    <div class="p-4 bg-red-100 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/financial/overdueAccounts" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Propiedad o residente..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Días Vencido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No se encontraron registros</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($records as $index => $record): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($record['property_number']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span id="name-text-<?php echo $index; ?>" class="hidden"><?php echo htmlspecialchars($record['resident_name'] ?? 'Sin asignar'); ?></span>
                                <button type="button"
                                        id="name-btn-<?php echo $index; ?>"
                                        onclick="openPasswordModal(<?php echo $index; ?>, <?php echo htmlspecialchars(json_encode($record['resident_name'] ?? 'Sin asignar'), ENT_QUOTES); ?>)"
                                        class="inline-flex items-center px-2 py-1 text-xs bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition"
                                        title="Ver nombre del residente">
                                    <i class="fas fa-eye mr-1"></i> Ver
                                </button>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($record['resident_phone'] ?? '-'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($record['period']); ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">$<?php echo number_format($record['amount'], 2); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($record['due_date'])); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="<?php echo $record['days_overdue'] > 0 ? 'text-red-600 font-semibold' : 'text-yellow-600'; ?>">
                                    <?php echo max(0, $record['days_overdue']); ?> días
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $record['status'] === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $record['status'] === 'overdue' ? 'Vencido' : 'Pendiente'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Password Modal (for resident name reveal and export actions) -->
            <div id="passwordModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closePasswordModal()"></div>
                <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Verificación de Seguridad</h3>
                    <p class="text-sm text-gray-600 mb-4">Ingresa la contraseña de un Superadmin para continuar.</p>
                    <div id="passwordModalError" class="mb-3 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded hidden"></div>
                    <input type="password" id="superadminPassword" placeholder="Contraseña Superadmin"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           onkeydown="if(event.key==='Enter') submitPassword()">
                    <div class="flex space-x-3">
                        <button type="button" onclick="submitPassword()"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                            <i class="fas fa-unlock mr-1"></i> Verificar
                        </button>
                        <button type="button" onclick="closePasswordModal()"
                                class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center mt-6 space-x-2">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                   class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
var _currentModalIndex  = null;
var _currentModalName   = null;
var _currentExportAction = null; // 'print' | 'pdf' | 'excel' | null

// Filter values embedded safely as JSON
var _filterSearch   = <?php echo json_encode($search, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var _filterDateFrom = <?php echo json_encode($date_from, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var _filterDateTo   = <?php echo json_encode($date_to, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// Build current filter query string
function currentFilters() {
    var params = new URLSearchParams();
    params.set('search',    _filterSearch);
    params.set('date_from', _filterDateFrom);
    params.set('date_to',   _filterDateTo);
    return params.toString();
}

function openPasswordModal(index, name) {
    _currentModalIndex   = index;
    _currentModalName    = name;
    _currentExportAction = null;
    _resetModal();
    document.getElementById('passwordModal').classList.remove('hidden');
    setTimeout(function(){ document.getElementById('superadminPassword').focus(); }, 100);
}

function openExportModal(action) {
    _currentModalIndex   = null;
    _currentModalName    = null;
    _currentExportAction = action;
    _resetModal();
    document.getElementById('passwordModal').classList.remove('hidden');
    setTimeout(function(){ document.getElementById('superadminPassword').focus(); }, 100);
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
    _currentModalIndex   = null;
    _currentModalName    = null;
    _currentExportAction = null;
}

function _resetModal() {
    document.getElementById('superadminPassword').value = '';
    document.getElementById('passwordModalError').classList.add('hidden');
    document.getElementById('passwordModalError').textContent = '';
}

function submitPassword() {
    var password = document.getElementById('superadminPassword').value;
    if (!password) {
        showModalError('Por favor ingresa la contraseña.');
        return;
    }

    var formData = new FormData();
    formData.append('password', password);

    fetch('<?php echo BASE_URL; ?>/financial/verifyResidentPassword', {
        method: 'POST',
        body: formData
    })
    .then(function(res){ return res.json(); })
    .then(function(data) {
        if (data.success) {
            if (_currentExportAction === 'excel') {
                window.location.href = '<?php echo BASE_URL; ?>/financial/exportOverdueCSV?' + currentFilters();
                closePasswordModal();
            } else if (_currentExportAction === 'pdf' || _currentExportAction === 'print') {
                var exportUrl = '<?php echo BASE_URL; ?>/financial/exportOverduePDF?' + currentFilters();
                var win = window.open(exportUrl, '_blank');
                closePasswordModal();
                if (_currentExportAction === 'print' && win) {
                    win.onload = function() {
                        try { win.print(); } catch(e) {}
                    };
                }
            } else if (_currentModalIndex !== null) {
                var nameText = document.getElementById('name-text-' + _currentModalIndex);
                var nameBtn  = document.getElementById('name-btn-'  + _currentModalIndex);
                if (nameText) nameText.classList.remove('hidden');
                if (nameBtn)  nameBtn.classList.add('hidden');
                closePasswordModal();
            }
        } else {
            showModalError('Contraseña incorrecta. Solo se aceptan contraseñas de Superadmin.');
        }
    })
    .catch(function() {
        showModalError('Error de conexión. Intenta de nuevo.');
    });
}

function showModalError(msg) {
    var el = document.getElementById('passwordModalError');
    el.textContent = msg;
    el.classList.remove('hidden');
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
