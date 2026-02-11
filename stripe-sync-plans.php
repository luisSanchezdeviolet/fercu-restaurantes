<?php
/**
 * Script para sincronizar planes de la BD con Stripe
 * Crea productos y precios en Stripe automáticamente
 */

require_once 'config/database.php';
require_once 'config/stripe.php';

echo "🚀 Iniciando sincronización de planes con Stripe...\n\n";

try {
    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();
    
    // Obtener todos los planes activos (excepto trial)
    $stmt = $conn->prepare("
        SELECT * FROM plans 
        WHERE status = 'active' AND type != 'trial'
        ORDER BY id
    ");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($plans)) {
        echo "❌ No se encontraron planes para sincronizar\n";
        exit(1);
    }
    
    echo "📋 Encontrados " . count($plans) . " planes para sincronizar\n\n";
    
    foreach ($plans as $plan) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📦 Procesando: {$plan['name']} ({$plan['type']})\n";
        echo "   Precio: \${$plan['amount']} {$plan['currency']}\n";
        
        // Si ya tiene product_id y price_id, solo verificamos
        if (!empty($plan['stripe_product_id']) && !empty($plan['stripe_price_id'])) {
            echo "   ✅ Ya existe en Stripe\n";
            echo "      Product ID: {$plan['stripe_product_id']}\n";
            echo "      Price ID: {$plan['stripe_price_id']}\n";
            continue;
        }
        
        // 1. Crear o buscar el producto en Stripe
        $productId = $plan['stripe_product_id'];
        
        if (empty($productId)) {
            echo "   📝 Creando producto en Stripe...\n";
            
            $product = stripeRequest('products', 'POST', [
                'name' => $plan['name'],
                'description' => $plan['description'],
                'metadata' => [
                    'plan_id' => $plan['id'],
                    'system' => 'fercu_restaurante'
                ]
            ]);
            
            $productId = $product['id'];
            echo "   ✅ Producto creado: $productId\n";
            
            // Actualizar en la BD
            $updateStmt = $conn->prepare("UPDATE plans SET stripe_product_id = ? WHERE id = ?");
            $updateStmt->execute([$productId, $plan['id']]);
        }
        
        // 2. Crear el precio en Stripe
        echo "   💰 Creando precio en Stripe...\n";
        
        // Convertir a centavos
        $amountCents = (int)($plan['amount'] * 100);
        
        // Determinar intervalo
        $interval = ($plan['type'] === 'annual') ? 'year' : 'month';
        
        $price = stripeRequest('prices', 'POST', [
            'product' => $productId,
            'unit_amount' => $amountCents,
            'currency' => strtolower($plan['currency']),
            'recurring' => [
                'interval' => $interval
            ],
            'metadata' => [
                'plan_id' => $plan['id'],
                'plan_type' => $plan['type'],
                'system' => 'fercu_restaurante'
            ]
        ]);
        
        $priceId = $price['id'];
        echo "   ✅ Precio creado: $priceId\n";
        
        // Actualizar en la BD
        $updateStmt = $conn->prepare("UPDATE plans SET stripe_price_id = ? WHERE id = ?");
        $updateStmt->execute([$priceId, $plan['id']]);
        
        echo "   🎉 Plan sincronizado exitosamente\n";
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Sincronización completada exitosamente\n\n";
    
    // Mostrar resumen
    $stmt = $conn->prepare("
        SELECT id, name, type, amount, currency, stripe_product_id, stripe_price_id 
        FROM plans 
        WHERE status = 'active' AND type != 'trial'
        ORDER BY id
    ");
    $stmt->execute();
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 RESUMEN DE PLANES SINCRONIZADOS:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    foreach ($plans as $plan) {
        echo "📦 {$plan['name']}\n";
        echo "   Tipo: " . ($plan['type'] === 'monthly' ? 'Mensual' : 'Anual') . "\n";
        echo "   Precio: \${$plan['amount']} {$plan['currency']}\n";
        echo "   Product ID: {$plan['stripe_product_id']}\n";
        echo "   Price ID: {$plan['stripe_price_id']}\n";
        echo "\n";
    }
    
    echo "🎯 Próximo paso: Los usuarios ahora podrán suscribirse con pagos recurrentes\n";
    echo "💡 Consejo: Revisa los productos en tu Stripe Dashboard\n";
    echo "   https://dashboard.stripe.com/products\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>

