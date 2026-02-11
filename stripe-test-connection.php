#!/usr/bin/env php
<?php
/**
 * Script para probar la conexión con Stripe y listar productos
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/stripe.php';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔍 VERIFICACIÓN DE CONEXIÓN CON STRIPE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📋 Configuración Actual:\n";
echo "   Modo: " . STRIPE_MODE . "\n";
echo "   Public Key: " . substr(STRIPE_PUBLIC_KEY, 0, 20) . "...\n";
echo "   Secret Key: " . substr(STRIPE_SECRET_KEY, 0, 20) . "...\n\n";

try {
    echo "🔄 Intentando conectar con Stripe...\n\n";
    
    // Obtener información de la cuenta
    $account = \Stripe\Account::retrieve();
    
    echo "✅ CONEXIÓN EXITOSA\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 INFORMACIÓN DE LA CUENTA\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "   Account ID: " . $account->id . "\n";
    echo "   Email: " . ($account->email ?? 'N/A') . "\n";
    echo "   País: " . ($account->country ?? 'N/A') . "\n";
    echo "   Moneda: " . ($account->default_currency ?? 'N/A') . "\n\n";
    
    // Listar productos
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📦 PRODUCTOS EN STRIPE\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    $products = \Stripe\Product::all(['limit' => 100]);
    
    if (count($products->data) === 0) {
        echo "⚠️  No se encontraron productos en esta cuenta de Stripe\n";
        echo "   Esto puede significar:\n";
        echo "   - Los productos no se crearon correctamente\n";
        echo "   - Estás viendo una cuenta diferente\n";
        echo "   - Necesitas sincronizar los planes\n\n";
    } else {
        echo "✅ Se encontraron " . count($products->data) . " productos:\n\n";
        
        foreach ($products->data as $product) {
            echo "📦 " . $product->name . "\n";
            echo "   ID: " . $product->id . "\n";
            echo "   Estado: " . ($product->active ? '✅ Activo' : '❌ Inactivo') . "\n";
            
            // Obtener precios de este producto
            $prices = \Stripe\Price::all(['product' => $product->id, 'limit' => 10]);
            
            if (count($prices->data) > 0) {
                echo "   Precios:\n";
                foreach ($prices->data as $price) {
                    $amount = $price->unit_amount / 100;
                    $currency = strtoupper($price->currency);
                    $interval = $price->recurring ? $price->recurring->interval : 'one-time';
                    echo "      💰 $$amount $currency ($interval)\n";
                    echo "         Price ID: " . $price->id . "\n";
                }
            }
            
            // Verificar metadata
            if (!empty($product->metadata)) {
                echo "   Metadata:\n";
                foreach ($product->metadata as $key => $value) {
                    echo "      $key: $value\n";
                }
            }
            
            echo "\n";
        }
    }
    
    // Comparar con la base de datos local
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🗄️  PLANES EN BASE DE DATOS LOCAL\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $stmt = $conn->query("SELECT id, name, type, amount, currency, stripe_product_id, stripe_price_id FROM plans WHERE type != 'trial' ORDER BY id");
    $localPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($localPlans)) {
        echo "⚠️  No hay planes en la base de datos\n\n";
    } else {
        foreach ($localPlans as $plan) {
            echo "📋 " . $plan['name'] . "\n";
            echo "   Tipo: " . $plan['type'] . "\n";
            echo "   Precio: $" . $plan['amount'] . " " . $plan['currency'] . "\n";
            echo "   Product ID: " . ($plan['stripe_product_id'] ?: '❌ No sincronizado') . "\n";
            echo "   Price ID: " . ($plan['stripe_price_id'] ?: '❌ No sincronizado') . "\n";
            
            // Verificar si el producto existe en Stripe
            if ($plan['stripe_product_id']) {
                $existeEnStripe = false;
                foreach ($products->data as $product) {
                    if ($product->id === $plan['stripe_product_id']) {
                        $existeEnStripe = true;
                        break;
                    }
                }
                echo "   Estado: " . ($existeEnStripe ? '✅ Existe en Stripe' : '❌ NO existe en Stripe') . "\n";
            }
            
            echo "\n";
        }
    }
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🎯 DASHBOARD DE STRIPE\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    if (STRIPE_MODE === 'test') {
        echo "Accede a tu dashboard de TEST:\n";
        echo "   https://dashboard.stripe.com/test/products\n\n";
        echo "⚠️  IMPORTANTE: Asegúrate de estar en modo TEST (no LIVE)\n";
        echo "   Verifica que en la esquina superior izquierda diga \"Test mode\"\n\n";
    } else {
        echo "Accede a tu dashboard de PRODUCCIÓN:\n";
        echo "   https://dashboard.stripe.com/products\n\n";
        echo "⚠️  IMPORTANTE: Estás en modo LIVE (producción)\n";
        echo "   Los pagos serán reales\n\n";
    }
    
} catch (\Stripe\Exception\AuthenticationException $e) {
    echo "❌ ERROR DE AUTENTICACIÓN\n\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getHttpStatus() . "\n\n";
    echo "⚠️  Esto significa que tus claves de API de Stripe no son válidas\n";
    echo "   Verifica las claves en: /var/www/restaurantes/config/stripe.php\n\n";
    exit(1);
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "❌ ERROR DE API DE STRIPE\n\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getHttpStatus() . "\n\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ ERROR GENERAL\n\n";
    echo "   Mensaje: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "✅ Verificación completada\n\n";

