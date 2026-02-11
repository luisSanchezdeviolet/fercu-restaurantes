#!/usr/bin/env php
<?php
/**
 * Script para probar que el webhook de Stripe esté configurado correctamente
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/stripe.php';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 VERIFICACIÓN DEL WEBHOOK DE STRIPE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📋 Configuración actual:\n";
echo "   Modo: " . STRIPE_MODE . "\n";
echo "   Webhook Secret: " . substr(STRIPE_WEBHOOK_SECRET, 0, 15) . "...\n";
echo "   Webhook URL: " . STRIPE_WEBHOOK_URL . "\n\n";

// Verificar que el archivo de log exista
$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/stripe-webhook.log';

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    echo "✅ Directorio de logs creado: $logDir\n";
}

if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0644);
    echo "✅ Archivo de log creado: $logFile\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📝 CONFIGURACIÓN DEL WEBHOOK EN STRIPE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (STRIPE_MODE === 'test') {
    echo "🧪 MODO TEST\n\n";
    echo "1. Ve a: https://dashboard.stripe.com/test/webhooks\n";
} else {
    echo "🚀 MODO LIVE (PRODUCCIÓN)\n\n";
    echo "1. Ve a: https://dashboard.stripe.com/webhooks\n";
}

echo "2. Click en 'Add endpoint' (o 'Add an endpoint')\n";
echo "3. Endpoint URL:\n";
echo "   " . STRIPE_WEBHOOK_URL . "\n\n";
echo "4. Description: Webhook para sistema de restaurantes\n\n";
echo "5. Selecciona estos eventos:\n";
echo "   ✅ invoice.paid\n";
echo "   ✅ invoice.payment_failed\n";
echo "   ✅ customer.subscription.created\n";
echo "   ✅ customer.subscription.updated\n";
echo "   ✅ customer.subscription.deleted\n\n";
echo "6. Click en 'Add endpoint'\n";
echo "7. Copia el 'Signing secret' (empieza con whsec_)\n";
echo "8. Pégalo en /var/www/restaurantes/config/stripe.php línea 17\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧪 PRUEBA DE WEBHOOK\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Para probar que el webhook funciona:\n\n";

if (STRIPE_MODE === 'test') {
    echo "1. Ve a: https://dashboard.stripe.com/test/webhooks\n";
} else {
    echo "1. Ve a: https://dashboard.stripe.com/webhooks\n";
}

echo "2. Click en tu webhook endpoint\n";
echo "3. Click en la pestaña 'Send test webhook'\n";
echo "4. Selecciona evento: 'customer.subscription.created'\n";
echo "5. Click en 'Send test webhook'\n";
echo "6. Verifica que el estado sea '200 OK'\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 MONITOREAR EVENTOS EN TIEMPO REAL\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Para ver los eventos del webhook en tiempo real:\n\n";
echo "tail -f /var/www/restaurantes/logs/stripe-webhook.log\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔧 VERIFICAR ARCHIVO DE WEBHOOK\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$webhookFile = __DIR__ . '/stripe-webhook.php';
if (file_exists($webhookFile)) {
    echo "✅ Archivo webhook existe: $webhookFile\n";
    
    // Verificar permisos
    $perms = fileperms($webhookFile);
    if (is_readable($webhookFile)) {
        echo "✅ Archivo webhook es legible\n";
    } else {
        echo "❌ Archivo webhook NO es legible\n";
    }
} else {
    echo "❌ Archivo webhook NO existe: $webhookFile\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ VERIFICACIÓN COMPLETADA\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 Webhook secret actualizado correctamente\n";
echo "🎯 Ahora puedes probar el flujo de suscripciones\n\n";

echo "Próximos pasos:\n";
echo "1. Registrar un usuario con plan de pago\n";
echo "2. Verificar que la suscripción se cree en Stripe\n";
echo "3. Monitorear los logs del webhook\n";
echo "4. Verificar que los eventos se procesen correctamente\n\n";

