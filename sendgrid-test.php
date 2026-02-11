#!/usr/bin/env php
<?php
/**
 * Script de prueba para SendGrid
 * Envía un email de prueba para verificar la configuración
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/sendgrid.php';
require_once __DIR__ . '/config/email-templates.php';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📧 PRUEBA DE SENDGRID - FERCU RESTAURANTE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📋 Configuración actual:\n";
echo "   API Key: " . substr(SENDGRID_API_KEY, 0, 20) . "...\n";
echo "   From Email: " . SENDGRID_FROM_EMAIL . "\n";
echo "   From Name: " . SENDGRID_FROM_NAME . "\n\n";

// Solicitar email de prueba
echo "Ingresa el email de destino para la prueba: ";
$handle = fopen("php://stdin", "r");
$to_email = trim(fgets($handle));
fclose($handle);

if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
    echo "\n❌ Email no válido\n";
    exit(1);
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📬 SELECCIONA EL TIPO DE EMAIL A PROBAR:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "1. Email de bienvenida con prueba gratuita (15 días)\n";
echo "2. Email de bienvenida con suscripción de pago\n";
echo "3. Email de confirmación de pago\n\n";
echo "Selecciona una opción (1-3): ";

$handle = fopen("php://stdin", "r");
$opcion = trim(fgets($handle));
fclose($handle);

$subject = '';
$htmlContent = '';

switch ($opcion) {
    case '1':
        echo "\n✅ Generando email de PRUEBA GRATUITA...\n";
        $subject = '🎉 ¡Bienvenido a Fercu Restaurante! - Prueba Gratuita de 15 Días';
        $htmlContent = getWelcomeTrialEmailHTML(
            'Restaurante Demo',
            'Usuario de Prueba',
            $to_email,
            'Demo123456',
            date('d/m/Y', strtotime('+15 days'))
        );
        break;
        
    case '2':
        echo "\n✅ Generando email de SUSCRIPCIÓN DE PAGO...\n";
        $subject = '🎉 ¡Bienvenido a Fercu Restaurante! - Suscripción Activada';
        $htmlContent = getWelcomeSubscriptionEmailHTML(
            'Restaurante Demo',
            'Usuario de Prueba',
            $to_email,
            'Demo123456',
            'Plan Professional',
            '899.00',
            'monthly',
            date('d/m/Y', strtotime('+30 days'))
        );
        break;
        
    case '3':
        echo "\n✅ Generando email de CONFIRMACIÓN DE PAGO...\n";
        $subject = '✅ Pago Recibido - Fercu Restaurante';
        $htmlContent = getPaymentConfirmationEmailHTML(
            'Restaurante Demo',
            '899.00',
            'MXN',
            'Plan Professional',
            date('d/m/Y', strtotime('+30 days'))
        );
        break;
        
    default:
        echo "\n❌ Opción no válida\n";
        exit(1);
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📤 ENVIANDO EMAIL...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "   Destinatario: $to_email\n";
echo "   Asunto: $subject\n\n";

try {
    $result = sendEmail(
        $to_email,
        'Usuario de Prueba',
        $subject,
        $htmlContent
    );
    
    // Log del resultado
    logEmail($to_email, $subject, $result);
    
    if ($result['success']) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "✅ EMAIL ENVIADO EXITOSAMENTE\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "   Message ID: " . ($result['message_id'] ?? 'N/A') . "\n";
        echo "   Status Code: " . ($result['status_code'] ?? 'N/A') . "\n";
        echo "   Mensaje: " . $result['message'] . "\n\n";
        echo "📬 Revisa la bandeja de entrada de: $to_email\n";
        echo "💡 Si no lo ves, revisa la carpeta de SPAM\n\n";
    } else {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "❌ ERROR AL ENVIAR EMAIL\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "   Status Code: " . ($result['status_code'] ?? 'N/A') . "\n";
        echo "   Error: " . $result['message'] . "\n\n";
        
        echo "🔍 POSIBLES CAUSAS:\n";
        echo "   1. API Key inválida o expirada\n";
        echo "   2. Email remitente no verificado en SendGrid\n";
        echo "   3. Cuenta de SendGrid suspendida o con límites alcanzados\n";
        echo "   4. Error de red o conectividad\n\n";
        
        echo "💡 SOLUCIONES:\n";
        echo "   1. Verifica tu API Key en: https://app.sendgrid.com/settings/api_keys\n";
        echo "   2. Verifica tu sender en: https://app.sendgrid.com/settings/sender_auth\n";
        echo "   3. Revisa los logs: tail -20 /var/www/restaurantes/logs/emails.log\n\n";
    }
    
} catch (Exception $e) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "❌ EXCEPCIÓN AL ENVIAR EMAIL\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 LOG DE EMAILS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$logFile = __DIR__ . '/logs/emails.log';
if (file_exists($logFile)) {
    echo "Últimas 5 líneas del log:\n\n";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -5);
    foreach ($lastLines as $line) {
        echo "   " . $line;
    }
    echo "\n";
    echo "Ver log completo: tail -f /var/www/restaurantes/logs/emails.log\n\n";
} else {
    echo "⚠️  El archivo de log aún no existe\n\n";
}

echo "✅ Prueba completada\n\n";
?>


