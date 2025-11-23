<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">👤 Detalles del Residente</h1>
                        <p class="text-gray-600 mt-1">Información completa del residente</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/residents" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Volver
                    </a>
                </div>

                <!-- Resident Info Card -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Información Personal</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600">Nombre Completo</p>
                            <p class="text-lg font-medium text-gray-900">
                                <?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($resident['email']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Teléfono</p>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($resident['phone']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Propiedad</p>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($resident['property_number']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Relación</p>
                            <p class="text-lg font-medium">
                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                    echo match($resident['relationship']) {
                                        'propietario' => 'bg-green-100 text-green-800',
                                        'inquilino' => 'bg-yellow-100 text-yellow-800',
                                        'familiar' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>">
                                    <?php echo ucfirst($resident['relationship']); ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Estado</p>
                            <p class="text-lg font-medium">
                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                    echo $resident['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                ?>">
                                    <?php echo ucfirst($resident['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Vehicles -->
                <?php if (!empty($vehicles)): ?>
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Vehículos Registrados</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Placa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modelo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($vehicle['plate']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($vehicle['brand']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($vehicle['model']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($vehicle['color']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo ucfirst($vehicle['vehicle_type']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Maintenance Fees -->
                <?php if (!empty($fees)): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Estado de Cuenta</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Venc.</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($fees as $fee): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo date('F Y', strtotime($fee['period'] . '-01')); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            $<?php echo number_format($fee['amount'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('d/m/Y', strtotime($fee['due_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full <?php 
                                                echo match($fee['status']) {
                                                    'paid' => 'bg-green-100 text-green-800',
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'overdue' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            ?>">
                                                <?php echo ucfirst($fee['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
