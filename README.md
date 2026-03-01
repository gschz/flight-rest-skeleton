# FlightPHP REST Skeleton — Pure REST API

<p align="center">
    <a href="https://github.com/flightphp/core"><img src="https://img.shields.io/badge/Flight-v3.0-blue?style=flat-square&logo=php" alt="FlightPHP"></a>
    <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat-square&logo=php" alt="PHP 8.4"></a>
    <a href="https://laravel.com/docs/eloquent"><img src="https://img.shields.io/badge/Eloquent-ORM-FF2D20?style=flat-square&logo=laravel" alt="Eloquent"></a>
    <a href="https://book.cakephp.org/phinx/0/en/index.html"><img src="https://img.shields.io/badge/Phinx-Migrations-blue?style=flat-square" alt="Phinx"></a>
    <a href="https://tracy.nette.org/"><img src="https://img.shields.io/badge/Tracy-Debugger-green?style=flat-square" alt="Tracy"></a>
    <a href="https://phpunit.de/"><img src="https://img.shields.io/badge/PHPUnit-Testing-3c9cd7?style=flat-square&logo=php" alt="PHPUnit"></a>
</p>

Proyecto base para FlightPHP v3 como **API REST pura** con Eloquent ORM, Phinx migrations, Runway (CLI), Tracy, PHPStan, PHPUnit, Rector y PHP-CS-Fixer.

## Requisitos

- PHP 8.4+
- Composer
- SQLite (para desarrollo local, sin servidor necesario)
- PostgreSQL (opcional, para staging/producción)

## Instalación e inicio rápido

```bash
composer install

# Copiar la plantilla de entorno para desarrollo local (SQLite por defecto)
cp .envs/.env.example .envs/.env.local

# (Opcional) Editar .envs/.env.local con tus valores (APP_KEY, etc.)

# Genera migraciones iniciales y seeds
composer db:migrate
composer db:seed

# Arrancar el servidor de desarrollo
composer dev
```

Abrir http://localhost:8000/health

> [!WARNING]
> No commitear `.envs/.env.local` ni `.envs/.env.pg.local`. Están git-ignorados y deben contener valores reales (secrets).

## Multi-entorno: SQLite vs PostgreSQL

> [!TIP]
> En Windows, puedes usar rutas relativas en `DB_DATABASE` para referenciar la base de datos SQLite.
> El sistema de resolución automática en `config.php` convierte la ruta relativa en una ruta absoluta.
> Ejemplo: `database/database.sqlite3`

| Variable               | Descripción                                              | Default                     |
| ---------------------- | -------------------------------------------------------- | --------------------------- |
| `APP_ENV`              | `development`, `production`, `testing`                   | `development`               |
| `DB_CONNECTION`        | `sqlite` o `pgsql`                                       | `sqlite`                    |
| `DB_DATABASE`          | Ruta SQLite o nombre BD PostgreSQL                       | `database/database.sqlite3` |
| `DB_HOST`              | Host PostgreSQL                                          | `127.0.0.1`                 |
| `DB_PORT`              | Puerto PostgreSQL                                        | `5432`                      |
| `DB_USERNAME`          | Usuario PostgreSQL                                       | `postgres`                  |
| `DB_PASSWORD`          | Contraseña PostgreSQL                                    | _(vacío)_                   |
| `DATABASE_URL`         | DSN completo (Heroku/Railway/etc.) — sobreescribe DB\_\* | _(vacío)_                   |
| `CORS_ALLOWED_ORIGINS` | Orígenes CORS permitidos                                 | `*`                         |

> [!NOTE]
> La aplicación lee configuración con `getenv()`; no se parsean `.env` dentro del runtime. En desarrollo, los env files se cargan con `composer dev` / `composer dev:pg` (ver `bin/dev.php`).

### Desarrollo local (SQLite)

```bash
cp .envs/.env.example .envs/.env.local

# Editar .envs/.env.local con tus valores (APP_KEY, etc.)
php -r '$b = base64_encode(random_bytes(32)); echo "base64:$b";'

# Arrancar el servidor de desarrollo (carga .envs/.env.local automáticamente)
composer dev
```

### Desarrollo con PostgreSQL

```bash
cp .envs/.env.pg.example .envs/.env.pg.local

# Editar .envs/.env.pg.local con tus credenciales
composer dev:pg
```

### Estructura de `.envs/`

| Archivo                   | Descripción                                   | Trackeado en git  |
| ------------------------- | --------------------------------------------- | ----------------- |
| `.env.example`            | Plantilla SQLite (desarrollo local, default)  | Sí                |
| `.env.pg.example`         | Plantilla PostgreSQL (desarrollo local)       | Sí                |
| `.env.production.example` | Referencia de producción (sin valores reales) | Sí                |
| `.env.local`              | Valores reales SQLite locales                 | No (git-ignorado) |
| `.env.pg.local`           | Valores reales PostgreSQL locales             | No (git-ignorado) |

