<?php
/**
 * Mailer - Clase para envío de correos electrónicos
 * Utiliza funciones nativas de PHP con configuración SMTP
 */

class Mailer {
    
    private $db;
    private $settings = [];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->loadSettings();
        $this->configureSMTP();
    }
    
    /**
     * Cargar configuración de correo desde la base de datos
     */
    private function loadSettings() {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'email_%' OR setting_key = 'site_name'");
        while ($row = $stmt->fetch()) {
            $this->settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    /**
     * Configurar SMTP para envío de correos
     */
    private function configureSMTP() {
        // Configure SMTP settings - will use fsockopen for SMTP
        // Settings are stored in $this->settings for use in send()
    }
    
    /**
     * Enviar correo electrónico usando SMTP
     * 
     * @param string|array $to Dirección(es) de destino
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo del correo (HTML)
     * @param string $altBody Cuerpo alternativo (texto plano) - no usado en esta implementación
     * @return bool True si se envió exitosamente, False en caso contrario
     */
    public function send($to, $subject, $body, $altBody = '') {
        try {
            if (!$this->isConfigured()) {
                error_log("Email configuration incomplete");
                return false;
            }
            
            $host = $this->settings['email_host'];
            $port = intval($this->settings['email_port'] ?? 465);
            $user = $this->settings['email_user'];
            $pass = $this->settings['email_password'];
            $from = $this->settings['email_from'] ?? $user;
            $fromName = $this->settings['site_name'] ?? 'ERP Residencial';
            
            // Handle multiple recipients
            $recipients = is_array($to) ? $to : [$to];
            
            // Connect to SMTP server (without @ to allow proper error capture)
            $socket = fsockopen($host, $port, $errno, $errstr, 30);
            if (!$socket) {
                $errorDetails = "SMTP connection to {$host}:{$port} failed";
                if ($errno) {
                    $errorDetails .= " - Error {$errno}: {$errstr}";
                }
                if ($port == 465) {
                    $errorDetails .= " (SSL/TLS). Verify that SSL is enabled and port 465 is open.";
                } elseif ($port == 587) {
                    $errorDetails .= " (STARTTLS). Verify that port 587 is open.";
                }
                error_log($errorDetails);
                return false;
            }
            
            // Read server response
            $this->smtpRead($socket);
            
            // Send EHLO/HELO
            $this->smtpWrite($socket, "EHLO " . $host . "\r\n");
            $this->smtpRead($socket);
            
            // TLS/SSL for port 465 or STARTTLS for port 587
            if ($port == 587) {
                $this->smtpWrite($socket, "STARTTLS\r\n");
                $starttlsResponse = $this->smtpRead($socket);
                
                // Verify STARTTLS was accepted (220 response)
                if (strpos($starttlsResponse, '220') === false) {
                    error_log("STARTTLS failed: {$starttlsResponse}");
                    fclose($socket);
                    return false;
                }
                
                // Enable TLS encryption
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    error_log("Failed to enable TLS encryption");
                    fclose($socket);
                    return false;
                }
                
                $this->smtpWrite($socket, "EHLO " . $host . "\r\n");
                $this->smtpRead($socket);
            }
            
            // Authenticate
            $this->smtpWrite($socket, "AUTH LOGIN\r\n");
            $this->smtpRead($socket);
            $this->smtpWrite($socket, base64_encode($user) . "\r\n");
            $this->smtpRead($socket);
            $this->smtpWrite($socket, base64_encode($pass) . "\r\n");
            $authResponse = $this->smtpRead($socket);
            
            if (strpos($authResponse, '235') === false) {
                error_log("SMTP authentication failed: $authResponse");
                fclose($socket);
                return false;
            }
            
            // Send MAIL FROM
            $this->smtpWrite($socket, "MAIL FROM: <{$from}>\r\n");
            $this->smtpRead($socket);
            
            // Send RCPT TO for each recipient
            foreach ($recipients as $recipient) {
                $this->smtpWrite($socket, "RCPT TO: <{$recipient}>\r\n");
                $this->smtpRead($socket);
            }
            
            // Send DATA command
            $this->smtpWrite($socket, "DATA\r\n");
            $this->smtpRead($socket);
            
            // Prepare email headers and body
            $boundary = md5(uniqid(time()));
            $headers = "From: {$fromName} <{$from}>\r\n";
            $headers .= "Reply-To: {$from}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
            $headers .= "To: " . implode(', ', $recipients) . "\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "\r\n";
            
            // Plain text version
            $message = "--{$boundary}\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= strip_tags($body) . "\r\n";
            
            // HTML version
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= $body . "\r\n";
            $message .= "--{$boundary}--\r\n";
            
            // Send message
            $this->smtpWrite($socket, $headers . $message . "\r\n.\r\n");
            $dataResponse = $this->smtpRead($socket);
            
            // Quit
            $this->smtpWrite($socket, "QUIT\r\n");
            $this->smtpRead($socket);
            fclose($socket);
            
            if (strpos($dataResponse, '250') !== false) {
                error_log("Email sent successfully to: " . implode(', ', $recipients));
                return true;
            } else {
                error_log("Email sending failed. Server response: $dataResponse");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Email sending error: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Write to SMTP socket
     */
    private function smtpWrite($socket, $data) {
        fputs($socket, $data);
    }
    
    /**
     * Read from SMTP socket
     */
    private function smtpRead($socket) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        return $response;
    }
    
    /**
     * Enviar correo de recuperación de contraseña
     * 
     * @param string $email Email del usuario
     * @param string $token Token de recuperación
     * @param array $user Datos del usuario
     * @return bool
     */
    public function sendPasswordReset($email, $token, $user) {
        $resetLink = BASE_URL . '/auth/resetPassword?token=' . $token;
        
        $subject = 'Recuperación de Contraseña - ' . ($this->settings['site_name'] ?? 'ERP Residencial');
        
        $body = $this->getPasswordResetTemplate($user, $resetLink);
        
        return $this->send($email, $subject, $body);
    }
    
    /**
     * Enviar recordatorio de pago
     * 
     * @param string $email Email del residente
     * @param array $resident Datos del residente
     * @param array $fee Datos de la cuota
     * @return bool
     */
    public function sendPaymentReminder($email, $resident, $fee) {
        $subject = 'Recordatorio de Pago - ' . ($this->settings['site_name'] ?? 'ERP Residencial');
        
        $body = $this->getPaymentReminderTemplate($resident, $fee);
        
        return $this->send($email, $subject, $body);
    }
    
    /**
     * Plantilla HTML para recuperación de contraseña
     */
    private function getPasswordResetTemplate($user, $resetLink) {
        $siteName = $this->settings['site_name'] ?? 'ERP Residencial';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #3B82F6; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                .button { display: inline-block; padding: 12px 30px; background-color: #3B82F6; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #6b7280; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔒 Recuperación de Contraseña</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$user['first_name']} {$user['last_name']}</strong>,</p>
                    
                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>{$siteName}</strong>.</p>
                    
                    <p>Para restablecer tu contraseña, haz clic en el siguiente botón:</p>
                    
                    <p style='text-align: center;'>
                        <a href='{$resetLink}' class='button'>Restablecer Contraseña</a>
                    </p>
                    
                    <p>O copia y pega el siguiente enlace en tu navegador:</p>
                    <p style='word-break: break-all; color: #3B82F6;'>{$resetLink}</p>
                    
                    <p><strong>Este enlace expirará en 1 hora.</strong></p>
                    
                    <p>Si no solicitaste este cambio, puedes ignorar este correo de forma segura.</p>
                    
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;'>
                    
                    <p style='font-size: 12px; color: #6b7280;'>
                        Por seguridad, nunca compartas este enlace con nadie.
                    </p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " {$siteName}. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Plantilla HTML para recordatorio de pago
     */
    private function getPaymentReminderTemplate($resident, $fee) {
        $siteName = $this->settings['site_name'] ?? 'ERP Residencial';
        $dueDate = date('d/m/Y', strtotime($fee['due_date']));
        $amount = number_format($fee['amount'], 2);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #10B981; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                .amount { font-size: 32px; font-weight: bold; color: #10B981; text-align: center; margin: 20px 0; }
                .button { display: inline-block; padding: 12px 30px; background-color: #10B981; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #6b7280; }
                .info-box { background-color: #fff; padding: 15px; border-left: 4px solid #10B981; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>💳 Recordatorio de Pago</h1>
                </div>
                <div class='content'>
                    <p>Estimado(a) <strong>{$resident['resident_name']}</strong>,</p>
                    
                    <p>Le recordamos que tiene un pago pendiente que vence mañana.</p>
                    
                    <div class='info-box'>
                        <p><strong>Propiedad:</strong> {$resident['property_number']}</p>
                        <p><strong>Período:</strong> {$fee['period']}</p>
                        <p><strong>Fecha de vencimiento:</strong> {$dueDate}</p>
                    </div>
                    
                    <div class='amount'>
                        \${$amount} MXN
                    </div>
                    
                    <p>Para realizar su pago, puede:</p>
                    <ul>
                        <li>Acudir a administración en horario de oficina</li>
                        <li>Realizar un pago en línea a través de su portal de residentes</li>
                        <li>Realizar una transferencia bancaria y enviar el comprobante</li>
                    </ul>
                    
                    <p style='text-align: center;'>
                        <a href='" . BASE_URL . "/residents/payments' class='button'>Ver mis Pagos</a>
                    </p>
                    
                    <p>Si ya realizó su pago, por favor ignore este mensaje.</p>
                    
                    <hr style='margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;'>
                    
                    <p style='font-size: 12px; color: #6b7280;'>
                        Para cualquier duda o aclaración, puede contactarnos a través de nuestros canales de atención.
                    </p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " {$siteName}. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Verificar si la configuración de correo está completa
     * 
     * @return bool
     */
    public function isConfigured() {
        $required = ['email_host', 'email_user', 'email_password', 'email_from'];
        
        foreach ($required as $key) {
            if (empty($this->settings[$key])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Enviar correo de prueba
     * 
     * @param string $to Email de destino
     * @return bool
     */
    public function sendTest($to) {
        $subject = 'Correo de Prueba - ' . ($this->settings['site_name'] ?? 'ERP Residencial');
        
        $body = "
        <html>
        <body>
            <h2>✅ Correo de Prueba</h2>
            <p>Este es un correo de prueba enviado desde el sistema ERP Residencial.</p>
            <p>Si recibiste este correo, la configuración SMTP está funcionando correctamente.</p>
            <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
        </body>
        </html>
        ";
        
        return $this->send($to, $subject, $body);
    }
}
