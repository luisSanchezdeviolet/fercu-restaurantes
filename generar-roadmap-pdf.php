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
<p>Fecha: 15 de Abril, 2026 | Versión: 1.2</p>

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
    <li>Envío de credenciales por email (Mailgun)</li>
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

<h3>1.6 Integración Mailgun</h3>
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
<h2>2. ESTADO POR FASES (PENDIENTES Y COMPLETADAS)</h2>

<h3>2.1 Fase 1 - Acceso y aislamiento tenant (COMPLETADA)</h3>
<ul class="check">
    <li>APIs operativas protegidas con sesión</li>
    <li>Filtros por configuracion_id en controladores críticos</li>
    <li>Vistas operativas protegidas con requireLogin()</li>
</ul>

<h3>2.2 Fase 2 - Seguridad operativa base (COMPLETADA)</h3>
<ul class="check">
    <li>Base de datos migrada a variables de entorno</li>
    <li>Plantilla .env.example incorporada</li>
    <li>Registro sin exposición de contraseña temporal en JSON</li>
    <li>Hardening mínimo de sesión (strict mode, httponly, samesite, regenerate id)</li>
</ul>

<h3>2.3 Fase 3 - Billing robusto (PENDIENTE)</h3>
<ul class="pending">
    <li>Idempotencia de webhooks Stripe</li>
    <li>Reconciliación de cobros/eventos</li>
    <li>Manejo robusto de fallos y reintentos</li>
</ul>

<h3>2.4 Fase 4 - Reglas comerciales SaaS (PENDIENTE)</h3>
<ul class="pending">
    <li>Enforcement real de límites por plan (usuarios, mesas, etc.)</li>
    <li>Políticas anti-abuso de trial</li>
    <li>Alertas de consumo y vencimiento por plan</li>
</ul>

<h3>2.5 Fase 5 - Operación y salida a producción (PENDIENTE)</h3>
<ul class="pending">
    <li>Pruebas E2E formales (registro, pago, renovación, cancelación)</li>
    <li>Migraciones SQL versionadas</li>
    <li>Observabilidad y runbook operativo</li>
    <li>Checklist final de producción (backup, rollback, monitoreo)</li>
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
<p><strong>Estado actual:</strong> Fase 1 y Fase 2 completadas; proyecto en transición a Fase 3.</p>
<p><strong>Para ser un SaaS 100% profesional falta:</strong></p>
<ul>
    <li>Completar Fase 3 (billing robusto)</li>
    <li>Completar Fase 4 (límites y reglas comerciales)</li>
    <li>Completar Fase 5 (operación y salida a producción)</li>
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
