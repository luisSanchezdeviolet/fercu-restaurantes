<?php
require_once 'layouts/session.php';
requireLogin();

if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit;
}

require_once 'controllers/SaasAdminController.php';
$controller = new SaasAdminController();
$plans = $controller->getAllPlans();
?>
<?php include 'layouts/main.php'; ?>
<head>
    <title>Planes SaaS | Fercu Restaurante</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>
<body>
    <div class="wrapper">
        <?php include 'layouts/menu.php'; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="saas-admin.php">SaaS</a></li>
                                        <li class="breadcrumb-item active">Planes</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Planes SaaS</h4>
                            </div>
                        </div>
                    </div>

                    <?php if (!$plans['success']): ?>
                        <div class="alert alert-danger">Error al cargar planes: <?php echo htmlspecialchars($plans['error']); ?></div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-1">Planes comerciales</h4>
                                <p class="text-muted mb-3">Vista de planes, precios, límites y sincronización con Stripe.</p>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Plan</th>
                                                <th>Tipo</th>
                                                <th>Precio</th>
                                                <th>Límites</th>
                                                <th>Estado</th>
                                                <th>Stripe</th>
                                                <th>Actualizado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($plans['data'] as $plan): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($plan['name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($plan['description'] ?? ''); ?></small>
                                                </td>
                                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($plan['type']); ?></span></td>
                                                <td><strong>$<?php echo number_format((float)$plan['amount'], 2); ?></strong> <?php echo htmlspecialchars($plan['currency']); ?></td>
                                                <td>
                                                    Usuarios: <?php echo $plan['max_users'] === null ? 'Ilimitado' : (int)$plan['max_users']; ?><br>
                                                    Mesas: <?php echo $plan['max_tables'] === null ? 'Ilimitado' : (int)$plan['max_tables']; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $plan['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo htmlspecialchars($plan['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>
                                                        Product: <code><?php echo htmlspecialchars($plan['stripe_product_id'] ?? ''); ?></code><br>
                                                        Price: <code><?php echo htmlspecialchars($plan['stripe_price_id'] ?? ''); ?></code>
                                                    </small>
                                                </td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($plan['updated_at']))); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($plans['data'])): ?>
                                            <tr><td colspan="7" class="text-center text-muted py-4">No hay planes registrados.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
    <?php include 'layouts/footer-scripts.php'; ?>
    <script src="assets/js/app.min.js"></script>
</body>
</html>
