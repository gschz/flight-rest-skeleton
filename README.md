# FlightPHP REST Skeleton

<p align="center">
    <a href="https://github.com/flightphp/core"><img src="https://img.shields.io/badge/Flight-v3.0-blue?style=flat-square&logo=php" alt="FlightPHP"></a>
    <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat-square&logo=php" alt="PHP 8.4"></a>
    <a href="https://laravel.com/docs/eloquent"><img src="https://img.shields.io/badge/Eloquent-ORM-FF2D20?style=flat-square&logo=laravel" alt="Eloquent"></a>
    <a href="https://book.cakephp.org/phinx/0/en/index.html"><img src="https://img.shields.io/badge/Phinx-Migrations-blue?style=flat-square" alt="Phinx"></a>
    <a href="https://github.com/firebase/php-jwt"><img src="https://img.shields.io/badge/JWT-Auth-orange?style=flat-square" alt="JWT"></a>
    <a href="https://swagger.io/specification/"><img src="https://img.shields.io/badge/OpenAPI-3.0-85EA2D?style=flat-square&logo=swagger" alt="OpenAPI"></a>
    <a href="https://tracy.nette.org/"><img src="https://img.shields.io/badge/Tracy-Debugger-green?style=flat-square" alt="Tracy"></a>
    <a href="https://phpunit.de/"><img src="https://img.shields.io/badge/PHPUnit-Testing-3c9cd7?style=flat-square&logo=php" alt="PHPUnit"></a>
</p>

Skeleton production-ready para FlightPHP v3 como **REST API pragmática** con autenticación JWT, rate limiting DB-backed, documentación OpenAPI 3.0, Eloquent ORM, Phinx migrations, Runway CLI, Tracy, PHPStan level max, Rector y PHP-CS-Fixer.

> Este skeleton sigue un enfoque **REST pragmático (RMM Nivel 2)**: recursos con sustantivos, verbos HTTP semánticos (GET/POST/PUT/DELETE), respuestas JSON estandarizadas y stateless via JWT. No implementa HATEOAS (nivel 3 de Richardson) por diseño.

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

## Decisiones arquitectónicas

Este skeleton adopta decisiones que se apartan deliberadamente del FlightPHP vanilla:

| Decisión                                                                       | Por qué                                                                                                                      |
| ------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------- |
| **Eloquent ORM** en lugar de `flightphp/active-record`                         | Ecosistema maduro, relaciones, scopes, soporte nativo para SQLite y PostgreSQL sin cambio de código                          |
| **Variables de entorno vía `getenv()`** en lugar de hardcodear en `config.php` | Portabilidad entre entornos (local, staging, producción, CI). Compatible con Heroku, Railway, Fly.io, etc.                   |
| **`.envs/*.env.*` para desarrollo local**                                      | Perfiles explícitos por entorno sin depender de loaders en runtime. Cargado por `bin/dev.php` antes de arrancar el servidor. |
| **Phinx** para migraciones                                                     | Independiente de Laravel, compatible con SQLite y PostgreSQL, integrado con Runway CLI                                       |
| **Release phase en Heroku** (`Procfile`)                                       | Las migraciones corren con acceso a la DB, antes de promover el nuevo dyno, sin riesgo de ejecutarlas en build time          |
| **PHPStan level max + Rector + PHP-CS-Fixer**                                  | Estándares de calidad de código modernos, compatibles con PHP 8.4                                                            |
| **`Cache-Control` explícito**                                                  | `no-store` en rutas autenticadas (datos sensibles), `public, max-age=60` en `/health` (idempotente y público)                |

