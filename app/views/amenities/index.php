<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">🏊 Amenidades</h1>
                    <p class="text-gray-600 mt-1">Reserva las amenidades del residencial</p>
                </div>
                <div class="flex space-x-2">
                    <?php if ($_SESSION['role'] === 'superadmin'): ?>
                        <a href="<?php echo BASE_URL; ?>/amenities/manage" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-cog mr-2"></i> Gestionar
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/amenities/myReservations" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-calendar-check mr-2"></i> Mis Reservaciones
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($amenities as $amenity): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                        <div class="h-48 bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <i class="fas fa-<?php 
                                echo match($amenity['amenity_type']) {
                                    'salon' => 'door-open',
                                    'alberca' => 'swimming-pool',
                                    'asadores' => 'fire',
                                    'cancha' => 'futbol',
                                    'gimnasio' => 'dumbbell',
                                    default => 'star'
                                };
                            ?> text-white text-6xl"></i>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($amenity['name']); ?></h3>
                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($amenity['description']); ?></p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-users w-5"></i>
                                    <span>Capacidad: <?php echo $amenity['capacity']; ?> personas</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-clock w-5"></i>
                                    <span><?php echo date('H:i', strtotime($amenity['hours_open'])); ?> - <?php echo date('H:i', strtotime($amenity['hours_close'])); ?></span>
                                </div>
                                <?php if ($amenity['requires_payment']): ?>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-dollar-sign w-5"></i>
                                        <span>$<?php echo number_format($amenity['hourly_rate'], 2); ?> por hora</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <a href="<?php echo BASE_URL; ?>/amenities/reserve/<?php echo $amenity['id']; ?>" 
                               class="block w-full bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-calendar-plus mr-2"></i> Reservar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
