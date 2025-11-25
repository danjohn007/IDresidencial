<?php
/**
 * Verificar imágenes en FTP y proceso del cron
 * Acceso: https://janetzy.shop/residencial/verify_images.php?key=verify2025
 */

if (!isset($_GET['key']) || $_GET['key'] !== 'verify2025') {
    die('Acceso denegado');
}

header('Content-Type: text/html; charset=utf-8');

$ftpDir = '/home2/janetzy/placas/IP CAMERA/01';
$publicDir = '/home2/janetzy/public_html/placas';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Verificación de Imágenes</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #0f0; }
        .container { max-width: 1200px; margin: 0 auto; background: #000; padding: 20px; border: 2px solid #0f0; }
        h2 { color: #0f0; border-bottom: 2px solid #0f0; padding-bottom: 10px; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #fa0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #333; }
        th { background: #1a4d1a; }
        img { max-width: 300px; border: 2px solid #0f0; margin: 5px; }
        pre { background: #0a0a0a; padding: 10px; border-left: 3px solid #0f0; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #0f0; color: #000; text-decoration: none; border-radius: 5px; margin: 5px; font-weight: bold; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔍 VERIFICACIÓN DE IMÁGENES</h1>";

// 1. Imágenes en FTP (pendientes de procesar)
echo "<h2>📂 1. IMÁGENES EN FTP (Pendientes)</h2>";
if (is_dir($ftpDir)) {
    $ftpImages = glob($ftpDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
    
    if (count($ftpImages) > 0) {
        echo "<p class='warning'>⚠️ Hay " . count($ftpImages) . " imagen(es) esperando ser procesadas por el cron</p>";
        echo "<table>";
        echo "<tr><th>#</th><th>Archivo</th><th>Tamaño</th><th>Fecha</th></tr>";
        foreach ($ftpImages as $idx => $img) {
            echo "<tr>";
            echo "<td>" . ($idx + 1) . "</td>";
            echo "<td><strong>" . basename($img) . "</strong></td>";
            echo "<td>" . round(filesize($img)/1024, 2) . " KB</td>";
            echo "<td>" . date('Y-m-d H:i:s', filemtime($img)) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='success'>✓ No hay imágenes pendientes (el cron ya las procesó)</p>";
    }
} else {
    echo "<p class='error'>✗ El directorio FTP no existe: $ftpDir</p>";
}

// 2. Imágenes en carpeta pública
echo "<h2>🌐 2. IMÁGENES EN CARPETA PÚBLICA (Procesadas)</h2>";
if (is_dir($publicDir)) {
    $publicImages = glob($publicDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
    
    // Ordenar por fecha (más recientes primero)
    usort($publicImages, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    echo "<p class='success'>✓ Total de imágenes procesadas: " . count($publicImages) . "</p>";
    
    if (count($publicImages) > 0) {
        echo "<h3>Últimas 10 imágenes procesadas:</h3>";
        echo "<table>";
        echo "<tr><th>#</th><th>Archivo</th><th>Tipo</th><th>Placa</th><th>Fecha</th><th>Vista Previa</th></tr>";
        
        foreach (array_slice($publicImages, 0, 10) as $idx => $img) {
            $filename = basename($img);
            $isLP = strpos($filename, 'LP_') === 0;
            $isVH = strpos($filename, 'VH_') === 0;
            $type = $isLP ? 'Placa' : ($isVH ? 'Vehículo' : 'Desconocido');
            
            // Extraer placa del nombre
            preg_match('/(?:LP|VH)_([A-Z0-9]+)_/', $filename, $matches);
            $plate = isset($matches[1]) ? $matches[1] : 'N/A';
            
            echo "<tr>";
            echo "<td>" . ($idx + 1) . "</td>";
            echo "<td style='font-size:11px;'><strong>$filename</strong></td>";
            echo "<td class='" . ($isLP ? 'success' : 'warning') . "'>$type</td>";
            echo "<td><strong>$plate</strong></td>";
            echo "<td>" . date('Y-m-d H:i:s', filemtime($img)) . "</td>";
            echo "<td><a href='https://janetzy.shop/placas/$filename' target='_blank'>
                    <img src='https://janetzy.shop/placas/$filename' style='max-width:150px;'>
                  </a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p class='error'>✗ El directorio público no existe: $publicDir</p>";
}

// 3. Base de datos
echo "<h2>💾 3. REGISTROS EN BASE DE DATOS</h2>";
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=janetzy_residencial;charset=utf8mb4",
        "janetzy_residencial",
        "Danjohn007!",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM detected_plates");
    $total = $stmt->fetch()['total'];
    
    echo "<p class='success'>✓ Total placas en BD: $total</p>";
    
    if ($total > 0) {
        echo "<h3>Últimas 10 detecciones:</h3>";
        $stmt = $db->query("
            SELECT 
                plate_text, 
                captured_at, 
                is_match,
                status,
                JSON_EXTRACT(payload_json, '$.image_path') as image_path
            FROM detected_plates 
            ORDER BY captured_at DESC 
            LIMIT 10
        ");
        
        echo "<table>";
        echo "<tr><th>#</th><th>Placa</th><th>Fecha</th><th>Match</th><th>Status</th><th>Imagen</th></tr>";
        $idx = 1;
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>$idx</td>";
            echo "<td><strong>{$row['plate_text']}</strong></td>";
            echo "<td>{$row['captured_at']}</td>";
            echo "<td class='" . ($row['is_match'] ? 'success' : 'warning') . "'>" . 
                 ($row['is_match'] ? 'SÍ' : 'NO') . "</td>";
            echo "<td>{$row['status']}</td>";
            
            $imgPath = str_replace('"', '', $row['image_path']);
            if ($imgPath && file_exists($publicDir . '/' . basename($imgPath))) {
                echo "<td><a href='https://janetzy.shop/placas/" . basename($imgPath) . "' target='_blank'>Ver</a></td>";
            } else {
                echo "<td class='error'>Sin imagen</td>";
            }
            echo "</tr>";
            $idx++;
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error de BD: " . $e->getMessage() . "</p>";
}

// 4. Estado del cron
echo "<h2>⏰ 4. ESTADO DEL CRON</h2>";
$cronLog = '/home2/janetzy/public_html/logs/plate_processing.log';

if (file_exists($cronLog)) {
    $logContent = file_get_contents($cronLog);
    $lines = explode("\n", $logContent);
    $lastLines = array_filter(array_slice($lines, -20));
    
    echo "<p class='success'>✓ Log del cron existe</p>";
    echo "<h3>Últimas 20 líneas:</h3>";
    echo "<pre>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
} else {
    echo "<p class='warning'>⚠️ No existe log del cron todavía</p>";
}

// Resumen
echo "<h2>📊 RESUMEN</h2>";
$ftpCount = isset($ftpImages) ? count($ftpImages) : 0;
$publicCount = isset($publicImages) ? count($publicImages) : 0;

if ($ftpCount > 0) {
    echo "<p class='warning'>⚠️ Hay $ftpCount imagen(es) en FTP esperando ser procesadas</p>";
    echo "<p>El cron debería procesarlas en menos de 1 minuto</p>";
} elseif ($publicCount > 0) {
    echo "<p class='success'>✓ TODO FUNCIONANDO CORRECTAMENTE</p>";
    echo "<p>Se han procesado $publicCount imágenes exitosamente</p>";
} else {
    echo "<p class='warning'>⚠️ Aún no hay imágenes procesadas</p>";
    echo "<p>Espera a que pase un vehículo frente a la cámara</p>";
}

echo "<div style='margin-top:20px;'>";
echo "<a href='/residencial/access/detectedPlates' class='btn'>Ver Placas Detectadas</a>";
echo "<a href='?key=verify2025' class='btn'>🔄 Actualizar</a>";
echo "</div>";

echo "<p style='text-align:center; margin-top:30px; color:#666;'>
        Actualizado: " . date('Y-m-d H:i:s') . "
      </p>";
echo "</div></body></html>";
?>
