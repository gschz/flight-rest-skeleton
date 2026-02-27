<?php

declare(strict_types=1);

namespace app\controllers;

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
        // You could actually pull data from the database if you had one set up
        // $users = $this->app->db()->fetchAll("SELECT * FROM users");
        $users = [
            ['id' => 1, 'name' => 'Bob Jones', 'email' => 'bob@example.com'],
            ['id' => 2, 'name' => 'Bob Smith', 'email' => 'bsmith@example.com'],
            ['id' => 3, 'name' => 'Suzy Johnson', 'email' => 'suzy@example.com'],
        ];

        // You actually could overwrite the json() method if you just wanted to
        // to ->json($users); and it would auto set pretty print for you.
        // https://flightphp.com/learn#overriding
        $this->app->json($users, 200, true, 'utf-8', JSON_PRETTY_PRINT);
    }

    public function getUser(int $id): void
    {
        // You could actually pull data from the database if you had one set up
        // $user = $this->app->db()->fetchRow("SELECT * FROM users WHERE id = ?", [ $id ]);
        $users = [
            ['id' => 1, 'name' => 'Bob Jones', 'email' => 'bob@example.com'],
            ['id' => 2, 'name' => 'Bob Smith', 'email' => 'bsmith@example.com'],
            ['id' => 3, 'name' => 'Suzy Johnson', 'email' => 'suzy@example.com'],
        ];
        $user = array_find($users, fn ($candidate): bool => $candidate['id'] === $id);

        $this->app->json([
            'success' => true,
            'user' => $user
        ], 200, true, 'utf-8', JSON_PRETTY_PRINT);
    }

    public function updateUser(int $id): void
    {
        // You could actually update data from the database if you had one set up
        // $statement = $this->app->db()->runQuery("UPDATE users SET email = ? WHERE id = ?", [ $this->app->data['email'], $id ]);
        $this->app->json([
            'success' => true,
            'id' => $id
        ], 200, true, 'utf-8', JSON_PRETTY_PRINT);
    }
}
