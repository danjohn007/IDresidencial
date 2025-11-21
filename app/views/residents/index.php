<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">👥 Residentes</h1>
                    <p class="text-gray-600 mt-1">Gestión de residentes y propiedades</p>
                </div>
                <div class="space-x-2">
                    <a href="<?php echo BASE_URL; ?>/residents/create" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-user-plus mr-2"></i> Nuevo Residente
                    </a>
                    <a href="<?php echo BASE_URL; ?>/residents/properties" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-home mr-2"></i> Propiedades
                    </a>
                    <a href="<?php echo BASE_URL; ?>/residents/payments" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-dollar-sign mr-2"></i> Pagos
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Total Residentes</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Propietarios</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $stats['propietarios']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Inquilinos</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['inquilinos']; ?></p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-600 mb-1">Familiares</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $stats['familiares']; ?></p>
                </div>
            </div>

            <!-- Residents Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Propiedad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sección</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Relación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($residents as $resident): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                            <?php echo strtoupper(substr($resident['first_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo $resident['first_name'] . ' ' . $resident['last_name']; ?></p>
                                            <p class="text-sm text-gray-500"><?php echo $resident['email']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo $resident['property_number']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $resident['section']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $resident['phone']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        echo $resident['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                    ?>">
                                        <?php echo ucfirst($resident['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="<?php echo BASE_URL; ?>/residents/view/<?php echo $resident['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
