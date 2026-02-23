# 📧 Guía de SendGrid - Fercu Restaurante

## 📋 Estado Actual

✅ **SendGrid integrado y configurado**
- API Key: Configurada
- Biblioteca instalada: `sendgrid/sendgrid` v8.1
- Remitente: `notificaciones@fercupuntodeventa.com`
- Nombre: Fercu Restaurante

---

## 🎯 Emails Implementados

### 1️⃣ Email de Bienvenida con Prueba Gratuita

**Cuándo se envía:**
- Cuando un usuario se registra y selecciona la prueba gratuita de 15 días

**Contenido:**
- 🎉 Bienvenida personalizada
- 📋 Información de la cuenta (empresa, email, fecha de expiración)
- 🔐 Credenciales de acceso
- ✨ Lista de características disponibles durante el trial
- 💡 Próximos pasos para empezar
- 🔗 Botón para acceder al sistema

**Archivo:** `config/email-templates.php` → `getWelcomeTrialEmailHTML()`

---

### 2️⃣ Email de Bienvenida con Suscripción de Pago

**Cuándo se envía:**
- Cuando un usuario se registra y paga por un plan (Básico o Professional)

**Contenido:**
- 🎉 Bienvenida personalizada
- 📦 Detalles del plan contratado (nombre, precio, tipo)
- 📅 Fecha de próxima renovación
- 📋 Información de la cuenta
- 🔐 Credenciales de acceso
- ✨ Lista de beneficios del plan
- 💳 Información sobre renovación automática
- 🔗 Botón para acceder al sistema

**Archivo:** `config/email-templates.php` → `getWelcomeSubscriptionEmailHTML()`

---

### 3️⃣ Email de Confirmación de Pago (Renovación)

**Cuándo se envía:**
- Cuando se procesa un pago exitoso de renovación de suscripción
- Se envía automáticamente desde el webhook de Stripe (`invoice.paid`)

**Contenido:**
- ✅ Confirmación de pago recibido
- 💚 Monto pagado
- 📋 Detalles del plan
- 📅 Fecha del próximo pago
- 🔗 Enlace al panel de control
- 📄 Nota sobre descarga de factura

**Archivo:** `config/email-templates.php` → `getPaymentConfirmationEmailHTML()`

---

## 🛠️ Archivos de Configuración

### 1. `/var/www/restaurantes/config/sendgrid.php`

Configuración principal de SendGrid:

Las variables se configuran en el archivo `.env` (no subir a Git). Ver `.env.example` para la plantilla:

```bash
# Copia .env.example a .env y completa con tus valores reales
cp .env.example .env
```

Variables de SendGrid en `.env`:
```
SENDGRID_API_KEY=tu_api_key_aqui
SENDGRID_FROM_EMAIL=notificaciones@fercupuntodeventa.com
SENDGRID_FROM_NAME=Fercu Restaurante
SENDGRID_SUPPORT_EMAIL=soporte@fercupuntodeventa.com
APP_URL=http://restaurante.fercupuntodeventa.com
```

**Funciones helpers:**
- `sendEmail()` - Envía un email usando SendGrid
- `logEmail()` - Registra el envío en el log

---

### 2. `/var/www/restaurantes/config/email-templates.php`

Plantillas HTML de los emails:

- `getWelcomeTrialEmailHTML()` - Email de prueba gratuita
- `getWelcomeSubscriptionEmailHTML()` - Email de suscripción de pago
- `getPaymentConfirmationEmailHTML()` - Email de confirmación de pago

---

## 🧪 Probar el Envío de Emails

### Método 1: Script de Prueba Interactivo

```bash
cd /var/www/restaurantes
php sendgrid-test.php
```

El script te pedirá:
1. Email de destino
2. Tipo de email a probar (Trial, Suscripción o Confirmación de pago)

### Método 2: Registro Real

Registra un nuevo usuario desde la landing page:

```
http://restaurante.fercupuntodeventa.com/
```

1. Completa el formulario de registro
2. Selecciona "Prueba Gratis" o un plan de pago
3. Verifica tu email (revisa SPAM si no lo ves)

---

## 📊 Monitorear Emails

### Ver el Log de Emails

```bash
# Ver en tiempo real
tail -f /var/www/restaurantes/logs/emails.log

# Ver últimas 50 líneas
tail -50 /var/www/restaurantes/logs/emails.log

# Buscar emails específicos
grep "prueba@ejemplo.com" /var/www/restaurantes/logs/emails.log
```

### Formato del Log

```
[2024-11-05 10:30:45] SUCCESS | To: usuario@ejemplo.com | Subject: 🎉 ¡Bienvenido... | MessageID: <xxx> | Message: Email enviado correctamente
[2024-11-05 10:31:12] FAILED | To: otro@ejemplo.com | Subject: ✅ Pago Recibido... | MessageID: N/A | Message: Error: Invalid API Key
```

---

## 🎯 Dashboard de SendGrid

### Ver Estadísticas de Envío

1. Ve a: https://app.sendgrid.com/
2. Login con tu cuenta
3. Dashboard → Activity
4. Podrás ver:
   - Emails enviados
   - Emails entregados
   - Emails abiertos
   - Clicks en enlaces
   - Bounces y spam reports

### Ver Detalles de un Email Específico

1. Dashboard → Activity → Search
2. Busca por email, fecha o Message ID
3. Ver detalles completos del envío

---

## 🔧 Configuración de SendGrid

### 1. Verificar Sender (Remitente)

