<?php

declare(strict_types=1);

use flight\Engine;

/**
 * FlightPHP Config
 *
 * This file reads all sensitive values from environment variables.
 * Set them in your shell, in a .env file loaded externally, or via
 * your platform's config panel (Heroku, Railway, etc.).
 *
 * For local development, create a .envs/.env.local file and load it
 * before starting the server (see composer scripts).
 *
 * DO NOT hardcode credentials here. This file is git-ignored.
 * See config_sample.php for the template and .env.example for variables.
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

define('PROJECT_ROOT', __DIR__ . '/../..');
$app->path(PROJECT_ROOT);

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
 * For SQLite: set DB_DATABASE to the path of the .sqlite file, or leave
 *   empty to default to database/database.sqlite
 * For PostgreSQL: set DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
 *   or set DB_URL with a full DSN (e.g. Heroku DATABASE_URL).
 */
$dbConnection = (string)(getenv('DB_CONNECTION') ?: 'sqlite');

// Support Heroku-style DATABASE_URL
$dbUrl = (string)(getenv('DATABASE_URL') ?: '');
if ($dbUrl !== '' && $dbConnection === 'pgsql') {
    $parsed      = parse_url($dbUrl);
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
    $dbConfig = [
        'driver'   => 'sqlite',
        'database' => (string)(getenv('DB_DATABASE') ?: PROJECT_ROOT . '/database/database.sqlite'),
        'prefix'   => '',
        'foreign_key_constraints' => true,
    ];
}

return [
    'app' => [
        'env'   => $appEnv,
        'debug' => (bool)(getenv('APP_DEBUG') ?: !IS_PRODUCTION),
        'key'   => (string)(getenv('APP_KEY') ?: ''),
    ],
    'database' => array_merge(['connection' => $dbConnection], $dbConfig),
    'runway' => [
        'index_root' => 'public/index.php',
        'app_root'   => 'app/',
    ],
];
