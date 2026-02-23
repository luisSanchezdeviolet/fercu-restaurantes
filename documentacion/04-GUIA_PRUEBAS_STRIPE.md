# 🧪 Guía de Pruebas - Sistema de Suscripciones Stripe

## 📋 Estado Actual del Sistema

✅ **Configurado en MODO TEST**
- Stripe Mode: `test`
- Los pagos NO serán reales
- Puedes usar tarjetas de prueba

---

## 🎯 Cómo Hacer Pruebas

### 1. Ver los Productos en Stripe Dashboard (TEST)

Accede a tu dashboard de **TEST**:
```
https://dashboard.stripe.com/test/products
```

Deberías ver **4 productos** del sistema de restaurantes:
- Plan Básico (Mensual - $399 MXN)
- Plan Básico Anual (Anual - $3,990 MXN)
- Plan Professional (Mensual - $899 MXN)
- Plan Professional Anual (Anual - $8,630 MXN)

---

### 2. Configurar Webhook en Modo TEST

**IMPORTANTE:** Necesitas configurar el webhook para que las suscripciones se renueven automáticamente.

#### Opción A: Webhook Remoto (Recomendado)

1. Ve a: https://dashboard.stripe.com/test/webhooks
2. Click en "Add endpoint"
3. URL del webhook:
   ```
   http://restaurante.fercupuntodeventa.com/stripe-webhook.php
   ```
4. Selecciona estos eventos:
   - `invoice.paid`
   - `invoice.payment_failed`
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
5. Click en "Add endpoint"
6. Copia el **Signing secret** (empieza con `whsec_`)
7. Pégalo en `/var/www/restaurantes/config/stripe.php` en la línea 17:
   ```php
   define('STRIPE_TEST_WEBHOOK_SECRET', 'whsec_TU_NUEVO_SECRET_AQUI');
   ```

#### Opción B: Stripe CLI (Para desarrollo local)

Si tienes problemas con el webhook remoto:
```bash
stripe listen --forward-to http://restaurante.fercupuntodeventa.com/stripe-webhook.php
```

---

### 3. Probar el Flujo Completo de Registro

#### A. Acceder a la Landing Page
```
http://restaurante.fercupuntodeventa.com/
```

#### B. Registrarse con Prueba Gratuita (15 días)
1. Click en "Prueba Gratis 15 días"
2. Completa el formulario:
   - Nombre del Restaurante: `Restaurante Test`
   - Email: `prueba@test.com`
   - Contraseña: `Test123456`
   - Teléfono: `5512345678`
3. Click en "Comenzar Prueba Gratuita"
4. Deberías ser redirigido al dashboard
5. Verifica que aparezca: "Plan: Prueba Gratuita" con fecha de expiración

#### C. Probar Registro con Plan de Pago
1. **IMPORTANTE:** Usa un email diferente (recuerda que no se puede repetir el trial)
2. En la landing, selecciona "Plan Básico" o "Plan Professional"
3. Completa el formulario de registro
4. En la página de checkout, usa una **tarjeta de prueba de Stripe**:

**Tarjetas de Prueba:**

✅ **Pago exitoso:**
```
Número: 4242 4242 4242 4242
Fecha: 12/34 (cualquier fecha futura)
CVC: 123 (cualquier 3 dígitos)
ZIP: 12345 (cualquier código postal)
```

❌ **Pago rechazado:**
```
Número: 4000 0000 0000 0002
```

🔄 **Requiere autenticación 3D Secure:**
```
Número: 4000 0027 6000 3184
```

💳 **Más tarjetas de prueba:**
https://stripe.com/docs/testing#cards

5. Click en "Pagar Ahora"
6. Verifica que el pago se procese correctamente
7. Deberías ser redirigido al dashboard

---

### 4. Verificar en Stripe Dashboard

#### A. Ver Clientes Creados
```
https://dashboard.stripe.com/test/customers
```
- Deberías ver los clientes registrados con sus emails

#### B. Ver Suscripciones Activas
```
https://dashboard.stripe.com/test/subscriptions
```
- Verifica que las suscripciones estén en estado "Active"
- Revisa la fecha de renovación

#### C. Ver Pagos Recibidos
```
https://dashboard.stripe.com/test/payments
```
- Verifica que aparezcan los pagos procesados

#### D. Verificar Webhooks
```
https://dashboard.stripe.com/test/webhooks
```
- Click en tu webhook
- Verifica que los eventos se estén enviando correctamente
- Si hay errores, aparecerán en rojo

---

### 5. Probar Escenarios Específicos

#### ✅ Prueba 1: Registro con Trial
```
Email: trial1@test.com
Resultado esperado: Acceso inmediato, expira en 15 días
```

