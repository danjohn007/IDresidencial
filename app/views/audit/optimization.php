<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-5xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">⚡ Auto-Optimización del Sistema</h1>
                    <p class="text-gray-600 mt-1">Configuración para mejorar el rendimiento y velocidad</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- System Stats -->
                <?php if (isset($stats)): ?>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                        <p class="text-sm text-gray-600">Tamaño BD</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['db_size'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                        <p class="text-sm text-gray-600">Usuarios</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_users'] ?? 0; ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                        <p class="text-sm text-gray-600">Visitas</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total_visits'] ?? 0); ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                        <p class="text-sm text-gray-600">Logs</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total_logs'] ?? 0); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/audit/optimization">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Cache Settings -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                                    <i class="fas fa-database text-blue-600 mr-2"></i>Configuración de Caché
                                </h3>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Cache Habilitado</p>
                                    <p class="text-sm text-gray-500">Mejora la velocidad de consultas frecuentes</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="cache_enabled" value="1" 
                                           <?php echo (!isset($current['cache_enabled']) || $current['cache_enabled'] == '1') ? 'checked' : ''; ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tiempo de Vida del Cache (segundos)
                                </label>
                                <input type="number" name="cache_ttl" 
                                       value="<?php echo htmlspecialchars($current['cache_ttl'] ?? '3600'); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <!-- Query Optimization -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2 mt-4">
                                    <i class="fas fa-search text-green-600 mr-2"></i>Optimización de Consultas
                                </h3>
                            </div>

                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Cache de Consultas</p>
                                    <p class="text-sm text-gray-500">Almacena resultados de consultas SQL</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="query_cache_enabled" value="1" 
                                           <?php echo (!isset($current['query_cache_enabled']) || $current['query_cache_enabled'] == '1') ? 'checked' : ''; ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Máximo de Registros por Página
                                </label>
                                <input type="number" name="max_records_per_page" min="10" max="100"
                                       value="<?php echo htmlspecialchars($current['max_records_per_page'] ?? '50'); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <!-- Frontend Optimization -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2 mt-4">
                                    <i class="fas fa-image text-purple-600 mr-2"></i>Optimización Frontend
                                </h3>
                            </div>

                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Optimización de Imágenes</p>
                                    <p class="text-sm text-gray-500">Comprime imágenes automáticamente</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="image_optimization" value="1" 
                                           <?php echo (!isset($current['image_optimization']) || $current['image_optimization'] == '1') ? 'checked' : ''; ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Lazy Loading</p>
                                    <p class="text-sm text-gray-500">Carga diferida de imágenes</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="lazy_loading" value="1" 
                                           <?php echo (!isset($current['lazy_loading']) || $current['lazy_loading'] == '1') ? 'checked' : ''; ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Session Management -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2 mt-4">
                                    <i class="fas fa-clock text-orange-600 mr-2"></i>Gestión de Sesiones
                                </h3>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tiempo de Expiración de Sesión (segundos)
                                </label>
                                <input type="number" name="session_timeout" min="300" max="86400"
                                       value="<?php echo htmlspecialchars($current['session_timeout'] ?? '3600'); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Recomendado: 3600 (1 hora)</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="border-t pt-6 flex justify-between">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" name="run_optimization" id="run_optimization" value="1"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="run_optimization" class="text-sm text-gray-700">
                                    Ejecutar optimización inmediata (optimizar tablas y limpiar datos antiguos)
                                </label>
                            </div>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Recommendations -->
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3">
                        <i class="fas fa-lightbulb mr-2"></i>Recomendaciones
                    </h3>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li><i class="fas fa-check-circle text-blue-600 mr-2"></i>Habilitar el caché mejora significativamente el rendimiento</li>
                        <li><i class="fas fa-check-circle text-blue-600 mr-2"></i>Un TTL de caché entre 1-2 horas es ideal para datos que cambian poco</li>
                        <li><i class="fas fa-check-circle text-blue-600 mr-2"></i>Ejecutar optimización de tablas semanalmente mantiene la BD eficiente</li>
                        <li><i class="fas fa-check-circle text-blue-600 mr-2"></i>Lazy loading reduce el tiempo de carga inicial de páginas</li>
                        <li><i class="fas fa-check-circle text-blue-600 mr-2"></i>Limitar registros por página (50-100) mejora la experiencia de usuario</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
