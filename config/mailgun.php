<?php
/**
 * Configuración de Mailgun para correos transaccionales
 *
 * Este archivo centraliza el envío de correos para landing y SaaS:
 * - sendEmail(...)
 * - logEmail(...)
 */

require_once __DIR__ . '/env.php';

if (!defined('MAILGUN_API_KEY')) {
    define('MAILGUN_API_KEY', getenv('MAILGUN_API_KEY') ?: '');
}
if (!defined('MAILGUN_DOMAIN')) {
    define('MAILGUN_DOMAIN', getenv('MAILGUN_DOMAIN') ?: '');
}
if (!defined('MAILGUN_BASE_URL')) {
    define('MAILGUN_BASE_URL', rtrim(getenv('MAILGUN_BASE_URL') ?: 'https://api.mailgun.net', '/'));
}
if (!defined('MAILGUN_FROM_EMAIL')) {
    define('MAILGUN_FROM_EMAIL', getenv('MAILGUN_FROM_EMAIL') ?: 'notificaciones@fercupuntodeventa.com');
}
if (!defined('MAILGUN_FROM_NAME')) {
    define('MAILGUN_FROM_NAME', getenv('MAILGUN_FROM_NAME') ?: 'Fercu Restaurante');
}
if (!defined('MAILGUN_SUPPORT_EMAIL')) {
    define('MAILGUN_SUPPORT_EMAIL', getenv('MAILGUN_SUPPORT_EMAIL') ?: 'soporte@fercupuntodeventa.com');
}
if (!defined('EMAIL_SUPPORT_EMAIL')) {
    define('EMAIL_SUPPORT_EMAIL', MAILGUN_SUPPORT_EMAIL);
}

// Constantes compartidas por plantillas de correo
if (!defined('APP_URL')) {
    define('APP_URL', getenv('APP_URL') ?: 'http://restaurante.fercupuntodeventa.com');
}
if (!defined('LOGIN_URL')) {
    define('LOGIN_URL', APP_URL . '/presentation/login.php');
}
if (!defined('DASHBOARD_URL')) {
    define('DASHBOARD_URL', APP_URL . '/dashboard.php');
}

/**
 * Enviar email con Mailgun
 *
 * @param string $to
 * @param string $toName
 * @param string $subject
 * @param string $htmlContent
 * @param string $textContent
 * @return array
 */
function sendEmail($to, $toName, $subject, $htmlContent, $textContent = '')
{
    try {
        if (MAILGUN_API_KEY === '' || MAILGUN_DOMAIN === '') {
            return [
                'success' => false,
                'message' => 'Mailgun no configurado: faltan MAILGUN_API_KEY o MAILGUN_DOMAIN'
            ];
        }

        $endpoint = MAILGUN_BASE_URL . '/v3/' . MAILGUN_DOMAIN . '/messages';
        $from = MAILGUN_FROM_NAME . ' <' . MAILGUN_FROM_EMAIL . '>';
        $toFormatted = trim($toName) !== '' ? ($toName . ' <' . $to . '>') : $to;

        $postFields = [
            'from' => $from,
            'to' => $toFormatted,
            'subject' => $subject,
            'html' => $htmlContent
        ];

        if ($textContent !== '') {
            $postFields['text'] = $textContent;
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:' . MAILGUN_API_KEY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'message' => 'Error de conexión Mailgun: ' . $curlError
            ];
        }

        $decoded = json_decode((string)$response, true);
        $messageId = $decoded['id'] ?? 'unknown';
        $messageText = $decoded['message'] ?? 'Respuesta sin detalle';

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'message' => 'Email enviado correctamente',
                'message_id' => $messageId,
                'status_code' => $httpCode
            ];
        }

        return [
            'success' => false,
            'message' => 'Error Mailgun: ' . $messageText,
            'status_code' => $httpCode
        ];
    } catch (Exception $e) {
        error_log('Error Mailgun: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

/**
 * Log de emails enviados
 */
function logEmail($to, $subject, $result)
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/emails.log';
    $timestamp = date('Y-m-d H:i:s');
    $status = !empty($result['success']) ? 'SUCCESS' : 'FAILED';
    $messageId = $result['message_id'] ?? 'N/A';
    $provider = 'MAILGUN';

    $logMessage = sprintf(
        "[%s] %s | Provider: %s | To: %s | Subject: %s | MessageID: %s | Message: %s\n",
        $timestamp,
        $status,
        $provider,
        $to,
        $subject,
        $messageId,
        $result['message'] ?? ''
    );

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
