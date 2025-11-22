<!-- Sidebar -->
<aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-full flex flex-col">
        <!-- Logo (mobile) -->
        <div class="p-4 border-b lg:hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                    <span class="text-lg font-bold text-gray-800"><?php echo SITE_NAME; ?></span>
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
                    <a href="<?php echo BASE_URL; ?>/residents" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-users w-5"></i>
                        <span>Residentes</span>
                    </a>
                </li>
                
                <!-- Pagos -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/payments" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-credit-card w-5"></i>
                        <span>Pagos</span>
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
                
                <!-- Configuración -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/settings" class="sidebar-item flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-cog w-5"></i>
                        <span>Configuración</span>
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
