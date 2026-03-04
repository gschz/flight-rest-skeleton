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
 *   RATE_LIMIT_ENABLED         — 'false' para deshabilitar (útil en dev/test)
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
            : (is_numeric($envMax)
                ? max(1, (int) $envMax)
                : self::DEFAULT_MAX
            );

        $this->windowSeconds = $windowSeconds > 0
            ? $windowSeconds
            : (is_numeric($envWindow)
                ? max(1, (int) $envWindow)
                : self::DEFAULT_WINDOW
            );
    }

    /**
     * Se ejecuta antes de cada request en las rutas donde está registrado.
     * Evalúa el contador de la IP y devuelve 429 si se superó el límite.
     *
     * @param array<int, mixed> $params
     */
    public function before(array $params): void
    {
        if (getenv('RATE_LIMIT_ENABLED') === 'false') {
            return;
        }

        $key = 'rl:' . $this->resolveIp();
        $now = time();

        $record      = Capsule::table('rate_limits')->where('key', $key)->first();
        $rawHits     = $record !== null ? $record->hits : 0;
        $rawWindow   = $record !== null ? $record->window_start : 0;
        $hits        = is_numeric($rawHits) ? (int) $rawHits : 0;
        $windowStart = is_numeric($rawWindow) ? (int) $rawWindow : 0;

        // Ventana expirada o primera visita: reiniciar a 1 hit y permitir el request
        if ($windowStart === 0 || ($now - $windowStart) >= $this->windowSeconds) {
            Capsule::table('rate_limits')->updateOrInsert(
                ['key' => $key],
                ['hits' => 1, 'window_start' => $now],
            );

            return;
        }

        // Límite alcanzado: rechazar con 429 y header Retry-After
        if ($hits >= $this->maxRequests) {
            $retryAfter = $this->windowSeconds - ($now - $windowStart);
            $this->app->response()->header('Retry-After', (string) max(1, $retryAfter));

            ApiResponse::error($this->app, 'Too many requests', 429);
            $this->app->stop();

            return;
        }

        // Dentro del límite: incrementar contador
        Capsule::table('rate_limits')->where('key', $key)->increment('hits');
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
