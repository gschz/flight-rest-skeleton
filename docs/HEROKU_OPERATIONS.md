# Heroku — Guía completa de Deploy y Operaciones

Referencia de comandos para desplegar, verificar, mantener y operar la aplicación en Heroku.

---

## Deploy inicial

### Prerequisitos

- [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli) instalado
- Cuenta en [heroku.com](https://heroku.com)
- Git configurado con el repositorio del proyecto

### Pasos

```bash
# Login
heroku login

# Crear app (genera un nombre aleatorio si no se especifica)
heroku create tu-app-name

# Agregar Heroku Postgres (setea DATABASE_URL automáticamente)
heroku addons:create heroku-postgresql:essential-0

# Configurar variables de entorno
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_KEY=$(php -r '$b = base64_encode(random_bytes(32)); echo "base64:$b";')
heroku config:set CORS_ALLOWED_ORIGINS=*
heroku config:set LOG_LEVEL=error

# Deploy
git push heroku main

# Verificar
heroku open /health
heroku logs --tail
```

> Las migraciones se ejecutan **automáticamente** en cada deploy gracias al script
> `bin/migrate-if-production.php` configurado como `post-install-cmd` en `composer.json`.
> No es necesario correrlas manualmente después del primer deploy.

### Variables de entorno requeridas

| Variable               | Descripción                      | Cómo setearla                              |
| ---------------------- | -------------------------------- | ------------------------------------------ |
| `DATABASE_URL`         | DSN de PostgreSQL                | Seteada automáticamente por el addon       |
| `APP_ENV`              | Entorno de la aplicación         | `heroku config:set APP_ENV=production`     |
| `APP_KEY`              | Clave secreta de la aplicación   | Ver comando arriba                         |
| `APP_DEBUG`            | Modo debug (false en producción) | `heroku config:set APP_DEBUG=false`        |
| `CORS_ALLOWED_ORIGINS` | Orígenes CORS permitidos         | `heroku config:set CORS_ALLOWED_ORIGINS=*` |
| `LOG_LEVEL`            | Nivel de logging                 | `heroku config:set LOG_LEVEL=error`        |

---

## Información general de la app

```bash
# Resumen completo de la app (región, stack, dynos, addons, git remote)
heroku info

# Listar todas tus apps
heroku apps

# Abrir la app en el navegador
heroku open

# Abrir una ruta específica
heroku open /health
heroku open /api/v1/users
```

---

## Variables de entorno

```bash
# Ver todas las variables de entorno configuradas
heroku config

# Ver una variable específica
heroku config:get DATABASE_URL
heroku config:get APP_ENV

# Setear una variable
heroku config:set APP_KEY=base64:...

# Setear múltiples variables a la vez
heroku config:set APP_ENV=production APP_DEBUG=false LOG_LEVEL=error

# Eliminar una variable
heroku config:unset MI_VARIABLE

# Generar un APP_KEY seguro directamente desde CLI
heroku config:set APP_KEY=$(php -r '$b = base64_encode(random_bytes(32)); echo "base64:$b";')
```

> [!NOTE]
> Cada `config:set` reinicia los dynos automáticamente para aplicar los cambios.

---

## Logs

```bash
# Seguir logs en tiempo real (Ctrl+C para salir)
heroku logs --tail

# Ver las últimas N líneas de log
heroku logs -n 200
heroku logs -n 500

# Filtrar por dyno (solo Web)
heroku logs --tail --dyno web

# Filtrar por proceso (app, heroku, router)
heroku logs --tail --source app
heroku logs --tail --source heroku

```

---

## Base de datos (PostgreSQL)

### Información del addon

```bash
# Resumen del addon PostgreSQL (tamaño, conexiones, estado, plan)
heroku pg:info

# Ver todos los addons de la app
heroku addons

# Ver detalles de un addon específico
heroku addons:info heroku-postgresql
```

### Inspección de datos

```bash
# Abrir consola psql interactiva (requiere PostgreSQL instalado localmente)
heroku pg:psql

# Ver credenciales de conexión (host, usuario, password, base de datos)
heroku pg:credentials:url
```

> [!TIP]
> Si no tienes `psql` instalado localmente en Windows, puedes instalar
> [PostgreSQL para Windows](https://www.postgresql.org/download/windows/) solo para obtener el cliente CLI,
> o usar la consola interactiva en el dashboard de Heroku:
> **heroku.com → tu app → Resources → PostgreSQL addon → Dataclips**.

### Backups

```bash
# Crear un backup manual
heroku pg:backups:capture

# Listar backups disponibles
heroku pg:backups

# Descargar el último backup
heroku pg:backups:download

# Restaurar desde un backup
heroku pg:backups:restore <BACKUP_ID> DATABASE_URL
```

---

## Migraciones y Seeds en producción

> [!IMPORTANT]
> Al usar `heroku run` con comandos que incluyen flags (como `-c`), **siempre encierra el comando completo entre comillas dobles**. De lo contrario, el Heroku CLI captura los flags antes de pasarlos al dyno.

### Migraciones

```bash
# Ver estado de migraciones en producción
heroku run "vendor/bin/phinx status -c phinx.php"

# Ejecutar migraciones manualmente (normalmente corre automático en deploy)
heroku run "vendor/bin/phinx migrate -c phinx.php"

# Rollback de la última migración
heroku run "vendor/bin/phinx rollback -c phinx.php"

# Rollback de las últimas N migraciones
heroku run "vendor/bin/phinx rollback -c phinx.php -t 0"
```

> Las migraciones se ejecutan **automáticamente** en cada deploy gracias al script
> `bin/migrate-if-production.php` configurado como `post-install-cmd` en `composer.json`.
> El comando manual es útil para verificar el estado o forzar una corrección.

### Seeds

```bash
# Ejecutar todos los seeders
heroku run "vendor/bin/phinx seed:run -c phinx.php"

# Ejecutar un seeder específico
heroku run "vendor/bin/phinx seed:run -c phinx.php -s UserSeeder"
```

> [!WARNING]
> Los seeds en producción insertan datos de ejemplo. Úsalos solo si es intencional
> (por ejemplo, datos de catálogo o configuración inicial). No ejecutes seeds en producción
> si ya existen datos reales.

---

## Addons

```bash
# Listar todos los addons activos
heroku addons

# Crear el addon de PostgreSQL (si aún no existe)
heroku addons:create heroku-postgresql:essential-0

# Ver información de un addon por su nombre completo
heroku addons:info postgresql-dimensional-08762

# Eliminar un addon duplicado o no usado
# ! Esto destruye la base de datos y todos sus datos
heroku addons:destroy postgresql-nombre-del-addon

# Promover un addon DB como DATABASE_URL principal
heroku pg:promote HEROKU_POSTGRESQL_NOMBRE_COLOR_URL
```

> [!WARNING]
> `heroku addons:destroy` elimina permanentemente la base de datos y todos sus datos.
> Asegúrate de que no es el addon activo (`DATABASE_URL`) antes de destruirlo.

---

## Verificación post-deploy

Lista de verificación después de cada deploy:

```bash
# 1. Confirmar que el deploy terminó sin errores
heroku releases
heroku releases:output   # ver el log del último release

# 2. Verificar que los dynos están corriendo
heroku ps

# 3. Verificar health check
heroku open /health

# 4. Verificar estado de migraciones
heroku run "vendor/bin/phinx status -c phinx.php"

# 5. Revisar logs recientes buscando errores
heroku logs -n 100 --source app

# 6. Confirmar que la API responde correctamente
heroku open /api/v1/users
```

---

## Comandos one-off (heroku run)

`heroku run` lanza un dyno temporal ("one-off") para ejecutar un comando puntual.

```bash
# Regla general: encerrar el comando entre comillas cuando use flags con guión
heroku run "COMANDO -flag valor"

# Verificar la versión de PHP en producción
heroku run "php -v"

# Ver variables de entorno dentro del dyno
heroku run "env | grep -E 'APP_|DATABASE_|LOG_'"

# Ejecutar un script PHP arbitrario
heroku run "php bin/mi-script.php"

# Abrir una shell interactiva (bash)
heroku run bash
```

> [!NOTE]
> Los dynos one-off tienen acceso a las mismas variables de entorno que el dyno `web`,
> incluyendo `DATABASE_URL`. No se necesita configuración adicional.

---

## Troubleshooting

### La app devuelve 500 o no responde

```bash
heroku logs --tail --source app
heroku ps
heroku restart
```

### Las migraciones no corrieron en el deploy

```bash
# Verificar que el script existe y es ejecutable
heroku run "php bin/migrate-if-production.php"

# Revisar el log del release específico
heroku releases
heroku releases:output <número>
```

### `heroku run vendor/bin/phinx ... -c phinx.php` falla con "Nonexistent flag: -c"

El Heroku CLI intercepta los flags antes de pasarlos al dyno. Solución:

```bash
# Incorrecto
heroku run vendor/bin/phinx migrate -c phinx.php

# Correcto
heroku run "vendor/bin/phinx migrate -c phinx.php"
```

### `heroku pg:psql` falla por no tener psql instalado localmente

Opciones sin instalar psql:

```bash
# Ver estado de migraciones
heroku run "vendor/bin/phinx status -c phinx.php"

# Abrir shell y ejecutar SQL directamente desde php
heroku run bash
# Dentro del dyno:
php -r "require 'vendor/autoload.php'; require 'app/config/bootstrap.php'; print_r(Flight::db()->fetchAll('SELECT * FROM users'));"
```

O instalar el cliente PostgreSQL desde [postgresql.org/download/windows](https://www.postgresql.org/download/windows/).

### Verificar que `DATABASE_URL` está configurada

```bash
heroku config:get DATABASE_URL
```

Si está vacía, el addon de PostgreSQL no fue creado o no está attached:

```bash
heroku addons:create heroku-postgresql:essential-0
```

### Ver adónde apunta realmente `DATABASE_URL`

```bash
heroku pg:info
```

El addon marcado como `DATABASE_URL` en la columna `Add-on` es el que usa la app.
Si hay más de uno, solo uno tendrá esa etiqueta.

### `heroku run` falla con "Couldn't find that app"

Ocurre cuando se renombra la app en Heroku pero el remote de git local sigue apuntando al nombre anterior.

**Solución — actualizar el remote:**

```bash
# Ver a dónde apunta el remote actual
git remote -v

# Opción A: actualizar la URL del remote manualmente
git remote set-url heroku https://git.heroku.com/NUEVO-NOMBRE-APP.git

# Opción B: dejar que el CLI reescriba el remote automáticamente
heroku git:remote -a NUEVO-NOMBRE-APP
```

Verificar que quedó correcto:

```bash
git remote -v
heroku run "php -v"
```

> [!TIP]
> Si manejas varias apps y no quieres depender del remote, puedes pasar `--app` explícitamente
> en cualquier comando del CLI:
>
> ```bash
> heroku run "php -v" --app NOMBRE-APP
> heroku logs --tail --app NOMBRE-APP
> heroku config --app NOMBRE-APP
> ```
