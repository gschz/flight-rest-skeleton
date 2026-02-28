<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->table('users')->truncate();

        $data = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => password_hash('secret', PASSWORD_BCRYPT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => password_hash('secret', PASSWORD_BCRYPT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Guest User',
                'email' => 'guest@example.com',
                'password' => password_hash('secret', PASSWORD_BCRYPT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        $this->table('users')->insert($data)->saveData();
    }
}
