<?php
require_once 'layouts/session.php';
requireLogin();

if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit;
}

require_once 'controllers/SaasAdminController.php';
$controller = new SaasAdminController();
$status = $_GET['status'] ?? 'all';
if (!in_array($status, ['all', 'active', 'inactive'], true)) {
    $status = 'all';
}
$subscriptions = $controller->getAllSubscriptions($status);

function subscriptionStatusBadge($status) {
    return ((int)$status === 1)
        ? '<span class="badge bg-success">Activa</span>'
        : '<span class="badge bg-secondary">Inactiva</span>';
}

function daysLeftBadge($days) {
    if ($days === null || $days === '') {
        return '<span class="badge bg-secondary">N/A</span>';
    }
    $days = (int)$days;
    if ($days < 0) {
        return '<span class="badge bg-danger">Vencida (' . abs($days) . ' d)</span>';
    }
    if ($days <= 7) {
        return '<span class="badge bg-warning">' . $days . ' d</span>';
    }
    return '<span class="badge bg-success">' . $days . ' d</span>';
}
?>
<?php include 'layouts/main.php'; ?>
<head>
    <title>Suscripciones SaaS | Fercu Restaurante</title>
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
                                        <li class="breadcrumb-item active">Suscripciones</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Suscripciones SaaS</h4>
                            </div>
                        </div>
                    </div>

                    <?php if (!$subscriptions['success']): ?>
                        <div class="alert alert-danger">Error al cargar suscripciones: <?php echo htmlspecialchars($subscriptions['error']); ?></div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <div>
                                        <h4 class="header-title mb-1">Suscripciones</h4>
                                        <p class="text-muted mb-0">Vista global por empresa, plan, vencimiento y proveedor.</p>
                                    </div>
                                    <div class="btn-group">
                                        <a href="saas-subscriptions.php?status=all" class="btn btn-sm <?php echo $status === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">Todas</a>
                                        <a href="saas-subscriptions.php?status=active" class="btn btn-sm <?php echo $status === 'active' ? 'btn-primary' : 'btn-outline-primary'; ?>">Activas</a>
                                        <a href="saas-subscriptions.php?status=inactive" class="btn btn-sm <?php echo $status === 'inactive' ? 'btn-primary' : 'btn-outline-primary'; ?>">Inactivas</a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Empresa</th>
                                                <th>Plan</th>
                                                <th>Inicio</th>
                                                <th>Vencimiento</th>
                                                <th>Días</th>
                                                <th>Estado</th>
                                                <th>Método</th>
                                                <th>Stripe</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($subscriptions['data'] as $subscription): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($subscription['company_name'] ?? 'Sin empresa'); ?></strong><br>
                                                    <small class="text-muted">ID <?php echo (int)$subscription['configuracion_id']; ?></small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($subscription['plan_name'] ?? 'Sin plan'); ?><br>
                                                    <small class="text-muted">$<?php echo number_format((float)$subscription['amount'], 2); ?> <?php echo htmlspecialchars($subscription['currency'] ?? 'MXN'); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($subscription['start_date']))); ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($subscription['limit_date']))); ?></td>
                                                <td><?php echo daysLeftBadge($subscription['days_left']); ?></td>
                                                <td><?php echo subscriptionStatusBadge($subscription['status']); ?></td>
                                                <td><span class="badge bg-info"><?php echo htmlspecialchars($subscription['payment_method']); ?></span></td>
                                                <td>
                                                    <small>
                                                        Sub: <code><?php echo htmlspecialchars($subscription['stripe_subscription_id'] ?? ''); ?></code><br>
                                                        Cus: <code><?php echo htmlspecialchars($subscription['stripe_customer_id'] ?? ''); ?></code>
                                                    </small>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($subscriptions['data'])): ?>
                                            <tr><td colspan="8" class="text-center text-muted py-4">No hay suscripciones para este filtro.</td></tr>
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
