# 🎯 GUÍA COMPLETA DE CONFIGURACIÓN DE STRIPE

## Fercu Restaurante - Sistema de Pagos

---

## 📋 **ÍNDICE**

1. [Crear cuenta en Stripe](#1-crear-cuenta-en-stripe)
2. [Obtener las claves API](#2-obtener-las-claves-api)
3. [Configurar las claves en el sistema](#3-configurar-las-claves-en-el-sistema)
4. [Configurar Webhook](#4-configurar-webhook)
5. [Probar pagos en modo test](#5-probar-pagos-en-modo-test)
6. [Activar modo producción](#6-activar-modo-producci%C3%B3n)
7. [Crear productos y precios en Stripe](#7-crear-productos-y-precios-en-stripe)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. **CREAR CUENTA EN STRIPE**

### Paso 1.1: Registro
1. Ve a: https://dashboard.stripe.com/register
2. Completa el formulario con:
   - Email
   - Nombre completo
   - País: **México**
   - Contraseña
3. Verifica tu email

### Paso 1.2: Completar perfil de negocio
1. Nombre del negocio: **Fercu Restaurante**
2. Tipo de negocio: **SaaS / Software**
3. Producto/Servicio: **Sistema de gestión para restaurantes**
4. URL del sitio: **https://restaurante.fercupuntodeventa.com**

---

## 2. **OBTENER LAS CLAVES API**

### Paso 2.1: Ir a API Keys
1. En el Dashboard de Stripe, haz clic en **"Developers"** (arriba a la derecha)
2. Click en **"API keys"**

### Paso 2.2: Copiar las claves de TEST
Verás 2 claves en modo **test**:

- **Publishable key** (empieza con `pk_test_...`)
  - Esta clave es pública y se usa en el frontend
  
- **Secret key** (empieza con `sk_test_...`)
  - Esta clave es secreta y se usa en el backend
  - ⚠️ **NUNCA la compartas ni la expongas en el frontend**

### Paso 2.3: Copiar las claves de LIVE (para producción)
1. Activa el toggle de **"Test mode"** para desactivarlo
2. Copia las claves de producción:
   - **Publishable key** (empieza con `pk_live_...`)
   - **Secret key** (empieza con `sk_live_...`)

---

## 3. **CONFIGURAR LAS CLAVES EN EL SISTEMA**

**Nota:** Las claves se configuran en el archivo `.env` (copia `.env.example` a `.env`).

### Archivo: `/var/www/restaurantes/.env`

Agrega o edita las variables:

```
STRIPE_MODE=test
STRIPE_TEST_PUBLIC_KEY=pk_test_TU_CLAVE_AQUI
STRIPE_TEST_SECRET_KEY=sk_test_TU_CLAVE_AQUI
STRIPE_TEST_WEBHOOK_SECRET=whsec_TU_SECRET_TEST

STRIPE_LIVE_PUBLIC_KEY=pk_live_TU_CLAVE_AQUI
STRIPE_LIVE_SECRET_KEY=sk_live_TU_CLAVE_AQUI
STRIPE_LIVE_WEBHOOK_SECRET=whsec_TU_SECRET_LIVE
```

### Cambiar entre TEST y LIVE

Por defecto está en **TEST**:
```
STRIPE_MODE=test
```

Para producción:
```
STRIPE_MODE=live
```

---

## 4. **CONFIGURAR WEBHOOK**

Los webhooks permiten que Stripe notifique a tu servidor cuando ocurren eventos (pagos exitosos, fallos, etc.).

### Paso 4.1: Crear endpoint de webhook

1. En Stripe Dashboard → **Developers** → **Webhooks**
2. Click en **"Add endpoint"**
3. URL del endpoint:
   ```
   https://restaurante.fercupuntodeventa.com/stripe-webhook.php
   ```
4. Selecciona los eventos a escuchar:
   - ✅ `payment_intent.succeeded`
   - ✅ `payment_intent.payment_failed`
   - ✅ `charge.succeeded`
   - ✅ `invoice.paid`
   - ✅ `invoice.payment_failed`
   - ✅ `customer.subscription.created`
   - ✅ `customer.subscription.updated`
   - ✅ `customer.subscription.deleted`

5. Click en **"Add endpoint"**

### Paso 4.2: Copiar el Webhook Secret

Después de crear el webhook, verás un **Signing secret** (empieza con `whsec_...`).

Cópialo y agrégalo a tu archivo `.env`:

```
STRIPE_TEST_WEBHOOK_SECRET=whsec_TU_SECRET_TEST
STRIPE_LIVE_WEBHOOK_SECRET=whsec_TU_SECRET_LIVE
```

### Paso 4.3: Probar el webhook

1. En la página del webhook en Stripe, click en **"Send test webhook"**
2. Selecciona el evento `payment_intent.succeeded`
3. Click en **"Send test event"**
4. Revisa los logs en: `/var/www/restaurantes/logs/stripe-webhook.log`

---

## 5. **PROBAR PAGOS EN MODO TEST**

### Tarjetas de prueba de Stripe

Stripe proporciona tarjetas de prueba para diferentes escenarios:

#### ✅ **Pago exitoso:**
- Número: `4242 4242 4242 4242`
- Fecha: Cualquier fecha futura (ej: `12/25`)
- CVC: Cualquier 3 dígitos (ej: `123`)
- ZIP: Cualquier código postal

#### ❌ **Pago rechazado (fondos insuficientes):**
- Número: `4000 0000 0000 9995`

#### ⚠️ **Requiere autenticación 3D Secure:**
- Número: `4000 0025 0000 3155`

#### 💳 **Otras tarjetas de prueba:**
- Visa: `4242 4242 4242 4242`
- Mastercard: `5555 5555 5555 4444`
- American Express: `3782 822463 10005`

### Flujo de prueba completo

1. Ve a: `http://restaurante.fercupuntodeventa.com/`
2. Inicia sesión con una cuenta existente
3. Ve a: `http://restaurante.fercupuntodeventa.com/checkout.php?plan_id=2`
4. Ingresa los datos de la tarjeta de prueba
5. Click en **"Pagar Ahora"**
6. Deberías ver el mensaje de éxito
7. Revisa en Stripe Dashboard → **Payments** para ver la transacción

---

## 6. **ACTIVAR MODO PRODUCCIÓN**

⚠️ **NO actives producción hasta haber probado completamente en TEST**

### Requisitos antes de activar LIVE:

1. ✅ Todos los pagos de prueba funcionan correctamente
2. ✅ El webhook está recibiendo eventos correctamente
3. ✅ Los logs no muestran errores
4. ✅ Las suscripciones se crean en la base de datos
5. ✅ Los emails se envían correctamente (cuando esté implementado)

### Pasos para activar:

1. En Stripe Dashboard, completa la **activación de tu cuenta**:
   - Información del negocio
   - Información bancaria (para recibir pagos)
   - Verificación de identidad

2. Obtén las claves de LIVE (paso 2.3)

3. Configura el webhook de LIVE (paso 4.1) con la misma URL

4. En tu archivo `.env`:
   ```
   STRIPE_MODE=live
   ```

5. Limpia la caché:
   ```bash
   cd /var/www/restaurantes
   php -r "if(function_exists('opcache_reset')) opcache_reset();"
   ```

---

## 7. **CREAR PRODUCTOS Y PRECIOS EN STRIPE** (Opcional - Para suscripciones recurrentes)

Si quieres usar Subscriptions de Stripe en lugar de PaymentIntents:

### Paso 7.1: Crear productos

1. En Stripe Dashboard → **Product catalog** → **Products**
2. Click en **"Add product"**

#### Producto 1: Plan Básico
- **Name:** Plan Básico - Fercu Restaurante
- **Description:** Hasta 10 mesas, 3 usuarios
- **Pricing:**
  - **Mensual:** $399.00 MXN / mes
  - **Anual:** $3,990.00 MXN / año
- **Billing period:** Monthly / Yearly
- **Currency:** MXN

#### Producto 2: Plan Professional
- **Name:** Plan Professional - Fercu Restaurante
- **Description:** Mesas y usuarios ilimitados
- **Pricing:**
  - **Mensual:** $899.00 MXN / mes
  - **Anual:** $8,630.00 MXN / año

### Paso 7.2: Obtener los Price IDs

Después de crear los productos, copia los **Price IDs** (empiezan con `price_...`) y guárdalos en la tabla `plans` de tu base de datos:

```sql
UPDATE plans SET stripe_price_id = 'price_XXXXX' WHERE id = 2; -- Básico Mensual
UPDATE plans SET stripe_price_id = 'price_XXXXX' WHERE id = 3; -- Básico Anual
UPDATE plans SET stripe_price_id = 'price_XXXXX' WHERE id = 4; -- Professional Mensual
UPDATE plans SET stripe_price_id = 'price_XXXXX' WHERE id = 5; -- Professional Anual
```

---

## 8. **TROUBLESHOOTING**

### Problema 1: "No se puede crear el PaymentIntent"

**Causa:** Las claves API están incorrectas o no están configuradas.

**Solución:**
1. Verifica que las claves en `config/stripe.php` sean correctas
2. Asegúrate de estar usando las claves del modo correcto (test o live)
3. Limpia la caché de PHP

### Problema 2: "Webhook signature verification failed"

**Causa:** El Webhook Secret no coincide.

**Solución:**
1. Verifica que el `STRIPE_WEBHOOK_SECRET` en tu `.env` sea correcto
2. Asegúrate de estar usando el secret del endpoint correcto
3. Revisa los logs en `/var/www/restaurantes/logs/stripe-webhook.log`

### Problema 3: "Payment declined"

**Causa:** La tarjeta fue rechazada (en producción) o estás usando una tarjeta de prueba incorrecta (en test).

**Solución:**
- **En TEST:** Usa una de las tarjetas de prueba del paso 5
- **En LIVE:** El cliente debe verificar los fondos de su tarjeta

### Problema 4: "La suscripción no se crea en la BD"

**Causa:** El webhook no está ejecutándose correctamente.

**Solución:**
1. Revisa los logs: `/var/www/restaurantes/logs/stripe-webhook.log`
2. Verifica que el webhook esté configurado correctamente en Stripe
3. Prueba el webhook manualmente desde Stripe Dashboard

### Problema 5: "Card element no se muestra"

**Causa:** Stripe.js no está cargando o hay un error de JavaScript.

**Solución:**
1. Abre la consola del navegador (F12)
2. Verifica que no haya errores de JavaScript
3. Asegúrate de que `STRIPE_TEST_PUBLIC_KEY` o `STRIPE_LIVE_PUBLIC_KEY` esté en tu `.env`

---

## 📊 **RESUMEN DE ARCHIVOS CREADOS**

| Archivo | Descripción |
|---------|-------------|
| `config/stripe.php` | Configuración de claves y funciones helper |
| `stripe-create-payment-intent.php` | Crea el PaymentIntent en Stripe |
| `stripe-webhook.php` | Recibe notificaciones de Stripe |
| `checkout.php` | Formulario de pago con Stripe Elements |
| `payment-success.php` | Página de confirmación de pago exitoso |
| `payment-cancel.php` | Página cuando se cancela el pago |

---

## 🎉 **¡LISTO!**

Ya tienes Stripe completamente integrado. Los pasos siguientes son:

1. ✅ Probar pagos en modo TEST
2. ✅ Configurar SendGrid para emails
3. ✅ Activar modo LIVE cuando todo esté listo
4. ✅ Monitorear los pagos en Stripe Dashboard

**¿Necesitas ayuda?** Contacta a soporte: contacto@fercupuntodeventa.com

