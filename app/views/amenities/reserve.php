<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <a href="<?php echo BASE_URL; ?>/amenities" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Amenidades
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900 mt-2">🎯 <?php echo htmlspecialchars($amenity['name']); ?></h1>
                    <p class="text-gray-600 mt-1">Complete el formulario para reservar esta amenidad</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <p class="font-medium">Error</p>
                        <p><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                        <p class="font-medium">Éxito</p>
                        <p><?php echo $success; ?></p>
                    </div>
                <?php endif; ?>

                <!-- Información de la Amenidad -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Información de la Amenidad</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Tipo</p>
                            <p class="font-medium"><?php echo ucfirst($amenity['amenity_type']); ?></p>
                        </div>
                        <?php if ($amenity['capacity']): ?>
                        <div>
                            <p class="text-sm text-gray-600">Capacidad</p>
                            <p class="font-medium"><?php echo $amenity['capacity']; ?> personas</p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-sm text-gray-600">Horario</p>
                            <p class="font-medium"><?php echo $amenity['hours_open'] . ' - ' . $amenity['hours_close']; ?></p>
                        </div>
                        <?php if ($amenity['requires_payment']): ?>
                        <div>
                            <p class="text-sm text-gray-600">Costo por Hora</p>
                            <p class="font-medium text-green-600">$<?php echo number_format($amenity['hourly_rate'], 2); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($amenity['description']): ?>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">Descripción</p>
                        <p class="text-gray-800"><?php echo htmlspecialchars($amenity['description']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Formulario de Reservación -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Datos de la Reservación</h2>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/amenities/reserve/<?php echo $amenity['id']; ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fecha de Reservación -->
                            <div>
                                <label for="reservation_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha de Reservación *
                                </label>
                                <input type="date" 
                                       id="reservation_date" 
                                       name="reservation_date" 
                                       required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Hora de Inicio -->
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                                    Hora de Inicio *
                                </label>
                                <input type="time" 
                                       id="start_time" 
                                       name="start_time" 
                                       required
                                       min="<?php echo $amenity['hours_open']; ?>"
                                       max="<?php echo $amenity['hours_close']; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Hora de Fin -->
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                                    Hora de Fin *
                                </label>
                                <input type="time" 
                                       id="end_time" 
                                       name="end_time" 
                                       required
                                       min="<?php echo $amenity['hours_open']; ?>"
                                       max="<?php echo $amenity['hours_close']; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Número de Invitados -->
                            <div>
                                <label for="guests_count" class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de Invitados
                                </label>
                                <input type="number" 
                                       id="guests_count" 
                                       name="guests_count" 
                                       min="0"
                                       max="<?php echo $amenity['capacity'] ?: 100; ?>"
                                       value="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Notas -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Notas o Comentarios
                                </label>
                                <textarea id="notes" 
                                          name="notes" 
                                          rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Información adicional sobre su reservación..."></textarea>
                            </div>
                        </div>

                        <!-- Información de Pago -->
                        <?php if ($amenity['requires_payment']): ?>
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h3 class="font-semibold text-blue-900 mb-2">
                                <i class="fas fa-info-circle mr-2"></i>Información de Pago
                            </h3>
                            <p class="text-sm text-blue-800">
                                Esta amenidad requiere pago. El costo es de <strong>$<?php echo number_format($amenity['hourly_rate'], 2); ?> por hora</strong>. 
                                Después de enviar su reservación, recibirá instrucciones para realizar el pago.
                            </p>
                        </div>
                        <?php endif; ?>

                        <!-- Botones -->
                        <div class="mt-6 flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/amenities" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-check mr-2"></i>Crear Reservación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
