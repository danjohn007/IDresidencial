<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100 px-4">
    <div class="max-w-md w-full">
        <!-- Logo and title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-full mb-4">
                <i class="fas fa-building text-white text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo SITE_NAME; ?></h1>
            <p class="text-gray-600">Registro de Nuevo Usuario</p>
        </div>
        
        <!-- Register form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Crear Cuenta</h2>
            
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <p><?php echo $success; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/auth/register">
                <!-- First Name -->
                <div class="mb-4">
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="first_name" 
                        name="first_name" 
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Tu nombre"
                        required
                    >
                </div>

                <!-- Last Name -->
                <div class="mb-4">
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Apellido <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="last_name" 
                        name="last_name" 
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Tu apellido"
                        required
                    >
                </div>
                
                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="correo@ejemplo.com"
                        required
                    >
                </div>

                <!-- Phone/WhatsApp -->
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono/WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        maxlength="10"
                        pattern="[0-9]{10}"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="4421234567 (10 dígitos)"
                        required
                    >
                    <p class="text-xs text-gray-500 mt-1">Ingresa 10 dígitos sin espacios ni guiones</p>
                </div>

                <!-- Property Selection -->
                <div class="mb-4">
                    <label for="property_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Propiedad/Casa <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="property_id" 
                        name="property_id" 
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        required
                    >
                        <option value="">Selecciona tu propiedad</option>
                        <?php
                        $db = Database::getInstance()->getConnection();
                        $stmt = $db->query("SELECT id, property_number, street, section FROM properties ORDER BY section, property_number");
                        $properties = $stmt->fetchAll();
                        foreach ($properties as $property):
                        ?>
                            <option value="<?php echo $property['id']; ?>">
                                <?php echo htmlspecialchars($property['property_number']); ?>
                                <?php if ($property['section']): ?>
                                    - <?php echo htmlspecialchars($property['section']); ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        minlength="6"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Mínimo 6 caracteres"
                        required
                    >
                </div>

                <!-- CAPTCHA -->
                <div class="mb-4">
                    <label for="captcha" class="block text-sm font-medium text-gray-700 mb-2">
                        Verificación <span class="text-red-500">*</span>
                    </label>
                    <?php
                    // Only generate CAPTCHA if not in session or if form was submitted incorrectly
                    if (!isset($_SESSION['captcha_num1']) || !isset($_SESSION['captcha_num2']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $_SESSION['captcha_num1'] = rand(1, 9);
                        $_SESSION['captcha_num2'] = rand(1, 9);
                        $_SESSION['captcha_answer'] = $_SESSION['captcha_num1'] + $_SESSION['captcha_num2'];
                    }
                    $num1 = $_SESSION['captcha_num1'];
                    $num2 = $_SESSION['captcha_num2'];
                    ?>
                    <div class="flex items-center space-x-3">
                        <div class="bg-gray-100 px-4 py-3 rounded-lg border border-gray-300 font-mono text-lg">
                            <?php echo $num1; ?> + <?php echo $num2; ?> = ?
                        </div>
                        <input 
                            type="number" 
                            id="captcha" 
                            name="captcha" 
                            class="w-24 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="?"
                            required
                        >
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="mb-6">
                    <label class="flex items-start space-x-3">
                        <input 
                            type="checkbox" 
                            name="accept_terms" 
                            required
                            class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        >
                        <span class="text-sm text-gray-700">
                            Acepto los <a href="#" class="text-blue-600 hover:text-blue-700 underline">términos y condiciones</a> 
                            y la <a href="#" class="text-blue-600 hover:text-blue-700 underline">política de privacidad</a>
                            <span class="text-red-500">*</span>
                        </span>
                    </label>
                </div>
                
                <!-- Submit button -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 shadow-lg hover:shadow-xl"
                >
                    <i class="fas fa-user-plus mr-2"></i>
                    Registrarse
                </button>

                <!-- Notice -->
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-xs text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Tu cuenta estará pendiente de verificación por correo electrónico y aprobación del administrador antes de poder acceder al sistema.
                    </p>
                </div>
            </form>
            
            <!-- Additional links -->
            <div class="mt-6 text-center text-sm text-gray-600">
                ¿Ya tienes cuenta? 
                <a href="<?php echo BASE_URL; ?>/auth/login" class="text-blue-600 hover:text-blue-700 font-medium">
                    Inicia sesión aquí
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
