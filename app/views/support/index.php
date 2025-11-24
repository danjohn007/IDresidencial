<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - <?php echo $settings['site_name'] ?? 'ERP Residencial'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-building text-blue-600 text-2xl"></i>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($settings['site_name'] ?? 'ERP Residencial'); ?></h1>
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/login" class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-6">
                <i class="fas fa-headset text-blue-600 text-4xl"></i>
            </div>
            <h2 class="text-4xl font-bold text-gray-900 mb-4">🛠️ Soporte Técnico</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Estamos aquí para ayudarte. Encuentra respuestas o contáctanos directamente.
            </p>
        </div>

        <!-- Contact Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Email -->
            <?php if (!empty($settings['support_email'])): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-envelope text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Email</h3>
                <a href="mailto:<?php echo htmlspecialchars($settings['support_email']); ?>" 
                   class="text-blue-600 hover:text-blue-800 break-all">
                    <?php echo htmlspecialchars($settings['support_email']); ?>
                </a>
            </div>
            <?php endif; ?>

            <!-- Phone -->
            <?php if (!empty($settings['support_phone'])): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                    <i class="fas fa-phone text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Teléfono</h3>
                <a href="tel:<?php echo htmlspecialchars($settings['support_phone']); ?>" 
                   class="text-green-600 hover:text-green-800">
                    <?php echo htmlspecialchars($settings['support_phone']); ?>
                </a>
            </div>
            <?php endif; ?>

            <!-- Hours -->
            <?php if (!empty($settings['support_hours'])): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                    <i class="fas fa-clock text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Horario</h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($settings['support_hours']); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- FAQ Section -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Preguntas Frecuentes</h3>
            
            <div class="space-y-4">
                <div class="border-b pb-4">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleFaq(1)">
                        <span class="text-lg font-semibold text-gray-900">¿Cómo puedo recuperar mi contraseña?</span>
                        <i class="fas fa-chevron-down text-gray-500" id="faq-icon-1"></i>
                    </button>
                    <div id="faq-content-1" class="hidden mt-3 text-gray-600">
                        <p>En la página de inicio de sesión, haz clic en "¿Olvidaste tu contraseña?" e ingresa tu correo electrónico. 
                        Recibirás un enlace para restablecer tu contraseña.</p>
                    </div>
                </div>

                <div class="border-b pb-4">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleFaq(2)">
                        <span class="text-lg font-semibold text-gray-900">¿Cómo puedo realizar pagos en línea?</span>
                        <i class="fas fa-chevron-down text-gray-500" id="faq-icon-2"></i>
                    </button>
                    <div id="faq-content-2" class="hidden mt-3 text-gray-600">
                        <p>Los residentes pueden realizar pagos desde su portal personal en la sección "Mis Pagos". 
                        Aceptamos pagos a través de PayPal y otras opciones configuradas por la administración.</p>
                    </div>
                </div>

                <div class="border-b pb-4">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleFaq(3)">
                        <span class="text-lg font-semibold text-gray-900">¿Cómo genero un pase de acceso para visitantes?</span>
                        <i class="fas fa-chevron-down text-gray-500" id="faq-icon-3"></i>
                    </button>
                    <div id="faq-content-3" class="hidden mt-3 text-gray-600">
                        <p>Desde tu portal de residente, ve a "Generar Accesos" donde podrás crear códigos QR 
                        para tus visitantes con diferentes opciones de duración y uso.</p>
                    </div>
                </div>

                <div class="pb-4">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleFaq(4)">
                        <span class="text-lg font-semibold text-gray-900">¿Quién puede ayudarme con problemas técnicos?</span>
                        <i class="fas fa-chevron-down text-gray-500" id="faq-icon-4"></i>
                    </button>
                    <div id="faq-content-4" class="hidden mt-3 text-gray-600">
                        <p>Puedes contactarnos directamente por correo o teléfono durante nuestro horario de atención. 
                        Nuestro equipo de soporte técnico está disponible para ayudarte con cualquier problema.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-8 text-center text-white">
            <h3 class="text-2xl font-bold mb-4">¿Necesitas más ayuda?</h3>
            <p class="mb-6 text-blue-100">
                Nuestro equipo está listo para asistirte con cualquier consulta o problema que tengas.
            </p>
            <?php if (!empty($settings['support_email'])): ?>
            <a href="mailto:<?php echo htmlspecialchars($settings['support_email']); ?>" 
               class="inline-block px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-colors">
                <i class="fas fa-envelope mr-2"></i>Contáctanos
            </a>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-600 text-sm">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name'] ?? 'ERP Residencial'); ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        function toggleFaq(id) {
            const content = document.getElementById('faq-content-' + id);
            const icon = document.getElementById('faq-icon-' + id);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
    </script>
</body>
</html>
