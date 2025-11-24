<?php
/**
 * Script de prueba para verificar configuración
 * Ejecutar: php test_plate_processing.php
 */

echo "========== TEST DE CONFIGURACIÓN ==========\n\n";

// Test 1: Verificar PHP
echo "✓ PHP Version: " . phpversion() . "\n";

// Test 2: Verificar directorios
$ftpDir = '/home2/janetzy/placas/IP CAMERA/01';
$publicDir = '/home2/janetzy/public_html/placas';
$logDir = '/home2/janetzy/public_html/logs';

echo "\nVerificando directorios:\n";
echo is_dir($ftpDir) ? "✓" : "✗";
echo " FTP Source: $ftpDir\n";

echo is_dir($publicDir) ? "✓" : "✗";
echo " Public Dest: $publicDir\n";

echo is_dir($logDir) ? "✓" : "✗";
echo " Logs: $logDir\n";

// Test 3: Verificar permisos de escritura
echo "\nVerificando permisos:\n";
echo is_writable($publicDir) ? "✓" : "✗";
echo " Escritura en /placas\n";

echo is_writable($logDir) ? "✓" : "✗";
echo " Escritura en /logs\n";

// Test 4: Verificar conexión a BD
echo "\nVerificando conexión a BD:\n";
try {
    // CAMBIA ESTOS VALORES
    $db = new PDO(
        "mysql:host=localhost;dbname=janetzy_residencial;charset=utf8mb4",
        "janetzy_admin", // TU USUARIO
        "tu_password_aqui", // TU CONTRASEÑA
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Conexión exitosa\n";
    
    // Verificar tablas
    $tables = ['detected_plates', 'vehicles', 'access_logs', 'residents', 'users'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        echo $stmt->rowCount() > 0 ? "✓" : "✗";
        echo " Tabla: $table\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error de BD: " . $e->getMessage() . "\n";
}

// Test 5: Buscar imágenes de prueba
echo "\nBuscando imágenes en FTP:\n";
$images = glob($ftpDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
echo "Encontradas: " . count($images) . " imágenes\n";

if (count($images) > 0) {
    echo "Primeras 5 imágenes:\n";
    foreach (array_slice($images, 0, 5) as $img) {
        echo "  - " . basename($img) . "\n";
    }
}

// Test 6: Probar extracción de placa
echo "\nProbando extracción de placas:\n";
$testNames = [
    'ABC123_20251124_143015.jpg',
    'ABC-123-D_20251124143015.jpg',
    'Snapshot_1_20251124143015_ABC123.jpg',
    'XYZ789_2025112414.jpg'
];

foreach ($testNames as $name) {
    $info = testExtractPlate($name);
    echo $info ? "✓" : "✗";
    echo " $name → " . ($info ? $info['plate'] : 'NO DETECTADO') . "\n";
}

echo "\n========== FIN DEL TEST ==========\n";
echo "\nSi todos los tests pasaron (✓), ejecuta:\n";
echo "php /home2/janetzy/public_html/cron/process_plate_images.php\n";

function testExtractPlate($fileName) {
    $name = pathinfo($fileName, PATHINFO_FILENAME);
    
    if (preg_match('/^([A-Z0-9\-]+)_(\d{8})_(\d{6})/', $name, $matches)) {
        return [
            'plate' => $matches[1],
            'captured_at' => date('Y-m-d H:i:s', strtotime($matches[2] . $matches[3]))
        ];
    }
    
    if (preg_match('/Snapshot_\d+_(\d{8})(\d{6})_([A-Z0-9\-]+)/', $name, $matches)) {
        return [
            'plate' => $matches[3],
            'captured_at' => date('Y-m-d H:i:s', strtotime($matches[1] . $matches[2]))
        ];
    }
    
    if (preg_match('/([A-Z]{3}\-?\d{3}\-?[A-Z0-9]?)/', $name, $matches)) {
        return [
            'plate' => $matches[1],
            'captured_at' => date('Y-m-d H:i:s')
        ];
    }
    
    return null;
}
