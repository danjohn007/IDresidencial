<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="flex h-screen overflow-hidden">
    <?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-hidden">
        <?php require_once APP_PATH . '/views/layouts/navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            <div class="max-w-2xl mx-auto">
                <div class="mb-6 text-center">
                    <h1 class="text-3xl font-bold text-gray-900">Validar Código QR</h1>
                    <p class="text-gray-600 mt-1">Escanea o ingresa el código QR del visitante</p>
                </div>

                <!-- Scanner Options -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex gap-2 mb-4">
                        <button onclick="showScanner()" id="btnScanner"
                                class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fas fa-camera mr-2"></i>
                            Escanear QR
                        </button>
                        <button onclick="showManual()" id="btnManual"
                                class="flex-1 bg-gray-600 text-white py-3 rounded-lg font-semibold hover:bg-gray-700 transition">
                            <i class="fas fa-keyboard mr-2"></i>
                            Ingresar Manual
                        </button>
                    </div>

                    <!-- QR Scanner Container -->
                    <div id="scannerContainer" class="hidden">
                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                Apunta la cámara hacia el código QR del visitante
                            </p>
                        </div>
                        <div id="qr-reader" class="rounded-lg overflow-hidden border-2 border-gray-300"></div>
                        <div class="mt-4 text-center">
                            <button onclick="stopScanner()" 
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                <i class="fas fa-stop mr-2"></i>
                                Detener Cámara
                            </button>
                        </div>
                    </div>

                    <!-- Manual Input Form -->
                    <div id="manualContainer">
                        <form method="POST" action="<?php echo BASE_URL; ?>/access/validate" id="validateForm">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Código QR
                                </label>
                                <input type="text" name="qr_code" id="qr_code" autofocus required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                       placeholder="VIS-20241121-ABCD1234">
                            </div>
                            
                            <button type="submit" 
                                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                                <i class="fas fa-search mr-2"></i>
                                Validar Código
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Validation Result -->
                <?php if (isset($valid)): ?>
                    <?php if ($valid && $visit): ?>
                        <div class="bg-green-50 border-2 border-green-500 rounded-lg p-6">
                            <div class="text-center mb-4">
                                <i class="fas fa-check-circle text-green-600 text-6xl mb-2"></i>
                                <h2 class="text-2xl font-bold text-green-800">✅ Código Válido</h2>
                            </div>
                            
                            <div class="bg-white rounded-lg p-6 space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600">Visitante</p>
                                    <p class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($visit['visitor_name']); ?></p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Residente</p>
                                        <p class="font-semibold text-gray-900"><?php echo $visit['first_name'] . ' ' . $visit['last_name']; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Propiedad</p>
                                        <p class="font-semibold text-gray-900"><?php echo $visit['property_number']; ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($visit['vehicle_plate']): ?>
                                    <div>
                                        <p class="text-sm text-gray-600">Vehículo</p>
                                        <p class="font-semibold text-gray-900"><?php echo $visit['vehicle_plate']; ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div>
                                    <p class="text-sm text-gray-600">Vigencia</p>
                                    <p class="font-semibold text-gray-900">
                                        <?php echo date('d/m/Y H:i', strtotime($visit['valid_from'])); ?> - 
                                        <?php echo date('d/m/Y H:i', strtotime($visit['valid_until'])); ?>
                                    </p>
                                </div>
                                
                                <?php if ($visit['status'] === 'pending'): ?>
                                    <!-- Identification Photo Section -->
                                    <div class="pt-4 border-t">
                                        <div id="photoSection">
                                            <button type="button" onclick="startPhotoCapture()" id="btnCapturePhoto"
                                                    class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition mb-3">
                                                <i class="fas fa-camera mr-2"></i>
                                                Tomar Foto de Identificación
                                            </button>
                                            
                                            <!-- Photo Capture Container -->
                                            <div id="photoCaptureContainer" class="hidden mb-4">
                                                <div class="bg-gray-100 rounded-lg p-4">
                                                    <video id="photoVideo" autoplay class="w-full rounded-lg border-2 border-gray-300 mb-3"></video>
                                                    <div class="flex gap-2">
                                                        <button type="button" onclick="capturePhoto()" 
                                                                class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                                                            <i class="fas fa-camera mr-2"></i>
                                                            Capturar
                                                        </button>
                                                        <button type="button" onclick="cancelPhotoCapture()" 
                                                                class="flex-1 bg-gray-600 text-white py-2 rounded-lg hover:bg-gray-700 transition">
                                                            <i class="fas fa-times mr-2"></i>
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Photo Preview -->
                                            <div id="photoPreview" class="hidden mb-4">
                                                <div class="bg-gray-100 rounded-lg p-4">
                                                    <p class="text-sm font-semibold text-gray-700 mb-2">
                                                        <i class="fas fa-check-circle text-green-600 mr-1"></i>
                                                        Foto de Identificación Capturada
                                                    </p>
                                                    <img id="capturedPhoto" class="w-full rounded-lg border-2 border-green-500 mb-3">
                                                    <button type="button" onclick="retakePhoto()" 
                                                            class="w-full bg-orange-600 text-white py-2 rounded-lg hover:bg-orange-700 transition">
                                                        <i class="fas fa-redo mr-2"></i>
                                                        Tomar Otra Foto
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <a href="<?php echo BASE_URL; ?>/access/registerEntry/<?php echo $visit['id']; ?>" 
                                           id="btnRegisterEntry"
                                           class="block w-full bg-green-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-green-700 transition opacity-50 pointer-events-none"
                                           title="Primero debes tomar la foto de identificación">
                                            <i class="fas fa-sign-in-alt mr-2"></i>
                                            Registrar Entrada
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-red-50 border-2 border-red-500 rounded-lg p-6">
                            <div class="text-center">
                                <i class="fas fa-times-circle text-red-600 text-6xl mb-2"></i>
                                <h2 class="text-2xl font-bold text-red-800 mb-2">❌ Código Inválido</h2>
                                <p class="text-red-700"><?php echo $error; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- HTML5 QR Code Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    let html5QrCode;
    let isScanning = false;

    // Show scanner view
    function showScanner() {
        document.getElementById('scannerContainer').classList.remove('hidden');
        document.getElementById('manualContainer').classList.add('hidden');
        document.getElementById('btnScanner').classList.add('bg-blue-600');
        document.getElementById('btnScanner').classList.remove('bg-gray-600');
        document.getElementById('btnManual').classList.remove('bg-blue-600');
        document.getElementById('btnManual').classList.add('bg-gray-600');
        startScanner();
    }

    // Show manual input view
    function showManual() {
        stopScanner();
        document.getElementById('scannerContainer').classList.add('hidden');
        document.getElementById('manualContainer').classList.remove('hidden');
        document.getElementById('btnManual').classList.add('bg-blue-600');
        document.getElementById('btnManual').classList.remove('bg-gray-600');
        document.getElementById('btnScanner').classList.remove('bg-blue-600');
        document.getElementById('btnScanner').classList.add('bg-gray-600');
        document.getElementById('qr_code').focus();
    }

    // Start QR scanner
    function startScanner() {
        if (isScanning) return;

        html5QrCode = new Html5Qrcode("qr-reader");
        
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        html5QrCode.start(
            { facingMode: "environment" }, // Use back camera on mobile
            config,
            onScanSuccess,
            onScanError
        ).then(() => {
            isScanning = true;
            console.log("QR Scanner started successfully");
        }).catch(err => {
            console.error("Error starting scanner:", err);
            alert("No se pudo acceder a la cámara. Por favor, verifica los permisos o usa la entrada manual.");
            showManual();
        });
    }

    // Stop QR scanner
    function stopScanner() {
        if (html5QrCode && isScanning) {
            html5QrCode.stop().then(() => {
                isScanning = false;
                console.log("QR Scanner stopped");
            }).catch(err => {
                console.error("Error stopping scanner:", err);
            });
        }
    }

    // Handle successful QR scan
    function onScanSuccess(decodedText, decodedResult) {
        console.log("QR Code detected:", decodedText);
        
        // Validate QR format (VIS-YYYYMMDD-XXXXXXXX)
        if (decodedText.match(/^VIS-\d{8}-[A-Z0-9]{8}$/)) {
            // Fill the form and submit
            document.getElementById('qr_code').value = decodedText;
            stopScanner();
            
            // Show success feedback
            const scannerContainer = document.getElementById('qr-reader');
            scannerContainer.style.border = '4px solid #10B981';
            
            // Submit the form after brief delay
            setTimeout(() => {
                document.getElementById('validateForm').submit();
            }, 500);
        } else {
            // Invalid format - show warning but don't submit
            console.warn("Invalid QR format:", decodedText);
            alert("Código QR no válido. Formato esperado: VIS-YYYYMMDD-XXXXXXXX");
        }
    }

    // Handle scan errors (silent - too many false positives)
    function onScanError(errorMessage) {
        // Ignore - continuous scanning produces many errors
    }

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        stopScanner();
        stopPhotoStream();
    });

    // Auto-show scanner on mobile devices
    if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        showScanner();
    }

    // ==================== PHOTO CAPTURE FUNCTIONALITY ====================
    let photoStream = null;
    let capturedPhotoData = null;
    const visitId = <?php echo isset($visit['id']) ? $visit['id'] : 'null'; ?>;

    function startPhotoCapture() {
        document.getElementById('photoCaptureContainer').classList.remove('hidden');
        document.getElementById('btnCapturePhoto').classList.add('hidden');
        
        // Request camera access - BACK CAMERA for ID documents
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: { exact: "environment" }, // Back camera for ID photo
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            } 
        })
        .then(stream => {
            photoStream = stream;
            document.getElementById('photoVideo').srcObject = stream;
        })
        .catch(err => {
            console.error("Error accessing back camera:", err);
            // Fallback to any available camera
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: "environment",
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                } 
            })
            .then(stream => {
                photoStream = stream;
                document.getElementById('photoVideo').srcObject = stream;
            })
            .catch(err2 => {
                console.error("Error accessing camera:", err2);
                alert("No se pudo acceder a la cámara. Verifica los permisos.");
                cancelPhotoCapture();
            });
        });
    }

    function capturePhoto() {
        const video = document.getElementById('photoVideo');
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        // Get base64 image data
        capturedPhotoData = canvas.toDataURL('image/png');
        
        // Show preview
        document.getElementById('capturedPhoto').src = capturedPhotoData;
        document.getElementById('photoPreview').classList.remove('hidden');
        document.getElementById('photoCaptureContainer').classList.add('hidden');
        
        // Stop camera stream
        stopPhotoStream();
        
        // Save photo to server
        savePhotoToServer();
    }

    function savePhotoToServer() {
        if (!visitId || !capturedPhotoData) {
            alert("Error: Datos incompletos");
            return;
        }

        // Show loading state
        const btnRegister = document.getElementById('btnRegisterEntry');
        btnRegister.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando foto...';
        
        fetch('<?php echo BASE_URL; ?>/access/saveIdentificationPhoto', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'visit_id=' + visitId + '&photo_data=' + encodeURIComponent(capturedPhotoData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Photo saved successfully:", data.photo_url);
                
                // Enable register entry button
                btnRegister.classList.remove('opacity-50', 'pointer-events-none');
                btnRegister.removeAttribute('title');
                btnRegister.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i> Registrar Entrada';
                
                // Show success message
                alert("✅ Foto guardada exitosamente");
            } else {
                alert("❌ Error al guardar foto: " + (data.error || "Error desconocido"));
                retakePhoto();
            }
        })
        .catch(error => {
            console.error("Error saving photo:", error);
            alert("❌ Error de conexión al guardar la foto");
            retakePhoto();
        });
    }

    function retakePhoto() {
        document.getElementById('photoPreview').classList.add('hidden');
        document.getElementById('btnCapturePhoto').classList.remove('hidden');
        capturedPhotoData = null;
        
        // Disable register button again
        const btnRegister = document.getElementById('btnRegisterEntry');
        btnRegister.classList.add('opacity-50', 'pointer-events-none');
        btnRegister.setAttribute('title', 'Primero debes tomar la foto de identificación');
    }

    function cancelPhotoCapture() {
        stopPhotoStream();
        document.getElementById('photoCaptureContainer').classList.add('hidden');
        document.getElementById('btnCapturePhoto').classList.remove('hidden');
    }

    function stopPhotoStream() {
        if (photoStream) {
            photoStream.getTracks().forEach(track => track.stop());
            photoStream = null;
        }
    }
</script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
