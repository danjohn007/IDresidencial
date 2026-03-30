#!/usr/bin/env php
<?php
/**
 * Cron Job: Generar Reportes del Catálogo de Incidencias Fijas
 *
 * Este script revisa el catálogo de incidencias recurrentes y genera
 * reportes de mantenimiento automáticamente para las entradas vencidas.
 *
 * Configuración de Crontab (ejecutar diariamente a las 7:00 AM):
 * 0 7 * * * /usr/bin/php /path/to/IDresidencial/cron/generate_catalog_reports.php >> /var/log/catalog_reports.log 2>&1
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/app');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', ROOT_PATH . '/config');
}

require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

// Load the model
require_once APP_PATH . '/models/MaintenanceCatalog.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando generación de reportes del catálogo de incidencias fijas...\n";

try {
    $catalog = new MaintenanceCatalog();
    $dueItems = $catalog->getDueItems();

    if (empty($dueItems)) {
        echo "[" . date('Y-m-d H:i:s') . "] No hay incidencias fijas vencidas. Finalizando.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Se encontraron " . count($dueItems) . " incidencia(s) vencida(s).\n";

    $created = 0;
    $errors  = 0;

    foreach ($dueItems as $item) {
        $reportId = $catalog->generateReport($item);

        if ($reportId !== false) {
            $created++;
            echo "[" . date('Y-m-d H:i:s') . "] Reporte #{$reportId} generado para: {$item['title']} (ID catálogo: {$item['id']})\n";
        } else {
            $errors++;
            echo "[" . date('Y-m-d H:i:s') . "] ERROR al generar reporte para: {$item['title']} (ID catálogo: {$item['id']})\n";
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Proceso finalizado. Reportes creados: {$created}, Errores: {$errors}.\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] EXCEPCIÓN: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
