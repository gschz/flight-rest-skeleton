<?php

declare(strict_types=1);

use flight\Engine;

/**
 * FlightPHP Application Config
 *
 * This file is tracked in git. It contains NO credentials.
 * All sensitive/environment-specific values are read via getenv().
 *
 * Local development workflow:
 *   1. Copy .envs/.env.example  →  .envs/.env.local       (SQLite, default)
 *   2. Copy .envs/.env.pg.example → .envs/.env.pg.local   (PostgreSQL)
 *   3. Use the composer scripts to load the env and start the server:
 *        composer dev          →  loads .envs/.env.local,    starts on :8000
 *        composer dev:pg       →  loads .envs/.env.pg.local, starts on :8000
 *
 * On platforms like Heroku/Railway: set environment variables via the dashboard.
 * DATABASE_URL is automatically parsed when present.
 *
 * @see .envs/.env.example      for SQLite local setup
 * @see .envs/.env.pg.example   for PostgreSQL local setup
 */

date_default_timezone_set('UTC');
error_reporting(E_ALL);

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

/** @var Engine<object> $app */
if (!isset($app) || !$app instanceof Engine) {
    $app = Flight::app();
}

define('PROJECT_ROOT', realpath(__DIR__ . '/../..') ?: __DIR__ . '/../..');
$app->path(PROJECT_ROOT);

// Set view path
$app->set('flight.views.path', PROJECT_ROOT . '/app/views');

$appEnv = (string)(getenv('APP_ENV') ?: 'development');
define('APP_ENV', $appEnv);
define('IS_PRODUCTION', $appEnv === 'production');
define('IS_DEVELOPMENT', $appEnv === 'development');
define('IS_TESTING', $appEnv === 'testing');

$app->set('flight.base_url', '/');
$app->set('flight.case_sensitive', false);
$app->set('flight.log_errors', true);
$app->set('flight.handle_errors', IS_PRODUCTION);
$app->set('flight.content_length', false);

/**
 * Database configuration — reads from environment variables.
 *
 * Multi-environment strategy:
 *   - DB_CONNECTION=sqlite  → local development (fast, no server needed)
 *   - DB_CONNECTION=pgsql   → staging / production (PostgreSQL)
 *
 * For SQLite: set DB_DATABASE to the path of the .sqlite3 file, or leave
 *   empty to default to database/database.sqlite3
 * For PostgreSQL: set DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
 *   or set DATABASE_URL with a full DSN (e.g. Heroku DATABASE_URL).
 */
$dbConnection = (string)(getenv('DB_CONNECTION') ?: 'sqlite');

// Soporte para DATABASE_URL estilo Heroku/Railway — tiene precedencia y auto-infiere pgsql
$dbUrl = (string)(getenv('DATABASE_URL') ?: '');
if ($dbUrl !== '') {
    $dbConnection = 'pgsql';
    $parsed      = parse_url($dbUrl) ?: [];
    $dbConfig    = [
        'driver'   => 'pgsql',
        'host'     => (string)($parsed['host'] ?? '127.0.0.1'),
        'port'     => (int)($parsed['port'] ?? 5432),
        'database' => ltrim((string)($parsed['path'] ?? 'app'), '/'),
        'username' => (string)($parsed['user'] ?? ''),
        'password' => (string)($parsed['pass'] ?? ''),
        'charset'  => 'utf8',
        'sslmode'  => 'require',
    ];
} elseif ($dbConnection === 'pgsql') {
    $dbConfig = [
        'driver'   => 'pgsql',
        'host'     => (string)(getenv('DB_HOST') ?: '127.0.0.1'),
        'port'     => (int)(getenv('DB_PORT') ?: 5432),
        'database' => (string)(getenv('DB_DATABASE') ?: 'app'),
        'username' => (string)(getenv('DB_USERNAME') ?: 'postgres'),
        'password' => (string)(getenv('DB_PASSWORD') ?: ''),
        'charset'  => 'utf8',
        'sslmode'  => (string)(getenv('DB_SSLMODE') ?: 'prefer'),
    ];
} else {
    // SQLite (default for local development)
    $sqlitePath = (string)(getenv('DB_DATABASE') ?: 'database/database.sqlite3');
    if (
        !str_contains($sqlitePath, '/')
        && !str_contains($sqlitePath, '\\')
        && $sqlitePath !== ':memory:'
    ) {
        $sqlitePath = PROJECT_ROOT . '/database/' . $sqlitePath;
    } elseif ($sqlitePath !== ':memory:' && !file_exists($sqlitePath)) {
        // Try to resolve relative path from PROJECT_ROOT
        $absolutePath = PROJECT_ROOT . '/' . ltrim($sqlitePath, '/\\');
        if (file_exists($absolutePath)) {
            $sqlitePath = $absolutePath;
        }
    }

    $dbConfig = [
        'driver'   => 'sqlite',
        'database' => $sqlitePath,
        'prefix'   => '',
        'foreign_key_constraints' => true,
    ];
}

return [
    'app' => [
        'env'   => $appEnv,
        'debug' => filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN),
        'key'   => (string)(getenv('APP_KEY') ?: ''),
    ],
    'database' => array_merge(['connection' => $dbConnection], $dbConfig),
    'runway' => [
        'index_root' => 'public/index.php',
        'app_root'   => 'app/',
    ],
];
