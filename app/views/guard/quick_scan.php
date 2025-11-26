<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">📷 Escaneo Rápido QR</h1>
                        <p class="text-gray-600 mt-1">Escanea el código QR del pase de visita</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/guard" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>

            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <!-- Scanner Container -->
                    <div id="scanner-container" class="mb-4">
                        <div id="reader" class="border-4 border-blue-500 rounded-lg overflow-hidden"></div>
                    </div>

                    <!-- Manual Input Alternative -->
                    <div class="text-center mb-4">
                        <p class="text-gray-600 mb-2">¿No puedes escanear? Ingresa el código manualmente:</p>
                        <div class="flex gap-2">
                            <input type="text" id="manualCode" placeholder="VIS-20241126-XXXXXXXX" 
                                   class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <button onclick="validateManualCode()" 
                                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                Validar
                            </button>
                        </div>
                    </div>

                    <!-- Result Display -->
                    <div id="result" class="mt-4"></div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Include html5-qrcode library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
let html5QrcodeScanner;

// Initialize QR Scanner
document.addEventListener('DOMContentLoaded', function() {
    html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { 
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            facingMode: "environment" // Usar cámara trasera
        },
        false
    );
    
    html5QrcodeScanner.render(onScanSuccess, onScanError);
});

function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning
    html5QrcodeScanner.clear();
    
    // Validate the QR code
    validateQRCode(decodedText);
}

function onScanError(error) {
    // Silent error handling
}

function validateQRCode(qrCode) {
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin text-3xl text-blue-600"></i><p class="mt-2">Validando...</p></div>';
    
    fetch('<?php echo BASE_URL; ?>/access/validateQR', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'qr_code=' + encodeURIComponent(qrCode)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = `
                <div class="bg-green-50 border-2 border-green-500 rounded-lg p-6">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-6xl text-green-500"></i>
                        <h3 class="text-2xl font-bold text-green-700 mt-2">¡Acceso Autorizado!</h3>
                    </div>
                    <div class="text-left space-y-2">
                        <p><strong>Visitante:</strong> ${data.visit.visitor_name}</p>
                        <p><strong>Propiedad:</strong> ${data.visit.property_number}</p>
                        <p><strong>Residente:</strong> ${data.visit.resident_name}</p>
                        <p><strong>Válido:</strong> ${data.visit.valid_from} - ${data.visit.valid_until}</p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button onclick="registerEntry('${data.visit.id}')" 
                                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                            Registrar Entrada
                        </button>
                        <button onclick="location.reload()" 
                                class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                            Escanear Otro
                        </button>
                    </div>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border-2 border-red-500 rounded-lg p-6">
                    <div class="text-center mb-4">
                        <i class="fas fa-times-circle text-6xl text-red-500"></i>
                        <h3 class="text-2xl font-bold text-red-700 mt-2">Acceso Denegado</h3>
                    </div>
                    <p class="text-red-600 text-center mb-4">${data.message}</p>
                    <button onclick="location.reload()" 
                            class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        Escanear Otro
                    </button>
                </div>
            `;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-500 rounded-lg p-4">
                <p class="text-red-600">Error al validar: ${error.message}</p>
                <button onclick="location.reload()" class="mt-2 bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Intentar de nuevo
                </button>
            </div>
        `;
    });
}

function validateManualCode() {
    const code = document.getElementById('manualCode').value.trim();
    if (code) {
        validateQRCode(code);
    } else {
        alert('Por favor ingresa un código válido');
    }
}

function registerEntry(visitId) {
    fetch('<?php echo BASE_URL; ?>/access/registerAccess', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `visit_id=${visitId}&access_type=entry`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Entrada registrada exitosamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    });
}
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
