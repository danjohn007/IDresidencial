<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-2xl mx-auto">
                <div class="mb-6 text-center">
                    <h1 class="text-3xl font-bold text-gray-900">Validar Código QR</h1>
                    <p class="text-gray-600 mt-1">Escanea o ingresa el código QR del visitante</p>
                </div>

                <!-- Scanner Form -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/access/validate">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Código QR
                            </label>
                            <input type="text" name="qr_code" autofocus required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                   placeholder="VIS-20241121-ABCD1234">
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fas fa-search mr-2"></i>
                            Validar Código
                        </button>
                    </form>
                </div>

                <!-- Validation Result -->
                <?php if (isset($valid)): ?>
                    <?php if ($valid && $visit): ?>
                        <div class="bg-green-50 border-2 border-green-500 rounded-lg p-6">
                            <div class="text-center mb-4">
                                <i class="fas fa-check-circle text-green-600 text-6xl mb-2"></i>
                                <h2 class="text-2xl font-bold text-green-800">✅ Código Válido</h2>
                            </div>
                            
                            <div class="bg-white rounded-lg p-6 space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600">Visitante</p>
                                    <p class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($visit['visitor_name']); ?></p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Residente</p>
                                        <p class="font-semibold text-gray-900"><?php echo $visit['first_name'] . ' ' . $visit['last_name']; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Propiedad</p>
                                        <p class="font-semibold text-gray-900"><?php echo $visit['property_number']; ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($visit['vehicle_plate']): ?>
                                    <div>
                                        <p class="text-sm text-gray-600">Vehículo</p>
                                        <p class="font-semibold text-gray-900"><?php echo $visit['vehicle_plate']; ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div>
                                    <p class="text-sm text-gray-600">Vigencia</p>
                                    <p class="font-semibold text-gray-900">
                                        <?php echo date('d/m/Y H:i', strtotime($visit['valid_from'])); ?> - 
                                        <?php echo date('d/m/Y H:i', strtotime($visit['valid_until'])); ?>
                                    </p>
                                </div>
                                
                                <?php if ($visit['status'] === 'pending'): ?>
                                    <div class="pt-4 border-t">
                                        <a href="<?php echo BASE_URL; ?>/access/registerEntry/<?php echo $visit['id']; ?>" 
                                           class="block w-full bg-green-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                                            <i class="fas fa-sign-in-alt mr-2"></i>
                                            Registrar Entrada
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-red-50 border-2 border-red-500 rounded-lg p-6">
                            <div class="text-center">
                                <i class="fas fa-times-circle text-red-600 text-6xl mb-2"></i>
                                <h2 class="text-2xl font-bold text-red-800 mb-2">❌ Código Inválido</h2>
                                <p class="text-red-700"><?php echo $error; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
