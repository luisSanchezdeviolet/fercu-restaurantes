<?php
/**
 * Controlador para gestión de suscripciones
 */

class SubscriptionController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener detalles completos de la suscripción actual del usuario
     */
    public function getCurrentSubscription($configuracion_id) {
        try {
            $query = "
                SELECT 
                    s.*,
                    p.name as plan_name,
                    p.type as plan_type,
                    p.amount as plan_amount,
                    p.currency as plan_currency,
                    p.description as plan_description,
                    p.features as plan_features,
                    c.nombre as empresa_nombre,
                    c.correo as empresa_correo
                FROM subscriptions s
                INNER JOIN plans p ON s.plan_id = p.id
                INNER JOIN configuracion c ON s.configuracion_id = c.id
                WHERE s.configuracion_id = ? AND s.status = 1
                ORDER BY s.created_at DESC
                LIMIT 1
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$configuracion_id]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($subscription) {
                // Calcular días restantes
                $today = new DateTime();
                $limit_date = new DateTime($subscription['limit_date']);
                $interval = $today->diff($limit_date);
                $subscription['days_remaining'] = $interval->days;
                $subscription['is_expired'] = $today > $limit_date;
                
                // Decodificar features si existen
                if (!empty($subscription['plan_features'])) {
                    $subscription['plan_features'] = json_decode($subscription['plan_features'], true);
                }
                
                return ['success' => true, 'data' => $subscription];
            }
            
            return ['success' => false, 'message' => 'No se encontró una suscripción activa'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener todos los planes disponibles
     */
    public function getAvailablePlans() {
        try {
            $query = "
                SELECT * FROM plans 
                WHERE status = 'active' AND type != 'trial'
                ORDER BY amount ASC
            ";
            
            $stmt = $this->conn->query($query);
            $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($plans as &$plan) {
                if (!empty($plan['features'])) {
                    $plan['features'] = json_decode($plan['features'], true);
                }
            }
            
            return ['success' => true, 'data' => $plans];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener historial de pagos
     */
    public function getPaymentHistory($configuracion_id, $limit = 10) {
        try {
            $query = "
                SELECT 
                    p.*,
                    pl.name as plan_name,
                    pl.type as plan_type
                FROM payments p
                LEFT JOIN plans pl ON p.plan_id = pl.id
                WHERE p.configuracion_id = ?
                ORDER BY p.payment_date DESC
                LIMIT ?
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(1, $configuracion_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'data' => $payments];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener información de Stripe sobre la suscripción
     */
    public function getStripeSubscriptionInfo($stripe_subscription_id) {
        try {
            if (empty($stripe_subscription_id)) {
                return ['success' => false, 'message' => 'No hay suscripción en Stripe'];
            }
            
            require_once __DIR__ . '/../vendor/autoload.php';
            require_once __DIR__ . '/../config/stripe.php';
            
            $subscription = \Stripe\Subscription::retrieve($stripe_subscription_id);
            
            $data = [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end),
                'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
                'cancel_at_period_end' => $subscription->cancel_at_period_end,
                'canceled_at' => $subscription->canceled_at ? date('Y-m-d H:i:s', $subscription->canceled_at) : null,
                'default_payment_method' => $subscription->default_payment_method,
            ];
            
            // Obtener información del método de pago
            if ($subscription->default_payment_method) {
                try {
                    $paymentMethod = \Stripe\PaymentMethod::retrieve($subscription->default_payment_method);
                    $data['payment_method_details'] = [
                        'type' => $paymentMethod->type,
                        'card_brand' => $paymentMethod->card->brand ?? null,
                        'card_last4' => $paymentMethod->card->last4 ?? null,
                        'card_exp_month' => $paymentMethod->card->exp_month ?? null,
                        'card_exp_year' => $paymentMethod->card->exp_year ?? null,
                    ];
                } catch (Exception $e) {
                    $data['payment_method_details'] = null;
                }
            }
            
            return ['success' => true, 'data' => $data];
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ['success' => false, 'message' => 'Error de Stripe: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Actualizar método de pago en Stripe
     */
    public function updatePaymentMethod($stripe_subscription_id, $payment_method_id) {
        try {
            require_once __DIR__ . '/../vendor/autoload.php';
            require_once __DIR__ . '/../config/stripe.php';
            
            // Obtener la suscripción
            $subscription = \Stripe\Subscription::retrieve($stripe_subscription_id);
            
            // Actualizar el método de pago por defecto
            \Stripe\Subscription::update($stripe_subscription_id, [
                'default_payment_method' => $payment_method_id
            ]);
            
            // También actualizar en el customer
            \Stripe\Customer::update($subscription->customer, [
                'invoice_settings' => [
                    'default_payment_method' => $payment_method_id
                ]
            ]);
            
            return ['success' => true, 'message' => 'Método de pago actualizado correctamente'];
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ['success' => false, 'message' => 'Error de Stripe: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Cambiar de plan (upgrade o downgrade)
     */
    public function changePlan($configuracion_id, $new_plan_id, $stripe_subscription_id) {
        try {
            $this->conn->beginTransaction();
            
            // Obtener el nuevo plan
            $stmt = $this->conn->prepare("SELECT * FROM plans WHERE id = ? AND status = 'active'");
            $stmt->execute([$new_plan_id]);
            $newPlan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$newPlan) {
                throw new Exception('Plan no encontrado o inactivo');
            }
            
            if (empty($newPlan['stripe_price_id'])) {
                throw new Exception('El plan no tiene configurado un Price ID de Stripe');
            }
            
            require_once __DIR__ . '/../vendor/autoload.php';
            require_once __DIR__ . '/../config/stripe.php';
            
            // Obtener la suscripción actual de Stripe
            $subscription = \Stripe\Subscription::retrieve($stripe_subscription_id);
            
            // Cambiar el plan en Stripe (con proration automática)
            \Stripe\Subscription::update($stripe_subscription_id, [
                'items' => [[
                    'id' => $subscription->items->data[0]->id,
                    'price' => $newPlan['stripe_price_id'],
                ]],
                'proration_behavior' => 'create_prorations', // Cálculo prorrateado automático
            ]);
            
            // Actualizar en nuestra BD
            $stmt = $this->conn->prepare("
                UPDATE subscriptions 
                SET plan_id = ?, updated_at = NOW()
                WHERE configuracion_id = ? AND status = 1
            ");
            $stmt->execute([$new_plan_id, $configuracion_id]);
            
            // Registrar en el log
            $stmt = $this->conn->prepare("
                INSERT INTO saas_activity_log (configuracion_id, action, description, created_at)
                VALUES (?, 'plan_changed', ?, NOW())
            ");
            $description = "Cambio de plan a: " . $newPlan['name'];
            $stmt->execute([$configuracion_id, $description]);
            
            $this->conn->commit();
            
            return [
                'success' => true, 
                'message' => 'Plan actualizado correctamente',
                'data' => ['new_plan' => $newPlan]
            ];
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Error de Stripe: ' . $e->getMessage()];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Cancelar suscripción
     */
    public function cancelSubscription($configuracion_id, $stripe_subscription_id, $cancel_immediately = false) {
        try {
            $this->conn->beginTransaction();
            
            require_once __DIR__ . '/../vendor/autoload.php';
            require_once __DIR__ . '/../config/stripe.php';
            
            if ($cancel_immediately) {
                // Cancelar inmediatamente
                \Stripe\Subscription::update($stripe_subscription_id, [
                    'cancel_at_period_end' => false
                ]);
                \Stripe\Subscription::cancel($stripe_subscription_id);
                
                // Desactivar en nuestra BD
                $stmt = $this->conn->prepare("
                    UPDATE subscriptions 
                    SET status = 0, updated_at = NOW()
                    WHERE configuracion_id = ? AND status = 1
                ");
                $stmt->execute([$configuracion_id]);
                
                // Desactivar empresa
                $stmt = $this->conn->prepare("UPDATE configuracion SET activo = 0 WHERE id = ?");
                $stmt->execute([$configuracion_id]);
                
                $message = 'Suscripción cancelada inmediatamente';
                
            } else {
                // Cancelar al final del período
                \Stripe\Subscription::update($stripe_subscription_id, [
                    'cancel_at_period_end' => true
                ]);
                
                $message = 'Suscripción programada para cancelarse al final del período';
            }
            
            // Registrar en el log
            $stmt = $this->conn->prepare("
                INSERT INTO saas_activity_log (configuracion_id, action, description, created_at)
                VALUES (?, 'subscription_cancelled', ?, NOW())
            ");
            $description = $cancel_immediately ? 'Cancelación inmediata' : 'Cancelación al final del período';
            $stmt->execute([$configuracion_id, $description]);
            
            $this->conn->commit();
            
            return ['success' => true, 'message' => $message];
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Error de Stripe: ' . $e->getMessage()];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Reactivar una suscripción que fue programada para cancelarse
     */
    public function reactivateSubscription($stripe_subscription_id) {
        try {
            require_once __DIR__ . '/../vendor/autoload.php';
            require_once __DIR__ . '/../config/stripe.php';
            
            // Remover la cancelación programada
            \Stripe\Subscription::update($stripe_subscription_id, [
                'cancel_at_period_end' => false
            ]);
            
            return ['success' => true, 'message' => 'Suscripción reactivada correctamente'];
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ['success' => false, 'message' => 'Error de Stripe: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>


