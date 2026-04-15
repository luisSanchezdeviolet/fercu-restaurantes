# Propuesta Comercial de Servicios

## 1. Datos generales

- **Proveedor:** Fercu / Equipo de desarrollo
- **Cliente:** [Nombre del cliente]
- **Proyecto:** Fercu Restaurante SaaS
- **Versión de propuesta:** v1.0
- **Fecha de emisión:** 15 de abril de 2026
- **Vigencia de propuesta:** 15 días naturales (hasta el 30 de abril de 2026)
- **Moneda:** MXN

---

## 2. Resumen ejecutivo

Esta propuesta formaliza servicios técnicos para fortalecer y evolucionar el sistema SaaS.  
El objetivo es contratar por bloques cerrados (fases o adicionales), con entregables claros y costo por cada alcance.

---

## 3. Alcance inicial cotizado

### Servicio base: Hardening Fase 1.1 (Seguridad)

Incluye:
1. Implementación de protección CSRF en endpoints y acciones críticas.
2. Restricción de CORS por dominios autorizados.
3. Endurecimiento de sesión/cookies (secure, httponly, samesite, regeneración de sesión).
4. Rate limiting básico para login y endpoints sensibles.
5. Auditoría mínima de eventos críticos.
6. Ajustes de endpoints administrativos para reducir riesgo de acciones no autorizadas.

No incluye:
1. Pentest externo certificado.
2. Infraestructura cloud administrada.
3. Integraciones de terceros no contempladas.
4. Cambios funcionales de negocio fuera del alcance de seguridad.

---

## 4. Entregables

1. Código implementado en repositorio.
2. Documento de cambios y endpoints protegidos (antes/después).
3. Evidencia de pruebas funcionales y validaciones de seguridad básica.
4. Checklist de configuración para producción.
5. Guía corta operativa para soporte inicial.

---

## 5. Esquema económico

### 5.1 Costo del servicio base

- **Hardening Fase 1.1 (paquete recomendado):** **$[Monto] MXN + IVA**

### 5.2 Forma de pago sugerida

1. **50% anticipo** al inicio.
2. **50% contra entrega** de alcance y evidencias.

### 5.3 Condiciones

1. Todo trabajo adicional fuera de este alcance se cotiza por separado.
2. El cliente debe proveer accesos, dominios y ambientes requeridos.
3. Si hay pausa por falta de información del cliente por más de 5 días hábiles, el calendario se recorre.

---

## 6. Cronograma estimado

- **Inicio estimado:** [Fecha]
- **Duración estimada:** [X] días hábiles
- **Entrega estimada:** [Fecha]

> El calendario final se confirma al recibir anticipo y accesos.

---

## 7. Control de cambios (adicionales cobrables)

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

## 8. Bolsa de horas (opcional para extras)

Si el cliente prefiere flexibilidad:

- **Bolsa mínima:** 10 horas
- **Tarifa por hora:** $[Monto]/hora + IVA
- **Caducidad de bolsa:** [30/60/90] días
- **Consumo mínimo por intervención:** 1 hora

---

## 9. Criterios de aceptación

Se considera entregado cuando:
1. Se completa el alcance definido en esta propuesta.
2. Se entregan evidencias y documentación acordadas.
3. El cliente valida funcionalmente en ambiente acordado.

---

## 10. Soporte post-entrega

- **Garantía técnica de ajustes por defecto de implementación:** [7/15/30] días naturales.
- No cubre nuevas funcionalidades ni cambios de alcance.

---

## 11. Aprobación

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

