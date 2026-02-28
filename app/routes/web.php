<?php

declare(strict_types=1);

use flight\net\Router;

/**
 * Rutas Web
 *
 * Este archivo define las rutas web generales de la aplicación.
 *
 * @var Router $router
 */

$router->get('/', function (): void {
    $app = Flight::app();
    $app->render('welcome', ['title' => 'Bienvenido a FlightPHP']);
});

$router->get('/health', function (): void {
    Flight::json([
        'status' => 'ok',
        'message' => 'Servicio operativo',
        'timestamp' => date('c'),
    ]);
});