> **Nota:** `app/config/config.php` está trackeado en git — no contiene credenciales. No es necesario crearlo manualmente.

## Migraciones (Phinx)

```bash
# Ejecutar migraciones (SQLite por defecto)
composer db:migrate

# Ejecutar migraciones en entorno de tests
composer db:migrate:test

# Rollback
composer db:rollback

# Seeds
composer db:seed
```

## Estructura del proyecto

```
project-root/
├── app/
│   ├── commands/        # Comandos CLI (Runway)
│   ├── config/
│   │   ├── bootstrap.php
│   │   ├── config.php         ← trackeado en git, lee variables de entorno
│   │   ├── routes.php         ← punto de entrada de rutas
│   │   └── services.php       ← Eloquent Capsule + Tracy + Flight::db()
│   ├── controllers/
│   │   └── ApiExampleController.php
│   ├── log/             # Logs (git-ignorados)
│   ├── middlewares/
│   │   ├── CorsMiddleware.php
│   │   └── SecurityHeadersMiddleware.php
│   ├── models/
│   │   └── User.php     ← modelo Eloquent de ejemplo
│   ├── routes/
│   │   ├── api.php      ← rutas de API (/api/v1/...)
│   │   └── web.php      ← rutas Web (/)
│   ├── utils/
│   │   ├── ApiResponse.php
│   │   └── helpers.php  ← funciones globales (base_path, etc.)
│   └── views/           # Vistas (plantillas PHP simples)
├── bin/
│   └── dev.php          # Launcher: carga env file y arranca el servidor
├── database/
│   ├── migrations/      # Migraciones Phinx
│   └── seeders/         # Seeds Phinx
├── public/              # Web root (index.php)
├── tests/               # PHPUnit tests
├── .envs/
│   ├── .env.example            ← plantilla SQLite (trackeada en git)
│   ├── .env.pg.example         ← plantilla PostgreSQL (trackeada en git)
│   └── .env.production.example ← referencia producción (trackeada en git)
├── phinx.php                   ← configuración de migraciones
├── phpstan.neon
├── .php-cs-fixer.php
└── composer.json
```

## Endpoints API

| Método   | Ruta                | Descripción        |
| -------- | ------------------- | ------------------ |
| `GET`    | `/health`           | Health check       |
| `GET`    | `/api/v1/users`     | Listar usuarios    |
| `GET`    | `/api/v1/users/:id` | Obtener usuario    |
| `POST`   | `/api/v1/users`     | Crear usuario      |
| `PUT`    | `/api/v1/users/:id` | Actualizar usuario |
| `DELETE` | `/api/v1/users/:id` | Eliminar usuario   |

## Scripts y calidad (Composer)

```bash
composer dev              # Carga .envs/.env.local y arranca en :8000 (SQLite)
composer dev:pg           # Carga .envs/.env.pg.local y arranca en :8000 (PostgreSQL)
composer db:migrate       # Ejecuta migraciones (SQLite por defecto)
composer db:migrate:pg    # Ejecuta migraciones con PostgreSQL
composer db:migrate:test  # Ejecuta migraciones en entorno de tests
composer db:rollback      # Rollback de la última migración
composer db:seed          # Ejecuta seeds
composer test:unit        # PHPUnit
composer test:stan        # PHPStan análisis estático
composer rector:dry       # Rector (dry-run)
composer rector:fix       # Rector (fix)
composer lint             # PHP-CS-Fixer (dry-run)
composer lint:fix         # PHP-CS-Fixer (fix)
```

## Configuración

- `app/config/config.php` — trackeado en git, sin credenciales, lee variables de entorno con `getenv()`
- `app/config/services.php` — inicializa Eloquent Capsule, Tracy y `Flight::db()`
- `app/config/routes.php` — define todas las rutas API REST

## Seguridad

- Todos los valores sensibles se leen desde variables de entorno (nunca hardcodeados)
- `config.php` está trackeado en git (no contiene credenciales)
- Los env files con valores reales (`.envs/.env.local`, `.envs/.env.pg.local`) están git-ignorados
- CORS configurable via `CORS_ALLOWED_ORIGINS`
- Security headers en todas las rutas API

## Deploy en Heroku

Este proyecto está listo para desplegarse en Heroku sin configuración adicional.
Ver la guía completa de deploy y operaciones en [docs/HEROKU_OPERATIONS.md](docs/HEROKU_OPERATIONS.md).
