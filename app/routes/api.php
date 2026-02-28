<?php

declare(strict_types=1);

use app\controllers\ApiExampleController;
use flight\net\Router;

/**
 * Rutas de API
 *
 * Este archivo define todas las rutas de la API de la aplicación.
 * Las rutas están prefijadas automáticamente por el grupo definido en el cargador de rutas.
 *
 * @var Router $router
 */

// Grupo de rutas para usuarios
$router->group('/users', function (Router $router): void {
    $router->get('', [ApiExampleController::class, 'getUsers']);
    $router->get('/@id:[0-9]+', [ApiExampleController::class, 'getUser']);
    $router->post('', [ApiExampleController::class, 'createUser']);
    $router->put('/@id:[0-9]+', [ApiExampleController::class, 'updateUser']);
    $router->delete('/@id:[0-9]+', [ApiExampleController::class, 'deleteUser']);
});
