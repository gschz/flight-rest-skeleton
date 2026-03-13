<?php

declare(strict_types=1);

use app\controllers\AuthController;
use app\middlewares\AuthMiddleware;
use app\middlewares\RateLimitMiddleware;
use app\models\User;
use app\utils\JwtService;
use flight\Engine;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

final class ApiEndpointsTest extends TestCase
{
    private array $originalGet;
    private array $originalPost;
    private array $originalRequest;
    private array $originalCookie;
    private array $originalFiles;
    private array $originalServer;
    private string $authToken = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalGet     = $_GET;
        $this->originalPost    = $_POST;
        $this->originalRequest = $_REQUEST;
        $this->originalCookie  = $_COOKIE;
        $this->originalFiles   = $_FILES;
        $this->originalServer  = $_SERVER;

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

        Capsule::schema()->create('rate_limits', function (Blueprint $table) {
            $table->string('key', 255)->primary();
            $table->integer('hits')->default(0);
            $table->bigInteger('window_start');
        });

        Capsule::schema()->create('refresh_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('token', 255)->unique();
            $table->dateTime('expires_at');
            $table->integer('revoked')->default(0);
            $table->dateTime('created_at')->nullable();
        });

        // Seed initial data
        User::create([
            'name' => 'Bob Jones',
            'email' => 'bob@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);
        User::create([
            'name' => 'Bob Smith',
            'email' => 'bsmith@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);
        User::create([
            'name' => 'Suzy Johnson',
            'email' => 'suzy@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        // Generar token JWT para autenticar las pruebas de rutas protegidas
        $jwtService      = new JwtService();
        $this->authToken = $jwtService->encode($jwtService->generatePayload(1, 'user'));
    }

    protected function tearDown(): void
    {
        Capsule::schema()->dropIfExists('refresh_tokens');
        Capsule::schema()->dropIfExists('rate_limits');
        Capsule::schema()->dropIfExists('users');

        $_GET     = $this->originalGet;
        $_POST    = $this->originalPost;
        $_REQUEST = $this->originalRequest;
        $_COOKIE  = $this->originalCookie;
        $_FILES   = $this->originalFiles;
        $_SERVER  = $this->originalServer;

        parent::tearDown();
    }

    public function testLoginSuccess(): void
    {
        [$status, $body] = $this->dispatch('POST', '/api/v1/auth/login', [
            'email'    => 'bob@example.com',
            'password' => 'secret',
        ], false);

        self::assertSame(200, $status);
        $json = json_decode($body, true);
        self::assertTrue($json['success'] ?? false);
        self::assertArrayHasKey('access_token', $json['data'] ?? []);
        self::assertArrayHasKey('refresh_token', $json['data'] ?? []);
        self::assertSame('Bearer', $json['data']['token_type'] ?? null);
        self::assertGreaterThan(0, $json['data']['expires_in'] ?? 0);
    }

    public function testLoginInvalidCredentials(): void
    {
        [$status, $body] = $this->dispatch('POST', '/api/v1/auth/login', [
            'email'    => 'bob@example.com',
            'password' => 'wrong_password',
        ], false);

        self::assertSame(401, $status);
        $json = json_decode($body, true);
        self::assertFalse($json['success'] ?? true);
    }

    public function testLoginValidationFails(): void
    {
        [$status, $body] = $this->dispatch('POST', '/api/v1/auth/login', [
            'email'    => 'not-an-email',
            'password' => '',
        ], false);

        self::assertSame(422, $status);
        $json = json_decode($body, true);
        self::assertFalse($json['success'] ?? true);
        self::assertArrayHasKey('errors', $json);
    }

    public function testRefreshToken(): void
    {
        // Login para obtener un refresh_token
        $loginResult = $this->dispatch('POST', '/api/v1/auth/login', [
            'email'    => 'bob@example.com',
            'password' => 'secret',
        ], false);
        $loginData    = json_decode((string) $loginResult[1], true);
        $refreshToken = (string) ($loginData['data']['refresh_token'] ?? '');

        self::assertNotEmpty($refreshToken, 'El login debe devolver un refresh_token');

        [$status, $body] = $this->dispatch('POST', '/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ], false);

        self::assertSame(200, $status);
        $json = json_decode($body, true);
        self::assertTrue($json['success'] ?? false);
        self::assertArrayHasKey('access_token', $json['data'] ?? []);
    }

    public function testLogout(): void
    {
        // Login para obtener un refresh_token
        $loginResult  = $this->dispatch('POST', '/api/v1/auth/login', [
            'email'    => 'bob@example.com',
            'password' => 'secret',
        ], false);
        $loginData    = json_decode((string) $loginResult[1], true);
        $refreshToken = (string) ($loginData['data']['refresh_token'] ?? '');

        self::assertNotEmpty($refreshToken, 'El login debe devolver un refresh_token');

        [$status, $body] = $this->dispatch('POST', '/api/v1/auth/logout', [
            'refresh_token' => $refreshToken,
        ], false);

        self::assertSame(200, $status);
        $json = json_decode($body, true);
        self::assertTrue($json['success'] ?? false);

        // Verificar que el token ya no puede usarse
        [$refreshStatus] = $this->dispatch('POST', '/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ], false);
        self::assertSame(401, $refreshStatus);
    }

    public function testProtectedRouteRequiresToken(): void
    {
        $engine = new Engine();
        Flight::setEngine($engine);
        $engine->set('flight.handle_errors', false);
        $engine->set('flight.content_length', false);

        // Sin header Authorization — debe devolver 401
        unset($_SERVER['HTTP_AUTHORIZATION']);

        $middleware = new AuthMiddleware($engine);
        ob_start();
        $middleware->before([]);
        ob_end_clean();

        self::assertSame(401, $engine->response()->status());
        /** @var array<string, mixed> $json */
        $json = json_decode((string) $engine->response()->getBody(), true);
        self::assertFalse($json['success'] ?? true);
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

        // Verificar estructura de paginación
        $meta = $json['meta'] ?? [];
        self::assertSame(3, $meta['total'] ?? null);
        self::assertSame(1, $meta['page'] ?? null);
        self::assertSame(15, $meta['per_page'] ?? null);
        self::assertSame(1, $meta['pages'] ?? null);
        self::assertFalse($meta['has_more'] ?? true);
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
            'name'  => 'Updated',
            'email' => 'updated@example.com',
        ]);

        self::assertSame(200, $status);
        $json = json_decode($body, true);
        self::assertTrue($json['success'] ?? false);
        self::assertSame(2, $json['data']['id'] ?? null);
        self::assertSame('Updated', $json['data']['name'] ?? null);
    }

    public function testRateLimitingBlocks429(): void
    {
        putenv('RATE_LIMIT_ENABLED=true');
        $_ENV['RATE_LIMIT_ENABLED'] = 'true';

        try {
            $makeEngine = static function (): Engine {
                $engine = new Engine();
                Flight::setEngine($engine);
                $engine->set('flight.handle_errors', false);
                $engine->set('flight.content_length', false);

                return $engine;
            };

            // Requests 1 + 2: dentro del límite (maxRequests=2)
            $engine1    = $makeEngine();
            $middleware1 = new RateLimitMiddleware($engine1, 2, 60);
            ob_start();
            $middleware1->before([]);
            ob_end_clean();
            self::assertSame(200, $engine1->response()->status());

            $engine2    = $makeEngine();
            $middleware2 = new RateLimitMiddleware($engine2, 2, 60);
            ob_start();
            $middleware2->before([]);
            ob_end_clean();
            self::assertSame(200, $engine2->response()->status());

            // Request 3: límite alcanzado — debe devolver 429 + Retry-After
            $engine3    = $makeEngine();
            $middleware3 = new RateLimitMiddleware($engine3, 2, 60);
            ob_start();
            $middleware3->before([]);
            ob_end_clean();

            self::assertSame(429, $engine3->response()->status());

            $headers = $engine3->response()->headers();
            self::assertArrayHasKey('Retry-After', $headers);
            self::assertGreaterThan(0, (int) ($headers['Retry-After'] ?? 0));
        } finally {
            putenv('RATE_LIMIT_ENABLED');
            unset($_ENV['RATE_LIMIT_ENABLED']);
            Capsule::table('rate_limits')->truncate();
        }
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

    public function testCreateUserDuplicateEmail(): void
    {
        [$status, $body] = $this->dispatch(
            'POST',
            '/api/v1/users',
            [
                'name'  => 'Duplicate',
                'email' => 'bob@example.com', // ya existe en setUp
            ]
        );

        // Debe rechazar con 409 Conflict o 422 Unprocessable
        self::assertContains($status, [409, 422]);
        $json = json_decode($body, true);
        self::assertFalse($json['success'] ?? true);
    }

    public function testUpdateUserNotFound(): void
    {
        [$status, $body] = $this->dispatch(
            'PUT',
            '/api/v1/users/999',
            [
                'name'  => 'Ghost',
                'email' => 'ghost@example.com',
            ]
        );

        self::assertSame(404, $status);
        $json = json_decode($body, true);
        self::assertFalse($json['success'] ?? true);
    }

    public function testDeleteUserNotFound(): void
    {
        [$status, $body] = $this->dispatch(
            'DELETE',
            '/api/v1/users/999'
        );

        self::assertSame(404, $status);
        $json = json_decode($body, true);
        self::assertFalse($json['success'] ?? true);
    }

    public function testDocsSpecNotFound(): void
    {
        // El archivo openapi.json no existe en el entorno de tests
        $specPath = __DIR__ . '/../../public/api-docs/openapi.json';
        $existed  = file_exists($specPath);
        $backupPath = null;
        if ($existed) {
            $backupPath = $specPath . '.bak.' . uniqid('', true);
            rename($specPath, $backupPath);
        }

        try {
            [$status, $body] = $this->dispatch(
                'GET',
                '/api-docs/openapi.json',
                [],
                false
            );

            self::assertSame(404, $status);
            $json = json_decode($body, true);
            self::assertFalse($json['success'] ?? true);
        } finally {
            if ($existed && is_string($backupPath) && file_exists($backupPath)) {
                rename($backupPath, $specPath);
            }
        }
    }

    public function testDocsSpecServedWhenExists(): void
    {
        $specPath = __DIR__ . '/../../public/api-docs/openapi.json';
        $created  = false;

        if (!file_exists($specPath)) {
            file_put_contents($specPath, json_encode([
                'openapi' => '3.0.0',
                'info' => ['title' => 'Test', 'version' => '1.0.0'],
                'paths' => []
            ]));
            $created = true;
        }

        try {
            [$status,, $headers] = $this->dispatch(
                'GET',
                '/api-docs/openapi.json',
                [],
                false
            );

            self::assertSame(200, $status);
            self::assertArrayHasKey('Content-Type', $headers);
            self::assertStringContainsString(
                'application/json',
                (string) ($headers['Content-Type'] ?? '')
            );
            self::assertSame(
                'no-store',
                $headers['Cache-Control'] ?? null
            );
        } finally {
            if ($created) {
                unlink($specPath);
            }
        }
    }

    public function testDocsSwaggerUI(): void
    {
        [$status, $body] = $this->dispatchWithViews(
            'GET',
            '/docs',
            [],
            false
        );

        self::assertSame(200, $status);
        self::assertStringContainsString('swagger', strtolower($body));
    }

    public function testDocsRedoc(): void
    {
        [$status, $body] = $this->dispatchWithViews(
            'GET',
            '/docs/redoc',
            [],
            false
        );

        self::assertSame(200, $status);
        self::assertStringContainsString('redoc', strtolower($body));
    }

    /**
     * Despacha una petición con la ruta de vistas configurada (para endpoints que renderizan HTML).
     *
     * @param array<string, mixed> $post
     * @return array{0: int, 1: string, 2: array<string, string>}
     */
    private function dispatchWithViews(
        string $method,
        string $uri,
        array $post = [],
        bool $withAuth = true
    ): array {
        return $this->dispatch(
            $method,
            $uri,
            $post,
            $withAuth,
            __DIR__ . '/../../app/views'
        );
    }

    private function dispatch(
        string $method,
        string $uri,
        array $post = [],
        bool $withAuth = true,
        string $viewsPath = ''
    ): array {
        $engine = new Engine();
        Flight::setEngine($engine);

        $engine->set('flight.handle_errors', false);
        $engine->set('flight.content_length', false);

        if ($viewsPath !== '') {
            $engine->set('flight.views.path', $viewsPath);
        }

        $app    = $engine;
        $router = $app->router();
        require __DIR__ . '/../../app/config/routes.php';

        $_GET     = [];
        $_POST    = $post;
        $_REQUEST = array_merge($_GET, $_POST);
        $_COOKIE  = [];
        $_FILES   = [];
        $_SERVER  = [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI'    => $uri,
            'SCRIPT_NAME'    => '/index.php',
            'HTTP_HOST'      => 'localhost',
            'SERVER_NAME'    => 'localhost',
            'SERVER_PORT'    => '8000',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => (string) strlen(http_build_query($_POST)),
        ];

        if ($withAuth && $this->authToken !== '') {
            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $this->authToken;
        }

        ob_start();
        $engine->start();
        $body = ob_get_clean();

        $status  = $engine->response()->status();
        $headers = $engine->response()->headers();

        return [$status, $body, $headers];
    }
}
