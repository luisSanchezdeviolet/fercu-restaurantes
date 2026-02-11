#!/usr/bin/env php
<?php
/**
 * Script para cambiar entre modo TEST y LIVE en Stripe
 * 
 * Uso:
 *   php stripe-switch-mode.php test   - Cambiar a modo TEST
 *   php stripe-switch-mode.php live   - Cambiar a modo LIVE (producción)
 */

if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la línea de comandos.\n");
}

if ($argc < 2) {
    echo "❌ Error: Debes especificar el modo (test o live)\n";
    echo "\nUso:\n";
    echo "  php stripe-switch-mode.php test   - Cambiar a modo TEST\n";
    echo "  php stripe-switch-mode.php live   - Cambiar a modo LIVE (producción)\n\n";
    exit(1);
}

$mode = strtolower($argv[1]);

if ($mode !== 'test' && $mode !== 'live') {
    echo "❌ Error: Modo inválido '$mode'. Usa 'test' o 'live'\n";
    exit(1);
}

$configFile = __DIR__ . '/config/stripe.php';

if (!file_exists($configFile)) {
    echo "❌ Error: No se encontró el archivo de configuración: $configFile\n";
    exit(1);
}

echo "🔄 Cambiando Stripe a modo " . strtoupper($mode) . "...\n\n";

// Leer el archivo
$content = file_get_contents($configFile);

// Reemplazar el modo
if ($mode === 'test') {
    $content = preg_replace(
        "/define\('STRIPE_MODE', 'live'\);(.*)/",
        "define('STRIPE_MODE', 'test');$1",
        $content
    );
    $newMode = 'test';
} else {
    $content = preg_replace(
        "/define\('STRIPE_MODE', 'test'\);(.*)/",
        "define('STRIPE_MODE', 'live');$1",
        $content
    );
    $newMode = 'live';
}

// Guardar el archivo
file_put_contents($configFile, $content);

echo "✅ Modo actualizado a: " . strtoupper($newMode) . "\n\n";

// Preguntar si quiere sincronizar los planes
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "⚠️  IMPORTANTE: Los planes deben sincronizarse con Stripe\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($mode === 'live') {
    echo "⚠️  ¡CUIDADO! Estás a punto de crear productos en PRODUCCIÓN\n";
    echo "   Los pagos serán REALES y se cobrarán tarjetas reales.\n\n";
}

echo "¿Quieres sincronizar los planes ahora? (s/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$respuesta = trim(strtolower($line));
fclose($handle);

if ($respuesta === 's' || $respuesta === 'y' || $respuesta === 'yes' || $respuesta === 'si' || $respuesta === 'sí') {
    echo "\n🔄 Limpiando IDs anteriores...\n";
    
    require_once __DIR__ . '/config/database.php';
    
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $stmt = $conn->prepare("UPDATE plans SET stripe_product_id = NULL, stripe_price_id = NULL WHERE type != 'trial'");
        $stmt->execute();
        
        echo "✅ IDs limpiados\n\n";
        echo "🚀 Sincronizando planes con Stripe...\n\n";
        
        // Ejecutar el script de sincronización
        passthru('php ' . __DIR__ . '/stripe-sync-plans.php');
        
    } catch (Exception $e) {
        echo "❌ Error al limpiar IDs: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "\n⏭️  Sincronización omitida\n";
    echo "   Puedes sincronizar manualmente más tarde con:\n";
    echo "   php stripe-sync-plans.php\n\n";
}

// Limpiar caché de OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ Caché de PHP limpiado\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Cambio completado exitosamente\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($mode === 'test') {
    echo "🧪 Modo TEST activado\n";
    echo "   - Dashboard: https://dashboard.stripe.com/test/dashboard\n";
    echo "   - Productos: https://dashboard.stripe.com/test/products\n";
    echo "   - Webhooks: https://dashboard.stripe.com/test/webhooks\n";
    echo "   - Usa tarjetas de prueba: 4242 4242 4242 4242\n\n";
    echo "📖 Revisa la guía de pruebas: GUIA_PRUEBAS_STRIPE.md\n";
} else {
    echo "🚀 Modo LIVE activado\n";
    echo "   - Dashboard: https://dashboard.stripe.com/dashboard\n";
    echo "   - Productos: https://dashboard.stripe.com/products\n";
    echo "   - Webhooks: https://dashboard.stripe.com/webhooks\n";
    echo "   - ⚠️  Los pagos son REALES\n\n";
    echo "⚠️  No olvides configurar el webhook en producción\n";
}

echo "\n";

