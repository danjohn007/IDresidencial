#!/usr/bin/env php
<?php
/**
 * Cron Job: Aplicar Penalizaciones por Pagos Atrasados
 *
 * Evalúa las cuotas de mantenimiento vencidas y aplica las penalizaciones
 * configuradas en la tabla penalty_rules.
 *
 * Configuración de Crontab (ejecutar diariamente a las 6:00 AM):
 * 0 6 * * * /usr/bin/php /path/to/IDresidencial/cron/apply_late_penalties.php >> /var/log/late_penalties.log 2>&1
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once CONFIG_PATH . '/database.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando proceso de penalizaciones por pagos atrasados...\n";

try {
    $db = Database::getInstance()->getConnection();

    // Obtener la regla de penalización activa
    $ruleStmt = $db->query("SELECT * FROM penalty_rules WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $rule = $ruleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$rule) {
        echo "[" . date('Y-m-d H:i:s') . "] No hay regla de penalización activa configurada. Finalizando.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Regla activa encontrada (ID: {$rule['id']}).\n";

    $today = new DateTime('today');

    // Look up the system superadmin user to use as created_by for automated actions
    $sysUserStmt = $db->query("SELECT id FROM users WHERE role = 'superadmin' ORDER BY id ASC LIMIT 1");
    $sysUser = $sysUserStmt->fetch(PDO::FETCH_ASSOC);
    $systemUserId = $sysUser ? (int) $sysUser['id'] : null;

    // Determinar el día de corte del mes actual
    $cutDayType = $rule['cut_day_type'];
    $cutDay     = intval($rule['cut_day']);
    $graceDays  = intval($rule['grace_days']);

    switch ($cutDayType) {
        case 'first':
            $cutDayNum = 1;
            break;
        case 'last':
            $cutDayNum = (int) $today->format('t'); // last day of current month
            break;
        default: // custom
            $cutDayNum = max(1, min(28, $cutDay));
            break;
    }

    // Calcular la fecha límite de pago con días de gracia
    $cutDate = new DateTime($today->format('Y-m-') . str_pad($cutDayNum, 2, '0', STR_PAD_LEFT));
    $dueDate = clone $cutDate;
    if ($graceDays > 0) {
        $dueDate->modify("+{$graceDays} days");
    }

    echo "[" . date('Y-m-d H:i:s') . "] Fecha límite (con {$graceDays} días gracia): " . $dueDate->format('Y-m-d') . "\n";

    // Solo procesar si hoy es posterior a la fecha límite
    if ($today <= $dueDate) {
        echo "[" . date('Y-m-d H:i:s') . "] Todavía dentro del período de gracia. No se aplican penalizaciones hoy.\n";
        exit(0);
    }

    // Obtener cuotas de mantenimiento pendientes/vencidas cuya due_date ya pasó
    $feesStmt = $db->query("
        SELECT mf.id, mf.property_id, mf.amount, mf.period, mf.due_date,
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
        $daysOverdue  = intval($fee['days_overdue']);
        $originalAmt  = floatval($fee['amount']);
        $propertyNum  = $fee['property_number'];
        $period       = $fee['period'];

        // Determine days in the month of the fee's due_date for accurate tier calculation
        $feeDueDate   = new DateTime($fee['due_date']);
        $daysInMonth  = (int) $feeDueDate->format('t');

        // Determine which penalty tier applies based on days overdue
        if ($daysOverdue <= $daysInMonth) {
            // First tier: after cut day, same month
            $penType  = $rule['after_cutday_type'];
            $penValue = floatval($rule['after_cutday_value']);
        } elseif ($daysOverdue <= $daysInMonth * 2) {
            // Second tier: next month
            $penType  = $rule['next_month_type'];
            $penValue = floatval($rule['next_month_value']);
        } else {
            // Third tier: second month or beyond
            $penType  = $rule['second_month_type'];
            $penValue = floatval($rule['second_month_value']);
        }

        if ($penValue <= 0) {
            $skipped++;
            continue;
        }

        // Calculate penalty amount
        if ($penType === 'percentage') {
            $penaltyAmt = round($originalAmt * $penValue / 100, 2);
        } else {
            $penaltyAmt = round($penValue, 2);
        }

        if ($penaltyAmt <= 0) {
            $skipped++;
            continue;
        }

        // Check if a penalty record already exists for this fee today to avoid duplicates
        $dupCheck = $db->prepare("
            SELECT id FROM financial_movements
            WHERE reference_type = 'penalty'
              AND reference_id   = ?
              AND DATE(transaction_date) = CURDATE()
        ");
        $dupCheck->execute([$fee['id']]);
        if ($dupCheck->fetch()) {
            echo "[" . date('Y-m-d H:i:s') . "] Penalización ya aplicada hoy para cuota #{$fee['id']} ({$propertyNum} / {$period}). Omitiendo.\n";
            $skipped++;
            continue;
        }

        // Obtain or create a financial_movement_type for "Penalización"
        $typeStmt = $db->query("SELECT id FROM financial_movement_types WHERE name = 'Penalización' LIMIT 1");
        $typeRow  = $typeStmt->fetch(PDO::FETCH_ASSOC);
        if (!$typeRow) {
            $db->exec("INSERT INTO financial_movement_types (name, category) VALUES ('Penalización', 'egreso')");
            $penaltyTypeId = $db->lastInsertId();
        } else {
            $penaltyTypeId = $typeRow['id'];
        }

        $db->beginTransaction();
        try {
            // Insert financial movement for the penalty
            $insStmt = $db->prepare("
                INSERT INTO financial_movements
                    (movement_type_id, transaction_type, amount, description,
                     property_id, reference_type, reference_id,
                     transaction_date, created_by)
                VALUES (?, 'egreso', ?, ?, ?, 'penalty', ?, CURDATE(), ?)
            ");
            $desc = "Penalización por pago atrasado — Cuota {$period} ({$propertyNum}). Días de atraso: {$daysOverdue}.";
            $insStmt->execute([
                $penaltyTypeId,
                $penaltyAmt,
                $desc,
                $fee['property_id'],
                $fee['id'],
                $systemUserId
            ]);

            // Mark the maintenance fee as overdue if not already
            $db->prepare("UPDATE maintenance_fees SET status = 'overdue' WHERE id = ? AND status = 'pending'")->execute([$fee['id']]);

            $db->commit();
            $applied++;
            echo "[" . date('Y-m-d H:i:s') . "] ✓ Penalización de \${$penaltyAmt} aplicada para {$propertyNum} / {$period} ({$daysOverdue} días de atraso).\n";
        } catch (Exception $e) {
            $db->rollBack();
            echo "[" . date('Y-m-d H:i:s') . "] ✗ Error al aplicar penalización para cuota #{$fee['id']}: " . $e->getMessage() . "\n";
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado. Aplicadas: {$applied}, Omitidas: {$skipped}.\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR CRÍTICO: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
