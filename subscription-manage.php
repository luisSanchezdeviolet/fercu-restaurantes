<?php
session_start();
require_once 'layouts/session.php';
require_once 'config/database.php';
require_once 'controllers/SubscriptionController.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    header('Location: presentation/login.php');
    exit;
}

// No permitir acceso a Super Admins (ellos no tienen suscripción)
if (isSuperAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$user_data = getUserData();
$configuracion_id = $_SESSION['configuracion_id'];

// Obtener información de la suscripción
$database = new Database();
$conn = $database->getConnection();
$subscriptionController = new SubscriptionController($conn);

$subscription_data = $subscriptionController->getCurrentSubscription($configuracion_id);
$available_plans = $subscriptionController->getAvailablePlans();
$payment_history = $subscriptionController->getPaymentHistory($configuracion_id, 10);

// Obtener información de Stripe si hay suscripción
$stripe_info = null;
if ($subscription_data['success'] && !empty($subscription_data['data']['stripe_subscription_id'])) {
    $stripe_info = $subscriptionController->getStripeSubscriptionInfo($subscription_data['data']['stripe_subscription_id']);
}

$current_subscription = $subscription_data['success'] ? $subscription_data['data'] : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Gestión de Suscripción - Fercu Restaurante</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    
    <!-- App css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style"/>
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Stripe -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <style>
        .subscription-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .subscription-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .plan-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            font-size: 0.9rem;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .status-cancelled {
            color: #ffc107;
            font-weight: bold;
        }
        .payment-method-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .card-brand-icon {
            width: 40px;
            height: 25px;
            margin-right: 10px;
        }
        #card-element {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 12px;
            background: white;
        }
        .plan-comparison {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .plan-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .plan-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .plan-card.current-plan {
            border-color: #28a745;
            background: #f0fff4;
        }
        .plan-card.current-plan::after {
            content: "Plan Actual";
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
        }
        .payment-history-table {
            font-size: 0.9rem;
        }
        .days-remaining {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>

<body class="loading" data-layout-color="light" data-leftbar-theme="dark" data-layout-mode="fluid" data-rightbar-onstart="true">
    <div class="wrapper">
        <!-- Menu lateral -->
        <?php include 'layouts/left-sidebar.php'; ?>

        <div class="content-page">
            <div class="content">
                <!-- Topbar -->
                <?php include 'layouts/topbar.php'; ?>

                <!-- Contenido -->
                <div class="container-fluid">
                    <!-- Título -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="mdi mdi-arrow-left"></i> Volver al Dashboard
                                    </a>
                                </div>
                                <h4 class="page-title">
                                    <i class="mdi mdi-credit-card-outline"></i> Gestión de Suscripción
                                </h4>
                            </div>
                        </div>
                    </div>

                    <?php if ($current_subscription): ?>
                    
                    <!-- Tarjeta de Suscripción Actual -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card subscription-card">
                                <div class="subscription-header">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h3 class="mb-2"><?php echo htmlspecialchars($current_subscription['plan_name']); ?></h3>
                                            <span class="plan-badge">
                                                <?php 
                                                echo $current_subscription['plan_type'] === 'monthly' ? 'Plan Mensual' : 'Plan Anual';
                                                ?>
                                            </span>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="days-remaining"><?php echo $current_subscription['days_remaining']; ?></div>
                                            <div>días restantes</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <h6 class="text-muted">Precio</h6>
                                            <h4>$<?php echo number_format($current_subscription['plan_amount'], 2); ?> <?php echo $current_subscription['plan_currency']; ?></h4>
                                        </div>
                                        <div class="col-md-3">
                                            <h6 class="text-muted">Fecha de Inicio</h6>
                                            <p><?php echo date('d/m/Y', strtotime($current_subscription['start_date'])); ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <h6 class="text-muted">Próxima Renovación</h6>
                                            <p><?php echo date('d/m/Y', strtotime($current_subscription['limit_date'])); ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <h6 class="text-muted">Estado</h6>
                                            <p class="<?php echo $current_subscription['is_expired'] ? 'status-inactive' : 'status-active'; ?>">
                                                <i class="mdi mdi-<?php echo $current_subscription['is_expired'] ? 'close-circle' : 'check-circle'; ?>"></i>
                                                <?php echo $current_subscription['is_expired'] ? 'Expirada' : 'Activa'; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php if ($stripe_info && $stripe_info['success']): ?>
                                    <hr>
                                    <div class="row">
                                        <div class="col-12">
                                            <h6 class="text-muted mb-3">
                                                <i class="mdi mdi-credit-card"></i> Método de Pago
                                            </h6>
                                            
                                            <?php if (isset($stripe_info['data']['payment_method_details'])): ?>
                                            <div class="payment-method-card">
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-credit-card-outline" style="font-size: 2rem; margin-right: 15px;"></i>
                                                    <div>
                                                        <div>
                                                            <strong><?php echo strtoupper($stripe_info['data']['payment_method_details']['card_brand']); ?></strong>
                                                            terminada en <?php echo $stripe_info['data']['payment_method_details']['card_last4']; ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            Expira: <?php echo $stripe_info['data']['payment_method_details']['card_exp_month']; ?>/<?php echo $stripe_info['data']['payment_method_details']['card_exp_year']; ?>
                                                        </small>
                                                    </div>
                                                    <div class="ms-auto">
                                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updatePaymentModal">
                                                            <i class="mdi mdi-pencil"></i> Actualizar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="mdi mdi-alert"></i> No hay método de pago configurado
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($stripe_info['data']['cancel_at_period_end']): ?>
                                            <div class="alert alert-warning mt-3">
                                                <i class="mdi mdi-alert-circle"></i>
                                                <strong>Cancelación Programada:</strong> Tu suscripción se cancelará el 
                                                <?php echo date('d/m/Y', strtotime($current_subscription['limit_date'])); ?>
                                                <button class="btn btn-sm btn-success float-end" onclick="reactivateSubscription()">
                                                    <i class="mdi mdi-restart"></i> Reactivar Suscripción
                                                </button>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs de Gestión -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-bordered mb-3">
                                        <li class="nav-item">
                                            <a href="#change-plan" data-bs-toggle="tab" aria-expanded="false" class="nav-link active">
                                                <i class="mdi mdi-swap-horizontal d-md-none d-block"></i>
                                                <span class="d-none d-md-block">Cambiar de Plan</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#payment-history" data-bs-toggle="tab" aria-expanded="true" class="nav-link">
                                                <i class="mdi mdi-history d-md-none d-block"></i>
                                                <span class="d-none d-md-block">Historial de Pagos</span>
                                            </a>
                                        </li>
                                        <?php if ($current_subscription['plan_type'] !== 'trial'): ?>
                                        <li class="nav-item">
                                            <a href="#cancel-subscription" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                <i class="mdi mdi-close-circle d-md-none d-block"></i>
                                                <span class="d-none d-md-block">Cancelar Suscripción</span>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>

                                    <div class="tab-content">
                                        <!-- Tab: Cambiar de Plan -->
                                        <div class="tab-pane show active" id="change-plan">
                                            <h4 class="mb-3">Cambiar de Plan</h4>
                                            <p class="text-muted">Selecciona un nuevo plan. El cambio se realizará de inmediato con cálculo prorrateado.</p>
                                            
                                            <div class="plan-comparison">
                                                <?php if ($available_plans['success']): ?>
                                                    <?php foreach ($available_plans['data'] as $plan): ?>
                                                    <div class="plan-card position-relative <?php echo $plan['id'] == $current_subscription['plan_id'] ? 'current-plan' : ''; ?>" 
                                                         data-plan-id="<?php echo $plan['id']; ?>"
                                                         onclick="selectNewPlan(<?php echo $plan['id']; ?>, '<?php echo htmlspecialchars($plan['name']); ?>', <?php echo $plan['amount']; ?>)">
                                                        <h5><?php echo htmlspecialchars($plan['name']); ?></h5>
                                                        <div class="mb-3">
                                                            <span style="font-size: 2rem; font-weight: bold;">$<?php echo number_format($plan['amount'], 0); ?></span>
                                                            <span class="text-muted">MXN / <?php echo $plan['type'] === 'monthly' ? 'mes' : 'año'; ?></span>
                                                        </div>
                                                        <?php if ($plan['description']): ?>
                                                        <p class="text-muted"><?php echo htmlspecialchars($plan['description']); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Tab: Historial de Pagos -->
                                        <div class="tab-pane" id="payment-history">
                                            <h4 class="mb-3">Historial de Pagos</h4>
                                            
                                            <?php if ($payment_history['success'] && count($payment_history['data']) > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover payment-history-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Plan</th>
                                                            <th>Monto</th>
                                                            <th>Método</th>
                                                            <th>Estado</th>
                                                            <th>ID Transacción</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($payment_history['data'] as $payment): ?>
                                                        <tr>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($payment['payment_date'])); ?></td>
                                                            <td><?php echo htmlspecialchars($payment['plan_name']); ?></td>
                                                            <td>
                                                                <strong>$<?php echo number_format($payment['amount'], 2); ?></strong>
                                                                <?php echo $payment['currency']; ?>
                                                            </td>
                                                            <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                                            <td>
                                                                <?php if ($payment['status'] === 'completed'): ?>
                                                                <span class="badge bg-success">Completado</span>
                                                                <?php elseif ($payment['status'] === 'pending'): ?>
                                                                <span class="badge bg-warning">Pendiente</span>
                                                                <?php else: ?>
                                                                <span class="badge bg-danger">Fallido</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted"><?php echo htmlspecialchars($payment['payment_gateway_id']); ?></small>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php else: ?>
                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information"></i> No hay pagos registrados aún
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Tab: Cancelar Suscripción -->
                                        <?php if ($current_subscription['plan_type'] !== 'trial'): ?>
                                        <div class="tab-pane" id="cancel-subscription">
                                            <h4 class="mb-3 text-danger"><i class="mdi mdi-alert-circle"></i> Cancelar Suscripción</h4>
                                            
                                            <div class="alert alert-warning">
                                                <strong>¿Estás seguro?</strong> Al cancelar tu suscripción perderás acceso a todas las funcionalidades del sistema.
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <h5><i class="mdi mdi-calendar-clock"></i> Cancelar al Final del Período</h5>
                                                            <p class="text-muted">Mantendrás acceso hasta el <?php echo date('d/m/Y', strtotime($current_subscription['limit_date'])); ?></p>
                                                            <button class="btn btn-warning" onclick="cancelSubscription(false)">
                                                                Cancelar al Final del Período
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            <h5><i class="mdi mdi-close-circle"></i> Cancelar Inmediatamente</h5>
                                                            <p class="text-muted">Perderás acceso de inmediato (no hay reembolso)</p>
                                                            <button class="btn btn-danger" onclick="cancelSubscription(true)">
                                                                Cancelar Ahora
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php else: ?>
                    <!-- No hay suscripción -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <h4><i class="mdi mdi-alert"></i> No tienes una suscripción activa</h4>
                                <p>Por favor, contacta al administrador o selecciona un plan desde nuestra página principal.</p>
                                <a href="index.php" class="btn btn-primary">Ver Planes Disponibles</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Footer -->
            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>

    <!-- Modal: Actualizar Método de Pago -->
    <div class="modal fade" id="updatePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-credit-card"></i> Actualizar Método de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Ingresa los datos de tu nueva tarjeta. Se usará para futuros cargos.</p>
                    <div id="card-element"></div>
                    <div id="card-errors" class="text-danger mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="updatePaymentBtn">
                        <span id="update-button-text">Actualizar Tarjeta</span>
                        <span id="update-spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/vendor.min.js"></script>
    <script src="assets/js/app.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // Configuración de Stripe
    <?php if ($current_subscription && !empty($current_subscription['stripe_subscription_id'])): ?>
    const STRIPE_PUBLIC_KEY = '<?php require_once "config/stripe.php"; echo STRIPE_PUBLIC_KEY; ?>';
    const STRIPE_SUBSCRIPTION_ID = '<?php echo $current_subscription['stripe_subscription_id']; ?>';
    const CURRENT_PLAN_ID = <?php echo $current_subscription['plan_id']; ?>;
    
    const stripe = Stripe(STRIPE_PUBLIC_KEY);
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    
    // Montar el elemento de tarjeta cuando se abre el modal
    document.getElementById('updatePaymentModal').addEventListener('shown.bs.modal', function () {
        cardElement.mount('#card-element');
    });
    
    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    
    // Actualizar método de pago
    document.getElementById('updatePaymentBtn').addEventListener('click', async function() {
        const btn = this;
        const btnText = document.getElementById('update-button-text');
        const spinner = document.getElementById('update-spinner');
        
        btn.disabled = true;
        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');
        
        try {
            const {error, paymentMethod} = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
            });
            
            if (error) {
                throw new Error(error.message);
            }
            
            // Enviar al servidor
            const response = await fetch('api/subscription-update-payment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    payment_method_id: paymentMethod.id,
                    stripe_subscription_id: STRIPE_SUBSCRIPTION_ID
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Método de pago actualizado!',
                    text: data.message,
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Error al actualizar');
            }
            
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message
            });
        } finally {
            btn.disabled = false;
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
        }
    });
    
    // Seleccionar nuevo plan
    function selectNewPlan(planId, planName, planAmount) {
        if (planId === CURRENT_PLAN_ID) {
            Swal.fire({
                icon: 'info',
                title: 'Plan Actual',
                text: 'Ya tienes este plan activo'
            });
            return;
        }
        
        Swal.fire({
            title: '¿Cambiar de plan?',
            html: `
                <p>Cambiarás al plan: <strong>${planName}</strong></p>
                <p>Nuevo precio: <strong>$${planAmount.toLocaleString()} MXN</strong></p>
                <p class="text-muted small">El cambio se hará con cálculo prorrateado. Serás cargado/abonado proporcionalmente.</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar plan',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                changePlan(planId);
            }
        });
    }
    
    // Cambiar de plan
    async function changePlan(newPlanId) {
        Swal.fire({
            title: 'Procesando...',
            text: 'Cambiando tu plan',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        try {
            const response = await fetch('api/subscription-change-plan.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    new_plan_id: newPlanId,
                    stripe_subscription_id: STRIPE_SUBSCRIPTION_ID
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Plan actualizado!',
                    text: data.message,
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Error al cambiar de plan');
            }
            
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message
            });
        }
    }
    
    // Cancelar suscripción
    function cancelSubscription(immediately) {
        const title = immediately ? '¿Cancelar inmediatamente?' : '¿Cancelar al final del período?';
        const text = immediately ? 
            'Perderás acceso de inmediato y no habrá reembolso' :
            'Mantendrás acceso hasta el final de tu período actual';
        
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, mantener'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                try {
                    const response = await fetch('api/subscription-cancel.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            stripe_subscription_id: STRIPE_SUBSCRIPTION_ID,
                            cancel_immediately: immediately
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Suscripción cancelada',
                            text: data.message,
                        }).then(() => {
                            if (immediately) {
                                window.location.href = 'presentation/login.php';
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        throw new Error(data.message || 'Error al cancelar');
                    }
                    
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message
                    });
                }
            }
        });
    }
    
    // Reactivar suscripción
    async function reactivateSubscription() {
        Swal.fire({
            title: '¿Reactivar suscripción?',
            text: 'Tu suscripción continuará renovándose automáticamente',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                try {
                    const response = await fetch('api/subscription-reactivate.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            stripe_subscription_id: STRIPE_SUBSCRIPTION_ID
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Reactivada!',
                            text: data.message,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Error al reactivar');
                    }
                    
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message
                    });
                }
            }
        });
    }
    <?php endif; ?>
    </script>
</body>
</html>