> Ver también: [docs/CODE_QUALITY_LOCAL.md](docs/CODE_QUALITY_LOCAL.md) para la guía de calidad de código y convención bilingüe del proyecto.

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
│   ├── commands/                       # Comandos CLI (Runway)
│   ├── config/
│   │   ├── bootstrap.php
│   │   ├── config.php                  ← trackeado en git, lee variables de entorno
│   │   ├── routes.php                  ← punto de entrada de rutas
│   │   └── services.php                ← Eloquent Capsule + Tracy + Flight::db()
│   ├── controllers/
│   │   ├── ApiExampleController.php    ← CRUD de usuarios (auth requerido)
│   │   ├── AuthController.php          ← login / refresh / logout (JWT)
│   │   ├── DocsController.php          ← Swagger UI / ReDoc / openapi.json
│   │   └── WelcomeController.php       ← página de bienvenida (/)
│   ├── log/                            # Logs (git-ignorados)
│   ├── middlewares/
│   │   ├── AuthMiddleware.php          ← valida JWT en rutas protegidas
│   │   ├── CorsMiddleware.php
│   │   ├── RateLimitMiddleware.php     ← ventana fija por IP (DB-backed)
│   │   └── SecurityHeadersMiddleware.php
│   ├── models/
│   │   └── User.php                    ← modelo Eloquent de ejemplo
│   ├── openapi/
│   │   └── OpenApiInfo.php             ← atributos #[OA\Info] y #[OA\Server]
│   ├── routes/
│   │   ├── api.php                     ← rutas de API (/api/v1/...)
│   │   └── web.php                     ← rutas Web (/ + /docs)
│   ├── utils/
│   │   ├── ApiResponse.php
│   │   └── helpers.php                 ← funciones globales (base_path, etc.)
│   └── views/
│       ├── docs/
│       │   ├── redoc.php               ← vista ReDoc
│       │   └── swagger.php             ← vista Swagger UI
│       └── welcome.php                 ← página de bienvenida
├── bin/
│   └── dev.php                         # Launcher: carga env file y arranca el servidor
├── database/
│   ├── migrations/                     # Migraciones Phinx
│   └── seeders/                        # Seeds Phinx
├── public/
│   ├── api-docs/
│   │   └── openapi.json                ← spec generado (git-ignorado)
│   └── index.php                       # Web root
├── tests/                              # PHPUnit tests
├── .envs/
│   ├── .env.example                    ← plantilla SQLite (trackeada en git)
│   ├── .env.pg.example                 ← plantilla PostgreSQL (trackeada en git)
│   └── .env.production.example         ← referencia producción (trackeada en git)
├── phinx.php                           ← configuración de migraciones
├── phpstan.neon
├── .php-cs-fixer.php
└── composer.json
```

## Endpoints API

### Sistema

| Método | Ruta      | Descripción  | Auth |
| ------ | --------- | ------------ | ---- |
| `GET`  | `/health` | Health check | No   |

### Auth

| Método | Ruta                   | Descripción                                     | Auth |
| ------ | ---------------------- | ----------------------------------------------- | ---- |
| `POST` | `/api/v1/auth/login`   | Login — devuelve `access_token`+`refresh_token` | No   |
| `POST` | `/api/v1/auth/refresh` | Obtener nuevo `access_token` con refresh token  | No   |
| `POST` | `/api/v1/auth/logout`  | Revocar refresh token (idempotente)             | No   |

### Usuarios (requieren `Authorization: Bearer <token>`)

| Método   | Ruta                | Descripción                |
| -------- | ------------------- | -------------------------- |
| `GET`    | `/api/v1/users`     | Listar usuarios (paginado) |
| `GET`    | `/api/v1/users/:id` | Obtener usuario            |
| `POST`   | `/api/v1/users`     | Crear usuario              |
| `PUT`    | `/api/v1/users/:id` | Actualizar usuario         |
| `DELETE` | `/api/v1/users/:id` | Eliminar usuario           |

### Documentación (solo desarrollo local)

| Método | Ruta                     | Descripción                |
| ------ | ------------------------ | -------------------------- |
| `GET`  | `/docs`                  | Swagger UI 5 (interactivo) |
| `GET`  | `/docs/redoc`            | ReDoc (solo lectura)       |
| `GET`  | `/api-docs/openapi.json` | Spec OpenAPI 3.0 JSON      |

> Los endpoints de documentación solo se registran cuando `IS_PRODUCTION` es falso. Ejecuta `composer docs:generate` para generar el spec antes de visitarlos.

## Scripts y calidad (Composer)

```bash
composer dev              # Carga .envs/.env.local y arranca en :8000 (SQLite)
composer dev:pg           # Carga .envs/.env.pg.local y arranca en :8000 (PostgreSQL)
composer db:migrate       # Ejecuta migraciones (SQLite por defecto)
composer db:migrate:pg    # Ejecuta migraciones con PostgreSQL
composer db:migrate:test  # Ejecuta migraciones en entorno de tests
composer db:rollback      # Rollback de la última migración
composer db:seed          # Ejecuta seeds
composer docs:generate    # Genera public/api-docs/openapi.json desde atributos OA
composer test:unit        # PHPUnit
composer test:stan        # PHPStan análisis estático
composer rector:dry       # Rector (dry-run)
composer rector:fix       # Rector (fix)
composer lint             # PHP-CS-Fixer (dry-run)
composer lint:fix         # PHP-CS-Fixer (fix)
```

## Autenticación JWT

El skeleton incluye un flujo JWT completo con access token + refresh token.

| Variable env      | Descripción                       | Default           |
| ----------------- | --------------------------------- | ----------------- |
| `JWT_SECRET`      | Clave de firma (mínimo 32 bytes)  | Requerido         |
| `JWT_TTL`         | TTL del access token en segundos  | `3600`            |
| `JWT_REFRESH_TTL` | TTL del refresh token en segundos | `604800` (7 días) |

## Rate Limiting

Protege las rutas `/api/v1` con ventana fija configurable por IP, sin dependencias externas (tabla SQLite/PostgreSQL).

| Variable env                | Descripción                        | Default |
| --------------------------- | ---------------------------------- | ------- |
| `RATE_LIMIT_ENABLED`        | Activa/desactiva el middleware     | `true`  |
| `RATE_LIMIT_MAX_REQUESTS`   | Máximo de requests por ventana     | `60`    |
| `RATE_LIMIT_WINDOW_SECONDS` | Duración de la ventana en segundos | `60`    |

Cuando se supera el límite, la respuesta es `429 Too Many Requests` con el header `Retry-After`.

## Documentación OpenAPI

El spec se genera desde atributos PHP 8 (`#[OA\...]`) en los controladores.

