<?php
/**
 * Script de prueba para diagnosticar movimiento de imágenes
 * Acceso: https://janetzy.shop/test_plate_move.php?key=test2025
 */

if (!isset($_GET['key']) || $_GET['key'] !== 'test2025') {
    die('Acceso denegado');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DE MOVIMIENTO DE PLACAS ===\n\n";

// Configuración
$ftpDir = '/home2/janetzy/placas/IP CAMERA/01';
$publicDir = '/home2/janetzy/public_html/placas';

echo "1. Verificando directorios...\n";
echo "   FTP: $ftpDir\n";
echo "   - Existe: " . (is_dir($ftpDir) ? 'SÍ' : 'NO') . "\n";
echo "   - Legible: " . (is_readable($ftpDir) ? 'SÍ' : 'NO') . "\n";
echo "\n";
echo "   Público: $publicDir\n";
echo "   - Existe: " . (is_dir($publicDir) ? 'SÍ' : 'NO') . "\n";
echo "   - Escribible: " . (is_writable($publicDir) ? 'SÍ' : 'NO') . "\n";
echo "   - Permisos: " . substr(sprintf('%o', fileperms($publicDir)), -4) . "\n";
echo "\n";

// Crear directorio público si no existe
if (!is_dir($publicDir)) {
    echo "2. Creando directorio público...\n";
    if (mkdir($publicDir, 0755, true)) {
        echo "   ✓ Directorio creado\n";
    } else {
        echo "   ✗ ERROR al crear directorio\n";
    }
    echo "\n";
}

// Buscar imágenes en FTP
echo "3. Buscando imágenes en FTP...\n";
$images = glob($ftpDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
echo "   Encontradas: " . count($images) . " imágenes\n";

if (count($images) > 0) {
    echo "\n4. Intentando mover la primera imagen...\n";
    $sourceFile = $images[0];
    $fileName = basename($sourceFile);
    $destFile = $publicDir . '/' . $fileName;
    
    echo "   Origen: $sourceFile\n";
    echo "   Destino: $destFile\n";
    echo "   Tamaño: " . filesize($sourceFile) . " bytes\n";
    echo "\n";
    
    // Intentar copiar
    echo "   Intentando copy()...\n";
    if (copy($sourceFile, $destFile)) {
        echo "   ✓ Copiado exitosamente\n";
        
        // Verificar que el archivo existe
        if (file_exists($destFile)) {
            echo "   ✓ Archivo verificado en destino\n";
            echo "   - Tamaño: " . filesize($destFile) . " bytes\n";
            
            // Intentar eliminar original
            echo "\n   Intentando eliminar archivo original...\n";
            if (unlink($sourceFile)) {
                echo "   ✓ Archivo original eliminado\n";
            } else {
                echo "   ✗ ERROR: No se pudo eliminar original\n";
                echo "   - Error: " . print_r(error_get_last(), true) . "\n";
            }
        } else {
            echo "   ✗ ERROR: El archivo no existe en destino\n";
        }
    } else {
        echo "   ✗ ERROR al copiar\n";
        $error = error_get_last();
        echo "   - Error: " . print_r($error, true) . "\n";
    }
    
    echo "\n5. Estado final:\n";
    $ftpImages = glob($ftpDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
    $publicImages = glob($publicDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
    echo "   FTP: " . count($ftpImages) . " imágenes\n";
    echo "   Público: " . count($publicImages) . " imágenes\n";
} else {
    echo "\n   No hay imágenes para procesar\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
