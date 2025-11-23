    <!-- Footer -->
    <footer class="bg-white border-t mt-auto py-4">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <?php
            // Get copyright text from database
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'site_copyright'");
            $copyrightRow = $stmt->fetch();
            $copyright = $copyrightRow ? $copyrightRow['setting_value'] : ('© ' . date('Y') . ' ' . SITE_NAME . '. Todos los derechos reservados.');
            ?>
            <p><?php echo htmlspecialchars($copyright); ?></p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Toggle mobile menu
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuButton = document.getElementById('menu-button');
            
            if (sidebar && menuButton && !sidebar.contains(event.target) && !menuButton.contains(event.target)) {
                if (!sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-auto-hide');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
