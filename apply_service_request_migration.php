<?php
/**
 * Script to apply migration 016 - Add image field to service requests
 * Run this once to update the database schema
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Aplicando migración 016: Agregar campo de imagen a solicitudes de servicio...\n\n";
    
    // Read the migration file
    $migrationFile = __DIR__ . '/database/migrations/016_add_image_to_service_requests.sql';
    
    if (!file_exists($migrationFile)) {
        die("Error: Archivo de migración no encontrado: $migrationFile\n");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Remove comments and split by semicolons
    $statements = array_filter(
        array_map('trim', 
            preg_split('/;\\s*$/m', 
                preg_replace('/^--.*$/m', '', $sql)
            )
        )
    );
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            echo "Ejecutando: " . substr($statement, 0, 80) . "...\n";
            $db->exec($statement);
            echo "✓ Completado\n\n";
        }
    }
    
    echo "✅ Migración aplicada exitosamente!\n";
    echo "\nLa tabla provider_service_requests ahora tiene el campo image_path.\n";
    echo "Los residentes pueden adjuntar imágenes opcionales a sus solicitudes de servicio.\n";
    
} catch (PDOException $e) {
    echo "❌ Error al aplicar migración:\n";
    echo $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "\n⚠️  El campo image_path ya existe en la tabla. La migración ya fue aplicada anteriormente.\n";
    }
    exit(1);
} catch (Exception $e) {
    echo "❌ Error general:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
