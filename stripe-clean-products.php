#!/usr/bin/env php
<?php
/**
 * Script para limpiar productos duplicados/antiguos en Stripe
 * Mantiene solo los 4 productos actuales del sistema de restaurantes
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/stripe.php';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🧹 LIMPIEZA DE PRODUCTOS EN STRIPE (" . strtoupper(STRIPE_MODE) . ")\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (STRIPE_MODE === 'live') {
    echo "⚠️  ¡ADVERTENCIA! Estás en modo PRODUCCIÓN\n";
    echo "   ¿Estás seguro de que quieres limpiar productos en PRODUCCIÓN? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $respuesta = trim(strtolower($line));
    fclose($handle);
    
    if ($respuesta !== 's' && $respuesta !== 'si' && $respuesta !== 'sí') {
        echo "❌ Operación cancelada\n";
        exit(0);
    }
}

try {
    // Obtener IDs de productos activos de nuestra BD
    $database = new Database();
    $conn = $database->getConnection();
    
    $stmt = $conn->query("
        SELECT stripe_product_id, name 
        FROM plans 
        WHERE stripe_product_id IS NOT NULL 
        AND status = 'active'
    ");
    $localProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $keepProductIds = array_column($localProducts, 'stripe_product_id');
    
    echo "📋 Productos a MANTENER (según base de datos local):\n";
    foreach ($localProducts as $product) {
        echo "   ✅ {$product['name']} - {$product['stripe_product_id']}\n";
    }
    echo "\n";
    
    // Obtener todos los productos de Stripe
    echo "🔍 Obteniendo productos de Stripe...\n\n";
    $allProducts = \Stripe\Product::all(['limit' => 100, 'active' => true]);
    
    $toArchive = [];
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🗑️  PRODUCTOS A ARCHIVAR:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    foreach ($allProducts->data as $product) {
        if (!in_array($product->id, $keepProductIds)) {
            $toArchive[] = $product;
            echo "❌ {$product->name}\n";
            echo "   ID: {$product->id}\n";
            
            // Mostrar precios
            $prices = \Stripe\Price::all(['product' => $product->id, 'active' => true, 'limit' => 10]);
            if (count($prices->data) > 0) {
                echo "   Precios activos: " . count($prices->data) . "\n";
            }
            echo "\n";
        }
    }
    
    if (empty($toArchive)) {
        echo "✅ No hay productos para archivar. Todo está limpio!\n\n";
        exit(0);
    }
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "⚠️  Se archivarán " . count($toArchive) . " productos\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "¿Continuar? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $respuesta = trim(strtolower($line));
    fclose($handle);
    
    if ($respuesta !== 's' && $respuesta !== 'si' && $respuesta !== 'sí' && $respuesta !== 'y' && $respuesta !== 'yes') {
        echo "\n❌ Operación cancelada\n";
        exit(0);
    }
    
    echo "\n🔄 Archivando productos...\n\n";
    
    $archived = 0;
    $errors = 0;
    
    foreach ($toArchive as $product) {
        try {
            // Primero, archivar todos los precios del producto
            $prices = \Stripe\Price::all(['product' => $product->id, 'active' => true, 'limit' => 100]);
            
            foreach ($prices->data as $price) {
                try {
                    \Stripe\Price::update($price->id, ['active' => false]);
                } catch (Exception $e) {
                    // Algunos precios pueden no ser actualizables, continuar
                }
            }
            
            // Luego, archivar el producto
            \Stripe\Product::update($product->id, ['active' => false]);
            
            echo "   ✅ Archivado: {$product->name}\n";
            $archived++;
            
        } catch (Exception $e) {
            echo "   ❌ Error al archivar {$product->name}: {$e->getMessage()}\n";
            $errors++;
        }
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ LIMPIEZA COMPLETADA\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "   Productos archivados: $archived\n";
    echo "   Errores: $errors\n";
    echo "   Productos activos restantes: " . count($keepProductIds) . "\n\n";
    
    echo "🎯 Verifica tu dashboard de Stripe:\n";
    if (STRIPE_MODE === 'test') {
        echo "   https://dashboard.stripe.com/test/products\n\n";
    } else {
        echo "   https://dashboard.stripe.com/products\n\n";
    }
    
    echo "💡 Nota: Los productos archivados no se eliminan permanentemente,\n";
    echo "   solo se ocultan. Puedes restaurarlos desde el dashboard si lo necesitas.\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

