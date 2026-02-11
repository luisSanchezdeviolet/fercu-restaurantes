#!/usr/bin/env php
<?php
/**
 * Verificación rápida de API Key de SendGrid
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/sendgrid.php';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 VERIFICACIÓN DE SENDGRID API KEY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📋 Configuración:\n";
echo "   API Key: " . substr(SENDGRID_API_KEY, 0, 20) . "...\n";
echo "   From: " . SENDGRID_FROM_EMAIL . "\n\n";

echo "🔄 Verificando conexión con SendGrid...\n\n";

try {
    $sendgrid = new \SendGrid(SENDGRID_API_KEY);
    
    // Intentar obtener información de la API
    // Usamos un endpoint que no envía email pero valida la key
    $response = $sendgrid->client->_('scopes')->get();
    
    if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "✅ API KEY VÁLIDA Y FUNCIONANDO\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "   Status Code: " . $response->statusCode() . "\n";
        echo "   La API Key tiene los permisos correctos\n\n";
        
        // Obtener scopes (permisos)
        $body = json_decode($response->body(), true);
        if (isset($body['scopes']) && is_array($body['scopes'])) {
            echo "🔐 Permisos de la API Key:\n";
            $mail_send_found = false;
            foreach ($body['scopes'] as $scope) {
                if (strpos($scope, 'mail.send') !== false) {
                    echo "   ✅ " . $scope . " (necesario para enviar emails)\n";
                    $mail_send_found = true;
                } else {
                    echo "   • " . $scope . "\n";
                }
            }
            
            if (!$mail_send_found) {
                echo "\n⚠️  ADVERTENCIA: No se encontró el permiso 'mail.send'\n";
                echo "   Esta API Key podría no tener permisos para enviar emails.\n";
            }
            echo "\n";
        }
        
        echo "🎯 Siguiente paso: Probar envío real\n";
        echo "   php sendgrid-test.php\n\n";
        
    } else {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "❌ API KEY INVÁLIDA O CON PROBLEMAS\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "   Status Code: " . $response->statusCode() . "\n";
        echo "   Body: " . $response->body() . "\n\n";
        
        echo "💡 POSIBLES CAUSAS:\n";
        echo "   1. API Key incorrecta o expirada\n";
        echo "   2. API Key revocada\n";
        echo "   3. Permisos insuficientes\n\n";
        
        echo "🔧 SOLUCIÓN:\n";
        echo "   1. Ve a: https://app.sendgrid.com/settings/api_keys\n";
        echo "   2. Verifica que la API Key existe y está activa\n";
        echo "   3. Si no, crea una nueva con permisos de 'Mail Send'\n\n";
    }
    
} catch (\SendGrid\Mail\TypeException $e) {
    echo "❌ Error de tipo: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "✅ Verificación completada\n\n";
?>


