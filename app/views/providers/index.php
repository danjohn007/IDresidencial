<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-hard-hat mr-2 text-yellow-600"></i>Proveedores</h1>
                    <p class="text-gray-600 mt-1">Gestión de proveedores autorizados de mantenimiento</p>
                </div>
                <div class="flex space-x-2">
                    <a href="<?php echo BASE_URL; ?>/providers/requests" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-clipboard-list mr-2"></i> Solicitudes de Servicio
                    </a>
                    <a href="<?php echo BASE_URL; ?>/providers/create" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                        <i class="fas fa-plus mr-2"></i> Nuevo Proveedor
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['new_provider_credentials'])): ?>
            <?php $creds = $_SESSION['new_provider_credentials']; unset($_SESSION['new_provider_credentials']); ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showNewProviderModal(
                        <?php echo json_encode($creds['company']); ?>,
                        <?php echo json_encode($creds['username']); ?>,
                        <?php echo json_encode($creds['email']); ?>,
                        <?php echo json_encode($creds['password']); ?>
                    );
                });
            </script>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-600 mb-1">Total Proveedores</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['total']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600 mb-1">Activos</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['active']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-400">
                    <p class="text-sm text-gray-600 mb-1">Inactivos</p>
                    <p class="text-3xl font-bold text-gray-600"><?php echo $stats['inactive']; ?></p>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="<?php echo BASE_URL; ?>/providers" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-search mr-1"></i>Buscar</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>"
                               placeholder="Empresa, contacto, teléfono o email..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                            <option value="">Todas</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filters['category'] === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="<?php echo BASE_URL; ?>/providers" class="px-3 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solicitudes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($providers)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-hard-hat text-4xl mb-2 text-gray-300"></i>
                                <p>No se encontraron proveedores</p>
                                <a href="<?php echo BASE_URL; ?>/providers/create" class="mt-2 inline-block text-yellow-600 hover:underline">
                                    Agregar el primer proveedor
                                </a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($providers as $provider): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($provider['company_name']); ?></div>
                                <?php if ($provider['email']): ?>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($provider['email']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php echo htmlspecialchars($provider['contact_name'] ?? '—'); ?>
                                <?php if ($provider['phone']): ?>
                                <br><span class="text-gray-500"><?php echo htmlspecialchars($provider['phone']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ($provider['category']): ?>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                                    <?php echo htmlspecialchars($provider['category']); ?>
                                </span>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="font-medium"><?php echo $provider['total_requests']; ?></span> total
                                <?php if ($provider['open_requests'] > 0): ?>
                                <span class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full"><?php echo $provider['open_requests']; ?> abiertas</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $provider['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                    <?php echo $provider['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-3">
                                    <a href="<?php echo BASE_URL; ?>/providers/edit/<?php echo $provider['id']; ?>"
                                       class="text-green-600 hover:text-green-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Toggle Status Checkbox -->
                                    <label class="relative inline-flex items-center cursor-pointer" title="<?php echo $provider['status'] === 'active' ? 'Desactivar' : 'Activar'; ?>">
                                        <input type="checkbox" 
                                               class="sr-only peer provider-status-toggle" 
                                               data-provider-id="<?php echo $provider['id']; ?>"
                                               data-provider-name="<?php echo htmlspecialchars($provider['company_name']); ?>"
                                               <?php echo $provider['status'] === 'active' ? 'checked' : ''; ?>>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                                    </label>
                                    <?php if ($provider['status'] === 'active'): ?>
                                    <button onclick="confirmDeactivate(<?php echo $provider['id']; ?>, '<?php echo addslashes($provider['company_name']); ?>')"
                                            class="text-red-600 hover:text-red-900" title="Eliminar permanentemente">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script>
// Toggle provider status
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.provider-status-toggle');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const providerId = this.getAttribute('data-provider-id');
            const providerName = this.getAttribute('data-provider-name');
            const isChecked = this.checked;
            const action = isChecked ? 'activar' : 'desactivar';
            
            if (confirm('¿Está seguro de ' + action + ' al proveedor "' + providerName + '"?')) {
                // Make AJAX call
                fetch('<?php echo BASE_URL; ?>/providers/toggleStatus/' + providerId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to update stats
                        location.reload();
                    } else {
                        alert('Error al cambiar el estado del proveedor');
                        this.checked = !isChecked; // Revert checkbox
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cambiar el estado del proveedor');
                    this.checked = !isChecked; // Revert checkbox
                });
            } else {
                // User cancelled, revert checkbox
                this.checked = !isChecked;
            }
        });
    });
});

