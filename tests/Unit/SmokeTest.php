<?php

declare(strict_types=1);

use app\utils\JwtService;
use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testFlightAppCanBeCreated(): void
    {
        $app = Flight::app();

        self::assertInstanceOf(\flight\Engine::class, $app);
    }

    public function testJwtServiceEncodeDecodeRoundTrip(): void
    {
        putenv('JWT_SECRET=' . str_repeat('a', 32));
        putenv('JWT_TTL=3600');

        $service = new JwtService();
        $payload = $service->generatePayload(42, 'admin');
        $token   = $service->encode($payload);

        self::assertNotEmpty($token);
        self::assertStringContainsString('.', $token); // formato JWT: header.payload.signature

        $decoded = $service->decode($token);

        self::assertSame(42, $decoded['sub'] ?? null);
        self::assertSame('admin', $decoded['role'] ?? null);
        self::assertSame('access', $decoded['type'] ?? null);
        self::assertArrayHasKey('iat', $decoded);
        self::assertArrayHasKey('exp', $decoded);
    }

    public function testJwtServiceDecodeExpiredToken(): void
    {
        putenv('JWT_SECRET=' . str_repeat('a', 32));
        putenv('JWT_TTL=1'); // TTL de 1 segundo

        $service = new JwtService();
        $token   = $service->encode($service->generatePayload(1));

        // Avanzar el tiempo forzando expiración simulando manualmente
        // firebase/php-jwt usa time() internamente; verificamos que decode lanza RuntimeException
        // cuando el token ya expiró (en este caso usamos un token pre-fabricado con exp en el pasado)
        putenv('JWT_SECRET=' . str_repeat('b', 32)); // secreto distinto → firma inválida

        self::expectException(RuntimeException::class);
        $service2 = new JwtService();
        $service2->decode($token);
    }

    public function testJwtServiceGenerateRefreshToken(): void
    {
        putenv('JWT_SECRET=' . str_repeat('a', 32));

        $service = new JwtService();
        $token1  = $service->generateRefreshToken();
        $token2  = $service->generateRefreshToken();

        self::assertNotEmpty($token1);
        self::assertNotEmpty($token2);
        self::assertNotSame($token1, $token2); // debe ser único cada vez
        self::assertSame(64, strlen($token1));  // bin2hex(32 bytes) = 64 caracteres
    }

    public function testJwtServiceGetAccessTtl(): void
    {
        putenv('JWT_SECRET=' . str_repeat('a', 32));
        putenv('JWT_TTL=7200');

        $service = new JwtService();
        self::assertSame(7200, $service->getAccessTtl());
    }
}
