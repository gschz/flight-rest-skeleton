<?php

declare(strict_types=1);

namespace app\controllers;

use app\utils\ApiResponse;
use flight\Engine;

class ApiExampleController
{
    /** @param Engine<object> $app */
    public function __construct(protected Engine $app)
    {
        //
    }

    public function getUsers(): void
    {
        // TODO: Replace with real Eloquent query e.g. User::all()
        $users = [
            ['id' => 1, 'name' => 'Bob Jones', 'email' => 'bob@example.com'],
            ['id' => 2, 'name' => 'Bob Smith', 'email' => 'bsmith@example.com'],
            ['id' => 3, 'name' => 'Suzy Johnson', 'email' => 'suzy@example.com'],
        ];

        ApiResponse::success($this->app, $users);
    }

    public function getUser(int $id): void
    {
        // TODO: Replace with real Eloquent query e.g. User::findOrFail($id)
        $users = [
            ['id' => 1, 'name' => 'Bob Jones', 'email' => 'bob@example.com'],
            ['id' => 2, 'name' => 'Bob Smith', 'email' => 'bsmith@example.com'],
            ['id' => 3, 'name' => 'Suzy Johnson', 'email' => 'suzy@example.com'],
        ];
        $user = array_values(array_filter($users, fn (array $u): bool => $u['id'] === $id))[0] ?? null;

        if ($user === null) {
            ApiResponse::error($this->app, 'User not found', 404);
            return;
        }

        ApiResponse::success($this->app, $user);
    }

    public function createUser(): void
    {
        // TODO: Validate and persist via Eloquent
        $body = $this->app->request()->data->getData();
        ApiResponse::success($this->app, $body, 201);
    }

    public function updateUser(int $id): void
    {
        // TODO: Validate and update via Eloquent
        $body = $this->app->request()->data->getData();
        ApiResponse::success($this->app, array_merge(['id' => $id], $body));
    }

    public function deleteUser(int $id): void
    {
        // TODO: Delete via Eloquent
        ApiResponse::success($this->app, ['deleted' => true, 'id' => $id]);
    }
}
