<!-- Top Navigation Bar -->
<nav class="bg-white shadow-sm border-b">
    <div class="px-4 py-3">
        <div class="flex items-center justify-between">
            <!-- Mobile menu button -->
            <button id="menu-button" class="lg:hidden text-gray-600 hover:text-gray-900" onclick="toggleMobileMenu()">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <?php
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'site_logo'");
                $logoRow = $stmt->fetch();
                $siteLogo = $logoRow ? $logoRow['setting_value'] : null;
                $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'site_name'");
                $nameRow = $stmt->fetch();
                $siteName = $nameRow ? $nameRow['setting_value'] : SITE_NAME;
                
                if ($siteLogo && file_exists($siteLogo)):
                ?>
                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($siteLogo); ?>" 
                         alt="Logo" class="h-10 object-contain hidden lg:block">
                <?php else: ?>
                    <i class="fas fa-building text-blue-600 text-2xl hidden lg:block"></i>
                <?php endif; ?>
                <span class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($siteName); ?></span>
            </div>
            
            <!-- Right side items -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="text-gray-600 hover:text-gray-900 relative">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                    </button>
                </div>
                
                <!-- User menu -->
                <div class="relative group">
                    <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                        <?php
                        $userId = $_SESSION['user_id'] ?? null;
                        $userPhoto = null;
                        if ($userId) {
                            $stmt = $db->prepare("SELECT photo FROM users WHERE id = ?");
                            $stmt->execute([$userId]);
                            $userRow = $stmt->fetch();
                            $userPhoto = $userRow ? $userRow['photo'] : null;
                        }
                        ?>
                        <?php if ($userPhoto && file_exists(PUBLIC_PATH . '/uploads/profiles/' . $userPhoto)): ?>
                            <img src="<?php echo PUBLIC_URL . '/uploads/profiles/' . htmlspecialchars($userPhoto); ?>" 
                                 alt="Foto de perfil" 
                                 class="w-8 h-8 rounded-full object-cover border-2 border-blue-500">
                        <?php else: ?>
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="hidden md:block"><?php echo $_SESSION['first_name'] ?? 'Usuario'; ?></span>
                        <i class="fas fa-chevron-down text-sm"></i>
                    </button>
                    
                    <!-- Dropdown menu -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="py-2">
                            <div class="px-4 py-2 border-b">
                                <p class="text-sm font-semibold text-gray-900"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
                                <p class="text-xs text-gray-500"><?php echo ucfirst($_SESSION['role']); ?></p>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Mi Perfil
                            </a>
                            <a href="<?php echo BASE_URL; ?>/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i> Configuración
                            </a>
                            <div class="border-t"></div>
                            <a href="<?php echo BASE_URL; ?>/auth/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
