<?php

declare(strict_types=1);

use app\models\User;
use flight\Engine;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

final class ApiEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Boot Eloquent with in-memory SQLite
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // Run migrations
        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        // Seed initial data
        User::create(['name' => 'Bob Jones', 'email' => 'bob@example.com']);
        User::create(['name' => 'Bob Smith', 'email' => 'bsmith@example.com']);
        User::create(['name' => 'Suzy Johnson', 'email' => 'suzy@example.com']);
    }

    protected function tearDown(): void
    {
        Capsule::schema()->dropIfExists('users');
        parent::tearDown();
    }

    public function testHealthEndpoint(): void
    {
        [$status, $body] = $this->dispatch('GET', '/health');

        self::assertSame(200, $status);
        $json = json_decode($body, true);
        self::assertIsArray($json);
        self::assertSame('ok', $json['status'] ?? null);
        self::assertIsString($json['timestamp'] ?? null);
    }

    public function testListUsers(): void
    {
        [$status, $body] = $this->dispatch('GET', '/api/v1/users');

        self::assertSame(200, $status);
        $json = json_decode($body, true);
        self::assertTrue($json['success'] ?? false);
        self::assertCount(3, $json['data'] ?? []);
    }

    public function testGetUserSuccess(): void
    {
        [$status, $body] = $this->dispatch('GET', '/api/v1/users/1');

        self::assertSame(200, $status);
        $json = json_decode($body, true);
        self::assertTrue($json['success'] ?? false);
        self::assertSame(1, $json['data']['id'] ?? null);
    }

    public function testGetUserNotFound(): void
    {
        [$status, $body] = $this->dispatch('GET', '/api/v1/users/999');

        self::assertSame(404, $status);
        $json = json_decode($body, true);
        self::assertFalse($json['success'] ?? true);
        self::assertSame('Usuario no encontrado', $json['message'] ?? null);
    }

    public function testCreateUser(): void
    {
        [$status, $body] = $this->dispatch('POST', '/api/v1/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
        ]);

        self::assertSame(201, $status);
        $json = json_decode($body, true);
        self::assertTrue($json['success'] ?? false);
        self::assertSame('New User', $json['data']['name'] ?? null);
    }

    public function testUpdateUser(): void
    {
        [$status, $body] = $this->dispatch('PUT', '/api/v1/users/2', [
            'name' => 'Updated',
        ]);

        self::assertSame(200, $status);
        $json = json_decode($body, true);
        self::assertTrue($json['success'] ?? false);
        self::assertSame(2, $json['data']['id'] ?? null);
        self::assertSame('Updated', $json['data']['name'] ?? null);
    }

    public function testDeleteUser(): void
    {
        [$status, $body] = $this->dispatch('DELETE', '/api/v1/users/3');

        self::assertSame(200, $status);
        $json = json_decode($body, true);
        self::assertTrue($json['success'] ?? false);
        self::assertSame(true, $json['data']['deleted'] ?? null);
        self::assertSame(3, $json['data']['id'] ?? null);
    }

    private function dispatch(string $method, string $uri, array $post = []): array
    {
        $engine = new Engine();
        Flight::setEngine($engine);

        $engine->set('flight.handle_errors', false);
        $engine->set('flight.content_length', false);

        $app = $engine;
        $router = $app->router();
        require __DIR__ . '/../../app/config/routes.php';

        $_GET = [];
        $_POST = $post;
        $_REQUEST = array_merge($_GET, $_POST);
        $_COOKIE = [];
        $_FILES = [];
        $_SERVER = [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $uri,
            'SCRIPT_NAME' => '/index.php',
            'HTTP_HOST' => 'localhost',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '8000',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => (string) strlen(http_build_query($_POST)),
        ];

        ob_start();
        $engine->start();
        $body = ob_get_clean();

        $status = $engine->response()->status();
        $headers = $engine->response()->headers();

        return [$status, $body, $headers];
    }
}
