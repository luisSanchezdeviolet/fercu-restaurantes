<?php
header('Content-Type: application/json');

// Incluir archivos necesarios
require_once 'config/database.php';

// Verificar que sea un POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'type' => 'error',
        'msg' => 'Método no permitido'
    ]);
    exit;
}

// Obtener datos del POST
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$clave = isset($_POST['clave']) ? $_POST['clave'] : '';

// Validar campos
if (empty($correo) || empty($clave)) {
    echo json_encode([
        'type' => 'warning',
        'msg' => 'El correo y la contraseña son requeridos'
    ]);
    exit;
}

try {
    // Crear conexión a la base de datos
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Error al conectar con la base de datos');
    }
    
    // Buscar usuario por correo
    $stmt = $conn->prepare("
        SELECT u.*, c.nombre as empresa_nombre, c.activo as empresa_activa
        FROM usuarios u
        LEFT JOIN configuracion c ON u.configuracion_id = c.id
        WHERE u.email = ? AND u.activo = 1
    ");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode([
            'type' => 'warning',
            'msg' => 'El correo no existe o el usuario está inactivo'
        ]);
        exit;
    }
    
    // Verificar contraseña
    if (!password_verify($clave, $usuario['password'])) {
        echo json_encode([
            'type' => 'warning',
            'msg' => 'Contraseña incorrecta'
        ]);
        exit;
    }
    
    // Verificar que la empresa esté activa
    if (isset($usuario['empresa_activa']) && $usuario['empresa_activa'] == 0) {
        echo json_encode([
            'type' => 'warning',
            'msg' => 'Tu cuenta de empresa está inactiva. Contacta al soporte.'
        ]);
        exit;
    }
    
    // Verificar suscripción activa
    if ($usuario['configuracion_id']) {
        $stmtSub = $conn->prepare("
            SELECT * FROM subscriptions 
            WHERE configuracion_id = ? 
            AND status = 1 
            AND limit_date >= CURDATE()
            ORDER BY id DESC LIMIT 1
        ");
        $stmtSub->execute([$usuario['configuracion_id']]);
        $subscription = $stmtSub->fetch(PDO::FETCH_ASSOC);
        
        if (!$subscription) {
            echo json_encode([
                'type' => 'warning',
                'msg' => 'Tu suscripción ha expirado. Por favor renueva tu plan.'
            ]);
            exit;
        }
    }
    
    // Iniciar sesión
    session_start();
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_name'] = $usuario['nombre'];
    $_SESSION['user_email'] = $usuario['email'];
    $_SESSION['user_role'] = $usuario['rol'];
    $_SESSION['configuracion_id'] = $usuario['configuracion_id'];
    $_SESSION['empresa_nombre'] = $usuario['empresa_nombre'] ?? 'Mi Restaurante';
    
    // Actualizar último login
    $updateLogin = $conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
    $updateLogin->execute([$usuario['id']]);
    
    // Respuesta exitosa
    echo json_encode([
        'type' => 'success',
        'msg' => '¡Bienvenido! Redirigiendo...'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'type' => 'error',
        'msg' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>


