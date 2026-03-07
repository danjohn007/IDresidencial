<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">⚙️ Reglas de Penalización</h1>
                    <p class="text-gray-600 mt-1">Configure las reglas de penalización por retraso en pagos</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/penaltyRules">

                        <!-- Día de Corte -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 border-b pb-2">
                                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Día de Corte del Mes
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Día de Corte</label>
                                    <select name="cut_day_type" id="cut_day_type" onchange="toggleCutDay()" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="first" <?php echo (!$rule || $rule['cut_day_type'] === 'first') ? 'selected' : ''; ?>>Primer día del mes</option>
                                        <option value="last" <?php echo ($rule && $rule['cut_day_type'] === 'last') ? 'selected' : ''; ?>>Último día del mes</option>
                                        <option value="custom" <?php echo ($rule && $rule['cut_day_type'] === 'custom') ? 'selected' : ''; ?>>Día específico (1-28)</option>
                                    </select>
                                </div>
                                <div id="custom_day_wrapper" class="<?php echo ($rule && $rule['cut_day_type'] === 'custom') ? '' : 'hidden'; ?>">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Día del mes (1-28)</label>
                                    <input type="number" name="cut_day" min="1" max="28"
                                           value="<?php echo $rule ? intval($rule['cut_day']) : 1; ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Días de Gracia -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 border-b pb-2">
                                <i class="fas fa-clock text-yellow-600 mr-2"></i>Días de Gracia
                            </h3>
                            <div class="max-w-xs">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Días de gracia (máximo 15)</label>
                                <input type="number" name="grace_days" min="0" max="15"
                                       value="<?php echo $rule ? intval($rule['grace_days']) : 0; ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Número de días después del corte sin penalización</p>
                            </div>
                        </div>

                        <!-- Penalización: Después del día de corte -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 border-b pb-2">
                                <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>Después del día de corte (+ días de gracia)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de cargo extra</label>
                                    <select name="after_cutday_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="percentage" <?php echo (!$rule || $rule['after_cutday_type'] === 'percentage') ? 'selected' : ''; ?>>Porcentaje (%)</option>
                                        <option value="amount" <?php echo ($rule && $rule['after_cutday_type'] === 'amount') ? 'selected' : ''; ?>>Cantidad fija ($)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor</label>
                                    <input type="number" name="after_cutday_value" step="0.01" min="0"
                                           value="<?php echo $rule ? $rule['after_cutday_value'] : 0; ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- Penalización: Al mes siguiente -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 border-b pb-2">
                                <i class="fas fa-calendar-times text-red-500 mr-2"></i>Al mes siguiente (moroso nivel 1)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de cargo extra</label>
                                    <select name="next_month_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="percentage" <?php echo (!$rule || $rule['next_month_type'] === 'percentage') ? 'selected' : ''; ?>>Porcentaje (%)</option>
                                        <option value="amount" <?php echo ($rule && $rule['next_month_type'] === 'amount') ? 'selected' : ''; ?>>Cantidad fija ($)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor</label>
                                    <input type="number" name="next_month_value" step="0.01" min="0"
                                           value="<?php echo $rule ? $rule['next_month_value'] : 0; ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- Penalización: Al segundo mes -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 border-b pb-2">
                                <i class="fas fa-ban text-red-700 mr-2"></i>Al segundo mes (moroso — retiro de servicios)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de cargo extra</label>
                                    <select name="second_month_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="percentage" <?php echo (!$rule || $rule['second_month_type'] === 'percentage') ? 'selected' : ''; ?>>Porcentaje (%)</option>
                                        <option value="amount" <?php echo ($rule && $rule['second_month_type'] === 'amount') ? 'selected' : ''; ?>>Cantidad fija ($)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor</label>
                                    <input type="number" name="second_month_value" step="0.01" min="0"
                                           value="<?php echo $rule ? $rule['second_month_value'] : 0; ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           placeholder="0.00">
                                </div>
                            </div>
                            <p class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded p-3 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Al cumplirse el segundo mes sin pago, el residente es marcado como moroso y se procede a retirar servicios.
                            </p>
                        </div>

                        <!-- Current Rule Summary -->
                        <?php if ($rule): ?>
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="font-semibold text-blue-800 mb-2">Configuración Actual</h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>
                                    <strong>Día de corte:</strong>
                                    <?php
                                    echo match($rule['cut_day_type']) {
                                        'first' => 'Primer día del mes',
                                        'last' => 'Último día del mes',
                                        default => 'Día ' . $rule['cut_day'] . ' de cada mes'
                                    };
                                    ?>
                                </li>
                                <li><strong>Días de gracia:</strong> <?php echo $rule['grace_days']; ?> días</li>
                                <li><strong>Cargo tras corte:</strong>
                                    <?php echo $rule['after_cutday_type'] === 'percentage' ? $rule['after_cutday_value'] . '%' : '$' . number_format($rule['after_cutday_value'], 2); ?>
                                </li>
                                <li><strong>Cargo al mes siguiente:</strong>
                                    <?php echo $rule['next_month_type'] === 'percentage' ? $rule['next_month_value'] . '%' : '$' . number_format($rule['next_month_value'], 2); ?>
                                </li>
                                <li><strong>Cargo al segundo mes (moroso):</strong>
                                    <?php echo $rule['second_month_type'] === 'percentage' ? $rule['second_month_value'] . '%' : '$' . number_format($rule['second_month_value'], 2); ?>
                                </li>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="<?php echo BASE_URL; ?>/residents/payments"
                               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i> Guardar Reglas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function toggleCutDay() {
    const type = document.getElementById('cut_day_type').value;
    const wrapper = document.getElementById('custom_day_wrapper');
    wrapper.classList.toggle('hidden', type !== 'custom');
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