**IMPORTANTE:** Para que los emails se envíen correctamente, debes verificar el dominio o email remitente.

#### Opción A: Single Sender Verification (Rápida)

1. Ve a: https://app.sendgrid.com/settings/sender_auth/senders
2. Click en "Create New Sender"
3. Completa el formulario:
   - From Name: `Fercu Restaurante`
   - From Email: `notificaciones@fercupuntodeventa.com`
   - Reply To: `soporte@fercupuntodeventa.com`
   - Company Address, City, State, Zip, Country
4. Click en "Create"
5. Recibirás un email en `notificaciones@fercupuntodeventa.com`
6. Click en el enlace de verificación

#### Opción B: Domain Authentication (Recomendada para Producción)

1. Ve a: https://app.sendgrid.com/settings/sender_auth
2. Click en "Authenticate Your Domain"
3. Selecciona tu proveedor de DNS (ej: Hostinger, Cloudflare, etc.)
4. Ingresa tu dominio: `fercupuntodeventa.com`
5. SendGrid te dará 3 registros DNS (CNAME) para agregar
6. Agrega esos registros en tu panel de DNS
7. Espera a que se verifiquen (puede tardar hasta 48 horas)

**Beneficios de Domain Authentication:**
- ✅ Mejor deliverability
- ✅ Mejor reputación del dominio
- ✅ Menos probabilidad de ir a SPAM
- ✅ Permite usar cualquier email @fercupuntodeventa.com

---

### 2. Configurar API Key

Tu API Key se configura en el archivo `.env` (en la raíz del proyecto):
```
/var/www/restaurantes/.env
```

Copia `.env.example` a `.env` si no lo tienes y agrega tu clave.

Si necesitas crear una nueva:

1. Ve a: https://app.sendgrid.com/settings/api_keys
2. Click en "Create API Key"
3. Nombre: `Fercu Restaurante - Production`
4. Permisos: `Full Access` o `Mail Send` (mínimo)
5. Click en "Create & View"
6. **COPIA LA KEY INMEDIATAMENTE** (solo se muestra una vez)
7. Actualiza en tu archivo `.env`:
   ```
   SENDGRID_API_KEY=SG.NUEVA_API_KEY_AQUI
   ```

---

## 🚨 Solución de Problemas

### Error: "The from address does not match a verified Sender Identity"

**Causa:** El email remitente no está verificado en SendGrid.

**Solución:**
1. Ve a: https://app.sendgrid.com/settings/sender_auth/senders
2. Verifica que `notificaciones@fercupuntodeventa.com` esté en la lista
3. Si no está, créalo y verif ícalo

---

### Error: "Invalid API Key"

**Causa:** La API Key es inválida, expiró o fue eliminada.

**Solución:**
1. Verifica la key en: https://app.sendgrid.com/settings/api_keys
2. Si no existe, crea una nueva
3. Actualiza en `/var/www/restaurantes/.env`
4. Limpia el caché: `php -r "if(function_exists('opcache_reset')) opcache_reset();"`

---

### Los emails van a SPAM

**Causas posibles:**
- Domain no autenticado
- Contenido sospechoso
- Sender no verificado
- Reputación baja del dominio

**Soluciones:**
1. Autentica tu dominio (Domain Authentication)
2. Agrega un registro SPF:
   ```
   v=spf1 include:sendgrid.net ~all
   ```
3. Agrega un registro DKIM (SendGrid te lo proporciona)
4. Evita palabras spam en el asunto ("GRATIS", "GANA DINERO", etc.)
5. Incluye un enlace de "Unsubscribe" en los emails

---

### Límites de SendGrid

**Plan Gratuito:**
- 100 emails/día
- Sin soporte técnico
- Límite de 2,000 contactos

**Si necesitas más:**
- Plan Essentials: $19.95/mes - 50,000 emails/mes
- Plan Pro: $89.95/mes - 1,500,000 emails/mes

Verifica tu plan en: https://app.sendgrid.com/account/billing

---

## 📋 Checklist de Configuración

- [x] SendGrid instalado (`sendgrid/sendgrid`)
- [x] API Key configurada
- [x] Configuración de remitente
- [x] Plantillas de email creadas
- [x] Integración en registro de usuarios
- [x] Integración en webhook de Stripe
- [x] Script de prueba creado
- [ ] Sender verificado en SendGrid
- [ ] Domain authentication configurado (opcional pero recomendado)
- [ ] Prueba de envío exitosa

---

## 🎯 Próximos Pasos Recomendados

1. **Verificar Sender:**
   - Ve a SendGrid y verifica `notificaciones@fercupuntodeventa.com`

2. **Probar Envío:**
   ```bash
   php /var/www/restaurantes/sendgrid-test.php
   ```

3. **Hacer Registro de Prueba:**
   - Registra un usuario desde la landing page
   - Verifica que reciba el email

4. **Monitorear Logs:**
   ```bash
   tail -f /var/www/restaurantes/logs/emails.log
   ```

5. **Configurar Domain Authentication** (Producción):
   - Mejora significativamente la deliverability
   - Reduce probabilidad de ir a SPAM

---

## 📚 Recursos Adicionales

- **Documentación SendGrid:** https://docs.sendgrid.com/
- **API Reference:** https://docs.sendgrid.com/api-reference/
- **Dashboard:** https://app.sendgrid.com/
- **Support:** https://support.sendgrid.com/

---

**🎉 ¡SendGrid está listo para enviar emails profesionales a tus usuarios!**


