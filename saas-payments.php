<?php
require_once 'layouts/session.php';
requireLogin();

if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit;
}

require_once 'controllers/SaasAdminController.php';
$controller = new SaasAdminController();
$payments = $controller->getAllPayments();

function paymentStatusBadge($status) {
    $classes = [
        'pending' => 'bg-warning',
        'completed' => 'bg-success',
        'failed' => 'bg-danger',
        'refunded' => 'bg-secondary'
    ];
    $class = $classes[$status] ?? 'bg-light text-dark';
    return '<span class="badge ' . $class . '">' . htmlspecialchars((string)$status) . '</span>';
}
?>
<?php include 'layouts/main.php'; ?>
<head>
    <title>Pagos SaaS | Fercu Restaurante</title>
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
                                        <li class="breadcrumb-item active">Pagos</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Pagos SaaS</h4>
                            </div>
                        </div>
                    </div>

                    <?php if (!$payments['success']): ?>
                        <div class="alert alert-danger">Error al cargar pagos: <?php echo htmlspecialchars($payments['error']); ?></div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <div>
                                        <h4 class="header-title mb-1">Pagos registrados</h4>
                                        <p class="text-muted mb-0">Vista global de pagos por empresa, plan y método.</p>
                                    </div>
                                    <span class="badge bg-primary"><?php echo count($payments['data']); ?> registros</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Empresa</th>
                                                <th>Plan</th>
                                                <th>Monto</th>
                                                <th>Método</th>
                                                <th>Estado</th>
                                                <th>Referencia</th>
                                                <th>Suscripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payments['data'] as $payment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($payment['payment_date']))); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($payment['company_name'] ?? 'Sin empresa'); ?></strong><br>
                                                    <small class="text-muted">ID <?php echo (int)$payment['configuracion_id']; ?></small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($payment['plan_name'] ?? 'Sin plan'); ?><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($payment['plan_type'] ?? ''); ?></small>
                                                </td>
                                                <td><strong>$<?php echo number_format((float)$payment['amount'], 2); ?></strong> <?php echo htmlspecialchars($payment['currency']); ?></td>
                                                <td><span class="badge bg-info"><?php echo htmlspecialchars($payment['payment_method']); ?></span></td>
                                                <td><?php echo paymentStatusBadge($payment['status']); ?></td>
                                                <td><code><?php echo htmlspecialchars($payment['transaction_id'] ?? ''); ?></code></td>
                                                <td><?php echo $payment['subscription_id'] ? '#' . (int)$payment['subscription_id'] : 'N/A'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($payments['data'])): ?>
                                            <tr><td colspan="8" class="text-center text-muted py-4">No hay pagos registrados.</td></tr>
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
