<?php

declare(strict_types=1);

use flight\Engine;
use flight\database\PdoWrapper;
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
Debugger::$logDirectory = PROJECT_ROOT . $ds . 'app' . $ds . 'log';

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
$driver = $dbConfig['driver'] ?? 'sqlite';
if ($driver === 'pgsql') {
    $pdoDsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s',
        $dbConfig['host'] ?? '127.0.0.1',
        (int)($dbConfig['port'] ?? 5432),
        $dbConfig['database'] ?? 'app'
    );
    $pdoUser     = $dbConfig['username'] ?? null;
    $pdoPassword = $dbConfig['password'] ?? null;
} else {
    $pdoDsn      = 'sqlite:' . ($dbConfig['database'] ?? PROJECT_ROOT . '/database/database.sqlite');
    $pdoUser     = null;
    $pdoPassword = null;
}

$pdoClass = (IS_DEVELOPMENT && Debugger::$showBar && PHP_SAPI !== 'cli') ? PdoQueryCapture::class : PdoWrapper::class;
$app->register('db', $pdoClass, [$pdoDsn, $pdoUser, $pdoPassword]);