function confirmDeactivate(id, name) {
    if (confirm('¿ELIMINAR PERMANENTEMENTE al proveedor "' + name + '"?\n\nEsta acción NO se puede deshacer. Se eliminará de la base de datos.')) {
        window.location.href = '<?php echo BASE_URL; ?>/providers/delete/' + id;
    }
}

function showNewProviderModal(company, username, email, password) {
    document.getElementById('modalProviderCompany').textContent = company;
    document.getElementById('modalProviderUsername').value = username;
    document.getElementById('modalProviderEmail').value = email;
    document.getElementById('modalProviderPassword').value = password;
    document.getElementById('newProviderModal').classList.remove('hidden');
}

function closeNewProviderModal() {
    document.getElementById('newProviderModal').classList.add('hidden');
}

function copyToClipboard(fieldId, btn) {
    const input = document.getElementById(fieldId);
    input.select();
    input.setSelectionRange(0, 99999);
    try {
        navigator.clipboard.writeText(input.value).then(function() {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-green-600"></i>';
            setTimeout(function() { btn.innerHTML = original; }, 1500);
        });
    } catch (e) {
        document.execCommand('copy');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-green-600"></i>';
        setTimeout(function() { btn.innerHTML = original; }, 1500);
    }
}
</script>

<!-- New Provider Credentials Modal -->
<div id="newProviderModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-lg mx-4">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                <i class="fas fa-check-circle text-green-600 text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">¡Proveedor Creado!</h2>
            <p class="text-gray-600 mt-1">Guarda estas credenciales. La contraseña <strong>no se mostrará nuevamente</strong>.</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-5">
            <p class="text-sm font-semibold text-yellow-800 mb-1"><i class="fas fa-hard-hat mr-1"></i>Empresa: <span id="modalProviderCompany"></span></p>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <div class="flex">
                    <input type="text" id="modalProviderUsername" readonly
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg bg-gray-50 font-mono text-sm focus:outline-none">
                    <button onclick="copyToClipboard('modalProviderUsername', this)"
                            class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-200 text-gray-600" title="Copiar usuario">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <div class="flex">
                    <input type="text" id="modalProviderEmail" readonly
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg bg-gray-50 font-mono text-sm focus:outline-none">
                    <button onclick="copyToClipboard('modalProviderEmail', this)"
                            class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-200 text-gray-600" title="Copiar email">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña Temporal <span class="text-red-500">*</span></label>
                <div class="flex">
                    <input type="text" id="modalProviderPassword" readonly
                           class="flex-1 px-4 py-2 border border-yellow-400 rounded-l-lg bg-yellow-50 font-mono text-sm font-bold focus:outline-none">
                    <button onclick="copyToClipboard('modalProviderPassword', this)"
                            class="px-3 py-2 bg-yellow-100 border border-l-0 border-yellow-400 rounded-r-lg hover:bg-yellow-200 text-yellow-800" title="Copiar contraseña">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <p class="text-xs text-red-600 mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>Copia esta contraseña ahora. No se volverá a mostrar.</p>
            </div>
        </div>
        <div class="mt-6 flex justify-center">
            <button onclick="closeNewProviderModal()"
                    class="px-8 py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700">
                <i class="fas fa-check mr-2"></i>Entendido, ya copié las credenciales
            </button>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
