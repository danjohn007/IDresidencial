<?php
/**
 * Script para verificar la estructura de detected_plates
 * Acceso: https://janetzy.shop/residencial/test_detected_plates.php?key=test2025
 */

if (!isset($_GET['key']) || $_GET['key'] !== 'test2025') {
    die('Acceso denegado');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== VERIFICACIÓN DE TABLA detected_plates ===\n\n";

// Conectar a BD
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=janetzy_residencial;charset=utf8mb4",
        "janetzy_residencial",
        "Danjohn007!",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Conexión a BD exitosa\n\n";
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage() . "\n");
}

// Verificar si la tabla existe
echo "1. Verificando existencia de tabla...\n";
$stmt = $db->query("SHOW TABLES LIKE 'detected_plates'");
if ($stmt->rowCount() > 0) {
    echo "   ✓ Tabla 'detected_plates' existe\n\n";
} else {
    echo "   ❌ Tabla 'detected_plates' NO EXISTE\n";
    echo "   Ejecuta la migración: database/migrations/007_create_detected_plates.sql\n";
    exit;
}

// Mostrar estructura
echo "2. Estructura de la tabla:\n";
$stmt = $db->query("DESCRIBE detected_plates");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "   - {$col['Field']}: {$col['Type']} " . 
         ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
         ($col['Key'] ? " ({$col['Key']})" : '') . "\n";
}
echo "\n";

// Contar registros
echo "3. Registros en la tabla:\n";
$stmt = $db->query("SELECT COUNT(*) as total FROM detected_plates");
$total = $stmt->fetch()['total'];
echo "   Total: $total registros\n\n";

// Mostrar últimos 5 registros
if ($total > 0) {
    echo "4. Últimos 5 registros:\n";
    $stmt = $db->query("
        SELECT id, plate_text, captured_at, is_match, status, created_at
        FROM detected_plates
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($records as $r) {
        echo "   ID: {$r['id']} | Placa: {$r['plate_text']} | ";
        echo "Captura: {$r['captured_at']} | ";
        echo "Match: " . ($r['is_match'] ? 'SÍ' : 'NO') . " | ";
        echo "Status: {$r['status']}\n";
    }
    echo "\n";
}

// Probar inserción
echo "5. Probando inserción de prueba...\n";
try {
    $testPlate = 'TEST' . rand(1000, 9999);
    $stmt = $db->prepare("
        INSERT INTO detected_plates (
            plate_text,
            captured_at,
            unit_id,
            is_match,
            matched_vehicle_id,
            payload_json,
            status,
            processed_at,
            notes
        ) VALUES (?, NOW(), ?, ?, ?, ?, ?, NOW(), ?)
    ");
    
    $payload = json_encode([
        'test' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    $stmt->execute([
        $testPlate,
        1,
        0,
        null,
        $payload,
        'test',
        'Registro de prueba desde script de verificación'
    ]);
    
    $insertId = $db->lastInsertId();
    echo "   ✓ Inserción exitosa - ID: $insertId - Placa: $testPlate\n";
    
    // Eliminar registro de prueba
    $db->exec("DELETE FROM detected_plates WHERE id = $insertId");
    echo "   ✓ Registro de prueba eliminado\n\n";
    
} catch (PDOException $e) {
    echo "   ❌ ERROR al insertar: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n\n";
}

echo "=== FIN DE VERIFICACIÓN ===\n";
?>
