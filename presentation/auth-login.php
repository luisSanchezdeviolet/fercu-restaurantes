<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT u.*, c.nombre as empresa_nombre, c.activo as empresa_activa, u.is_super_admin
              FROM usuarios u 
              LEFT JOIN configuracion c ON u.configuracion_id = c.id 
              WHERE u.email = :email AND u.activo = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $user['password'])) {
            // Verificar que la empresa esté activa
            if (isset($user['empresa_activa']) && $user['empresa_activa'] == 0) {
                echo json_encode(['success' => false, 'message' => 'Tu cuenta de empresa está inactiva. Contacta al soporte.']);
                exit;
            }
            
            // Verificar suscripción activa
            if ($user['configuracion_id']) {
                $subQuery = "SELECT * FROM subscriptions 
                            WHERE configuracion_id = :config_id 
                            AND status = 1 
                            AND limit_date >= CURDATE()
                            ORDER BY id DESC LIMIT 1";
                $subStmt = $db->prepare($subQuery);
                $subStmt->bindParam(':config_id', $user['configuracion_id']);
                $subStmt->execute();
                $subscription = $subStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$subscription) {
                    echo json_encode(['success' => false, 'message' => 'Tu suscripción ha expirado. Por favor renueva tu plan.']);
                    exit;
                }
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_role'] = $user['rol'];
            $_SESSION['logged_in'] = true;
            $_SESSION['configuracion_id'] = $user['configuracion_id'];
            $_SESSION['empresa_nombre'] = $user['empresa_nombre'] ?? 'Mi Restaurante';
            $_SESSION['is_super_admin'] = $user['is_super_admin'] ?? 0;
            
            $update_query = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $user['id']);
            $update_stmt->execute();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login exitoso',
                'redirect' => 'dashboard.php'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
    
} catch(Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>