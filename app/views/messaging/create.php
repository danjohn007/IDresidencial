<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-box mr-2 text-blue-600"></i>Registrar Paquete</h1>
                    <p class="text-gray-600 mt-1">Registrar recepción de un nuevo paquete o correspondencia</p>
                </div>

                <!-- Error Messages -->
                <?php if (!empty($error)): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/messaging/create">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Propiedad / Residente <span class="text-red-500">*</span>
                                </label>
                                <select name="property_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Seleccionar propiedad...</option>
                                    <?php foreach ($properties as $prop): ?>
                                    <option value="<?php echo $prop['id']; ?>"><?php echo htmlspecialchars($prop['property_number']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Paquete <span class="text-red-500">*</span>
                                </label>
                                <select name="package_type" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="paquete">Paquete</option>
                                    <option value="sobre">Sobre</option>
                                    <option value="documento">Documento</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Remitente
                                </label>
                                <input type="text" name="sender"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Amazon, DHL, FedEx, nombre de persona...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de Rastreo
                                </label>
                                <input type="text" name="tracking_number"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                                       placeholder="Ej: 1Z999AA10123456784">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Descripción
                                </label>
                                <input type="text" name="description"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Caja mediana, sobre blanco...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Notas adicionales
                                </label>
                                <textarea name="notes" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Observaciones, estado del paquete, etc."></textarea>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="<?php echo BASE_URL; ?>/messaging"
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Registrar Paquete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
