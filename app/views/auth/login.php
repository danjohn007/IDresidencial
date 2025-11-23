<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100 px-4">
    <div class="max-w-md w-full">
        <!-- Logo and title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-full mb-4">
                <i class="fas fa-building text-white text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo SITE_NAME; ?></h1>
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
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
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
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Ingresa tu contraseña"
                            required
                        >
                    </div>
                </div>
                
                <!-- Remember me -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                    </label>
                    <a href="<?php echo BASE_URL; ?>/auth/forgotPassword" class="text-sm text-blue-600 hover:text-blue-700">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                
                <!-- Submit button -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 shadow-lg hover:shadow-xl"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Iniciar Sesión
                </button>
            </form>
            
            <!-- Additional links -->
            <div class="mt-6 text-center text-sm text-gray-600">
                ¿No tienes una cuenta? 
                <a href="<?php echo BASE_URL; ?>/auth/register" class="text-blue-600 hover:text-blue-700 font-medium">
                    Regístrate aquí
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
            <p class="mt-1">
                <a href="<?php echo BASE_URL; ?>/test_connection.php" class="text-blue-600 hover:text-blue-700">
                    <i class="fas fa-plug mr-1"></i> Probar conexión
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
