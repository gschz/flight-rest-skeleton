<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\User;
use app\utils\ApiResponse;
use app\utils\JwtService;
use app\utils\Validator;
use flight\Engine;
use Illuminate\Database\Capsule\Manager as Capsule;
use OpenApi\Attributes as OA;
use Respect\Validation\Validator as v;
use Throwable;

/**
 * Controlador de autenticación JWT.
 *
 * Gestiona el login, refresco de token y logout.
 * Las rutas de este controlador son públicas — no requieren AuthMiddleware.
 */
#[OA\Tag(
    name: 'Auth',
    description: 'Autenticación JWT — login, refresh y logout'
)]
class AuthController
{
    private const string INTERNAL_ERROR_MSG = 'Error interno del servidor';

    /**
     * Constructor del controlador.
     *
     * @param Engine<object> $app Instancia del motor de Flight.
     */
    public function __construct(protected Engine $app)
    {
        //
    }

    /**
     * Autentica al usuario y devuelve access_token + refresh_token.
     *
     * POST /api/v1/auth/login
     * Body: { email: string, password: string }
     */
    #[OA\Post(path: '/api/v1/auth/login', summary: 'Inicia sesión y obtiene tokens JWT', requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    example: 'admin@example.com'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    format: 'password',
                    example: 'secret'
                ),
            ]
        )
    ), tags: ['Auth'], responses: [
        new OA\Response(
            response: 200,
            description: 'Login exitoso',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(
                                property: 'access_token',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'refresh_token',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'token_type',
                                type: 'string',
                                example: 'Bearer'
                            ),
                            new OA\Property(
                                property: 'expires_in',
                                type: 'integer',
                                example: 3600
                            ),
                        ],
                        type: 'object'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Credenciales incorrectas'
        ),
        new OA\Response(
            response: 422,
            description: 'Datos de entrada inválidos'
        ),
    ])]
    public function login(): void
    {
        $data = $this->app->request()->data->getData();

        $validationResult = Validator::validate(
            $data,
            [
                'email'    => v::email()->notEmpty(),
                'password' => v::stringType()->notEmpty(),
            ]
        );

        if (!$validationResult->isValid()) {
            ApiResponse::error(
                $this->app,
                'Datos de entrada inválidos',
                422,
                $validationResult->errors()
            );

            return;
        }

        /** @var string $email */
        $email    = $data['email'] ?? '';
        /** @var string $password */
        $password = $data['password'] ?? '';

        try {
            /** @var User|null $user */
            $user = User::where('email', $email)->first();
            if ($user === null) {
                ApiResponse::error(
                    $this->app,
                    'Credenciales incorrectas',
                    401
                );

                return;
            }

            $storedHash = $user->password ?? '';

            if ($storedHash === '' || !password_verify($password, $storedHash)) {
                ApiResponse::error(
                    $this->app,
                    'Credenciales incorrectas',
                    401
                );

                return;
            }

            $jwtService   = new JwtService();
            $payload      = $jwtService->generatePayload($user->id, 'user');
            $accessToken  = $jwtService->encode($payload);
            $refreshToken = $jwtService->generateRefreshToken();
            $tokenHash    = hash('sha256', $refreshToken);

            Capsule::table('refresh_tokens')->insert([
                'user_id'    => $user->id,
                'token'      => $tokenHash,
                'expires_at' => date(
                    'Y-m-d H:i:s',
                    time() + $jwtService->getRefreshTtl()
                ),
                'revoked'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            ApiResponse::success($this->app, [
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type'    => 'Bearer',
                'expires_in'    => $jwtService->getAccessTtl(),
            ]);
        } catch (Throwable $throwable) {
            error_log((string) $throwable);
            ApiResponse::error(
                $this->app,
                self::INTERNAL_ERROR_MSG,
                500
            );
        }
    }

    /**
     * Genera un nuevo access_token a partir de un refresh_token válido.
     *
     * POST /api/v1/auth/refresh
     * Body: { refresh_token: string }
     */
    #[OA\Post(path: '/api/v1/auth/refresh', summary: 'Renueva el access token usando un refresh token válido', requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['refresh_token'],
            properties: [
                new OA\Property(
                    property: 'refresh_token',
                    type: 'string',
                    example: 'abc123...'
                ),
            ]
        )
    ), tags: ['Auth'], responses: [
        new OA\Response(
            response: 200,
            description: 'Token renovado correctamente',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(
                                property: 'access_token',
                                type: 'string'
                            ),
                            new OA\Property(
                                property: 'token_type',
                                type: 'string',
                                example: 'Bearer'
                            ),
                            new OA\Property(
                                property: 'expires_in',
                                type: 'integer',
                                example: 3600
                            ),
                        ],
                        type: 'object'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Refresh token inválido o expirado'
        ),
        new OA\Response(
            response: 422,
            description: 'Datos de entrada inválidos'
        ),
    ])]
    public function refresh(): void
    {
        $data = $this->app->request()->data->getData();
        /** @var string $refreshToken */
        $refreshToken = $data['refresh_token'] ?? '';

        if ($refreshToken === '') {
            ApiResponse::error(
                $this->app,
                'refresh_token requerido',
                422
            );

            return;
        }

        try {
            $record = Capsule::table('refresh_tokens')
                ->where('token', hash('sha256', $refreshToken))
                ->first();

            if ($record === null) {
                ApiResponse::error(
                    $this->app,
                    'Token de refresco inválido',
                    401
                );

                return;
            }

            $revoked = (bool) $record->revoked;
            /** @var string $expiresAt */
            $expiresAt = $record->expires_at ?? '';
            /** @var int $userId */
            $userId    = $record->user_id ?? 0;

            $expiresTimestamp = strtotime($expiresAt);

            if (
                $revoked
                || $expiresTimestamp === false
                || $expiresTimestamp < time()
            ) {
                ApiResponse::error(
                    $this->app,
                    'Token de refresco expirado o revocado',
                    401
                );

                return;
            }

            $jwtService  = new JwtService();
            $payload     = $jwtService->generatePayload($userId, 'user');
            $accessToken = $jwtService->encode($payload);

            ApiResponse::success($this->app, [
                'access_token' => $accessToken,
                'token_type'   => 'Bearer',
                'expires_in'   => $jwtService->getAccessTtl(),
            ]);
        } catch (Throwable $throwable) {
            error_log((string) $throwable);
            ApiResponse::error(
                $this->app,
                self::INTERNAL_ERROR_MSG,
                500
            );
        }
    }

    /**
     * Revoca el refresh_token (logout).
     *
     * POST /api/v1/auth/logout
     * Body: { refresh_token: string }
     */
    #[OA\Post(path: '/api/v1/auth/logout', summary: 'Cierra sesión revocando el refresh token', requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['refresh_token'],
            properties: [
                new OA\Property(
                    property: 'refresh_token',
                    type: 'string',
                    example: 'abc123...'
                ),
            ]
        )
    ), tags: ['Auth'], responses: [
        new OA\Response(
            response: 200,
            description: 'Sesión cerrada correctamente'
        ),
        new OA\Response(
            response: 422,
            description: 'Datos de entrada inválidos'
        ),
    ])]
    public function logout(): void
    {
        $data = $this->app->request()->data->getData();
        /** @var string $refreshToken */
        $refreshToken = $data['refresh_token'] ?? '';

        if ($refreshToken === '') {
            ApiResponse::error(
                $this->app,
                'refresh_token requerido',
                422
            );

            return;
        }

        try {
            Capsule::table('refresh_tokens')
                ->where('token', hash('sha256', $refreshToken))
                ->where('revoked', 0)
                ->update(['revoked' => 1]);

            ApiResponse::success($this->app, [
                'message' => 'Sesión cerrada correctamente'
            ]);
        } catch (Throwable $throwable) {
            error_log((string) $throwable);
            ApiResponse::error(
                $this->app,
                self::INTERNAL_ERROR_MSG,
                500
            );
        }
    }
}
