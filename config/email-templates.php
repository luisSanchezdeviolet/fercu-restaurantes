<?php
/**
 * Plantillas de emails para Fercu Restaurante
 */

/**
 * Email de bienvenida con prueba gratuita (15 días)
 */
function getWelcomeTrialEmailHTML($nombre_empresa, $nombre_usuario, $email, $password, $fecha_expiracion) {
    $loginUrl = LOGIN_URL;
    $dashboardUrl = DASHBOARD_URL;
    $supportEmail = SENDGRID_SUPPORT_EMAIL;
    
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Fercu Restaurante</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px 20px; }
        .badge { display: inline-block; background: #28a745; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: bold; margin: 10px 0; }
        .info-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .info-box strong { color: #667eea; }
        .credentials { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .btn { display: inline-block; background: #667eea; color: white; text-decoration: none; padding: 14px 30px; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .btn:hover { background: #5568d3; }
        .features { margin: 20px 0; }
        .feature-item { padding: 10px 0; border-bottom: 1px solid #eee; }
        .feature-item:last-child { border-bottom: none; }
        .feature-item strong { color: #28a745; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 14px; }
        .footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ¡Bienvenido a Fercu Restaurante!</h1>
            <span class="badge">✨ PRUEBA GRATUITA - 15 DÍAS</span>
        </div>
        
        <div class="content">
            <h2>¡Hola! 👋</h2>
            <p>Gracias por registrarte en <strong>Fercu Restaurante</strong>. Tu cuenta ha sido creada exitosamente y ya puedes empezar a gestionar tu restaurante de manera profesional.</p>
            
            <div class="info-box">
                <strong>📋 Información de tu cuenta:</strong><br>
                <strong>Empresa:</strong> $nombre_empresa<br>
                <strong>Email:</strong> $email<br>
                <strong>Expira el:</strong> $fecha_expiracion
            </div>
            
            <div class="credentials">
                <strong>🔐 Tus credenciales de acceso:</strong><br>
                <strong>Email:</strong> $email<br>
                <strong>Contraseña:</strong> $password<br>
                <em style="font-size: 12px; color: #856404;">⚠️ Te recomendamos cambiar tu contraseña después del primer inicio de sesión.</em>
            </div>
            
            <div style="text-align: center;">
                <a href="$loginUrl" class="btn">🚀 Acceder a mi Cuenta</a>
            </div>
            
            <h3>✨ Lo que puedes hacer durante tu prueba gratuita:</h3>
            <div class="features">
                <div class="feature-item"><strong>✓</strong> Gestionar hasta 10 mesas</div>
                <div class="feature-item"><strong>✓</strong> Crear hasta 3 usuarios</div>
                <div class="feature-item"><strong>✓</strong> Órdenes ilimitadas</div>
                <div class="feature-item"><strong>✓</strong> Control de inventario</div>
                <div class="feature-item"><strong>✓</strong> Reportes básicos</div>
                <div class="feature-item"><strong>✓</strong> Soporte por email</div>
            </div>
            
            <div class="info-box">
                <strong>💡 Próximos pasos:</strong><br>
                1. Inicia sesión en tu cuenta<br>
                2. Configura tus mesas y productos<br>
                3. Invita a tu equipo de trabajo<br>
                4. ¡Comienza a recibir órdenes!
            </div>
            
            <p style="margin-top: 30px;">¿Necesitas ayuda? Escríbenos a <a href="mailto:$supportEmail">$supportEmail</a></p>
        </div>
        
        <div class="footer">
            <p><strong>Fercu Restaurante</strong><br>
            Sistema de gestión para restaurantes</p>
            <p>
                <a href="$dashboardUrl">Panel de Control</a> | 
                <a href="mailto:$supportEmail">Soporte</a>
            </p>
            <p style="font-size: 12px; color: #999;">
                Este es un correo automático, por favor no respondas a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Email de bienvenida con suscripción de pago
 */
function getWelcomeSubscriptionEmailHTML($nombre_empresa, $nombre_usuario, $email, $password, $plan_name, $plan_amount, $plan_type, $fecha_expiracion) {
    $loginUrl = LOGIN_URL;
    $dashboardUrl = DASHBOARD_URL;
    $supportEmail = SENDGRID_SUPPORT_EMAIL;
    
    $intervalText = ($plan_type === 'monthly') ? 'mensual' : 'anual';
    
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Fercu Restaurante</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .badge { display: inline-block; background: #28a745; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: bold; margin: 10px 0; }
        .content { padding: 30px 20px; }
        .info-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .info-box strong { color: #667eea; }
        .credentials { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .plan-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .plan-box h3 { margin: 0 0 10px 0; }
        .plan-box .price { font-size: 32px; font-weight: bold; margin: 10px 0; }
        .btn { display: inline-block; background: #667eea; color: white; text-decoration: none; padding: 14px 30px; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .btn:hover { background: #5568d3; }
        .features { margin: 20px 0; }
        .feature-item { padding: 10px 0; border-bottom: 1px solid #eee; }
        .feature-item:last-child { border-bottom: none; }
        .feature-item strong { color: #28a745; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 14px; }
        .footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ¡Bienvenido a Fercu Restaurante!</h1>
            <span class="badge">✨ SUSCRIPCIÓN ACTIVA</span>
        </div>
        
        <div class="content">
            <h2>¡Gracias por confiar en nosotros! 🙏</h2>
            <p>Tu suscripción a <strong>Fercu Restaurante</strong> ha sido activada exitosamente. Ya puedes empezar a gestionar tu restaurante de manera profesional.</p>
            
            <div class="plan-box">
                <h3>📦 Tu Plan</h3>
                <div class="price">\$$plan_amount MXN</div>
                <p style="margin: 0;"><strong>$plan_name</strong> - Renovación $intervalText</p>
                <p style="font-size: 14px; margin: 10px 0 0 0;">Próxima renovación: $fecha_expiracion</p>
            </div>
            
            <div class="info-box">
                <strong>📋 Información de tu cuenta:</strong><br>
                <strong>Empresa:</strong> $nombre_empresa<br>
                <strong>Email:</strong> $email
            </div>
            
            <div class="credentials">
                <strong>🔐 Tus credenciales de acceso:</strong><br>
                <strong>Email:</strong> $email<br>
                <strong>Contraseña:</strong> $password<br>
                <em style="font-size: 12px; color: #856404;">⚠️ Te recomendamos cambiar tu contraseña después del primer inicio de sesión.</em>
            </div>
            
            <div style="text-align: center;">
                <a href="$loginUrl" class="btn">🚀 Acceder a mi Cuenta</a>
            </div>
            
            <h3>✨ Beneficios de tu plan:</h3>
            <div class="features">
                <div class="feature-item"><strong>✓</strong> Mesas ilimitadas</div>
                <div class="feature-item"><strong>✓</strong> Usuarios ilimitados</div>
                <div class="feature-item"><strong>✓</strong> Órdenes ilimitadas</div>
                <div class="feature-item"><strong>✓</strong> Control de inventario avanzado</div>
                <div class="feature-item"><strong>✓</strong> Reportes detallados</div>
                <div class="feature-item"><strong>✓</strong> Soporte prioritario</div>
                <div class="feature-item"><strong>✓</strong> Actualizaciones automáticas</div>
            </div>
            
            <div class="info-box">
                <strong>💡 Próximos pasos:</strong><br>
                1. Inicia sesión en tu cuenta<br>
                2. Configura tus mesas y productos<br>
                3. Invita a tu equipo de trabajo<br>
                4. ¡Comienza a recibir órdenes!
            </div>
            
            <div class="info-box">
                <strong>💳 Sobre tu suscripción:</strong><br>
                • Tu tarjeta será cargada automáticamente cada $intervalText<br>
                • Recibirás un recibo por cada cargo<br>
                • Puedes cancelar en cualquier momento desde tu panel de control
            </div>
            
            <p style="margin-top: 30px;">¿Necesitas ayuda? Escríbenos a <a href="mailto:$supportEmail">$supportEmail</a></p>
        </div>
        
        <div class="footer">
            <p><strong>Fercu Restaurante</strong><br>
            Sistema de gestión para restaurantes</p>
            <p>
                <a href="$dashboardUrl">Panel de Control</a> | 
                <a href="mailto:$supportEmail">Soporte</a>
            </p>
            <p style="font-size: 12px; color: #999;">
                Este es un correo automático, por favor no respondas a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Email de confirmación de pago de suscripción
 */
function getPaymentConfirmationEmailHTML($nombre_empresa, $amount, $currency, $plan_name, $fecha_proximo_pago) {
    $dashboardUrl = DASHBOARD_URL;
    $supportEmail = SENDGRID_SUPPORT_EMAIL;
    
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Recibido - Fercu Restaurante</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: #28a745; color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px 20px; }
        .amount-box { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 25px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .amount-box .amount { font-size: 42px; font-weight: bold; margin: 10px 0; }
        .info-box { background: #f8f9fa; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .info-box strong { color: #28a745; }
        .btn { display: inline-block; background: #667eea; color: white; text-decoration: none; padding: 14px 30px; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 14px; }
        .footer a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Pago Recibido</h1>
        </div>
        
        <div class="content">
            <h2>¡Gracias por tu pago! 💚</h2>
            <p>Hemos recibido correctamente el pago de tu suscripción a <strong>Fercu Restaurante</strong>.</p>
            
            <div class="amount-box">
                <div style="font-size: 18px;">Monto Pagado</div>
                <div class="amount">\$$amount $currency</div>
                <div>$plan_name</div>
            </div>
            
            <div class="info-box">
                <strong>📋 Detalles del pago:</strong><br>
                <strong>Empresa:</strong> $nombre_empresa<br>
                <strong>Plan:</strong> $plan_name<br>
                <strong>Monto:</strong> \$$amount $currency<br>
                <strong>Fecha de pago:</strong> ${fecha_proximo_pago}<br>
                <strong>Estado:</strong> ✅ Completado
            </div>
            
            <div class="info-box">
                <strong>📅 Próximo pago:</strong><br>
                Tu próximo cargo será procesado el: <strong>$fecha_proximo_pago</strong>
            </div>
            
            <div style="text-align: center;">
                <a href="$dashboardUrl" class="btn">📊 Ver mi Panel de Control</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
                <strong>Nota:</strong> Este email sirve como comprobante de pago. Puedes descargar tu factura desde tu panel de control.
            </p>
            
            <p>¿Tienes alguna pregunta? Escríbenos a <a href="mailto:$supportEmail">$supportEmail</a></p>
        </div>
        
        <div class="footer">
            <p><strong>Fercu Restaurante</strong><br>
            Sistema de gestión para restaurantes</p>
            <p>
                <a href="$dashboardUrl">Panel de Control</a> | 
                <a href="mailto:$supportEmail">Soporte</a>
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}
?>


