<?php
/**
 * Genera el PDF del Roadmap SaaS - Fercu Restaurante
 * Ejecutar: php generar-roadmap-pdf.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #333; margin: 25px; }
        h1 { color: #c45c26; font-size: 22px; border-bottom: 2px solid #c45c26; padding-bottom: 8px; margin-top: 0; }
        h2 { color: #2d5a3d; font-size: 16px; margin-top: 20px; }
        h3 { font-size: 13px; margin-top: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f0ebe3; font-weight: bold; }
        ul { margin: 5px 0; padding-left: 20px; }
        li { margin: 3px 0; }
        .check { color: #198754; }
        .pending { color: #856404; }
        .section { margin-bottom: 25px; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #666; }
        .highlight { background: #fff3cd; padding: 15px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>

<h1>ROADMAP SAAS - FERCU RESTAURANTE</h1>
<p><strong>Sistema de gestión de restaurantes con suscripciones</strong></p>
<p>Fecha: 10 de Febrero, 2025 | Versión: 1.0</p>

<div class="section">
<h2>1. LO QUE YA TENEMOS (IMPLEMENTADO)</h2>

<h3>1.1 Arquitectura Multi-Tenancy</h3>
<ul>
    <li>Base de datos con configuracion_id en todas las tablas</li>
    <li>Tabla configuracion para empresas/restaurantes</li>
    <li>Tabla plans con 5 planes (1 trial + 4 de pago)</li>
    <li>Tabla subscriptions y payments</li>
    <li>Log de actividades (saas_activity_log)</li>
    <li>Super Admin para administración del SaaS</li>
</ul>

<h3>1.2 Landing Page y Registro</h3>
<ul>
    <li>Landing profesional con diseño moderno</li>
    <li>Modal de registro con 3 opciones: Trial 15 días, Básico, Professional</li>
    <li>Validación trial único por email</li>
    <li>Generación automática de credenciales</li>
    <li>Envío de credenciales por email (SendGrid)</li>
    <li>Diseño responsive</li>
</ul>

<h3>1.3 Autenticación y Seguridad</h3>
<ul>
    <li>Login con validación de empresa activa</li>
    <li>Validación de suscripción vigente</li>
    <li>Roles: Admin, Mesero, Super Admin</li>
    <li>Hash de contraseñas (password_hash/verify)</li>
    <li>Variables de entorno para claves (.env)</li>
</ul>

<h3>1.4 Panel SAAS Admin</h3>
<ul>
    <li>Dashboard con estadísticas (empresas, ingresos, planes)</li>
    <li>Lista de empresas con búsqueda y filtros</li>
    <li>Ver detalle de empresa (suscripción, usuarios)</li>
    <li>Activar/Desactivar empresas</li>
    <li>Acceso solo para Super Admin</li>
</ul>

<h3>1.5 Integración Stripe</h3>
<ul>
    <li>4 productos sincronizados (Básico y Professional, mensual/anual)</li>
    <li>Checkout con Stripe Elements</li>
    <li>Webhook para invoice.paid, subscription.*</li>
    <li>Renovación automática</li>
    <li>Scripts: stripe-sync-plans, stripe-switch-mode, stripe-test-connection</li>
</ul>

<h3>1.6 Integración SendGrid</h3>
<ul>
    <li>Email de bienvenida con trial</li>
    <li>Email de bienvenida con suscripción de pago</li>
    <li>Email de confirmación de renovación</li>
    <li>Plantillas HTML profesionales</li>
</ul>

<h3>1.7 Gestión de Suscripción (Usuario)</h3>
<ul>
    <li>Ver plan actual y días restantes</li>
    <li>Actualizar método de pago</li>
    <li>Cambiar de plan (con proration)</li>
    <li>Historial de pagos</li>
    <li>Cancelar (inmediato o al final del período)</li>
    <li>Reactivar suscripción cancelada</li>
</ul>

<h3>1.8 Sistema POS para Restaurantes</h3>
<ul>
    <li>Control de mesas</li>
    <li>Gestión de órdenes</li>
    <li>Menú digital y productos</li>
    <li>Inventario de ingredientes</li>
    <li>Cierre de caja</li>
    <li>Gestión de personal</li>
    <li>Reportes y estadísticas</li>
</ul>
</div>

<div class="section">
<h2>2. LO QUE NOS FALTA (PENDIENTE)</h2>

<h3>2.1 Alta Prioridad - Antes de Producción</h3>
<ul class="pending">
    <li>Pruebas completas en modo TEST (registro, pagos, webhooks)</li>
    <li>Migración Stripe a modo LIVE</li>
    <li>Configurar webhook en Stripe LIVE</li>
    <li>Certificado SSL/HTTPS</li>
    <li>Backup de base de datos antes de lanzar</li>
</ul>

<h3>2.2 Media Prioridad - SaaS Profesional</h3>
<ul class="pending">
    <li><strong>Notificaciones por email:</strong> Recordatorio 7 días antes de vencer, pago fallido, cancelación</li>
    <li><strong>Facturación:</strong> Generar facturas PDF, descargar desde historial</li>
    <li><strong>Integración SAT (México):</strong> Facturación electrónica CFDI</li>
    <li><strong>Métricas de uso:</strong> Dashboard por empresa, alertas de límite de plan</li>
</ul>

<h3>2.3 Baja Prioridad - Mejoras Futuras</h3>
<ul class="pending">
    <li>Cupones y códigos promocionales</li>
    <li>Programa de referidos</li>
    <li>2FA (autenticación de dos factores)</li>
    <li>Log de accesos y alertas de seguridad</li>
    <li>Tour guiado para nuevos usuarios</li>
    <li>Chat de soporte</li>
    <li>Reportes SAAS avanzados (churn, LTV)</li>
</ul>
</div>

<div class="section">
<h2>3. PLANES Y PRECIOS ACTUALES</h2>
<table>
<tr><th>Plan</th><th>Mensual</th><th>Anual</th><th>Características</th></tr>
<tr><td>Prueba Gratuita</td><td>$0 (15 días)</td><td>-</td><td>Sin tarjeta, acceso completo</td></tr>
<tr><td>Básico</td><td>$399 MXN</td><td>$3,990 MXN (17% off)</td><td>10 mesas, 3 usuarios</td></tr>
<tr><td>Professional</td><td>$899 MXN</td><td>$8,630 MXN (20% off)</td><td>Ilimitado</td></tr>
</table>
</div>

<div class="section highlight">
<h2>4. CONCLUSIÓN</h2>
<p><strong>Estado actual:</strong> El sistema está ~95% completo para funcionar como SaaS.</p>
<p><strong>Para ser un SaaS 100% profesional falta:</strong></p>
<ul>
    <li>Completar pruebas en TEST</li>
    <li>Migrar a producción (Stripe LIVE, HTTPS)</li>
    <li>Notificaciones automáticas de vencimiento</li>
    <li>Facturación electrónica (opcional para México)</li>
</ul>
<p><strong>Documentación:</strong> Toda la guía de instalación y configuración está en la carpeta <code>documentacion/</code></p>
</div>

<div class="footer">
<p>Fercu Restaurante - Sistema de Gestión SaaS para Restaurantes</p>
<p>Contacto: contacto@fercupuntodeventa.com</p>
</div>

</body>
</html>
HTML;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$outputPath = __DIR__ . '/documentacion/ROADMAP_SAAS_FERCU.pdf';
$output = $dompdf->output();
file_put_contents($outputPath, $output);

echo "PDF generado correctamente en: documentacion/ROADMAP_SAAS_FERCU.pdf\n";
