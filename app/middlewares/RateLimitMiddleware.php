<?php

declare(strict_types=1);

namespace app\middlewares;

use app\utils\ApiResponse;
use flight\Engine;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Middleware de Rate Limiting basado en tabla DB (compatible con SQLite y PostgreSQL).
 *
 * Estrategia: ventana fija. Cada IP tiene un registro en `rate_limits` con
 * su contador de hits y el timestamp de inicio de la ventana actual.
 *
 * Configuración via env:
 *   RATE_LIMIT_ENABLED         — 'true' para habilitar (default: deshabilitado si no se define)
 *   RATE_LIMIT_MAX_REQUESTS    — hits máximos por ventana (default: 60)
 *   RATE_LIMIT_WINDOW_SECONDS  — duración de la ventana en segundos (default: 60)
 */
class RateLimitMiddleware
{
    private const int DEFAULT_MAX    = 60;

    private const int DEFAULT_WINDOW = 60;

    private readonly int $maxRequests;

    private readonly int $windowSeconds;

    /**
     * @param Engine<object> $app
     * @param int $maxRequests    Límite de requests por ventana (0 = leer de env o usar default)
     * @param int $windowSeconds  Duración de la ventana en segundos (0 = leer de env o usar default)
     */
    public function __construct(
        protected Engine $app,
        int $maxRequests = 0,
        int $windowSeconds = 0,
    ) {
        $envMax    = getenv('RATE_LIMIT_MAX_REQUESTS');
        $envWindow = getenv('RATE_LIMIT_WINDOW_SECONDS');

        $this->maxRequests   = $maxRequests > 0
            ? $maxRequests
            : (
                is_numeric($envMax)
                ? max(1, (int) $envMax)
                : self::DEFAULT_MAX
            );

        $this->windowSeconds = $windowSeconds > 0
            ? $windowSeconds
            : (
                is_numeric($envWindow)
                ? max(1, (int) $envWindow)
                : self::DEFAULT_WINDOW
            );
    }

    /**
     * Se ejecuta antes de cada request en las rutas donde está registrado.
     * Evalúa el contador de la IP dentro de una transacción atómica.
     * En caso de error de DB, aplica fail-open (permite el request y loguea).
     *
     * @param array<int, mixed> $params
     */
    public function before(array $params): void
    {
        if ($this->isDisabled()) {
            return;
        }

        $key = 'rl:' . $this->resolveIp();
        $now = time();

        try {
            $retryAfter = $this->checkAndIncrement($key, $now);
        } catch (\Throwable $throwable) {
            // Fail-open: error de DB no debe tumbar la API
            error_log('[RateLimitMiddleware] DB error — fail-open: ' . $throwable->getMessage());

            return;
        }

        if ($retryAfter > 0) {
            $this->app->response()->header('Retry-After', (string) $retryAfter);
            ApiResponse::error(
                $this->app,
                'Too many requests',
                429
            );
            $this->app->stop();
        }
    }

    /**
     * Evalúa y actualiza el contador de forma atómica dentro de una transacción DB.
     * Usa lockForUpdate() en PostgreSQL para evitar race conditions.
     * En SQLite el lock es ignorado (la transacción serializa igualmente).
     *
     * @return int 0 si el request está permitido, segundos de espera si está bloqueado
     */
    private function checkAndIncrement(string $key, int $now): int
    {
        return Capsule::connection()->transaction(function () use ($key, $now): int {
            $record = Capsule::table('rate_limits')
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            $hits        = is_numeric($record?->hits) ? (int) $record->hits : 0;
            $windowStart = is_numeric($record?->window_start) ? (int) $record->window_start : 0;

            // Ventana expirada o primera visita: reiniciar a 1 hit y permitir el request
            if ($windowStart === 0 || ($now - $windowStart) >= $this->windowSeconds) {
                Capsule::table('rate_limits')->updateOrInsert(
                    ['key' => $key],
                    ['hits' => 1, 'window_start' => $now],
                );

                return 0;
            }

            // Límite alcanzado: devolver segundos de espera
            if ($hits >= $this->maxRequests) {
                return max(1, $this->windowSeconds - ($now - $windowStart));
            }

            // Dentro del límite: incrementar contador de forma atómica
            Capsule::table('rate_limits')->where('key', $key)->increment('hits');

            return 0;
        });
    }

    /**
     * Normaliza el valor de RATE_LIMIT_ENABLED.
     * Si la variable no está definida o tiene un valor falsy común, deshabilita el middleware.
     */
    private function isDisabled(): bool
    {
        $env = getenv('RATE_LIMIT_ENABLED');

        if ($env === false) {
            return true;
        }

        return in_array(
            strtolower(trim((string) $env)),
            ['', '0', 'false', 'off', 'no'],
            true
        );
    }

    /**
     * Resuelve la IP del cliente desde la request de Flight con fallback a REMOTE_ADDR.
     */
    private function resolveIp(): string
    {
        $ip = $this->app->request()->ip;
        if ($ip !== '') {
            return $ip;
        }

        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

        return is_string($remoteAddr) && $remoteAddr !== '' ? $remoteAddr : 'unknown';
    }
}
