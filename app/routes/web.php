<?php

declare(strict_types=1);

use app\controllers\DocsController;
use app\controllers\WelcomeController;
use flight\net\Router;

/**
 * Rutas Web
 *
 * Este archivo define las rutas web generales de la aplicación.
 *
 * @var Router $router
 */

$router->get('/', [WelcomeController::class, 'index']);

$router->get('/health', function (): void {
    Flight::json([
        'status' => 'ok',
        'message' => 'Servicio operativo',
        'timestamp' => date('c'),
    ]);
});

// Documentación OpenAPI — solo disponible fuera de producción
if (!(defined('IS_PRODUCTION') && IS_PRODUCTION)) {
    $router->get(
        '/api-docs/openapi.json',
        [DocsController::class, 'spec']
    );
    $router->get(
        '/docs',
        [DocsController::class, 'swagger']
    );
    $router->get(
        '/docs/redoc',
        [DocsController::class, 'redoc']
    );
}
