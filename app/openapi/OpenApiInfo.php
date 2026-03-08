<?php

declare(strict_types=1);

namespace app\openapi;

use OpenApi\Attributes as OA;

/**
 * Clase auxiliar para centralizar la metadata del spec OpenAPI.
 *
 * No contiene lógica — solo atributos OA que swagger-php escanea al generar el spec.
 * Separada de los controladores para no mezclar configuración con lógica de negocio.
 */
#[OA\Info(version: '0.1.0-alpha', description: 'API REST construida con FlightPHP v3, Eloquent ORM y autenticación JWT.', title: 'FlightPHP REST Skeleton API', contact: new OA\Contact(
    name: 'FlightPHP Skeleton',
    url: 'https://github.com/gschz/flight-rest-skeleton'
))]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Servidor local de desarrollo'
)]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', description: 'Token JWT obtenido en POST /api/v1/auth/login. Enviar como: Authorization: Bearer {token}', bearerFormat: 'JWT', scheme: 'bearer')]
final class OpenApiInfo
{
    //
}
