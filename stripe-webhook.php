<?php
/**
 * Webhook de Stripe para Fercu Restaurante
 * Este archivo recibe las notificaciones de Stripe cuando ocurren eventos
 */

require_once 'config/database.php';
require_once 'config/stripe.php';

// Leer el cuerpo de la petición
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Log para debugging
$log_file = __DIR__ . '/logs/stripe-webhook.log';
$log_dir = dirname($log_file);
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

writeLog("=== Webhook recibido ===");
writeLog("Signature: $sig_header");

try {
    // Verificar la firma del webhook
    $event = null;
    
    if (empty($sig_header)) {
        throw new Exception('No se recibió la firma del webhook');
    }
    
    // Verificar la firma manualmente
    $sig_parts = explode(',', $sig_header);
    $timestamp = null;
    $signature = null;
    
    foreach ($sig_parts as $part) {
        list($key, $value) = explode('=', $part, 2);
        if ($key === 't') {
            $timestamp = $value;
        } elseif ($key === 'v1') {
            $signature = $value;
        }
    }
    
    if (!$timestamp || !$signature) {
        throw new Exception('Firma inválida');
    }
    
    // Construir la cadena firmada
    $signed_payload = $timestamp . '.' . $payload;
    $expected_signature = hash_hmac('sha256', $signed_payload, STRIPE_WEBHOOK_SECRET);
    
    // Comparar firmas
    if (!hash_equals($expected_signature, $signature)) {
        throw new Exception('Firma no coincide');
    }
    
    // Verificar que el timestamp no sea muy antiguo (5 minutos)
    if (abs(time() - $timestamp) > 300) {
        throw new Exception('Webhook muy antiguo');
    }
    
    // Decodificar el evento
    $event = json_decode($payload, true);
    
    if (!$event) {
        throw new Exception('Error al decodificar el evento');
    }
    
    writeLog("Evento recibido: " . $event['type']);
    
    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();
    
    // Manejar diferentes tipos de eventos
    switch ($event['type']) {
        case 'payment_intent.succeeded':
            handlePaymentIntentSucceeded($event['data']['object'], $conn);
            break;
            
        case 'payment_intent.payment_failed':
            handlePaymentIntentFailed($event['data']['object'], $conn);
            break;
            
        case 'charge.succeeded':
            handleChargeSucceeded($event['data']['object'], $conn);
            break;
            
        case 'invoice.paid':
            handleInvoicePaid($event['data']['object'], $conn);
            break;
            
        case 'invoice.payment_failed':
            handleInvoicePaymentFailed($event['data']['object'], $conn);
            break;
            
        case 'customer.subscription.created':
        case 'customer.subscription.updated':
            handleSubscriptionUpdated($event['data']['object'], $conn);
            break;
            
        case 'customer.subscription.deleted':
            handleSubscriptionDeleted($event['data']['object'], $conn);
            break;
            
        default:
            writeLog("Evento no manejado: " . $event['type']);
    }
    
    // Responder con 200 OK
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    writeLog("Webhook procesado exitosamente");
    
} catch (Exception $e) {
    writeLog("ERROR: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Manejar pago exitoso de PaymentIntent
 */
function handlePaymentIntentSucceeded($paymentIntent, $conn) {
    writeLog("PaymentIntent exitoso: " . $paymentIntent['id']);
    
    try {
        $metadata = $paymentIntent['metadata'];
        $configuracion_id = $metadata['configuracion_id'] ?? null;
        $plan_id = $metadata['plan_id'] ?? null;
        $user_id = $metadata['user_id'] ?? null;
        
        if (!$configuracion_id || !$plan_id) {
            throw new Exception('Metadata incompleto');
        }
        
        // Obtener información del plan
        $stmt = $conn->prepare("SELECT * FROM plans WHERE id = ?");
        $stmt->execute([$plan_id]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$plan) {
            throw new Exception('Plan no encontrado');
        }
        
        // Calcular fechas
        $start_date = date('Y-m-d');
        $limit_date = '';
        
        switch ($plan['type']) {
            case 'monthly':
                $limit_date = date('Y-m-d', strtotime('+30 days'));
                break;
            case 'annual':
                $limit_date = date('Y-m-d', strtotime('+365 days'));
                break;
            default:
                $limit_date = date('Y-m-d', strtotime('+30 days'));
        }
        
        $conn->beginTransaction();
        
        // 1. Desactivar suscripciones anteriores
        $stmt = $conn->prepare("UPDATE subscriptions SET status = 0 WHERE configuracion_id = ?");
        $stmt->execute([$configuracion_id]);
        
        // 2. Crear nueva suscripción
        $stmt = $conn->prepare("
            INSERT INTO subscriptions (configuracion_id, plan_id, start_date, limit_date, status, created_at)
            VALUES (?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$configuracion_id, $plan_id, $start_date, $limit_date]);
        $subscription_id = $conn->lastInsertId();
        
        // 3. Registrar el pago
        $amount = $paymentIntent['amount'] / 100; // Convertir de centavos
        $currency = strtoupper($paymentIntent['currency']);
        
        $stmt = $conn->prepare("
            INSERT INTO payments (
                configuracion_id,
                subscription_id,
                plan_id,
                amount,
                currency,
                status,
                payment_method,
                payment_gateway_id,
                payment_gateway,
                payment_date,
                created_at
            ) VALUES (?, ?, ?, ?, ?, 'completed', 'stripe', ?, 'stripe', NOW(), NOW())
        ");
        $stmt->execute([
            $configuracion_id,
            $subscription_id,
            $plan_id,
            $amount,
            $currency,
            $paymentIntent['id']
        ]);
        
        // 4. Activar la empresa si estaba inactiva
        $stmt = $conn->prepare("UPDATE configuracion SET activo = 1 WHERE id = ?");
        $stmt->execute([$configuracion_id]);
        
        // 5. Registrar actividad
        $stmt = $conn->prepare("
            INSERT INTO saas_activity_log (configuracion_id, user_id, action, description, ip_address)
            VALUES (?, ?, 'payment_success', ?, ?)
        ");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'webhook';
        $description = "Pago exitoso por " . $plan['name'] . ": $" . number_format($amount, 2) . " " . $currency;
        $stmt->execute([$configuracion_id, $user_id, $description, $ip]);
        
        $conn->commit();
        
        writeLog("Suscripción creada exitosamente: subscription_id=$subscription_id");
        
        // TODO: Enviar email de confirmación de pago
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        writeLog("Error al procesar pago exitoso: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Manejar pago fallido
 */
function handlePaymentIntentFailed($paymentIntent, $conn) {
    writeLog("PaymentIntent fallido: " . $paymentIntent['id']);
    
    $metadata = $paymentIntent['metadata'];
    $configuracion_id = $metadata['configuracion_id'] ?? null;
    
    if ($configuracion_id) {
        // Registrar el intento de pago fallido
        $stmt = $conn->prepare("
            INSERT INTO saas_activity_log (configuracion_id, user_id, action, description, ip_address)
            VALUES (?, NULL, 'payment_failed', ?, 'webhook')
        ");
        $error = $paymentIntent['last_payment_error']['message'] ?? 'Error desconocido';
        $description = "Pago fallido: " . $error;
        $stmt->execute([$configuracion_id, $description]);
        
        // TODO: Enviar email notificando el fallo
    }
}

/**
 * Manejar cargo exitoso
 */
function handleChargeSucceeded($charge, $conn) {
    writeLog("Charge exitoso: " . $charge['id']);
    // Ya manejado en payment_intent.succeeded
}

/**
 * Manejar factura pagada (para suscripciones recurrentes)
 */
function handleInvoicePaid($invoice, $conn) {
    writeLog("Invoice pagado: " . $invoice['id']);
    
    try {
        $subscription_id = $invoice['subscription'] ?? null;
        
        if (!$subscription_id) {
            writeLog("Invoice sin subscription_id");
            return;
        }
        
        // Buscar la suscripción en nuestra BD
        $stmt = $conn->prepare("
            SELECT * FROM subscriptions 
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$subscription_id]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sub) {
            writeLog("Suscripción no encontrada en BD: $subscription_id");
            return;
        }
        
        $conn->beginTransaction();
        
        // Extender la fecha de vencimiento según el período
        $stmt = $conn->prepare("SELECT type FROM plans WHERE id = ?");
        $stmt->execute([$sub['plan_id']]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $new_limit_date = '';
        if ($plan['type'] === 'monthly') {
            $new_limit_date = date('Y-m-d', strtotime($sub['limit_date'] . ' +30 days'));
        } else {
            $new_limit_date = date('Y-m-d', strtotime($sub['limit_date'] . ' +365 days'));
        }
        
        // Actualizar la suscripción
        $stmt = $conn->prepare("
            UPDATE subscriptions 
            SET limit_date = ?, status = 1
            WHERE id = ?
        ");
        $stmt->execute([$new_limit_date, $sub['id']]);
        
        // Activar la empresa si estaba inactiva
        $stmt = $conn->prepare("UPDATE configuracion SET activo = 1 WHERE id = ?");
        $stmt->execute([$sub['configuracion_id']]);
        
        // Registrar el pago
        $amount = $invoice['amount_paid'] / 100;
        $currency = strtoupper($invoice['currency']);
        
        $stmt = $conn->prepare("
            INSERT INTO payments (
                configuracion_id, subscription_id, plan_id, amount, currency,
                status, payment_method, payment_gateway_id, payment_gateway,
                payment_date, created_at
            ) VALUES (?, ?, ?, ?, ?, 'completed', 'stripe', ?, 'stripe', NOW(), NOW())
        ");
        $stmt->execute([
            $sub['configuracion_id'],
            $sub['id'],
            $sub['plan_id'],
            $amount,
            $currency,
            $invoice['id']
        ]);
        
        // Registrar actividad
        $stmt = $conn->prepare("
            INSERT INTO saas_activity_log (configuracion_id, user_id, action, description, ip_address)
            VALUES (?, NULL, 'subscription_renewed', ?, 'webhook')
        ");
        $description = "Renovación automática: $$amount $currency - Nueva fecha: $new_limit_date";
        $stmt->execute([$sub['configuracion_id'], $description]);
        
        $conn->commit();
        writeLog("Suscripción renovada exitosamente hasta: $new_limit_date");
        
        // ✅ Enviar email de confirmación de pago
        try {
            require_once __DIR__ . '/config/sendgrid.php';
            require_once __DIR__ . '/config/email-templates.php';
            
            // Obtener información de la empresa y plan
            $stmt = $conn->prepare("
                SELECT c.nombre, c.correo, p.name, p.amount, p.currency
                FROM configuracion c
                INNER JOIN plans p ON p.id = ?
                WHERE c.id = ?
            ");
            $stmt->execute([$sub['plan_id'], $sub['configuracion_id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data && $data['correo']) {
                $subject = '✅ Pago Recibido - Fercu Restaurante';
                $htmlContent = getPaymentConfirmationEmailHTML(
                    $data['nombre'],
                    number_format($amount, 2),
                    $currency,
                    $data['name'],
                    date('d/m/Y', strtotime($new_limit_date))
                );
                
                $result = sendEmail(
                    $data['correo'],
                    $data['nombre'],
                    $subject,
                    $htmlContent
                );
                
                logEmail($data['correo'], $subject, $result);
                
                if ($result['success']) {
                    writeLog("Email de confirmación enviado a: " . $data['correo']);
                } else {
                    writeLog("No se pudo enviar email de confirmación: " . $result['message']);
                }
            }
        } catch (Exception $e) {
            writeLog("Error al enviar email de confirmación: " . $e->getMessage());
        }
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        writeLog("Error en handleInvoicePaid: " . $e->getMessage());
    }
}

/**
 * Manejar pago de factura fallido
 */
function handleInvoicePaymentFailed($invoice, $conn) {
    writeLog("Invoice pago fallido: " . $invoice['id']);
    
    try {
        $subscription_id = $invoice['subscription'] ?? null;
        
        if (!$subscription_id) {
            return;
        }
        
        // Buscar la suscripción
        $stmt = $conn->prepare("
            SELECT * FROM subscriptions 
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$subscription_id]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sub) {
            return;
        }
        
        // Registrar el intento fallido
        $stmt = $conn->prepare("
            INSERT INTO saas_activity_log (configuracion_id, user_id, action, description, ip_address)
            VALUES (?, NULL, 'payment_failed', ?, 'webhook')
        ");
        $error = $invoice['last_payment_error']['message'] ?? 'Error desconocido';
        $description = "Pago recurrente fallido: $error";
        $stmt->execute([$sub['configuracion_id'], $description]);
        
        writeLog("Pago fallido registrado para configuracion_id: " . $sub['configuracion_id']);
        
        // TODO: Enviar email notificando el fallo y pedir actualización de método de pago
        
    } catch (Exception $e) {
        writeLog("Error en handleInvoicePaymentFailed: " . $e->getMessage());
    }
}

/**
 * Manejar actualización de suscripción
 */
function handleSubscriptionUpdated($subscription, $conn) {
    writeLog("Subscription actualizada: " . $subscription['id']);
    
    try {
        $subscription_id = $subscription['id'];
        $status = $subscription['status']; // active, past_due, canceled, etc
        $metadata = $subscription['metadata'];
        $configuracion_id = $metadata['configuracion_id'] ?? null;
        
        if (!$configuracion_id) {
            writeLog("Sin configuracion_id en metadata");
            return;
        }
        
        // Buscar o crear la suscripción en nuestra BD
        $stmt = $conn->prepare("
            SELECT id FROM subscriptions 
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$subscription_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $conn->beginTransaction();
        
        if (!$existing) {
            // Crear nueva suscripción
            $plan_id = $metadata['plan_id'] ?? null;
            if (!$plan_id) {
                $conn->rollBack();
                writeLog("Sin plan_id en metadata");
                return;
            }
            
            $stmt = $conn->prepare("SELECT type FROM plans WHERE id = ?");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $start_date = date('Y-m-d', $subscription['current_period_start']);
            $limit_date = date('Y-m-d', $subscription['current_period_end']);
            
            $stmt = $conn->prepare("
                INSERT INTO subscriptions (
                    configuracion_id, plan_id, start_date, limit_date,
                    status, stripe_subscription_id, stripe_customer_id, created_at
                ) VALUES (?, ?, ?, ?, 1, ?, ?, NOW())
            ");
            $stmt->execute([
                $configuracion_id,
                $plan_id,
                $start_date,
                $limit_date,
                $subscription_id,
                $subscription['customer']
            ]);
            
            writeLog("Nueva suscripción creada en BD");
        } else {
            // Actualizar fechas de la suscripción existente
            $limit_date = date('Y-m-d', $subscription['current_period_end']);
            $db_status = ($status === 'active') ? 1 : 0;
            
            $stmt = $conn->prepare("
                UPDATE subscriptions 
                SET limit_date = ?, status = ?
                WHERE stripe_subscription_id = ?
            ");
            $stmt->execute([$limit_date, $db_status, $subscription_id]);
            
            writeLog("Suscripción actualizada en BD, status: $status");
        }
        
        // Activar/desactivar empresa según el status
        $empresa_activa = in_array($status, ['active', 'trialing']) ? 1 : 0;
        $stmt = $conn->prepare("UPDATE configuracion SET activo = ? WHERE id = ?");
        $stmt->execute([$empresa_activa, $configuracion_id]);
        
        $conn->commit();
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        writeLog("Error en handleSubscriptionUpdated: " . $e->getMessage());
    }
}

/**
 * Manejar cancelación de suscripción
 */
function handleSubscriptionDeleted($subscription, $conn) {
    writeLog("Subscription cancelada: " . $subscription['id']);
    
    try {
        $subscription_id = $subscription['id'];
        
        // Buscar la suscripción
        $stmt = $conn->prepare("
            SELECT * FROM subscriptions 
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$subscription_id]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sub) {
            writeLog("Suscripción no encontrada en BD");
            return;
        }
        
        $conn->beginTransaction();
        
        // Desactivar la suscripción
        $stmt = $conn->prepare("UPDATE subscriptions SET status = 0 WHERE id = ?");
        $stmt->execute([$sub['id']]);
        
        // Desactivar la empresa
        $stmt = $conn->prepare("UPDATE configuracion SET activo = 0 WHERE id = ?");
        $stmt->execute([$sub['configuracion_id']]);
        
        // Registrar actividad
        $stmt = $conn->prepare("
            INSERT INTO saas_activity_log (configuracion_id, user_id, action, description, ip_address)
            VALUES (?, NULL, 'subscription_canceled', 'Suscripción cancelada', 'webhook')
        ");
        $stmt->execute([$sub['configuracion_id']]);
        
        $conn->commit();
        writeLog("Empresa desactivada por cancelación de suscripción");
        
        // TODO: Enviar email notificando la cancelación
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        writeLog("Error en handleSubscriptionDeleted: " . $e->getMessage());
    }
}
?>

