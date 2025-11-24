<!-- Sidebar -->
<aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-full flex flex-col">
        <?php
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'site_logo'");
        $logoRow = $stmt->fetch();
        $siteLogo = $logoRow ? $logoRow['setting_value'] : null;
        $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'site_name'");
        $nameRow = $stmt->fetch();
        $siteName = $nameRow ? $nameRow['setting_value'] : SITE_NAME;
        ?>
        
        <!-- Logo (desktop) -->
        <div class="p-4 border-b hidden lg:block">
            <div class="flex flex-col items-center text-center">
                <?php if ($siteLogo && file_exists($siteLogo)): ?>
                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($siteLogo); ?>" 
                         alt="Logo" class="h-16 object-contain mb-2">
                <?php else: ?>
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-2">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                <?php endif; ?>
                <span class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($siteName); ?></span>
            </div>
        </div>
        
        <!-- Logo (mobile) -->
        <div class="p-4 border-b lg:hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <?php if ($siteLogo && file_exists($siteLogo)): ?>
                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($siteLogo); ?>" 
                             alt="Logo" class="h-8 object-contain">
                    <?php else: ?>
                        <i class="fas fa-building text-blue-600 text-xl"></i>
                    <?php endif; ?>
                    <span class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($siteName); ?></span>
                </div>
                <button onclick="toggleMobileMenu()" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4">
            <ul class="space-y-1">
                <!-- Dashboard -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-home w-5"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if (in_array($_SESSION['role'], ['superadmin', 'administrador', 'guardia'])): ?>
                <!-- Control de Accesos -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/access" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-door-open w-5"></i>
                        <span>Control de Accesos</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array($_SESSION['role'], ['superadmin', 'administrador'])): ?>
                <!-- Administración de Predios -->
                <li>
                    <button onclick="toggleSubmenu('residents-submenu')" class="sidebar-item flex items-center justify-between w-full px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-users w-5"></i>
                            <span>Residentes</span>
                        </div>
                        <i class="fas fa-chevron-down text-sm" id="residents-submenu-icon"></i>
                    </button>
                    <ul id="residents-submenu" class="ml-8 mt-1 space-y-1 hidden">
                        <li>
                            <a href="<?php echo BASE_URL; ?>/residents" class="sidebar-item flex items-center space-x-3 px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-list w-5"></i>
                                <span>Lista de Residentes</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/residents/pendingRegistrations" class="sidebar-item flex items-center space-x-3 px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-user-clock w-5"></i>
                                <span>Registros Pendientes</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/vehicles" class="sidebar-item flex items-center space-x-3 px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-car w-5"></i>
                                <span>Vehículos Registrados</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Módulo Financiero -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/financial" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-chart-line w-5"></i>
                        <span>Módulo Financiero</span>
                    </a>
                </li>
                
                <!-- Membresías -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/memberships" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-id-card w-5"></i>
                        <span>Membresías</span>
                    </a>
                </li>
                
                <!-- Pagos -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/residents/payments" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-credit-card w-5"></i>
                        <span>Pagos</span>
                    </a>
                </li>
                
                <!-- Reportes -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/reports" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span>Reportes</span>
                    </a>
                </li>
                
                <!-- Comunicados -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/announcements" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-bullhorn w-5"></i>
                        <span>Comunicados</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Amenidades -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/amenities" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-swimming-pool w-5"></i>
                        <span>Amenidades</span>
                    </a>
                </li>
                
                <!-- Mantenimiento -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/maintenance" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-tools w-5"></i>
                        <span>Mantenimiento</span>
                    </a>
                </li>
                
                <?php if (in_array($_SESSION['role'], ['superadmin', 'administrador', 'guardia'])): ?>
                <!-- Seguridad -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/security" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-shield-alt w-5"></i>
                        <span>Seguridad</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($_SESSION['role'] === 'guardia'): ?>
                <!-- Consola de Guardia -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/guard" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-clipboard-check w-5"></i>
                        <span>Consola Guardia</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array($_SESSION['role'], ['superadmin', 'administrador'])): ?>
                <!-- Divider -->
                <li class="pt-4 pb-2">
                    <div class="border-t"></div>
                </li>
                
                <!-- Dispositivos -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/devices" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-microchip w-5"></i>
                        <span>Dispositivos</span>
                    </a>
                </li>
                
                <!-- Configuración -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/settings" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-cog w-5"></i>
                        <span>Configuración</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($_SESSION['role'] === 'superadmin'): ?>
                <!-- Fraccionamientos -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/subdivisions" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-building w-5"></i>
                        <span>Fraccionamientos</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($_SESSION['role'] === 'superadmin'): ?>
                <!-- Admin Modules -->
                <li class="pt-4 pb-2">
                    <div class="border-t"></div>
                </li>
                
                <!-- Usuarios -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/users" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-users-cog w-5"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                
                <!-- Importar Datos -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/import" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-file-import w-5"></i>
                        <span>Importar Datos</span>
                    </a>
                </li>
                
                <!-- Auditoría -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/audit" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-clipboard-list w-5"></i>
                        <span>Auditoría</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <!-- User info at bottom -->
        <div class="p-4 border-t">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                    <?php echo strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        <?php echo $_SESSION['first_name'] ?? 'Usuario'; ?>
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        <?php echo ucfirst($_SESSION['role'] ?? ''); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</aside>
