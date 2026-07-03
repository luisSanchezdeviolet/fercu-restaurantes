# Guia de Mailgun - Fercu Restaurante

## Estado actual

Mailgun es el proveedor de correos transaccionales del SaaS.

- Configuracion central: `config/mailgun.php`
- Plantillas HTML: `config/email-templates.php`
- Logs: `logs/emails.log`
- Variables: `.env`

La integracion anterior fue retirada del codigo y de Composer. Usar solamente `config/mailgun.php` y variables `MAILGUN_*`.

---

## Emails implementados

### Bienvenida con prueba gratuita

Se envia cuando un usuario registra una empresa con trial de 15 dias.

Archivo:
- `config/email-templates.php` -> `getWelcomeTrialEmailHTML()`

### Bienvenida con suscripcion de pago

Se envia cuando se registra una empresa con plan de pago.

Archivo:
- `config/email-templates.php` -> `getWelcomeSubscriptionEmailHTML()`

Nota operativa: el flujo de checkout todavia debe cerrarse para evitar activar planes pagados sin confirmacion real de cobro.

### Confirmacion de pago

Se envia desde `stripe-webhook.php` cuando Stripe notifica `invoice.paid`.

Archivo:
- `config/email-templates.php` -> `getPaymentConfirmationEmailHTML()`

---

## Variables de entorno

Completar en `/var/www/restaurantes/.env`:

```bash
MAILGUN_API_KEY=key-tu-api-key
MAILGUN_DOMAIN=mg.tudominio.com
MAILGUN_BASE_URL=https://api.mailgun.net
MAILGUN_FROM_EMAIL=notificaciones@fercupuntodeventa.com
MAILGUN_FROM_NAME=Fercu Restaurante
MAILGUN_SUPPORT_EMAIL=soporte@fercupuntodeventa.com
APP_URL=http://restaurante.fercupuntodeventa.com
```

Para dominios EU, Mailgun suele usar:

```bash
MAILGUN_BASE_URL=https://api.eu.mailgun.net
```

---

## Configuracion en Mailgun

1. Entrar al dashboard de Mailgun.
2. Crear o seleccionar el dominio de envio, por ejemplo `mg.fercupuntodeventa.com`.
3. Agregar los DNS que Mailgun indique:
   - SPF/TXT
   - DKIM/TXT
   - MX si se usara recepcion
   - CNAME de tracking si se habilita
4. Esperar verificacion del dominio.
5. Crear una API key con permisos de envio.
6. Cargar valores en `.env`.

Para produccion se recomienda usar un subdominio dedicado, por ejemplo `mg.fercupuntodeventa.com`, y mantener el remitente como `notificaciones@fercupuntodeventa.com` solo si Mailgun/DNS lo validan correctamente.

---

## Base de datos local en Docker

El proyecto usa MySQL 8 en Docker segun `docker-compose.yml`:

```text
Servicio: phprestaurante
Imagen: mysql:8.0
Host desde PHP local: 127.0.0.1
Puerto host: 3308
Puerto contenedor: 3306
Base: restaurante_pos
```

Variables locales recomendadas:

```bash
DB_HOST=127.0.0.1
DB_PORT=3308
DB_NAME=restaurante_pos
```

No ejecutar SQL destructivo sin respaldo y aprobacion explicita.

---

## Probar envio

No hay un script interactivo dedicado para Mailgun. Para una prueba rapida desde CLI:

```bash
php -r "require 'config/mailgun.php'; var_export(sendEmail('destino@ejemplo.com', 'Prueba', 'Prueba Mailgun', '<p>OK Mailgun</p>', 'OK Mailgun')); echo PHP_EOL;"
```

Tambien se puede probar con el flujo real:

1. Registrar una empresa desde la landing.
2. Revisar el correo destino.
3. Revisar `logs/emails.log`.

Ver logs:

```bash
tail -f /var/www/restaurantes/logs/emails.log
tail -50 /var/www/restaurantes/logs/emails.log
grep "usuario@ejemplo.com" /var/www/restaurantes/logs/emails.log
```

Formato esperado:

```text
[2026-05-06 10:30:45] SUCCESS | Provider: MAILGUN | To: usuario@ejemplo.com | Subject: Bienvenido | MessageID: <...> | Message: Email enviado correctamente
```

---

## Troubleshooting

### Mailgun no configurado

Mensaje:

```text
Mailgun no configurado: faltan MAILGUN_API_KEY o MAILGUN_DOMAIN
```

Solucion:
- Confirmar `MAILGUN_API_KEY`.
- Confirmar `MAILGUN_DOMAIN`.
- Confirmar que `config/env.php` esta cargando `.env`.

### Dominio no verificado

Solucion:
- Revisar DNS en Mailgun.
- Validar SPF/DKIM.
- Esperar propagacion.

### API base incorrecta

Solucion:
- Usar `https://api.mailgun.net` para region US.
- Usar `https://api.eu.mailgun.net` para region EU.

### Emails llegan a spam

Solucion:
- Verificar SPF y DKIM.
- Usar dominio de envio dedicado.
- Evitar asuntos agresivos.
- Revisar reputacion y logs de Mailgun.

---

## Checklist

- [x] `config/mailgun.php` creado.
- [x] `config/email-templates.php` usa `EMAIL_SUPPORT_EMAIL`.
- [x] `register-procesar.php` usa Mailgun.
- [x] `stripe-webhook.php` usa Mailgun.
- [x] Dependencia anterior de correo retirada de Composer.
- [ ] Dominio Mailgun verificado.
- [ ] API key real cargada en `.env`.
- [ ] Prueba de envio exitosa.
- [ ] Registro real probado contra BD Docker.
