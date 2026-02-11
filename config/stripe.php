<?php
/**
 * Configuración de Stripe para Fercu Restaurante
 * 
 * Las claves se cargan desde variables de entorno (.env)
 * Ver .env.example para la lista de variables requeridas.
 * 
 * Para obtener tus claves de Stripe:
 * 1. Crea una cuenta en https://dashboard.stripe.com/register
 * 2. Ve a Developers > API keys
 * 3. Copia las claves de prueba (test) o producción (live)
 * 4. Agrégales a tu archivo .env
 */

require_once __DIR__ . '/env.php';

// Determinar el modo (test o live)
define('STRIPE_MODE', getenv('STRIPE_MODE') ?: 'test');

// Claves de Stripe - MODO TEST (desde variables de entorno)
define('STRIPE_TEST_PUBLIC_KEY', getenv('STRIPE_TEST_PUBLIC_KEY') ?: '');
define('STRIPE_TEST_SECRET_KEY', getenv('STRIPE_TEST_SECRET_KEY') ?: '');
define('STRIPE_TEST_WEBHOOK_SECRET', getenv('STRIPE_TEST_WEBHOOK_SECRET') ?: '');

// Claves de Stripe - MODO LIVE (desde variables de entorno)
define('STRIPE_LIVE_PUBLIC_KEY', getenv('STRIPE_LIVE_PUBLIC_KEY') ?: '');
define('STRIPE_LIVE_SECRET_KEY', getenv('STRIPE_LIVE_SECRET_KEY') ?: '');
define('STRIPE_LIVE_WEBHOOK_SECRET', getenv('STRIPE_LIVE_WEBHOOK_SECRET') ?: '');

// Configuración activa según el modo
define('STRIPE_PUBLIC_KEY', STRIPE_MODE === 'live' ? STRIPE_LIVE_PUBLIC_KEY : STRIPE_TEST_PUBLIC_KEY);
define('STRIPE_SECRET_KEY', STRIPE_MODE === 'live' ? STRIPE_LIVE_SECRET_KEY : STRIPE_TEST_SECRET_KEY);
define('STRIPE_WEBHOOK_SECRET', STRIPE_MODE === 'live' ? STRIPE_LIVE_WEBHOOK_SECRET : STRIPE_TEST_WEBHOOK_SECRET);

// Versión de la API de Stripe
define('STRIPE_API_VERSION', '2023-10-16');

// Configuración de moneda
define('STRIPE_CURRENCY', 'mxn'); // Pesos mexicanos

// Cargar el autoloader de Composer si no está cargado
if (!class_exists('\Stripe\Stripe')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Inicializar Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
\Stripe\Stripe::setApiVersion(STRIPE_API_VERSION);

// URLs para redirección
define('STRIPE_SUCCESS_URL', 'https://restaurante.fercupuntodeventa.com/payment-success.php');
define('STRIPE_CANCEL_URL', 'https://restaurante.fercupuntodeventa.com/payment-cancel.php');

// Webhook endpoint
define('STRIPE_WEBHOOK_URL', 'https://restaurante.fercupuntodeventa.com/stripe-webhook.php');

/**
 * Función helper para obtener la configuración de Stripe
 */
function getStripeConfig() {
    return [
        'mode' => STRIPE_MODE,
        'public_key' => STRIPE_PUBLIC_KEY,
        'secret_key' => STRIPE_SECRET_KEY,
        'webhook_secret' => STRIPE_WEBHOOK_SECRET,
        'api_version' => STRIPE_API_VERSION,
        'currency' => STRIPE_CURRENCY
    ];
}

/**
 * Función helper para hacer peticiones a la API de Stripe
 */
function stripeRequest($endpoint, $method = 'POST', $data = []) {
    $url = 'https://api.stripe.com/v1/' . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Stripe-Version: ' . STRIPE_API_VERSION,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    } elseif ($method === 'GET') {
        $url .= '?' . http_build_query($data);
        curl_setopt($ch, CURLOPT_URL, $url);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception('Error en petición a Stripe: ' . $error);
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode >= 400) {
        $errorMsg = isset($result['error']['message']) ? $result['error']['message'] : 'Error desconocido';
        throw new Exception('Error de Stripe: ' . $errorMsg);
    }
    
    return $result;
}
?>
