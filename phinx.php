<?php

declare(strict_types=1);

/**
 * Configuración de migraciones Phinx.
 *
 * Soporta SQLite (desarrollo local) y PostgreSQL (staging/producción).
 * DATABASE_URL tiene precedencia y auto-infiere el driver pgsql.
 *
 * @see .envs/.env.example            para SQLite local
 * @see .envs/.env.pg.example         para PostgreSQL local
 * @see .envs/.env.production.example para referencia de producción (Heroku/Railway/etc.)
 */

$projectRoot  = __DIR__;
$dbUrl        = (string)(getenv('DATABASE_URL') ?: '');
$dbConnection = (string)(getenv('DB_CONNECTION') ?: 'sqlite');

// DATABASE_URL (Heroku/Railway) tiene precedencia sobre las variables DB_* individuales
if ($dbUrl !== '') {
    $parsed       = parse_url($dbUrl);
    $dbConnection = 'pgsql';
    $pgHost       = (string)($parsed['host'] ?? '127.0.0.1');
    $pgPort       = (int)($parsed['port'] ?? 5432);
    $pgName       = ltrim((string)($parsed['path'] ?? 'app'), '/');
    $pgUser       = (string)($parsed['user'] ?? '');
    $pgPass       = (string)($parsed['pass'] ?? '');
} else {
    $pgHost = (string)(getenv('DB_HOST') ?: '127.0.0.1');
    $pgPort = (int)(getenv('DB_PORT') ?: 5432);
    $pgName = (string)(getenv('DB_DATABASE') ?: 'app');
    $pgUser = (string)(getenv('DB_USERNAME') ?: 'postgres');
    $pgPass = (string)(getenv('DB_PASSWORD') ?: '');
}

// Normaliza la ruta SQLite: Phinx requiere el path sin extensión
$sqlitePath = (string)(getenv('DB_DATABASE') ?: $projectRoot . '/database/database.sqlite3');
if (str_ends_with($sqlitePath, '.sqlite3')) {
    $sqlitePath = substr($sqlitePath, 0, -strlen('.sqlite3'));
} elseif (str_ends_with($sqlitePath, '.sqlite')) {
    $sqlitePath = substr($sqlitePath, 0, -strlen('.sqlite'));
}

$defaultEnv = ($dbConnection === 'pgsql') ? 'pgsql' : 'development';

return [
    'paths' => [
        'migrations' => $projectRoot . '/database/migrations',
        'seeds'      => $projectRoot . '/database/seeders',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => $defaultEnv,
        'development' => [
            'adapter' => 'sqlite',
            'name'    => $sqlitePath,
        ],
        // Entorno PostgreSQL: sirve tanto para desarrollo local (pgAdmin)
        // como para producción (Heroku/Railway/etc.). Las credenciales vienen de
        // DB_HOST/PORT/etc. en local, o de DATABASE_URL en producción.
        'pgsql' => [
            'adapter' => 'pgsql',
            'host'    => $pgHost,
            'port'    => $pgPort,
            'name'    => $pgName,
            'user'    => $pgUser,
            'pass'    => $pgPass,
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'sqlite',
            'name'    => ':memory:',
        ],
    ],
    'version_order' => 'creation',
];
