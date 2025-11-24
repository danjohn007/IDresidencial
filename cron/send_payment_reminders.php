#!/usr/bin/env php
<?php
/**
 * Cron Job: Enviar Recordatorios de Pago
 * 
 * Este script debe ejecutarse diariamente para enviar recordatorios
 * de pago a los residentes un día antes del vencimiento.
 * 
 * Configuración de Crontab (ejecutar diariamente a las 8:00 AM):
 * 0 8 * * * /usr/bin/php /path/to/IDresidencial/cron/send_payment_reminders.php >> /var/log/payment_reminders.log 2>&1
 */

// Configurar rutas
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Cargar configuración
require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/core/Mailer.php';

// Iniciar sesión para logging
session_start();

echo "[" . date('Y-m-d H:i:s') . "] Iniciando envío de recordatorios de pago...\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Generar recordatorios para hoy usando el procedimiento almacenado
    $db->exec("CALL generate_payment_reminders()");
    echo "[" . date('Y-m-d H:i:s') . "] Recordatorios generados.\n";
    
    // Obtener recordatorios pendientes de enviar para hoy
    $stmt = $db->query("
        SELECT 
            pr.id as reminder_id,
            pr.email_to,
            mf.id as fee_id,
            mf.amount,
            mf.period,
            mf.due_date,
            p.property_number,
            CONCAT(u.first_name, ' ', u.last_name) as resident_name
        FROM payment_reminders pr
        JOIN maintenance_fees mf ON pr.maintenance_fee_id = mf.id
        JOIN properties p ON mf.property_id = p.id
        JOIN residents r ON r.property_id = p.id AND r.is_primary = 1
        JOIN users u ON r.user_id = u.id
        WHERE pr.status = 'pending'
        AND pr.reminder_date = CURDATE()
        AND mf.status IN ('pending', 'overdue')
    ");
    
    $reminders = $stmt->fetchAll();
    $sentCount = 0;
    $failedCount = 0;
    
    if (empty($reminders)) {
        echo "[" . date('Y-m-d H:i:s') . "] No hay recordatorios pendientes para enviar hoy.\n";
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] Encontrados " . count($reminders) . " recordatorios para enviar.\n";
        
        $mailer = new Mailer();
        
        if (!$mailer->isConfigured()) {
            echo "[" . date('Y-m-d H:i:s') . "] ERROR: El sistema de correo no está configurado.\n";
            exit(1);
        }
        
        foreach ($reminders as $reminder) {
            echo "[" . date('Y-m-d H:i:s') . "] Enviando recordatorio a {$reminder['email_to']}...\n";
            
            $resident = [
                'resident_name' => $reminder['resident_name'],
                'property_number' => $reminder['property_number']
            ];
            
            $fee = [
                'period' => $reminder['period'],
                'due_date' => $reminder['due_date'],
                'amount' => $reminder['amount']
            ];
            
            $emailSent = $mailer->sendPaymentReminder($reminder['email_to'], $resident, $fee);
            
            if ($emailSent) {
                // Marcar como enviado
                $updateStmt = $db->prepare("
                    UPDATE payment_reminders 
                    SET status = 'sent', sent = 1, sent_at = NOW() 
                    WHERE id = ?
                ");
                $updateStmt->execute([$reminder['reminder_id']]);
                
                $sentCount++;
                echo "[" . date('Y-m-d H:i:s') . "] ✓ Recordatorio enviado exitosamente.\n";
            } else {
                // Marcar como fallido
                $updateStmt = $db->prepare("
                    UPDATE payment_reminders 
                    SET status = 'failed' 
                    WHERE id = ?
                ");
                $updateStmt->execute([$reminder['reminder_id']]);
                
                $failedCount++;
                echo "[" . date('Y-m-d H:i:s') . "] ✗ Error al enviar recordatorio.\n";
            }
            
            // Pequeña pausa para no saturar el servidor de email
            sleep(1);
        }
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado. Enviados: {$sentCount}, Fallidos: {$failedCount}\n";
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
