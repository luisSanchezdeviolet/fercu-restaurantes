<?php
session_start();
require_once 'config/database.php';

// Verificar si se recibió el plan seleccionado
$plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;

if ($plan_id === 0) {
    header('Location: index.php');
    exit;
}

// Conectar a la base de datos
$database = new Database();
$conn = $database->getConnection();

// Obtener información del plan
$stmt = $conn->prepare("SELECT * FROM plans WHERE id = ? AND status = 'active'");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plan) {
    header('Location: index.php');
    exit;
}

// Verificar si el usuario ya está logueado
$is_logged_in = isset($_SESSION['user_id']);
$user_email = $is_logged_in ? $_SESSION['user_email'] : '';
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';

// Decodificar features JSON
$features = json_decode($plan['features'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($plan['name']); ?> | Fercu Restaurante</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .checkout-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .checkout-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .checkout-body {
            padding: 2rem;
        }
        
        .plan-summary {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .plan-price {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .plan-features {
            list-style: none;
            padding: 0;
        }
        
        .plan-features li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .plan-features li i {
            color: #28a745;
            margin-right: 0.5rem;
        }
        
        .payment-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            color: #6c757d;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .badge-plan {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-trial {
            background: #ffc107;
            color: #000;
        }
        
        .badge-monthly {
            background: #17a2b8;
            color: white;
        }
        
        .badge-annual {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <!-- Botón de regresar -->
        <div class="mb-3">
            <a href="index.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver a la página principal
            </a>
        </div>
        
        <div class="checkout-card">
            <div class="checkout-header">
                <h1><i class="bi bi-cart-check"></i> Finalizar Suscripción</h1>
                <p class="mb-0">Estás a un paso de transformar tu restaurante</p>
            </div>
            
            <div class="checkout-body">
                <div class="row">
                    <!-- Columna izquierda: Resumen del plan -->
                    <div class="col-lg-5">
                        <div class="plan-summary">
                            <div class="text-center mb-3">
                                <span class="badge-plan badge-<?php echo $plan['type']; ?>">
                                    <?php 
                                    $type_labels = [
                                        'trial' => 'Prueba Gratuita',
                                        'monthly' => 'Plan Mensual',
                                        'annual' => 'Plan Anual'
                                    ];
                                    echo $type_labels[$plan['type']]; 
                                    ?>
                                </span>
                            </div>
                            
                            <h3 class="text-center mb-3"><?php echo htmlspecialchars($plan['name']); ?></h3>
                            
                            <div class="text-center mb-4">
                                <div class="plan-price">
                                    $<?php echo number_format($plan['amount'], 2); ?>
                                </div>
                                <div class="text-muted">
                                    <?php echo $plan['currency']; ?> 
                                    <?php if ($plan['type'] === 'monthly'): ?>
                                        al mes
                                    <?php elseif ($plan['type'] === 'annual'): ?>
                                        al año
                                    <?php else: ?>
                                        (15 días gratis)
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="text-muted"><?php echo htmlspecialchars($plan['description']); ?></p>
                            
                            <?php if ($features && count($features) > 0): ?>
                            <h5 class="mt-4 mb-3">¿Qué incluye?</h5>
                            <ul class="plan-features">
                                <?php foreach ($features as $key => $value): ?>
                                    <?php if (is_bool($value)): ?>
                                        <?php if ($value): ?>
                                            <li><i class="bi bi-check-circle-fill"></i> <?php echo ucfirst(str_replace('_', ' ', $key)); ?></li>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <li><i class="bi bi-check-circle-fill"></i> <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong> <?php echo $value; ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                            
                            <?php if (isset($features['ahorro'])): ?>
                            <div class="alert alert-success mt-3">
                                <i class="bi bi-piggy-bank-fill"></i> <strong><?php echo $features['ahorro']; ?></strong>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Columna derecha: Formulario de pago -->
                    <div class="col-lg-7">
                        <div class="payment-section">
                            <h4 class="mb-4">
                                <i class="bi bi-credit-card"></i> Información de Pago
                            </h4>
                            
                            <?php if ($plan['type'] === 'trial'): ?>
                            <!-- Para plan de prueba gratuita -->
                            <div class="alert alert-info">
                                <h5><i class="bi bi-gift-fill"></i> ¡Prueba Gratuita!</h5>
                                <p>No se requiere información de pago para la prueba gratuita de 15 días. Solo necesitas crear tu cuenta.</p>
                            </div>
                            
                            <?php if (!$is_logged_in): ?>
                            <p class="text-muted mb-3">Si ya tienes una cuenta, <a href="#" id="show-login">inicia sesión aquí</a>. Si no, <a href="index.php#register">regístrate desde la página principal</a>.</p>
                            <?php else: ?>
                            <div class="alert alert-success">
                                <i class="bi bi-person-check-fill"></i> Ya tienes una cuenta activa: <strong><?php echo htmlspecialchars($user_email); ?></strong>
                            </div>
                            <a href="dashboard.php" class="btn btn-primary-custom w-100">
                                <i class="bi bi-speedometer2"></i> Ir a mi Dashboard
                            </a>
                            <?php endif; ?>
                            
                            <?php else: ?>
                            <!-- Para planes de pago -->
                            <form id="payment-form">
                                <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">
                                
                                <?php if (!$is_logged_in): ?>
                                <div class="alert alert-info mb-4">
                                    <i class="bi bi-info-circle-fill"></i> 
                                    <strong>¿Eres nuevo?</strong> Completa el formulario para crear tu cuenta y contratar este plan.<br>
                                    <small>¿Ya tienes cuenta? <a href="#" id="show-login-2" style="color: #0056b3; font-weight: bold;">Inicia sesión aquí</a></small>
                                </div>
                                
                                <h5 class="mb-3"><i class="bi bi-person-plus"></i> Información de Registro</h5>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="reg-nombre" class="form-label">Nombre *</label>
                                        <input type="text" class="form-control" id="reg-nombre" name="nombre" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reg-apellido" class="form-label">Apellido *</label>
                                        <input type="text" class="form-control" id="reg-apellido" name="apellido" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="reg-empresa" class="form-label">Nombre de tu Restaurante *</label>
                                        <input type="text" class="form-control" id="reg-empresa" name="empresa" placeholder='Ej: Restaurante "La Cocina"' required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reg-correo" class="form-label">Correo Electrónico *</label>
                                        <input type="email" class="form-control" id="reg-correo" name="correo" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reg-telefono" class="form-label">Teléfono *</label>
                                        <input type="tel" class="form-control" id="reg-telefono" name="telefono" placeholder="10 dígitos" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reg-giro" class="form-label">Tipo de Cocina</label>
                                        <select class="form-select" id="reg-giro" name="giro">
                                            <option value="Mexicana">Comida Mexicana</option>
                                            <option value="Italiana">Comida Italiana</option>
                                            <option value="Japonesa">Comida Japonesa</option>
                                            <option value="China">Comida China</option>
                                            <option value="Internacional">Internacional</option>
                                            <option value="FastFood">Comida Rápida</option>
                                            <option value="Vegetariana">Vegetariana/Vegana</option>
                                            <option value="Mariscos">Mariscos</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="reg-empleados" class="form-label">¿Cuántos empleados tienes?</label>
                                        <select class="form-select" id="reg-empleados" name="empleados">
                                            <option value="1-5">1-5 empleados</option>
                                            <option value="6-10">6-10 empleados</option>
                                            <option value="11-20">11-20 empleados</option>
                                            <option value="21-50">21-50 empleados</option>
                                            <option value="50+">Más de 50</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                <?php else: ?>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-person"></i> Cuenta</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" disabled>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5 class="mb-3"><i class="bi bi-credit-card-fill"></i> Información de Tarjeta</h5>
                                
                                <!-- Stripe Card Element -->
                                <div id="card-element" class="form-control" style="height: 50px; padding: 15px;"></div>
                                <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                                
                                <hr class="my-4">
                                
                                <h5 class="mb-3">Resumen del Pedido</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong>$<?php echo number_format($plan['amount'], 2); ?> <?php echo $plan['currency']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Impuestos (16% IVA):</span>
                                    <strong>$<?php echo number_format($plan['amount'] * 0.16, 2); ?> <?php echo $plan['currency']; ?></strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-4">
                                    <strong>Total:</strong>
                                    <strong class="text-primary" style="font-size: 1.5rem;">
                                        $<?php echo number_format($plan['amount'] * 1.16, 2); ?> <?php echo $plan['currency']; ?>
                                    </strong>
                                </div>
                                
                                <button type="submit" id="submit-payment" class="btn btn-primary-custom w-100">
                                    <span id="button-text"><i class="bi bi-lock-fill"></i> Pagar Ahora</span>
                                    <div id="spinner" class="spinner-border spinner-border-sm d-none" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </button>
                                
                                <div class="security-badge">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    <small>Pago seguro con Stripe - Encriptación SSL 256-bit</small>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <img src="https://stripe.com/img/v3/home/social.png" alt="Stripe" style="height: 30px; opacity: 0.7;">
                                </div>
                                <?php endif; ?>
                            </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4 text-center text-muted">
                            <small>
                                <i class="bi bi-info-circle"></i> 
                                Al continuar, aceptas nuestros <a href="#">Términos y Condiciones</a> y <a href="#">Política de Privacidad</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <?php
    // Cargar configuración de Stripe
    require_once 'config/stripe.php';
    ?>
    
    <script>
        const STRIPE_PUBLIC_KEY = '<?php echo STRIPE_PUBLIC_KEY; ?>';
        const PLAN_ID = <?php echo $plan_id; ?>;
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
            const planId = <?php echo $plan_id; ?>;
            
            // Manejar envío del formulario de pago
            const paymentForm = document.getElementById('payment-form');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Si el usuario NO está logueado, primero registrar
                    if (!isLoggedIn) {
                        // Validar campos
                        const nombre = document.getElementById('reg-nombre').value.trim();
                        const apellido = document.getElementById('reg-apellido').value.trim();
                        const empresa = document.getElementById('reg-empresa').value.trim();
                        const correo = document.getElementById('reg-correo').value.trim();
                        const telefono = document.getElementById('reg-telefono').value.trim();
                        const giro = document.getElementById('reg-giro').value;
                        const empleados = document.getElementById('reg-empleados').value;
                        
                        if (!nombre || !apellido || !empresa || !correo || !telefono) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Campos incompletos',
                                text: 'Por favor completa todos los campos obligatorios'
                            });
                            return;
                        }
                        
                        Swal.fire({
                            title: 'Creando tu cuenta...',
                            html: 'Estamos registrando tu restaurante y asignando tu plan.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Registrar usuario con el plan específico
                        const registroData = {
                            nombre: nombre,
                            apellido: apellido,
                            empresa: empresa,
                            correo: correo,
                            telefono: telefono,
                            giro: giro,
                            empleados: empleados,
                            plan_id: planId  // Enviar el plan seleccionado
                        };
                        
                        fetch('register-procesar.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(registroData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const planInfo = data.data || {};
                                const planNombre = planInfo.plan_nombre || 'Plan seleccionado';
                                const fechaFin = planInfo.fecha_fin || '';
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Registro completado!',
                                    html: `
                                        <div class="text-start" style="padding: 1rem;">
                                            <h5><i class="bi bi-check-circle-fill text-success"></i> ¡Bienvenido a Fercu Restaurante!</h5>
                                            <hr>
                                            <p><strong>Restaurante:</strong> ${empresa}</p>
                                            <p><strong>Plan asignado:</strong> <span class="badge bg-primary">${planNombre}</span></p>
                                            <p><strong>Válido hasta:</strong> ${fechaFin}</p>
                                            <hr>
                                            <p class="text-muted small">
                                                <i class="bi bi-envelope-fill"></i> 
                                                Hemos enviado tus credenciales a <strong>${correo}</strong>
                                            </p>
                                            <div class="alert alert-warning small mt-2">
                                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                                Si no encuentras el correo, revisa tu carpeta de <strong>spam</strong>.
                                            </div>
                                            <p class="text-center text-muted small">Serás redirigido al login...</p>
                                        </div>
                                    `,
                                    width: '600px',
                                    timer: 5000,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = 'login.php?email=' + encodeURIComponent(correo);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error en el registro',
                                    text: data.error || data.message || 'No se pudo completar el registro'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de conexión',
                                text: 'No pudimos procesar tu solicitud. Por favor intenta de nuevo.'
                            });
                        });
                    } else {
                        // Usuario YA está logueado, procesar el pago con Stripe
                        handleStripePayment(e);
                    }
                });
            }
            
            // Mostrar login modal si se hace clic en "iniciar sesión"
            const showLoginLinks = document.querySelectorAll('#show-login, #show-login-2');
            showLoginLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Iniciar Sesión',
                        html: `
                            <form id="swal-login-form">
                                <input type="email" id="login-email" class="swal2-input" placeholder="Correo electrónico" required>
                                <input type="password" id="login-password" class="swal2-input" placeholder="Contraseña" required>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Iniciar Sesión',
                        cancelButtonText: 'Cancelar',
                        preConfirm: () => {
                            const email = document.getElementById('login-email').value;
                            const password = document.getElementById('login-password').value;
                            
                            if (!email || !password) {
                                Swal.showValidationMessage('Por favor completa todos los campos');
                                return false;
                            }
                            
                            return { email, password };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Aquí iría la lógica de login
                            const formData = new URLSearchParams({
                                correo: result.value.email,
                                clave: result.value.password
                            });
                            
                            fetch('landing-login.php', {
                                method: 'POST',
                                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                                body: formData
                            })
                            .then(res => res.json())
                            .then(res => {
                                if (res.type === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: res.msg,
                                        timer: 800,
                                        showConfirmButton: false
                                    }).then(() => window.location.reload());
                                } else {
                                    Swal.fire({ icon: res.type, title: res.msg });
                                }
                            })
                            .catch(() => {
                                Swal.fire('Error','No se pudo conectar al servidor','error');
                            });
                        }
                    });
                });
            });
            
            // ============================================
            // INTEGRACIÓN DE STRIPE
            // ============================================
            
            <?php if ($is_logged_in && $plan['type'] !== 'trial' && $plan['amount'] > 0): ?>
            
            // Inicializar Stripe
            const stripe = Stripe(STRIPE_PUBLIC_KEY);
            const elements = stripe.elements();
            
            // Estilo personalizado para el formulario de tarjeta
            const style = {
                base: {
                    color: '#32325d',
                    fontFamily: '"Segoe UI", Tahoma, Geneva, Verdana, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };
            
            // Crear el elemento de tarjeta
            const cardElement = elements.create('card', { style: style });
            cardElement.mount('#card-element');
            
            // Manejar errores en tiempo real
            cardElement.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
            
            // Función para manejar el pago con Stripe (SUBSCRIPTIONS)
            async function handleStripePayment(e) {
                e.preventDefault();
                
                const submitButton = document.getElementById('submit-payment');
                const buttonText = document.getElementById('button-text');
                const spinner = document.getElementById('spinner');
                
                // Deshabilitar el botón y mostrar spinner
                submitButton.disabled = true;
                buttonText.classList.add('d-none');
                spinner.classList.remove('d-none');
                
                try {
                    // 1. Crear Payment Method primero
                    const { error: pmError, paymentMethod } = await stripe.createPaymentMethod({
                        type: 'card',
                        card: cardElement
                    });
                    
                    if (pmError) {
                        throw new Error(pmError.message);
                    }
                    
                    // 2. Crear Subscription en el servidor
                    const response = await fetch('stripe-create-subscription.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            plan_id: PLAN_ID,
                            payment_method_id: paymentMethod.id
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        throw new Error(data.error || 'Error al crear la suscripción');
                    }
                    
                    // 3. Confirmar el pago de la suscripción
                    const { error, paymentIntent } = await stripe.confirmCardPayment(
                        data.client_secret
                    );
                    
                    if (error) {
                        // Mostrar error al usuario
                        submitButton.disabled = false;
                        buttonText.classList.remove('d-none');
                        spinner.classList.add('d-none');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en el pago',
                            text: error.message
                        });
                    } else {
                        // Pago exitoso
                        if (paymentIntent.status === 'succeeded') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Suscripción activada!',
                                html: `
                                    <p><i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i></p>
                                    <p><strong>¡Tu suscripción ha sido activada exitosamente!</strong></p>
                                    <p class="text-muted">Los pagos se renovarán automáticamente cada período.</p>
                                    <p class="text-muted small">Subscription ID: ${data.subscription_id}</p>
                                `,
                                confirmButtonText: 'Ir al Dashboard',
                                allowOutsideClick: false
                            }).then(() => {
                                window.location.href = 'dashboard.php';
                            });
                        }
                    }
                    
                } catch (error) {
                    submitButton.disabled = false;
                    buttonText.classList.remove('d-none');
                    spinner.classList.add('d-none');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Ocurrió un error al procesar la suscripción'
                    });
                }
            }
            
            <?php endif; ?>
        });
    </script>
</body>
</html>

