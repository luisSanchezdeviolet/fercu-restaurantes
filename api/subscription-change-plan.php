<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../layouts/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/SubscriptionController.php';

// Verificar autenticación
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$configuracion_id = $_SESSION['configuracion_id'];

// Leer datos
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$new_plan_id = $data['new_plan_id'] ?? null;
$stripe_subscription_id = $data['stripe_subscription_id'] ?? null;

if (!$new_plan_id || !$stripe_subscription_id) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    $controller = new SubscriptionController($conn);
    
    $result = $controller->changePlan($configuracion_id, $new_plan_id, $stripe_subscription_id);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>


