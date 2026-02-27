<?php

declare(strict_types=1);

use app\controllers\ApiExampleController;
use app\middlewares\CorsMiddleware;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

/**
 * @var Router $router
 * @var Engine<object> $app
 */

$router->group('/api/v1', function (Router $router) use ($app): void {
    $router->get('/users', [ApiExampleController::class, 'getUsers']);
    $router->get('/users/@id:[0-9]+', [ApiExampleController::class, 'getUser']);
    $router->post('/users', [ApiExampleController::class, 'createUser']);
    $router->put('/users/@id:[0-9]+', [ApiExampleController::class, 'updateUser']);
    $router->delete('/users/@id:[0-9]+', [ApiExampleController::class, 'deleteUser']);
}, [CorsMiddleware::class, SecurityHeadersMiddleware::class]);

// Health check
$router->get('/health', function () use ($app): void {
    $app->json(['status' => 'ok', 'timestamp' => date('c')]);
});
