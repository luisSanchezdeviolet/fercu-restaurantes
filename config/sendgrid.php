<?php
/**
 * Configuración de SendGrid para Fercu Restaurante
 * 
 * Las claves se cargan desde variables de entorno (.env)
 * Ver .env.example para la lista de variables requeridas.
 * 
 * Para obtener tu API Key de SendGrid:
 * 1. Ve a https://app.sendgrid.com/settings/api_keys
 * 2. Click en "Create API Key"
 * 3. Selecciona "Full Access" o permisos específicos de Mail Send
 * 4. Copia la API Key y agrégala a tu archivo .env
 */

require_once __DIR__ . '/env.php';

// API Key de SendGrid (desde variables de entorno)
define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY') ?: '');

// Configuración del remitente
define('SENDGRID_FROM_EMAIL', getenv('SENDGRID_FROM_EMAIL') ?: 'notificaciones@fercupuntodeventa.com');
define('SENDGRID_FROM_NAME', getenv('SENDGRID_FROM_NAME') ?: 'Fercu Restaurante');

// Email de soporte
define('SENDGRID_SUPPORT_EMAIL', getenv('SENDGRID_SUPPORT_EMAIL') ?: 'soporte@fercupuntodeventa.com');

// URLs del sistema
define('APP_URL', getenv('APP_URL') ?: 'http://restaurante.fercupuntodeventa.com');
define('LOGIN_URL', APP_URL . '/presentation/login.php');
define('DASHBOARD_URL', APP_URL . '/dashboard.php');

// Cargar autoloader si no está cargado
if (!class_exists('\SendGrid')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * Función helper para enviar emails con SendGrid
 * 
 * @param string $to Email destinatario
 * @param string $toName Nombre del destinatario
 * @param string $subject Asunto del email
 * @param string $htmlContent Contenido HTML del email
 * @param string $textContent Contenido en texto plano (opcional)
 * @return array ['success' => bool, 'message' => string, 'message_id' => string]
 */
function sendEmail($to, $toName, $subject, $htmlContent, $textContent = '') {
    try {
        $email = new \SendGrid\Mail\Mail();
        
        // Configurar remitente
        $email->setFrom(SENDGRID_FROM_EMAIL, SENDGRID_FROM_NAME);
        
        // Configurar destinatario
        $email->addTo($to, $toName);
        
        // Configurar asunto
        $email->setSubject($subject);
        
        // Configurar contenido
        if (!empty($textContent)) {
            $email->addContent("text/plain", $textContent);
        }
        $email->addContent("text/html", $htmlContent);
        
        // Configurar categorías para análisis en SendGrid
        $email->addCategory("restaurantes-saas");
        
        // Enviar email
        $sendgrid = new \SendGrid(SENDGRID_API_KEY);
        $response = $sendgrid->send($email);
        
        if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
            // Obtener message ID de las cabeceras
            $headers = $response->headers();
            $messageId = isset($headers['X-Message-Id']) ? $headers['X-Message-Id'] : 'unknown';
            
            return [
                'success' => true,
                'message' => 'Email enviado correctamente',
                'message_id' => $messageId,
                'status_code' => $response->statusCode()
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $response->body(),
                'status_code' => $response->statusCode()
            ];
        }
        
    } catch (Exception $e) {
        error_log('Error SendGrid: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

/**
 * Log de emails enviados
 */
function logEmail($to, $subject, $result) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/emails.log';
    $timestamp = date('Y-m-d H:i:s');
    $status = $result['success'] ? 'SUCCESS' : 'FAILED';
    $messageId = isset($result['message_id']) ? $result['message_id'] : 'N/A';
    
    $logMessage = sprintf(
        "[%s] %s | To: %s | Subject: %s | MessageID: %s | Message: %s\n",
        $timestamp,
        $status,
        $to,
        $subject,
        $messageId,
        $result['message']
    );
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
?>
