<?php
session_start();
require_once 'layouts/session.php';

// Redirigir al login si no está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso | Fercu Restaurante</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            text-align: center;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .success-icon i {
            font-size: 3rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        
        <h1 class="mb-3">¡Pago Exitoso!</h1>
        <p class="lead mb-4">Tu pago ha sido procesado correctamente y tu suscripción ha sido activada.</p>
        
        <div class="alert alert-success">
            <i class="bi bi-info-circle-fill"></i>
            <strong>¿Qué sigue?</strong><br>
            Ya puedes acceder a todas las funcionalidades de tu plan. Revisa tu correo para más detalles.
        </div>
        
        <div class="d-grid gap-2 mt-4">
            <a href="dashboard.php" class="btn btn-primary btn-lg">
                <i class="bi bi-speedometer2"></i> Ir al Dashboard
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Ir al Inicio
            </a>
        </div>
    </div>
</body>
</html>