#### ✅ Prueba 2: Registro con Plan de Pago
```
Email: pago1@test.com
Plan: Básico Mensual
Tarjeta: 4242 4242 4242 4242
Resultado esperado: Suscripción activa, se renueva en 30 días
```

#### ❌ Prueba 3: Intentar Usar Trial Dos Veces
```
Paso 1: Registrar trial1@test.com
Paso 2: Intentar registrar de nuevo con trial1@test.com
Resultado esperado: Error - "Este correo ya utilizó la prueba gratuita"
```

#### ❌ Prueba 4: Tarjeta Rechazada
```
Email: rechazo@test.com
Tarjeta: 4000 0000 0000 0002
Resultado esperado: Error de pago, suscripción no creada
```

#### 🔄 Prueba 5: 3D Secure
```
Email: 3dsecure@test.com
Tarjeta: 4000 0027 6000 3184
Resultado esperado: Ventana emergente para autenticación, luego pago exitoso
```

---

### 6. Verificar en el Sistema

#### A. Panel SAAS Admin
1. Login con tu usuario super admin: `admin@fercupuntodeventa.com`
2. Ve a "Panel SAAS"
3. Verifica que aparezcan todas las empresas registradas
4. Revisa los estados de suscripción
5. Prueba activar/desactivar empresas

#### B. Base de Datos
```bash
mysql -h 127.0.0.1 -P 3308 -u restaurantpos -prestaurantpos restaurante_pos -e "
SELECT 
    c.nombre as empresa,
    c.correo,
    p.name as plan,
    s.start_date,
    s.limit_date,
    s.status,
    s.stripe_subscription_id
FROM configuracion c
LEFT JOIN subscriptions s ON c.id = s.configuracion_id AND s.status = 1
LEFT JOIN plans p ON s.plan_id = p.id
ORDER BY c.id DESC
LIMIT 10;
"
```

---

### 7. Monitorear Logs

#### Logs de Webhook
```bash
tail -f /var/www/restaurantes/logs/stripe-webhook.log
```

Deberías ver eventos como:
- `invoice.paid`
- `customer.subscription.created`
- `customer.subscription.updated`

---

## 🚀 Cuando las Pruebas Sean Exitosas

### Migrar a Producción:

1. **Actualizar el modo en `config/stripe.php`:**
   ```php
   define('STRIPE_MODE', 'live');
   ```

2. **Limpiar IDs de test y sincronizar en producción:**
   ```bash
   cd /var/www/restaurantes
   mysql -h 127.0.0.1 -P 3308 -u restaurantpos -prestaurantpos restaurante_pos -e "UPDATE plans SET stripe_product_id = NULL, stripe_price_id = NULL WHERE type != 'trial';"
   php stripe-sync-plans.php
   ```

3. **Configurar webhook en producción:**
   - Ve a: https://dashboard.stripe.com/webhooks
   - Crea un nuevo endpoint con la misma URL
   - Actualiza el `STRIPE_LIVE_WEBHOOK_SECRET` en `config/stripe.php`

4. **Limpiar caché:**
   ```bash
   php -r "if(function_exists('opcache_reset')) opcache_reset();"
   ```

5. **Verificar que todo funcione en producción con pagos reales**

---

## 📞 Soporte

Si encuentras algún error durante las pruebas:

1. **Revisa los logs de webhook:**
   ```bash
   tail -50 /var/www/restaurantes/logs/stripe-webhook.log
   ```

2. **Revisa logs de PHP:**
   ```bash
   tail -50 /var/log/apache2/error.log
   # o
   tail -50 /var/log/nginx/error.log
   ```

3. **Revisa el dashboard de Stripe:**
   - Los webhooks fallidos aparecen en rojo
   - Click en el evento para ver el detalle del error

---

## ✅ Checklist de Pruebas

Marca cada prueba que completes:

- [ ] Ver productos en Stripe Dashboard TEST
- [ ] Configurar webhook en modo TEST
- [ ] Registrar usuario con prueba gratuita
- [ ] Registrar usuario con plan Básico Mensual
- [ ] Registrar usuario con plan Professional Anual
- [ ] Probar tarjeta de prueba exitosa (4242...)
- [ ] Probar tarjeta rechazada (4000 0000 0000 0002)
- [ ] Verificar que no se pueda repetir trial con mismo email
- [ ] Ver clientes en Stripe Dashboard
- [ ] Ver suscripciones activas en Stripe
- [ ] Verificar webhooks recibidos correctamente
- [ ] Acceder al Panel SAAS y ver todas las empresas
- [ ] Activar/desactivar empresa desde Panel SAAS
- [ ] Verificar datos en la base de datos

**Cuando todas las pruebas pasen ✅, estarás listo para migrar a producción!**

