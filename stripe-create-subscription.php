<?php
session_start();
header('Content-Type: application/json');

require_once 'config/database.php';
require_once 'config/stripe.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['configuracion_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Debes iniciar sesión para continuar'
    ]);
    exit;
}

// Leer datos JSON
$json = file_get_contents('php://input');
$datos = json_decode($json, true);

$plan_id = isset($datos['plan_id']) ? (int)$datos['plan_id'] : 0;
$payment_method_id = isset($datos['payment_method_id']) ? $datos['payment_method_id'] : null;

if ($plan_id === 0 || !$payment_method_id) {
    echo json_encode([
        'success' => false,
        'error' => 'Datos incompletos'
    ]);
    exit;
}

try {
    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();
    
    // Obtener información del plan
    $stmt = $conn->prepare("SELECT * FROM plans WHERE id = ? AND status = 'active'");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$plan) {
        throw new Exception('Plan no encontrado');
    }
    
    // No permitir suscripciones para plan de prueba
    if ($plan['type'] === 'trial' || $plan['amount'] <= 0) {
        throw new Exception('Este plan no requiere pago');
    }
    
    // Verificar que el plan tenga price_id
    if (empty($plan['stripe_price_id'])) {
        throw new Exception('Este plan no está configurado correctamente en Stripe');
    }
    
    // Obtener información del usuario y empresa
    $configuracion_id = $_SESSION['configuracion_id'];
    $stmt = $conn->prepare("
        SELECT c.nombre, c.correo, u.nombre as usuario_nombre, u.email as usuario_email
        FROM configuracion c
        INNER JOIN usuarios u ON c.id_usuario = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$configuracion_id]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$empresa) {
        throw new Exception('Empresa no encontrada');
    }
    
    // 1. Crear o buscar el Customer en Stripe
    $stmt = $conn->prepare("
        SELECT stripe_customer_id 
        FROM subscriptions 
        WHERE configuracion_id = ? AND stripe_customer_id IS NOT NULL 
        LIMIT 1
    ");
    $stmt->execute([$configuracion_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $customerId = null;
    
    if ($existing && !empty($existing['stripe_customer_id'])) {
        $customerId = $existing['stripe_customer_id'];
    } else {
        // Crear nuevo customer
        $customer = stripeRequest('customers', 'POST', [
            'email' => $empresa['correo'],
            'name' => $empresa['nombre'],
            'metadata' => [
                'configuracion_id' => $configuracion_id,
                'user_id' => $_SESSION['user_id'],
                'system' => 'fercu_restaurante'
            ]
        ]);
        $customerId = $customer['id'];
    }
    
    // 2. Adjuntar el método de pago al customer
    stripeRequest("payment_methods/$payment_method_id/attach", 'POST', [
        'customer' => $customerId
    ]);
    
    // 3. Establecer como método de pago predeterminado
    stripeRequest("customers/$customerId", 'POST', [
        'invoice_settings' => [
            'default_payment_method' => $payment_method_id
        ]
    ]);
    
    // 4. Crear la suscripción
    $subscription = stripeRequest('subscriptions', 'POST', [
        'customer' => $customerId,
        'items' => [[
            'price' => $plan['stripe_price_id']
        ]],
        'payment_behavior' => 'default_incomplete',
        'payment_settings' => [
            'save_default_payment_method' => 'on_subscription'
        ],
        'expand' => ['latest_invoice.payment_intent'],
        'metadata' => [
            'configuracion_id' => $configuracion_id,
            'plan_id' => $plan_id,
            'user_id' => $_SESSION['user_id'],
            'system' => 'fercu_restaurante'
        ]
    ]);
    
    // Obtener el client_secret del PaymentIntent
    $clientSecret = $subscription['latest_invoice']['payment_intent']['client_secret'] ?? null;
    
    if (!$clientSecret) {
        throw new Exception('No se pudo obtener el client_secret');
    }
    
    // Guardar temporalmente en la sesión
    $_SESSION['pending_subscription_id'] = $subscription['id'];
    $_SESSION['pending_customer_id'] = $customerId;
    $_SESSION['pending_plan_id'] = $plan_id;
    
    // Responder con el client_secret
    echo json_encode([
        'success' => true,
        'client_secret' => $clientSecret,
        'subscription_id' => $subscription['id'],
        'customer_id' => $customerId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

