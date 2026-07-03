# Propuesta Comercial de Servicios

## 1. Datos generales

- **Proveedor:** Fercu / Equipo de desarrollo
- **Cliente:** [Nombre del cliente]
- **Proyecto:** Fercu Restaurante SaaS
- **Versión de propuesta:** v1.1
- **Fecha de emisión:** 06 de mayo de 2026
- **Vigencia de propuesta:** 15 días naturales (hasta el 21 de mayo de 2026)
- **Moneda:** MXN

---

## 2. Resumen ejecutivo

Esta propuesta formaliza servicios técnicos para fortalecer y evolucionar el sistema SaaS.  
El objetivo es contratar por bloques cerrados (fases o adicionales), con entregables claros y costo por cada alcance.

---

## 3. Arquitectura comercial del proyecto

El proyecto se divide en dos módulos de venta y operación:

1. **Landing pública (adquisición):**
- Sitio público de marketing.
- Registro de nuevas empresas.
- Selección de planes y conversión comercial.

2. **Sistema SaaS privado (operación):**
- Backoffice autenticado del restaurante.
- Gestión operativa (mesas, órdenes, productos, inventario, caja, ventas).
- Suscripciones y ciclo de vida de cliente.

---

## 4. Paquete 1 - Crítico técnico (recomendado inmediato)

Objetivo: cerrar riesgos de seguridad/ingresos antes de escalar clientes.

Incluye:
1. Cierre de bypass de cobro y validación de pago Stripe en flujo de suscripción.
2. Validación estricta de ownership tenant en endpoints de suscripción (`stripe_subscription_id` ligado al `configuracion_id` de sesión).
3. Protección de endpoints sensibles expuestos (impresión térmica y pruebas).
4. Idempotencia persistente en webhooks Stripe (control por `event.id`).
5. Hardening esencial: CSRF en acciones críticas + CORS restringido por dominio.

No incluye:
1. Pentest externo certificado.
2. Refactor completo de UI/UX.
3. Nuevas funcionalidades de negocio.
4. Integraciones de terceros fuera de Stripe/Mailgun.

---

## 5. Paquete 2 - Mejoras vendibles (upsell)

Estas mejoras se pueden cotizar por separado y vender por prioridad:

1. **Gestión avanzada de eventos de cobro**
- Reconciliación periódica Stripe vs estado local.
- Reporte de discrepancias y auto-corrección guiada.

2. **Manejo robusto de fallos y reintentos**
- Reintentos automáticos controlados.
- Reglas por tipo de error de cobro.

3. **Recuperación/reactivación de suscripción**
- Flujos guiados de recuperación para pagos fallidos.
- Notificaciones automáticas de reactivación.

4. **Alertas de consumo y vencimiento por plan**
- Alertas por proximidad a fecha de corte.
- Alertas de consumo según reglas comerciales.

5. **Enforcement real de límites por plan**
- Aplicar límites de plan en backend:
  - Básico: 3 usuarios y 8 mesas.
  - Enterprise: sin límite de usuarios/mesas.

6. **Reglas anti-abuso del trial**
- Controles adicionales más allá de correo (IP, dispositivo, fingerprint, ventana temporal, etc.).

---

## 6. Entregables

1. Código implementado en repositorio.
2. Documento de cambios y endpoints protegidos (antes/después).
3. Evidencia de pruebas funcionales y validaciones de seguridad básica.
4. Checklist de configuración para producción.
5. Guía corta operativa para soporte inicial.

---

## 7. Esquema económico

### 7.1 Costo del paquete crítico

- **Paquete 1 - Crítico técnico:** **$[Monto] MXN + IVA**

### 7.2 Costo de mejoras vendibles (opcionales)

- **Se cotizan por ítem o por bolsa de horas**, según complejidad y urgencia.

### 7.3 Forma de pago sugerida

1. **50% anticipo** al inicio.
2. **50% contra entrega** de alcance y evidencias.

### 7.4 Condiciones

1. Todo trabajo adicional fuera de este alcance se cotiza por separado.
2. El cliente debe proveer accesos, dominios y ambientes requeridos.
3. Si hay pausa por falta de información del cliente por más de 5 días hábiles, el calendario se recorre.

---

## 8. Cronograma estimado

- **Inicio estimado:** [Fecha]
- **Duración estimada:** [X] días hábiles
- **Entrega estimada:** [Fecha]

> El calendario final se confirma al recibir anticipo y accesos.

---

## 9. Control de cambios (adicionales cobrables)

Este apartado permite sumar nuevos puntos sin rehacer la propuesta.

| ID | Concepto adicional | Descripción breve | Tipo (fijo/hrs) | Monto MXN | Estado |
|---|---|---|---|---:|---|
| AD-001 | [Ejemplo] Reporte ejecutivo mensual | Dashboard PDF con KPIs | Fijo | $[Monto] | Pendiente |
| AD-002 | [Reservado] |  |  |  |  |
| AD-003 | [Reservado] |  |  |  |  |

Estados sugeridos:
- `Pendiente`
- `Aprobado`
- `En ejecución`
- `Entregado`
- `Facturado`

---

## 10. Bolsa de horas (opcional para extras)

Si el cliente prefiere flexibilidad:

- **Bolsa mínima:** 10 horas
- **Tarifa por hora:** $[Monto]/hora + IVA
- **Caducidad de bolsa:** [30/60/90] días
- **Consumo mínimo por intervención:** 1 hora

---

## 11. Criterios de aceptación

Se considera entregado cuando:
1. Se completa el alcance definido en esta propuesta.
2. Se entregan evidencias y documentación acordadas.
3. El cliente valida funcionalmente en ambiente acordado.

---

## 12. Soporte post-entrega

- **Garantía técnica de ajustes por defecto de implementación:** [7/15/30] días naturales.
- No cubre nuevas funcionalidades ni cambios de alcance.

---

## 13. Aprobación

**Cliente**  
Nombre: ____________________  
Cargo: _____________________  
Firma: _____________________  
Fecha: _____________________

**Proveedor**  
Nombre: ____________________  
Cargo: _____________________  
Firma: _____________________  
Fecha: _____________________

---

## Anexo A - Catálogo rápido de adicionales vendibles

1. Monitoreo básico y alertas de errores.
2. Hardening avanzado de panel administrativo.
3. Auditoría de roles y permisos por módulo.
4. Optimización de rendimiento de consultas críticas.
5. Automatización de respaldos y restauración probada.
6. Reportes ejecutivos para dueño de restaurante.
7. Entrenamiento operativo para personal del cliente.

## Anexo B - Recomendación comercial

Para clientes con presupuesto limitado:
1. Vender primero **servicio base de hardening**.
2. Luego agregar adicionales por impacto y urgencia.
3. Usar la tabla de control de cambios para aprobar cada nuevo cobro.
