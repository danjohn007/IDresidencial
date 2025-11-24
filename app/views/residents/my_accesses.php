<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🎫 Mis Pases de Acceso</h1>
                    <p class="text-gray-600 mt-1">Gestiona tus pases de acceso generados</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/residents/generateAccess" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Generar Nuevo Pase
                </a>
            </div>

            <?php if (empty($passes)): ?>
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <i class="fas fa-qrcode text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes pases de acceso</h3>
                    <p class="text-gray-500 mb-6">Genera tu primer pase de acceso para visitantes o servicios</p>
                    <a href="<?php echo BASE_URL; ?>/residents/generateAccess" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Generar Pase
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($passes as $pass): ?>
                        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
                            <div class="p-6">
                                <!-- Status Badge -->
                                <div class="flex items-center justify-between mb-4">
                                    <span class="px-3 py-1 text-xs rounded-full font-semibold <?php 
                                        echo match($pass['status']) {
                                            'active' => 'bg-green-100 text-green-800',
                                            'expired' => 'bg-gray-100 text-gray-800',
                                            'used' => 'bg-blue-100 text-blue-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($pass['status']); ?>
                                    </span>
                                    <span class="px-3 py-1 text-xs rounded-full font-semibold bg-purple-100 text-purple-800">
                                        <?php echo ucfirst(str_replace('_', ' ', $pass['pass_type'])); ?>
                                    </span>
                                </div>

                                <!-- QR Code -->
                                <div class="flex justify-center mb-4">
                                    <div class="bg-gray-100 p-4 rounded-lg">
                                        <?php
                                        // Check if local QR library is available, otherwise use external service
                                        $qrImageUrl = BASE_URL . '/api/qr?data=' . urlencode($pass['qr_code']);
                                        // Fallback to external service if local not available
                                        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($pass['qr_code']);
                                        ?>
                                        <img src="<?php echo $qrImageUrl; ?>" 
                                             alt="QR Code" class="w-32 h-32"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22150%22 height=%22150%22%3E%3Crect width=%22150%22 height=%22150%22 fill=%22%23ddd%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EQR Code%3C/text%3E%3C/svg%3E'">
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-calendar-alt w-5 mr-2"></i>
                                        <span>Desde: <?php echo date('d/m/Y H:i', strtotime($pass['valid_from'])); ?></span>
                                    </div>
                                    <?php if ($pass['valid_until']): ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-calendar-check w-5 mr-2"></i>
                                        <span>Hasta: <?php echo date('d/m/Y H:i', strtotime($pass['valid_until'])); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-hashtag w-5 mr-2"></i>
                                        <span>Usos: <?php echo $pass['uses_count']; ?> / <?php echo $pass['max_uses']; ?></span>
                                    </div>
                                    <?php if ($pass['notes']): ?>
                                    <div class="pt-2 border-t">
                                        <p class="text-gray-700 text-xs"><strong>Notas:</strong> <?php echo htmlspecialchars($pass['notes']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Actions -->
                                <?php if ($pass['status'] === 'active'): ?>
                                <div class="mt-4 pt-4 border-t">
                                    <button onclick="cancelPass(<?php echo $pass['id']; ?>)" 
                                            class="w-full px-4 py-2 text-sm text-red-600 border border-red-300 rounded-lg hover:bg-red-50">
                                        <i class="fas fa-ban mr-2"></i> Cancelar Pase
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
function cancelPass(passId) {
    if (confirm('¿Está seguro que desea cancelar este pase de acceso?')) {
        window.location.href = `<?php echo BASE_URL; ?>/residents/cancelPass/${passId}`;
    }
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
