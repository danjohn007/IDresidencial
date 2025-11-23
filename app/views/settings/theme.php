<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🎨 Personalización de Tema</h1>
                    <p class="text-gray-600 mt-1">Personaliza los colores y apariencia del sistema</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded alert-auto-hide">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/settings/theme" id="themeForm">
                        <!-- Theme Color -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-4">
                                Color Principal del Tema
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <?php
                                // Get current theme color
                                $db = Database::getInstance()->getConnection();
                                $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'theme_color'");
                                $currentThemeRow = $stmt->fetch();
                                $currentTheme = $currentThemeRow ? $currentThemeRow['setting_value'] : 'blue';
                                
                                $colors = [
                                    'blue' => ['name' => 'Azul', 'class' => 'bg-blue-600', 'hover' => 'hover:bg-blue-700'],
                                    'green' => ['name' => 'Verde', 'class' => 'bg-green-600', 'hover' => 'hover:bg-green-700'],
                                    'purple' => ['name' => 'Morado', 'class' => 'bg-purple-600', 'hover' => 'hover:bg-purple-700'],
                                    'red' => ['name' => 'Rojo', 'class' => 'bg-red-600', 'hover' => 'hover:bg-red-700'],
                                    'orange' => ['name' => 'Naranja', 'class' => 'bg-orange-600', 'hover' => 'hover:bg-orange-700'],
                                    'indigo' => ['name' => 'Índigo', 'class' => 'bg-indigo-600', 'hover' => 'hover:bg-indigo-700']
                                ];
                                foreach ($colors as $color => $info):
                                ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="theme_color" value="<?php echo $color; ?>" 
                                               class="peer sr-only theme-color-radio" 
                                               <?php echo $color === $currentTheme ? 'checked' : ''; ?>>
                                        <div class="p-4 border-2 rounded-lg peer-checked:border-<?php echo $color; ?>-600 peer-checked:bg-<?php echo $color; ?>-50 hover:border-gray-400 transition">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 <?php echo $info['class']; ?> rounded"></div>
                                                <span class="font-medium"><?php echo $info['name']; ?></span>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-semibold text-gray-900 mb-3">Vista Previa</h3>
                            <div class="space-y-2" id="previewButtons">
                                <button type="button" id="previewPrimary" class="px-4 py-2 <?php echo $colors[$currentTheme]['class'] . ' ' . $colors[$currentTheme]['hover']; ?> text-white rounded-lg transition">Botón Primario</button>
                                <button type="button" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">Botón Secundario</button>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/settings" 
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Volver
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Tema
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Box -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-semibold text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información
                    </h4>
                    <p class="text-sm text-blue-800">
                        Los cambios de tema se aplicarán inmediatamente después de guardar.
                    </p>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Update preview when theme color changes
document.querySelectorAll('.theme-color-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        const color = this.value;
        const colorMap = {
            'blue': { bg: 'bg-blue-600', hover: 'hover:bg-blue-700' },
            'green': { bg: 'bg-green-600', hover: 'hover:bg-green-700' },
            'purple': { bg: 'bg-purple-600', hover: 'hover:bg-purple-700' },
            'red': { bg: 'bg-red-600', hover: 'hover:bg-red-700' },
            'orange': { bg: 'bg-orange-600', hover: 'hover:bg-orange-700' },
            'indigo': { bg: 'bg-indigo-600', hover: 'hover:bg-indigo-700' }
        };
        
        const previewBtn = document.getElementById('previewPrimary');
        // Remove all color classes
        Object.values(colorMap).forEach(classes => {
            previewBtn.classList.remove(classes.bg, classes.hover);
        });
        
        // Add new color classes
        previewBtn.classList.add(colorMap[color].bg, colorMap[color].hover);
    });
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
