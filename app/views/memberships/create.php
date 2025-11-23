<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">🎫 Nueva Membresía</h1>
                    <p class="text-gray-600 mt-1">Asignar membresía a un residente</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <form method="POST" action="<?php echo BASE_URL; ?>/memberships/create">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Residente <span class="text-red-500">*</span>
                                </label>
                                <select name="resident_id" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Seleccionar residente...</option>
                                    <?php foreach ($residents as $resident): ?>
                                        <option value="<?php echo $resident['id']; ?>">
                                            <?php echo htmlspecialchars($resident['name']); ?> 
                                            - <?php echo htmlspecialchars($resident['property_number']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Plan de Membresía <span class="text-red-500">*</span>
                                </label>
                                <select name="membership_plan_id" id="membership_plan_id" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Seleccionar plan...</option>
                                    <?php foreach ($plans as $plan): ?>
                                        <option value="<?php echo $plan['id']; ?>" 
                                                data-cost="<?php echo $plan['monthly_cost']; ?>"
                                                data-benefits="<?php echo htmlspecialchars($plan['benefits']); ?>">
                                            <?php echo htmlspecialchars($plan['name']); ?> 
                                            - $<?php echo number_format($plan['monthly_cost'], 2); ?>/mes
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="plan-details" class="md:col-span-2 hidden p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="font-semibold text-blue-900 mb-2">Detalles del Plan:</h4>
                                <div id="plan-benefits"></div>
                                <p class="mt-2 text-sm text-blue-700">
                                    <strong>Costo mensual:</strong> $<span id="plan-cost">0.00</span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha de Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha de Fin
                                </label>
                                <input type="date" name="end_date" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <p class="text-xs text-gray-500 mt-1">Dejar vacío para membresía indefinida</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Día de Pago <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_day" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <?php for ($i = 1; $i <= 28; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $i === 1 ? 'selected' : ''; ?>>
                                            Día <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado <span class="text-red-500">*</span>
                                </label>
                                <select name="status" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="active">Activo</option>
                                    <option value="suspended">Suspendido</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Notas Adicionales
                                </label>
                                <textarea name="notes" rows="3" 
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <a href="<?php echo BASE_URL; ?>/memberships" 
                               class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Mostrar detalles del plan seleccionado
document.getElementById('membership_plan_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const planDetails = document.getElementById('plan-details');
    
    if (this.value) {
        const cost = selectedOption.dataset.cost;
        const benefits = selectedOption.dataset.benefits;
        
        document.getElementById('plan-cost').textContent = parseFloat(cost).toFixed(2);
        
        // Mostrar beneficios
        const benefitsDiv = document.getElementById('plan-benefits');
        try {
            const benefitsList = JSON.parse(benefits);
            benefitsDiv.innerHTML = '<ul class="list-disc list-inside text-sm text-blue-700">' + 
                benefitsList.map(b => '<li>' + b + '</li>').join('') + 
                '</ul>';
        } catch (e) {
            benefitsDiv.innerHTML = '<p class="text-sm text-blue-700">' + benefits + '</p>';
        }
        
        planDetails.classList.remove('hidden');
    } else {
        planDetails.classList.add('hidden');
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
