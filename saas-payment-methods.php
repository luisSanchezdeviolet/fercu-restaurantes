<?php
require_once 'layouts/session.php';
requireLogin();

if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit;
}

require_once 'controllers/SaasAdminController.php';
$controller = new SaasAdminController();
$methods = $controller->getPaymentMethodsSummary();
?>
<?php include 'layouts/main.php'; ?>
<head>
    <title>Métodos de Pago SaaS | Fercu Restaurante</title>
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
                                        <li class="breadcrumb-item active">Métodos de pago</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Métodos de pago SaaS</h4>
                            </div>
                        </div>
                    </div>

                    <?php if (!$methods['success']): ?>
                        <div class="alert alert-danger">Error al cargar métodos: <?php echo htmlspecialchars($methods['error']); ?></div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-1">Métodos disponibles</h4>
                                <p class="text-muted mb-3">Resumen basado en los métodos actuales definidos en pagos y suscripciones.</p>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Método</th>
                                                <th>Clave</th>
                                                <th>Descripción</th>
                                                <th>Pagos</th>
                                                <th>Total cobrado</th>
                                                <th>Suscripciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($methods['data'] as $method): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($method['name']); ?></strong></td>
                                                <td><code><?php echo htmlspecialchars($method['key']); ?></code></td>
                                                <td><?php echo htmlspecialchars($method['description']); ?></td>
                                                <td><?php echo (int)$method['payments_count']; ?></td>
                                                <td>$<?php echo number_format((float)$method['payments_amount'], 2); ?></td>
                                                <td><?php echo (int)$method['subscriptions_count']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-info mb-0">
                                    Esta vista es solo lectura. Para hacer métodos editables se requiere una tabla catálogo y una migración aprobada.
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
