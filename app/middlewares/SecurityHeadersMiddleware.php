<?php

declare(strict_types=1);

namespace app\middlewares;

use flight\Engine;
use Tracy\Debugger;

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
        $nonceValue = $this->app->get('csp_nonce');
        if (!is_string($nonceValue)) {
            $nonceValue = bin2hex(random_bytes(16));
            $this->app->set('csp_nonce', $nonceValue);
        }

        $nonce = $nonceValue;

        // development mode to execute Tracy debug bar CSS
        $tracyCssBypass = sprintf("'nonce-%s'", $nonce);
        if (Debugger::$showBar) {
            $tracyCssBypass = " 'unsafe-inline'";
        }

        $csp = sprintf("default-src 'self'; script-src 'self' 'nonce-%s' 'strict-dynamic'; style-src 'self' %s; img-src 'self' data:;", $nonce, $tracyCssBypass);
        $this->app->response()->header('X-Frame-Options', 'SAMEORIGIN');
        $this->app->response()->header("Content-Security-Policy", $csp);
        $this->app->response()->header('X-XSS-Protection', '1; mode=block');
        $this->app->response()->header('X-Content-Type-Options', 'nosniff');
        $this->app->response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
        $this->app->response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $this->app->response()->header('Permissions-Policy', 'geolocation=()');
    }
}