```bash
# Generar spec
composer docs:generate

# Abrir Swagger UI interactivo
open http://localhost:8000/docs

# O la vista de solo lectura (ReDoc)
open http://localhost:8000/docs/redoc
```

> Los endpoints de documentación solo se registran fuera de producción.

## Configuración

- `app/config/config.php` — trackeado en git, sin credenciales, lee variables de entorno con `getenv()`
- `app/config/services.php` — inicializa Eloquent Capsule, Tracy y `Flight::db()`
- `app/config/routes.php` — define todas las rutas API REST

## Seguridad

- Todos los valores sensibles se leen desde variables de entorno (nunca hardcodeados)
- `config.php` está trackeado en git (no contiene credenciales)
- Los env files con valores reales (`.envs/.env.local`, `.envs/.env.pg.local`) están git-ignorados
- CORS configurable via `CORS_ALLOWED_ORIGINS`
- Security headers en todas las rutas API (`X-Content-Type-Options`, `X-Frame-Options`, `HSTS`, etc.)
- `Cache-Control: no-store` en todas las rutas `/api/v1` — datos autenticados jamás se almacenan en caché de proxies
- `Cache-Control: public, max-age=60` en `/health` — endpoint público e idempotente, cacheable en CDN/proxies

## Deploy en Heroku

Este proyecto está listo para desplegarse en Heroku sin configuración adicional.
Ver la guía completa de deploy y operaciones en [docs/HEROKU_OPERATIONS.md](docs/HEROKU_OPERATIONS.md).
