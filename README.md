# PHP Flight Setup — Pure REST API Skeleton

Proyecto base para FlightPHP v3 como **API REST pura** con Eloquent ORM, Phinx migrations, Runway (CLI), Tracy, PHPStan, PHPUnit, Rector y PHP-CS-Fixer.

## Requisitos

- PHP 8.4
- Composer
- SQLite (para desarrollo local, sin servidor necesario)
- PostgreSQL (opcional, para staging/producción)

## Instalación e inicio rápido

```bash
composer install

# Copiar la plantilla de configuración
cp app/config/config_sample.php app/config/config.php

# Crear el directorio de base de datos (si no existe)
mkdir -p database

# Arrancar el servidor de desarrollo (SQLite por defecto)
composer start
```

Abrir http://localhost:8000/health

## Multi-entorno: SQLite vs PostgreSQL

Este proyecto usa `getenv()` para leer variables de entorno — sin loaders `.env` embebidos en la app.

| Variable | Descripción | Default |
|---|---|---|
| `APP_ENV` | `development`, `production`, `testing` | `development` |
| `DB_CONNECTION` | `sqlite` o `pgsql` | `sqlite` |
| `DB_DATABASE` | Ruta SQLite o nombre BD PostgreSQL | `database/database.sqlite` |
| `DB_HOST` | Host PostgreSQL | `127.0.0.1` |
| `DB_PORT` | Puerto PostgreSQL | `5432` |
| `DB_USERNAME` | Usuario PostgreSQL | `postgres` |
| `DB_PASSWORD` | Contraseña PostgreSQL | _(vacío)_ |
| `CORS_ALLOWED_ORIGINS` | Orígenes CORS permitidos | `*` |

### Desarrollo local (SQLite)

```bash
composer start
```

### Desarrollo con PostgreSQL

```bash
DB_CONNECTION=pgsql DB_HOST=localhost DB_DATABASE=myapp DB_USERNAME=postgres composer start:pg
```

Ver `.env.example` y `.envs/.env.example` / `.envs/.env.pg.example` para referencia.

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
│   │   ├── config_sample.php  ← plantilla pública (trackeada en git)
│   │   ├── config.php         ← git-ignorado, leer variables de entorno
│   │   ├── routes.php         ← rutas API REST
│   │   └── services.php       ← Eloquent Capsule + Tracy + Flight::db()
│   ├── controllers/
│   │   └── ApiExampleController.php
│   ├── log/             # Logs (git-ignorados)
│   ├── middlewares/
│   │   ├── CorsMiddleware.php
│   │   └── SecurityHeadersMiddleware.php
│   ├── models/
│   │   └── User.php     ← modelo Eloquent de ejemplo
│   ├── utils/
│   │   └── ApiResponse.php
│   └── views/           # Mantenido pero no expuesto como endpoints HTTP
├── database/
│   ├── migrations/      # Migraciones Phinx
│   └── seeders/         # Seeds Phinx
├── public/              # Web root (index.php)
├── tests/               # PHPUnit tests
├── .envs/
│   ├── .env.example     ← plantilla SQLite
│   └── .env.pg.example  ← plantilla PostgreSQL
├── phinx.php            ← configuración de migraciones
├── phpstan.neon
├── .php-cs-fixer.php
└── composer.json
```

## Endpoints API

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/health` | Health check |
| `GET` | `/api/v1/users` | Listar usuarios |
| `GET` | `/api/v1/users/:id` | Obtener usuario |
| `POST` | `/api/v1/users` | Crear usuario |
| `PUT` | `/api/v1/users/:id` | Actualizar usuario |
| `DELETE` | `/api/v1/users/:id` | Eliminar usuario |

## Scripts y calidad (Composer)

```bash
composer run test:unit      # PHPUnit
composer run test:stan      # PHPStan análisis estático
composer run rector:dry     # Rector (dry-run)
composer run rector:fix     # Rector (fix)
composer run lint            # PHP-CS-Fixer (dry-run)
composer run lint:fix        # PHP-CS-Fixer (fix)
```

## Configuración

- `app/config/config_sample.php` — plantilla pública trackeada en git (sin credenciales)
- `app/config/config.php` — git-ignorado, lee variables de entorno con `getenv()`
- `app/config/services.php` — inicializa Eloquent Capsule, Tracy y `Flight::db()`
- `app/config/routes.php` — define todas las rutas API REST

## Seguridad

- Todos los valores sensibles se leen desde variables de entorno (nunca hardcodeados)
- `config.php` es git-ignorado
- CORS configurable via `CORS_ALLOWED_ORIGINS`
- Security headers en todas las rutas API
