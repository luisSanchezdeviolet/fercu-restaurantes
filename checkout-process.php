<?php
session_start();
require_once 'config/database.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['configuracion_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para continuar'
    ]);
    exit;
}

// Obtener datos
$plan_id = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'pending';
$configuracion_id = $_SESSION['configuracion_id'];

if ($plan_id === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Plan no válido'
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
        echo json_encode([
            'success' => false,
            'message' => 'Plan no encontrado'
        ]);
        exit;
    }
    
    // Calcular fechas según el tipo de plan
    $start_date = date('Y-m-d');
    $limit_date = '';
    
    switch ($plan['type']) {
        case 'trial':
            $limit_date = date('Y-m-d', strtotime('+15 days'));
            break;
        case 'monthly':
            $limit_date = date('Y-m-d', strtotime('+30 days'));
            break;
        case 'annual':
            $limit_date = date('Y-m-d', strtotime('+365 days'));
            break;
        default:
            $limit_date = date('Y-m-d', strtotime('+30 days'));
    }
    
    $conn->beginTransaction();
    
    // 1. Desactivar suscripciones anteriores de esta empresa
    $stmt = $conn->prepare("UPDATE subscriptions SET status = 0 WHERE configuracion_id = ?");
    $stmt->execute([$configuracion_id]);
    
    // 2. Crear nueva suscripción
    $stmt = $conn->prepare("
        INSERT INTO subscriptions (configuracion_id, plan_id, start_date, limit_date, status, created_at)
        VALUES (?, ?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$configuracion_id, $plan_id, $start_date, $limit_date]);
    $subscription_id = $conn->lastInsertId();
    
    // 3. Crear registro de pago (pendiente por ahora)
    if ($plan['amount'] > 0) {
        $stmt = $conn->prepare("
            INSERT INTO payments (
                configuracion_id, 
                subscription_id, 
                plan_id, 
                amount, 
                currency, 
                status, 
                payment_method,
                payment_date,
                created_at
            ) VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())
        ");
        $stmt->execute([
            $configuracion_id,
            $subscription_id,
            $plan_id,
            $plan['amount'],
            $plan['currency'],
            $payment_method
        ]);
    }
    
    // 4. Registrar actividad
    $stmt = $conn->prepare("
        INSERT INTO saas_activity_log (configuracion_id, user_id, action, description, ip_address)
        VALUES (?, ?, 'subscription_created', ?, ?)
    ");
    $user_id = $_SESSION['user_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $description = "Nueva suscripción al plan: " . $plan['name'];
    $stmt->execute([$configuracion_id, $user_id, $description, $ip]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Suscripción creada exitosamente',
        'subscription_id' => $subscription_id,
        'redirect' => 'dashboard.php'
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la suscripción: ' . $e->getMessage()
    ]);
}
?>

