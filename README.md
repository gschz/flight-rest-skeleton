# PHP Flight Setup

Proyecto base para FlightPHP v3 con Runway (CLI), Tracy, PHPStan, PHPUnit, Rector y PHP-CS-Fixer, con configuración consistente para desarrollo local.

## Requisitos

- PHP 8.4
- Composer

## Instalación e inicio rápido

```bash
composer install
composer start
```

Abrir http://localhost:8000

Si el puerto 8000 está en uso, cámbialo en `composer.json` dentro de `scripts.start`.

## Estructura del proyecto

```
project-root/
├── app/
│   ├── commands/        # Comandos CLI (Runway)
│   ├── config/          # bootstrap.php, config.php, routes.php, services.php
│   ├── controllers/
│   ├── middlewares/
│   └── views/
├── public/              # Web root (index.php)
├── tests/               # PHPUnit tests
├── rector.php
├── phpstan.neon
├── .php-cs-fixer.php
└── composer.json
```

## Arranque de la aplicación

- Punto de entrada: `public/index.php`
- Bootstrap: `app/config/bootstrap.php`
- Configuración: `app/config/config.php` y `app/config/services.php`
- Rutas: `app/config/routes.php`

## Scripts y calidad (Composer)

```bash
composer run test:unit
composer run test:stan
composer run lint
composer run lint:fix
composer run rector:dry
composer run rector:fix
```

## Configuración de herramientas

- PHPUnit: `tests/phpunit/phpunit.xml`
- PHPStan: `phpstan.neon`
- PHP-CS-Fixer: `.php-cs-fixer.php`
- Rector: `rector.php`

## Namespaces y autoload

- El autoload usa PSR-4 con el prefijo `app\` apuntando al directorio `app/` (ver `composer.json`).
- Para mantener compatibilidad con la estructura actual de carpetas en minúsculas, los sub-namespaces se mantienen en minúsculas (por ejemplo `app\controllers\*`, `app\middlewares\*`, `app\commands\*`).

## Runway (CLI)

- Los comandos personalizados viven en `app/commands`.
- Ejemplo incluido: `app\commands\SampleDatabaseCommand` (inicializa una base SQLite de ejemplo).

## Formato y editor

- `.editorconfig` define LF, longitud máxima 140 y 2 espacios por defecto (PHP usa 4).
- `.vscode/settings.json` alinea la configuración de VS Code con esas reglas.

## Archivos generados

Algunas herramientas generan caches locales (resultados de PHPUnit, cache de la app, etc.). Estos archivos se ignoran vía `.gitignore`.
