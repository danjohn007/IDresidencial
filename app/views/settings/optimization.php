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
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/optimization">
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
                                <p class="text-xs text-gray-500 mt-1">Menor cantidad = páginas más rápidas</p>
                            </div>

                            <!-- Frontend Optimization -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2 mt-4">
                                    <i class="fas fa-images text-purple-600 mr-2"></i>Optimización Frontend
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
                                    <p class="text-sm text-gray-500">Carga contenido bajo demanda</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="lazy_loading" value="1" 
                                           <?php echo (!isset($current['lazy_loading']) || $current['lazy_loading'] == '1') ? 'checked' : ''; ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Session Settings -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Timeout de Sesión (segundos)
                                </label>
                                <input type="number" name="session_timeout" min="900" max="86400"
                                       value="<?php echo htmlspecialchars($current['session_timeout'] ?? '3600'); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Recomendado: 3600 (1 hora)</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="border-t pt-6 mt-6">
                            <div class="flex items-center justify-between">
                                <button type="submit" name="run_optimization" value="1"
                                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="fas fa-bolt mr-2"></i>Guardar y Optimizar Ahora
                                </button>
                                
                                <div class="flex space-x-3">
                                    <a href="<?php echo BASE_URL; ?>/settings" 
                                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                        Volver
                                    </a>
                                    <button type="submit" 
                                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        <i class="fas fa-save mr-2"></i>Guardar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Recommendations -->
                <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-yellow-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Recomendaciones</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>• Ejecutar optimización al menos una vez al mes</p>
                                <p>• Mantener máximo de registros entre 20-50 para mejor rendimiento</p>
                                <p>• Habilitar cache y lazy loading para navegación más fluida</p>
                                <p>• Los logs se limpian automáticamente después de 180 días</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
