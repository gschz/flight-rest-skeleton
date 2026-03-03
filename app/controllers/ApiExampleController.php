<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\User;
use app\utils\ApiResponse;
use app\utils\Validator;
use flight\Engine;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Respect\Validation\Validator as v;
use Throwable;

/**
 * Controlador de Ejemplo para la API.
 *
 * Este controlador gestiona las operaciones CRUD para el recurso Usuario.
 * Sirve como referencia para la implementación de controladores RESTful
 * utilizando Eloquent ORM y respuestas estandarizadas.
 */
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
    public function getUsers(): void
    {
        try {
            $rawPage    = $this->app->request()->query['page'];
            $rawPerPage = $this->app->request()->query['per_page'];
            $page    = max(1, is_numeric($rawPage) ? (int) $rawPage : 1);
            $perPage = min(100, max(1, is_numeric($rawPerPage) ? (int) $rawPerPage : 15));

            $paginator = User::paginate($perPage, ['*'], 'page', $page);
            ApiResponse::fromPaginator($this->app, $paginator);
        } catch (Throwable $throwable) {
            error_log((string) $throwable);
            ApiResponse::error($this->app, self::INTERNAL_ERROR_MSG, 500);
        }
    }

    /**
     * Obtiene un usuario específico por su ID.
     *
     * @param int $id El identificador único del usuario.
     */
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
            ApiResponse::error($this->app, self::INTERNAL_ERROR_MSG, 500);
        }
    }

    /**
     * Crea un nuevo usuario.
     *
     * Valida los datos de entrada y crea un nuevo registro en la base de datos.
     */
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
            ApiResponse::error($this->app, self::INTERNAL_ERROR_MSG, 500);
        }
    }

    /**
     * Actualiza un usuario existente.
     *
     * @param int $id El identificador único del usuario a actualizar.
     */
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
            ApiResponse::error($this->app, self::INTERNAL_ERROR_MSG, 500);
        }
    }

    /**
     * Elimina un usuario existente.
     *
     * @param int $id El identificador único del usuario a eliminar.
     */
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
            ApiResponse::error($this->app, self::INTERNAL_ERROR_MSG, 500);
        }
    }
}
