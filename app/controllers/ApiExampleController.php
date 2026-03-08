<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\User;
use app\utils\ApiResponse;
use app\utils\Validator;
use flight\Engine;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Attributes as OA;
use Respect\Validation\Validator as v;
use Throwable;

/**
 * Controlador de Ejemplo para la API.
 *
 * Este controlador gestiona las operaciones CRUD para el recurso Usuario.
 * Sirve como referencia para la implementación de controladores RESTful
 * utilizando Eloquent ORM y respuestas estandarizadas.
 */
#[OA\Tag(
    name: 'Users',
    description: 'Gestión de usuarios (requiere JWT)'
)]
class ApiExampleController
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
     * Obtiene la lista paginada de usuarios.
     *
     * Acepta parámetros de query string: `page` (default 1) y `per_page` (default 15, máximo 100).
     */
    #[OA\Get(
        path: '/api/v1/users',
        summary: 'Lista paginada de usuarios',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, maximum: 100)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de usuarios paginada'
            ),
            new OA\Response(
                response: 401,
                description: 'Token JWT ausente o inválido'
            ),
        ]
    )]
    public function getUsers(): void
    {
        try {
            $rawPage    = $this->app->request()->query['page'];
            $rawPerPage = $this->app->request()->query['per_page'];
            $page    = max(1, is_numeric($rawPage) ? (int) $rawPage : 1);
            $perPage = min(
                100,
                max(1, is_numeric($rawPerPage)
                    ? (int) $rawPerPage
                    : 15)
            );

            $paginator = User::paginate($perPage, ['*'], 'page', $page);
            ApiResponse::fromPaginator($this->app, $paginator);
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
     * Obtiene un usuario específico por su ID.
     *
     * @param int $id El identificador único del usuario.
     */
    #[OA\Get(
        path: '/api/v1/users/{id}',
        summary: 'Obtiene un usuario por ID',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Datos del usuario'
            ),
            new OA\Response(
                response: 401,
                description: 'Token JWT ausente o inválido'
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado'
            ),
        ]
    )]
    public function getUser(int $id): void
    {
        try {
            $user = User::findOrFail($id);
            ApiResponse::success($this->app, $user);
        } catch (ModelNotFoundException) {
            ApiResponse::error(
                $this->app,
                'Usuario no encontrado',
                404
            );
        } catch (Throwable $e) {
            error_log((string) $e);
            ApiResponse::error(
                $this->app,
                self::INTERNAL_ERROR_MSG,
                500
            );
        }
    }

    /**
     * Crea un nuevo usuario.
     *
     * Valida los datos de entrada y crea un nuevo registro en la base de datos.
     */
    #[OA\Post(path: '/api/v1/users', summary: 'Crea un nuevo usuario', security: [['bearerAuth' => []]], requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email'],
            properties: [
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Juan Pérez',
                    maxLength: 100
                ),
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    example: 'juan@example.com'
                ),
            ]
        )
    ), tags: ['Users'], responses: [
        new OA\Response(
            response: 201,
            description: 'Usuario creado correctamente'
        ),
        new OA\Response(
            response: 401,
            description: 'Token JWT ausente o inválido'
        ),
        new OA\Response(
            response: 409,
            description: 'Email ya registrado'
        ),
        new OA\Response(
            response: 422,
            description: 'Datos de entrada inválidos'
        ),
    ])]
    public function createUser(): void
    {
        $data = $this->app->request()->data->getData();

        $validationResult = Validator::validate(
            $data,
            [
                'name'  => v::stringType()->notEmpty()->length(1, 100),
                'email' => v::email()->notEmpty(),
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

        try {
            // Verificar que el email no esté registrado antes de insertar
            if (User::where('email', $data['email'])->exists()) {
                ApiResponse::error(
                    $this->app,
                    'El correo electrónico ya está registrado',
                    409
                );

                return;
            }

            // Solo insertar los campos validados — evitar mass assignment no controlado
            $user = User::create([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);
            ApiResponse::success($this->app, $user, 201);
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
     * Actualiza un usuario existente.
     *
     * @param int $id El identificador único del usuario a actualizar.
     */
    #[OA\Put(path: '/api/v1/users/{id}', summary: 'Actualiza un usuario existente', security: [['bearerAuth' => []]], requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email'],
            properties: [
                new OA\Property(
                    property: 'name',
                    type: 'string',
                    example: 'Juan Pérez',
                    maxLength: 60
                ),
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    example: 'juan@example.com'
                ),
            ]
        )
    ), tags: ['Users'], parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        ),
    ], responses: [
        new OA\Response(
            response: 200,
            description: 'Usuario actualizado correctamente'
        ),
        new OA\Response(
            response: 401,
            description: 'Token JWT ausente o inválido'
        ),
        new OA\Response(
            response: 404,
            description: 'Usuario no encontrado'
        ),
        new OA\Response(
            response: 422,
            description: 'Datos de entrada inválidos'
        ),
    ])]
    public function updateUser(int $id): void
    {
        try {
            $user = User::findOrFail($id);
            $data = $this->app->request()->data->getData();

            $validationResult = Validator::validate(
                $data,
                [
                    'name'  => v::stringType()->notEmpty()->length(1, 60),
                    'email' => v::email()->notEmpty(),
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

            // Solo actualizar los campos validados — evitar mass assignment no controlado
            $user->update([
                'name'  => $data['name'],
                'email' => $data['email'],
            ]);

            ApiResponse::success($this->app, $user);
        } catch (ModelNotFoundException) {
            ApiResponse::error(
                $this->app,
                'Usuario no encontrado',
                404
            );
        } catch (Throwable $e) {
            error_log((string) $e);
            ApiResponse::error(
                $this->app,
                self::INTERNAL_ERROR_MSG,
                500
            );
        }
    }

    /**
     * Elimina un usuario existente.
     *
     * @param int $id El identificador único del usuario a eliminar.
     */
    #[OA\Delete(
        path: '/api/v1/users/{id}',
        summary: 'Elimina un usuario por ID',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario eliminado correctamente'
            ),
            new OA\Response(
                response: 401,
                description: 'Token JWT ausente o inválido'
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado'
            ),
        ]
    )]
    public function deleteUser(int $id): void
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            ApiResponse::success($this->app, [
                'deleted' => true,
                'id' => $id
            ]);
        } catch (ModelNotFoundException) {
            ApiResponse::error(
                $this->app,
                'Usuario no encontrado',
                404
            );
        } catch (Throwable $e) {
            error_log((string) $e);
            ApiResponse::error(
                $this->app,
                self::INTERNAL_ERROR_MSG,
                500
            );
        }
    }
}
