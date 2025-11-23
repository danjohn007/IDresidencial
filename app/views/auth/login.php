<?php 
require_once APP_PATH . '/views/layouts/header.php';

// Get settings from database
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('site_name', 'site_logo', 'site_email', 'site_phone', 'theme_color')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$siteName = $settings['site_name'] ?? SITE_NAME;
$siteLogo = $settings['site_logo'] ?? null;
$siteEmail = $settings['site_email'] ?? SITE_EMAIL;
$sitePhone = $settings['site_phone'] ?? SITE_PHONE;
$themeColor = $settings['theme_color'] ?? 'blue';
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-<?php echo $themeColor; ?>-50 to-<?php echo $themeColor; ?>-100 px-4">
    <div class="max-w-md w-full">
        <!-- Logo and title -->
        <div class="text-center mb-8">
            <?php if ($siteLogo && file_exists($siteLogo)): ?>
                <div class="mb-4">
                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($siteLogo); ?>" 
                         alt="Logo" class="h-20 mx-auto object-contain">
                </div>
            <?php else: ?>
                <div class="inline-flex items-center justify-center w-20 h-20 bg-<?php echo $themeColor; ?>-600 rounded-full mb-4">
                    <i class="fas fa-building text-white text-4xl"></i>
                </div>
            <?php endif; ?>
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($siteName); ?></h1>
            <p class="text-gray-600">Sistema de Gestión Residencial</p>
        </div>
        
        <!-- Login form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Iniciar Sesión</h2>
            
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/auth/login">
                <!-- Username -->
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Usuario o Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-<?php echo $themeColor; ?>-500 focus:border-<?php echo $themeColor; ?>-500 transition"
                            placeholder="Ingresa tu usuario"
                            required
                            autofocus
                        >
                    </div>
                </div>
                
                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-<?php echo $themeColor; ?>-500 focus:border-<?php echo $themeColor; ?>-500 transition"
                            placeholder="Ingresa tu contraseña"
                            required
                        >
                    </div>
                </div>
                
                <!-- Remember me -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-<?php echo $themeColor; ?>-600 focus:ring-<?php echo $themeColor; ?>-500">
                        <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                    </label>
                    <a href="<?php echo BASE_URL; ?>/auth/forgotPassword" class="text-sm text-<?php echo $themeColor; ?>-600 hover:text-<?php echo $themeColor; ?>-700">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                
                <!-- Submit button -->
                <button 
                    type="submit" 
                    class="w-full bg-<?php echo $themeColor; ?>-600 text-white py-3 rounded-lg font-semibold hover:bg-<?php echo $themeColor; ?>-700 transition-colors duration-200 shadow-lg hover:shadow-xl"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Iniciar Sesión
                </button>
            </form>
            
            <!-- Additional links -->
            <div class="mt-6 text-center text-sm text-gray-600">
                ¿No tienes una cuenta? 
                <a href="<?php echo BASE_URL; ?>/auth/register" class="text-<?php echo $themeColor; ?>-600 hover:text-<?php echo $themeColor; ?>-700 font-medium">
                    Regístrate aquí
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?>. Todos los derechos reservados.</p>
            <p class="mt-2">
                <i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($siteEmail); ?>
            </p>
            <p class="mt-1">
                <i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($sitePhone); ?>
            </p>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
