# Solución para el push bloqueado por GitHub (claves secretas)

GitHub bloqueó el push porque detectó claves secretas en el código. Se han migrado las claves a variables de entorno.

## Cambios realizados

- **config/sendgrid.php** y **config/stripe.php**: ahora usan variables de entorno
- **config/env.php**: cargador del archivo `.env`
- **.env.example**: plantilla con placeholders (sí se sube a Git)
- **.env**: tus claves reales (NO se sube, está en .gitignore)
- **vendor/**: añadido a .gitignore (las dependencias se instalan con `composer install`)

## Pasos para poder hacer push

Como los secretos ya están en el historial de commits, necesitas reescribir el historial:

### Opción A: Si solo tienes 1 commit pendiente de push

```bash
# 1. Deshacer el último commit (mantiene los cambios)
git reset --soft HEAD~1

# 2. Dejar de trackear vendor (ya está en .gitignore)
git rm -r --cached vendor/

# 3. Verificar que .env NO aparezca en los archivos a subir
git status

# 4. Añadir los cambios
git add .

# 5. Hacer un nuevo commit sin secretos
git commit -m "Usar variables de entorno para claves secretas"

# 6. Push
git push origin main
```

### Opción B: Si tienes varios commits pendientes

Necesitas hacer un rebase para editar el commit que contiene los secretos (cea239f).
Contacta si necesitas ayuda con esto.

## En el servidor / despliegue

1. Copia `.env.example` a `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edita `.env` y agrega tus claves reales de SendGrid y Stripe.

3. Instala dependencias si no están:
   ```bash
   composer install
   ```
