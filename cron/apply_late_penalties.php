#!/usr/bin/env php
<?php
/**
 * Cron Job: Aplicar Penalizaciones por Pagos Atrasados
 * Versión Mejorada - Aplica UNA penalización por tier/periodo
 *
 * Configuración de Crontab (ejecutar diariamente a las 6:00 AM):
 * 0 6 * * * /usr/bin/php /path/to/IDresidencial/cron/apply_late_penalties.php >> /var/log/late_penalties.log 2>&1
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



require_once CONFIG_PATH . '/database.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando proceso de penalizaciones por pagos atrasados...\n";

try {
    $db = Database::getInstance()->getConnection();

    // Obtener la regla de penalización activa
    $ruleStmt = $db->query("
        SELECT * 
        FROM penalty_rules 
        WHERE is_active = 1 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $rule = $ruleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$rule) {
        echo "[" . date('Y-m-d H:i:s') . "] No hay regla de penalización activa configurada. Finalizando.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Regla activa encontrada (ID: {$rule['id']}).\n";
    
    // Obtener días de gracia de la regla
    $graceDays = intval($rule['grace_days'] ?? 0);
    echo "[" . date('Y-m-d H:i:s') . "] Días de gracia configurados: {$graceDays}\n";

    // Obtener usuario del sistema para created_by
    $sysUserStmt = $db->query("SELECT id FROM users WHERE role = 'superadmin' ORDER BY id ASC LIMIT 1");
    $sysUser = $sysUserStmt->fetch(PDO::FETCH_ASSOC);
    $systemUserId = $sysUser ? (int) $sysUser['id'] : null;

    // Obtener o crear tipo de movimiento "Penalización"
    $typeStmt = $db->query("SELECT id FROM financial_movement_types WHERE name = 'Penalización' LIMIT 1");
    $typeRow = $typeStmt->fetch(PDO::FETCH_ASSOC);
    if (!$typeRow) {
        $db->exec("INSERT INTO financial_movement_types (name, category) VALUES ('Penalización', 'egreso')");
        $penaltyTypeId = $db->lastInsertId();
    } else {
        $penaltyTypeId = $typeRow['id'];
    }

    $today = new DateTime();

    // Obtener cuotas vencidas
    $feesStmt = $db->query("
        SELECT mf.id, mf.property_id, mf.amount, mf.period, mf.due_date, mf.late_fee,
               p.property_number,
               DATEDIFF(CURDATE(), mf.due_date) as days_overdue
        FROM maintenance_fees mf
        INNER JOIN properties p ON mf.property_id = p.id
        WHERE mf.status IN ('pending', 'overdue')
          AND mf.due_date < CURDATE()
        ORDER BY mf.due_date ASC
    ");
    $fees = $feesStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($fees)) {
        echo "[" . date('Y-m-d H:i:s') . "] No hay cuotas vencidas para procesar.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Procesando " . count($fees) . " cuotas vencidas...\n";

    $applied = 0;
    $skipped = 0;

    foreach ($fees as $fee) {
        $feeId = $fee['id'];
        $amount = floatval($fee['amount']);
        $daysOverdue = intval($fee['days_overdue']);
        $propertyNum = $fee['property_number'];
        $period = $fee['period'];
        
        // Aplicar período de gracia - no penalizar si todavía está en gracia
        if ($daysOverdue <= $graceDays) {
            echo "[" . date('Y-m-d H:i:s') . "] Cuota #{$feeId} ({$propertyNum} / {$period}) tiene {$daysOverdue} días de atraso (dentro de {$graceDays} días de gracia). Omitiendo.\n";
            $skipped++;
            continue;
        }

        // Determinar tier ACTUAL según días de atraso
        $currentTier = 0;
        if ($daysOverdue <= 30) {
            $currentTier = 1;
        } elseif ($daysOverdue <= 60) {
            $currentTier = 2;
        } else {
            $currentTier = 3;
        }

        // Obtener qué tiers ya fueron aplicados para esta cuota
        $appliedTiersStmt = $db->prepare("
            SELECT DISTINCT 
                CASE 
                    WHEN description LIKE '%Tier 1%' THEN 1
                    WHEN description LIKE '%Tier 2%' THEN 2
                    WHEN description LIKE '%Tier 3%' THEN 3
                END as tier_num
            FROM financial_movements
            WHERE reference_type = 'penalty'
              AND reference_id = ?
              AND description LIKE '%Tier%'
        ");
        $appliedTiersStmt->execute([$feeId]);
        $appliedTiers = array_filter(array_column($appliedTiersStmt->fetchAll(PDO::FETCH_ASSOC), 'tier_num'));
        
        // Determinar qué tiers necesitan aplicarse
        $tiersToApply = [];
        for ($tier = 1; $tier <= $currentTier; $tier++) {
            if (!in_array($tier, $appliedTiers)) {
                $tiersToApply[] = $tier;
            }
        }
        
        if (empty($tiersToApply)) {
            echo "[" . date('Y-m-d H:i:s') . "] Cuota #{$feeId} ({$propertyNum} / {$period}) ya tiene todas las penalizaciones hasta Tier {$currentTier}. Omitiendo.\n";
            $skipped++;
            continue;
        }

        // Aplicar cada tier faltante
        $db->beginTransaction();
        try {
            $totalPenaltyApplied = 0;
            
            foreach ($tiersToApply as $tier) {
                // Configurar penalización según tier
                if ($tier === 1) {
                    $tierName = 'Primer mes de atraso';
                    $penType = $rule['after_cutday_type'];
                    $penValue = floatval($rule['after_cutday_value']);
                } elseif ($tier === 2) {
                    $tierName = 'Segundo mes de atraso (Moroso Nivel 1)';
                    $penType = $rule['next_month_type'];
                    $penValue = floatval($rule['next_month_value']);
                } else {
                    $tierName = 'Tercer mes o más (Moroso - Retiro de Servicios)';
                    $penType = $rule['second_month_type'];
                    $penValue = floatval($rule['second_month_value']);
                }

                // Calcular monto de penalización
                if ($penValue <= 0) {
                    continue;
                }

                if ($penType === 'percentage') {
                    $penaltyAmount = round($amount * $penValue / 100, 2);
                } else {
                    $penaltyAmount = round($penValue, 2);
                }

                if ($penaltyAmount <= 0) {
                    continue;
                }

                // Insertar movimiento financiero
                $insertStmt = $db->prepare("
                    INSERT INTO financial_movements
                    (
                        movement_type_id,
                        transaction_type,
                        amount,
                        description,
                        property_id,
                        reference_type,
                        reference_id,
                        transaction_date,
                        created_by
                    )
                    VALUES (?, 'egreso', ?, ?, ?, 'penalty', ?, CURDATE(), ?)
                ");
                
                $description = "Penalización Tier $tier por atraso — Cuota {$period} ({$propertyNum}). {$tierName}";
                
                $insertStmt->execute([
                    $penaltyTypeId,
                    $penaltyAmount,
                    $description,
                    $fee['property_id'],
                    $feeId,
                    $systemUserId
                ]);

                $totalPenaltyApplied += $penaltyAmount;
                echo "[" . date('Y-m-d H:i:s') . "] ✓ Penalización Tier {$tier} de \${$penaltyAmount} aplicada para {$propertyNum} / {$period}.\n";
            }

            // Actualizar late_fee en la cuota (acumular todas las penalizaciones aplicadas)
            $updateFeeStmt = $db->prepare("
                UPDATE maintenance_fees
                SET late_fee = late_fee + ?,
                    status = 'overdue'
                WHERE id = ?
            ");
            $updateFeeStmt->execute([$totalPenaltyApplied, $feeId]);

            // Si el tier actual es 3, marcar residente como moroso
            if ($currentTier === 3) {
                $markMorosoStmt = $db->prepare("
                    UPDATE residents 
                    SET payment_status = 'moroso' 
                    WHERE property_id = ? AND payment_status != 'moroso'
                ");
                $markMorosoStmt->execute([$fee['property_id']]);
            }

            $db->commit();
            $applied++;
            echo "[" . date('Y-m-d H:i:s') . "] ✓ Total de \${$totalPenaltyApplied} en penalizaciones aplicadas (Tiers: " . implode(', ', $tiersToApply) . ") para {$propertyNum} / {$period} ({$daysOverdue} días de atraso).\n";
            
        } catch (Exception $e) {
            $db->rollBack();
            echo "[" . date('Y-m-d H:i:s') . "] ✗ Error al aplicar penalización para cuota #{$feeId}: " . $e->getMessage() . "\n";
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado. Aplicadas: {$applied}, Omitidas: {$skipped}.\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR CRÍTICO: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
