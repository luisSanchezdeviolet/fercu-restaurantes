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

if ($plan_id === 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Plan no válido'
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
    
    // No permitir pagos para plan de prueba
    if ($plan['type'] === 'trial' || $plan['amount'] <= 0) {
        throw new Exception('Este plan no requiere pago');
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
    
    // Calcular el monto (incluir IVA 16%)
    $subtotal = $plan['amount'];
    $iva = $subtotal * 0.16;
    $total = $subtotal + $iva;
    
    // Convertir a centavos (Stripe requiere el monto en la unidad más pequeña)
    $amount_cents = (int)($total * 100);
    
    // Crear el PaymentIntent en Stripe
    $paymentIntent = stripeRequest('payment_intents', 'POST', [
        'amount' => $amount_cents,
        'currency' => STRIPE_CURRENCY,
        'description' => $plan['name'] . ' - ' . $empresa['nombre'],
        'metadata' => [
            'plan_id' => $plan_id,
            'plan_name' => $plan['name'],
            'configuracion_id' => $configuracion_id,
            'empresa_nombre' => $empresa['nombre'],
            'user_id' => $_SESSION['user_id']
        ],
        'receipt_email' => $empresa['correo']
    ]);
    
    // Guardar el payment_intent_id temporalmente
    $_SESSION['pending_payment_intent'] = $paymentIntent['id'];
    $_SESSION['pending_plan_id'] = $plan_id;
    
    // Responder con el client_secret
    echo json_encode([
        'success' => true,
        'client_secret' => $paymentIntent['client_secret'],
        'payment_intent_id' => $paymentIntent['id'],
        'amount' => $total,
        'currency' => $plan['currency']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

