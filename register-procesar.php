<?php
header('Content-Type: application/json');

// Incluir archivos necesarios
require_once 'config/database.php';

// Leer datos JSON del cuerpo de la solicitud
$json = file_get_contents('php://input');
$datos = json_decode($json, true);

// Validar datos recibidos
if (empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['empresa']) || empty($datos['correo']) || empty($datos['telefono'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Todos los campos marcados con * son obligatorios'
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
    
    // Limpiar y preparar datos
    $nombre = trim($datos['nombre']);
    $apellido = trim($datos['apellido']);
    $empresa = trim($datos['empresa']);
    $correo = trim(strtolower($datos['correo']));
    $telefono = trim($datos['telefono']);
    $giro = isset($datos['giro']) ? trim($datos['giro']) : '';
    $empleados = isset($datos['empleados']) ? trim($datos['empleados']) : '';
    
    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }
    
    // Verificar si el correo ya existe
    $checkEmail = $conn->prepare("SELECT id, configuracion_id FROM usuarios WHERE email = ?");
    $checkEmail->execute([$correo]);
    $existingUser = $checkEmail->fetch(PDO::FETCH_ASSOC);
    
    if ($existingUser) {
        throw new Exception('Este correo electrónico ya está registrado. Si olvidaste tu contraseña, usa la opción de recuperación.');
    }
    
    // VALIDACIÓN CRÍTICA: Verificar si este correo ya usó la prueba gratuita antes
    $plan_id_solicitado = isset($datos['plan_id']) ? (int)$datos['plan_id'] : 1;
    
    if ($plan_id_solicitado == 1) {
        // Verificar si este correo ALGUNA VEZ tuvo una suscripción de prueba gratuita
        $checkTrialHistory = $conn->prepare("
            SELECT COUNT(*) as trial_count
            FROM subscriptions s
            INNER JOIN configuracion c ON s.configuracion_id = c.id
            WHERE c.correo = ? AND s.plan_id = 1
        ");
        $checkTrialHistory->execute([$correo]);
        $trialCount = $checkTrialHistory->fetch(PDO::FETCH_ASSOC)['trial_count'];
        
        if ($trialCount > 0) {
            // Este correo ya usó la prueba gratuita antes
            throw new Exception(
                'Este correo electrónico ya ha utilizado la prueba gratuita anteriormente. ' .
                'Por favor selecciona uno de nuestros planes de pago para continuar. ' .
                'Planes disponibles: Básico ($399/mes) o Professional ($899/mes).'
            );
        }
    }
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    // 1. Crear la configuración (empresa)
    $insertConfig = $conn->prepare("
        INSERT INTO configuracion (
            nombre, 
            telefono, 
            correo, 
            giro, 
            empleados,
            created_at
        ) VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $insertConfig->execute([
        $empresa,
        $telefono,
        $correo,
        $giro,
        $empleados
    ]);
    
    $configuracion_id = $conn->lastInsertId();
    
    if (!$configuracion_id) {
        throw new Exception('Error al crear la configuración de la empresa');
    }
    
    // 2. Generar contraseña temporal
    $password_temporal = 'Restaurante' . rand(1000, 9999);
    $password_hash = password_hash($password_temporal, PASSWORD_DEFAULT);
    
    // 3. Crear usuario administrador
    $insertUser = $conn->prepare("
        INSERT INTO usuarios (
            nombre,
            email,
            password,
            rol,
            activo,
            configuracion_id,
            fecha_creacion
        ) VALUES (?, ?, ?, 'Administrador', 1, ?, NOW())
    ");
    
    $nombre_completo = $nombre . ' ' . $apellido;
    $insertUser->execute([
        $nombre_completo,
        $correo,
        $password_hash,
        $configuracion_id
    ]);
    
    $usuario_id = $conn->lastInsertId();
    
    if (!$usuario_id) {
        throw new Exception('Error al crear el usuario');
    }
    
    // 4. Actualizar la configuración con el id del usuario creador
    $updateConfig = $conn->prepare("UPDATE configuracion SET id_usuario = ? WHERE id = ?");
    $updateConfig->execute([$usuario_id, $configuracion_id]);
    
    // 5. Determinar el plan a asignar (ya validado arriba)
    $plan_id = $plan_id_solicitado;
    
    // Validar que el plan existe y está activo
    $checkPlan = $conn->prepare("SELECT * FROM plans WHERE id = ? AND status = 'active'");
    $checkPlan->execute([$plan_id]);
    $plan = $checkPlan->fetch(PDO::FETCH_ASSOC);
    
    if (!$plan) {
        throw new Exception('El plan seleccionado no está disponible en este momento.');
    }
    
    // Calcular fechas según el tipo de plan
    $fecha_inicio = date('Y-m-d');
    $fecha_fin = '';
    
    switch ($plan['type']) {
        case 'trial':
            $fecha_fin = date('Y-m-d', strtotime('+15 days'));
            break;
        case 'monthly':
            $fecha_fin = date('Y-m-d', strtotime('+30 days'));
            break;
        case 'annual':
            $fecha_fin = date('Y-m-d', strtotime('+365 days'));
            break;
        default:
            $fecha_fin = date('Y-m-d', strtotime('+15 days'));
    }
    
    // Crear suscripción con el plan seleccionado
    $insertSuscripcion = $conn->prepare("
        INSERT INTO subscriptions (
            configuracion_id,
            plan_id,
            start_date,
            limit_date,
            status,
            created_at
        ) VALUES (?, ?, ?, ?, 1, NOW())
    ");
    
    $insertSuscripcion->execute([
        $configuracion_id,
        $plan_id,
        $fecha_inicio,
        $fecha_fin
    ]);
    
    // Commit de la transacción
    $conn->commit();
    
    // ✅ Enviar correo electrónico con credenciales
    $correo_enviado = false;
    $correo_mensaje = '';
    
    try {
        require_once 'config/sendgrid.php';
        require_once 'config/email-templates.php';
        
        $fecha_expiracion_formatted = date('d/m/Y', strtotime($fecha_fin));
        
        // Determinar qué tipo de email enviar según el plan
        if ($plan['type'] === 'trial') {
            // Email de bienvenida con prueba gratuita
            $subject = '🎉 ¡Bienvenido a Fercu Restaurante! - Prueba Gratuita de 15 Días';
            $htmlContent = getWelcomeTrialEmailHTML(
                $empresa,
                $nombre . ' ' . $apellido,
                $correo,
                $password_temporal,
                $fecha_expiracion_formatted
            );
        } else {
            // Email de bienvenida con suscripción de pago
            $subject = '🎉 ¡Bienvenido a Fercu Restaurante! - Suscripción Activada';
            $htmlContent = getWelcomeSubscriptionEmailHTML(
                $empresa,
                $nombre . ' ' . $apellido,
                $correo,
                $password_temporal,
                $plan['name'],
                number_format($plan['amount'], 2),
                $plan['type'],
                $fecha_expiracion_formatted
            );
        }
        
        // Enviar email
        $result = sendEmail(
            $correo,
            $nombre . ' ' . $apellido,
            $subject,
            $htmlContent
        );
        
        // Log del resultado
        logEmail($correo, $subject, $result);
        
        if ($result['success']) {
            $correo_enviado = true;
            $correo_mensaje = 'Hemos enviado un correo con tus credenciales de acceso a ' . $correo;
        } else {
            $correo_mensaje = 'No pudimos enviar el correo. Tus credenciales son: Email: ' . $correo . ', Password: ' . $password_temporal;
        }
        
    } catch (Exception $e) {
        // No fallar si no se puede enviar el correo
        error_log("Error al enviar correo de bienvenida: " . $e->getMessage());
        $correo_mensaje = 'Registro exitoso. Tus credenciales son: Email: ' . $correo . ', Password: ' . $password_temporal;
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Registro completado exitosamente',
        'data' => [
            'usuario_id' => $usuario_id,
            'configuracion_id' => $configuracion_id,
            'correo' => $correo,
            'password_temporal' => $password_temporal, // Solo para desarrollo, remover en producción
            'plan_id' => $plan_id,
            'plan_nombre' => $plan['name'],
            'plan_tipo' => $plan['type'],
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ],
        'correo_enviado' => $correo_enviado,
        'correo_mensaje' => $correo_mensaje
    ]);
    
} catch (Exception $e) {
    // Rollback en caso de error
    if ($conn && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>


