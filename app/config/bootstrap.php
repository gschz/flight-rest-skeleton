<?php

declare(strict_types=1);

/**
 * Bootstrap File
 *
 * Loads the autoloader, configuration, services, and routes,
 * then starts the Flight application.
 *
 * Environment variables are loaded externally before this file runs.
 * For local development, use: composer dev  (loads .envs/.env.local)
 */
$ds = DIRECTORY_SEPARATOR;
require __DIR__ . $ds . '..' . $ds . '..' . $ds . 'vendor' . $ds . 'autoload.php';

// config.php is tracked in git — it should always exist.
// If missing, it means the repo was cloned incorrectly.
if (file_exists(__DIR__ . $ds . 'config.php') === false) {
    Flight::halt(500, 'Config file not found. This should not happen — please check your installation.');
}

$app    = Flight::app();
$config = require __DIR__ . '/config.php';

require __DIR__ . '/../utils/helpers.php';
require __DIR__ . '/services.php';

$router = $app->router();
require __DIR__ . '/routes.php';

$app->start();
