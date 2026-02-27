<?php

declare(strict_types=1);

use app\controllers\ApiExampleController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

/**
 * @var Router $router
 * @var Engine<object> $app
 */

// This wraps all routes in the group with the SecurityHeadersMiddleware
$router->group('', function (Router $router) use ($app): void {

    $router->get('/', function () use ($app): void {
        $app->render('welcome', ['message' => 'You are gonna do great things!']);
    });

    $router->get('/hello-world/@name', function (string $name): void {
        echo '<h1>Hello world! Oh hey ' . $name . '!</h1>';
    });

    $router->group('/api', function () use ($router): void {
        $router->get('/users', [ApiExampleController::class, 'getUsers']);
        $router->get('/users/@id:[0-9]', [ApiExampleController::class, 'getUser']);
        $router->post('/users/@id:[0-9]', [ApiExampleController::class, 'updateUser']);
    });
}, [SecurityHeadersMiddleware::class]);
