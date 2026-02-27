<?php

declare(strict_types=1);

use flight\Engine;

/**
 * FlightPHP Sample Config
 *
 * Copy this file to config.php:
 *   cp app/config/config_sample.php app/config/config.php
 *
 * IMPORTANT: config.php is git-ignored. Never commit real credentials.
 * All sensitive values should be set as environment variables.
 * See .env.example for the list of required variables.
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

$app->set('flight.base_url', '/');
$app->set('flight.case_sensitive', false);
$app->set('flight.log_errors', true);
$app->set('flight.handle_errors', false);
$app->set('flight.content_length', false);

// Remove views settings since this is a pure REST API project
// $app->set('flight.views.path', PROJECT_ROOT . '/app/views');
// $app->set('flight.views.extension', '.php');

return [
    'runway' => [
        'index_root' => 'public/index.php',
        'app_root'   => 'app/',
    ],
];
