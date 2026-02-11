<?php
header('Content-Type: application/json');

// Incluir archivos necesarios
require_once 'config/database.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Leer datos JSON del cuerpo de la solicitud
$json = file_get_contents('php://input');
$datos = json_decode($json, true);

// Validar que se recibió un correo
if (empty($datos['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email requerido'
    ]);
    exit;
}

try {
    $correo = trim(strtolower($datos['email']));
    
    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'eligible' => false,
            'message' => 'Formato de correo no válido'
        ]);
        exit;
    }
    
    // Crear conexión a la base de datos
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Error al conectar con la base de datos');
    }
    
    // Verificar si este correo ya usó la prueba gratuita
    $checkTrialHistory = $conn->prepare("
        SELECT COUNT(*) as trial_count
        FROM subscriptions s
        INNER JOIN configuracion c ON s.configuracion_id = c.id
        WHERE c.correo = ? AND s.plan_id = 1
    ");
    $checkTrialHistory->execute([$correo]);
    $trialCount = $checkTrialHistory->fetch(PDO::FETCH_ASSOC)['trial_count'];
    
    if ($trialCount > 0) {
        // Este correo YA usó la prueba gratuita
        echo json_encode([
            'success' => true,
            'eligible' => false,
            'message' => 'Este correo ya ha utilizado la prueba gratuita anteriormente.',
            'recommendation' => 'Por favor selecciona uno de nuestros planes de pago: Básico ($399/mes) o Professional ($899/mes).'
        ]);
    } else {
        // Este correo SÍ puede usar la prueba gratuita
        echo json_encode([
            'success' => true,
            'eligible' => true,
            'message' => 'Este correo es elegible para la prueba gratuita de 15 días.'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'eligible' => false,
        'message' => 'Error al verificar elegibilidad: ' . $e->getMessage()
    ]);
}
?>

