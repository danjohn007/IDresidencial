<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">📢 Comunicados</h1>
                    <p class="text-gray-600 mt-1">Avisos y notificaciones del residencial</p>
                </div>
                <?php if (in_array($_SESSION['role'], ['superadmin', 'administrador'])): ?>
                    <a href="<?php echo BASE_URL; ?>/announcements/create" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Nuevo Comunicado
                    </a>
                <?php endif; ?>
            </div>

            <div class="space-y-4">
                <?php if (empty($announcements)): ?>
                    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-6xl mb-4"></i>
                        <p class="text-lg">No hay comunicados disponibles</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($announcement['title']); ?></h2>
                                        <span class="px-3 py-1 text-xs rounded-full <?php 
                                            echo match($announcement['priority']) {
                                                'urgent' => 'bg-red-100 text-red-800',
                                                'high' => 'bg-orange-100 text-orange-800',
                                                'normal' => 'bg-blue-100 text-blue-800',
                                                'low' => 'bg-gray-100 text-gray-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?php echo ucfirst($announcement['priority']); ?>
                                        </span>
                                    </div>
                                    <p class="text-gray-600 mb-3">
                                        <?php echo nl2br(htmlspecialchars(substr($announcement['content'], 0, 300))); ?>
                                        <?php if (strlen($announcement['content']) > 300) echo '...'; ?>
                                    </p>
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <span><i class="fas fa-user mr-1"></i> <?php echo $announcement['first_name'] . ' ' . $announcement['last_name']; ?></span>
                                        <span><i class="fas fa-calendar mr-1"></i> <?php echo date('d/m/Y H:i', strtotime($announcement['created_at'])); ?></span>
                                    </div>
                                </div>
                                <a href="<?php echo BASE_URL; ?>/announcements/viewDetails/<?php echo $announcement['id']; ?>" 
                                   class="ml-4 px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                                    Ver más
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
