<?php

declare(strict_types=1);

namespace app\middlewares;

use flight\Engine;

class SecurityHeadersMiddleware
{
    /** @param Engine<object> $app */
    public function __construct(protected Engine $app)
    {
        //
    }

    /** @param array<int, mixed> $params */
    public function before(array $params): void
    {
        $this->app->response()->header('X-Content-Type-Options', 'nosniff');
        $this->app->response()->header('X-Frame-Options', 'DENY');
        $this->app->response()->header('X-XSS-Protection', '1; mode=block');
        $this->app->response()->header('Referrer-Policy', 'no-referrer');
        $this->app->response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $this->app->response()->header('Permissions-Policy', 'geolocation=()');
        // For REST APIs there's no document-level CSP needed,
        // but we still disallow framing and inline scripts to harden any error pages.
        $this->app->response()->header('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'");
    }
}
