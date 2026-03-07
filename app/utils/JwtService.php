<?php

declare(strict_types=1);

namespace app\utils;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

/**
 * Servicio JWT para codificación y decodificación de tokens de acceso.
 *
 * Wrapper tipado sobre firebase/php-jwt compatible con PHPStan level max.
 * Lee la configuración desde variables de entorno.
 *
 * Configuración via env:
 *   JWT_SECRET      — clave secreta de firma (requerido, mínimo 32 bytes)
 *   JWT_TTL         — TTL del access token en segundos (default: 3600)
 *   JWT_REFRESH_TTL — TTL del refresh token en segundos (default: 604800)
 */
final readonly class JwtService
{
    private const string ALGORITHM = 'HS256';

    private const int DEFAULT_TTL = 3600;

    private const int DEFAULT_REFRESH_TTL = 604800;

    private string $secret;

    private int $ttl;

    private int $refreshTtl;

    /**
     * @throws RuntimeException si JWT_SECRET no está configurado
     */
    public function __construct()
    {
        $secret = getenv('JWT_SECRET');
        if ($secret === false || $secret === '') {
            throw new RuntimeException('JWT_SECRET no está configurado');
        }

        $this->secret = $secret;

        $envTtl    = getenv('JWT_TTL');
        $this->ttl = is_numeric($envTtl)
            ? max(1, (int) $envTtl)
            : self::DEFAULT_TTL;

        $envRefreshTtl    = getenv('JWT_REFRESH_TTL');
        $this->refreshTtl = is_numeric($envRefreshTtl)
            ? max(1, (int) $envRefreshTtl)
            : self::DEFAULT_REFRESH_TTL;
    }

    /**
     * Codifica un array en un JWT firmado con HS256.
     * Añade automáticamente los claims estándar iat y exp.
     *
     * @param array<string, mixed> $payload
     */
    public function encode(array $payload): string
    {
        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $this->ttl;

        return JWT::encode($payload, $this->secret, self::ALGORITHM);
    }

    /**
     * Decodifica y valida un JWT.
     * Lanza RuntimeException si el token es inválido, ha expirado o la firma no coincide.
     *
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    public function decode(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key($this->secret, self::ALGORITHM)
            );
            /** @var array<string, mixed> $result */
            $result = (array) $decoded;

            return $result;
        } catch (ExpiredException $e) {
            throw new RuntimeException('Token expirado', 0, $e);
        } catch (\Exception $e) {
            throw new RuntimeException('Token inválido', 0, $e);
        }
    }

    /**
     * Genera el payload estándar para un access token.
     *
     * @return array<string, mixed>
     */
    public function generatePayload(int $userId, string $role = 'user'): array
    {
        return [
            'sub'  => $userId,
            'role' => $role,
            'type' => 'access',
        ];
    }

    /**
     * Genera un refresh token opaco usando bytes aleatorios criptográficamente seguros.
     */
    public function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Devuelve el TTL del access token en segundos.
     */
    public function getAccessTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Devuelve el TTL del refresh token en segundos.
     */
    public function getRefreshTtl(): int
    {
        return $this->refreshTtl;
    }
}
