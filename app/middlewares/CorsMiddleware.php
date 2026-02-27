<?php

declare(strict_types=1);

namespace app\middlewares;

use flight\Engine;

class CorsMiddleware
{
    /** @param Engine<object> $app */
    public function __construct(protected Engine $app)
    {
        //
    }

    /** @param array<int, mixed> $params */
    public function before(array $params): void
    {
        $allowedOrigins = (string)(getenv('CORS_ALLOWED_ORIGINS') ?: '*');

        $this->app->response()->header('Access-Control-Allow-Origin', $allowedOrigins);
        $this->app->response()->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $this->app->response()->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->app->response()->header('Access-Control-Max-Age', '86400');

        // Handle preflight requests
        if ($this->app->request()->method === 'OPTIONS') {
            $this->app->response()->status(204)->send();
            exit;
        }
    }
}
