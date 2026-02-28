<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\User;
use app\utils\ApiResponse;
use flight\Engine;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * Obtiene la lista de todos los usuarios.
     *
     * Recupera todos los registros de la tabla de usuarios y los devuelve
     * en formato JSON.
     */
    public function getUsers(): void
    {
        try {
            $users = User::all();
            ApiResponse::success($this->app, $users);
        } catch (Throwable $throwable) {
            ApiResponse::error(
                $this->app,
                'Error al recuperar la lista de usuarios: ' . $throwable->getMessage(),
                500
            );
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
            ApiResponse::error($this->app, 'Usuario no encontrado', 404);
        } catch (Throwable $e) {
            ApiResponse::error(
                $this->app,
                'Error al recuperar el usuario: ' . $e->getMessage(),
                500
            );
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

        // Validación simple
        if (empty($data['name']) || empty($data['email'])) {
            ApiResponse::error(
                $this->app,
                'El nombre y el correo electrónico son obligatorios',
                400
            );

            return;
        }

        try {
            // Verificar duplicados (ejemplo simple, idealmente usar request validation)
            if (User::where('email', $data['email'])->exists()) {
                ApiResponse::error(
                    $this->app,
                    'El correo electrónico ya está registrado',
                    409
                );

                return;
            }

            $user = User::create($data);
            ApiResponse::success($this->app, $user, 201);
        } catch (Throwable $throwable) {
            ApiResponse::error(
                $this->app,
                'No se pudo crear el usuario: ' . $throwable->getMessage(),
                500
            );
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

            $user->update($data);

            ApiResponse::success($this->app, $user);
        } catch (ModelNotFoundException) {
            ApiResponse::error($this->app, 'Usuario no encontrado', 404);
        } catch (Throwable $e) {
            ApiResponse::error(
                $this->app,
                'Error al actualizar el usuario: ' . $e->getMessage(),
                500
            );
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

            ApiResponse::success($this->app, ['deleted' => true, 'id' => $id]);
        } catch (ModelNotFoundException) {
            ApiResponse::error($this->app, 'Usuario no encontrado', 404);
        } catch (Throwable $e) {
            ApiResponse::error(
                $this->app,
                'Error al eliminar el usuario: ' . $e->getMessage(),
                500
            );
        }
    }
}
