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
        
        // Toggle submenu
        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const icon = document.getElementById(submenuId + '-icon');
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                submenu.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-auto-hide');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        
        // Global Search functionality
        const globalSearch = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;
        
        if (globalSearch) {
            globalSearch.addEventListener('input', function(e) {
                const query = e.target.value.trim();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    return;
                }
                
                // Debounce search
                searchTimeout = setTimeout(function() {
                    performSearch(query);
                }, 300);
            });
            
            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!globalSearch.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }
        
        function performSearch(query) {
            fetch('<?php echo BASE_URL; ?>/api/search?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    displaySearchResults(data);
                })
                .catch(error => {
                    console.error('Search error:', error);
                    searchResults.innerHTML = '<div class="p-4 text-red-600">Error en la búsqueda</div>';
                    searchResults.classList.remove('hidden');
                });
        }
        
        function displaySearchResults(data) {
            if (!data || (data.residents.length === 0 && data.users.length === 0)) {
                searchResults.innerHTML = '<div class="p-4 text-gray-500 text-center">No se encontraron resultados</div>';
                searchResults.classList.remove('hidden');
                return;
            }
            
            let html = '<div class="py-2">';
            
            // Residents section
            if (data.residents && data.residents.length > 0) {
                html += '<div class="px-4 py-2 bg-gray-50 font-semibold text-xs text-gray-600 uppercase">Residentes</div>';
                data.residents.forEach(resident => {
                    html += `
                        <a href="<?php echo BASE_URL; ?>/residents/viewDetails/${resident.id}" 
                           class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-semibold mr-3">
                                    ${resident.first_name.charAt(0)}${resident.last_name.charAt(0)}
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">${resident.first_name} ${resident.last_name}</p>
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-envelope mr-1"></i> ${resident.email}
                                        ${resident.phone ? ` <i class="fas fa-phone ml-2 mr-1"></i> ${resident.phone}` : ''}
                                    </p>
                                    ${resident.property_number ? `<p class="text-xs text-gray-500 mt-1"><i class="fas fa-home mr-1"></i> ${resident.property_number}</p>` : ''}
                                </div>
                            </div>
                        </a>
                    `;
                });
            }
            
            // Users section (non-residents)
            if (data.users && data.users.length > 0) {
                html += '<div class="px-4 py-2 bg-gray-50 font-semibold text-xs text-gray-600 uppercase">Usuarios</div>';
                data.users.forEach(user => {
                    html += `
                        <a href="<?php echo BASE_URL; ?>/users/view/${user.id}" 
                           class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 font-semibold mr-3">
                                    ${user.first_name.charAt(0)}${user.last_name.charAt(0)}
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">${user.first_name} ${user.last_name}</p>
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-envelope mr-1"></i> ${user.email}
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100">${user.role}</span>
                                    </p>
                                </div>
                            </div>
                        </a>
                    `;
                });
            }
            
            html += '</div>';
            
            searchResults.innerHTML = html;
            searchResults.classList.remove('hidden');
        }
    </script>
</body>
</html>
