<?php

declare(strict_types=1);

use flight\Engine;
use flight\database\SimplePdo;
use flight\debug\database\PdoQueryCapture;
use flight\debug\tracy\TracyExtensionLoader;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Tracy\Debugger;

$ds = DIRECTORY_SEPARATOR;

/** @var Engine<object> $app */
if (!isset($app) || !$app instanceof Engine) {
    $app = Flight::app();
}

/** @var array<string, mixed> $config */
$config = is_array($config ?? null) ? $config : [];

// ── Tracy Debugger ────────────────────────────────────────────────────────────
if (IS_DEVELOPMENT && PHP_SAPI !== 'cli') {
    Debugger::enable(Debugger::Development);
    Debugger::$strictMode = E_ALL & ~E_DEPRECATED;
    if (Debugger::$showBar) {
        (new TracyExtensionLoader($app));
    }
} else {
    Debugger::enable(Debugger::Production);
}

Debugger::$logDirectory = base_path('app' . $ds . 'log');

// ── Eloquent ORM ──────────────────────────────────────────────────────────────
/** @var array<string, mixed> $dbConfig */
$dbConfig = is_array($config['database'] ?? null) ? $config['database'] : [];

$capsule = new Capsule();
$capsule->addConnection($dbConfig);
$capsule->setEventDispatcher(new Dispatcher());
$capsule->setAsGlobal();
$capsule->bootEloquent();

// ── Flight PdoWrapper (for raw queries via Flight::db()) ──────────────────────
// Build a PDO DSN from the same config so Flight::db() still works alongside Eloquent.
$driver = is_string($dbConfig['driver'] ?? null)
    ? $dbConfig['driver']
    : 'sqlite';
if ($driver === 'pgsql') {
    $host = is_string($dbConfig['host'] ?? null)
        ? $dbConfig['host']
        : '127.0.0.1';
    $portValue = $dbConfig['port'] ?? 5432;
    if (is_int($portValue)) {
        $port = $portValue;
    } elseif (is_string($portValue) && ctype_digit($portValue)) {
        $port = (int)$portValue;
    } else {
        $port = 5432;
    }

    $databaseName = is_string($dbConfig['database'] ?? null)
        ? $dbConfig['database']
        : 'app';

    $pdoDsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s',
        $host,
        $port,
        $databaseName
    );
    $pdoUser     = is_string($dbConfig['username'] ?? null)
        ? $dbConfig['username']
        : null;
    $pdoPassword = is_string($dbConfig['password'] ?? null)
        ? $dbConfig['password']
        : null;
} else {
    $sqliteDatabase = $dbConfig['database'] ?? base_path('database/database.sqlite3');
    $sqliteDatabase = is_string($sqliteDatabase)
        ? $sqliteDatabase
        : base_path('database/database.sqlite3');

    $pdoDsn      = 'sqlite:' . $sqliteDatabase;
    $pdoUser     = null;
    $pdoPassword = null;
}

$pdoClass = (IS_DEVELOPMENT && Debugger::$showBar && PHP_SAPI !== 'cli')
    ? PdoQueryCapture::class
    : SimplePdo::class;

$app->register('db', $pdoClass, [$pdoDsn, $pdoUser, $pdoPassword]);
