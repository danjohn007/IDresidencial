<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-book mr-2 text-blue-600"></i><?php echo htmlspecialchars($title); ?>
                    </h1>
                    <p class="text-gray-600 mt-1">
                        <?php echo $item ? 'Modifica los datos de la incidencia fija.' : 'Define una nueva incidencia de mantenimiento recurrente.'; ?>
                    </p>
                </div>

                <!-- Error -->
                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow p-6">
                    <?php
                        $action = $item
                            ? BASE_URL . '/maintenance/catalogEdit/' . $item['id']
                            : BASE_URL . '/maintenance/catalogCreate';
                        $v = fn($key, $default = '') => htmlspecialchars($item[$key] ?? $default);
                    ?>
                    <form method="POST" action="<?php echo $action; ?>">
                        <div class="space-y-5">

                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Título <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" required maxlength="200"
                                       value="<?php echo $v('title'); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Cambio de extintores">
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Descripción
                                </label>
                                <textarea name="description" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Describe el tipo de mantenimiento a realizar..."><?php echo $v('description'); ?></textarea>
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Categoría <span class="text-red-500">*</span>
                                </label>
                                <select name="category" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Seleccionar...</option>
                                    <?php
                                    $cats = [
                                        'alumbrado'    => 'Alumbrado',
                                        'jardineria'   => 'Jardinería',
                                        'plomeria'     => 'Plomería',
                                        'electricidad' => 'Electricidad',
                                        'seguridad'    => 'Seguridad',
                                        'limpieza'     => 'Limpieza',
                                        'pintura'      => 'Pintura',
                                        'otro'         => 'Otro',
                                    ];
                                    foreach ($cats as $val => $label):
                                        $sel = isset($item['category']) && $item['category'] === $val ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $val; ?>" <?php echo $sel; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Location -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Ubicación
                                </label>
                                <input type="text" name="location" maxlength="200"
                                       value="<?php echo $v('location'); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Reja de entrada, Cisterna, etc.">
                            </div>

                            <!-- Priority -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Prioridad <span class="text-red-500">*</span>
                                </label>
                                <select name="priority" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <?php
                                    $priorities = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta', 'urgente' => 'Urgente'];
                                    $curPriority = $item['priority'] ?? 'media';
                                    foreach ($priorities as $val => $label):
                                    ?>
                                        <option value="<?php echo $val; ?>" <?php echo $curPriority === $val ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Interval -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Frecuencia de mantenimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-3">
                                    <input type="number" name="interval_value" required min="1" max="999"
                                           value="<?php echo $v('interval_value', '1'); ?>"
                                           class="w-28 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="6">
                                    <select name="interval_unit" required
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <?php
                                        $units   = ['dias' => 'Días', 'meses' => 'Meses', 'anios' => 'Años'];
                                        $curUnit = $item['interval_unit'] ?? 'meses';
                                        foreach ($units as $val => $label):
                                        ?>
                                            <option value="<?php echo $val; ?>" <?php echo $curUnit === $val ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Indica cada cuánto tiempo se debe generar un reporte automáticamente.
                                    Ejemplos: 6 meses (extintores), 1 año (reja), 18 meses (cisternas).
                                </p>
                            </div>

                            <!-- Active toggle (only on edit) -->
                            <?php if ($item): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="active"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1" <?php echo ($item['active'] ?? 1) == 1 ? 'selected' : ''; ?>>Activa</option>
                                    <option value="0" <?php echo ($item['active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactiva</option>
                                </select>
                            </div>
                            <?php endif; ?>

                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="<?php echo BASE_URL; ?>/maintenance/catalog"
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>
                                <?php echo $item ? 'Actualizar' : 'Guardar'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
