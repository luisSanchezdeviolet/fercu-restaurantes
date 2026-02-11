<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, device-width=1.0">
    <title>Pago Cancelado | Fercu Restaurante</title>
    
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
        
        .cancel-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            text-align: center;
        }
        
        .cancel-icon {
            width: 100px;
            height: 100px;
            background: #ffc107;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }
        
        .cancel-icon i {
            font-size: 3rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="cancel-card">
        <div class="cancel-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        
        <h1 class="mb-3">Pago Cancelado</h1>
        <p class="lead mb-4">Has cancelado el proceso de pago. No se ha realizado ningún cargo.</p>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill"></i>
            ¿Cambiaste de opinión? Puedes intentar de nuevo cuando quieras.
        </div>
        
        <div class="d-grid gap-2 mt-4">
            <a href="index.php#planes" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-left"></i> Ver Planes Nuevamente
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Ir al Inicio
            </a>
        </div>
    </div>
</body>
</html>

