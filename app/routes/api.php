<?php

declare(strict_types=1);

use app\controllers\ApiExampleController;
use app\controllers\AuthController;
use app\middlewares\AuthMiddleware;
use flight\net\Router;

/**
 * Rutas de API
 *
 * Este archivo define todas las rutas de la API de la aplicación.
 * Las rutas están prefijadas automáticamente por el grupo definido en el cargador de rutas.
 *
 * @var Router $router
 */

// Rutas públicas de autenticación (no requieren JWT)
$router->group('/auth', function (Router $router): void {
    $router->post(
        '/login',
        [AuthController::class, 'login']
    );
    $router->post(
        '/refresh',
        [AuthController::class, 'refresh']
    );
    $router->post(
        '/logout',
        [AuthController::class, 'logout']
    );
});

// Rutas protegidas — requieren JWT válido en header Authorization: Bearer {token}
$router->group('', function (Router $router): void {
    $router->group('/users', function (Router $router): void {
        $router->get(
            '',
            [ApiExampleController::class, 'getUsers']
        );
        $router->get(
            '/@id:[0-9]+',
            [ApiExampleController::class, 'getUser']
        );
        $router->post(
            '',
            [ApiExampleController::class, 'createUser']
        );
        $router->put(
            '/@id:[0-9]+',
            [ApiExampleController::class, 'updateUser']
        );
        $router->delete(
            '/@id:[0-9]+',
            [ApiExampleController::class, 'deleteUser']
        );
    });
}, [AuthMiddleware::class]);
