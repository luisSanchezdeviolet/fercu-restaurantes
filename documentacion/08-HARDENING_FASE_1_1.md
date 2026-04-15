# 🔒 Fase 1.1 - Hardening de Seguridad (Propuesta Comercial)

## Objetivo
Implementar una capa de seguridad adicional sobre la Fase 1 para reducir riesgos de abuso, proteger sesiones y fortalecer APIs antes de escalar clientes SaaS.

## Estado actual (base ya lograda)
- APIs operativas protegidas con sesión.
- Aislamiento multi-tenant base por `configuracion_id`.
- Vistas críticas protegidas con login.

## Mejoras propuestas para vender al cliente

### 1) Protección CSRF en acciones sensibles
Aplicar tokens CSRF en operaciones de escritura:
- Alta/edición/baja de productos, categorías, mesas, inventario.
- Acciones de órdenes y caja.
- Cambios de suscripción y administración.

**Beneficio comercial:** evita ataques de ejecución involuntaria desde sitios externos.

### 2) CORS restringido por dominio
Eliminar `Access-Control-Allow-Origin: *` y permitir solo dominios autorizados:
- Producción
- Staging (opcional)
- Desarrollo local (opcional)

**Beneficio comercial:** reduce exposición de APIs a orígenes no confiables.

### 3) Hardening de sesión y cookies
- `session.cookie_httponly=1`
- `session.cookie_secure=1` (HTTPS)
- `session.cookie_samesite=Lax/Strict`
- Regeneración de ID de sesión al autenticar.
- Timeout de inactividad + cierre seguro de sesión.

**Beneficio comercial:** mitiga secuestro de sesión y reduce fraude por sesión robada.

### 4) Rate limiting y anti-abuso
- Límite de intentos de login por IP/cuenta.
- Límite básico por endpoint crítico (`/api/*` de escritura).
- Backoff temporal (bloqueo corto progresivo).

**Beneficio comercial:** frena fuerza bruta y abuso automatizado.

### 5) Trazabilidad y auditoría mínima
- Log estructurado de acciones sensibles:
  - usuario, tenant, endpoint, acción, resultado, IP, timestamp.
- Log de eventos de seguridad (bloqueos, intentos inválidos).

**Beneficio comercial:** soporte, diagnóstico y evidencia operativa.

### 6) Revisión de endpoints administrativos
- Cambiar acciones por GET sensibles a POST + CSRF.
- Revisión de permisos por rol en cada endpoint administrativo.

**Beneficio comercial:** reduce riesgo de acciones no autorizadas.

## Entregables
1. Código hardening implementado.
2. Matriz de endpoints protegidos (antes/después).
3. Checklist de configuración de servidor para producción.
4. Guía corta de operación y monitoreo.
5. Evidencia de pruebas funcionales y de seguridad básica.

## Paquetes sugeridos para cotizar

### Paquete A - Esencial
- CSRF en endpoints críticos.
- CORS restringido.
- Hardening de sesión/cookies.

**Ideal para:** salir a producción con seguridad básica sólida.

### Paquete B - Recomendado
- Todo Paquete A.
- Rate limiting en login y endpoints críticos.
- Auditoría mínima estructurada.

**Ideal para:** operación SaaS estable con menor riesgo de abuso.

### Paquete C - Completo
- Todo Paquete B.
- Revisión extendida de permisos/roles.
- Endurecimiento adicional de panel admin.
- Documento final de cumplimiento interno + runbook.

**Ideal para:** clientes que piden mayor control y trazabilidad.

## Estimación de esfuerzo (referencial)
- Paquete A: 16 a 24 horas.
- Paquete B: 28 a 40 horas.
- Paquete C: 40 a 60 horas.

> Ajustar según alcance final, número de endpoints y nivel de pruebas requerido.

## Criterios de aceptación
- Ningún endpoint de escritura sin validación CSRF (cuando aplique).
- CORS abierto eliminado.
- Cookies de sesión con flags seguros en producción.
- Bloqueo de abuso visible en pruebas.
- Logs de seguridad consultables.

## Nota para propuesta al cliente
Este hardening **no cambia funcionalidad de negocio**, pero **reduce riesgo operativo y legal**.  
Se recomienda venderlo como etapa previa al crecimiento comercial o a la incorporación de nuevos restaurantes.

