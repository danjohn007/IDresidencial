<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <a href="<?php echo BASE_URL; ?>/access" class="text-blue-600 hover:text-blue-700">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white">
                        <h1 class="text-2xl font-bold">Pase de Visita</h1>
                        <p class="text-blue-100">Código: <?php echo $visit['qr_code']; ?></p>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- QR Code -->
                            <div class="text-center">
                                <img src="<?php echo $qr_image; ?>" alt="QR Code" class="mx-auto mb-4 border-4 border-gray-200 rounded-lg">
                                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-print mr-2"></i> Imprimir
                                </button>
                            </div>

                            <!-- Visit Details -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Visitante</h3>
                                    <p class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($visit['visitor_name']); ?></p>
                                </div>

                                <?php if ($visit['visitor_id']): ?>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Identificación</h3>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($visit['visitor_id']); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if ($visit['visitor_phone']): ?>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Teléfono</h3>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($visit['visitor_phone']); ?></p>
                                </div>
                                <?php endif; ?>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Residente</h3>
                                    <p class="text-gray-900"><?php echo $visit['first_name'] . ' ' . $visit['last_name']; ?></p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Propiedad</h3>
                                    <p class="text-gray-900"><?php echo $visit['property_number']; ?> - <?php echo $visit['section']; ?></p>
                                </div>

                                <?php if ($visit['vehicle_plate']): ?>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Vehículo</h3>
                                    <p class="text-gray-900"><?php echo $visit['vehicle_plate']; ?></p>
                                </div>
                                <?php endif; ?>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Vigencia</h3>
                                    <p class="text-gray-900">
                                        Desde: <?php echo date('d/m/Y H:i', strtotime($visit['valid_from'])); ?><br>
                                        Hasta: <?php echo date('d/m/Y H:i', strtotime($visit['valid_until'])); ?>
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">Estado</h3>
                                    <span class="px-3 py-1 text-sm rounded-full <?php 
                                        echo match($visit['status']) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'active' => 'bg-green-100 text-green-800',
                                            'completed' => 'bg-gray-100 text-gray-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'expired' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($visit['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
