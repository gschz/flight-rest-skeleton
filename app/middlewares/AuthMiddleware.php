<?php

declare(strict_types=1);

namespace app\middlewares;

use app\utils\ApiResponse;
use app\utils\JwtService;
use flight\Engine;
use RuntimeException;

/**
 * Middleware de autenticación JWT para FlightPHP v3.
 *
 * Extrae y valida el token Bearer del header Authorization.
 * En caso de éxito, almacena el payload en el motor de Flight
 * para uso posterior en los controladores protegidos.
 *
 * Uso: registrar en el grupo de rutas protegidas como middleware.
 * Las rutas públicas (login, refresh) deben quedar fuera de este grupo.
 */
final readonly class AuthMiddleware
{
    /** @param Engine<object> $engine */
    public function __construct(private Engine $engine)
    {
        //
    }

    public function before(): void
    {
        $rawHeader  = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $authHeader = is_string($rawHeader) ? $rawHeader : '';
        if ($authHeader === '' || !str_starts_with($authHeader, 'Bearer ')) {
            ApiResponse::error(
                $this->engine,
                'Token de autenticación requerido',
                401
            );
            $this->engine->stop();

            return;
        }

        $token = substr($authHeader, 7);
        try {
            $jwtService = new JwtService();
            $payload    = $jwtService->decode($token);

            $this->engine->set('jwt_payload', $payload);
            $subRaw = $payload['sub'] ?? 0;
            $this->engine->set('auth_user_id', is_int($subRaw) ? $subRaw : 0);
        } catch (RuntimeException $runtimeException) {
            ApiResponse::error(
                $this->engine,
                $runtimeException->getMessage(),
                401
            );
            $this->engine->stop();
        }
    }
}
