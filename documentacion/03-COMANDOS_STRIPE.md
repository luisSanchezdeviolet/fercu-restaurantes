# 🚀 Comandos Rápidos de Stripe

## 📍 Estás Actualmente en: MODO TEST ✅

---

## 🔄 Cambiar entre Modos

### Cambiar a modo TEST (pruebas)
```bash
cd /var/www/restaurantes
php stripe-switch-mode.php test
```

### Cambiar a modo LIVE (producción)
```bash
cd /var/www/restaurantes
php stripe-switch-mode.php live
```

---

## 🔄 Sincronizar Planes Manualmente

Si solo quieres sincronizar los planes sin cambiar de modo:

```bash
cd /var/www/restaurantes
php stripe-sync-plans.php
```

---

## 🧹 Limpiar IDs de Stripe y Resincronizar

Si necesitas limpiar los IDs y volver a crear los productos:

```bash
cd /var/www/restaurantes
mysql -h 127.0.0.1 -P 3308 -u restaurantpos -prestaurantpos restaurante_pos -e "UPDATE plans SET stripe_product_id = NULL, stripe_price_id = NULL WHERE type != 'trial';"
php stripe-sync-plans.php
```

## 🗑️ Limpiar Productos Duplicados en Stripe

Para archivar productos antiguos/duplicados y dejar solo los 4 activos:

```bash
cd /var/www/restaurantes
echo "s" | php stripe-clean-products.php
```

## 🔍 Verificar Conexión y Productos

Para ver el estado actual de tu conexión con Stripe:

```bash
cd /var/www/restaurantes
php stripe-test-connection.php
```

---

## 📊 Ver Estado Actual

### Ver en qué modo estás
```bash
cd /var/www/restaurantes
grep "STRIPE_MODE" config/stripe.php | head -1
```

### Ver planes sincronizados
```bash
cd /var/www/restaurantes
mysql -h 127.0.0.1 -P 3308 -u restaurantpos -prestaurantpos restaurante_pos -e "SELECT id, name, type, amount, stripe_product_id, stripe_price_id FROM plans WHERE type != 'trial';"
```

### Ver suscripciones activas
```bash
cd /var/www/restaurantes
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
ORDER BY c.created_at DESC
LIMIT 10;
"
```

---

## 📝 Ver Logs

### Logs de Webhook (tiempo real)
```bash
tail -f /var/www/restaurantes/logs/stripe-webhook.log
```

### Últimos 50 eventos de webhook
```bash
tail -50 /var/www/restaurantes/logs/stripe-webhook.log
```

### Logs de errores de PHP
```bash
# Apache
tail -50 /var/log/apache2/error.log

# Nginx
tail -50 /var/log/nginx/error.log
```

---

## 🧪 URLs de Prueba

### Landing Page
```
http://restaurante.fercupuntodeventa.com/
```

### Login Directo
```
http://restaurante.fercupuntodeventa.com/presentation/login.php
```

### Panel SAAS (Super Admin)
```
http://restaurante.fercupuntodeventa.com/saas-admin.php
```

---

## 🎯 Stripe Dashboards

### Modo TEST
- Dashboard: https://dashboard.stripe.com/test/dashboard
- Productos: https://dashboard.stripe.com/test/products
- Clientes: https://dashboard.stripe.com/test/customers
- Suscripciones: https://dashboard.stripe.com/test/subscriptions
- Pagos: https://dashboard.stripe.com/test/payments
- Webhooks: https://dashboard.stripe.com/test/webhooks

### Modo LIVE (Producción)
- Dashboard: https://dashboard.stripe.com/dashboard
- Productos: https://dashboard.stripe.com/products
- Clientes: https://dashboard.stripe.com/customers
- Suscripciones: https://dashboard.stripe.com/subscriptions
- Pagos: https://dashboard.stripe.com/payments
- Webhooks: https://dashboard.stripe.com/webhooks

---

## 💳 Tarjetas de Prueba (solo modo TEST)

### ✅ Pago exitoso
```
Número: 4242 4242 4242 4242
Fecha: 12/34
CVC: 123
ZIP: 12345
```

### ❌ Pago rechazado
```
Número: 4000 0000 0000 0002
Fecha: 12/34
CVC: 123
ZIP: 12345
```

### 🔐 Con 3D Secure
```
Número: 4000 0027 6000 3184
Fecha: 12/34
CVC: 123
ZIP: 12345
```

**Más tarjetas:** https://stripe.com/docs/testing#cards

---

## 🛠️ Limpiar Caché

Después de cualquier cambio en configuración:

```bash
php -r "if(function_exists('opcache_reset')) opcache_reset();"
```

---

## 📚 Documentación

- **Guía de Pruebas Completa:** `GUIA_PRUEBAS_STRIPE.md`
- **Configuración Inicial:** `STRIPE_SETUP.md`
- **Documentación de Stripe:** https://stripe.com/docs/api

---

## ⚠️ Antes de Pasar a Producción

1. ✅ Completar TODAS las pruebas en modo TEST
2. ✅ Verificar que los webhooks funcionen correctamente
3. ✅ Revisar que no haya errores en los logs
4. ✅ Configurar webhook en dashboard de PRODUCCIÓN
5. ✅ Ejecutar `php stripe-switch-mode.php live`
6. ✅ Verificar que los productos aparezcan en producción
7. ✅ Hacer una prueba con tarjeta real (un pago pequeño)

---

## 📧 SendGrid (Emails)

### Probar envío de emails

```bash
cd /var/www/restaurantes
php sendgrid-test.php
```

### Ver log de emails

```bash
# Tiempo real
tail -f /var/www/restaurantes/logs/emails.log

# Últimas 50 líneas
tail -50 /var/www/restaurantes/logs/emails.log
```

### Buscar emails específicos

```bash
grep "usuario@ejemplo.com" /var/www/restaurantes/logs/emails.log
```

---

**📚 Documentación Completa:**
- **Stripe:** `STRIPE_SETUP.md` y `GUIA_PRUEBAS_STRIPE.md`
- **SendGrid:** `SENDGRID_SETUP.md`
- **Comandos:** Este archivo

---

**🎉 ¡Tu sistema de suscripciones está listo para probar!**

