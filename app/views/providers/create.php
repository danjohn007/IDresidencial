<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-plus-circle mr-2 text-yellow-600"></i>Nuevo Proveedor</h1>
                </div>
                <a href="<?php echo BASE_URL; ?>/providers" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>

            <?php if (!empty($error)): ?>
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <i class="fas fa-exclamation-circle mr-1"></i><?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="<?php echo BASE_URL; ?>/providers/create" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Empresa <span class="text-red-500">*</span></label>
                            <input type="text" name="company_name" required
                                   value="<?php echo htmlspecialchars($provider['company_name'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Contacto</label>
                            <input type="text" name="contact_name"
                                   value="<?php echo htmlspecialchars($provider['contact_name'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="phone" id="phone"
                                   value="<?php echo htmlspecialchars($provider['phone'] ?? ''); ?>"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   placeholder="10 dígitos"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                            <p class="text-xs text-gray-500 mt-1">Debe contener exactamente 10 dígitos</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                            <input type="email" name="email"
                                   value="<?php echo htmlspecialchars($provider['email'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría / Especialidad</label>
                            <input type="text" name="category"
                                   list="categorySuggestions"
                                   value="<?php echo htmlspecialchars($provider['category'] ?? ''); ?>"
                                   placeholder="Ej: Plomería, Electricidad, Jardinería..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                            <datalist id="categorySuggestions">
                                <option value="Plomería">
                                <option value="Electricidad">
                                <option value="Jardinería">
                                <option value="Limpieza">
                                <option value="Pintura">
                                <option value="Carpintería">
                                <option value="Albañilería">
                                <option value="Herrería">
                                <option value="HVAC">
                                <option value="Elevadores">
                                <option value="Seguridad">
                                <option value="Vigilancia">
                                <option value="General">
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RFC</label>
                            <input type="text" name="rfc" id="rfc"
                                   value="<?php echo htmlspecialchars($provider['rfc'] ?? ''); ?>"
                                   placeholder="12 o 13 caracteres"
                                   maxlength="13"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 uppercase">
                            <p class="text-xs text-gray-500 mt-1">Persona Moral: 12 caracteres | Persona Física: 13 caracteres</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" name="address"
                               value="<?php echo htmlspecialchars($provider['address'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500"
                                  ><?php echo htmlspecialchars($provider['notes'] ?? ''); ?></textarea>
                    </div>
                    <div class="flex space-x-3">
                        <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium">
                            <i class="fas fa-save mr-2"></i>Guardar Proveedor
                        </button>
                        <a href="<?php echo BASE_URL; ?>/providers" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
// Validación de teléfono: solo números, exactamente 10 dígitos
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        // Remove non-numeric characters
        this.value = this.value.replace(/\D/g, '');
        
        // Validate length
        if (this.value.length > 0 && this.value.length !== 10) {
            this.setCustomValidity('El teléfono debe tener exactamente 10 dígitos');
            this.classList.add('border-red-500');
            this.classList.remove('border-gray-300');
        } else {
            this.setCustomValidity('');
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-300');
        }
    });
}

// Validación de RFC: 12 o 13 caracteres alfanuméricos
const rfcInput = document.getElementById('rfc');
if (rfcInput) {
    rfcInput.addEventListener('input', function(e) {
        // Convert to uppercase
        this.value = this.value.toUpperCase();
        
        // Remove invalid characters (only allow alphanumeric)
        this.value = this.value.replace(/[^A-Z0-9]/g, '');
        
        // Validate length and format
        const length = this.value.length;
        if (length > 0 && length !== 12 && length !== 13) {
            this.setCustomValidity('El RFC debe tener 12 caracteres (Persona Moral) o 13 caracteres (Persona Física)');
            this.classList.add('border-red-500');
            this.classList.remove('border-gray-300');
        } else if (length === 12 || length === 13) {
            // Basic RFC pattern validation
            const rfcPattern = length === 12 
                ? /^[A-Z&]{3}[0-9]{6}[A-Z0-9]{3}$/ // Persona Moral
                : /^[A-Z&]{4}[0-9]{6}[A-Z0-9]{3}$/; // Persona Física
            
            if (!rfcPattern.test(this.value)) {
                this.setCustomValidity('Formato de RFC inválido');
                this.classList.add('border-red-500');
                this.classList.remove('border-gray-300');
            } else {
                this.setCustomValidity('');
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-300');
            }
        } else {
            this.setCustomValidity('');
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-300');
        }
    });
}

// Validación al enviar formulario
document.querySelector('form').addEventListener('submit', function(e) {
    let isValid = true;
    let errors = [];
    
    // Validate phone if filled
    const phone = phoneInput.value.trim();
    if (phone && phone.length !== 10) {
        errors.push('El teléfono debe tener exactamente 10 dígitos');
        phoneInput.classList.add('border-red-500');
        isValid = false;
    }
    
    // Validate RFC if filled
    const rfc = rfcInput.value.trim();
    if (rfc && (rfc.length !== 12 && rfc.length !== 13)) {
        errors.push('El RFC debe tener 12 o 13 caracteres');
        rfcInput.classList.add('border-red-500');
        isValid = false;
    } else if (rfc) {
        const rfcPattern = rfc.length === 12 
            ? /^[A-Z&]{3}[0-9]{6}[A-Z0-9]{3}$/ 
            : /^[A-Z&]{4}[0-9]{6}[A-Z0-9]{3}$/;
        
        if (!rfcPattern.test(rfc)) {
            errors.push('Formato de RFC inválido');
            rfcInput.classList.add('border-red-500');
            isValid = false;
        }
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('Por favor corrige los siguientes errores:\\n\\n' + errors.join('\\n'));
        return false;
    }
});
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
