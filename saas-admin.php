<?php
require_once 'layouts/session.php';
requireLogin();

// Verificar que sea super admin
$userData = getUserData();
if (!isset($userData['is_super_admin']) || $userData['is_super_admin'] != 1) {
    header('Location: dashboard.php');
    exit;
}

require_once 'controllers/SaasAdminController.php';
$controller = new SaasAdminController();

// Obtener estadísticas
$stats = $controller->getDashboardStats();

// Obtener parámetros de búsqueda
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Obtener empresas
$companies = $controller->getAllCompanies($page, 20, $search, $status);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel SAAS - Fercu Restaurante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .saas-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .stat-card {
            border-left: 4px solid #667eea;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .company-card {
            transition: all 0.2s;
        }
        .company-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-active {
            background-color: #28a745;
        }
        .badge-inactive {
            background-color: #dc3545;
        }
        .badge-expiring {
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="saas-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-speedometer2"></i> Panel de Administración SAAS</h1>
                    <p class="mb-0">Gestión de empresas y suscripciones</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Volver al Dashboard
                    </a>
                    <a href="presentation/logout.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($stats['success']): ?>
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Empresas Totales</h6>
                                <h2 class="mb-0"><?php echo $stats['data']['total_companies']; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-building text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Empresas Activas</h6>
                                <h2 class="mb-0 text-success"><?php echo $stats['data']['active_companies']; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Suscripciones Activas</h6>
                                <h2 class="mb-0 text-info"><?php echo $stats['data']['active_subscriptions']; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-bookmark-star text-info" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Por Vencer (7 días)</h6>
                                <h2 class="mb-0 text-warning"><?php echo $stats['data']['expiring_soon']; ?></h2>
                            </div>
                            <div>
                                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-cash-stack"></i> Ingresos del Mes</h5>
                        <h2 class="text-success">$<?php echo number_format($stats['data']['monthly_revenue'], 2); ?> MXN</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-graph-up"></i> Nuevos Registros este Mes</h5>
                        <h2 class="text-primary"><?php echo $stats['data']['new_this_month']; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filtros y búsqueda -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" name="search" placeholder="Buscar por nombre, email o teléfono..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Todos los estados</option>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Activos</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de empresas -->
        <?php if ($companies['success']): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building"></i> Empresas Registradas (<?php echo $companies['pagination']['total']; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Propietario</th>
                                <th>Contacto</th>
                                <th>Plan Actual</th>
                                <th>Vencimiento</th>
                                <th>Usuarios</th>
                                <th>Mesas</th>
                                <th>Productos</th>
                                <th>Órdenes</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies['data'] as $company): ?>
                            <tr>
                                <td><?php echo $company['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($company['nombre']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($company['giro'] ?? 'N/A'); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($company['owner_name'] ?? 'N/A'); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($company['owner_email'] ?? 'N/A'); ?></small>
                                </td>
                                <td>
                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($company['correo']); ?><br>
                                    <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($company['telefono']); ?>
                                </td>
                                <td>
                                    <?php if ($company['plan_name']): ?>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($company['plan_name']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sin plan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($company['limit_date']): ?>
                                        <?php 
                                        $daysLeft = (strtotime($company['limit_date']) - time()) / 86400;
                                        $badgeClass = $daysLeft > 7 ? 'bg-success' : ($daysLeft > 0 ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo date('d/m/Y', strtotime($company['limit_date'])); ?>
                                        </span>
                                        <br><small class="text-muted"><?php echo (int)$daysLeft; ?> días</small>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo $company['total_users']; ?></td>
                                <td class="text-center"><?php echo $company['total_tables']; ?></td>
                                <td class="text-center"><?php echo $company['total_products']; ?></td>
                                <td class="text-center"><?php echo $company['total_orders']; ?></td>
                                <td>
                                    <?php if ($company['activo']): ?>
                                        <span class="badge badge-active">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="saas-company-detail.php?id=<?php echo $company['id']; ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-<?php echo $company['activo'] ? 'warning' : 'success'; ?> toggle-status" 
                                            data-id="<?php echo $company['id']; ?>" 
                                            data-status="<?php echo $company['activo'] ? '0' : '1'; ?>"
                                            title="<?php echo $company['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                        <i class="bi bi-<?php echo $company['activo'] ? 'x-circle' : 'check-circle'; ?>"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($companies['pagination']['total_pages'] > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination mb-0 justify-content-center">
                        <?php for ($i = 1; $i <= $companies['pagination']['total_pages']; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-danger">
            Error al cargar las empresas: <?php echo htmlspecialchars($companies['error']); ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle status de empresa
        document.querySelectorAll('.toggle-status').forEach(btn => {
            btn.addEventListener('click', function() {
                const companyId = this.dataset.id;
                const newStatus = this.dataset.status;
                const statusText = newStatus === '1' ? 'activar' : 'desactivar';
                
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Deseas ${statusText} esta empresa?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Aquí se puede hacer una llamada AJAX para cambiar el estado
                        window.location.href = `saas-toggle-status.php?id=${companyId}&status=${newStatus}`;
                    }
                });
            });
        });
    </script>
</body>
</html>

