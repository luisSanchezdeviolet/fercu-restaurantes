<?php
require_once 'layouts/session.php';
requireLogin();

// Verificar que sea super admin
if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit;
}

require_once 'controllers/SaasAdminController.php';
$controller = new SaasAdminController();

$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$details = $controller->getCompanyDetails($companyId);

if (!$details['success']) {
    header('Location: saas-admin.php');
    exit;
}

$company = $details['data']['company'];
$subscriptions = $details['data']['subscriptions'];
$users = $details['data']['users'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Empresa - <?php echo htmlspecialchars($company['nombre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-building"></i> <?php echo htmlspecialchars($company['nombre']); ?></h1>
            <a href="saas-admin.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="row">
            <!-- Información de la empresa -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Información de la Empresa</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">ID:</th>
                                <td><?php echo $company['id']; ?></td>
                            </tr>
                            <tr>
                                <th>Nombre:</th>
                                <td><?php echo htmlspecialchars($company['nombre']); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($company['correo']); ?></td>
                            </tr>
                            <tr>
                                <th>Teléfono:</th>
                                <td><?php echo htmlspecialchars($company['telefono']); ?></td>
                            </tr>
                            <tr>
                                <th>Tipo de Cocina:</th>
                                <td><?php echo htmlspecialchars($company['giro'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Empleados:</th>
                                <td><?php echo htmlspecialchars($company['empleados'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Fecha de Registro:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($company['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Estado:</th>
                                <td>
                                    <?php if ($company['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Información del propietario -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Propietario</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Nombre:</th>
                                <td><?php echo htmlspecialchars($company['owner_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($company['owner_email'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Último Login:</th>
                                <td>
                                    <?php 
                                    if ($company['owner_last_login']) {
                                        echo date('d/m/Y H:i', strtotime($company['owner_last_login']));
                                    } else {
                                        echo 'Nunca';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suscripciones -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Historial de Suscripciones</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Inicio</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Método de Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscriptions as $sub): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sub['plan_name']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($sub['plan_type']); ?></span></td>
                                <td>$<?php echo number_format($sub['amount'], 2); ?> MXN</td>
                                <td><?php echo date('d/m/Y', strtotime($sub['start_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($sub['limit_date'])); ?></td>
                                <td>
                                    <?php if ($sub['status']): ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($sub['payment_method']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Usuarios -->
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0">Usuarios de la Empresa</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha de Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($user['rol']); ?></span></td>
                                <td>
                                    <?php if ($user['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['fecha_creacion'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

