<?php

declare(strict_types=1);

use app\middlewares\CorsMiddleware;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

/**
 * Configuración de Rutas
 *
 * Este archivo actúa como el punto de entrada para la definición de rutas.
 * Se encarga de agrupar y cargar las rutas desde archivos separados para
 * mantener una estructura limpia y escalable.
 *
 * @var Router $router
 * @var Engine<object> $app
 */

// Rutas Web (Interfaz, Health Checks, etc.)
$router->group('', function (Router $router): void {
    require __DIR__ . '/../routes/web.php';
});

// Rutas API (Versionadas, JSON, CORS, etc.)
$router->group('/api/v1', function (Router $router): void {
    require __DIR__ . '/../routes/api.php';
}, [
    CorsMiddleware::class,
    SecurityHeadersMiddleware::class,
]);
